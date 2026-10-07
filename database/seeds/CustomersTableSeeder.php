<?php

use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Database\Factories\CustomerFactory::new()->count(5)->create()->each(function ($m) {
            $m->emails()->save(\Database\Factories\EmailFactory::new()->make());
            $m->emails()->save(\Database\Factories\EmailFactory::new()->make());
        });
    }
}
