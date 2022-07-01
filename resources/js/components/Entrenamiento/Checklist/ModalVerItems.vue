<template>
	<v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
		<v-card>
			<v-card-title class="headline">
				Checklist : {{ checklist.titulo }} - Actividades
				<v-spacer></v-spacer>
				<v-btn icon :ripple="false" @click="closeModal"><v-icon>mdi-close</v-icon></v-btn>
			</v-card-title>
			<v-container fluid>
				<v-row>
					<v-col cols="12" md="3" lg="3">
						<v-btn color="primary" @click="addActividad">
							<v-icon>mdi-plus</v-icon> &nbsp; Agregar Actividad
						</v-btn>
					</v-col>
				</v-row>
			</v-container>
			<v-card-text style="height: 500px">
				<v-expansion-panels inset v-model="actividades_expanded">
					<v-expansion-panel v-for="(actividad, i) in checklist.checklist_actividades" :key="i">
						<v-expansion-panel-header v-slot="{ open }">
							<v-row no-gutters>
								<v-col
									cols="4"
									style="
										text-overflow: ellipsis !important;
										overflow: hidden !important;
										width: 200px;
										white-space: nowrap;
									"
								>
									{{ actividad.actividad }}
								</v-col>
								<v-col cols="8" class="text--secondary">
									<v-fade-transition leave-absolute>
										<span v-if="open"></span>
										<v-row v-else no-gutters style="width: 100%">
											<v-col cols="6">
												Dirigido a:
												{{ actividad.tipo == "entrenador_usuario" ? "Alumnos" : "Entrenador" }}
											</v-col>
											<v-col cols="6">
												Estado: {{ checklist.estado == 1 ? "Activo" : "Inactivo" }}
											</v-col>
										</v-row>
									</v-fade-transition>
								</v-col>
							</v-row>
						</v-expansion-panel-header>

						<v-expansion-panel-content>
							<v-row>
								<v-col cols="12" md="7" lg="7">
									<v-textarea
										rows="3"
										outlined
										dense
										hide-details="auto"
										label="Actividad"
										v-model="actividad.actividad"
									></v-textarea>
								</v-col>
								<v-col cols="12" md="3" lg="3">
									<v-select
										outlined
										dense
										hide-details="auto"
										attach
										label="Dirigido a"
										:items="tipo_actividades"
										v-model="actividad.tipo"
										item-text="text"
										item-value="value"
									>
									</v-select>
								</v-col>
								<v-col cols="12" md="2" lg="2">
									<v-container class="px-0" fluid inset>
										<v-switch v-model="actividad.estado" :label="`¿Activo?`"></v-switch>
									</v-container>
								</v-col>
							</v-row>
							<v-row>
								<v-col cols="12" md="12" lg="12" class="d-flex justify-space-between">
									<v-btn color="red" outlined @click="eliminarActividad(actividad, i)"
										>Eliminar</v-btn
									>
									<v-btn color="primary" @click="guardarActividad(actividad)">Guardar</v-btn>
								</v-col>
							</v-row>
						</v-expansion-panel-content>
					</v-expansion-panel>
				</v-expansion-panels>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>

