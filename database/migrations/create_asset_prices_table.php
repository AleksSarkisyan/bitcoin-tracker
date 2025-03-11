<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $tableName;
    public $partitionSize;

    public function __construct()
    {
        $this->tableName = 'asset_prices';
        $this->partitionSize = 4;
    }

    public function up()
    {
        Schema::create($this->tableName, function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->nullable(false);
            $table->string('current_price')->nullable(false)->comment('The asset price when calling the API.');
            $table->decimal('bid', 15, 8)->nullable(false)->comment('Price of last highest bid.');
            $table->decimal('ask', 15, 8)->nullable(false)->comment('Price of last lowest ask.');
            $table->bigInteger('mts')->nullable(false)->comment('The last date on which email was sent.');
            $table->string('time_interval')->nullable(false)->comment('The mts in hours. Used to avoid converting mts programatically and easier development.');
            $table->smallInteger('percent_difference')->nullable(false)->comment('The percentage difference between the current price and bid.');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE {$this->tableName} PARTITION BY KEY(id) PARTITIONS {$this->partitionSize}");
    }

    public function down()
    {
        Schema::dropIfExists($this->tableName);
    }
};
