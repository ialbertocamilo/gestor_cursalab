<template>
	<div>
		<v-tooltip attach="" top>
			<template v-slot:activator="{ on, attrs }">
				<v-btn
					color="indigo darken-2"
					icon
					@click="(dialog = true), getInitialData()"
					v-bind="attrs"
					v-on="on"
					><v-icon>mdi-notebook-multiple</v-icon></v-btn
				>
			</template>
			<span class="text-one-line">Ver cursos</span>
		</v-tooltip>
		<v-dialog v-model="dialog" width="50%">
			<v-card>
				<v-card-title transition="fade-transition">
					GRUPO: {{ usuario_data.nom_grupo }} | CARRERA: {{ usuario_data.nom_carrera }} | CICLO:
					{{ usuario_data.nom_ciclo }}</v-card-title
				>
				<v-card-text>
					<v-container>
						<v-simple-table dense fixed-header height="500px">
							<template v-slot:default>
								<thead>
									<tr>
										<th
											class="text-left text-size-bold-white fondo-th"
											style="width: 30% !important"
										>
											Escuela
										</th>
										<th class="text-left text-size-bold-white fondo-th">Cursos</th>
									</tr>
								</thead>
								<tbody v-if="loading">
									<td colspan="2">
										<v-progress-linear :size="50" color="primary" indeterminate></v-progress-linear>
									</td>
								</tbody>
								<tbody v-else>
									<template v-for="escuela in escuelas">
										<tr class="text-left" v-for="(curso, index2) in escuela.cursos" :key="curso.id">
											<td v-if="index2 == 0" :rowspan="escuela.cursos.length">
												{{ escuela.nombre }}
											</td>
											<td>{{ curso.nom_curso }}</td>
										</tr>
									</template>
								</tbody>
							</template>
						</v-simple-table>
					</v-container>
				</v-card-text>
				<v-card-actions>
					<v-spacer> </v-spacer>
					<v-btn color="indigo darken-3 text-white" @click="dialog = false">Cerrar</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>
<script>
export default {
	props: ["usuario_id"],
	data() {
		return {
			dialog: false,
			escuelas: [],
			loading: true,
			usuario_data: {
				nom_grupo: "",
				nom_ciclo: "",
				nom_carrera: "",
			},
		};
	},
	created() {
		let vue = this;
		// vue.getInitialData();
	},
	methods: {
		getInitialData() {
			let vue = this;
			axios.get("/usuarios/getCursosxUsuario/" + vue.usuario_id).then((res) => {
				vue.usuario_data.nom_ciclo = res.data.nom_ciclo;
				vue.usuario_data.nom_grupo = res.data.nom_grupo;
				vue.usuario_data.nom_carrera = res.data.nom_carrera;
				vue.escuelas = res.data.categorias;
				setTimeout(() => {
					vue.loading = false;
				}, 1500);
			});
		},
	},
};
</script>

<style >
.v-application--wrap {
	min-height: 0;
}
.theme--light.v-application {
	background: transparent;
	color: rgba(0, 0, 0, 0.87);
}
.text-one-line {
	white-space: nowrap;
}
.text-size-bold-white {
	font-size: 1.23em !important;
	font-weight: bold !important;
	color: white !important;
}
.fondo-th {
	background: #343a40 !important;
}
</style>