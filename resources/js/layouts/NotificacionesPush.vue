<template>
	<v-app fluid class="container font-family">
		<!-- <v-overlay :value="overlay">
			<div style="display: flex !important; align-items: flex-end !important">
				<p class="text-h4 mr-4">{{ loader_text }}</p>
				<v-progress-circular indeterminate size="64"></v-progress-circular>
			</div>
		</v-overlay> -->
		<v-card class="ml-3 mr-2 px-3" elevation="1">
			<v-card-title>
				<v-spacer></v-spacer>
				<v-btn
					class="text-white element-bold text-capitalize size-1rem"
					color="#54e69d"
					elevation="0"
					@click="dialog = true"
				>
					<i class="fa fa-plus mr-1"></i> Crear</v-btn
				>
			</v-card-title>

			<v-card-text>
				<table class="table table-hover">
					<thead class="bg-dark">
						<th>Titulo</th>
						<th>Fecha de envío</th>
						<th class="text-center">Datos</th>
						<th class="text-center">Destinatarios</th>
					</thead>
					<tbody>
						<tr v-for="notificacion in notificaciones" :key="notificacion.id">
							<td class="size-0_7rem">{{ notificacion.titulo }}</td>
							<td size-0_7rem>{{ notificacion.created_at }}</td>
							<td class="text-center">
								<v-chip small class="ma-1" color="green" text-color="white">
									<v-icon size="19" class="mr-2">mdi-check-circle</v-icon>{{ notificacion.success }}
								</v-chip>
								<v-chip small class="ma-1" color="red" text-color="white">
									<v-icon size="19" class="mr-2">mdi-close-circle</v-icon>{{ notificacion.failure }}
								</v-chip>
							</td>
							<td class="text-center">
								<v-btn
									class="text-white element-bold text-capitalize size-1rem"
									small
									elevation="1"
									color="#343A40"
									@click="notificacion.dialog = true"
								>
									Destinatarios
								</v-btn>
								<v-dialog v-model="notificacion.dialog" width="700">
									<v-card flat elevation="0">
										<v-card-title>
											Destinatarios
											<v-spacer> </v-spacer>
											<v-btn icon @click="notificacion.dialog = false">
												<v-icon small>mdi-close</v-icon>
											</v-btn>
										</v-card-title>
										<v-card-text>
											<v-row>
												<v-col cols="12" md="12" lg="12">
													<table class="table">
														<thead class="bg-dark">
															<tr>
																<th>Módulo</th>
																<th>Carrera</th>
															</tr>
														</thead>
														<tbody>
															<template v-for="(value, name) in notificacion.destinatarios">
																<tr
																	v-for="(carrera, index2) in value[0].carreras"
																	:key="carrera.nombre"
																>
																	<td
																		class="element-bold size-1rem"
																		v-if="index2 == 0"
																		:rowspan="value[0].carreras.length"
																	>
																		{{ name }}
																	</td>
																	<td
																		class="size-0_7rem"
																		style="height: 33px !important; display: block"
																	>
																		{{ carrera.carrera_nombre }}
																	</td>
																</tr>
															</template>
														</tbody>
													</table>
												</v-col>
											</v-row>
										</v-card-text>
									</v-card>
								</v-dialog>
							</td>
						</tr>
					</tbody>
				</table>
				<div class="text-center pt-2">
					<v-pagination
						circle
						:total-visible="7"
						color="#796AEE"
						@input="cambiar_pagina"
						v-model="paginate.page"
						:length="paginate.total_paginas"
					></v-pagination>
				</div>
			</v-card-text>
		</v-card>
		<v-dialog v-model="dialog" width="1550" :hide-overlay="hide_overlay">
			<v-card elevation="0">
				<v-card-title class="pt-0 pb-0">
					<v-row>
						<v-col cols="12" md="8" lg="8" class="d-flex justify-start"> Nueva Notificación </v-col>
						<v-col cols="12" md="4" lg="4" class="d-flex justify-center"> Vista previa </v-col>
					</v-row>
				</v-card-title>
				<v-form ref="form_notificacion" lazy-validation @submit.prevent="enviarNotificacion()">
					<v-card-text class="pt-0 pb-0">
						<v-row>
							<v-col cols="12" md="8" lg="8">
								<v-row>
									<v-col cols="12" md="2" lg="2" class="vertical-align">
										<label
											class="form-control-label texto-negrita"
											style="font-size: 1.15em !important"
											>Título
										</label>
									</v-col>
									<v-col cols="12" md="10" lg="10">
										<v-text-field
											label="Título"
											outlined
											dense
											hide-details="auto"
											placeholder="Ingresa un título"
											v-model="nueva_notificacion.titulo"
											:rules="rules.titulo"
										></v-text-field>
									</v-col>
								</v-row>
								<v-row>
									<v-col cols="12" md="2" lg="2" class="vertical-align">
										<label
											class="form-control-label texto-negrita"
											style="font-size: 1.15em !important"
											>Texto
										</label>
									</v-col>
									<v-col cols="12" md="10" lg="10">
										<v-textarea
											label="Texto de la notificación (opcional) "
											outlined
											dense
											hide-details="auto"
											placeholder="Ingresa un texto"
											rows="2"
											v-model="nueva_notificacion.texto"
										></v-textarea>
									</v-col>
								</v-row>
								<v-row>
									<v-col cols="12" md="2" lg="2" class="vertical-align">
										<label
											class="form-control-label texto-negrita"
											style="font-size: 1.15em !important"
											>Destinatarios</label
										>
									</v-col>
									<v-col cols="12" md="10" lg="10"> </v-col>
								</v-row>
								<v-row class="pl-7">
									<v-col cols="12" md="12" lg="12" class="vertical-align">
										<table class="table table-hover">
											<thead class="bg-dark">
												<tr>
													<th style="width: 5% !important">
														<input type="checkbox" v-model="cbxAll" @change="allModules()" />
													</th>
													<th class="text-left" style="width: 20% !important">Módulo</th>
													<th class="text-left" style="width: 75% !important">Carreras</th>
												</tr>
											</thead>
											<tbody>
												<tr v-for="(modulo, index) in modulos" :key="index">
													<td>
														<input type="checkbox" v-model="modulo.modulo_selected" />
													</td>
													<td>{{ modulo.etapa }}</td>
													<td>
														<v-autocomplete
															:items="modulo.carreras"
															multiple
															chips
															dense
															v-model="modulo.carreras_selected"
															hide-details="auto"
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
																				modulo.carreras_selected.length > 0 ? 'indigo darken-4' : ''
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
																<v-chip v-if="index < 3">
																	<span> {{ item.nombre }}</span>
																</v-chip>
																<span v-if="index === 3" class="grey--text caption">
																	(+{{ modulo.carreras_selected.length - 3 }} carrera{{
																		modulo.carreras_selected.length - 3 > 1 ? "s" : ""
																	}})
																</span>
															</template></v-autocomplete
														>
													</td>
												</tr>
											</tbody>
										</table>
									</v-col>
								</v-row>
							</v-col>
							<v-col cols="12" md="4" lg="4">
								<v-card color="#ececec" elevation="0" outlined>
									<v-container fluid class="container-custom-preview">
										<v-img
											src="https://www.gstatic.com/mobilesdk/190403_mobilesdk/android.png"
											class="image-prev"
										>
											<v-list-item two-line class="box-notificacion">
												<v-list-item-content>
													<v-list-item-title class="texto-negrita notificacion_texto">{{
														nueva_notificacion.titulo
													}}</v-list-item-title>
													<v-list-item-subtitle>{{
														nueva_notificacion.texto
													}}</v-list-item-subtitle>
												</v-list-item-content>
											</v-list-item>
										</v-img>
										<p class="os-preview texto-negrita">Android</p>
									</v-container>
									<v-container fluid class="container-custom-preview">
										<v-img src="https://www.gstatic.com/mobilesdk/190403_mobilesdk/iphone.png">
											<v-list-item two-line class="box-notificacion">
												<v-list-item-content>
													<v-list-item-title class="texto-negrita notificacion_texto">{{
														nueva_notificacion.titulo
													}}</v-list-item-title>
													<v-list-item-subtitle>{{
														nueva_notificacion.texto
													}}</v-list-item-subtitle>
												</v-list-item-content>
											</v-list-item></v-img
										>
										<p class="os-preview texto-negrita">iOS</p>
									</v-container>
								</v-card>
							</v-col>
						</v-row>
					</v-card-text>
					<v-divider></v-divider>
					<v-card-actions class="d-flex justify-center">
						<v-btn
							color="#727b84"
							class="text-white text-capitalize size-1rem"
							@click="cerrarModal()"
						>
							Cancelar
						</v-btn>
						<v-btn
							color="#2c32e4"
							class="text-white text-capitalize size-1rem"
							type="submit"
							:disabled="!existeDestinatarios"
						>
							Enviar
						</v-btn>
					</v-card-actions>
				</v-form>
			</v-card>
		</v-dialog>
	</v-app>
