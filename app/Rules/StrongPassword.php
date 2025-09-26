<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    private $minLength;
    private $requireUppercase;
    private $requireLowercase;
    private $requireNumber;
    private $requireSpecialChar;
    
    public function __construct(
        int $minLength = 8, 
        bool $requireUppercase = true,
        bool $requireLowercase = true, 
        bool $requireNumber = true,
        bool $requireSpecialChar = true
    ) {
        $this->minLength = $minLength;
        $this->requireUppercase = $requireUppercase;
        $this->requireLowercase = $requireLowercase;
        $this->requireNumber = $requireNumber;
        $this->requireSpecialChar = $requireSpecialChar;
    }

    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('The :attribute must be a string.');
            return;
        }

        $errors = [];

        // Check minimum length
        if (strlen($value) < $this->minLength) {
            $errors[] = "at least {$this->minLength} characters";
        }

        // Check for uppercase letter
        if ($this->requireUppercase && !preg_match('/[A-Z]/', $value)) {
            $errors[] = "at least one uppercase letter";
        }

        // Check for lowercase letter
        if ($this->requireLowercase && !preg_match('/[a-z]/', $value)) {
            $errors[] = "at least one lowercase letter";
        }

        // Check for number
        if ($this->requireNumber && !preg_match('/[0-9]/', $value)) {
            $errors[] = "at least one number";
        }

        // Check for special character
        if ($this->requireSpecialChar && !preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = "at least one special character (!@#$%^&*()_+-=[]{}|;:,.<>?)";
        }

        if (!empty($errors)) {
            $errorMessage = 'The :attribute must contain ' . implode(', ', $errors) . '.';
            $fail($errorMessage);
        }
    }

    /**
     * Get password strength score (0-5)
     */
    public static function getStrengthScore(string $password): int
    {
        $score = 0;
        
        // Length check
        if (strlen($password) >= 8) $score++;
        if (strlen($password) >= 12) $score++;
        
        // Character type checks
        if (preg_match('/[A-Z]/', $password)) $score++;
        if (preg_match('/[a-z]/', $password)) $score++;
        if (preg_match('/[0-9]/', $password)) $score++;
        if (preg_match('/[^A-Za-z0-9]/', $password)) $score++;
        
        return min($score, 5);
    }

    /**
     * Get password strength text
     */
    public static function getStrengthText(int $score): string
    {
        return match($score) {
            0, 1 => 'Very Weak',
            2 => 'Weak',
            3 => 'Fair',
            4 => 'Good',
            5 => 'Strong',
            default => 'Unknown'
        };
    }

    /**
     * Get password requirements as array
     */
    public static function getRequirements(): array
    {
        return [
            'min_length' => config('auth.password_policy.min_length', 8),
            'uppercase' => config('auth.password_policy.require_uppercase', true),
            'lowercase' => config('auth.password_policy.require_lowercase', true),
            'number' => config('auth.password_policy.require_number', true),
            'special_char' => config('auth.password_policy.require_special_char', true),
        ];
    }
}