<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Mail;
use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\Supply;
use App\Models\User;
use App\Models\AssetTransferHistory;
use App\Mail\LoginNotificationMail;

class ActivityLogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Listen for login events
        $this->app['events']->listen(Login::class, function ($event) {
            ActivityLog::log(
                'login', 
                $event->user, 
                [], 
                [], 
                null, 
                $event->user->id, 
                true // Include security context
            );

            // Send login notification email (optional feature)
            $this->sendLoginNotification($event->user);
        });

        // Listen for logout events
        $this->app['events']->listen(Logout::class, function ($event) {
            if ($event->user) {
                ActivityLog::log(
                    'logout', 
                    $event->user, 
                    [], 
                    [], 
                    null, 
                    $event->user->id, 
                    true // Include security context
                );
            }
        });

        // Listen for model events
        $this->registerModelEvents();
    }

    private function sendLoginNotification($user)
    {
        try {
            $agent = new \Jenssegers\Agent\Agent();
            $loginDetails = [
                'timestamp' => now()->format('F j, Y \a\t g:i A'),
                'ip_address' => request()->ip(),
                'browser' => $agent->browser(),
                'browser_version' => $agent->version($agent->browser()),
                'platform' => $agent->platform(),
                'device' => $agent->deviceType(),
                'location' => $this->getLocationFromIP(request()->ip()),
            ];

            Mail::to($user->email)->send(new LoginNotificationMail($user, $loginDetails));
        } catch (\Exception $e) {
            // Log the error but don't prevent login
            \Log::error('Failed to send login notification: ' . $e->getMessage());
        }
    }

    private function getLocationFromIP($ip)
    {
        // Simple IP location detection - you could enhance this with a service like GeoIP
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'Local Development';
        }
        // For production, you might integrate with a GeoIP service
        return 'Unknown Location';
    }

    /**
     * Register model event listeners
     */
    private function registerModelEvents(): void
    {
        // Asset events
        Asset::created(function ($asset) {
            ActivityLog::log('created', $asset, [], $asset->toArray());
        });

        Asset::updated(function ($asset) {
            ActivityLog::log('updated', $asset, $asset->getOriginal(), $asset->getChanges());
        });

        Asset::deleted(function ($asset) {
            ActivityLog::log('deleted', $asset, $asset->toArray(), []);
        });

        // Supply events
        Supply::created(function ($supply) {
            ActivityLog::log('created', $supply, [], $supply->toArray());
        });

        Supply::updated(function ($supply) {
            ActivityLog::log('updated', $supply, $supply->getOriginal(), $supply->getChanges());
        });

        Supply::deleted(function ($supply) {
            ActivityLog::log('deleted', $supply, $supply->toArray(), []);
        });

        // User events
        User::created(function ($user) {
            ActivityLog::log('created', $user, [], $user->toArray());
        });

        User::updated(function ($user) {
            // Don't log password changes here as it's handled separately
            $changes = $user->getChanges();
            if (isset($changes['password'])) {
                unset($changes['password']);
                if (empty($changes)) {
                    return; // Only password was changed, skip logging
                }
            }
            
            ActivityLog::log('updated', $user, $user->getOriginal(), $changes);
        });

        User::deleted(function ($user) {
            ActivityLog::log('deleted', $user, $user->toArray(), []);
        });

        // Asset Transfer History events
        AssetTransferHistory::created(function ($transfer) {
            ActivityLog::log('transferred', $transfer, [], $transfer->toArray());
        });
    }
}
