<template>
	<div>
		<small class="form-text text-muted">Áreas {{ ` (${Grupos.length}) ` || "" }}</small>
		<v-select
			attach
			solo
			chips
			clearable
			multiple
			hide-details="false"
			v-model="grupo"
			:menu-props="{ overflowY: true, maxHeight: '250' }"
			:items="Grupos"
			:label="modulo ? 'Selecciona uno o mas Áreas' : 'Selecciona un #Modulo'"
			:loading="loadingGrupos"
			:disabled="!Grupos[0]"
			:background-color="!Grupos[0] ? 'grey lighten-3' : 'light-blue lighten-5'"
		></v-select>
	</div>
</template>

<script>
export default {
	props: ["modulo"],
	data() {
		return {
			loadingGrupos: false,
			grupo: undefined,
			Grupos: []
		};
	},
	methods: {
		cargarGrupos() {
			this.loadingGrupos = true;
			let params = {};
			this.modulo ? (params.mod = this.modulo) : "";
			axios.get(this.API_FILTROS + "cargar_grupos", { params }).then((res) => {
				res.data.forEach((el) => {
					let NewJSON = {};
					NewJSON.text = el.valor;
					NewJSON.value = el.id;
					this.Grupos.push(NewJSON);
				});
				this.loadingGrupos = false;
			});
		}
	},
	watch: {
		modulo: () => {
			console.log("Cambio");
		}
	}
};
</script>

<style>
</style>