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
    {{ Form::label('name', 'Nombre', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::text('name', null, ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    {{ Form::label('lastname', 'Apellido Paterno', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::text('lastname', null, ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    {{ Form::label('surname', 'Apellido Materno', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::text('surname', null, ['class' => 'form-control']) }}
    </div>
</div>

<div class="form-group row">
    {{ Form::label('email', 'Email', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::email('email', null, ['class' => 'form-control']) }}
    </div>
</div>

<div class="form-group row">
    @if (isset($user))
        {{ Form::label('password', 'Cambiar contraseña', ['class' => 'col-sm-3 form-control-label']) }}
    @else
        {{ Form::label('password', 'Contraseña', ['class' => 'col-sm-3 form-control-label']) }}
    @endif
    <div class="col-sm-9">
        {{ Form::text('password', '', ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    {{ Form::label('document', 'N° Documento', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::text('document', null, ['class' => 'form-control']) }}
    </div>
</div>

<hr>
<h3>Lista de roles</h3>
<div class="form-group row">
    <div class="col-sm-9">
        <ul class="list-unstyled" style="height: auto;">
            @foreach ($roles as $role)
                @if ($role->name != 'super-user')
                    <li>
                        <label class="form-control-label">
                            {{ Form::checkbox('roles[]', $role->id, null) }}
                            {{ $role->title }}
                            <em>({{ $role->description ?: 'N/A' }})</em>
                        </label>
                    </li>
                @endif
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
