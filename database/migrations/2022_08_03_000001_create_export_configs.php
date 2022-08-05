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
        Schema::create(config('nova-export-configuration.tables.export_configs'), function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('name', 255);
            $table->tinyText('description')->nullable();
            $table->json('filters')->nullable();
            $table->json('meta')->nullable();
            $table->longText('sql_query')->nullable();
            $table->dateTime('last_export_at')->nullable()->index();
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
        Schema::dropIfExists(config('nova-export-configuration.tables.export_configs'));
    }
};
