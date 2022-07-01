@extends('layouts.appback')

@section('content')
<div class="dashboard">

  <!-- <header class="page-header">
    <div class="container-fluid">
      <h2 class="no-margin-bottom">Resumen</h2>
    </div>
  </header> -->

  <section class="dashboard-counts no-padding-bottom">
    <div class="container-fluid">
      <div class="home_mod_tabs">
          <ul class="nav nav-tabs nav-fill">
            <li class="nav-item">
              <a class="nav-link {{ (!request()->modulo_id) ? 'active' : '' }}" href="{{ route('home') }}">TODOS</a>
            </li>
            @foreach ($data['data']['modulos'] as $modulo)
            <li class="nav-item">
              <a class="nav-link {{ (request()->modulo_id == $modulo->id) ? 'active' : '' }}" href="{{ route('home', ['modulo_id' => $modulo->id]) }}">
                <div class="">
                  <img src="{{ Storage::disk('do_spaces')->url($modulo->logo) }}" height="20" alt="{{ $modulo->etapa }}" title="{{ $modulo->etapa }}" class="">
                  {{ $modulo->etapa }}
                </div>
              </a>
            </li>
            @endforeach
          </ul>

        </div>
      </div>
  </section>

  <section class="dashboard-counts no-padding-bottom">
    <div class="container-fluid">
      <div class="row bg-white has-shadow">
        <!-- Item -->
        <div class="col-xl-3 col-sm-6">
          <div class="item d-flex align-items-center">
            <div class="icon bg-ucfp"><i class="fas fa-user-friends"></i></div>
            <div class="title"><span>Usuarios</span>
            </div>
            <div class="number"><strong>{{ $data['totales']['usuarios'] }}</strong></div>
          </div>
        </div>
        <!-- Item -->
        <div class="col-xl-3 col-sm-6">
          <div class="item d-flex align-items-center">
            <div class="icon bg-ucfp"><i class="fab fa-readme"></i></div>
            <div class="title"><span>Cursos</span>
            </div>
            <div class="number"><strong>{{ $data['totales']['cursos'] }}</strong></div>
          </div>
        </div>
        <!-- Item -->
        <div class="col-xl-3 col-sm-6">
          <div class="item d-flex align-items-center">
            <div class="icon bg-ucfp"><i class="fas fa-book-open"></i></div>
            <div class="title"><span>Temas</span>
            </div>
            <div class="number"><strong>{{ $data['totales']['temas'] }}</strong></div>
          </div>
        </div>
        <!-- Item -->
        <div class="col-xl-3 col-sm-6">
          <div class="item d-flex align-items-center">
            <div class="icon bg-ucfp"><i class="fas fa-book-reader"></i></div>
            <div class="title"><span>Temas evaluables</span>
            </div>
            <div class="number"><strong>{{ $data['totales']['temas_evaluables'] }}</strong></div>
          </div>
        </div>
      </div>
      <div class="bg-white has-shadow text-right pr-2 py-1">
        <span title="Actualizar ahora"> <a href="javascript:;" class="clear-cache"> <i class="fas fa-sync"></i></a></span>
          <small title="{{ $data['last_update']['time'] }}" class="">Última actualización {{ $data['last_update']['text'] }}</small>
      </div>

