<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use jeremykenedy\LaravelRoles\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminRole = Role::find(1);
        $userRole = Role::find(2);

        // Seed test admin
        $adminEmail = 'justin+mmstaff@midnightmechanism.com';
        $user = User::where('email', '=', $adminEmail)->first();
        if ($user === null) {
            $user = User::create([
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => $adminEmail,
                'password' => Hash::make('mmstaff'),
                'activated' => true,
                'token' => str_random(64),
            ]);

            $user->attachRole($adminRole);
            $user->save();
        }

        $userEmail = 'samsung@test.com';
        $user = User::where('email', '=', $userEmail)->first();
        if ($user === null) {
            $user = User::create([
                'email' => $userEmail,
                'password' => Hash::make('testtest'),
                'token' => str_random(64),
                'activated' => true,
            ]);

            $user->attachRole($userRole);
            $user->save();
        }
    }
}
