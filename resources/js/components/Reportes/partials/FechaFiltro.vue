<template>
	<div>
		<div class="mb-3">
			<small class="text-muted">Fecha inicial</small>
			<div class="input-group">
				<input
					type="date"
					autocomplete="off"
					class="datepicker form-control hasDatepicker"
					v-model="start"
					@change=" $emit('update-start')"
				/>
				<b-form-datepicker
					v-model="start"
					button-only
					button-variant="light"
					locale="es-PE"
					aria-controls="date-start"
					today-button
					label-today-button="Hoy"
					reset-button
					label-reset-button="Reiniciar"
					selected-variant="danger"
				></b-form-datepicker>
			</div>
		</div>
		<div class="mb-3">
			<small class="text-muted">Fecha final</small>
			<div class="input-group">
				<input
					type="date"
					autocomplete="off"
					class="datepicker form-control hasDatepicker"
					v-model="end"
					@change=" $emit('update-end')"
				/>
				<b-form-datepicker
					v-model="end"
					button-only
					button-variant="light"
					locale="es-PE"
					aria-controls="date-start"
					today-button
					label-today-button="Hoy"
					reset-button
					label-reset-button="Reiniciar"
					selected-variant="danger"
				></b-form-datepicker>
			</div>
		</div>
	</div>
</template>
<script>
export default {
	data() {
		return {
			start: undefined,
			end: undefined
		};
	}
};
</script>
