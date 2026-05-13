<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Categories
        $ballId   = DB::table('item_categories')->insertGetId(['name' => 'Ball']);
        $pitchId  = DB::table('item_categories')->insertGetId(['name' => 'Pitch']);
        $racketId = DB::table('item_categories')->insertGetId(['name' => 'Racket']);

        // Item types
        DB::table('item_types')->insert([
            ['category_id' => $ballId,   'type_name' => 'Basic',   'price' => 2.00],
            ['category_id' => $ballId,   'type_name' => 'Premium', 'price' => 4.00],
            ['category_id' => $pitchId,  'type_name' => 'Open',    'price' => 20.00],
            ['category_id' => $pitchId,  'type_name' => 'Covered', 'price' => 30.00],
            ['category_id' => $racketId, 'type_name' => 'Basic',   'price' => 5.00],
            ['category_id' => $racketId, 'type_name' => 'Premium', 'price' => 10.00],
        ]);

        // Default admin (password: admin123)
        DB::table('users')->insert([
            'full_name'  => 'Admin',
            'email'      => 'admin@padel.com',
            'password'   => Hash::make('admin123'),
            'role'       => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
