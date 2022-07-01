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
	{{ Form::label('pregunta_id', 'Pregunta', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::select('pregunta_id', $pregunta_array, null, [ 'class' => 'form-control']) }}
	</div>
</div>


<div class="form-group row">
	{{ Form::label('usuario_id', 'usuario', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::select('usuario_id', $user_array, null, [ 'class' => 'form-control']) }}
	</div>
</div>


<div class="form-group row">
	{{ Form::label('respuestas', 'Respuestas', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::text('respuestas',null, ['class' => 'form-control']) }}
	</div>
</div>


<div class="form-group row">
	{{ Form::label('tipo_respuesta', 'Tipo de respuesta', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::text('tipo_pregunta',null, ['class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('created_at', 'Fecha de inicio de la encuesta', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::text('created_at',null, ['class' => 'form-control fecha']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('updated_at', 'Actualizacion de la encuesta', ['class'=>'col-sm-3 form-control-label ' ]) }}
	<div class="col-sm-9">
		{{ Form::text('updated_at',null, ['class' => 'form-control fecha']) }}
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