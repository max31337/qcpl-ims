<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email configuration by sending a test email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'test@example.com';
        
        try {
            Mail::raw('This is a test email from QCPL-IMS to verify Mailtrap configuration.', function ($message) use ($email) {
                $message->to($email)
                        ->subject('QCPL-IMS Test Email');
            });
            
            $this->info("✅ Test email sent successfully to: {$email}");
            $this->info("📧 Check your Mailtrap inbox for the email.");
        } catch (\Exception $e) {
            $this->error("❌ Failed to send email: " . $e->getMessage());
        }
    }
}
