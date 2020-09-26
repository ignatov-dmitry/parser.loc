<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('name')->nullable();
            $table->string('url');
            $table->float('price')->nullable();
            $table->integer('year')->nullable();

            $table->unsignedBigInteger('country_id');
            $table->foreign('country_id')
                  ->references('id')
                  ->on('countries');

            $table->unsignedBigInteger('region_id');
            $table->foreign('region_id')
                  ->references('id')
                  ->on('regions');

            
            $table->unsignedBigInteger('city_id');
            $table->foreign('city_id')
                  ->references('id')
                  ->on('cities');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')
                ->references('id')
                ->on('categories');
            $table->unsignedBigInteger('platform_id');
            $table->foreign('platform_id')
                ->references('id')
                ->on('platforms');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
}
