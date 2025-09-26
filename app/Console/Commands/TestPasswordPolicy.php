<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Rules\StrongPassword;

class TestPasswordPolicy extends Command
{
    protected $signature = 'test:password-policy';
    protected $description = 'Test the password policy implementation';

    public function handle()
    {
        $this->info('Testing Password Policy Implementation');
        $this->info('=====================================');
        $this->newLine();

        // Test passwords
        $testPasswords = [
            'weak' => 'password',
            'medium' => 'Password123',
            'strong' => 'MyStr0ng!P@ssw0rd',
            'very_strong' => 'C0mpl3x!P@ssw0rd$2024',
            'short' => 'P@1',
            'no_upper' => 'mypassword123!',
            'no_lower' => 'MYPASSWORD123!',
            'no_number' => 'MyPassword!',
            'no_special' => 'MyPassword123'
        ];

        foreach ($testPasswords as $type => $password) {
            $this->info("Testing: $type - '$password'");
            
            // Test with validation rule
            $rule = new StrongPassword();
            $errors = [];
            
            $rule->validate('password', $password, function($message) use (&$errors) {
                $errors[] = str_replace('The password', 'Password', $message);
            });
            
            // Get strength score
            $score = StrongPassword::getStrengthScore($password);
            $strength = StrongPassword::getStrengthText($score);
            
            if (empty($errors)) {
                $this->line("  ✓ Valid - Strength: $strength (Score: $score/5)");
            } else {
                $this->error("  ✗ Invalid - " . implode(', ', $errors));
                $this->line("    Strength: $strength (Score: $score/5)");
            }
            
            $this->newLine();
        }

        // Test configuration
        $this->info('Password Policy Configuration:');
        $this->line('Min Length: ' . config('auth.password_policy.min_length', 8));
        $this->line('Require Uppercase: ' . (config('auth.password_policy.require_uppercase', true) ? 'Yes' : 'No'));
        $this->line('Require Lowercase: ' . (config('auth.password_policy.require_lowercase', true) ? 'Yes' : 'No'));
        $this->line('Require Number: ' . (config('auth.password_policy.require_number', true) ? 'Yes' : 'No'));
        $this->line('Require Special Char: ' . (config('auth.password_policy.require_special_char', true) ? 'Yes' : 'No'));

        $this->newLine();
        $this->comment('Password policy testing completed!');
        $this->comment('You can now test the registration form at: /register');

        return 0;
    }
}