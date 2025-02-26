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
            $table->string('email')->nullable(false);
            $table->string('symbol')->nullable(false);
            $table->decimal('target_price', 15, 8)->nullable()->comment('User\'s target price the asset needs to reach when the user will be notified.');
            $table->smallInteger('percent_change')->nullable()->comment('User\'s percent of change the asset needs to reach so that user will be notified.');
            $table->smallInteger('current_percent_change')->nullable()->comment('Current API percent change of the asset.');
            $table->string('time_interval')->nullable()->comment('User\'s time in hours before user is notified. Works in combination with percent_change.');
            $table->timestamp('percent_change_notified_on')->nullable()->comment('The last date on which email was sent.');
            $table->timestamp('target_price_notified_on')->nullable()->comment('The last date on which email was sent.');
            $table->timestamps();

            /** Foreign keys do not work with partitioning */
            // $table->foreign('user_id')->references('id')->on('users');

            /** Useful flags for improved notification experience */
            // $table->smallInteger('target_price_repeats')->default(0)->comment('How many times the user should be notified. 0 is for each time the job runs. 1 is for once only.');
            // $table->smallInteger('percent_change_repeats')->default(0)->comment('How many times the user should be notified. 0 is for each time the job runs. 1 is for once only.');
            // $table->timestamp('target_price_expires')->nullable()->comment('Notification expires if a date is set.');
            // $table->timestamp('percent_change_expires')->nullable()->comment('Notification expires if a date is set.');
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
