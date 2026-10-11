<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateCoopUser extends Command
{
    protected $signature = 'coop:user-create {name} {email} {role=student}';
    protected $description = 'Create a URU Coop-IS user with a selected role';

    public function handle()
    {
        $role = $this->argument('role');
        if (!in_array($role, ['student', 'advisor', 'officer', 'admin'], true)) {
            $this->error('Role must be student, advisor, officer, or admin.');
            return 1;
        }

        if (User::where('email', $this->argument('email'))->exists()) {
            $this->error('A user with this email already exists.');
            return 1;
        }

        $password = $this->secret('Password');
        $confirmation = $this->secret('Confirm password');
        if (!$password || $password !== $confirmation || strlen($password) < 10) {
            $this->error('Passwords must match and contain at least 10 characters.');
            return 1;
        }

        User::create([
            'name' => $this->argument('name'),
            'email' => $this->argument('email'),
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $this->info('User created with role: '.$role);
        return 0;
    }
}