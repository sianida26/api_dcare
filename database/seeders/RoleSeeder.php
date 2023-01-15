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
        $count = 0;

        foreach (Role::ROLES as $role) {
            if (Role::where(['name' => $role])->exists()) continue; //prevent duplicates

            $roles->push(['name' => $role]);
            $count++;
        }

        return $roles->toArray();
    }
}
