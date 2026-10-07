<?php

namespace Database\Factories;

use App\Conversation;
use App\Folder;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition()
    {
        return [
            'type'                => $this->faker->randomElement([Conversation::TYPE_EMAIL, Conversation::TYPE_PHONE]),
            // Unassigned folder of the mailbox if folder_id is not passed.
            'folder_id'           => function (array $attributes) {
                if (empty($attributes['mailbox_id'])) {
                    return null;
                }
                $folder = Folder::where(['mailbox_id' => $attributes['mailbox_id'], 'type' => Folder::TYPE_UNASSIGNED])->first();

                return $folder ? $folder->id : FolderFactory::new()->create()->id;
            },
            'state'               => Conversation::STATE_PUBLISHED, // $this->faker->randomElement(array_keys(Conversation::$states)),
            'subject'             => $this->faker->sentence(7),
            'customer_email'      => $this->faker->unique()->safeEmail,
            'cc'                  => json_encode([$this->faker->unique()->safeEmail]),
            'bcc'                 => json_encode([$this->faker->unique()->safeEmail]),
            'preview'             => $this->faker->text(Conversation::PREVIEW_MAXLENGTH),
            'imported'            => true,
            // Random user if created_by_user_id is not passed.
            'created_by_user_id'  => function () {
                return User::inRandomOrder()->value('id');
            },
            'source_via'          => Conversation::PERSON_CUSTOMER,
            'source_type'         => Conversation::SOURCE_TYPE_EMAIL,
        ];
    }
}
