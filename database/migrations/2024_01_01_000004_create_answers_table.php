<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('questionnaire.table_names.answers', 'questionnaire_answers');
        $responsesTable = config('questionnaire.table_names.responses', 'questionnaire_responses');
        $questionsTable = config('questionnaire.table_names.questions', 'questions');

        Schema::create($tableName, function (Blueprint $table) use ($responsesTable, $questionsTable) {
            $table->id();
            $table->foreignId('questionnaire_response_id')->constrained($responsesTable)->cascadeOnDelete();
            $table->foreignId('question_id')->constrained($questionsTable)->cascadeOnDelete();
            $table->text('value')->nullable(); // Store the answer value
            $table->timestamps();

            $table->index('questionnaire_response_id');
            $table->index('question_id');
            $table->unique(['questionnaire_response_id', 'question_id']);
        });
    }

    public function down(): void
    {
        $tableName = config('questionnaire.table_names.answers', 'questionnaire_answers');
        Schema::dropIfExists($tableName);
    }
};
