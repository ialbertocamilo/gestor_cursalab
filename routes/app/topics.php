<?php

use App\Http\Controllers\ApiRest\RestTopicController;

Route::controller(RestTopicController::class)->group(function() {

    Route::get('/{course}', 'topics');

    Route::get('/{school_id}/list-courses', 'listCoursesBySchoolV2');
    // Route::get('/{school_id}/list-courses2', 'listCoursesBySchoolV2');
    Route::prefix('v2')->group(function () {
        Route::get('/{course}', 'topicsv2');
    });


    Route::get('/topics/update-plays/{topic}', 'updateTopicPlays');
    Route::get('/topics/update-resets-count/{topic}', 'updateActivity');


    Route::get('/actividad_tema_revisado/{topic}', 'reviewTopic')->middleware('extend-session');
    Route::post('/actividad_contenido_revisado/{topic}/{media}', 'reviewTopicMedia')->middleware('extend-session');
    Route::post('/actividad_contenido_duracion/{topic}/{media}', 'reviewTopicMediaDuration');
    Route::get('/download/{topic}/{media}', 'downloadMedia');
});
