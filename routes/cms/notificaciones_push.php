<?php

use App\Http\Controllers\PushNotificationsFirebaseController;

Route::controller(PushNotificationsFirebaseController::class)->group(function() {

    Route::view('/', 'notificaciones_push.list')
         ->name('notificaciones_push.index');

    Route::get('/search', 'search');
    Route::get('/get-list-selects', 'getListSelects');
    Route::get('/detalle/{notificacion}', 'detalle');

});
