@php
	$excel_errors = request()->session()->get('excel-errors');
@endphp

@if($excel_errors AND $excel_errors->count())

<div class="alert alert-danger">
                
   	@foreach ($excel_errors as $failure)
      <div>Error en la fila #{{ $failure->row() }} - {{ $failure->errors()[0] }}</div>
  	@endforeach
    
</div>

@endif