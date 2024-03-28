<?php

use App\Http\Controllers\Induction\OnboardingController;

Route::controller(OnboardingController::class)->group(function() {

    Route::view('/', 'onboarding.dashboard.index')->name('onboarding.dashboard');
    Route::view('/dashboard', 'onboarding.dashboard.index')->name('onboarding.dashboard.index');
    Route::get('/dashboard/search', 'search');
    Route::get('/dashboard/info', 'info');

});