</template>
<script>
export default {
	props: ["creador_id"],
	data() {
		return {
			dialog: false,
			hide_overlay: true,
			btn_disabled: false,
			nueva_notificacion: {
				titulo: "Ingresa un título",
				texto: "",
				destinatarios: "",
				creador_id: this.creador_id.id,
			},
			rules: {
				titulo: [(v) => !!v || "El título no puede ir vacío."],
			},
			modulos: [],
			notificaciones: [],
			cbxAll: false,
			overlay: false,
			loader_text: "",
			paginate: {
				page: 1,
				total_paginas: 1,
			},
		};
	},
	created() {
		let vue = this;
		vue.getData();
	},
	computed: {
		existeDestinatarios() {
			console.log("ásd");
			let vue = this;
			let existe = false;
			for (let i = 0; i < vue.modulos.length; i++) {
				if (vue.modulos[i].modulo_selected && vue.modulos[i].carreras_selected.length > 0) {
					existe = true;
					break;
				}
			}
			return existe;
		},
	},
	methods: {
		getData() {
			let vue = this;
			axios
				.get("/notificaciones_push/getData?page=" + vue.paginate.page)
				.then((res) => {
					vue.modulos = res.data.modulos;
					vue.notificaciones = res.data._notificaciones;

					vue.paginate.total_paginas = res.data.paginate.total_paginas;
				})
				.catch((err) => {
					vue.$notification.error("Error de servidor.", {
						timer: 10,
						showLeftIcn: false,
						showCloseIcn: true,
					});
				});
		},
		allModules() {
			let vue = this;
			vue.modulos.forEach((modulo) => {
				modulo.modulo_selected = vue.cbxAll;
			});
		},
		seleccionarTodasLasCarreras(index) {
			let vue = this;
			return vue.modulos[index].carreras_selected.length === vue.modulos[index].carreras.length;
		},
		seleccionarAlgunasCarreras(index) {
			let vue = this;
			return vue.modulos[index].carreras_selected.length > 0 && !vue.seleccionarTodasLasCarreras;
		},
		icon(index) {
			let vue = this;
			if (vue.seleccionarTodasLasCarreras(index)) return "mdi-close-box";
			if (vue.seleccionarAlgunasCarreras(index)) return "mdi-minus-box";
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
		cambiar_pagina(page) {
			let vue = this;
			vue.getData(page);
		},
		generarJson() {
			let vue = this;
			let cadena = "[";
			if (vue.existeDestinatarios) {
				vue.modulos.forEach((mod) => {
					if (mod.carreras_selected.length > 0) {
						cadena +=
							'{"modulo_id":' + mod.id + ', "modulo_nombre": "' + mod.etapa + '", "carreras": [';
						mod.carreras_selected.forEach((carr) => {
							cadena += '{"carrera_id":' + carr.id + ', "carrera_nombre": "' + carr.nombre + '" },';
						});
						cadena = cadena.substring(0, cadena.length - 1);
						cadena += "]},";
					}
				});
				cadena = cadena.substring(0, cadena.length - 1);
			}
			cadena += "]";
			// console.log(cadena);
			// console.log(JSON.parse(cadena));
			vue.nueva_notificacion.destinatarios = cadena;
		},
		showOverlay(text) {
			let vue = this;
			vue.overlay = true;
			vue.loader_text = text;
		},
		hideOverlay() {
			let vue = this;
			vue.overlay = false;
			vue.loader_text = "";
		},
		enviarNotificacion() {
			let vue = this;
			let validar = this.$refs.form_notificacion.validate();
			vue.btn_disabled = true;
			// vue.showOverlay("Enviando notificaciones ...");
			if (validar) {
				vue.generarJson();
				// return;
				axios
					.post("/notificaciones_push/enviarNotificacionCustom", vue.nueva_notificacion)
					.then((response) => {
						vue.$notification.success(response.data.msg, {
							title: response.data.title,
							timer: 10,
							showLeftIcn: false,
							showCloseIcn: true,
						});
					})
					.catch((err) => {
						console.log(err.response.data);
						vue.$notification.error(err.response.data.msg, {
							timer: 10,
							title: err.response.data.title,
							showLeftIcn: false,
							showCloseIcn: true,
						});
					});
				vue.getData();
				setTimeout(() => {
					vue.btn_disabled = false;
					vue.cerrarModal();
					// vue.hideOverlay();
				}, 1500);
			} else {
				vue.btn_disabled = false;
			}
		},
		cerrarModal() {
			let vue = this;
			vue.nueva_notificacion.titulo = "Ingresa un título";
			vue.nueva_notificacion.texto = "";
			vue.nueva_notificacion.destinatarios = [];
			vue.modulos.forEach((mod) => {
				mod.carreras_selected = [];
				mod.modulo_selected = false;
			});
			vue.cbxAll = false;
			vue.dialog = false;
		},
	},
};
</script>


<style>
input[type="checkbox"] {
	transform: scale(1.5);
}
.notificationCenter {
	z-index: 1000 !important;
}
.os-preview {
	color: #939393;
}
.container-custom-preview {
	display: flex;
	justify-content: center;
	flex-direction: column;
	align-items: center;
}
.box-notificacion {
	background: white;
	border: black;
	border-radius: 2px;
	margin-top: 20%;
	padding: 0 8px 0 8px;
	margin-right: 7% !important;
	margin-left: 7% !important;
}
.img-prev {
	background-repeat: no-repeat;
	background-size: 100%;
	display: flex;
	margin: 4px;
	display: flex;
	justify-content: center;
	align-items: center;
}
.theme--light.v-application {
	background: transparent !important;
}
.vertical-align {
	display: flex !important;
	align-items: center !important;
	justify-content: end;
}
.font-family {
	font-family: "Roboto", sans-serif !important;
}
.text--white {
	color: #fff !important;
}
.element-bold {
	font-weight: 400 !important;
}
.size-1rem {
	font-size: 1rem !important;
}
.size-0_7rem {
	font-size: 0.9rem !important;
}
.texto-negrita {
	font-weight: bold !important;
}
.notificacion_texto {
	white-space: normal;
	text-align: left;
	font-size: 14px !important;
}
</style>