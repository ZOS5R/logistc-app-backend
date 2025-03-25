<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriverTripsTable extends Migration
{
    public function up()
    {
        Schema::create('driver_trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('driver_id');
            $table->string('trip_description');
            // Pickup coordinates
            $table->decimal('pick_up_latitude', 10, 7);
            $table->decimal('pick_up_longitude', 10, 7);
            // Drop-off coordinates
            $table->decimal('drop_off_latitude', 10, 7);
            $table->decimal('drop_off_longitude', 10, 7);
            // Status field with a default value
            $table->string('status')->default('pending');
            // Optional timestamps to record status changes
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('driver_trips');
    }
}
