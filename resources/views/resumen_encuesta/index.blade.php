<?php

use App\Http\Controllers\HomeController;
?>

@extends('layouts.appback')

<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script src="{{ asset('js/adm/chartjs-plugin-labels.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" defer/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script>
    coloresFondo = [
        'rgba(255, 99, 132, 0.4)',
        'rgba(54, 162, 235, 0.4)',
        'rgba(255, 235, 59, 0.4)',
        'rgba(153, 102, 255, 0.4)',
        'rgba(76, 175, 80, 0.4)',
        'rgba(255, 159, 64, 0.4)',
        'rgba(121, 85, 72, 0.4)',
        'rgba(96, 125, 139, 0.4)',
        'rgba(103, 58, 183, 0.4)',
        'rgba(255, 206, 86, 0.4)',
        'rgba(75, 192, 192, 0.4)',
    ];
    ColoresBorde = [
        'rgba(255,99,132,1)',
        'rgba(54, 162, 235, 1)',
        'rgba(255, 235, 59, 1)',
        'rgba(153, 102, 255, 1)',
        'rgba(76, 175, 80, 1)',
        'rgba(255, 159, 64, 1)',
        'rgba(121, 85, 72, 1)',
        'rgba(96, 125, 139, 1)',
        'rgba(103, 58, 183, 1)',
        'rgba(255, 206, 86, 1)',
        'rgba(75, 192, 192, 1)',
    ];
</script>

