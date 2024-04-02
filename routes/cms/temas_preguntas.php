<?php
use App\Http\Controllers\TemaController;

Route::controller(TemaController::class)->group(function() {

	// Route::view('/', 'temas.preguntas_list')->name('temas.preguntas_list');
	Route::get('/', 'preguntas_list')->name('temas.preguntas_list');
	// ->middleware('permission:abconfigs.list');
	Route::get('/download', 'downloadQuestions')->name('temas.downloadQuestions');
	Route::get('/search', 'search_preguntas')->name('temas.search');
	Route::get('/{pregunta}', 'showPregunta')->name('temas.showPregunta');
	Route::post('/store', 'storePregunta')->name('temas.storePregunta');
	Route::post('/store-ai-question', 'storeAIQuestion')->name('temas.storeAIQuestion');

	Route::post('/import', 'importPreguntas')->name('temas.importPreguntas');
	Route::delete('/{pregunta}', 'deletePregunta')->name('temas.deletePregunta');
});
