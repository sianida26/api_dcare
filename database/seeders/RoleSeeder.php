<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::query()->insert($this->generator());
    }

    private function generator(): array
    {
        $roles = collect([]);

        foreach (Role::ROLES as $role) {
            $roles->push(['name' => $role]);
        }

        return $roles->toArray();
    }
}
