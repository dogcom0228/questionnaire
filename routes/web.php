<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Questionnaire Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the QuestionnaireServiceProvider. The routes
| are grouped with the prefix and middleware defined in config.
|
*/

$controller = config('questionnaire.controllers.questionnaire', \Liangjin0228\Questionnaire\Http\Controllers\QuestionnaireController::class);

// Admin routes (require authentication)
if (config('questionnaire.features.admin', true)) {
    Route::prefix('admin')->name('admin.')->middleware(config('questionnaire.routes.middleware', ['web', 'auth']))->group(function () use ($controller) {
        Route::get('/', [$controller, 'index'])->name('index');
        Route::get('/create', [$controller, 'create'])->name('create');
        Route::post('/', [$controller, 'store'])->name('store');
        Route::get('/{questionnaire}', [$controller, 'show'])->name('show');
        Route::get('/{questionnaire}/edit', [$controller, 'edit'])->name('edit');
        Route::put('/{questionnaire}', [$controller, 'update'])->name('update');
        Route::delete('/{questionnaire}', [$controller, 'destroy'])->name('destroy');

        // Status actions
        Route::post('/{questionnaire}/publish', [$controller, 'publish'])->name('publish');
        Route::post('/{questionnaire}/close', [$controller, 'close'])->name('close');

        // Responses
        if (config('questionnaire.features.results', true)) {
            Route::get('/{questionnaire}/responses', [$controller, 'responses'])->name('responses');
        }
    });
}

// Public routes (for filling questionnaires)
if (config('questionnaire.features.public_fill', true)) {
    Route::prefix(config('questionnaire.routes.public_prefix', 'survey'))
        ->name('public.')
        ->middleware(config('questionnaire.routes.public_middleware', ['web', 'throttle:10,1']))
        ->group(function () use ($controller) {
            Route::get('/{questionnaire}', [$controller, 'fill'])->name('fill');
            Route::post('/{questionnaire}', [$controller, 'submit'])->name('submit')->middleware('throttle:5,1');
            Route::get('/{questionnaire}/thank-you', [$controller, 'thankyou'])->name('thankyou');
            Route::get('/{questionnaire}/closed', [$controller, 'closed'])->name('closed');
        });
}
