<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('questionnaire.table_names.questions', 'questions');
        $questionnairesTable = config('questionnaire.table_names.questionnaires', 'questionnaires');

        Schema::create($tableName, function (Blueprint $table) use ($questionnairesTable) {
            $table->id();
            $table->foreignId('questionnaire_id')->constrained($questionnairesTable)->cascadeOnDelete();
            $table->string('type'); // text, textarea, radio, checkbox, select, number, date, etc.
            $table->text('content'); // The question text
            $table->text('description')->nullable(); // Optional description/help text
            $table->json('options')->nullable(); // For choice-based questions
            $table->json('settings')->nullable(); // Type-specific settings
            $table->boolean('required')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index('questionnaire_id');
            $table->index('type');
            $table->index('order');
        });
    }

    public function down(): void
    {
        $tableName = config('questionnaire.table_names.questions', 'questions');
        Schema::dropIfExists($tableName);
    }
};
