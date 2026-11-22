<?php

namespace App\Console\Commands;

use Igniter\Local\Models\Location;
use Igniter\System\Models\Language;
use Igniter\User\Facades\AdminAuth;
use Igniter\User\Models\User;
use Igniter\User\Models\UserGroup;
use Igniter\User\Models\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Creates the first admin (the "Initial Setup" page of TastyIgniter), so the shop opens straight away.
 * The login is written to .admin-login.txt (never committed). Only for this Codespace.
 */
class RestaurantAdmin extends Command
{
    protected $signature = 'restaurant:admin {--email=admin@restaurant.test} {--file=.admin-login.txt}';

    protected $description = 'Create the first admin login for this Codespace (saved in .admin-login.txt)';

    public function handle(): int
    {
        if (User::query()->exists()) {
            $this->info('An admin already exists. Nothing to do.');
            return self::SUCCESS;
        }

        // letters, mixed case, numbers and a symbol: TastyIgniter's password rule
        $password = Str::password(16, symbols: false).'-7aA';

        AdminAuth::getProvider()->register([
            'email' => $this->option('email'),
            'name' => 'Restaurant Admin',
            'language_id' => Language::first()?->language_id,
            'user_role_id' => UserRole::first()->user_role_id,
            'username' => 'admin',
            'password' => $password,
            'super_user' => true,
            'groups' => [UserGroup::first()->user_group_id],
            'locations' => array_filter([Location::first()?->location_id]),
        ], true);

        $file = base_path($this->option('file'));
        file_put_contents($file, "Admin page: /admin\nEmail: {$this->option('email')}\nUsername: admin\nPassword: {$password}\n(Only for this Codespace. Do not share or commit this file.)\n");
        @chmod($file, 0600);

        $this->info('Admin created. Login saved in '.$this->option('file'));
        return self::SUCCESS;
    }
}
