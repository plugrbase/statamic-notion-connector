<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notion_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('database_id')->nullable();
            $table->string('collection_handle')->nullable();
            $table->json('field_mappings')->nullable();
            $table->boolean('enabled')->default(false);
            $table->json('mapped_pages')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notion_mappings');
    }
}; 