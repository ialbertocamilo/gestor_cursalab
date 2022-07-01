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
	{{ Form::label('config_id', 'Módulo', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::select('config_id', $config_array, session('mod')['id'], [ 'class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('perfil_id', 'Perfil', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::select('perfil_id', $perfil_array, null, [ 'class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	<div class="col-sm-3">
		<label class="form-control-label" for="image">Archivo</label>
		<p>(Menor a 50Mb)</p>
	</div>
	@if (isset($malla))
		<div class="col-sm-3">
				<p><strong>[Archivo actual]</strong></p>
				<div class="archivo_actual mb-4">
					<a href="{{ asset($malla->archivo) }}" target="_blank">Ver</a>
				</div>
		</div>
	@endif
	<div class="col-sm-6">
		@if (isset($malla))
		<p><strong>[Cambiar archivo]</strong></p>
		@endif
		{!! Form::file('archivo', ['class' => 'form-control']) !!}
	</div>
</div>

<div class="line"></div>
<div class="form-group row">
	<div class="col-sm-4 offset-sm-3">
		<button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
		{{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
	</div>
</div>