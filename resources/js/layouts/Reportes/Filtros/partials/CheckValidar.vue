<template>
	<div class="col" v-if="User.rol === 'Master'">
		<small class="text-muted text-bold">¿Deseas incluir validaciones ?</small>
		<div class="d-flex align-center p-0 mr-auto mt-2">
			<v-checkbox
				label="Validar"
				color="primary"
				class="my-0 mr-4"
				v-model="validacion"
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
import { mapState } from "vuex";
export default {
	data() {
		return {
			validacion: false
		};
	},
	computed: {
		...mapState(["User"])
	}
};
</script>

<style>
</style>