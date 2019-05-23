<?php

use Venespana\Sso\Core\AuthSystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBrokersTable extends Migration
{
    /**
     * @inheritDoc
     * @see Illuminate\Database\Migrations\Migration@up
     *
     * @return void
     */
    public function up()
    {
        if (AuthSystem::isServer()) {
            Schema::create(config('sso.broker_table', 'brokers'), function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->comment('Broker name');
                $table->string('hash')->unique()->comment('Broker unique hash identifiactor');
                $table->string('secret')->comment('secret key to make hand shake');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('sso.broker_table', 'brokers'));
    }
}
