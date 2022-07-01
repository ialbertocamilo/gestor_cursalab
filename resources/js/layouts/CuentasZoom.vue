<template>
	<v-container>
		<v-card>
			<v-data-table
				:items="cuentas_zoom"
				:headers="headers"
				hide-default-footer
				class="elevation-0"
				:items-per-page="items_per_page"
				:loading="loading_table"
			>
				<template v-slot:top>
					<v-toolbar flat>
						<v-toolbar-title>Cuentas Zoom</v-toolbar-title>
						<v-divider class="mx-4" inset vertical></v-divider>
						<v-tooltip right attach="">
							<template v-slot:activator="{ on, attrs }">
								<v-btn icon color="black" dark v-bind="attrs" v-on="on" @click="getInitialData()">
									<v-icon> mdi-table-refresh </v-icon>
								</v-btn>
							</template>
							<span>Actualizar datos</span>
						</v-tooltip>
						<v-spacer></v-spacer>
						<v-btn @click="crearRecurso()" color="primary"> <v-icon>mdi-plus</v-icon> Crear</v-btn>
					</v-toolbar>
					<v-alert
						border="left"
						dense
						transition="fade-transition"
						:type="tipo_alert"
						text
						v-model="alert"
						close-text="Cerrar alerta"
						class="mx-3"
					>
						{{ message_alert }}
					</v-alert>
				</template>
				<template v-slot:item.editar="{ item }">
					<v-tooltip right attach="">
						<template v-slot:activator="{ on, attrs }">
							<v-btn
								icon
								color="black"
								dark
								v-bind="attrs"
								v-on="on"
								@click="generarToken(item.id)"
							>
								<v-icon> mdi-code-greater-than </v-icon>
							</v-btn>
						</template>
						<span>Generar Tokens</span>
					</v-tooltip>
					<v-tooltip right attach="">
						<template v-slot:activator="{ on, attrs }">
							<v-btn
								icon
								color="teal darken-1"
								dark
								v-bind="attrs"
								v-on="on"
								@click="editarCuentaZoom(item)"
							>
								<v-icon> mdi-comment-edit </v-icon>
							</v-btn>
						</template>
						<span>Editar</span>
					</v-tooltip>
					<v-tooltip right attach="">
						<template v-slot:activator="{ on, attrs }">
							<v-btn icon color="red" dark v-bind="attrs" v-on="on" @click="eliminar(item)">
								<v-icon> mdi-delete </v-icon>
							</v-btn>
						</template>
						<span>Eliminar</span>
					</v-tooltip>
				</template>
				<template v-slot:item.estado="{ item }">
					<v-chip class="ma-2" color="grey" label v-if="item.estado == 0">
						<strong style="color: white !important">INACTIVO</strong>
					</v-chip>
					<v-chip class="ma-2" color="green" label v-if="item.estado == 1">
						<strong style="color: white !important">ACTIVO</strong>
					</v-chip>
				</template>
			</v-data-table>
			<v-divider></v-divider>
			<div class="text-center pb-2">
				<v-pagination
					@input="cambiar_pagina"
					:value="page"
					class="mx-12"
					v-model="page"
					:length="pageCount"
				></v-pagination>
			</div>
		</v-card>
		<v-dialog v-model="dialog_crear_editar" @click:outside="dialog_crear_editar=false" max-width="65%">
			<v-card>
				<v-card-title> Crear un criterio </v-card-title>
				<v-card-text>
					<v-row>
						<v-col cols="12" md="6" lg="6">
							<v-text-field v-model="cuenta_zoom.usuario" label="Usuario"></v-text-field>
						</v-col>
						<v-col cols="12" md="6" lg="6">
							<v-text-field v-model="cuenta_zoom.correo" label="Correo"></v-text-field>
						</v-col>
						<v-col cols="12" md="4" lg="4">
							<v-text-field v-model="cuenta_zoom.zoom_userid" label="Zoom UserID"></v-text-field>
						</v-col>
						<v-col cols="12" md="4" lg="4">
							<v-text-field v-model="cuenta_zoom.pmi" label="Zoom PMI"></v-text-field>
						</v-col>
						<v-col cols="12" md="2" lg="2">
							<v-select
								item-text="value"
								item-value="value"
								:items="tipo_select"
								v-model="cuenta_zoom.tipo"
								label="Tipo"
							></v-select>
						</v-col>
						<v-col cols="12" md="2" lg="2">
							<v-select
								item-text="text"
								item-value="id"
								:items="estado_select"
								v-model="cuenta_zoom.estado"
								label="Estado"
							></v-select>
						</v-col>
						<v-col cols="12" md="12" lg="12">
							<v-textarea
								clearable
								clear-icon="mdi-close-circle"
								label="API KEY"
								rows="1"
								v-model="cuenta_zoom.api_key"
							></v-textarea>
						</v-col>
						<v-col cols="12" md="12" lg="12">
							<v-textarea
								clearable
								clear-icon="mdi-close-circle"
								label="Client Secret"
								rows="1"
								v-model="cuenta_zoom.client_secret"
							></v-textarea>
						</v-col>
					</v-row>
				</v-card-text>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn @click="(dialog_crear_editar = false), limpiarRecurso()">Cancelar</v-btn>
					<v-btn
						v-if="acciones.crear > 0"
						class="ml-3"
						color="primary"
						@click="guardarRecurso()"
						:loading="loading_btn"
						:disabled="loading_btn"
						>Guardar</v-btn
					>
					<v-btn
						v-if="acciones.editar > 0"
						class="ml-3"
						color="primary"
						@click="editarRecurso()"
						:loading="loading_btn"
						:disabled="loading_btn"
						>Editar</v-btn
					>
				</v-card-actions>
			</v-card>
		</v-dialog>
		<v-dialog v-model="dialog_delete" @click:outside="dialog_delete=false" max-width="35%">
			<v-card>
				<v-card-title>
					<div>¿Está seguro que desea eliminar esta Cuenta de Zoom?</div>
				</v-card-title>

				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn small> Cancelar </v-btn>

					<v-btn small> Si, hazlo </v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog>
	</v-container>
