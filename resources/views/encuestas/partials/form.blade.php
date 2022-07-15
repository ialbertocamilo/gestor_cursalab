@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- <div class="form-group row">
    {{ Form::label('post_id', 'Tema', ['class'=>'col-sm-3 form-control-label'] ) }}
    <div class="col-sm-9">
        {{ Form::select('post_id', $post_array, null, [ 'class' => 'form-control']) }}
    </div>
</div> -->


<?php $array1 = [ 'xcurso'=> 'PARA CURSOS', 'libre'=> 'LIBRE' ]; ?>

<div class="form-group row" >
    {{ Form::label('type_id', 'Tipo', ['class'=>'col-sm-3 form-control-label'] ) }}
    <span></span>
    <div class="col-sm-9">
        {{ Form::select('type_id', $array1, null, [ 'class' => 'form-control']) }}
    </div>
</div>

<?php $arr_anonima = [ 'si'=> 'SI', 'no'=> 'NO' ]; ?>

<div class="form-group row" >
    {{ Form::label('anonima', '¿Es anónima?', ['class'=>'col-sm-3 form-control-label'] ) }}
    <span></span>
    <div class="col-sm-9">
        {{ Form::select('anonima', $arr_anonima, null, [ 'class' => 'form-control']) }}
    </div>
</div>

<div class="form-group row">
    {{ Form::label('titulo', 'Título', ['class'=>'col-sm-3 form-control-label'] ) }}
    <div class="col-sm-9">
        {{ Form::text('titulo',null, ['class' => 'form-control']) }}
    </div>
</div>

<!-- <div class="form-group row" >
    <div class="col-sm-3 form-control-label">
        {{ Form::label('nada', 'Imagen', ['class'=>'']) }}
        <p>Opcional <br>(subir para encuestas libres)</p>
    </div>
        @if (isset($encuesta))
    <div class="col-sm-3">
        <p><strong>[Imagen actual]</strong></p>
        <div class="imagen_actual mb-4">
            <img src="{{ asset($encuesta->imagen) }}" alt="">
        </div>
    </div>
        @endif
    <div class="col-sm-6">
        @if (isset($encuesta))
        <p><strong>[Cambiar imagen]</strong></p>
        @endif
        {!! Form::file('imagen', ['class' => 'form-control']) !!}
    </div>
</div> -->

<div class="form-group row">
    <div class="col-sm-3">
        <label class="form-control-label" for="imagen">Imagen</label>
        <p>Opcional <br>(subir para encuestas libres)</p>
    </div>
    <div class="col-sm-9">
        {{ Form::text('imagen',null, ['class' => 'form-control']) }}
    </div>
</div>

<!-- <div class="form-group row" style='display: none;'>
    {{ Form::label('vigencia', 'Vigencia', ['class'=>'col-sm-3 form-control-label ' ]) }}
    <div class="col-sm-9">
        {{ Form::text('vigencia',null, ['class' => 'form-control fecha']) }}
    </div>
</div> -->

<div class="form-group row">
    {{ Form::label('active', 'Estado', ['class'=>'col-sm-3 form-control-label'] ) }}
    <div class="col-sm-9">

        <div class="form-check">
          {{ Form::radio('active', '1', false, ['class'=>'form-check-input', 'id'=>'estado1']) }}
          <label class="form-check-label" for="estado1">
            Activo
          </label>
        </div>
        <div class="form-check">
            {{ Form::radio('active', '0', true, ['class'=>'form-check-input', 'id'=>'estado2']) }}
          <label class="form-check-label" for="estado2">
            Inactivo
          </label>
        </div>
    </div>
</div>

<div class="line"></div>
<div class="form-group row">
    <div class="col-sm-4 offset-sm-3">
        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
        {{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
    </div>
</div>

@section('js')
    @parent
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script>

$(".fecha").datepicker({
  dateFormat: 'yy-mm-dd',
  onSelect: function(dateText, inst) {
    $(inst).val(dateText); // Write the value in the input
  }
});

$(".fecha").on('click', function() {
  return false;
});
</script>
@endsection
