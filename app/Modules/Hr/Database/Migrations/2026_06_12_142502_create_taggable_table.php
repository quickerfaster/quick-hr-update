<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('taggables', function (Blueprint $table) {
            $table->id();
            
            // The fixed relation (e.g., tag_id) referencing the related table
            $table->foreignId('tag_id')
                  ->constrained('tags')
                  ->onDelete('cascade');

            // The polymorphic relation (Creates taggable_id and taggable_type)
            $table->morphs('taggable'); 
            
            $table->timestamps();

            // Professional Unique Index
            $table->unique(
                ['tag_id', 'taggable_id', 'taggable_type'], 
                'taggables_unique_idx'
            );
        });
    }

    public function down()
    {
        Schema::dropIfExists('taggables');
    }
};