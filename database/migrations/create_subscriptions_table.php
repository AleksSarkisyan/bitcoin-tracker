<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
          // $table->bigInteger('user_id')->nullable()->index();
            $table->string('email');
            $table->decimal('target_price', 15, 8)->nullable()->comment('The target price the asset needs to reach when the user will be notified.');
            $table->smallInteger('percent_change')->nullable()->comment('The percent of change the asset reaches when the user will be notified.');
            $table->smallInteger('time_interval')->nullable()->comment('The time in hours before user is notified. Works in combination with percent_change.');;
            $table->smallInteger('target_price_repeats')->default(0)->comment('How many times the user should be notified. 0 is for each time the job runs. 1 is for once only.');
            $table->smallInteger('percent_change_repeats')->default(0)->comment('How many times the user should be notified. 0 is for each time the job runs. 1 is for once only.');;
            $table->timestamp('target_price_expires')->default(0)->comment('Notification expires if a date is set.');
            $table->timestamp('percent_change_expires')->default(0)->comment('Notification expires if a date is set.');
            $table->timestamp('percent_change_notified_on')->nullable()->comment('The last date on which notification was sent.');
            $table->timestamp('target_price_notified_on')->nullable()->comment('The last date on which notification was sent.');
            $table->timestamps();

            /** Foreign keys do not work with partitioning */
            // $table->foreign('user_id')->references('id')->on('users');
        });

        DB::statement('
            ALTER TABLE subscriptions
            PARTITION BY KEY(id)
            PARTITIONS 4
        ');
    }

    public function down()
    {
        Schema::dropIfExists('subscriptions');
    }
};
