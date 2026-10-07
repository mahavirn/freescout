<?php

use Illuminate\Database\Seeder;

class MailboxesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Database\Factories\MailboxFactory::new()->count(3)->create();
    }
}
