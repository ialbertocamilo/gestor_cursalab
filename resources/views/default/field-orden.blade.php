<div class="form-group">
	{{ Form::label('orden', 'Orden', ['class'=>'form-control-label'] ) }}
	{{ Form::number('orden', $default_order ?? null, ['required' => 'required','class' => 'form-control', 'max' => $max_order ?? NULL, 'min' => 1]) }}
</div>