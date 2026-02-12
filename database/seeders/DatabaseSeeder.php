<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // Seed Roles
        $roles = [
            ['name' => 'Superadmin'],
            ['name' => 'Rendal'],
            ['name' => 'Admin Perwakilan'],
            ['name' => 'Korwas'],
            ['name' => 'Dalnis'],
            ['name' => 'Ketua Tim'],
            ['name' => 'Anggota'],
        ];

        DB::table('roles')->insert($roles);

        // Seed Perwakilan
        $perwakilanId = DB::table('perwakilan')->insertGetId([
            'nama_perwakilan' => 'BPKP Pusat',
            'kode_wilayah' => '00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed Superadmin User
        DB::table('users')->insert([
            'name' => 'Superadmin',
            'nip' => '199001012020011001',
            'email' => 'admin@evalueaction.test',
            'password' => Hash::make('password'), // default password
            'role_id' => 1, // Superadmin
            'perwakilan_id' => $perwakilanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->command->info('Roles, Perwakilan, and Superadmin user seeded!');
    }
}
