<template>
	<v-card>
		<v-overlay :value="overlay">
			<div style="display: flex !important; align-items: flex-end !important">
				<p class="text-h4 mr-4">{{ loader_text }}</p>
				<v-progress-circular indeterminate size="64"></v-progress-circular>
			</div>
		</v-overlay>
		<v-container>
			<v-row>
				<v-col cols="12" md="10" lg="10">
					<v-tabs v-model="tab">
						<v-tabs-slider></v-tabs-slider>
						<v-tab
							v-for="(modulo, index1) in modulos_componente"
							:key="index1"
							:href="'#tab-' + index1"
							style="text-decoration: none"
							@click="modulo_actual = modulo.id"
						>
							{{ modulo.etapa }}
						</v-tab>
					</v-tabs>
				</v-col>
			</v-row>
		</v-container>
		<v-tabs-items v-model="tab">
			<v-tab-item
				v-for="(modulo, index1) in modulos_componente"
				:key="index1"
				:value="'tab-' + index1"
			>
				<v-row>
					<v-col cols="12" md="10" lg="10" class="ml-4" style="display: flex; align-items: center">
						<p class="font-bold">
							#Escuelas: {{ modulo.total_escuelas }} | #Cursos: Activos
							{{ modulo.total_cursos_activos }} / Inactivos {{ modulo.total_cursos_inactivos }} |
							#Temas : Activos {{ modulo.total_temas_activos }} / Inactivos
							{{ modulo.total_temas_inactivos }}
						</p>
					</v-col>
				</v-row>
				<v-row>
					<v-col
						cols="12"
						md="12"
						lg="12"
						v-for="(escuela, index2) in modulo.categorias"
						:key="index2"
					>
						<v-card class="my-2 mx-4" color="#f1f1f1">
							<v-list-item class="pt-2" two-line>
								<v-list-item-avatar tile size="90">
									<v-img
										:src="'https://static.universidadcorporativafp.com.pe/' + escuela.imagen"
									></v-img>
								</v-list-item-avatar>
								<v-list-item-content>
									<v-list-item-title class="headline mb-1">{{ escuela.nombre }}</v-list-item-title>
									<v-list-item-subtitle style="font-size: 1.1rem">
										#Cursos: Activos {{ escuela.total_cursos_activos }} / Inactivos
										{{ escuela.total_cursos_inactivos }} | #Temas totales: Activos
										{{ escuela.total_temas_activos }} / Inactivos
										{{ escuela.total_temas_inactivos }}
									</v-list-item-subtitle>
								</v-list-item-content>
							</v-list-item>
							<v-card-text>
								<v-row style="display: flex !important; align-items: center !important">
									<v-col cols="12" md="4" lg="4">
										<v-container fluid>
											<v-autocomplete
												:items="escuela.cursos"
												solo
												prepend-inner-icon="mdi-notebook"
												label="Cursos"
												attach
												item-text="nombre"
												item-value="id"
												hide-details
												v-model="escuela.curso_seleccionado"
												@change="getDataCurso(index1, index2)"
											>
											</v-autocomplete>
										</v-container>
									</v-col>
									<v-col cols="12" md="4" lg="4">
										<p class="font-bold">
											#Temas: Activos
											{{ escuela.curso_seleccionado_temas_activos }} / Inactivos
											{{ escuela.curso_seleccionado_temas_inactivos }}
										</p>
									</v-col>
									<v-col cols="12" md="4" lg="4">
										<v-container fluid class="text-right">
											<v-btn
												color="#54E69D"
												class="mr-5 white-text"
												@click="agregarColumna(index1, index2)"
												:disabled="
													modulos_componente[index1].categorias[index2].curso_seleccionado == null
												"
												><i class="fa fa-plus"></i> Agregar
											</v-btn>
											<v-btn
												@click="guardarCurricula(index1, index2)"
												:disabled="
													modulos_componente[index1].categorias[index2].curso_seleccionado == null
												"
												>Guardar</v-btn
											>
										</v-container>
									</v-col>

									<v-expand-transition>
										<v-col
											cols="12"
											sm="12"
											md="12"
											lg="12"
											v-show="modulos_componente[index1].categorias[index2].alert.show"
										>
											<v-alert
												text
												prominent
												:type="modulos_componente[index1].categorias[index2].alert.type"
												:icon="modulos_componente[index1].categorias[index2].alert.icon"
												v-html="modulos_componente[index1].categorias[index2].alert.text "
											>
                         
											</v-alert>
										</v-col>
									</v-expand-transition>

									<v-col cols="12" sm="12" md="12" lg="12" class="p-0">
										<v-container fluid>
											<table class="table table-hover">
												<thead class="bg-dark">
													<tr class="text-center">
														<th style="vertical-align: middle;">Carrera</th>
														<th style="vertical-align: middle;">Ciclo</th>
														<th>
															<span class="d-flex align-center d-inline-flex align-self-start">
																<label class="mr-3">Segmentar por áreas a todos</label>
																<v-switch
																	@change="updateChangeSwithArea($event,index1,index2)"
																></v-switch>
															</span>
															<span>
																Área
																<v-tooltip
																	:v-model="true"
																	top
																>
																	<template v-slot:activator="{ on, attrs }">
																		<v-btn
																			icon
																			v-bind="attrs"
																			v-on="on"
																		>
																			<v-icon color="grey lighten-1">
																				mdi-alert-circle-outline
																			</v-icon>
																		</v-btn>
																	</template>
																	<span>Las áreas listadas son unicamente de usuarios activos</span>
																</v-tooltip>
															</span>
															<v-spacer></v-spacer>
														</th>
														<th>&nbsp;</th>
														<th>&nbsp;</th>
													</tr>
												</thead>
												<tbody>
													<tr v-for="(curricula, i) in escuela.curricula_mostrar" :key="i">
														<td class="pb-6" style="width: 15% !important;vertical-align: bottom;">
															<v-autocomplete
																:items="getCarrerasComponente(modulo.id, index1, index2, i)"
																class="sel_carreras"
																chips
																attach
																solo
																required
																dense
																hide-details
																item-text="nombre"
																item-value="id"
																v-model="curricula.carrera_id"
																@change="cargarCiclos(index1, index2, i)"
															>
															 	<template v-slot:selection="{ item}">
																	<v-chip small color="#F66D6D">
																		<span style="color:white;">{{ item.nombre }}</span>
																	</v-chip>
																</template>
															</v-autocomplete>
														</td>
														<td class="pb-6" style="width: 30% !important;vertical-align: bottom">
															<v-select
																:menu-props="{ top: true, offsetY: true }"
																attach
																solo
																chips
																clearable
																multiple
																required
																dense
																hide-details
																item-text="nombre"
																item-value="id"
																v-model="curricula.ciclos_seleccionados"
																return-object
																:items="curricula.ciclos_totales"
															>
																<template v-slot:selection="{ item}">
																	<v-chip small color="#F66D6D">
																		<span style="color:white;">{{ item.nombre }}</span>
																	</v-chip>
																</template>
															</v-select>
														</td>
														<td  style="width: 55% !important;vertical-align:bottom" >
															<div class="d-flex align-items-center" :style="(curricula.all_criterios) ? 'justify-content: start;' : 'justify-content: center;' ">
																<label for="input-1455" 
																class="v-label theme--light"
																 style="left: 0px; right: auto; position: relative;">¿Segmentar por áreas?</label>
																<v-switch
																	class="ml-2"
																	v-model="curricula.all_criterios"
																	inset
																></v-switch>
																<!-- <v-checkbox v-model="curricula.all_criterios" label="No tomar en cuenta las áreas"></v-checkbox> -->
															</div>
															<transition name="fade">
																<v-autocomplete
																	v-if="curricula.all_criterios"
																	attach
																	solo
																	clearable
																	multiple
																	required
																	dense
																	hide-details
																	item-text="nombre_grupo"
																	item-value="criterio_id"
																	v-model="curricula.grupos_seleccionados"
																	:items="curricula.criterios_listados"
																	return-object
																>
																	<template v-slot:prepend-item>
																		<v-list-item ripple @click="toggle(index1, index2, i)">
																			<v-list-item-action>
																				<v-icon>{{ icon(index1, index2, i) }}</v-icon>
																			</v-list-item-action>
																			<v-list-item-content>
																				<v-list-item-title >Seleccionar todos</v-list-item-title>
																			</v-list-item-content>
																		</v-list-item>
																		<v-divider></v-divider>
																	</template>
																	<template v-slot:selection="{ item, index }">
																		<v-chip v-if="index < 5" small color="#F66D6D">
																			<span style="color:white;">{{ item.nombre_grupo, }}</span>
																		</v-chip>
																		<span
																			v-if="index === 6"
																			class="grey--text text-caption"
																			>
																		(+{{ curricula.grupos_seleccionados.length -  5}} más)
																		</span>
																	</template>
																</v-autocomplete>
															</transition>
														</td>
														<td>
															<transition name="fade" v-if="curricula.all_criterios">
																<v-checkbox @change="cambiar_criterios(index1,index2,i,$event)" label="¿Mostrar todas las Áreas?"></v-checkbox>
															</transition>
														</td>

														<td class="text-center">
															<v-btn icon color="red" @click="eliminarFila(index1, index2, i)"
																><v-icon>mdi-close-circle</v-icon></v-btn
															>
														</td>
													</tr>
												</tbody>
											</table>
										</v-container>
									</v-col>
								</v-row>
							</v-card-text>
							<!-- <v-card-actions class="mx-4">
                  <button class="btn btn-md bg-green" @click="agregarColumna(index1, index2)" ><i class="fa fa-plus"></i> Agregar </button> <v-spacer></v-spacer>
                  <v-btn @click="guardarCurricula(index1, index2)">Guardar</v-btn>
              </v-card-actions> -->
						</v-card>
					</v-col>
				</v-row>
			</v-tab-item>
		</v-tabs-items>
		<!-- </v-card> -->
	</v-card>
