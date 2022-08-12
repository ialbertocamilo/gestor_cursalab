<?php

use App\Http\Controllers\GeneralController;


Route::get('/modulos', [GeneralController::class, 'getModulos']);
Route::get('/cards-info', [GeneralController::class, 'getCardsInfo']);
Route::get('/evaluaciones-por-fecha', [GeneralController::class, 'getEvaluacionesPorfecha']);
Route::get('/visitas-por-fecha', [GeneralController::class, 'loadVisitsByDate']);
Route::get('/top-boticas', [GeneralController::class, 'loadTopBoticas']);

