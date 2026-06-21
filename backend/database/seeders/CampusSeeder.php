<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Campus;

class CampusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Campus::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Campus::create([
            'name' => 'University of Lagos',
            'slug' => 'unilag',
            'email_domain' => 'unilag.edu.ng',
            'logo_url' => 'https://example.com/logos/unilag.png',
            'is_active' => true
        ]);
        Campus::create([
            'name' => 'Obafemi Awolowo University',
            'slug' => 'oau',
            'email_domain' => 'oau.edu.ng',
            'logo_url' => 'https://example.com/logos/oau.png',
            'is_active' => true
        ]);
        Campus::create([
            'name' => 'Ahmadu Bello University',
            'slug' => 'abu',
            'email_domain' => 'abu.edu.ng',
            'logo_url' => 'https://example.com/logos/abu.png',
            'is_active' => true
        ]);

    }
}
