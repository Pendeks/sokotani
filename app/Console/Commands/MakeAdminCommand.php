<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdminCommand extends Command
{
    protected $signature = 'sokotani:make-admin {email : Email address of an existing account}';

    protected $description = 'Promote an existing account to a SokoTani administrator after password confirmation';

    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->components->error('No account exists for that email. Register it first, then run this command again.');

            return self::FAILURE;
        }

        if ($user->role === 'admin') {
            $this->components->info('This account is already an administrator.');

            return self::SUCCESS;
        }

        $password = $this->secret('Confirm this account password');

        if (! is_string($password) || ! Hash::check($password, $user->password)) {
            $this->components->error('Password did not match; account role was not changed.');

            return self::FAILURE;
        }

        $user->forceFill(['role' => 'admin'])->save();
        $this->components->info("{$user->email} is now an administrator.");

        return self::SUCCESS;
    }
}
