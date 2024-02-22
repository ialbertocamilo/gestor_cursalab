<template>
	<v-main>
		<!-- Resumen del reporte -->
		<ResumenExpand :dialog="infoDialog" @onCancel="infoDialog = false" titulo="Resumen del reporte">
			<template v-slot:resumen>
				Descarga el acumulado de ingresos a cada tema, por parte de los usuarios
			</template>
			<list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
			<list-item
				titulo="Grupo sistema"
				subtitulo="Código de grupo (contiene la fecha de subida a la plataforma)"
			/>
			<list-item titulo="Grupo" subtitulo="Grupo al que pertenece el usuario" />
			<list-item titulo="Botica" subtitulo="Botica en la que se ubica el usuario" />
			<list-item titulo="Documento, Apellidos y nombres, Género" subtitulo="Datos personales" />
			<list-item titulo="Carrera" subtitulo="Carrera actual en la que se encuentra" />
			<list-item titulo="Ciclo" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item
				titulo="Estado"
				subtitulo="El estado indica si el usuario está habilitado para usar la plataforma (Activo: Si, Inactivo: No)"
			/>
			<list-item titulo="Modalidad" subtitulo="Modalidad de cada escuela: regular, extra(extracurricular), libre" />
			<list-item titulo="Escuela" subtitulo="Escuela de cada curso asignado" />
			<list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
			<list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
			<list-item titulo="Cantidad de visitas por tema" subtitulo="Total de visitas por cada tema" />
		</ResumenExpand>

		<v-card-text>
        	<v-alert
              border="top"
              colored-border
              elevation="2"
              color="primary"
              class=""
            >
                <p class="text-h6 text--primary text-center pt-3">
                	<v-icon color="primary" style="top: -5px;" @click="infoDialog = true">mdi-information</v-icon>
                	VISITAS DE USUARIOS
                </p>

                <small>Descarga el acumulado de ingresos a cada tema, por parte de los usuarios.</small>

            </v-alert>

			<v-divider class="mt-7"></v-divider>

			<form class="" @submit.prevent="exportVisitas">

				<v-row class="justify-content-center">
					<v-col cols="12" md="6">
						<DefaultSelect clearable
	                               :items="Modulos"
	                               v-model="modulo"
	                               label="Módulo"
	                               dense
	                               return-object
	                               :count-show-values="3"
	                               :show-select-all="false"
	                               item-text="etapa"
								   item-value="id"
	                               @onChange="moduloChange"
	                	/>
					</v-col>

					<v-col cols="12" md="6">
								<DefaultSelect
									attach
									solo
									chips
									clearable
									multiple
									dense
									hide-details="false"
		                           :items="Carreras"
		                           v-model="carrera"
		                           :label="!Carreras[0] || modulo == 'ALL' ? 'Selecciona un #Módulo ' : 'Carreras'"
									:background-color="!Carreras[0] || modulo == 'ALL' ? 'grey lighten-3' : 'light-blue lighten-5'"
		                           return-object
		                           :count-show-values="2"
		                           :show-select-all="false"
								   :loading="loadingCarreras"
									:disabled="!Carreras[0] || modulo == 'ALL'"
		                           @onChange="carreraChange" />

					</v-col>
				</v-row>

				<v-row class="justify-content-center">

					<v-col cols="12">
						<DefaultSelect
							attach
							solo
							chips
							clearable
							multiple
							dense
							hide-details="false"
	                       v-model="grupo"
							:items="Grupos"
							:label="!Grupos[0] || modulo == 'ALL' || !carrera[0]  ? 'Selecciona una #Carrera ' : 'Áreas'"
							:background-color="!Grupos[0] || modulo == 'ALL' || !carrera[0]  ? 'grey lighten-3' : 'light-blue lighten-5'"
	                       return-object
	                       :count-show-values="5"
	                       :show-select-all="false"
						   :loading="loadingGrupos"
							:disabled="!Grupos[0] || modulo == 'ALL' || !carrera[0] "
	                       />

					</v-col>

				</v-row>

				<v-row class="justify-content-center">
					<v-col cols="12" md="6">
						<UsuariosFiltro ref="UsuariosFiltroComponent" @emitir-cambio="moduloChange" />
					</v-col>

					<div class="col-12 col-md-6 my-2 --mr-auto d-flex justify-content-center reporte-filtro-checkbox">
						<v-checkbox
							label="Incluir Escuelas Libres"
							color="primary"
							class="my-0 mr-4"
							v-model="cursos_libres"
							hide-details="false"
						/>
						<div
							tooltip="Escuelas con cursos que no se cuentan en el progreso"
							tooltip-position="top"
						>
							<v-icon class="info-icon" style="top: -2px;">mdi-information-outline</v-icon>
						</div>
					</div>
				</v-row>

				<div class="mb-4 d-flex justify-content-center">
					<v-btn color="primary" type="submit" class="col-4">
						<v-icon class="mr-2" small>mdi-download</v-icon>
						Descargar
					</v-btn>

				</div>

	        	<v-divider class=""></v-divider>
			</form>

	        <div  class="d-flex justify-content-center py-10">
				<v-img max-width="350" class="text-center" src="/img/guides/visits.svg"></v-img>
			</div>

        </v-card-text>

	</v-main>
