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
<div class="row mb-3">
    <div class="col-md-6">
        <h3>Workspaces</h3>
    </div>
    <div class="col-md-6">
        <h3>Roles</h3>
    </div>
</div>
<div class="box_workspaces" id="box_workspaces">
    @isset($user)
        @if (count($user->roles) > 0)
            @foreach ($user->roles as $rol)
                <div class="row mb-3">
                    <div class="col-md-6">
                        {!! Form::select('workspaces[]', $workspaces, $rol->scope, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-6">
                        {!! Form::select('roles[]', $roles, $rol->id, ['class' => 'form-control']) !!}
                    </div>
                </div>
            @endforeach
        @else
            <div class="row mb-3">
                <div class="col-md-6">
                    {!! Form::select('workspaces[]', $workspaces, null, ['class' => 'form-control']) !!}
                </div>
                <div class="col-md-6">
                    {!! Form::select('roles[]', $roles, null, ['class' => 'form-control']) !!}
                </div>
            </div>
        @endif
    @else
        <div class="row mb-3">
            <div class="col-md-6">
                {!! Form::select('workspaces[]', $workspaces, null, ['class' => 'form-control']) !!}
            </div>
            <div class="col-md-6">
                {!! Form::select('roles[]', $roles, null, ['class' => 'form-control']) !!}
            </div>
        </div>
    @endisset
</div>
<div class="line"></div>
<div class="form-group row">
    <div class="col-sm-4 offset-sm-3">
        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
        {{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
    </div>
</div>
