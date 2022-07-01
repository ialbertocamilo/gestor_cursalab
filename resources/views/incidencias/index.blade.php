@extends('layouts.appback')
@section('content')
@include('layouts.user-header')
<section class="section-list">
    <header class="page-header">
        <div class="container-fluid">
            <div class="d-flex flex-row align-items-center justify-content-between">
                <h5 class="no-margin-bottom">Incidencias</h5>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="row">
            <div class="alert alert-info" role="alert">
                Se considera una incidencia cuando:
                <ul class="ml-2 mt-2">
                    <li>Proceso 1: La suma de aprobados, realizados, revisados y desaprobados pasa la cantidad de temas asignados que posee el curso</li>
                    <li>Proceso 2: La suma de aprobados, realizados, revisados, desaprobados es igual a asignados y el estado es diferente de "aprobado", "enc_pend" o "desaprobado"</li>
                    <li>Proceso 3: Hay registros duplicados en la tabla resumen_x_curso</li>
                    <li>Proceso 4: Hay registros duplicados en la tabla resumen_general</li>
                    <li>Proceso 5: Hay registros duplicados en la tabla visitas</li>
                    <li>Proceso 6: Hay registros duplicados en la tabla pruebas</li>
                    <li>Proceso 7: La cantidad de asignados que tiene el usuario es diferente al que aparece en resumen_general</li>
                    <li>Proceso 8: El usuario tiene cursos asignados de otros módulos</li>
                    <li>Proceso 9: Tiene un registro de resumen_x_curso de un curso que no tiene asignado</li>
                    <li>Proceso 10: El usuario tiene 100% pero la cantidad de asignados es diferente al total completado en la tabla resumen_general</li>
                    <li>Proceso 11: El usuario tiene una nota promedio pero no tiene un registro en la tabla resumen_x_curso</li>
                    <li>Proceso 12: Hay usuarios sin registro en la tabla resumen_general</li>
                    <li>Proceso 13: Si la prueba no tiene visitas</li>
                    <li>Proceso 14: La escuela tiene cursos inactivos</li>
                    <li>Proceso 15: Hay cursos activos en escuelas inactivas</li>
                    <li>Proceso 16: El reinicio por tema no tiene curso_id</li>
                    <li>Proceso 17: El reinicio no tiene admin_i</li>
                    <li>Proceso 18: La visita tiene el post_id o curso_id vacio</li>
                    <li>Proceso 19: Hay pruebas(sin reseto) sin intentos y con nota</li>
                    <li>Proceso 20: Hay aprobados con nota desaprobatoria</li>
                    <li>Proceso 21: Hay desaprobados con nota aprobatoria</li>
                    <li>Proceso 22: Hay visitas con sumatoria 0 o null y el campo estado_tema tiene valor</li>
                    <li>Proceso 23: Las visitas de resumen_x_curso no coincide con la tabla visitas </li>
                </ul>
            </div>
        </div>
        <div class="row">
            @if(!$disabled)
            <div class="d-flex justify-content-center align-items-center w-100">
                <div id="visbile" style="display:none">
                    @php
                    $numeros_de_procesos=['1','2','3','4','5','6','7_8_9_10_11_12',
                    '13','14','15','16','17','18',
                    '19','20','21','22','23'];
                    @endphp
                    {!! Form::open(['route' => ['incidencias.ejecutar'], 'method' => 'POST']) !!}
                    <div>
                        <select class="form-control select2-multiple" multiple="true" name="procesos[]">
                            @foreach ($numeros_de_procesos as $pr)
                            <option value="{{ $pr }}">
                                {{ $pr }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary mb-4">
                        Ejecutar comando
                    </button>
                    {!! Form::close() !!}
                </div>
                <button id="btn_hover" class="btn_activar"></button>
            </div>
            @else
            <div class="mb-4" style="color:red;font-weight:bold">
                El comando esta siendo ejecutado - proceso({{$ejecutando->total}}/23).
            </div>
            @endif
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Tablas involucradas</th>
                        <th scope="col">Mensaje</th>
                        <th scope="col">Cantidad de usuarios afectados</th>
                        <th scope="col">Fecha de comando</th>
                        <th scope="col">Opción</th>
                    </tr>
                </thead>
                @php
                $index = 1;
                @endphp
                @foreach ($incidencias_x_comando as $key => $incidencia_x_comando)
                <tbody style="border-bottom: 2px solid red;">
                    @foreach ($incidencia_x_comando as $incidencia)
                    <tr>
                        <th scope="row">{{$index}}</th>
                        <td>{{$incidencia->tipo}}</td>
                        <td>{{$incidencia->mensaje}}</td>
                        <td>{{$incidencia->total}}</td>
                        <td>{{$incidencia->created_at}}</td>
                        <td>
                            {!! Form::open(['route' => ['incidencias.destroy', $incidencia->id ], 'method' => 'DELETE']) !!}
                            <button class="btn btn-sm bg-red btndelete mt-1"><i class="far fa-trash-alt"></i></button>
                            {!! Form::close() !!}
                        </td>
                    </tr>
                    @php
                    $index++;
                    @endphp
                    @endforeach
                </tbody>
                @endforeach
            </table>
        </div>
    </div>
</section>
@endsection
@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script>
    $('#btn_hover').click(function() {
        console.log("entra");
        $("div#visbile:hidden").show();
    })
</script>
<style>
    .btn_activar {
        width: 80px;
        height: 20px;
        background: #eef5f9;
        border: 0px solid #eef5f9;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />
<style type="text/css">
    .select2 .selection {
        width: 100%;
    }

    .select2-container .select2-selection--single {
        height: calc(2.25rem + 2px);
        border: 1px solid #dee2e6;
        border-radius: 0;
        padding: 0.375rem 0.75rem;
    }

    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border: solid #dee2e6 1px;
        outline: 0;
    }

    .select2-container--default .select2-selection--multiple {
        background-color: white;
        border: 1px solid #dee2e6;
        border-radius: 0px;
        cursor: text;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.select2').select2();
        $('.select2-multiple').select2({
            placeholder: "procesos a ejecutar"
        });
    });
</script>