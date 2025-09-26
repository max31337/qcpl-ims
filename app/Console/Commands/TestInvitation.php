<?php

namespace App\Console\Commands;

use App\Models\UserInvitation;
use App\Mail\InvitationMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestInvitation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:invitation {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the invitation system by sending an invitation email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        try {
            // Create invitation
            $invitation = UserInvitation::createInvitation($email, 'This is a test invitation from the command line.');
            $this->info("Invitation created with token: {$invitation->token}");
            
            // Send email
            Mail::to($email)->send(new InvitationMail($invitation));
            $this->info("Email sent successfully to {$email}!");
            
            // Show registration URL
            $registrationUrl = route('register', ['token' => $invitation->token]);
            $this->info("Registration URL: {$registrationUrl}");
            
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
