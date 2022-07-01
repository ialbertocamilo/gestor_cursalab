@php
	$status_up = ( ! ($all->currentPage() == 1 AND $loop->index == 0) ) ? true : false;
	$status_down = ( ! ($all->currentPage() == $all->lastPage() AND $loop->last ) ) ? true : false;

	$collection = $all->getIterator();

	$next = $collection[$loop->index + 1] ?? ($all->_next ?? NULL);
	$previous = $collection[$loop->index - 1] ?? ($all->_previous ?? NULL);
@endphp

<ul class="order">
	{{-- <li><a href="javascript:;" title="Primera posición"><i class="mdi mdi-chevron-double-up"></i></a></li> --}}

	<li>
		{!! Form::open(['route' => [$route, $model->id, $previous->id ?? NULL ], 'method' => 'PATCH', 'class' => 'py-0 px-0']) !!}
		<a href="javascript:;" class="{{ $status_up ? 'change-order' : '' }}" title="Subir posición" {{ $status_up ? '' : 'disabled' }}>
			<i class="mdi mdi-chevron-right up lg {{ $status_up ? 'available' : 'unavailable' }}"></i>
		</a>
		{!! Form::close() !!}
	</li>

	<li>
		{{ $model->orden }}
	 	{{-- (Next : {{ $next->orden ?? '' }} - Before: {{ $previous->orden ?? '' }}) --}}
	 </li>

	<li>
		{!! Form::open(['route' => [$route, $model->id, $next->id ?? NULL ], 'method' => 'PATCH', 'class' => 'py-0 px-0']) !!}

		<a href="javascript:;" class="{{ $status_down ? 'change-order' : '' }}" title="Bajar posición" {{ $status_down ? '' : 'disabled' }}>
			<i class="mdi mdi-chevron-right down lg {{ $status_down ? 'available' : 'unavailable' }}"></i>
		</a>

		{!! Form::close() !!}
	</li>

	{{-- <li><a href="javascript:;" title="Última posición"><i class="mdi mdi-chevron-double-down"></i></a></li> --}}
</ul>