<template>
	<div style="background-color: #eef5f9">
		<v-row>
			<v-col cols="12" sm="12" md="12" lg="12">
				<v-container class="d-flex space-between">
					<v-container>
						<v-btn
							outlined
							color="#000fff"
							x-large
							@click="
								(btn.grupos = true),
									(btn.evivo = false),
									(tipo = 1),
									getData(),
									(expand = false),
									(paginate.page = 1),
									(paginate.total_paginas = 1)
							"
							:class="btn.grupos ? 'active' : ''"
							class="mr-0"
							>Aulas virtuales</v-btn
						>
						<v-btn
							outlined
							color="#000fff"
							x-large
							@click="
								(btn.evivo = true),
									(btn.grupos = false),
									(tipo = 2),
									getData(),
									(expand = true),
									(paginate.page = 1),
									(paginate.total_paginas = 1)
							"
							:class="btn.evivo ? 'active' : ''"
							class="ml-0"
							>Eventos en vivo</v-btn
						>
						<v-btn color="#000fff" icon class="ml-5" x-large @click="getData()" title="Recargar">
							<v-icon>mdi-refresh</v-icon>
						</v-btn>
						<v-btn color="#000fff" v-show="tipo == 1 && master" icon class="ml-5" x-large @click="descargarEventosEnVivo()" title="Descargar">
							<v-icon>mdi-download</v-icon>
						</v-btn>
						<v-expand-transition class="ml-8">
							<v-card color="#EEF5F9" elevation="0" outlined v-show="expand" class="mt-3">
								<v-card-title>
									<v-btn @click="modal_en_vivo = true" color="#A1C6EA" dark
										>Crear evento en vivo</v-btn
									>
									<v-dialog v-model="modal_en_vivo" @click:outside="modal_en_vivo=false" max-width="1200px">
										<v-card>
											<v-card-title>
												Crear evento en vivo
												<v-spacer></v-spacer>
												<v-btn icon @click="modal_en_vivo = false">
													<v-icon>mdi-close-thick</v-icon>
												</v-btn>
											</v-card-title>
											<v-form>
												<v-card-text>
													<v-row>
														<v-col cols="12" sm="6" md="4" lg="4">
															<v-menu
																ref="menuarribo"
																v-model="fecha.menu_arribo"
																:close-on-content-click="false"
																:return-value.sync="fecha.date"
																transition="scale-transition"
																offset-y
																min-width="290px"
															>
																<template v-slot:activator="{ on, attrs }">
																	<v-text-field
																		dense
																		v-model="fecha.date"
																		label="Fecha del evento"
																		prepend-icon="mdi-calendar"
																		readonly
																		:hide-details="hide_details"
																		v-bind="attrs"
																		v-on="on"
																		:disabled="disabled"
																	></v-text-field>
																</template>
																<v-date-picker v-model="fecha.date" no-title scrollable locale="es">
																	<v-spacer></v-spacer>
																	<v-btn text color="primary" @click="fecha.menu_arribo = false"
																		>Cancelar</v-btn
																	>
																	<v-btn
																		text
																		color="primary"
																		@click="$refs.menuarribo.save(fecha.date)"
																		>Elegir</v-btn
																	>
																</v-date-picker>
															</v-menu>
														</v-col>
														<v-col cols="12" sm="6" md="4" lg="4">
															<v-menu
																ref="horaarribo2"
																v-model="fecha.menu_arribo2"
																:close-on-content-click="false"
																:nudge-right="40"
																:return-value.sync="fecha.time"
																transition="scale-transition"
																offset-y
																max-width="290px"
																min-width="290px"
															>
																<template v-slot:activator="{ on, attrs }">
																	<v-text-field
																		v-model="fecha.time"
																		label="Hora del evento"
																		prepend-icon="mdi-clock"
																		dense
																		:hide-details="hide_details"
																		readonly
																		v-bind="attrs"
																		v-on="on"
																		:disabled="disabled"
																	></v-text-field>
																</template>
																<v-time-picker
																	v-if="fecha.menu_arribo2"
																	format="24hr"
																	v-model="fecha.time"
																	full-width
																	@click:minute="$refs.horaarribo2.save(fecha.time)"
																></v-time-picker>
															</v-menu>
														</v-col>
														<v-col cols="12" sm="6" md="4" lg="4">
															<v-text-field
																dense
																label="Duracion en min."
																prepend-icon="mdi-clock"
																:hide-details="hide_details"
																v-model="evento.duracion"
																:disabled="disabled"
															></v-text-field>
														</v-col>
													</v-row>
													<v-row>
														<v-col cols="12" sm="12" md="6" lg="6">
															<v-text-field
																dense
																label="Título del evento"
																prepend-icon="mdi-format-letter-case"
																:hide-details="hide_details"
																v-model="evento.titulo"
																:disabled="disabled"
															></v-text-field>
														</v-col>
														<v-col cols="12" sm="12" md="6" lg="6">
															<v-text-field
																dense
																label="Descripción del evento"
																prepend-icon="mdi-format-letter-case"
																:hide-details="hide_details"
																v-model="evento.descripcion"
																:disabled="disabled"
															></v-text-field>
														</v-col>
														<v-col cols="12" sm="6" md="12" lg="12">
															<v-simple-table>
																<template v-slot:default>
																	<thead>
																		<tr>
																			<th style="width: 5% !important">
																				<v-checkbox
																					v-model="cbxAll"
																					@click="allModules()"
																				></v-checkbox>
																			</th>
																			<th class="text-left" style="width: 20% !important">
																				Módulo
																			</th>
																			<th class="text-left" style="width: 75% !important">
																				Carreras
																			</th>
																		</tr>
																	</thead>
																	<tbody>
																		<tr v-for="(modulo, index) in modulos" :key="index">
																			<td>
																				<v-checkbox v-model="modulo.modulo_selected"></v-checkbox>
																			</td>
																			<td>{{ modulo.etapa }}</td>
																			<td>
																				<v-autocomplete
																					:items="modulo.carreras"
																					multiple
																					chips
																					dense
																					v-model="modulo.carreras_selected"
																					label="Carreras"
																					prepend-icon="mdi-account-group"
																					hide-details
																					item-text="nombre"
																					item-value="id"
																					return-object
																					:disabled="!modulo.modulo_selected"
																				>
																					<template v-slot:prepend-item>
																						<v-list-item ripple @click="toggle(index)">
																							<v-list-item-action>
																								<v-icon
																									:color="
																										modulo.carreras_selected.length > 0
																											? 'indigo darken-4'
																											: ''
																									"
																								>
																									{{ icon(index) }}
																								</v-icon>
																							</v-list-item-action>
																							<v-list-item-content>
																								<v-list-item-title>
																									Seleccionar todas las carreras
																								</v-list-item-title>
																							</v-list-item-content>
																						</v-list-item>
																						<v-divider class="mt-2"></v-divider>
																					</template>
																					<template v-slot:selection="{ item, index }">
																						<v-chip v-if="index < 4">
																							<span> {{ item.nombre }}</span>
																						</v-chip>
																						<span v-if="index === 4" class="grey--text caption">
																							(+{{ modulo.carreras_selected.length - 4 }} carrera{{
																								modulo.carreras_selected.length - 4 > 1 ? "s" : ""
																							}})
																						</span>
																					</template></v-autocomplete
																				>
																			</td>
																		</tr>
																	</tbody>
																</template>
															</v-simple-table>
														</v-col>
														<v-col cols="12" sm="12" md="4" lg="4">
															<v-text-field
																dense
																label="Código del evento"
																prepend-icon="mdi-numeric"
																:hide-details="hide_details"
																v-model="evento.link"
																item-text="etapa"
																item-value="id"
																:disabled="disabled"
															></v-text-field>
														</v-col>
													</v-row>
												</v-card-text>
												<v-card-actions>
													<v-spacer></v-spacer>
													<v-btn outlined @click="modal_en_vivo = false" color="#507DBC" dark
														>Cancelar</v-btn
													>
													<v-btn
														@click="crearEventoEnVivo()"
														color="#507DBC"
														dark
														:loading="disabled"
														>Enviar solicitud</v-btn
													>
												</v-card-actions>
											</v-form>
										</v-card>
									</v-dialog>
									<v-dialog v-model="waiting_response" hide-overlay @click:outside="waiting_response=false" width="300">
										<v-card color="primary" dark>
											<v-card-text>
												Procesando...
												<v-progress-linear
													indeterminate
													color="white"
													class="mb-0"
												></v-progress-linear>
											</v-card-text>
										</v-card>
									</v-dialog>
								</v-card-title>
							</v-card>
						</v-expand-transition>
					</v-container>

					<v-container class="text-right">
						<v-btn
							color="#000000"
							outlined
							text
							class="mt-5"
							@click="(btn.filtro1 = !btn.filtro1), getData()"
							:class="btn.filtro1 ? 'filtro' : ''"
							:disabled="btn.disabled_filtro"
						>
							Agendados
						</v-btn>
						<v-btn
							color="#000000"
							outlined
							text
							class="mt-5"
							@click="(btn.filtro2 = !btn.filtro2), getData()"
							:class="btn.filtro2 ? 'filtro' : ''"
							:disabled="btn.disabled_filtro"
						>
							En transcurso
						</v-btn>
						<v-btn
							color="#000000"
							outlined
							text
							class="mt-5"
							@click="(btn.filtro3 = !btn.filtro3), getData()"
							:class="btn.filtro3 ? 'filtro' : ''"
							:disabled="btn.disabled_filtro"
						>
							Finalizado
						</v-btn>
						<v-btn
							color="#000000"
							outlined
							text
							class="mt-5"
							@click="(btn.filtro4 = !btn.filtro4), getData()"
							:class="btn.filtro4 ? 'filtro' : ''"
							:disabled="btn.disabled_filtro"
						>
							Cancelado
						</v-btn>
					</v-container>
				</v-container>
			</v-col>
		</v-row>
		<v-row>
			<v-col cols="12" sm="12" md="12" lg="12" class="text-center">
				<v-pagination
					v-model="paginate.page"
					:length="paginate.total_paginas"
					@input="getData"
				></v-pagination>
			</v-col>
			<v-col cols="12" sm="12" md="12" lg="12">
				<v-container fluid>
					<v-progress-linear
						v-if="loading"
						:indeterminate="loading"
						color="#353B41"
						height="6"
					></v-progress-linear>
					<v-card height="80vh" width="100%" color="#EEF5F9" elevation="0" outlined class="my-0">
						<v-card-text>
							<v-row>
								<v-col v-for="(evento, i) in listado" :key="i" cols="12" sm="12" md="12" lg="12">
									<card-eventos
										:evento="evento"
										:tipo="tipo"
										@update_state="getData()"
									></card-eventos>
								</v-col>
							</v-row>
						</v-card-text>
					</v-card>
				</v-container>
			</v-col>
			<!-- <v-col cols="12" sm="12" md="12" lg="12" class="text-center">
        <v-pagination
          v-model="paginate.page"
          :length="paginate.total_paginas"
          @input="getData"
        ></v-pagination>
      </v-col> -->
		</v-row>
		{{ filtros }}
	</div>
