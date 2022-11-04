<template>
	<v-main>

    	<ResumenExpand :dialog="infoDialog" @onCancel="infoDialog = false">
			<list-item titulo="Tipo : Modalidad de escuela" subtitulo="R: Regular | E: Extracurricular | L: Libre" />
			<list-item titulo="Curso" subtitulo="Curso que tiene asignado el usuario" />
			<list-item titulo="Tema" subtitulo="Tema dentro de cada curso" />
			<list-item titulo="Nota" subtitulo="Nota correspondiente a un tema evaluable y calificado" />
			<list-item titulo="Resultado"
				subtitulo="Resultado de cada evaluación, considerando la nota mínima aprobatoria configurada"
			/>
			<list-item titulo="Última evaluación"
				subtitulo="Fecha de la última evaluación realizada de cada tema"
			/>
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
                	CALIFICACIONES DE USUARIOS 
                </p>

                <small>Consulta el avance del usuario por cada tema desarrollado, dentro de los cursos que tiene asignado.</small>
                
              <!--   <ul class="mt-3">
                    <li><small>Consulta el avance del usuario por cada tema desarrollado, dentro de los cursos que tiene asignado.</small></li>
                </ul> -->
            </v-alert>

			<v-divider class="mt-7"></v-divider>

            <v-row class="justify-content-start">
                <v-col cols="4">
                    <DefaultInput clearable dense
                                  v-model="search"
                                  label="Buscar por DNI..."
                                  @onEnter="buscarNotasUsuario"
                    />
                </v-col>
                <v-col cols="4">
                	<v-btn
						color="primary"
						@click="buscarNotasUsuario"
						v-bind:disabled="(this.search && this.search.length) >= 8 ? false : true"
					>
						<b-icon icon="search" class="mr-2"></b-icon>
						Consultar
					</v-btn>
                </v-col>
            </v-row>
        	<v-divider class=""></v-divider>
        </v-card-text>


