<template>
	<div>
		<button class="btn btn-primary" @click="dialog = true">
			<i class="fa fa-plus"></i> Crear Pregunta
		</button>
		<v-dialog v-model="dialog" width="55%">
			<v-card>
				<v-card-title>
					<v-toolbar flat>
						Crear pregunta
						<v-spacer> </v-spacer>
						<v-btn icon small @click="cancelarCambios()">
							<v-icon>mdi-close-thick</v-icon>
						</v-btn>
					</v-toolbar>
				</v-card-title>
				<v-alert
					border="left"
					dense
					transition="fade-transition"
					:type="alert_backend.tipo_alert"
					text
					v-model="alert_backend.alert"
					close-text="Cerrar alerta"
					class="mx-8 text-left"
				>
					{{ alert_backend.message_alert }} <strong>{{ alert_backend.titulo }}</strong>
				</v-alert>
				<v-alert
					border="left"
					dense
					transition="fade-transition"
					:type="alert_frontend.tipo_alert"
					text
					v-model="alert_frontend.alert"
					close-text="Cerrar alerta"
					class="mx-8 text-left"
					style="position: fixed;width: 50%;z-index: 27;"
				>
					{{ alert_frontend.message_alert }}
				</v-alert>
				<v-card-text>
					<v-row>
						<v-col cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<label class="form-control-label texto-negrita label-size">Título</label>
						</v-col>
						<v-col cols="12" md="10" lg="10" class="vertical-align text-right">
							<v-container>
								<editor
                                    :api-key="$root.getEditorAPIKey()"
									v-model="pregunta.pregunta"
									:init="{
										content_style: 'img { vertical-align: middle; };',
										height: 175,
										menubar: false,
										language: 'es',
										force_br_newlines : true,
									    force_p_newlines : false,
									    forced_root_block : '',
										plugins: ['lists image preview anchor', 'code', 'paste'],
										toolbar:
											'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code',
										images_upload_handler: images_upload_handler,
									}"
								/>
							</v-container>
						</v-col>
					</v-row>
					<!-- <v-row>
						<v-col cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<label class="form-control-label texto-negrita label-size">Pregunta</label>
						</v-col>
						<v-col cols="12" md="10" lg="10" class="vertical-align">
							<v-text-field outlined dense hide-details v-model="pregunta.pregunta"></v-text-field>
						</v-col>
					</v-row> -->
					<v-row v-show="evaluable != 'abierta'">
						<v-col cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<label class="form-control-label texto-negrita label-size">Agregar respuesta</label>
						</v-col>
						<v-col cols="12" md="10" lg="10" class="vertical-align text-right">
							<v-container>
								<editor
                                    :api-key="$root.getEditorAPIKey()"
									v-model="opcOpcion"
									:init="{
										content_style: 'img { vertical-align: middle; };',
										height: 175,
										menubar: false,
										language: 'es',
										force_br_newlines : true,
									    force_p_newlines : false,
									    forced_root_block : '',
										plugins: ['lists image preview anchor', 'code', 'paste'],
										toolbar:
											'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | image | preview | code',
										images_upload_handler: images_upload_handler,
									}"
								/>
							</v-container>
						</v-col>
						<v-col cols="12" md="2" lg="2"> </v-col>

						<v-col cols="12" md="10" lg="10" class="text-center">
							<v-btn class="white-text font-bold" color="#54E69D" @click="agregarOpcion()"
								><i class="fa fa-plus"></i> Agregar</v-btn
							>
						</v-col>
						<v-col cols="12" md="2" lg="2"> </v-col>
						<v-col cols="12" md="10" lg="10">
							<table class="table table-hover datatable" style="overflow-x: scroll">
								<thead>
									<tr class="td-size">
										<th class="text-left" style="width: 10%">#</th>
										<th class="text-left" style="width: 70%">Respuesta</th>
										<th class="text-left" style="width: 10%">¿Correcta?</th>
										<th class="text-left" style="width: 10%">Acción</th>
									</tr>
								</thead>
								<tbody>
									<tr
										class="text-left td-size"
										v-for="(respuesta, index2) in pregunta.rptas_json"
										:key="index2"
									>
										<td>{{ respuesta.id }}</td>
										<td v-html="respuesta.opc"></td>
										<td class="d-flex justify-center switch-pregunta">
											<v-checkbox
												@change="cambiarCorrecta(respuesta.id, respuesta.correcta)"
												v-model="respuesta.correcta"
											></v-checkbox>
										</td>
										<td>
											<v-btn icon small @click="borrarOpcion(respuesta.id)">
												<v-icon>mdi-delete</v-icon>
											</v-btn>
										</td>
									</tr>
								</tbody>
							</table>
						</v-col>
					</v-row>
					<v-row v-show="evaluable != 'abierta'">
						<v-col cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<label class="form-control-label texto-negrita label-size">Estado</label>
						</v-col>
						<v-col cols="12" md="2" lg="2">
							<v-switch class="ml-10" v-model="pregunta.estado"></v-switch>
						</v-col>
						<v-col  cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<label class="form-control-label texto-negrita label-size">Obligatorio:</label>
						</v-col>
						<v-col cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<v-switch class="ml-10" v-model="pregunta.obligatorio"></v-switch>
						</v-col>
						<v-col cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<label class="form-control-label texto-negrita label-size">Puntaje:</label>
						</v-col>
						<v-col  cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<input class="form-control text-center" type="number" step="any" min="1" :max="base_puntaje" v-model="pregunta.puntaje">
						</v-col>
					</v-row>
					<v-row v-show="evaluable == 'abierta'">
						<v-col cols="12" md="2" lg="2" class="vertical-align text-right" :class="alignLabel()">
							<label class="form-control-label texto-negrita label-size">Estado</label>
						</v-col>
						<v-col cols="12" md="2" lg="2">
							<v-switch class="ml-10" v-model="pregunta.estado"></v-switch>
						</v-col>
					</v-row>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<button class="btn btn-secondary mr-1" @click="cancelarCambios()">Cancelar</button>
					<button class="btn btn-primary" @click="guardarPregunta()">Guardar</button>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</div>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";

