<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ProvisionPortfolioAdmin extends Command
{
    protected $signature = 'portfolio:admin {email}';

    protected $description = 'Provision or update the owner administrator without public registration.';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || ! in_array($email, array_map('strtolower', config('portfolio.admin_emails', [])), true)) {
            $this->error('Email must be listed in PORTFOLIO_ADMIN_EMAILS.');

            return self::FAILURE;
        }
        $password = $this->secret('Temporary password (store it securely and rotate it)');
        if (! is_string($password) || strlen($password) < 12) {
            $this->error('Password must have at least 12 characters.');

            return self::FAILURE;
        }
        User::query()->updateOrCreate(['email' => $email], ['name' => 'Portfolio owner', 'password' => Hash::make($password), 'email_verified_at' => now()]);
        $this->info('Administrator provisioned.');

        return self::SUCCESS;
    }
}
