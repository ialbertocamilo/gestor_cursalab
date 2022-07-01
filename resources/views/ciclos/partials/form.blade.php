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


<div class="form-group row">
	{{ Form::label('nombre', 'Nombre', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::text('nombre',null, ['class' => 'form-control']) }}
	</div>
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

<div class="form-group row">
	<div class="col-sm-3">
		<label class="form-control-label" for="archiv">Secuencia</label>
		<p>(Orden secuencial de los ciclos)</p>
	</div>
	<div class="col-sm-9">
		{{ Form::text('secuencia',null, ['class' => 'form-control']) }}
	</div>
</div>

<div class="line"></div>

@if (isset($ciclo))
<div class="form-group row">
	<div class="col-sm-3 form-control-label">
		<h5>Ciclos compatibles</h5>
	</div>
	<div class="col-sm-9">
		<?php 
		$c_compas = [];
		if (isset($ciclo)) {
			$c_compas = json_decode(json_encode($ciclo->compatibles->pluck('ciclo_id_2')), true);
		}
		 ?>
		<div class=" carre row">
			@foreach($carreras as $carrera)
				<div class="col-sm-3 mb-2">
					<div class="ca-item" >
						<h5 class="mb-0 d-flex justify-content-between" >{{ $carrera->nombre }} </h5>
					</div>

					<div  class=" ca-conte" aria-labelledby="headingOne" >
						<?php 
							$ciclo_activo_id = "";
							$car_ciclos = json_decode(json_encode($carrera->ciclos->pluck('nombre', 'id')), true);
						?>

						<select name="ciclos[]" id="m" class="form-control">
                          <option value="">- Ninguno -</option>
                          <?php foreach ($car_ciclos as $key => $value): ?>
                                <?php $selected = (in_array($key, $c_compas)) ? "selected" : "";  ?>
                                <option value="{{ $key }}" {{ $selected }}>{{ $value }}</option>
                          <?php endforeach ?>
                        </select>
					</div>
				</div>
			@endforeach
		</div>
	</div>
</div>
@endif

<div class="line"></div>
<div class="form-group row">
	<div class="col-sm-4 offset-sm-3">
		<button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
		{{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
	</div>
</div>