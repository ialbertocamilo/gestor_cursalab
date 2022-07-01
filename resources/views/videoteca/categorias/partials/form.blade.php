@include('default.form-errors')

<div class="form-group row">
	{{ Form::label('nombre', 'Nombre', ['class'=>'col-sm-3 form-control-label'] ) }}
	<div class="col-sm-9">
		{{ Form::text('nombre',null, ['class' => 'form-control', 'required' => true, 'autocomplete' => 'off']) }}
	</div>
</div>

<div class="line"></div>
<div class="form-group row">
	<div class="col-sm-4 offset-sm-3">
		<button type="button" class="btn btn-secondary" onclick="history.back()">Cancelar</button>
		{{ Form::submit('Guardar', ['class' => 'btn btn-primary']) }}
	</div>
</div>

{{-- @include('default.scripts.dropify-simple') --}}

@section('js')

	<link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet" />

	<style type="text/css">
		.select2 .selection{
			width: 100%;
		}

		.select2-container .select2-selection--single{
			height: calc(2.25rem + 2px);
	    	border: 1px solid #dee2e6;
	    	border-radius: 0;
	    	padding: 0.375rem 0.75rem;
	    }

	    .select2-container--default.select2-container--focus .select2-selection--multiple {
		     border: solid #dee2e6 1px;
		    outline: 0;
		}
		.select2-container--default .select2-selection--multiple {
		    background-color: white;
		    border: 1px solid #dee2e6;
		     border-radius: 0px;
		    cursor: text;
		}

		.bold{
			font-weight: bold;
		}
	</style>

	<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>

    <script type="text/javascript">
    	$(document).ready(function() {
		    // $('.select2').select2({tags: false});
		    $('.select2-create').select2({tags: true});
		});
    </script>

	<link type="text/css" rel="stylesheet" href="{{ asset('vendor/dropify/dist/css/dropify.min.css') }}" />

	<script type="text/javascript" src="{{ asset('vendor/dropify/dist/js/dropify.min.js') }}"></script>

	<script type="text/javascript">

	    initDropify();

	  function initDropify(){
	      $(".dropify").dropify({
	            messages: {
	                'default': 'Arrastre y suelte un archivo aquí o haga clic aquí',
	                'replace': 'Arrastre y suelte o haga clic para reemplazar',
	                'remove':  'Eliminar',
	                'error':   'Vaya, algo malo pasó.'
	            }
	        });
	  }

	</script>

@endsection
