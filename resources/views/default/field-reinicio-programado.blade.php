@php
    $reinicio_activado = ($json_reinicios && $json_reinicios->activado)? 'checked' : '' ;
    $disabled = ($json_reinicios &&  $json_reinicios->activado)? '' : 'disabled' ;
    $css = ($json_reinicios &&  $json_reinicios->activado)? '' : 'color:#bcc2c6' ;
    $reinicio_dias = ($json_reinicios) ? $json_reinicios->reinicio_dias : 0;
    $reinicio_horas = ($json_reinicios) ? $json_reinicios->reinicio_horas : 0;
    $reinicio_minutos = ($json_reinicios) ? $json_reinicios->reinicio_minutos : 0;
@endphp
<label for="orden" class="col-sm-3 form-control-label">
    Reinicio automático
</label>
<div class="button-switch col-sm-1">
    <input onchange='change_switch_intentos(this);' {{$reinicio_activado}} name="reinicios_programado" type="checkbox" id="switch-intentos" class="switch switch-purple ml-3" />
</div>
<div class="d-flex justify-content-center col-sm-3" id="reinicio_div" style="margin-top: -40px;">
    <span style="background: lavender;border-radius: 7px;" class="pt-4 d-flex align-items-center flex-column mx-2">
        <input {{$disabled}} value="{{$reinicio_dias}}" name="reinicio_dias" required class="input_contador mx-4" type="number" min="0" max="7">
        <label style="{{$css}}" class="label_reinicio">Días</label>
    </span>
    <span style="background: lavender;border-radius: 7px;" class="pt-4 d-flex align-items-center flex-column mx-2">
        <input {{$disabled}} value="{{$reinicio_horas}}" name="reinicio_horas" required class="input_contador mx-4" type="number" min="0" max="24">
        <label style="{{$css}}" class="label_reinicio">Horas</label>
    </span>
    <span style="background: lavender;border-radius: 7px;" class="pt-4 d-flex align-items-center flex-column mx-2">
        <input {{$disabled}} value="{{$reinicio_minutos}}" name="reinicio_minutos" required class="input_contador mx-4" type="number" min="0" max="60">
        <label style="{{$css}}" class="label_reinicio">Minutos</label>
    </span>
</div>