<!-- 		<b-input-group size="" class="mb-2 col-6 mt-4">
			<b-form-input
				class="col-7"
				v-model="search"
				type="number"
				placeholder="DNI"
				oninput="javascript: if (this.value.length > 8) this.value = this.value.slice(0, 8);"
				@keyup.enter="buscarNotasUsuario"
			></b-form-input>
			<b-input-group-append>
				<b-input-group-append>
					<b-button
						variant="primary"
						class="text-light"
						@click="buscarNotasUsuario"
						v-bind:disabled="this.search.length >= 8 ? false : true"
					>
						<b-icon icon="search" class="mr-2"></b-icon>
						Consultar
					</b-button>
				</b-input-group-append>
			</b-input-group-append>
		</b-input-group> -->
		<!-- Datos Usuario -->
		<div v-if="Usuario">
			<div class="text-h7 pl-3 mt-4 text-secondary">Datos personales</div>
			<v-simple-table class="border">
				<template v-slot:default>
					<thead>
						<tr class="text-grey font-weight-bold">
							<th>Modulo</th>
							<th>Nombres</th>
							<th>DNI</th>
						</tr>
					</thead>
					<tbody class="text-dark">
						<tr>
							<td>{{ Usuario.modulo }}</td>
							<td>{{ Usuario.nombre }}</td>
							<td>{{ Usuario.dni }}</td>
						</tr>
					</tbody>
				</template>
			</v-simple-table>
		</div>

		<div v-else class="d-flex justify-content-center py-8">
			<!-- <v-img max-width="400" class="text-center" src="/img/guides/hiring.svg"></v-img> -->
			<v-img max-width="300" class="text-center" src="/img/guides/performance.svg"></v-img>
		</div>
		<!-- Tabla ~ Cursos-->
		<div v-if="Cursos">
			<div class="text-h7 pl-3 mt-10 text-secondary">Avance del usuario</div>
			<v-list dense>
				<v-subheader class="border text-body-2 align-center">
					<v-row class="text-grey font-weight-bold" no-gutters>
						<v-col class="col-modalidad px-4">Tipo</v-col>
						<v-col class="col-curso px-4">Curso</v-col>
						<v-col class="col-nota_prom px-4">Nota promedio</v-col>
						<v-col class="col-visitas px-4">Visitas</v-col>
						<v-col class="col-reinicios px-4">Reinicios del Curso</v-col>
						<v-col class="col-resultado px-4">Resultado(Curso)</v-col>
					</v-row>
				</v-subheader>
				<!-- For - Cursos -->
				<v-list-group
					v-for="(item, index) in Cursos"
					:key="index"
					v-model="item.active"
					:prepend-icon="item.action"
					no-action
					class="border"
					active-class="blue lighten-4 pl-2"
				>
					<template v-slot:activator>
						<v-list-item-content
							v-for="(datoCurso, index) in item"
							:key="index"
							v-show="titulosCurso(index)"
							:class="'col-' + index"
						>
							<v-list-item>
								<v-list-item-title v-if="index != 'modalidad'" v-text="datoCurso" class="text-body-2 white-space-normal" />
								<!-- MODALIDAD -->
								<v-list-item-title v-else class="text-body-2 white-space-normal">
									<b-badge v-text="datoCurso.charAt(0).toUpperCase()" n v-b-tooltip.hover :title="obt_texto_tooltip(datoCurso)"></b-badge>
								</v-list-item-title>
							</v-list-item>
						</v-list-item-content>
					</template>
					<v-list max-width="">
						<v-subheader class="pl-0 pr-14">
							<v-row class="text-center text-weight-bold text-body-2 align-center" no-gutters>
								<v-col class="tema-col-tema">Tema</v-col>
								<v-col class="tema-col-nota">Nota</v-col>
								<v-col class="header-icon tema-col-respuestas">
									Respuestas
									<div
										tooltip="Cantidad de respuestas correctas sobre el total de preguntas."
										tooltip-position="top"
									>
										<v-icon color="primary">mdi-information-outline</v-icon>
									</div>
								</v-col>
								<v-col class="header-icon tema-col-resultado">
									Resultado
									<div
										tooltip="Evaluable : Aprobado, Desaprobado   No Evaluable : Revisado o ' - '   Evaluación abierta : Realizado o ' - '"
										tooltip-position="top"
										class="text-left"
									>
										<v-icon color="primary">mdi-information-outline</v-icon>
									</div>
								</v-col>
								<v-col class="tema-col-visitas">Visitas</v-col>
								<v-col class="tema-col-reinicios">Reinicios del Tema</v-col>
								<v-col class="tema-col-ultima_evaluacion">Última evaluación</v-col>
							</v-row>
						</v-subheader>
						<!-- For Temas -->
						<v-list-group
							v-for="(tema, index) in item.temas"
							:key="index"
							class="teal lighten-5 flex-wrap"
						>
							<template v-slot:activator>
								<v-row no-gutters>
									<v-col
										v-for="(dato, index) in tema"
										:key="index"
										class="flex-wrap"
										:class="'tema-col-' + index"
										v-show="mostrarTema(index)"
									>
										<v-list-item-title
											v-text="dato"
											class="flex-wrap text-body-2 white-space-normal"
											:class="index == 'tema' ? 'text-left' : 'text-center'"
										></v-list-item-title>
									</v-col>
								</v-row>
							</template>
							<v-list v-if="tema.prueba">
								<v-subheader>
									<v-row class="text-center" no-gutters>
										<v-col cols="">Pregunta</v-col>
										<v-col cols="">Respuesta Marcada</v-col>
										<v-col cols="">Respuesta Correcta</v-col>
									</v-row>
								</v-subheader>
								<v-list-item
									class="text-center px-2 border-top"
									v-for="(prueba, index) in tema.prueba"
									:key="index"
								>
									<v-list-item-title class="text-body-2 prueba-text">
										{{ prueba.pregunta.trim() || prueba.pregunta}}
									</v-list-item-title>
									<v-list-item-title class="text-body-2 prueba-text">
										{{ prueba.respuesta_usuario.trim() || prueba.respuesta_usuario}}
									</v-list-item-title>
									<v-list-item-title class="text-body-2 prueba-text">
										{{ prueba.respuesta_ok.trim() || prueba.respuesta_ok}}
									</v-list-item-title>
								</v-list-item>
							</v-list>
							<div v-else class="prueba-nondata">No hay datos</div>
						</v-list-group>
					</v-list>
				</v-list-group>
			</v-list>
		</div>
		<v-alert v-if="Alert" dense text color="warning" width="80%" class="mx-3 py-5">
			{{ Alert }}
		</v-alert>
		<!-- Alert -->
	</v-main>
