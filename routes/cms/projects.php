<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectUserController;

Route::controller(ProjectController::class)->group(function() {

    Route::view('/', 'projects.list')->name('projects.list');
    Route::get('/search', 'searchProject');
    Route::get('/get-selects', 'getListSelects');
    //Tareas
    Route::post('/store','store');
    Route::get('/{project}/edit', 'editProject');
    Route::post('/{project}/update','update');
    Route::put('/{project}/status', 'changeStatus');
    Route::delete('/{project}/destroy','deleteProject');

    // //Usuario Tareas
    Route::view('/{tarea}/usuarios', 'projects.users')->name('project_users.list');
    Route::get('/users/status-list/{type}', [ProjectUserController::class, 'listStatus']);
    Route::get('/{project}/users/search', [ProjectUserController::class, 'search']);
    // Route::get('/{usuario_tarea_id}/download-zip-files', [UsuarioTareaController::class, 'downloadZipFiles']);
    // Route::post('/{usuario_tarea}/update-usuario-tarea', [UsuarioTareaController::class, 'update']);
});

?>