</template>

<script>
  export default {
  	data() {
  		return {
  			overlay: true,
  			loader_text: "Cargando",
  			tab: null,
  			modulos_componente: [],
  			carreras_componente: [],
  			grupos_componente: [],
  			grupos_activos: [],
  			grupos_totales: [],
  			selectedFruits: [],
  			modulo_actual: 0,
  			dataGeneral: {
  				total_escuelas: 0,
  				cursos_activos: 0,
  				cursos_inactivos: 0,
  				temas_activos: 0,
  				temas_inactivos: 0
  			},
			show:false,
  		};
  	},
  	computed: {},

  	methods: {
  		async getCurriculaGrupos() {
  			let vue = this;
  			await axios
  				.get("/getCurriculaGrupos")
  				.then((res) => {
  					vue.modulos_componente = res.data.configs;
  					vue.modulo_actual = res.data.configs[0].id;

  					// DISABLE CARRERAS QUE YA ESTAN SELECCIONADAS
  					// vue.modulos_componente.forEach(modulo => {
  					//   modulo.forEach(escuela => {
  					//     escuela.forEach(element => {

  					//     });
  					//   });
  					// });

  					vue.overlay = false;
  				})
  				.catch((err) => {
  					console.log(err);
  				});
  		},
  		async getCarreras() {
  			let vue = this;
  			await axios
  				.get("/getCarreras")
  				.then((res) => {
  					vue.carreras_componente = res.data.carreras;
  				})
  				.catch((err) => {
  					console.log(err);
  				});
  		},
  		async getGrupos() {
  			let vue = this;
  			await axios
  				.get("/getGrupos")
  				.then((res) => {
  					vue.grupos_componente = res.data;
					vue.grupos_activos = res.data.activos;
  					vue.grupos_totales = res.data.totales;
  				})
  				.catch((err) => {
  					console.log(err);
  				});
  		},
  		async getDataCurso(index1, index2) {
  			let vue = this;
			let curso_id = vue.modulos_componente[index1].categorias[index2].curso_seleccionado;
			vue.overlay = true;
			await axios.get("/getCurriculaXCurso/"+curso_id).then((res) => {
				let index_curso = vue.modulos_componente[index1].categorias[index2].cursos.findIndex(
					(curso) => curso.id == curso_id
				);
				vue.modulos_componente[index1].categorias[index2].cursos[index_curso].curricula=res.data.curriculas;
				let curso = vue.modulos_componente[index1].categorias[index2].cursos.find(
					  (curso) => curso.id == curso_id
				  );
				  vue.modulos_componente[index1].categorias[index2].curricula_mostrar = curso.curricula;

				  vue.modulos_componente[index1].categorias[index2].curso_seleccionado_temas_activos =
					  curso.temas_activos;
				  vue.modulos_componente[index1].categorias[index2].curso_seleccionado_temas_inactivos =
					  curso.temas_inactivos;
				vue.overlay = false;
			}).catch((err) => {
				vue.overlay = false;
				alert('Error');

			});
  		},
  		getCarrerasComponente(modulo_id, index1, index2, index3) {
            // console.log(modulo_id, index1, index2, index3)
  			let vue = this;
            let carreras_selected = vue.modulos_componente[index1].categorias[index2].curricula_mostrar.map(el => el.carrera_id)
            let carrera_curricula_selected = vue.modulos_componente[index1].categorias[index2].curricula_mostrar[index3].carrera_id

  			let array1 = vue.carreras_componente.filter(
  				(carrera) => carrera.config_id == modulo_id || carrera.config_id == 0
  			);
            let array = array1.filter(
  				(carrera) => carreras_selected.indexOf(carrera.id) === -1
  			);
            if (carrera_curricula_selected){
                array.push(vue.carreras_componente.find(el => el.id === carrera_curricula_selected) )
            }

            return array.sort(function (a, b) {
                if (a.id > b.id) {
                    return 1;
                }
                if (a.id < b.id) {
                    return -1;
                }
                return 0;
  			});
  		},
		cambiar_criterios(index1,index2,i,value){
			let tipo = (value) ? 'total' : 'activos';
			let criterios_listados;
			if(tipo=='total'){
				criterios_listados = this.getGruposxModulo(this.modulos_componente[index1].id,tipo);
			}else{
				let seleccionados = this.modulos_componente[index1].categorias[index2].curricula_mostrar[i].grupos_seleccionados
				let listados = this.modulos_componente[index1].categorias[index2].curricula_mostrar[i].activos = this.modulos_componente[index1].categorias[index2].curricula_mostrar[i].activos
				criterios_listados = seleccionados.concat(listados);
				criterios_listados = [...new Set([...seleccionados,...listados])]
			}
			this.modulos_componente[index1].categorias[index2].curricula_mostrar[i].criterios_listados = criterios_listados
		},
  		getGruposxModulo(modulo_id,tipo) {
  			let vue = this;
			let array;
			if(tipo === 'total'){
				array = vue.grupos_totales.filter((grupo) => grupo.config_id == modulo_id);
			}else{
				array = vue.grupos_activos.filter((grupo) => grupo.config_id == modulo_id);
			}
  			return array.sort(function (a, b) {
  				if (a.id > b.id) {
  					return 1;
  				}
  				if (a.id < b.id) {
  					return -1;
  				}
  				return 0;
  			});
  		},
  		agregarColumna(index1, index2) {
  			let vue = this;
  			let id_curso_seleccionado =
  				vue.modulos_componente[index1].categorias[index2].curso_seleccionado;
  			if (id_curso_seleccionado == null) {
  				vue.modulos_componente[index1].categorias[index2].alert.type = "error";
  				vue.modulos_componente[index1].categorias[index2].alert.icon = "mdi-cloud-alert";
  				vue.modulos_componente[index1].categorias[index2].alert.text =
  					"No hay ningún curso seleccionado.";
  				vue.modulos_componente[index1].categorias[index2].alert.show = true;

  				setTimeout(() => {
  					vue.modulos_componente[index1].categorias[index2].alert.show = false;
  				}, 2500);
  				return;
  			}
        let modulo_id = vue.modulos_componente[index1].id;
        	let criterios_activos = this.getGruposxModulo(modulo_id,'activos');
  			let nuevaColumna = {
  				carrera_id: null,
  				ciclo_id: null,
  				curso_id: id_curso_seleccionado,
  				curricula_id: null,
  				grupo_id: null,
  				ciclos_seleccionados: [],
  				ciclos_totales: [],
  				grupos_totales: [],
  				grupos_seleccionados: [],
  				criterios_listados: criterios_activos,
  				activos: criterios_activos,
				all_criterios:false,
  			};
			  console.log(nuevaColumna);
  			vue.modulos_componente[index1].categorias[index2].curricula_mostrar.push(nuevaColumna);
  		},
  		cargarCiclos(index1, index2, i) {
  			let vue = this;
  			let id_carrera_seleccionada =
  				vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].carrera_id;
  			let existe = vue.modulos_componente[index1].categorias[index2].curricula_mostrar.filter(
  				(c) => c.carrera_id == id_carrera_seleccionada
  			);
  			if (existe.length > 1) {
  				vue.modulos_componente[index1].categorias[index2].curricula_mostrar.splice(i, 1);
  				vue.modulos_componente[index1].categorias[index2].alert.type = "error";
  				vue.modulos_componente[index1].categorias[index2].alert.icon = "mdi-cloud-alert";
  				vue.modulos_componente[index1].categorias[index2].alert.text =
  					"Esta carrera ya se encuentra seleccionada, elija otra.";
  				vue.modulos_componente[index1].categorias[index2].alert.show = true;

  				setTimeout(() => {
  					vue.modulos_componente[index1].categorias[index2].alert.show = false;
  					vue.agregarColumna(index1, index2);
  				}, 1500);
  			} else if (existe.length == 1) {
  				let carrera_seleccionada = vue.carreras_componente.find(
  					(carrera) => carrera.id == id_carrera_seleccionada
  				);
  				// CARGO LOS CICLOS DE LA CARRERA SELECCIONADA
  				vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].ciclos_totales =
  					carrera_seleccionada.ciclos;
  			}
  		},
  		toggle(index1, index2, i) {
  			let vue = this;
			// console.log(vue.modulos_componente[index1].id);
			// console.log(vue.grupos_componente.filter((grupo) => grupo.config_id ==  vue.modulos_componente[index1].id));
  			vue.$nextTick(() => {
  				if (vue.seleccionarTodasLasOpciones(index1, index2, i)) {
  					vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].grupos_seleccionados = [];
  				} else {
  					vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].grupos_seleccionados = vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].criterios_listados.filter((grupo) => grupo.config_id ==  vue.modulos_componente[index1].id).slice();
  				}
  			});
  		},
  		seleccionarTodasLasOpciones(index1, index2, i) {
  			let vue = this;
			let array = vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].criterios_listados.filter((grupo) => grupo.config_id == vue.modulos_componente[index1].id);
  			return (
  				vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].grupos_seleccionados
  					.length === array.length
  			);
  		},
  		seleccionarUnaOpcion(index1, index2, i) {
  			let vue = this;
  			return (
  				vue.modulos_componente[index1].categorias[index2].curricula_mostrar[i].grupos_seleccionados
  					.length > 0 && !vue.seleccionarTodasLasOpciones(index1, index2, i)
  			);
  		},
  		icon(index1, index2, i) {
  			let vue = this;

  			if (vue.seleccionarTodasLasOpciones(index1, index2, i)) return "mdi-close-box";
  			if (vue.seleccionarUnaOpcion(index1, index2, i)) return "mdi-minus-box";
  			return "mdi-checkbox-blank-outline";
  		},
  		eliminarFila(index1, index2, i) {
  			let vue = this;
  			vue.modulos_componente[index1].categorias[index2].curricula_mostrar.splice(i, 1);
  		},
  		async guardarCurricula(index1, index2) {
  			let vue = this;
  			vue.loader_text = "Guardando datos ";
  			vue.overlay = true;
			let error = false;
			let text = '';
			vue.modulos_componente[index1].categorias[index2].curricula_mostrar.map(c=>{
				if(c.ciclos_seleccionados.length ==0) {error = true ; text = 'Hay curriculas sin ciclos seleccionados'};
				if(!c.carrera_id){error = true ; text = 'Hay curriculas sin la carrera seleccionada'};
				if(c.all_criterios && c.grupos_seleccionados.length == 0){error = true ; text = 'Hay curriculas sin áreas seleccionadas'};
			})
			if(error){
				vue.modulos_componente[index1].categorias[index2].alert.text = text;
				console.log(vue.modulos_componente[index1].categorias[index2].alert.text,text);
				vue.overlay = false;
				vue.modulos_componente[index1].categorias[index2].alert.show = true;
  				setTimeout(() => {
  					vue.modulos_componente[index1].categorias[index2].alert.show = false;
  				}, 2500);
				return false;
			}
  			await axios
  				.post("/guardarCurricula", {
  					data: vue.modulos_componente[index1].categorias[index2].curricula_mostrar,
  					curso_id: vue.modulos_componente[index1].categorias[index2].curso_seleccionado
  				})
  				.then((res) => {
  					vue.overlay = false;
  					vue.modulos_componente[index1].categorias[index2].alert.type = "success";
  					vue.modulos_componente[index1].categorias[index2].alert.icon = "mdi-check-bold";
  					vue.modulos_componente[index1].categorias[index2].alert.text = "Currícula actualizada. <br> Las actualizaciones se verán reflejadas en los reportes dentro de 20 minutos.";
  					vue.modulos_componente[index1].categorias[index2].alert.show = true;

  					setTimeout(() => {
  						vue.modulos_componente[index1].categorias[index2].alert.show = false;
  					}, 9500);
  				})
  				.catch((err) => {
  					console.log(err);
  				});
  		},
  		exportarCurriculasExcel() {
  			let vue = this;
  			axios
  				.post("/exportar/curricula_excel")
  				.then((res) => {})
  				.catch((err) => {
  					console.log(err);
  				});
  		},
		updateChangeSwithArea(val,index1,index2){
			let curriculas = this.modulos_componente[index1].categorias[index2].curricula_mostrar;
			if (curriculas.length>0) {
				curriculas.forEach((c,index) => {
					this.modulos_componente[index1].categorias[index2].curricula_mostrar[index].all_criterios=val;
				});
			}
			console.log(categoria);
		}
  	},
  	created() {
  		let vue = this;
  		vue.getCurriculaGrupos();
  		vue.getCarreras();
  		vue.getGrupos();
  	}
  };
</script>

<style>
  .custom_td {
  	height: 63px !important;
  }
  .v-tab--active {
  	color: white !important;
  	font-weight: bold;
  	background: #1867c0 !important;
  }

  .white-text {
  	color: white !important;
  }
  .font-bold {
  	font-weight: bold !important;
  }
  .fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
</style>