{{--       <div class="mt-4 text-right mb-0">
        </div>
 --}}

    </div>
  </section>

  <section class="dashboard-countsss no-padding-bottom">
    <div class="container-fluid mb-3 row">

      <div class="col">
            <small>NOTA: En los gráficos siguientes se están contabilizando todos los usuarios excepto los JEFES Y MONITORES.</small>
      </div>
     {{--  <div class="col text-right">

          <span title="Actualizar ahora"> <a href="javascript:;" class="clear-cache"> <i class="fas fa-sync"></i></a></span>
          <small title="" class="">Actualizar ahora</small>
      </div> --}}
    </div>

    <!-- <div class="row bg-white has-shadow">
      <div class="col-sm-10 offset-sm-1 chart ">

        <div class="mt-2 text-center mb-4">
            <h4>TOP TEMAS POPULARES</h4>
            <p>Los 10 temas más visitados</p>
        </div>

          <?php
          // $labels1 = array();
          // $data1 = array();
          // foreach ($top_cursos as $value) {
          //     $labels1[] = $value->nombre;
          //     $data1[] = $value->visitas;
          // }
          ?>
          <canvas id="myChart" width="400"></canvas>

      </div>
    </div> -->
  </section>

  <section class="dashboard-counts no-padding-top no-padding-bottom">
    <div class="container-fluid">
    <div class="row bg-white has-shadow section-graphic" id="evaluaciones-section">
      <div class="col-sm-10 offset-sm-1">
        <div class="mt-2 text-center mb-4">
            <h4>EVALUACIONES</h4>
            <p>Cantidad de evaluaciones realizadas por fecha</p>
        </div>

        <canvas id="chlinea"></canvas>

        <div class="mt-4 text-right mb-0">
          <span title="Actualizar ahora"> <a href="javascript:;" class="clear-cache"> <i class="fas fa-sync"></i></a></span>
          <small title="" class="hidden">Última actualización <span id="evaluaciones-date"></span></small>
        </div>
      </div>
    </div>
  </div>
  </section>

  <section class="dashboard-counts no-padding-bottom">
    <div class="container-fluid">
    <div class="row bg-white has-shadow section-graphic" id="visitas-section">
      <div class="col-sm-10 offset-sm-1">
        <div class="mt-2 text-center mb-4">
            <h4>VISITAS</h4>
            <p>Cantidad de visitas realizadas por fecha</p>
        </div>

        <canvas id="chlinea2"></canvas>

        <div class="mt-4 text-right mb-0">
          <span title="Actualizar ahora"> <a href="javascript:;" class="clear-cache"> <i class="fas fa-sync"></i></a></span>
          <small title="" class="hidden">Última actualización <span id="visitas-date"></span></small>
        </div>
      </div>
    </div>
  </div>
  </section>

  <section class="dashboard-counts no-padding-bottom">
    <div class="container-fluid">
    <div class="row bg-white has-shadow section-graphic" id="top-boticas-section">
      <div class="col-sm-10 offset-sm-1">
        <div class="mt-2 text-center mb-4">
            <h4>TOP 10 BOTICAS CON MÁS APROBADOS</h4>
            <p>Cantidad de usuarios aprobados por botica</p>
        </div>

        <canvas id="barsede"></canvas>

        <div class="mt-4 text-right mb-0">
          <span title="Actualizar ahora"> <a href="javascript:;" class="clear-cache"> <i class="fas fa-sync"></i></a></span>
          <small title="" class="hidden">Última actualización <span id="top-boticas-date"></span></small>
        </div>
      </div>
    </div>
  </div>
  </section>

  <!-- <section class="dashboard-counts no-padding-bottom">
    <div class="container-fluid">
    <div class="row bg-white has-shadow">
      <div class="col-md-8 offset-sm-2">
          <div class="mt-2 text-center mb-4">
              <h4>ESCUELAS POPULARES</h4>
              <p>Escuelas más visitados</p>
          </div>
          <?php
          // $labels_escpopulares = array();
          // $data_escpopulares = array();
          // foreach ($cate_populares as $value) {
          //     $labels_escpopulares[] = (isset($categorias[$value->categoria_id])) ? $categorias[$value->categoria_id] : "";
          //     $data_escpopulares[] = $value->suma;
          // }
          ?>
          <canvas id="catepopu" width="600" height="600"></canvas>

        </div>
      </div>
    </div>
    </section> -->

  <!-- <section class="dashboard-counts">
    <div class="container-fluid">
    <div class="row bg-white has-shadow">
      <div class="col-md-8 offset-sm-2">

          <div class="mt-2 text-center mb-4">
              <h4>AVANCE POR ESCUELA</h4>
              <p>Total de pruebas realizadas por escuela</p>
          </div>

          <?php
          // $labels_avanceesc = array();
          // $data_avanceesc = array();
          // foreach ($cate_avance as $value) {
          //     $labels_avanceesc[] = (isset($categorias[$value->categoria_id])) ? $categorias[$value->categoria_id] : "";
          //     $data_avanceesc[] = $value->cantprue;
          // }
          ?>
          <canvas id="cateavance" width="600" height="600"></canvas>

      </div>
    </div>
    </div>
  </section> -->


