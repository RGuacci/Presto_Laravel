<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\RevisorController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use Illuminate\Support\Facades\Route;

//  ROTTE PUBBLICHE
Route::get('/', [PublicController::class, 'homepage'])->name('homepage');
Route::get('/show/{article}/show', [ArticleController::class, 'show'])->name('article.show');
Route::get('/article/index', [ArticleController::class, 'index'])->name('article.index');
//Rotta cambio lingua 
Route::post('/lingua/{lang}',[PublicController::class,'setLanguage'])->name('setLocale');

// MIDDLEWARE UTENTE REGISTRATO
Route::middleware(['auth'])->group(function () {

    Route::get('/create/article', [ArticleController::class, 'create'])->name('create.article');
    Route::get('/article/{article}/edit', [ArticleController::class, 'edit'])->name('article.edit');
    Route::delete('/article/{article}', [ArticleController::class, 'destroy'])->name('article.destroy');
    // ROTTE REVISORE
    Route::get('/revisor/request', [RevisorController::class, 'becomeRevisor'])->name('become.revisor');
    Route::get('/make/{user}/revisor', [RevisorController::class, 'makeRevisor'])->name('make.revisor');
});

// ROTTE RECUPERO PASSWORD
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');

    // ROTTE AUTENTICAZIONE DI GOOGLE
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.login');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

// ROTTA REVISORE

Route::middleware('isRevisor')->group(function () {
    Route::get('/revisor/index', [RevisorController::class, 'index'])->name('revisor_index');
    Route::patch('/revisor/articles/review-selected', [RevisorController::class, 'reviewSelected'])
        ->name('revisor.articles.review_selected');
    Route::get('/revisor/articles/{article}', [RevisorController::class, 'show'])->name('revisor.article.show');
    Route::patch('/revisor/articles/{article}/reopen', [RevisorController::class, 'reopen'])
        ->name('revisor.article.reopen');
    Route::patch('/accept/{article}', [RevisorController::class, 'accept'])->name('accept');
    Route::patch('/reject/{article}', [RevisorController::class, 'reject'])->name('reject');
    Route::patch('/revisor/revision-actions/{revisionAction}/undo', [RevisorController::class, 'undo'])->name('revisor.undo');

});
