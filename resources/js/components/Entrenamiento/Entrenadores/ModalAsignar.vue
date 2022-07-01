<template>
	<v-dialog :max-width="width" v-model="value" @click:outside="closeModal">
		<v-card>
			<v-card-title class="headline">
				Asignar usuarios <v-spacer></v-spacer>
				<v-btn icon :ripple="false" @click="closeModal"><v-icon>mdi-close</v-icon></v-btn>
			</v-card-title>
			<v-card-text>
				<v-row>
				<v-col cols="12" md="12" lg="12">
					<v-alert border="top" colored-border type="info" elevation="2" class="pl-4">
						<div class="alert-title"><strong>Tener en cuenta</strong></div>
						<li class="m-0">
							En caso de no procesarse filas del Excel, se descargará un reporte que especifique la razón.
						</li>
					</v-alert>
				</v-col>
			</v-row>
				<v-row>
					<v-col cols="12" md="12" lg="12" class="d-flex justify-start">
						<a class="pt-2 pr-8" href="/templates/Plantilla-Asignar_Entrenador.xlsx"
							>Descargar plantilla</a
						>
						<v-file-input
							show-size
							label="Suba el archivo"
							v-model="file"
							color="#796aee"
							hide-details="auto"
							dense
							outlined
							hide-input
							prepend-icon="mdi-file-upload"
							@change="subirExcel()"
							accept=".xls,.xlsx"
						>
						</v-file-input>
					</v-col>
				</v-row>
				<v-row>
					<v-col cols="12" md="12" lg="12"> </v-col>
				</v-row>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>


<script>
  import XLSX from "xlsx";

  export default {
  	props: ["value", "width"],
  	data() {
  		return {
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
				"Z",
			],
  		};
  	},
  	methods: {
  		closeModal() {
  			let vue = this;
  			vue.$emit("close");
  		},
  		confirm() {
  			let vue = this;
  			vue.$emit("onConfirm");
  		},
  		cancel() {
  			let vue = this;
  			vue.$emit("onCancel");
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
  				.post("/entrenamiento/entrenador/asignar_masivo", data)
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
</style>