</template>


<script>
import CardEventos from "../components/aulas_virtuales/CardEventos";
export default {
	props: ["user_data", "master"],
	components: {
		CardEventos,
	},
	data() {
		return {
			cbxAll: false,
			paginate: {
				page: 1,
				total_paginas: 1,
			},
			btn: {
				grupos: true,
				evivo: false,
				filtro1: true,
				filtro2: false,
				filtro3: false,
				filtro4: false,
				disabled_filtro: false,
			},
			array_filtros: [2],
			hide_details: true,
			fecha: {
				menu_arribo: false,
				menu_arribo2: false,
				date: new Date().toISOString().substr(0, 10),
				time: new Date().toString().substring(21, 16),
			},
			modal_en_vivo: false,
			waiting_response: false,
			disabled: false,
			loading: true,
			listado: [],
			modulos: [],
			tipo: 1,
			expand: false,
			evento: {
				duracion: 0,
				titulo: "",
				descripcion: "",
				modulo: null,
				link: "",
			},
		};
	},
	computed: {
		filtros() {
			let vue = this;
			let filtro1 = vue.btn.filtro1 ? 1 : 0;
			let filtro2 = vue.btn.filtro2 ? 1 : 0;
			let filtro3 = vue.btn.filtro3 ? 1 : 0;
			let filtro4 = vue.btn.filtro4 ? 1 : 0;
			let suma = filtro1 + filtro2 + filtro3 + filtro4;

			if (suma == 0) {
				vue.btn.filtro1 = true;
				vue.getData();
			}
		},
	},
	created() {
		let vue = this;
		vue.getData();
	},
	methods: {
		async getData() {
			let vue = this;

			vue.loading = false;
			vue.btn.disabled_filtro = false;

			vue.listado = [];
			await axios
				.post("getEventos?page=" + vue.paginate.page, {
					tipo: vue.tipo,
					filtro1: vue.btn.filtro1,
					filtro2: vue.btn.filtro2,
					filtro3: vue.btn.filtro3,
					filtro4: vue.btn.filtro4,
				})
				.then((res) => {
					vue.listado = res.data._eventos;
					vue.modulos = res.data.modulos;
					vue.paginate.total_paginas = res.data.paginate["total_paginas"];

					vue.loading = false;
					vue.btn.disabled_filtro = false;
				})
				.catch((err) => {
					vue.loading = false;
					console.log(err);
				});
		},
		seleccionarTodasLasCarreras(index) {
			return this.modulos[index].carreras_selected.length === this.modulos[index].carreras.length;
		},
		seleccionarAlgunasCarreras(index) {
			return this.modulos[index].carreras_selected.length > 0 && !this.seleccionarTodasLasCarreras;
		},
		icon(index) {
			if (this.seleccionarTodasLasCarreras(index)) return "mdi-close-box";
			if (this.seleccionarAlgunasCarreras(index)) return "mdi-minus-box";
			return "mdi-checkbox-blank-outline";
		},
		toggle(index) {
			let vue = this;
			vue.$nextTick(() => {
				if (vue.seleccionarTodasLasCarreras(index)) {
					vue.modulos[index].carreras_selected = [];
				} else {
					vue.modulos[index].carreras_selected = vue.modulos[index].carreras.slice();
				}
			});
		},

		allModules() {
			let vue = this;
			vue.modulos.forEach((modulo) => {
				modulo.modulo_selected = vue.cbxAll;
			});
		},
		async crearEventoEnVivo() {
			let vue = this;
			vue.waiting_response = true;
			vue.disabled = true;
			await axios
				.post("crearEventoEnVivo", {
					titulo: vue.evento.titulo,
					link: vue.evento.link,
					descripcion: vue.evento.descripcion,
					duracion: vue.evento.duracion,
					fecha_inicio: vue.fecha.date + " " + vue.fecha.time,
					user_id: vue.user_data.id,
					modulo: vue.modulos,
				})
				.then((res) => {
					vue.getData();
					setTimeout(() => {
						vue.modal_en_vivo = false;
						vue.waiting_response = false;
						vue.disabled = false;
						vue.evento = {
							duracion: 0,
							titulo: "",
							descripcion: "",
							modulo: null,
						};
						vue.fecha.date = new Date().toISOString().substr(0, 10);
						vue.fecha.time = new Date().toString().substring(21, 16);
					}, 2500);
				})
				.catch((err) => {
					console.log(err);
				});
		},
		async descargarEventosEnVivo() {
			let vue = this;
			vue.waiting_response = true;
			vue.disabled = true;
			await axios
				.get("descargarEventosEnVivo", {
					responseType: 'blob', 
					params: {
						tipo: vue.tipo,
						master: vue.master,
						filtro1: vue.btn.filtro1,
						filtro2: vue.btn.filtro2,
						filtro3: vue.btn.filtro3,
						filtro4: vue.btn.filtro4,
					}
				})
				.then((res) => {
					// vue.getData();
					// setTimeout(() => {
					// 	vue.modal_en_vivo = false;
					console.log(res)

					vue.waiting_response = false;
					vue.disabled = false;
					
					const downloadUrl = window.URL.createObjectURL(new Blob([res.data]));
			        const link = document.createElement('a');
			        link.href = downloadUrl;
			        link.setAttribute('download', 'Aulas Virtuales.xlsx'); //any other extension
			        document.body.appendChild(link);
			        link.click();
			        link.remove();
				})
				.catch((err) => {
					console.log(err);
				});
		},
	},
};
</script>

<style >
.active {
	background: blue;
	color: white !important;
	font-weight: bold !important;
}
.ingresar {
	color: white !important;
	font-weight: bold !important;
}
.ingresar:hover {
	background: blue;
	color: white !important;
}
.filtro {
	background: #1976d2;
	color: white !important;
}
</style>
