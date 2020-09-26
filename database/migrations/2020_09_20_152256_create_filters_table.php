<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFiltersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('filters', function (Blueprint $table) {
            $table->id();
            $table->float('min_price')->default(0);
            $table->float('max_price')->default(0);
            $table->unsignedBigInteger('country_id')->default(0);
            $table->unsignedBigInteger('region_id')->default(0);
            $table->unsignedBigInteger('city_id')->default(0);
            $table->unsignedBigInteger('brand')->default(0);
            $table->unsignedBigInteger('models')->default(0);
            $table->integer('year')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('filters');
    }
}
