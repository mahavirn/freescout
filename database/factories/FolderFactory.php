<?php

namespace Database\Factories;

use App\Folder;
use App\Mailbox;
use Illuminate\Database\Eloquent\Factories\Factory;

class FolderFactory extends Factory
{
    protected $model = Folder::class;

    public function definition()
    {
        return [
            // Random mailbox if mailbox_id is not passed.
            'mailbox_id' => function () {
                return Mailbox::inRandomOrder()->value('id');
            },
            'type'       => Folder::TYPE_UNASSIGNED,
        ];
    }
}
