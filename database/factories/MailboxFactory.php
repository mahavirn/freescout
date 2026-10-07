<?php

namespace Database\Factories;

use App\Mailbox;
use Illuminate\Database\Eloquent\Factories\Factory;

class MailboxFactory extends Factory
{
    protected $model = Mailbox::class;

    public function definition()
    {
        $name = $this->faker->company;
        $email = $this->faker->unique()->companyEmail;
        $domain = explode('@', $email)[1];

        return [
            'name'      => $name,
            'email'     => $email,
            'aliases'   => 'support@'.$domain.',help@'.$domain.', contact@'.$domain,
            //'signature' => '--<br/>'.$name,
        ];
    }
}
