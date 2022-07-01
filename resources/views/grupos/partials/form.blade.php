@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group row">
	{{ Form::label('nombre', 'Nombre', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::text('nombre',null, ['class' => 'form-control']) }}
	</div>
</div>

<!-- <div class="form-group row">
	{{ Form::label('fecha_inicio', 'Fecha inicio', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::text('fecha_inicio',null, ['class' => 'form-control fecha']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('fecha_fin', 'Fecha fin', ['class'=>'col-sm-3 form-control-label ' ]) }}
	<div class="col-sm-9">
		{{ Form::text('fecha_fin',null, ['class' => 'form-control fecha']) }}
	</div>
</div> -->

<div class="form-group row">
  {{ Form::label('estado', 'Estado', ['class'=>'col-sm-3 form-control-label'] ) }}
  <div class="col-sm-9">

    <div class="form-check">
      {{ Form::radio('estado', '1', false, ['class'=>'form-check-input', 'id'=>'estado1']) }}
      <label class="form-check-label" for="estado1">
        Activo
      </label>
    </div>
    <div class="form-check">
      {{ Form::radio('estado', '0', true, ['class'=>'form-check-input', 'id'=>'estado2']) }}
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