<script>
  import XLSX from "xlsx";

  export default {
  	props: ["value", "width", "checklist"],

  	data() {
  		return {
  			actividades_expanded: [],
  			tipo_actividades: [
  				{
  					text: "Alumno",
  					value: "entrenador_usuario"
  				},
  				{
  					text: "Entrenador",
  					value: "usuario_entrenador"
  				}
  			],
  			dialog: false,
  			file: null,
  			abc: [
  				"A",
  				"B",
  				"C",
  				"D",
  				"E",
  				"F",
  				"G",
  				"H",
  				"I",
  				"J",
  				"K",
  				"L",
  				"M",
  				"N",
  				"O",
  				"P",
  				"Q",
  				"R",
  				"S",
  				"U",
  				"V",
  				"W",
  				"X",
  				"Y",
  				"Z"
  			]
  		};
  	},
  	methods: {
  		closeModal() {
  			let vue = this;
  			vue.actividades_expanded = [];
  			vue.$emit("onClose");
  		},
  		confirm() {
  			let vue = this;
  			vue.$emit("onConfirm");
  		},
  		cancel() {
  			let vue = this;
  			vue.$emit("onCancel");
  		},
  		guardarActividad(actividad) {
  			let vue = this;

  			axios
  				.post(`/entrenamiento/checklist/save_actividad_by_id`, actividad)
  				.then((res) => {
  					if (res.data.error) {
  						vue.$notification.warning(`${res.data.msg}`, {
  							timer: 6,
  							showLeftIcn: false,
  							showCloseIcn: true
  						});
  					} else {
  						actividad.id = res.data.actividad.id;
  						vue.$notification.success(`${res.data.msg}`, {
  							timer: 6,
  							showLeftIcn: false,
  							showCloseIcn: true
  						});
  					}
  				})
  				.catch((err) => {
  					console.err(err);
  				});
  		},

  		eliminarActividad(actividad, index) {
  			let vue = this;
  			axios
  				.post(`/entrenamiento/checklist/delete_actividad_by_id`, actividad)
  				.then((res) => {
  					if (res.data.error) {
  						vue.$notification.warning(`${res.data.msg}`, {
  							timer: 6,
  							showLeftIcn: false,
  							showCloseIcn: true
  						});
  					} else {
						vue.actividades_expanded = []
  						vue.checklist.checklist_actividades.splice(index, 1);
  						vue.$notification.success(`${res.data.msg}`, {
  							timer: 6,
  							showLeftIcn: false,
  							showCloseIcn: true
  						});
  					}
  				})
  				.catch((err) => {
  					console.err(err);
  				});
  		},

  		addActividad() {
  			let vue = this;
  			const newID = `n-${vue.checklist.checklist_actividades.length + 1}`;
  			const newActividad = {
  				id: newID,
  				actividad: "Nueva actividad",
  				estado: 0,
  				tipo: "entrenador_usuario",
  				checklist_id: vue.checklist.id
  			};
  			vue.checklist.checklist_actividades.unshift(newActividad);
  		},

  		descargarExcel(headers, values, array, filename, confirm_text) {
  			if (window.confirm(confirm_text)) {
  				let data = XLSX.utils.json_to_sheet(array, {
  					header: values
  				});
  				headers.forEach((element, index) => {
  					let indice = `${this.abc[index]}1`;
  					data[`${indice}`].v = element;
  				});
  				const workbook = XLSX.utils.book_new();
  				XLSX.utils.book_append_sheet(workbook, data, filename);
  				XLSX.writeFile(workbook, `${filename}.xlsx`);
  			}
  		},
  		subirExcel() {
  			let vue = this;
  			let data = new FormData();
  			data.append("archivo", vue.file);

  			axios
  				.post("/entrenamiento/checklist/asignar_masivo", data)
  				.then((res) => {
  					console.log(res);
  					if (res.data.info.data_no_procesada.length > 0) {
  						let headers = ["DNI", "MENSAJE", "NOMBRES Y APELLIDOS"];
  						let values = ["dni", "msg", "nombre"];
  						vue.descargarExcel(
  							headers,
  							values,
  							res.data.info.data_no_procesada,
  							"Data no procesada",
  							"¿Descargar datos no procesados?"
  						);
  					}
  					vue.file = null;
  					vue.closeModal();
  				})
  				.catch((err) => {
  					console.log(err);
  					vue.file = null;
  					vue.closeModal();
  				});
  		}
  	}
  };
</script>

<style>
  .txt-white-bold {
  	color: white !important;
  	font-weight: bold !important;
  }
  .v-input__icon {
  	padding-bottom: 12px;
  }
  .v-icon.v-icon.v-icon--link {
  	color: #1976d2;
  }
  .v-icon.v-icon {
  	font-size: 31px !important;
  }
  .v-input--selection-controls {
  	margin: unset !important;
  	padding-top: unset !important;
  }
</style>
