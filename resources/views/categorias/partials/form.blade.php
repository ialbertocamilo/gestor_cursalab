@if ($errors->any())
<div class="alert alert-danger">
	<ul>
		@foreach ($errors->all() as $error)
		<li>{{ $error }}</li>
		@endforeach
	</ul>
</div>
@endif

<?php 
	$display = 'display: none;'; 
?>
@can('ver.ocultos')
<?php $display = 'display: flex;'; ?>
@endcan

<div class="form-group row" style="{{ $display }} background: #fbe6c0;">
	{{ Form::label('config_id', 'Configuración', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::select('config_id', $config_array, null, [ 'class' => 'form-control']) }}
	</div>
</div>

<?php 
	$array = config( 'constantes.modalidad' );
?>
<div class="form-group row">
	{{ Form::label('modalidad', 'Modalidad', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::select('modalidad', $array, null, [ 'class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('nombre', 'Nombre', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::text('nombre',null, ['class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('nombre_ciclo_0', 'Nombre Ciclo 0', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::text('nombre_ciclo_0',null, ['class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('descripcion', 'Descripción', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::text('descripcion',null, ['class' => 'form-control']) }}
	</div>
</div>

<div class="form-group row">
	{{ Form::label('color', 'Color', ['class'=>'col-sm-3 form-control-label']) }}
	<div class="col-sm-9">
		{{ Form::text('color',null, ['class' => 'form-control']) }}
	</div>
</div>

{{-- <div class="form-group row">
	<div class="col-sm-3">
		<label class="form-control-label" for="image">Imagen</label>
		<p>(500x350 píxeles)</p>
	</div>
	<div class="col-sm-9">
		{{ Form::text('imagen',null, ['class' => 'form-control']) }}
	</div>
</div> --}}
<div class="form-group row">
	<div class="col-sm-3">
		<label class="form-control-label" for="image">Imagen</label>
		<p>(Medida: 500x350 píxeles)</p>
	</div>

	<div class="col-sm-3 mb-4  d-flex flex-column align-items-center">
		<?php $imagen = (isset($categoria)) ? Storage::disk('do_spaces')->url('images/'.$categoria->imagen) : "";?>
		<?php $imagen_text = (isset($categoria)) ? 'images/'.$categoria->imagen : "";?>
		<div class="imagen_actual m_imagen text-center">
			<img src="{{ $imagen }}" alt="">
		</div>
		{{ Form::text('imagen',$imagen_text, ['class' => 'form-control', 'id' => 'm_imagen', 'readonly'=>'true']) }}
	</div>
	<div class="col-sm-3 d-flex align-items-center">
		<div class="form-control btn_select_media" data-field="m_imagen" data-tipo="image" data-toggle="modal" data-target="#AsignMediaModal">
			<i class="fa fa-plus"></i> Seleccionar
		</div>
	</div>
</div>
<!-- <div class="form-group row">
	<div class="col-sm-3">
		<label class="form-control-label" for="image">Imagen</label>
		<p>(Medida: 500x350 píxeles)</p>
	</div>
	
	@if (isset($categoria))
		<div class="col-sm-3">
				<p><strong>[Imagen actual]</strong></p>
				<div class="imagen_actual mb-4">
					<img src="{{ asset($categoria->imagen) }}" alt="">
				</div>
		</div>
	@endif
	<div class="col-sm-6">
		@if (isset($categoria))
		<p><strong>[Cambiar imagen]</strong></p>
		@endif
		{!! Form::file('imagen', ['class' => 'form-control']) !!}
	</div>
</div> -->

<div class="form-group row">
	{{ Form::label('orden', 'Orden', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::number('orden',null, ['class' => 'form-control']) }}
	</div>
</div>
<div class="mt-5 form-group row" style="margin-top: 60px !important;">
	@php
		$reinicios_programado = isset($categoria) && !is_null($categoria->reinicios_programado) ? true : false ;
		$json_reinicios = ($reinicios_programado) ? json_decode($categoria->reinicios_programado) : false ;
	@endphp
	@include('../../default/field-reinicio-programado')
</div>
<div class="form-group row">
	{{ Form::label('estado', 'Estado', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">

		<div class="form-check">
			{{ Form::radio('estado', '1', false, ['class'=>'form-check-input', 'id'=>'estado1']) }}
			<label class="form-check-label" for="estado1">
				Activo
			</label>
		</div>
		<div class="form-check">
			{{ Form::radio('estado', '0', true, ['class'=>'form-check-input', 'id'=>'estado2']) }}
			<label class="form-check-label" for="estado2">
				Inactivo
			</label>
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script>
	function change_switch_intentos(checkbox){
		if(checkbox.checked == true){
			$('#reinicio_div').fadeIn();
			$('.label_reinicio').css("color","black");
			$('input[name="reinicio_dias"]').removeAttr("disabled");
			$('input[name="reinicio_horas"]').removeAttr("disabled");
			$('input[name="reinicio_minutos"]').removeAttr("disabled");
		}else{
			$('#reinicio_div').fadeOut();
			$('.label_reinicio').css("color","#bcc2c6");
			$('input[name="reinicio_dias"]').attr("disabled", "disabled");
			$('input[name="reinicio_horas"]').attr("disabled", "disabled");
			$('input[name="reinicio_minutos"]').attr("disabled", "disabled");
		}
	}
</script>