</template>
<script>
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
import UsuariosFiltro from "./partials/UsuariosFiltro.vue";
export default {
	components: { UsuariosFiltro, ResumenExpand, ListItem },
	props: {
		Modulos: Array,
		API_FILTROS: "",
		API_REPORTES: ""
	},
	data() {
		return {
			Carreras: [],
			Grupos: [],
			modulo: "",
			carrera: "",
			grupo: "",
			loadingCarreras: false,
			loadingGrupos: false,
			//
			cursos_libres:false,
			infoDialog: false,
		};
	},
	methods: {
		exportVisitas() {
			$("#pageloader").fadeIn();
			let UFC = this.$refs.UsuariosFiltroComponent;
			let params = {
				cursos_libres : this.cursos_libres,
				modulo: this.modulo,
				carrera: this.carrera,
				grupo: this.grupo,
				UsuariosActivos: UFC.UsuariosActivos,
				UsuariosInactivos: UFC.UsuariosInactivos
			};
			axios
				.post(this.API_REPORTES + "visitas", params)
				.then((res) => {
					if (!res.data.error) this.$emit("emitir-reporte", res);
					else {
						alert("Se ha encontrado el siguiente error : " + res.data.error);
						$("#pageloader").fadeOut();
					}
				})
				.catch((error) => {
					console.log(error);
					console.log(error.message);
					alert("Se ha encontrado el siguiente error : " + error);
					$("#pageloader").fadeOut();
				});
		},
		moduloChange() {
			this.carrera = [];
			this.Carreras = [];
			this.grupo = [];
			this.Grupos = [];
			if (!this.modulo || this.modulo == "ALL") return false;
			this.loadingCarreras = true;
			let UFC = this.$refs.UsuariosFiltroComponent;
			axios
				.post(this.API_FILTROS + "cambia_modulo_carga_carrera", {
					mod: this.modulo,
					UsuariosActivos: UFC.UsuariosActivos,
					UsuariosInactivos: UFC.UsuariosInactivos,
				})
				.then((res) => {
					res.data.forEach((el) => {
						let NewJSON = {};
						NewJSON.text = el.nombre;
						NewJSON.value = el.id;
						this.Carreras.push(NewJSON);
					});
					this.loadingCarreras = false;
				})
				.catch((err) => {
					console.log(err);
					this.loadingCarreras = false;
				});
		},
		carreraChange() {
			this.grupo = [];
			this.Grupos = [];
			if (!this.modulo || this.modulo == "ALL") return false;
			this.loadingGrupos = true;
			let JsonCarreras = this.carrera.map((val) => val).join(",");
			let UFC = this.$refs.UsuariosFiltroComponent;
			axios
				.post(this.API_FILTROS + "cambia_carrera_carga_grupo", {
					mod: this.modulo,
					carre: JsonCarreras,
					UsuariosActivos: UFC.UsuariosActivos,
					UsuariosInactivos: UFC.UsuariosInactivos,
				})
				.then((res) => {
					console.log(res);
					res.data.forEach((el) => {
						let NewJSON = {};
						NewJSON.text = el.grupo;
						NewJSON.value = el.id;
						this.Grupos.push(NewJSON);
					});
					this.loadingGrupos = false;
				})
				.catch((err) => {
					this.loadingGrupos = false;
					console.log(err);
				});
		}
	}
};
</script>

<style></style>
