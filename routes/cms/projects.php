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
    Route::get('/{project_user_id}/download-zip-files', [ProjectUserController::class, 'downloadZipFiles']);
    Route::post('/{project_user}/update-usuario-tarea', [ProjectUserController::class, 'update']);
});

?>
