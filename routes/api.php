<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Email\EmailApiController;
use App\Http\Controllers\Admin\Email\WebmailSsoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1/email')->middleware(['auth:sanctum'])->group(function () {
    Route::get('health', [EmailApiController::class, 'health']);
    Route::get('accounts', [EmailApiController::class, 'accounts']);
    Route::get('accounts/{account}', [EmailApiController::class, 'show']);
    Route::post('accounts/{account}/disable', [EmailApiController::class, 'disable']);
    Route::post('accounts/{account}/enable', [EmailApiController::class, 'enable']);
    Route::post('accounts/{account}/reset-password', [EmailApiController::class, 'resetPassword']);
    Route::post('accounts/{account}/quota', [EmailApiController::class, 'updateQuota']);
    Route::post('accounts/{account}/aliases', [EmailApiController::class, 'storeAlias']);
    Route::delete('aliases/{alias}', [EmailApiController::class, 'destroyAlias']);
    Route::get('lists', [EmailApiController::class, 'lists']);
    Route::post('lists', [EmailApiController::class, 'storeList']);
    Route::post('lists/{list}/sync', [EmailApiController::class, 'syncList']);
});

// Internal WebMail redeem (protect further with IP allowlist / shared secret in production)
Route::post('v1/email/webmail/redeem', [WebmailSsoController::class, 'redeem']);
