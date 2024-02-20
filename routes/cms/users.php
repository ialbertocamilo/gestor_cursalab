<?php

use App\Http\Controllers\UserController;

Route::controller(UserController::class)->group(function () {

    // Route::get('/', 'index')->name('users.index');
    Route::view('/', 'users.list')->name('users.list');
    Route::get('/search', 'search')->name('users.search');
    Route::get('/get-form-data', 'getFormData');

    Route::put('/update-profile', 'updateProfile');
    Route::put('/update-phones', 'updatePhones');
    Route::put('/update-password', 'updatePassword');

    Route::get('/create', 'create')->name('users.create');
    Route::post('/store', 'store')->name('users.store');

    Route::get('/{user}/edit', 'edit')->name('users.edit');
    Route::put('/{user}/update', 'update')->name('users.update');
    Route::get('/{user}/show', 'show')->name('users.show');
    // Route::get('/{document}/current-courses', 'currentCourses');

    
    Route::put('/{user}/status', 'status')->name('users.status');
    Route::delete('/{user}/destroy', 'destroy')->name('users.destroy');

    Route::post('/list-users-by-criteria', 'listUsersByCriteria');
});
