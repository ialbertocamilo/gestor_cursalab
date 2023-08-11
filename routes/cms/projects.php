<?php

use App\Http\Controllers\ProjectController;

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
    // Route::view('/{tarea}/usuarios', 'tareas.usuarios_list')->name('tareas_usuarios.list');
    // Route::get('/status-list/{type}', [UsuarioTareaController::class, 'listStatus']);
    // Route::get('/{tarea}/usuarios/search', [UsuarioTareaController::class, 'search']);
    // Route::get('/{usuario_tarea_id}/download-zip-files', [UsuarioTareaController::class, 'downloadZipFiles']);
    // Route::post('/{usuario_tarea}/update-usuario-tarea', [UsuarioTareaController::class, 'update']);
});

?>
