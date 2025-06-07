<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (env('DB_CONNECTION') === 'pgvector') {
            Schema::create('embeddings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('embeddable_id');
                $table->string('embeddable_type');
                $table->timestamps();
            });
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE embeddings ADD COLUMN data vector(1536)");
        } else {
            Schema::create('embeddings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('embeddable_id');
                $table->string('embeddable_type');
                $table->json('data');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('embeddings');
    }
};