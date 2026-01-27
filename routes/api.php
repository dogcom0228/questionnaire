<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Questionnaire API Routes
|--------------------------------------------------------------------------
|
| These API routes are loaded by the QuestionnaireServiceProvider.
|
*/

$controller = config('questionnaire.controllers.api', \Liangjin0228\Questionnaire\Infrastructure\Http\Controllers\QuestionnaireController::class);

// Authenticated admin routes
Route::middleware(config('questionnaire.routes.api_middleware', ['api']))->group(function () use ($controller) {
    // Meta routes
    Route::get('/question-types', [$controller, 'questionTypes'])->name('api.question-types');

    // CRUD routes (require authentication)
    Route::middleware(['auth:sanctum'])->group(function () use ($controller) {
        Route::get('/', [$controller, 'index'])->name('api.index');
        Route::post('/', [$controller, 'store'])->name('api.store');
        Route::get('/{questionnaire}', [$controller, 'show'])->name('api.show');
        Route::put('/{questionnaire}', [$controller, 'update'])->name('api.update');
        Route::delete('/{questionnaire}', [$controller, 'destroy'])->name('api.destroy');

        // Status actions
        Route::post('/{questionnaire}/publish', [$controller, 'publish'])->name('api.publish');
        Route::post('/{questionnaire}/close', [$controller, 'close'])->name('api.close');

        // Results
        Route::get('/{questionnaire}/responses', [$controller, 'responses'])->name('api.responses');
        Route::get('/{questionnaire}/statistics', [$controller, 'statistics'])->name('api.statistics');
    });

    // Public routes - Apply 'web' middleware group if session is needed, or ensure 'api' handles sessions if needed
    // Assuming 'api' middleware group includes 'StartSession' if configured, but typically API is stateless.
    // However, the error 'Session store not set on request' suggests something is trying to access session.
    // Let's explicitly check if 'StartSession' is needed for submit.
    Route::get('/public/{questionnaire}', [$controller, 'public'])->name('api.public');
    Route::group(['middleware' => [\Illuminate\Session\Middleware\StartSession::class]], function () use ($controller) {
        Route::post('/public/{questionnaire}/submit', [$controller, 'submit'])->name('api.submit');
    });
});
