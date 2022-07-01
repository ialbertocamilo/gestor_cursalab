<?php

use App\Http\Controllers\Controller;


Route::put('/change_order', [Controller::class, 'changeOrder']);

Route::get('get-modulos', [Controller::class, 'getModulos']);

Route::get('get-carreras-by-modulo/{modulo}', [Controller::class, 'getCarrerasByModulo']);

Route::get('get-escuelas-by-modulo/{modulo}', [Controller::class, 'getEscuelasByModulo']);

Route::get('get-ciclos-by-carrera/{carrera}', [Controller::class, 'getCicloByCarrera']);
