<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupervisorController;

Route::controller(SupervisorController::class)->group(function () {

//	Route::view('/', 'reportes_supervisores.list')->name('supervisores');
//
//	Route::get('/search', 'search');
//	Route::get('/get-list-selects', 'getListSelects');
//	Route::get('/form-selects', 'getListSelects');
//	Route::post('/get-usuarios', 'getUsuarios');
//
//	Route::post('/store-supervisor', 'storeSupervisor');
//	Route::post('/delete-supervisor', 'deleteSupervisor');
//
//	Route::get('/get-areas/{modulo}', 'getAreas');

    Route::view('/', 'supervisores.list')->name('supervisores.list');

    Route::get('/search', 'search');
    Route::post('/search-supervisores', 'searchSupervisores');

    Route::get('/get-data/{supervisor}/{type}', 'getData');
    Route::get('/tipo-criterios', 'tipoCriterios');
    Route::get('/modulos', 'modulos');

    Route::post('/search-usuarios', 'searchUsuarios');
    Route::post('/subir-excel-usuarios', 'subirExcelUsuarios');
    Route::post('/set-usuarios-as-supervisor', 'setUsuariosAsSupervisor');
    Route::post('/set-data-supervisor', 'setDataSupervisor');
    Route::post('/set-criterio-globales-supervisor', 'setCriterioGlobalesSupervisor');

    Route::delete('{supervisor}/destroy', 'destroy');

    Route::post('/search-instructors', 'searchInstructors');

});
