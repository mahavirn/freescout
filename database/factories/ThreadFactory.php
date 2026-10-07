<?php

namespace Database\Factories;

use App\Customer;
use App\Thread;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThreadFactory extends Factory
{
    protected $model = Thread::class;

    public function definition()
    {
        return [
            'type'                    => Thread::TYPE_CUSTOMER,
            // Random customer if customer_id is not passed.
            'customer_id'             => function () {
                $customer = Customer::inRandomOrder()->first();

                return $customer ? $customer->id : CustomerFactory::new()->create()->id;
            },
            'state'                   => Thread::STATE_PUBLISHED,
            'body'                    => $this->faker->text(500),
            'to'                      => function (array $attributes) {
                $customer = Customer::find($attributes['customer_id']);

                return json_encode([$customer ? $customer->getMainEmail() : $this->faker->unique()->safeEmail]);
            },
            'cc'                      => json_encode([$this->faker->unique()->safeEmail]),
            'bcc'                     => json_encode([$this->faker->unique()->safeEmail]),
            'source_via'              => Thread::PERSON_CUSTOMER,
            'source_type'             => Thread::SOURCE_TYPE_EMAIL,
            'created_by_customer_id'  => function (array $attributes) {
                return $attributes['customer_id'];
            },
        ];
    }
}
