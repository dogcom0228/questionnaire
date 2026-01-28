<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $questionnairesTable = config('questionnaire.table_names.questionnaires', 'questionnaires');
        $questionsTable = config('questionnaire.table_names.questions', 'questions');
        $responsesTable = config('questionnaire.table_names.responses', 'responses');
        $answersTable = config('questionnaire.table_names.answers', 'answers');

        Schema::table($questionnairesTable, function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        Schema::table($questionsTable, function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        Schema::table($responsesTable, function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });

        Schema::table($answersTable, function (Blueprint $table) {
            $table->uuid('uuid')->nullable()->after('id')->unique();
        });
    }

    public function down(): void
    {
        $questionnairesTable = config('questionnaire.table_names.questionnaires', 'questionnaires');
        $questionsTable = config('questionnaire.table_names.questions', 'questions');
        $responsesTable = config('questionnaire.table_names.responses', 'responses');
        $answersTable = config('questionnaire.table_names.answers', 'answers');

        Schema::table($questionnairesTable, function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table($questionsTable, function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table($responsesTable, function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table($answersTable, function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
