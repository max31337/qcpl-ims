<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use App\Mail\MfaCodeMail;
use App\Models\ActivityLog;

#[Layout('layouts.guest')]
class MfaChallenge extends Component
{
    public $mfa_code = '';
    public $code_sent = false;
    public $attempts = 0;
    public $max_attempts = 3;
    
    public function mount()
    {
        $user = auth()->user();
        
        \Log::info('MfaChallenge mount', [
            'user_id' => $user?->id,
            'mfa_enabled' => $user?->mfa_enabled,
            'session_mfa_verified_at' => session('mfa_verified_at'),
            'request_url' => request()->url()
        ]);
        
        // Redirect if MFA is not enabled for this user
        if (!$user || !$user->mfa_enabled) {
            return redirect()->route('dashboard');
        }
        
        // Send MFA code automatically only on first load
        if (!session()->has('mfa_challenge_mounted')) {
            session(['mfa_challenge_mounted' => true]);
            // Clear any existing rate limiting for the initial load
            session()->forget('mfa_last_code_sent');
            $this->sendMfaCode();
        } else {
            $this->code_sent = true;
        }
    }
    
    public function sendMfaCode()
    {
        $user = auth()->user();
        
        if (!$user || !$user->mfa_enabled) {
            return redirect()->route('dashboard');
        }
        
        // Check if we've already sent a code recently (within last 30 seconds)
        $lastCodeSent = session('mfa_last_code_sent');
        if ($lastCodeSent && now()->diffInSeconds($lastCodeSent) < 30) {
            session()->flash('info', 'Please wait before requesting another code.');
            $this->code_sent = true; // Ensure UI shows that code was sent
            return;
        }
        
        try {
            \Log::info('Starting MFA code generation', ['user_id' => $user->id]);
            
            $mfaCode = $user->generateMfaCode('login');
            \Log::info('MFA code generated successfully', [
                'user_id' => $user->id,
                'code' => $mfaCode->code,
                'expires_at' => $mfaCode->expires_at
            ]);
            
            // Send email immediately (not queued) for security
            Mail::to($user->email)->send(new MfaCodeMail($user, $mfaCode, 'login'));
            \Log::info('MFA email sent successfully', ['user_id' => $user->id, 'email' => $user->email]);
            
            $this->code_sent = true;
            session(['mfa_last_code_sent' => now()]);
            session()->flash('info', 'A verification code has been sent to your email address.');
            
            // Log the MFA challenge
            ActivityLog::log('mfa_challenge_sent', $user, [], [], 'MFA verification code sent for login', $user->id, true);
            
        } catch (\Exception $e) {
            \Log::error('MFA code sending failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            session()->flash('error', 'Failed to send verification code. Please try again.');
        }
    }
    
    public function verifyCode()
    {
        $this->validate([
            'mfa_code' => 'required|string|size:6'
        ]);
        
        $user = auth()->user();
        
        if (!$user || !$user->mfa_enabled) {
            return redirect()->route('dashboard');
        }
        
        $this->attempts++;
        
        \Log::info('MFA verification attempt', [
            'user_id' => $user->id,
            'code_entered' => $this->mfa_code,
            'attempts' => $this->attempts
        ]);
        
        if ($user->verifyMfaCode($this->mfa_code, 'login')) {
            // MFA verification successful
            session([
                'mfa_verified_at' => now(),
                'mfa_user_id' => $user->id
            ]);
            
            // Log successful MFA verification
            ActivityLog::log('mfa_verified', $user, [], [], 'MFA login verification successful', $user->id, true);
            
            \Log::info('MFA verification successful', [
                'user_id' => $user->id,
                'redirect_to' => session('url.intended', route('dashboard'))
            ]);
            
            // Get intended URL or default to dashboard
            $redirectUrl = session('url.intended', route('dashboard'));
            
            // Clear MFA-related session data
            session()->forget(['url.intended', 'mfa_challenge_mounted', 'mfa_last_code_sent']);
            
            // Set welcome back message
            $greeting = $this->getTimeBasedGreeting();
            session()->flash('success', "🎉 {$greeting}, {$user->name}! Multi-factor authentication verified successfully. Welcome back to QCPL-IMS!");
            
            // Clear the form and redirect immediately
            $this->mfa_code = '';
            
            return redirect()->to($redirectUrl);
            
        } else {
            // MFA verification failed
            ActivityLog::log('mfa_failed', $user, [], [], 'MFA login verification failed - attempt ' . $this->attempts, $user->id, true);
            
            \Log::info('MFA verification failed', [
                'user_id' => $user->id,
                'code_entered' => $this->mfa_code,
                'attempts' => $this->attempts
            ]);
            
            if ($this->attempts >= $this->max_attempts) {
                // Too many failed attempts - logout user
                Auth::logout();
                session()->invalidate();
                session()->regenerateToken();
                
                return redirect()->route('login')->with('error', 'Too many failed MFA attempts. Please login again.');
            }
            
            $this->addError('mfa_code', 'Invalid verification code. ' . ($this->max_attempts - $this->attempts) . ' attempts remaining.');
            $this->mfa_code = '';
        }
    }
    
    public function resendCode()
    {
        \Log::info('Resend code requested', ['user_id' => auth()->id()]);
        
        // Reset rate limiting for resend
        session()->forget('mfa_last_code_sent');
        
        $this->sendMfaCode();
        $this->mfa_code = '';
        
        if (!session()->has('error')) {
            session()->flash('info', 'A new verification code has been sent to your email.');
        }
    }
    
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        
        return redirect()->route('login');
    }

    private function getTimeBasedGreeting()
    {
        $hour = now()->hour;
        
        if ($hour >= 5 && $hour < 12) {
            return 'Good morning';
        } elseif ($hour >= 12 && $hour < 17) {
            return 'Good afternoon';
        } elseif ($hour >= 17 && $hour < 21) {
            return 'Good evening';
        } else {
            return 'Welcome back';
        }
    }

    public function render()
    {
        return view('livewire.mfa-challenge');
    }
}
