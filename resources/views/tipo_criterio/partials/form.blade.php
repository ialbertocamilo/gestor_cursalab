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
		{{ Form::text('nombre', isset($tipoCriterio) ? $tipoCriterio->nombre : null, ['required' => 'required','class' => 'form-control input_name']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('nombre_plural', 'Nombre plural', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::text('nombre_plural',isset($tipoCriterio) ? $tipoCriterio->nombre_plural : null, ['required' => 'required','class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('data_type', 'Tipo', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{!! Form::select('data_type', ['Texto' => 'Texto', 'Numérico' => 'Numérico', 'Fecha' => 'Fecha'], isset($tipoCriterio)  ? $tipoCriterio->data_type: 'Text', ['class' => 'form-control select_change']) !!}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('obligatorio', 'Obligatorio', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::checkbox('obligatorio', 1, isset($tipoCriterio) ? $tipoCriterio->obligatorio : true, ['class' => 'js-switch form-control check_menu', 'data-color' => '#796AEE']) }}
	</div>
</div>

<div class="form-group row ">
	<div class="col-sm-6">
		@include('default.field-orden')
	</div>
</div>

{{-- <div class="form-group row">
	{{ Form::label('estado', 'Estado', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
        {{ Form::checkbox('estado', true, isset($tipoCriterio->estado) ? $tipoCriterio->estado : true, ['class' => 'js-switch form-control', 'data-color' => '#796AEE']) }}
	</div>
</div> --}}

<div class="line"></div>
<div class="form-group row">
	<div class="col-sm-4 offset-sm-3">
		<button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
		{{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
	</div>
</div>
