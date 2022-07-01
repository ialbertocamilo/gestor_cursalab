<template>
	<div class="row py-2 reporte-filtro-checkbox">
		<div class="col-12 col-md-6 p-0 --mr-auto d-flex justify-content-center">
			<v-checkbox
				class="my-0 mr-4"
				label="Usuarios activos"
				color="primary"
				v-model="UsuariosActivos"
				hide-details="false"
				@click.native.stop="verifyCheck('UsuariosActivos')" 
			/>
			<div
				tooltip="Usuario que puede entrar a la plataforma y ver sus cursos asignados."
				tooltip-position="top"
			>
				<v-icon class="info-icon" style="top: -2px;">mdi-information-outline</v-icon>
			</div>
		</div>
		<div class="col-12 col-md-6 p-0 d-flex justify-content-center">
			<v-checkbox
				class="my-0 mr-4"
				label="Usuarios inactivos"
				color="gray"
				v-model="UsuariosInactivos"
				hide-details="false"
				@click.native.stop="verifyCheck('UsuariosInactivos')" 
			/>
			<div
				tooltip="Usuario que NO puede ingresar a la plataforma ni ver sus cursos."
				tooltip-position="top"
			>
				<v-icon class="info-icon" style="top: -2px;">mdi-information-outline</v-icon>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: "UsuariosFiltro",
	data() {
		return {
			UsuariosActivos: true,
			UsuariosInactivos: false
		};
	},
	methods: {
		verifyCheck(filter){
			(!this.UsuariosActivos && !this.UsuariosInactivos) ? (this[filter] = true) : this.$emit("emitir-cambio") ;
		}
	}
};
</script>

<style lang="scss">
	
</style>