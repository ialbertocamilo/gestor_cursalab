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
        <h3>Roles</h3>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-12">

        @isset($user)
            <div class="box_workspaces">
                @foreach ($workspaces as $wk)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-control-label">
                                        @php
                                            $existe = false;
                                            foreach ($workspaces_roles as $wksk => $wksv) {
                                                if ($wksk == $wk->slug) {
                                                    $existe = true;
                                                    break;
                                                }
                                            }
                                        @endphp
                                        @if ($existe)
                                            @foreach ($workspaces_roles as $wksk => $wksv)
                                                @if ($wksk == $wk->slug)
                                                    {{ Form::checkbox('workspacessel[' . $wk->slug . '][]', $wk->id, $wksk) }}
                                                @endif
                                            @endforeach
                                        @else
                                            {{ Form::checkbox('workspacessel[' . $wk->slug . '][]', $wk->id, null) }}
                                        @endif
                                        {{ $wk->name }}
                                        <input type="hidden" id="roles_{{ $wk->id }}"
                                            name="rolestowk[{{ $wk->slug }}][]" value=""
                                            ref="roles_{{ $wk->id }}">
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    @if ($existe)
                                        @foreach ($workspaces_roles as $wksk => $wksv)
                                            @if ($wksk == $wk->slug)
                                                @php
                                                    $selected = collect();
                                                    foreach ($wksv as $ww) {
                                                        $selected->push(
                                                            (object) [
                                                                'id' => $ww['id'],
                                                                'name' => $ww['name'],
                                                                'slug' => $ww['slug'],
                                                            ],
                                                        );
                                                    }
                                                @endphp
                                                <workspace-rol :workspaces="{{ $workspaces }}"
                                                    :roles="{{ $roles }}" :toworkspace="'{{ $wk->id }}'"
                                                    :roleselects="{{ $selected }}" />
                                            @endif
                                        @endforeach
                                    @else
                                        <workspace-rol :workspaces="{{ $workspaces }}" :roles="{{ $roles }}"
                                            :toworkspace="'{{ $wk->id }}'" />
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="box_workspaces">
                @foreach ($workspaces as $wk)
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-control-label">
                                        {{ Form::checkbox('workspacessel[' . $wk->slug . '][]', $wk->id, null) }}
                                        {{ $wk->name }}
                                        <input type="hidden" id="roles_{{ $wk->id }}"
                                            name="rolestowk[{{ $wk->slug }}][]" value=""
                                            ref="roles_{{ $wk->id }}">
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <workspace-rol :workspaces="{{ $workspaces }}" :roles="{{ $roles }}"
                                        :toworkspace="'{{ $wk->id }}'" />
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endisset
    </div>
</div>
<div class="line"></div>
<div class="form-group row">
    <div class="col-sm-4 offset-sm-3">
        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
        {{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
    </div>
</div>
