<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('nova-export-configuration.tables.export_config_stored_files'), function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('disk', 255)->index();
            $table->string('path', 255);
            $table->string('name', 255);
            $table->json('meta')->nullable();
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
        Schema::dropIfExists(config('nova-export-configuration.tables.export_config_stored_files'));
    }
};