</template>
<script>
	import ListItem from "./partials/ListItem.vue";
	import ResumenExpand from "./partials/ResumenExpand.vue";
	export default {
		components: { ResumenExpand, ListItem },
		props: {
			Modulos: Array,
			API_FILTROS: "",
			API_REPORTES: "",
		},
		data() {
			return {
				search: "",
				Cursos: "",
				Usuario: null,
				Alert: "",
				infoDialog: false
			};
		},
		mounted() {
        	let vue = this

    		let uri = window.location.search.substring(1); 
		    let params = new URLSearchParams(uri);
		    let param_dni = params.get("dni");

        	if (param_dni)
        	{
        		vue.search = param_dni

        		this.buscarNotasUsuario()
        	}
	    },
		methods: {
			async buscarNotasUsuario() {
				if (this.search.length < 8) return false;
				$("#pageloader").fadeIn();
				$("#pageloader-text").text(
					`Por favor espera a que se genere tu reporte (Puede haber reportes en
				cola procesándose).`
				);
				this.Cursos = "";
				this.Usuario = "";

				axios
					.post(this.API_REPORTES + "notas_usuario", { dni: this.search })
					.then(({ data }) => {
						if (data.alert) {
							this.Alert = data.alert;
						}
						this.Cursos = data.Cursos;
						this.Usuario = data.Usuario;
						$("#pageloader").fadeOut();
					})
					.catch((error) => {
						$("#pageloader").fadeOut();
					});
			},
			titulosCurso(index) {
				let indexTitulos = ["modalidad","curso", "nota_prom", "visitas", "reinicios", "resultado"];
				if (indexTitulos.includes(index)) return true;
			},
			mostrarTema(index) {
				let indexTitulos = ["prueba"];
				if (!indexTitulos.includes(index)) return true;
			},
			obt_texto_tooltip(text){
				if(text == 'extra') text = 'extracurricular';
				return text.toUpperCase();
			}
			// columnSize(index) {
			// 	switch (index) {
			// 		case "tema":
			// 			return "5";
			// 			break;
			// 		default:
			// 			return "";
			// 			break;
			// 	}
			// }
		}
	};
</script>
<style lang="scss">
	.col-modalidad {
		flex: 0 0 5%;
		max-width: 5%;
	}
	.col-curso {
		flex: 0 0 40%;
		max-width: 40%;
	}
	.col-nota_prom {
		flex: 0 0 13%;
		max-width: 13%;
	}
	.col-visitas {
		flex: 0 0 13%;
		max-width: 13%;
	}
	.col-reinicios {
		flex: 0 0 13%;
		max-width: 13%;
	}
	.col-resultado {
		flex: 0 0 14%;
		max-width: 14%;
	}
	// Temas
	.tema-col-tema {
		flex: 0 0 40%;
		max-width: 40%;
	}

	.tema-col-nota,
	.tema-col-respuestas,
	.tema-col-resultado,
	.tema-col-visitas,
	.tema-col-reinicios,
	.tema-col-ultima_evaluacion {
		flex: 0 0 10%;
		max-width: 10%;
	}

	.tema-col-resultado {
		text-transform: capitalize;
	}
	/*  */
	.prueba-text {
		font-size: 12px !important;
		font-weight: normal;
		height: 100%;
		white-space: normal;
		padding: 0.5rem 0;
	}
	.prueba-nondata {
		text-align: center;
		height: 30px;
		line-height: 30px;
		font-weight: 600;
		color: black;
		background: #e4e4e4;
	}
	.white-space-normal {
		white-space: normal;
	}
	.header-icon {
		justify-content: center;
		display: flex;
		align-items: center;
	}
</style>
