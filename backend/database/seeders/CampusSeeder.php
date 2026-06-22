<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Campus;

class CampusSeeder extends Seeder
{
    public function run(): void
    {
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Campus::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Campus::create([
            'name'         => 'Federal University of Technology, Akure',
            'slug'         => 'futa',
            'email_domain' => 'futa.edu.ng',
            'logo_url'     => 'https://upload.wikimedia.org/wikipedia/en/7/7a/Federal_University_of_Technology%2C_Akure_logo.png',
            'is_active'    => true,
        ]);
    }
}
