<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Category
        DB::table('permissions')->insert([
            'code' => 'category.read'
        ]);

        DB::table('permissions')->insert([
            'code' => 'category.create'
        ]);

        DB::table('permissions')->insert([
            'code' => 'category.update'
        ]);

        DB::table('permissions')->insert([
            'code' => 'category.delete'
        ]);

        //Brand
        DB::table('permissions')->insert([
            'code' => 'brand.read'
        ]);

        DB::table('permissions')->insert([
            'code' => 'brand.create'
        ]);

        DB::table('permissions')->insert([
            'code' => 'brand.update'
        ]);

        DB::table('permissions')->insert([
            'code' => 'brand.delete'
        ]);

        //Product
        DB::table('permissions')->insert([
            'code' => 'product.read'
        ]);

        DB::table('permissions')->insert([
            'code' => 'product.create'
        ]);

        DB::table('permissions')->insert([
            'code' => 'product.update'
        ]);

        DB::table('permissions')->insert([
            'code' => 'product.delete'
        ]);
    }
}
