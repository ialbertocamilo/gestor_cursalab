@extends('layouts.appback')

@section('content')

  <header class="page-header">
    <div class="container-fluid">
            <div class="d-flex flex-row align-items-center">
            <div class="pr-4">
                <h2 class="no-margin-bottom">Configuración de temas compatibles</h2>
            </div>
            
        </div>
    </div>
  </header>
  
  <section class="section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
            <p style="color: #777;">
                <!-- - Selecciona los temas que son compatibles.<br> -->
                <!-- - Guarda la configuración para finalizar.<br> -->
            </p>

            <div class="resumen bg-white">
                  
                  <form class="row formu">
                    @csrf
                    <div class="col-sm-4 mb-3" >
                      <select name="mod" id="mod" class="form-control required" required>
                        <option value="">- Selecciona un Módulo -</option>
                        <!-- <option value="ALL">[TODOS]</option> -->
                        <?php foreach ($modulos as $key => $value): ?>
                              <?php $selected = (app("request")->input("mod") == $value->id) ? "selected" : "";  ?>
                              <option value="{{ $value->id }}" {{ $selected }}>{{ $value->etapa }}</option>
                        <?php endforeach ?>
                      </select>
                    </div>

                  </form>
            </div>
        </div> 
      </div>
    </div>
  </section>

  @if ( app('request')->has('mod') )
    <?php 
        $mod = app('request')->input('mod'); 
        // $compatibles = app('request')->input('compatibles'); 
    ?>
  <section class="tables section">   
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">

            <div class="table-responsive bg-white">                       
              <table class="table table-hover ">
                <thead class="bg-dark">
                  <tr>
                    <th>Tema</th>
                    <th>&nbsp;</th>
                    <th>Tema compatible</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($compatibles as $compatible)
                      <tr>
                          <td>
                              <span style="color:#777;font-size: .95em;"><span style="color: #ccc;">[{{ $compatible->curso_id }}]</span> {{  $cursos[$compatible->curso_id] }}</span><br>
                              <strong style="font-size: 1.3em;"><span style="color: #ccc;">[{{ $compatible->tema_id }}]</span> {{ $temas[$compatible->tema_id] }}</strong>
                          </td>
                          <td><i class="fas fa-exchange-alt"></i></td>
                          <td>
                              <span style="color:#777;font-size: .95em;"><span style="color: #ccc;">[{{ $compatible->curso_compa_id }}]</span> {{ $cursos[$compatible->curso_compa_id] }}</span><br>
                              <strong style="font-size: 1.3em;"><span style="color: #ccc;">[{{ $compatible->tema_compa_id }}]</span> {{ $temas[$compatible->tema_compa_id] }}</strong>
                          </td>
                      </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="pt-2 float-right">

            </div>

        </div>
      </div>
    </div>
  </section>

  @endif

@endsection

@section('js')
    @parent
    <script>
      // headers
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });

      // cambia MODULO carga GRUPO
      $('body').on('change', '#mod', function() {
        var mod = $(this).val();
        var ruta = remove_last(window.location.href);

        window.location.href = ruta+'/compatibles?mod='+mod;
        // $('#g').attr('disabled', true);
        // $.ajax({
        //    type:'POST',
        //    url: ruta+'?mod='+mod,
        //    data: { mod:mod },
        //    success:function(result){
        //       console.log(result);
        //    }
        // });

      });

      // Helpers
      function remove_last(the_url){
          var the_arr = the_url.split('/');
          the_arr.pop();
          return( the_arr.join('/') );
      }
      

    </script>

@endsection