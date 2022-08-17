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
    {{ Form::label('title', 'Nombre', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::text('title', null, ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    {{ Form::label('name', 'URL amigable', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::text('name', null, ['class' => 'form-control']) }}
    </div>
</div>
<div class="form-group row">
    {{ Form::label('description', 'Descripción', ['class' => 'col-sm-3 form-control-label']) }}
    <div class="col-sm-9">
        {{ Form::text('description', null, ['class' => 'form-control']) }}
    </div>
</div>
<hr>
{{-- <h3>Permiso Especial</h3>
<div class="form-group row">
    <div class="col-sm-9">
        <label class=" form-control-label">{{ Form::radio('special', 'all-access') }} Acceso total</label>
        <label class=" form-control-label">{{ Form::radio('special', 'no-access') }} Ningun acceso</label>
        <label class=" form-control-label">{{ Form::radio('special', '') }} Sin permiso especial</label>
    </div>
</div> --}}
{{-- <h3>Lista de permisos</h3>
<div class="form-group row">
    <div class="col-sm-9">
        <ul class="list-unstyled" style="height: auto;">
            @foreach ($permissions as $permission)
                <li>
                    <label class="form-control-label">
                        {{ Form::checkbox('permissions[]', $permission->id, null) }}
                        {{ $permission->name }}
                        <em>({{ $permission->description ?: 'N/A' }})</em>
                    </label>
                </li>
            @endforeach
        </ul>
    </div>
</div> --}}

<div class="line"></div>
<div class="form-group row">
    <div class="col-sm-4 offset-sm-3">
        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
        {{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
    </div>
</div>