</div>
@endsection

@section('js')
<script src="{{ asset('vendor/chart.js/Chart.min.js') }}"></script>
<script type="text/javascript">
  coloresFondo = [
    'rgba(54, 162, 235, 0.6)',
    'rgba(255, 99, 132, 0.6)',
  'rgba(255, 235, 59, 0.6)',
  'rgba(153, 102, 255, 0.6)',
  'rgba(76, 175, 80, 0.6)',
  'rgba(255, 159, 64, 0.6)',
  'rgba(121, 85, 72, 0.6)',
  'rgba(96, 125, 139, 0.6)',
  'rgba(103, 58, 183, 0.6)',
  'rgba(255, 206, 86, 0.6)',
  'rgba(75, 192, 192, 0.6)',
  ];
  ColoresBorde = [
    'rgba(54, 162, 235, 1)',
    'rgba(255,99,132,1)',
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

  coloresFondo_linear2 = [
  'rgba(76, 175, 80, 0.6)',
  ];
  ColoresBorde_linear2 = [
  'rgba(76, 175, 80, 1)',
  ];

    // var ctx = document.getElementById("myChart").getContext('2d');
    // var myChart = new Chart(ctx, {
    //     type: 'horizontalBar',
    //     data: {
    //         labels: <?php //echo json_encode($labels1); ?>,
    //         datasets: [{
    //             label: 'Cantidad de Visitas',
    //             data: <?php //echo json_encode($data1); ?>,
    //             backgroundColor: coloresFondo,
    //             borderColor: ColoresBorde,
    //             borderWidth: 1
    //         }]
    //     },
    //     options: {
    //         legend: {
    //             display: false,
    //         },
    //         scales: {
    //             xAxes: [{
    //                 ticks: {
    //                     beginAtZero:true
    //                 }
    //             }]
    //         }
    //     }
    // });

    $(document).ready(function () {

        getEvaluaciones()
        getVisitasPorFecha()
        getTopBoticas()
    });

    $(document).on('click', '.clear-cache', function () {
      clearCache()
    });

    function getTopBoticas(retry_once = true)
    {
        setLoading('#top-boticas-section')

        var params = {modulo_id: "{{ request()->modulo_id }}"};

        $.ajax({
          type: 'GET',
          url: '/dashboard/get-data-for-top-boticas/',
          data: params,
          success: function (result) {
              if (result) {

                  setLastUpdate('#top-boticas-date', result.last_update.text, result.last_update.time)
                  drawBar(result.labels, result.values)
                  setLoading('#top-boticas-section', false)
              }
          },
          error: function()
          {
            if(retry_once)
            {
              getTopBoticas(false)
            }
          }
      });
    }

    function getVisitasPorFecha(retry_once = true)
    {
        setLoading('#visitas-section')

        var params = {modulo_id: "{{ request()->modulo_id }}"};

        $.ajax({
          type: 'GET',
          url: '/dashboard/get-data-for-visitas-por-fecha/',
          data: params,
          success: function (result) {
              if (result) {
                  setLastUpdate('#visitas-date', result.last_update.text, result.last_update.time)
                  drawLine("chlinea2", "Visitas", result.labels, result.values, coloresFondo_linear2, ColoresBorde_linear2)
                  setLoading('#visitas-section', false)
              }
          },
          error: function()
          {
            if(retry_once)
            {
              getVisitasPorFecha(false)
            }
          }
      });
    }

    function getEvaluaciones(retry_once = true)
    {
        setLoading('#evaluaciones-section')

        var params = {modulo_id: "{{ request()->modulo_id }}"};

        $.ajax({
          type: 'GET',
          url: '/dashboard/get-data-for-evaluaciones-por-fecha/',
          data: params,
          success: function (result) {
              if (result) {
                  setLastUpdate('#evaluaciones-date', result.last_update.text, result.last_update.time)
                  drawLine("chlinea", "Pruebas realizadas", result.labels, result.values, coloresFondo, ColoresBorde)
                  setLoading('#evaluaciones-section', false)
              }
          },
          error: function()
          {
            if(retry_once)
            {
              getEvaluaciones(false)
            }
          }
      });
    }

    function clearCache()
    {
        setLoading('#top-boticas-section')
        setLoading('#visitas-section')
        setLoading('#evaluaciones-section')

        $.ajax({
          type: 'GET',
          url: '/dashboard/clear-cache/',
          success: function (result) {
              if (result) {

                location.reload();
                  // setLoading('#evaluaciones-section', false)
              }
          },
          error: function()
          {
            setLoading('#top-boticas-section', false)
            setLoading('#visitas-section', false)
            setLoading('#evaluaciones-section', false)
          }
      });
    }

    function setLastUpdate(element_id, text, title)
    {
      $(element_id).html(text);
      $(element_id).parent().attr('title', title);

      $(element_id).parent().removeClass('hidden');
    }

    function setLoading(element_id, value = true)
    {
        var action = value ? 'addClass' : 'removeClass';

        $(element_id)[action]('loading');
    }

    function drawBar(labels, values)
    {
      var ctx = document.getElementById("barsede").getContext('2d');
      var stackedLine = new Chart(ctx,{
          type:"bar",
          data: {
              labels: labels,
              datasets: [{
                  label: '',
                  data: values,
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
                          beginAtZero:true
                      }
                  }]
              }
          }
      });
    }


    function drawLine(id, title, labels, values, background, border)
    {
      var ctx = document.getElementById(id).getContext('2d');
        var stackedLine = new Chart(ctx,{
            type:"line",
            data:{
                labels: labels,
                datasets:[{
                    label: title,
                    data: values,
                    backgroundColor: background,
                    borderColor: border,
                    fill:true,
                    lineTension:0.1
                }]
            },
            options: {
                legend: {
                    display: false,
                },
                responsive: true,
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        });
    }


    // Opciones para BAR charts con labels
    // var opciones = {
    //         responsive: true,
    //         maintainAspectRatio: true,
    //         legend: {
    //             display: false,
    //         },
    //         scales: {
    //             xAxes: [{
    //                 ticks: {
    //                     beginAtZero: true
    //                 }
    //             }],
    //             yAxes: [{
    //                 ticks: {
    //                     maxRotation: 90
    //                 }
    //             }]
    //         },
    //         hover: {
    //           animationDuration: 0
    //         },
    //         events: [],
    //         animation: {
    //           duration: 1,
    //           onComplete: function() {
    //             var chartInstance = this.chart,
    //               ctx = chartInstance.ctx;

    //             ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontSize, Chart.defaults.global.defaultFontStyle, Chart.defaults.global.defaultFontFamily);
    //             ctx.textAlign = 'center';
    //             ctx.textBaseline = 'bottom';

    //             this.data.datasets.forEach(function(dataset, i) {
    //               var meta = chartInstance.controller.getDatasetMeta(i);
    //               meta.data.forEach(function(bar, index) {
    //                 if (dataset.data[index] > 0) {
    //                   var data = dataset.data[index];
    //                   ctx.fillText(data, bar._model.x + 20, bar._model.y + 7);
    //                 }
    //               });
    //             });
    //           }
    //         },
    //         title: {
    //             display: false,
    //             text: ''
    //         },
    //     };


  //   //
  //   var dataesc = {
  //       labels: <?php //echo json_encode($labels_escpopulares); ?>,
  //       datasets: [{
  //           data: <?php //echo json_encode($data_escpopulares); ?>,
  //           backgroundColor: coloresFondo,
  //           borderColor: ColoresBorde,
  //           borderWidth: 1,
  //       }]
  //   };

  //   var ctx = document.getElementById("catepopu").getContext('2d');
  //   var stackedLine = new Chart(ctx,{
  //       type:"horizontalBar",
  //       data: dataesc,
  //       options: opciones
  //     });

  // //
  // var data_avae = {
  //         labels: <?php //echo json_encode($labels_avanceesc); ?>,
  //         datasets: [{
  //             data: <?php //echo json_encode($data_avanceesc); ?>,
  //             backgroundColor: coloresFondo,
  //             borderColor: ColoresBorde,
  //             borderWidth: 1,
  //         }]
  //     };

  // var ctx = document.getElementById("cateavance").getContext('2d');
  // var stackedLine = new Chart(ctx,{
  //       type:"horizontalBar",
  //       data:data_avae,
  //       options: opciones
  //   });

</script>
@endsection