</template>

<script>
export default {
	data() {
		return {
			loading_btn: false,
			dialog_crear_editar: false,
			loading_table: true,
			cuentas_zoom: [],
			items_per_page: 15,
			dialog_delete: false,
			estado_select: [
				{ text: "ACTIVO", id: 1 },
				{ text: "INACTIVO", id: 0 },
			],
			tipo_select: [{ value: "PRO" }, { value: "BUSINESS" }],
			headers: [
				{ text: "Nombre", value: "usuario" },
				{ text: "Correo", value: "correo" },
				{ text: "Tipo", value: "tipo" },
				{ text: "Estado", value: "estado" },
				{ text: "Acciones", value: "editar", align: "center", class: "mr-5" },
			],
			pageCount: 1,
			page: 1,
			cuenta_zoom: {
				id: "",
				usuario: "",
				correo: "",
				tipo: "",
				zoom_userid: "",
				pmi: "",
				api_key: "",
				client_secret: "",
				estado: "",
			},
			tipo_alert: "info",
			message_alert: "",
			alert: false,
			acciones: {
				crear: 0,
				editar: 0,
			},
		};
	},

	created() {
		let vue = this;
		vue.getInitialData();
	},
	methods: {
		getInitialData(page) {
			let vue = this;
			vue.loading_table = true;
			axios
				.get("/cuentas_zoom/getInitialData?page=" + vue.page)
				.then((res) => {
					vue.cuentas_zoom = res.data.data;
					vue.pageCount = res.data.total_paginas;
					vue.loading_table = false;
				})
				.catch((error) => {
					vue.loading_table = false;
					vue.show_alert("error", "Error de servidor.");
				});
		},
		cambiar_pagina(page) {
			let vue = this;
			vue.getInitialData(page);
		},
		crearRecurso() {
			let vue = this;
			vue.dialog_crear_editar = true;
			vue.acciones.crear = 1;
			vue.acciones.editar = 0;
		},

		editarCuentaZoom(item) {
			let vue = this;
			vue.dialog_crear_editar = true;
			vue.acciones.crear = 0;
			vue.acciones.editar = 1;

			vue.cuenta_zoom.id = item.id;
			vue.cuenta_zoom.usuario = item.usuario;
			vue.cuenta_zoom.correo = item.correo;
			vue.cuenta_zoom.tipo = item.tipo;
			vue.cuenta_zoom.zoom_userid = item.zoom_userid;
			vue.cuenta_zoom.pmi = item.pmi;
			vue.cuenta_zoom.api_key = item.api_key;
			vue.cuenta_zoom.client_secret = item.client_secret;
			vue.cuenta_zoom.estado = item.estado;
		},
		limpiarRecurso() {
			let vue = this;
			vue.cuenta_zoom.id = "";
			vue.cuenta_zoom.usuario = "";
			vue.cuenta_zoom.correo = "";
			vue.cuenta_zoom.tipo = "";
			vue.cuenta_zoom.zoom_userid = "";
			vue.cuenta_zoom.pmi = "";
			vue.cuenta_zoom.api_key = "";
			vue.cuenta_zoom.client_secret = "";
			vue.cuenta_zoom.estado = "";
		},
		guardarRecurso() {
			let vue = this;
			vue.loading_btn = true;
			vue.loading_table = true;
			vue.cuenta_zoom.id = "";

			axios
				.post("/cuentas_zoom/create", vue.cuenta_zoom)
				.then((res) => {
					// console.log(res);
					vue.dialog_crear_editar = false;
					vue.loading_btn = false;
					vue.loading_table = false;
					vue.show_alert("success", res.data.msg);
				})
				.catch((error) => {
					vue.loading_table = false;
					vue.dialog_crear_editar = false;
					vue.loading_btn = false;

					vue.show_alert("error", "Error de servidor.");
				});
			vue.getInitialData();
		},
		editarRecurso() {
			let vue = this;
			vue.loading_btn = true;
			vue.loading_table = true;
			axios
				.post("/cuentas_zoom/edit", vue.cuenta_zoom)
				.then((res) => {
					// console.log(res);
					vue.dialog_crear_editar = false;
					vue.loading_btn = false;
					vue.loading_table = false;
					vue.show_alert("success", res.data.msg);
				})
				.catch((error) => {
					vue.loading_table = false;
					vue.dialog_crear_editar = false;
					vue.loading_btn = false;

					vue.show_alert("error", "Error de servidor.");
				});
			vue.getInitialData();
		},
		generarToken(cuenta_zoom_id) {
			let vue = this;
			vue.loading_table = true;

			axios
				.post("/cuentas_zoom/generarToken", { key: cuenta_zoom_id })
				.then((res) => {
					vue.loading_table = false;
					vue.show_alert("success", res.data.msg);
				})
				.catch((error) => {
					vue.loading_table = false;

					vue.show_alert("error", "Error de servidor.");
				});
			vue.getInitialData();
		},
		eliminar(item) {
			let vue = this;
			vue.dialog_delete = true;
		},
		show_alert(tipo, message) {
			let vue = this;
			vue.tipo_alert = tipo;
			vue.message_alert = message;
			vue.alert = true;
			setTimeout(() => {
				vue.alert = false;
			}, 6500);
		},
	},
};
</script>