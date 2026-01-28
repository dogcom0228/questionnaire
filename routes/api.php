<?php

use Illuminate\Support\Facades\Route;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controllers\QuestionnaireCommandController;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controllers\QuestionnaireQueryController;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controllers\ResponseCommandController;
use Liangjin0228\Questionnaire\Infrastructure\Http\Controllers\ResponseQueryController;

Route::middleware(config('questionnaire.routes.api_middleware', ['api']))->group(function () {
    Route::get('/question-types', [QuestionnaireQueryController::class, 'questionTypes'])->name('api.question-types');

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/', [QuestionnaireQueryController::class, 'index'])->name('api.index');
        Route::post('/', [QuestionnaireCommandController::class, 'store'])->name('api.store');
        Route::get('/{questionnaire}', [QuestionnaireQueryController::class, 'show'])->name('api.show');
        Route::put('/{questionnaire}', [QuestionnaireCommandController::class, 'update'])->name('api.update');
        Route::delete('/{questionnaire}', [QuestionnaireCommandController::class, 'destroy'])->name('api.destroy');

        Route::post('/{questionnaire}/publish', [QuestionnaireCommandController::class, 'publish'])->name('api.publish');
        Route::post('/{questionnaire}/close', [QuestionnaireCommandController::class, 'close'])->name('api.close');

        Route::get('/{questionnaire}/responses', [ResponseQueryController::class, 'responses'])->name('api.responses');
        Route::get('/{questionnaire}/statistics', [ResponseQueryController::class, 'statistics'])->name('api.statistics');
    });

    Route::get('/public/{questionnaire}', [QuestionnaireQueryController::class, 'public'])->name('api.public');
    Route::group(['middleware' => [\Illuminate\Session\Middleware\StartSession::class]], function () {
        Route::post('/public/{questionnaire}/submit', [ResponseCommandController::class, 'submit'])->name('api.submit');
    });
});
