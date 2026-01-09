<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('questionnaire.table_names.questionnaires', 'questionnaires');

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'published', 'closed'])->default('draft');
            $table->json('settings')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('requires_auth')->default(false);
            $table->unsignedInteger('submission_limit')->nullable();
            $table->string('duplicate_submission_strategy')->default('allow_multiple');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('starts_at');
            $table->index('ends_at');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        $tableName = config('questionnaire.table_names.questionnaires', 'questionnaires');
        Schema::dropIfExists($tableName);
    }
};
