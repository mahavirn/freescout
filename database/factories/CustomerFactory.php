<?php

namespace Database\Factories;

use App\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'first_name' => $this->faker->firstName,
            'last_name'  => $this->faker->lastName,
            'job_title'  => $this->faker->jobTitle,
            'phones'     => json_encode(Customer::formatPhones([['value' => $this->faker->phoneNumber, 'type' => Customer::PHONE_TYPE_WORK]])),
        ];
    }
}
