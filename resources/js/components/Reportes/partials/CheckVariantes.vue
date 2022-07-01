<template>
	<div class="col">
		<small class="text-muted text-bold">{{ title }} </small>
		<div class="d-flex align-center p-0 mr-auto mt-2">
			<v-checkbox
				label="Si"
				color="primary"
				class="my-0 mr-4"
				v-model="variantes"
				hide-details="false"
			/>
			<div
				tooltip="Agrega columnas validadoras de la información exportada. (Solo disponible para Rol : Master)"
				tooltip-position="top"
			>
				<v-icon class="info-icon">mdi-information-outline</v-icon>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	props: {
		title: {
			default: `¿Deseas incluir los temas que han sido cambiados de estado “Evaluable” a “No Evaluable” que
			no se incluyen en el reporte normal?`,
			type: String
		}
	},
	data() {
		return {
			variantes: false
		};
	}
};
</script>

<style>
</style>