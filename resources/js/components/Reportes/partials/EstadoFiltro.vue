<template>
	<div class="col">
		<b-form-text text-variant="muted" v-text="title"></b-form-text>
		<div class="d-flex mt-2">
			<div class="col-3 p-0 mr-auto d-flex align-center">
				<v-checkbox
					class="my-0 mr-4"
					label="Activos"
					color="primary"
					v-model="UsuariosActivos"
					hide-details="false"
					@click.native.stop="verifyCheck('UsuariosActivos')"
				/>
				<div
					:tooltip="tooltip_activos"
					tooltip-position="top"
				>
					<v-icon class="info-icon">mdi-information-outline</v-icon>
				</div>
			</div>
			<div class="col-3 p-0 mr-auto d-flex align-center">
				<v-checkbox
					class="my-0 mr-4"
					label="Inactivos"
					color="gray"
					v-model="UsuariosInactivos"
					hide-details="false"
					@click.native.stop="verifyCheck('UsuariosInactivos')"
				/>
				<div
					:tooltip="tooltip_inactivos"
					tooltip-position="top"
				>
					<v-icon class="info-icon">mdi-information-outline</v-icon>
				</div>
			</div>
		</div>
	</div>
</template>

<script>
export default {
	name: "UsuariosFiltro",
	props:{
		title:{
			default:'Estado de usuarios'
		},
		tooltip_activos:{
			default:'Usuario que puede entrar a la plataforma y ver sus cursos asignados.'
		},
		tooltip_inactivos:{
			default:'Usuario que NO puede ingresar a la plataforma ni ver sus cursos.'
		}
	},
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

<style>
</style>
