<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;

class TestBrevoEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:brevo-email {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Brevo SMTP by sending a test OTP email';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info('Testing Brevo SMTP configuration...');
        $this->info('Sending test OTP email to: ' . $email);
        
        // Generate test OTP
        $testOtp = '123456';
        $expiresAt = Carbon::now()->addMinutes(5);
        
        try {
            Mail::to($email)->send(new OtpMail(
                $testOtp,
                'Test User',
                $expiresAt
            ));
            
            $this->info('');
            $this->info('✓ Email sent successfully!');
            $this->info('✓ Brevo SMTP is working correctly');
            $this->info('');
            $this->info('Check your email inbox for the test OTP: ' . $testOtp);
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('');
            $this->error('✗ Failed to send email');
            $this->error('Error: ' . $e->getMessage());
            $this->error('');
            $this->error('Please check your Brevo SMTP credentials in .env file');
            
            return Command::FAILURE;
        }
    }
}
