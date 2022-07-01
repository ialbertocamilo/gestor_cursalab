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
	{{ Form::label('name', 'Nombre', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::text('name',null, ['class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('email', 'Email', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::email('email',null, ['class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	@if(isset($user))
		{{ Form::label('password', 'Cambiar contraseña', ['class'=>'col-sm-3 form-control-label'] ) }}
	@else
		{{ Form::label('password', 'Contraseña', ['class'=>'col-sm-3 form-control-label'] ) }}
	@endif
	<div class="col-sm-9">
		{{ Form::text('password','', ['class' => 'form-control']) }}
	</div>
</div>

<hr>
<h3>Lista de roles</h3>
<div class="form-group row">
	<div class="col-sm-9">
		<ul class="list-unstyled">
			@foreach($roles as $role)
				<li>
					<label class="form-control-label">
						{{ Form::checkbox('roles[]', $role->id, null) }}
						{{ $role->name }}
						<em>({{ $role->description ?: 'N/A'}})</em>
					</label>
				</li>
			@endforeach
		</ul>
	</div>
</div>
<div class="line"></div>
<div class="form-group row">
	<div class="col-sm-4 offset-sm-3">
		<button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
		{{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
	</div>
</div>