@section('content')
    @include('layouts.user-header')
    {{--    <v-app >--}}
    <section class="section-list" style="padding: 20px 0 10px 0 ;">
{{--        <v-card flat class="elevation-0">--}}
{{--            <v-card-title>--}}
{{--                Resumen de encuestas--}}
{{--                <v-spacer/>--}}
{{--            </v-card-title>--}}
{{--        </v-card>--}}
        <div class="elevation-0 v-card v-card--flat v-sheet theme--light">
            <div class="v-card__title">
                Resultados de encuestas
                <div class="spacer"></div>
            </div>
        </div>
    </section>
    {{--    </v-app>--}}


    <section class="resumen">
        <div class="container-fluid">
            <div class="row bg-white has-shadow">
                <div class="col-sm-12">

                    <form action="{{ route('resumen_encuesta.index') }}" method="GET" class="rowsss">
                        <meta name="csrf-token" content="{{ csrf_token() }}">

                        <div class="--col-sm-4 mb-1" style="display: none;">
                            <label for="t">Tipo</label>
                            <select name="t" id="t" class="form-control">
                            <?php $s1 = (app('request')->input('t') == 'epc') ? "selected" : "";  ?>
                            <?php $s2 = (app('request')->input('t') == 'eli') ? "selected" : "";  ?>
                            <!-- <option value=""> -Selecciona-</option> -->
                                <option value="epc" {{ $s1 }}>Encuesta por curso</option>
                            <!-- <option value="eli" {{ $s2 }}>Encuesta libre</option> -->
                            </select>
                        </div>
                        <!-- <div class="col-sm-8" style="display: none;">
                            </div> -->

                        <div class="row">


                            <div class="col-md-6 col-sm-12 col-xl-3 col-lg-4 mb-1 f_encuesta">
                                <label for="encuesta">Encuesta</label>
                                <select name="encuesta" id="encuesta" class="form-control">
                                    <option value="">-Selecciona-</option>
                                    @if(isset($encuestas))
                                        <?php foreach ($encuestas as $key => $value) : ?>
                                        <?php $selected = (app('request')->input('encuesta') == $value->id) ? "selected" : "";  ?>
                                        <option data-tipo="{{$value->tipo}}"
                                                value="{{ $value->id }}" {{ $selected }}>{{ $value->titulo }}</option>
                                        <?php endforeach ?>
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-6 col-sm-12 col-xl-3 col-lg-4 mb-1 f_modulo">
                                <label for="mod">Módulo</label>
                                <select name="mod" id="mod" class="form-control">
                                    <option value="ALL">-Selecciona-</option>
                                    <option value="ALL">Todas las opciones</option>
                                    <?php foreach ($modulos as $key => $value) : ?>
                                    <?php $selected = (app('request')->input('mod') == $value->id) ? "selected" : "";  ?>
                                    <option value="{{ $value->id }}" {{ $selected }}>{{ $value->etapa }}</option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                            {{--
                              <div class="col-md-6 col-sm-6 col-xl-3 col-lg-4 mb-1">
                              </div> --}}

                            <div class="col-md-6 col-sm-12 col-xl-3 col-lg-4 mb-1 f_epc">
                                <label for="p">Curso</label>
                                {{-- @if(isset($cursos))
                                      {{ Form::select('p', $cursos, $selected, ['class' => 'form-control select2', 'placeholder' => 'Cursos']) }}
                                @endif --}}
                                <select name="p" id="p" class="form-control select2-multiline">
                                    <option value="ALL">-Selecciona-</option>
                                    <option value="ALL">Todas las opciones</option>
                                    @if(isset($cursos))
                                        @foreach ($cursos as $key => $value)
                                            <?php $selected = (app('request')->input('p') == $value->id) ? "selected" : "";  ?>
                                            {{-- <option value="{{ $value->id }}" {{ $selected }}>[{{ $value->escuela }}] <br> {{ $value->nombre }}</option> --}}
                                            <option value="{{ $value->id }}" {{ $selected }}>{{$value->nombre}}
                                                ///[{{ $value->codigo_matricula }}-CUR-{{$value->id}}
                                                ] {{ $value->escuela }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 col-xl-3 col-lg-4 mb-1 f_grupo">
                                <label for="g">Grupo</label>
                                <select name="g" id="g" class="form-control select2">
                                    <option value="ALL">-Selecciona-</option>
                                    <option value="ALL">- TODOS -</option>
                                    @if(isset($grupos))

                                        @foreach ($grupos as $key => $value)
                                            <?php $selected = (app('request')->input('g') == $value->grupo) ? "selected" : "";  ?>
                                            <option
                                                value="{{ $value->grupo }}" {{ $selected }}>{{ $value->grupo_nombre ?? 'UNDEFINED' }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                        </div>

                        <div class="row">

                            <div class="col-sm-12">
                                <button type="submit" class="btn btn-primary btn_filtro float-right">Aplicar Filtros
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>



    @if(isset($ec_pgtas) && count($ec_pgtas) === 0)
        <section class="resumen">
            <div class="container-fluid">
                <div class="row bg-white has-shadow" style="display: flex; justify-content: center">
                    <h5 class="mb-0">Esta encuesta aún no genera datos.</h5>
                </div>
            </div>
        </section>
    @elseif ( app('request')->has('encuesta') || app('request')->has('mod') || app('request')->has('g') || app('request')->has('p'))
        <?php
        $encuesta = (app('request')->has('encuesta')) ? app('request')->input('encuesta') : "ALL";
        $mod = (app('request')->has('mod')) ? app('request')->input('mod') : "ALL";
        $curso_id = (app('request')->has('p')) ? app('request')->input('p') : "ALL";
        $grupo = (app('request')->has('g')) ? app('request')->input('g') : "ALL";
        //echo $encuesta."*".$mod."*".$curso_id."*".$grupo;
        ?>
        <section class="resumen">
            <div class="container-fluid">
                <div class="row bg-white has-shadow ">
                    <div class="col-sm-10 offset-sm-1 text-center tit_bloque mt-3">
                        <h2>RESUMEN </h2>
                    </div>
                    <div class="col-sm-10 offset-sm-1 mt-2 mb-3">

                        <h6><strong>Total Encuestados: <span id="tot_enctados"></span></strong></h6>
                        <div class="table-responsive mt-3">
                            <table class="table table-hover ec_tabla tabla-t1 ">
                                <thead class="bg-dark">
                                <tr>
                                    <th><strong>Aspecto Evaluado</strong></th>
                                    <th><strong>Promedio</strong></th>
                                    <th><strong>T2B</strong></th>
                                    <th><strong>MB</strong></th>
                                    <th><strong>B</strong></th>
                                    <th><strong>R</strong></th>
                                    <th><strong>M</strong></th>
                                    <th><strong>MM</strong></th>
                                </tr>
                                </thead>
                                <tbody class="">
                                <?php
                                // echo "<pre>";
                                // print_r($ec_pgtas);
                                // echo "</pre>";
                                // echo "<hr>";
                                $tot_rptas = "0";
                                foreach ($ec_pgtas as $item) {

                                // echo "<p>".$item->id."</p>";
                                // $data = HomeController::resumenCalifica($item->curso_id, $item->grupo, $item->encuesta_id, $item->id );
                                $data = HomeController::resumenCalifica($item->id, $mod, $curso_id, $grupo);
                                // dd($data);
                                $tot_rptas = $data['tot_rptas'];

                                ?>
                                <!-- <pre> -->
                                <?php //print_r($data);
                                ?>
                                <!-- </pre> -->
                                <?php
                                // echo "<hr>";

                                foreach ($data['data_rptas'] as $key => $value) { ?>
                                <tr>
                                    <td>{{ $item->titulo }}</td>
                                    <td>{{ round( ($value['suma_cal']/$tot_rptas) ,2) }}</td>
                                    <td><strong>{{ round( ($value['sum_t2b']/$tot_rptas)*100 ,2) }}%</strong></td>
                                    <td>{{ round( ($value['sum_mb']/$tot_rptas)*100 ,2) }}%</td>
                                    <td>{{ round( ($value['sum_b']/$tot_rptas)*100 ,2) }}%</td>
                                    <td>{{ round( ($value['sum_r']/$tot_rptas)*100 ,2) }}%</td>
                                    <td>{{ round( ($value['sum_m']/$tot_rptas)*100 ,2) }}%</td>
                                    <td>{{ round( ($value['sum_mm']/$tot_rptas)*100 ,2) }}%</td>
                                </tr>
                                <?php } ?>
                                <?php } ?>
                                <input id="from_tot" type="hidden" value="{{ $tot_rptas }}">
                                </tbody>
                            </table>
                        </div>

                        <div class="enc_descargas mt-5">
                            <h5>Detalle de encuestas</h5>
                            <div class="tabla">
                                <table class="table table-borderedf">
                                    <tbody>
                                    <tr>
                                        <td>Calificación</td>
                                        <td>

                                            @if ( app('request')->input('t') == 'epc' )

                                                <a href="{{ url('ver/encuestaxgp/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro" target="_blank"><span
                                                        class="icon voyager-eye"></span> Ver </a>
                                                <a href="{{ url('export/encuestaxgp/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro"><span
                                                        class="icon voyager-download"></span> Descargar </a>
                                            @endif

                                            @if ( app('request')->input('t') == 'eli' )
                                                <?php
                                                $enc_id = (app('request')->has('el')) ? app('request')->input('el') : "";
                                                ?>
                                                <a href="{{ url('ver/encuestaxgel/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro" target="_blank"><span
                                                        class="icon voyager-eye"></span> Ver </a>
                                                <a href="{{ url('export/encuestaxgel/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro"><span
                                                        class="icon voyager-download"></span> Descargar </a>
                                            @endif

                                        </td>
                                    </tr>
                                    <!-- <tr>
                      <td>Respuestas simples </td>
                      <td>
                       @if ( app('request')->input('t') == 'epc' )

                                        <a href="{{ url('ver/enc_pos_uni/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-primary" target="_blank"><span class="icon voyager-eye"></span> Ver </a>
                          <a href="{{ url('export/enc_pos_uni/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-success"><span class="icon voyager-download"></span> Descargar </a>
                        @endif

                                    @if ( app('request')->input('t') == 'eli' )
                                        <a href="{{ url('ver/enc_lib_uni/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-primary" target="_blank"><span class="icon voyager-eye"></span> Ver </a>
                          <a href="{{ url('export/enc_lib_uni/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-success"><span class="icon voyager-download"></span> Descargar </a>
                        @endif
                                        </td>
                                      </tr>
                                      <tr>
                                        <td>Respuestas múltiples </td>
                                        <td>
@if ( app('request')->input('t') == 'epc' )
                                        <a href="{{ url('ver/enc_pos_mult/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-primary" target="_blank"><span class="icon voyager-eye"></span> Ver </a>
                          <a href="{{ url('export/enc_pos_mult/'.$encuesta.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-success"><span class="icon voyager-download"></span> Descargar </a>
                        @endif

                                    @if ( app('request')->input('t') == 'eli' )

                                        <a href="{{ url('ver/enc_lib_mult/'.$encuesta.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-primary" target="_blank"><span class="icon voyager-eye"></span> Ver </a>
                          <a href="{{ url('export/enc_lib_mult/'.$encuesta.'/'.$curso_id.'/'.$grupo) }}" class="btn btn-sm btn-success"><span class="icon voyager-download"></span> Descargar </a>
                        @endif
                                        </td>
                                      </tr> -->
                                    <tr>
                                        <td>Respuestas en texto</td>
                                        <td>
                                            @if ( app('request')->input('t') == 'epc' )
                                                <a href="{{ url('ver/enc_pos_text/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro" target="_blank"><span
                                                        class="icon voyager-eye"></span> Ver </a>
                                                <a href="{{ url('export/enc_pos_text/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro"><span
                                                        class="icon voyager-download"></span> Descargar </a>
                                            @endif

                                            @if ( app('request')->input('t') == 'eli' )
                                                <a href="{{ url('ver/enc_lib_te.'/'.$curso_idxt/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro" target="_blank"><span
                                                        class="icon voyager-eye"></span> Ver </a>
                                                <a href="{{ url('export/enc_lib_text/'.$encuesta.'/'.$mod.'/'.$curso_id.'/'.$grupo) }}"
                                                   class="btn btn-sm btn-primary btn_filtro"><span
                                                        class="icon voyager-download"></span> Descargar </a>
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
        </section>

        <section class="resumen">
            <div class="container-fluid">
                <div class="row bg-white has-shadow">
                    <div class="col-sm-10 offset-sm-1 text-center tit_bloque mt-3">
                        <h2>INFORME GRÁFICO</h2>
                    </div>
                    <?php
                    $conta = 0;
                    foreach ($ec_pgtas as $item) {
                    $data = HomeController::resumenCalifica($item->id, $mod, $curso_id, $grupo);

                    $tot_rptas = $data['tot_rptas'];
                    foreach ($data['data_rptas'] as $key => $value) {
                    $conta += 1;
                    $suma_rptas = $value['sum_mb'] + $value['sum_b'] + $value['sum_r'] + $value['sum_m'] + $value['sum_mm'];
                    ?>
                    <div class="col-sm-12 mt-3 mb-3">
                        <p class="res-aspecto">Aspecto: <strong>{{ $item->titulo }}</strong></p>
                        <div class="row">
                            <div class="col-md-8 offset-md-2">
                                <div class="table-responsive mt-1">
                                    <table class="table table-hover ec_tabla tabla-t2 bg-black">
                                        <thead>
                                        <tr class="text-center">
                                            <th colspan="3"><strong>Resumen</strong></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <tr>
                                            <td>Muy Bueno</td>
                                            <td>{{ $value['sum_mb'] }}</td>
                                            <td>{{ round( ($value['sum_mb']/$tot_rptas)*100 ,2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td>Bueno</td>
                                            <td>{{ $value['sum_b'] }}</td>
                                            <td>{{ round( ($value['sum_b']/$tot_rptas)*100 ,2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td>Regular</td>
                                            <td>{{ $value['sum_r'] }}</td>
                                            <td>{{ round( ($value['sum_r']/$tot_rptas)*100 ,2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td>Malo</td>
                                            <td>{{ $value['sum_m'] }}</td>
                                            <td>{{ round( ($value['sum_m']/$tot_rptas)*100 ,2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td>Muy Malo</td>
                                            <td>{{ $value['sum_mm'] }}</td>
                                            <td>{{ round( ($value['sum_mm']/$tot_rptas)*100 ,2) }}%</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Total</strong></td>
                                            <td>{{ $suma_rptas }}</td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $data_labels = array('Muy Bueno', 'Bueno', 'Regular', 'Malo', 'Muy Malo');
                    $data_graf = array($value['sum_mb'], $value['sum_b'], $value['sum_r'], $value['sum_m'], $value['sum_mm']);
                    ?>
                    <input type="hidden" class="graficos_encuesta" id="{{$conta}}_data"
                           data_graf="{{json_encode($data_graf)}}" data_labels="{{json_encode($data_labels)}}">
                    <div class="col-md-6 mt-2">
                        {{-- MOVER JS --}}
                        <canvas id="gra{{$conta}}"></canvas>
                    </div>
                    <div class="col-md-6 mt-2 mb-5">
                        <canvas id="grabar{{ $conta }}"></canvas>
                    </div>
                    <?php } ?>

                    <?php } ?>


                </div>
            </div>
        </section>


        <?php
        $all_rptas = array();
        foreach ($ec_pgtas as $item) {
            $data = HomeController::resumenCalifica($item->id, $mod, $curso_id, $grupo);

            $all_rptas[] = ["preg" => $item->titulo, "rptas" => $data['data_rptas'], "rptas_tot" => $data['tot_rptas']];
        }

        ?>
        <!-- <div class="col-sm-12">
            <?php print_r($all_rptas); ?>
            </div> -->

        <section class="resumen">
            <div class="container-fluid">
                <div class="row bg-white has-shadow">

                    <div class="col-sm-10 offset-sm-1 text-center tit_bloque mt-4">
                        <h2>RESUMEN EJECUTIVO</h2>
                    </div>

                    <div class="col-sm-12 mt-4">
                        <h6 class="mb-1 res-aspecto"><strong>1. Calificación Promedio</strong></h6>
                    </div>

                    <div class="col-sm-8 offset-md-2 mt-3 mb-2">
                        <div class="table-responsive">
                            <table class="table table-hover ec_tabla tabla-t2 bg-sky">
                                <thead>
                                <tr>
                                    <th>Tema</th>
                                    <th>Promedio</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $data_labels = array();
                                $data_graf = array();
                                foreach ($all_rptas as $i_rpta) {
                                foreach ($i_rpta['rptas'] as $key => $value) {
                                $data_labels[] = $i_rpta['preg'];
                                $data_graf[] = round(($value['suma_cal'] / $i_rpta['rptas_tot']), 2);
                                ?>
                                <tr>
                                    <td>{{ $i_rpta['preg'] }}</td>
                                    <td class="text-center">{{ round( ($value['suma_cal']/$i_rpta['rptas_tot']) ,2) }}</td>
                                </tr>
                                <?php
                                }
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-sm-8 mb-1 offset-sm-2">
                        <canvas id="rejecutivo"></canvas>
                        <input type="hidden" id="rejecutivo_data" data_graf="{{json_encode($data_graf)}}"
                               data_labels="{{json_encode($data_labels)}}">
                    </div>

                    <div class="col-sm-12 mt-5 mb-3">
                        <h6 class="mb-1 res-aspecto"><strong>2. Las 5 opciones más votadas</strong></h6>
                    </div>

                    <div class="col-sm-8 offset-md-2 mt-3">
                        <div class="table-responsive">
                            <table class="table table-hover ec_tabla tabla-t2 bg-sky tabla_orden">
                                <thead>
                                <tr>
                                    <th>Ranking de votos</th>
                                    <th>Temas</th>
                                    <th>Calificación</th>
                                    <th>Top votos</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $arr_labels = array('sum_mb' => 'Muy Bueno', 'sum_b' => 'Bueno', 'sum_r' => 'Regular', 'sum_m' => 'Malo', 'sum_mm' => 'Muy Malo');
                                $cont = 0;
                                foreach ($all_rptas as $i_rpta) {
                                if ($cont == 5) {
                                    break;
                                }
                                foreach ($i_rpta['rptas'] as $key => $value) {
                                if ($cont == 5) {
                                    break;
                                }
                                $cont += 1;
                                // Remover datos que no se usan en esta tabla
                                unset($value['suma_cal']);
                                unset($value['sum_t2b']);
                                arsort($value);
                                $value = array_slice($value, 0, -4, true);
                                $llave = key($value);
                                ?>
                                <tr>
                                    <td class="posi text-center">&nbsp;</td>
                                    <td>{{ $i_rpta['preg'] }}</td>
                                    <td class="text-center">{{ $arr_labels[$llave] }}</td>
                                    <td class="voto text-center">{{ $value[$llave] }}</td>
                                </tr>
                                <?php
                                }
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    @endif

@endsection

@section('js')
    <style type="text/css">
        .select2 .selection {
            width: 100% !important;
        }

        .select2-container {

            width: 100% !important;
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

        .bold {
            font-weight: bold;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #f5f5f5;
            border: 1px solid #dedede;
            border-radius: 2px;
        }

        .loading:before {
            font-size: 10px;
            width: 1em;
            height: 1em;
            margin: 0;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            //Generar graficos
            let grafs = document.getElementsByClassName('graficos_encuesta');
            if (grafs.length > 0) {
                Array.from(grafs).forEach((el) => {
                    let data_graf = JSON.parse(el.getAttribute('data_graf'));
                    let data_labels = JSON.parse(el.getAttribute('data_labels'));
                    var data = {
                        labels: data_labels,
                        datasets: [{
                            data: data_graf,
                            backgroundColor: coloresFondo,
                            borderColor: ColoresBorde,
                            borderWidth: 1,
                        }]
                    };
                    let ctx = document.getElementById('gra' + el.id.replace('_data', '')).getContext('2d');
                    //GENERAR GRAFICO PIE
                    new Chart(ctx, {
                        type: "pie",
                        data: data,
                        responsive: true,
                        maintainAspectRatio: false,
                        options: {
                            plugins: {
                                labels: {
                                    render: 'percentage',
                                    fontSize: 16,
                                    precision: 2,
                                    position: 'outside',
                                    outsidePadding: 4,
                                    textMargin: 4
                                }
                            }
                        }
                    });
                    //GENERAR GRAFICO DE BARRAS
                    let ctx_2 = document.getElementById('grabar' + el.id.replace('_data', '')).getContext('2d');
                    new Chart(ctx_2, {
                        type: "bar",
                        data: {
                            labels: data_labels,
                            datasets: [{
                                data: data_graf,
                                backgroundColor: coloresFondo,
                                borderColor: ColoresBorde,
                                borderWidth: 1,
                            }]
                        },
                        options: {
                            legend: {
                                display: false,
                            },
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero: true
                                    }
                                }]
                            }
                        }
                    });
                });
                let ctx3 = document.getElementById("rejecutivo").getContext('2d');
                let rejecutivo_data = document.getElementById("rejecutivo_data");

                new Chart(ctx3, {
                    type: "horizontalBar",
                    data: {
                        labels: JSON.parse(rejecutivo_data.getAttribute('data_labels')),
                        datasets: [{
                            data: JSON.parse(rejecutivo_data.getAttribute('data_graf')),
                            backgroundColor: coloresFondo,
                            borderColor: ColoresBorde,
                            borderWidth: 1,
                        }]
                    },
                    options: {
                        legend: {
                            display: false,
                        },
                        scales: {
                            xAxes: [{
                                id: 'x-axis-0',
                                gridLines: {
                                    display: false
                                },
                                ticks: {
                                    beginAtZero: true
                                }
                            }]
                        }
                    }
                });
            }
            $('.select2').select2();

            $('.select2-multiline').select2({
                // placeholder: "Select something",
                // minimumResultsForSearch: Infinity, //removes the search box
                escapeMarkup: function (markup) {
                    return markup;
                },
                templateResult: formatDesign,
                templateSelection: formatSelection
            });

            $('.select2-multiple').select2({
                placeholder: "Principios activos"
            });

            $(".select2-selection__rendered").attr("title", "");

            function formatDesign(item) {
                var selectionText = item.text.split("///");
                var $returnString = selectionText[1] ? "<b>" + selectionText[0] + "</b> </br> <small>" + selectionText[1] + "</small>" : "<b>" + selectionText[0] + "</b>";
                return $returnString;
            };

            function formatSelection(item) {
                var selectionText = item.text.split("///");
                // var $returnString = "<b>" + selectionText[0] + "</b> " + selectionText[1];
                var $returnString = selectionText[0];
                return $returnString;
            };
        });
        $(function () {
            $('#tot_enctados').text($("#from_tot").val());

            // Valores de url
            $.urlParam = function (name) {
                var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
                return results[1] || 0;
            }
            //
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            //
            $('.btn_filtro').prop("disabled", true);
            $('#mod').prop("disabled", true);
            $('#p').prop("disabled", true);
            $('#g').prop("disabled", true);

            if ($('#mod').val() != "" && $('#mod').val() != "ALL") {
                $('#mod').prop("disabled", false);
            }
            if ($('#p').val() != "" && $('#p').val() != "ALL") {
                $('#p').prop("disabled", false);
            }
            if ($('#g').val() != "" && $('#g').val() != "ALL") {
                $('#g').prop("disabled", false);
            }

            // Cambia encuestas a modulo
            $('body').on('change', '#encuesta', function () {
                var encuesta = $(this).val();
                let tipo_encuesta = $('#encuesta option:selected').attr('data-tipo');
                var ruta = remove_last(window.location.href);
                //desabilitar botones de curso y grupo y modulo
                $('#p').find('option').not(':first').remove();
                $('#p').attr('disabled', true);
                $('#g').attr('disabled', true);
                $('#g').find('option').not(':first').remove();
                $('#mod').attr('disabled', true);
                $('#mod').find('option').not(':first').remove();
                if (tipo_encuesta === 'libre') {
                    return false;
                }
                // $("#mod").after("<span class='loading'></span>");


                var url_post = ruta + '/cambiar_encuesta_mod';
                $.ajax({
                    type: 'POST',
                    url: url_post,
                    data: {
                        encuesta: encuesta
                    },
                    success: function (data) {
                        if (data) {
                            $('#mod').append($('<option>', {
                                value: 'ALL',
                                text: 'Todas las opciones'
                            }));
                            $.each(data, function (i, item) {
                                var sel = "";
                                $('#mod').append($('<option ' + sel + '>', {
                                    value: item.id,
                                    text: item.etapa
                                }));
                            });

                            $('#mod').attr('disabled', false);
                            $('#mod + span.loading').remove();
                        }

                    }

                });

            });

            // Cambia cursos
            $('body').on('change', '#mod', function () {
                var encuesta = $("#encuesta").val();
                var mod = $(this).val();
                var ruta = remove_last(window.location.href);
                $('#p').attr('disabled', true);
                $('#p').find('option').not(':first').remove();
                // $("#p").after("<span class='loading' style='z-index: 5;position: relative;display: flex;'></span>");
                //
                $('#g').attr('disabled', true);
                $('#g').find('option').not(':first').remove();
                $.ajax({
                    type: 'POST',
                    url: ruta + '/cambia_curso',
                    data: {
                        encuesta: encuesta,
                        mod: mod
                    },
                    success: function (data) {
                        if (data) {
                            $('#p').append($('<option>', {
                                value: 'ALL',
                                text: 'Todas las opciones'
                            }));
                            $.each(data, function (i, item) {
                                var sel = "";
                                var text = item.nombre + '///' + '[' + item.codigo_matricula + '-CUR-' + item.id + '] ' + item.escuela;
                                $('#p').append($('<option ' + sel + '>', {
                                    value: item.id,
                                    text: text
                                    // text : item.nombre
                                }));
                            });

                            $('#p').attr('disabled', false);
                            // $('#p + span.loading').remove();
                        }
                    }

                });

            });


            //
            $('body').on('change', '#p', function () {
                var tipo = $("#t").val();
                valida_selects(tipo);
            });

            //
            function valida_selects(tipo) {
                var ruta = remove_last(window.location.href);
                var curso = $("#p").val();
                $('#g').attr('disabled', true);
                $('#g').find('option').not(':first').remove();
                // $("#g").after("<span class='loading' style='z-index: 5;position: relative;display: flex;'></span>");

                $.ajax({
                    type: 'POST',
                    url: ruta + '/cambia_grupo',
                    data: {
                        tipo: tipo,
                        curso: curso
                    },
                    success: function (data) {
                        if (data) {
                            $('#g').append($('<option>', {
                                value: 'ALL',
                                text: '- TODOS -'
                            }));

                            $.each(data, function (i, item) {
                                var sel = "";
                                // if(in_grupo == item.grupo){ sel = "selected"}

                                $('#g').append($('<option ' + sel + '>', {
                                    value: item.grupo,
                                    text: item.grupo_nombre
                                }));
                            });

                            $('#g').attr('disabled', false);
                            // $('#g + span.loading').remove();
                        }
                    }

                });

            }


            setTimeout(function () {
                if (window.location.href.indexOf('?g=') > 0 || window.location.href.indexOf('&g=') > 0) {
                    // console.log('params found');
                    var grupo = $.urlParam('g');
                    grupo = grupo.replace('/+/g', ' ');
                    console.log(grupo);
                    $("#g").val(grupo);
                    // $('#g option[value="GRUPO TESTING"]').prop('selected', true);

                    $('#encuesta').attr('disabled', false);
                    $('#p').attr('disabled', false);
                    $('#g').attr('disabled', false);

                }
            }, 1500);

            //

            if ($('#encuesta').val() != "") {
                $('.btn_filtro').prop("disabled", false);
            }

            $('body').on('change', '#encuesta', function () {
                var g = $(this).val();
                if (g != '') {
                    $('.btn_filtro').prop("disabled", false);
                } else {
                    $('.btn_filtro').prop("disabled", true);
                }
            });

        });


        //
        function sortTable(table, order) {
            var asc = order === 'asc',
                tbody = table.find('tbody');

            tbody.find('tr').sort(function (a, b) {
                if (asc) {
                    return $('td.voto', a).text().localeCompare($('td.voto', b).text());
                } else {
                    return $('td.voto', b).text().localeCompare($('td.voto', a).text());
                }
            }).appendTo(tbody);
        }

        sortTable($('.tabla_orden'), 'desc');

        var cont = 0;
        $('.tabla_orden tr').each(function (index, element) {
            $(this).find('.posi').text(cont);
            cont = cont + 1;
        });

        /////////////////////////////

        // Helpers
        function remove_last(the_url) {
            var the_arr = the_url.split('/');
            the_arr.pop();
            return (the_arr.join('/'));
        }
    </script>
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/css-spinning-spinners/1.1.0/load8.css"/>
