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
                                                    {{ Form::checkbox('workspacessel[' . $wk->slug . '][]', $wk->id, $wksk, ['id' => 'workspacessel[' . $wk->slug . '][]' ]) }}
                                                @endif
                                            @endforeach
                                        @else
                                            {{ Form::checkbox('workspacessel[' . $wk->slug . '][]', $wk->id, null, [ 'id' => 'workspacessel[' . $wk->slug . '][]' ]) }}
                                        @endif
                                        {{ $wk->name }}
                                        <input type="hidden" id="roles_{{ $wk->id }}"
                                               name="rolestowk[{{ $wk->slug }}][]" value=""
                                               ref="roles_{{ $wk->id }}">
                                    </label>
                                </div>

                                {{--                                <pre>{{$existe}}</pre>--}}
                                @if ($existe)
                                    <div class="col-md-6" id="div-select-{{$wk->id}}">

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
                                                               :roleselects="{{ $selected }}"/>
                                            @endif
                                        @endforeach
                                    </div>

                                @else
                                    <div class="col-md-6" id="div-select-{{$wk->id}}">

                                        <workspace-rol :workspaces="{{ $workspaces }}" :roles="{{ $roles }}"
                                                       :toworkspace="'{{ $wk->id }}'"/>
                                    </div>
                                @endif
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
                                        {{ Form::checkbox('workspacessel[' . $wk->slug . '][]', $wk->id, null, [ 'id' => 'workspacessel[' . $wk->slug . '][]' ]) }}
                                        {{ $wk->name }}
                                        <input type="hidden" id="roles_{{ $wk->id }}"
                                               name="rolestowk[{{ $wk->slug }}][]" value=""
                                               ref="roles_{{ $wk->id }}">
                                    </label>
                                </div>
                                <div class="col-md-6" id="div-select-{{$wk->id}}">
                                    <workspace-rol :workspaces="{{ $workspaces }}" :roles="{{ $roles }}"
                                                   :toworkspace="'{{ $wk->id }}'"/>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endisset
    </div>
    <div class="col-md-3">
        <div class="col-sm-9">
            {{ Form::label('estado', 'Estado', ['class'=>'form-control-label m-0'] ) }}
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
</div>

<div class="line"></div>
<div class="form-group row">
    <div class="col-sm-4 offset-sm-3">
        <button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
        {{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
    </div>
</div>

@section('js')
    <style>
        .wk-div-disabled {
            pointer-events: none;
            opacity: 0.4;
        }

        .display-none {
            display: none;
        }
    </style>
    @parent
    <script>
        function disableSelect(value, wk_id) {
            // console.log(value);
            // console.log("WK ID", wk_id);
            let div = document.getElementById(`div-select-${wk_id}`)
            // console.log(div)
            // console.log(value.checked)
            if (value.checked)
                div.classList.remove("wk-div-disabled")
            else
                div.classList.add("wk-div-disabled")

        }
    </script>

@endsection