export default {
	components: {
		editor: Editor,
	},
	props: ['posteo_id','evaluable','base_puntaje'],
	data() {
		return {
			dialog: false,
			pregunta: {
				pregunta: "",
				id: null,
				rptas_json: [],
				rpta_ok: null,
				estado: 1,
				nuevasRptas: "",
				puntaje:1,
				obligatorio:0,
			},
			opcOpcion: null,
			alert_backend: {
				tipo_alert: "info",
				message_alert: "",
				alert: false,
				titulo: "",
			},
			alert_frontend: {
				tipo_alert: "info",
				message_alert: "",
				alert: false,
			},
		};
	},
	methods: {
		cancelarCambios()
		{
			let vue = this;
			vue.dialog = false

			location.reload()
		},
		images_upload_handler(blobInfo, success, failure) {
			console.log(blobInfo.blob());
			let formdata = new FormData();
			formdata.append("image", blobInfo.blob(), blobInfo.filename());
			formdata.append("model_id", null);

			axios
				.post("/upload-image/evaluaciones", formdata)
				.then((res) => {
					success(res.data.location);
				})
				.catch((err) => {
					failure("upload failed!");
				});
		},
		alignLabel() {
			switch (this.$vuetify.breakpoint.name) {
				case "xs":
					return "";
				case "sm":
					return "";
				case "md":
					return "justify-end";
				case "lg":
					return "justify-end";
				case "xl":
					return "justify-end";
			}
		},
		agregarOpcion() {
			let vue = this;
			let nuevaOpcion = {
				id: vue.pregunta.rptas_json.length + 1,
				opc: vue.opcOpcion,
				correcta: false,
			};
			vue.pregunta.rptas_json.push(nuevaOpcion);
			vue.opcOpcion = "";
		},
		borrarOpcion(respuesta_id) {
			let vue = this;
			let respuesta = vue.pregunta.rptas_json.findIndex((rpta) => rpta.id == respuesta_id);
			vue.pregunta.rptas_json.splice(respuesta, 1);
			vue.pregunta.rptas_json.forEach((rpta, index) => {
				rpta.id = index + 1;
				if(rpta.correcta){
					this.pregunta.rpta_ok = rpta.id;
				}
			});
		},
		cambiarCorrecta(respuesta_id) {
			let vue = this;
			vue.pregunta.rptas_json.forEach((rpta) => {
				if (rpta.id == respuesta_id) {
					rpta.correcta = true;
					vue.pregunta.rpta_ok = parseInt(rpta.id);
				} else {
					rpta.correcta = false;
				}
			});
		},
		generarJson() {
			let vue = this;
			// let cadena = "{";
			// if (vue.pregunta.rptas_json.length > 0) {
			// 	vue.pregunta.rptas_json.forEach((element) => {
			// 		cadena += '"' + element.id + '":"' + element.opc.replaceAll('"', "'") + '",';
			// 	});
			// 	cadena = cadena.substring(0, cadena.length - 1);
			// }
			// cadena += "}";
			vue.pregunta.nuevasRptas = JSON.stringify(vue.pregunta.rptas_json);
		},
		guardarPregunta() {
			let vue = this;
			vue.generarJson();
			if (vue.verificarExisteRptaCorrecta() != null && vue.validar_form()) {
				axios
					.post("/evaluaciones/preguntas/nuevaPregunta", {
						key_posteo: vue.posteo_id,
						key: vue.pregunta.id,
						rpta_ok: vue.pregunta.rpta_ok,
						estado: vue.pregunta.estado,
						pregunta: vue.pregunta.pregunta,
						rptas_json: vue.pregunta.rptas_json,
						evaluable: vue.evaluable,
						puntaje: vue.pregunta.puntaje,
						obligatorio:vue.pregunta.obligatorio,
					})
					.then((res) => {
						vue.show_alert("success", res.data.msg, res.data.titulo);
						location.reload();
					})
					.catch((e) => {
						if(e.response.data.msg){
							vue.show_alert_frontend("error",e.response.data.msg);
						}else{
							vue.show_alert_frontend("error", "Error de servidor");
						}
					});
			}
		},
		verificarExisteRptaCorrecta() {
			let vue = this;
			if(vue.evaluable != 'abierta'){
				let existe = vue.pregunta.rptas_json.find((rpta) => rpta.correcta == true);
				if(!existe){
					vue.show_alert_frontend("warning", "Debe marcar una respuesta correcta.");
				}
				return existe;
			}else{
				return true;
			}
		},
		show_alert(tipo, message, titulo) {
			let vue = this;
			vue.alert_backend.tipo_alert = tipo;
			vue.alert_backend.message_alert = message;
			vue.alert_backend.titulo = titulo;
			vue.alert_backend.alert = true;
			setTimeout(() => {
				vue.alert_backend.alert = false;
			}, 4000);
		},
		show_alert_frontend(tipo, message) {
			let vue = this;
			vue.alert_frontend.tipo_alert = tipo;
			vue.alert_frontend.message_alert = message;
			vue.alert_frontend.alert = true;
			setTimeout(() => {
				vue.alert_frontend.alert = false;
			}, 10000);
		},
		validar_form(){
			let vue = this;
			if(vue.pregunta.pregunta==''){
				vue.show_alert_frontend("warning", "Ingrese un título.");
				return false;
			}
			if(vue.evaluable == 'abierta') return true;
			//Verificar si el puntaje es mayor y menor a la mitad de la base (20 o 100))
			if(vue.pregunta.puntaje < 0  || (parseInt(vue.pregunta.puntaje) > parseInt(vue.base_puntaje)) ){
				vue.show_alert_frontend("warning", "El puntaje debe ser mayor o igual a 1, o menor o igual a "+(parseInt(vue.base_puntaje)));
				return false;
			}
			return true;
		}
	},
};
</script>

<style>
.v-application--wrap {
	min-height: 0;
}
.vertical-align {
	display: flex !important;
	align-items: center !important;
}
.label-size {
	font-size: 1.15em !important;
}
.td-size {
	font-size: 1.2em !important;
}
.tr-size {
	font-size: 1.3em !important;
}
.texto-negrita {
	font-weight: bold !important;
}
.switch-pregunta {
	max-height: 20px !important;
	align-items: center;
	padding-top: 23px !important;
}
.v-dialog__content {
	z-index: 203 !important;
}
.white-text {
	color: white !important;
}
.font-bold {
	font-weight: bold !important;
}
</style>
