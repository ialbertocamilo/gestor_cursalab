<?php

use \App\Http\Controllers\PushNotificationsFirebaseController;
Route::controller(PushNotificationsFirebaseController::class)->group(function () {

    // NOTIFICACIONES PUSH PERSONALIZADAS DESDE EL GESTOR //
    Route::get('/', 'index');
    // ->middleware('permission:/.index')->name('/.index');
    Route::get('/getData', 'getData');
    Route::post('/enviarNotificacionCustom', 'enviarNotificacionCustom');
});
?>