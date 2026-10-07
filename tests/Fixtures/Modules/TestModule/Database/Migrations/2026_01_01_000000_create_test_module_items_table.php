<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('test_module_items', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_module_items');
    }
};
