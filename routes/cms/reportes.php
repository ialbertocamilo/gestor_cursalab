<?php

use App\Http\Controllers\ExportarConferenciasController;
use App\Http\Controllers\CurriculasGruposController;
use App\Http\Controllers\ExportarController;
use App\Http\Controllers\ReportsController;

Route::get('reportes/{layout}', [
    ExportarController::class,
    'indexReport',
])->name('exportar.reportes');

Route::get('/reports/queue', [ReportsController::class, 'loadReportsQueue']);
Route::get('/reports/types', [ReportsController::class, 'loadRepotsTypes']);
Route::get('/reports/save/{id}', [ReportsController::class, 'saveAudits']);

Route::prefix('exportar')
    ->controller(ExportarController::class)
    ->group(function () {
        Route::get('/index', 'index')->name('exportar.index');
        // ->middleware('permission:exportar.index');

        Route::post('/usuarios_dw', 'exportUsuariosDW')->name(
            'exportar.usuarios_dw'
        );
        Route::post('/reporte_total_dw', 'exportReporteTotalDW')->name(
            'exportar.reporte_total_dw'
        );
        Route::post('/reporte_x_curso_dw', 'exportReportexCursoDW')->name(
            'exportar.reporte_x_curso_dw'
        );
        Route::post('/reinicios_dw', 'reiniciosDW')->name(
            'exportar.reinicios_dw'
        );
        Route::post(
            '/reporte_evabiertas_dw',
            'exportReporteEvAbiertasDW'
        )->name('exportar.reporte_evabiertas_dw');
        Route::post('/visitas', 'exportVisitas')->name('exportar.visitas');
        Route::post('/versiones', 'exportVersiones')->name(
            'exportar.versiones'
        );
        //
        Route::post('/cambia_modulo_carga_grupo', 'cambia_modulo_carga_grupo');
        Route::post(
            '/cambia_modulo_carga_carrera',
            'cambia_modulo_carga_carrera'
        );
        Route::post(
            '/cambia_carrera_carga_grupo',
            'cambia_carrera_carga_grupo'
        );
        Route::post('/cambia_grupo_carga_perfil', 'cambia_grupo_carga_perfil');
        Route::post(
            '/cambia_modulo_carga_escuela',
            'cambia_modulo_carga_escuela'
        );
        Route::post(
            '/cambia_escuela_carga_curso',
            'cambia_escuela_carga_curso'
        );
        Route::post('/cambia_curso_carga_tema', 'cambia_curso_carga_tema');

        Route::post(
            '/cambia_modulo_carga_escuela_ev_ab',
            'cambia_modulo_carga_escuela_ev_ab'
        );
        Route::post(
            '/cambia_escuela_carga_curso_ev_ab',
            'cambia_escuela_carga_curso_ev_ab'
        );
        Route::post(
            '/cambia_curso_carga_tema_abiertas',
            'cambia_curso_carga_tema_abiertas'
        );

        Route::get('/get_user_data', 'get_user_data')->name(
            'exportar.get_user_data'
        );
        Route::post('/usuario_notas_x_curso', 'usuario_notas_x_curso')->name(
            'exportar.usuario_notas_x_curso'
        );

        Route::get('/filtro_exportar', 'filtro_exportar')->name(
            'exportar.filtro_exportar'
        );
        // ->middleware('permission:exportar.index');
        Route::get('/reporte_filtrado', 'exportReporteFiltrado')->name(
            'exportar.reporte_filtrado'
        );

        //****************** MEETINGS *****************/
        Route::get('/meetings_types', 'meetings_types');

        //****************** NODE *****************/
        // Reportes Vue Node
        Route::get('/obtenerdatos', 'obtenerDatos')->name(
            'exportar.obtenerdatos'
        );
        Route::get('/node', 'index2')->name('exportar.node');
    });

Route::prefix('exportar')->group(function () {
    Route::get('conferencias', [
        ExportarConferenciasController::class,
        'index',
    ])->name('exportar_conferencias.index');
    //PRUEBAS MANTENIMIENTO
    // Route::get('restablecer_tablas_resumen', 'ApiRest\HelperController@set_data_update_resumenes'); //ya fue usado

    // EXPORTAR CURRICULAS EN EXCEL
    Route::get('/curricula_excel', [
        CurriculasGruposController::class,
        'exportarCurriculasExcel',
    ]);
});
