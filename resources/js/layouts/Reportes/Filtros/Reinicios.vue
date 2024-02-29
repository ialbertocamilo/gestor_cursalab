<template>
	<v-main>
		<!-- Resumen del reporte -->
		<ResumenExpand :dialog="infoDialog" @onCancel="infoDialog = false" titulo="Resumen del reporte">
			<!-- <template v-slot:resumen>
				Descarga el registro de reinicios de intentos de evaluación realizados por los
				administradores a los usuarios.
			</template> -->
			<list-item titulo="Módulo" subtitulo="Módulo al que pertenece el usuario" />
			<list-item titulo="Documento, Apellidos y nombres" subtitulo="Datos personales" />
			<list-item titulo="Carrera (Usuario)" subtitulo="Carrera actual en la que se encuentra" />
			<list-item titulo="Ciclo actual (Usuario)" subtitulo="Ciclo actual en la que se encuentra" />
			<list-item titulo="Tipo reinicio" subtitulo="Tipo de reinicio realizado (al tema o curso)" />
			<list-item titulo="Curso" subtitulo="Nombre del curso" />
			<list-item titulo="Tema" subtitulo="Nombre del tema" />
			<list-item
				titulo="Reinicios"
				subtitulo="Cantidad de reinicios realizados (Por tipo de reinicio)"
			/>
			<list-item titulo="Fecha" subtitulo="Fecha en la que se realizó el reinicio" />
			<list-item
				titulo="Administrador responsable"
				subtitulo="Administrador que realizó el reinicio"
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
                	REINICIOS DE USUARIOS
                </p>

                <small>Descarga el registro de reinicios de intentos de evaluación realizados por los
				administradores a los usuarios.</small>

            </v-alert>

			<v-divider class="mt-7"></v-divider>

			<form @submit.prevent="exportRenicios" class="">
				<!-- Admins -->
				<v-row class="justify-content-center">
					<v-col cols="12" md="6">
						<b-form-text text-variant="muted">Administrador</b-form-text>
						<select v-model="admin" class="form-control">
							<option value="">- Seleccionar Administrador -</option>
							<option value="ALL">[TODOS]</option>
							<option v-for="(item, index) in Admins" :key="index" :value="item.id">
								{{ item.name }}
							</option>
						</select>
					</v-col>

					<v-col cols="12" md="6">
						<b-form-text text-variant="muted">Tipo</b-form-text>
						<select v-model="tipo" class="form-control" :disabled="!admin || admin == 'ALL'">
							<option value="">- Seleccionar Tipo de reinicio -</option>
							<option value="ALL">[TODOS]</option>
							<option value="por_tema">Reinicios por tema</option>
							<option value="por_curso">Reinicios por curso</option>
							<option value="total">Reinicios totales</option>
						</select>
					</v-col>
				</v-row>

				<v-row class="justify-content-center">

				<v-col cols="12" md="6">
					<b-form-text text-variant="muted">Fecha inicial</b-form-text>
					<div class="input-group">
						<b-form-datepicker
							v-model="start"
							button-only
							button-variant="light"
							locale="es-PE"
							aria-controls="date-start"
							today-button
							label-today-button="Hoy"
							reset-button
							label-reset-button="Reiniciar"
							selected-variant="danger"
						></b-form-datepicker>
						<input
							type="date"
							autocomplete="off"
							class="datepicker form-control hasDatepicker"
							v-model="start"
						/>
					</div>
				</v-col>
				<v-col cols="12" md="6">
					<b-form-text text-variant="muted">Fecha final</b-form-text>
					<div class="input-group">
						<b-form-datepicker
							v-model="end"
							button-only
							button-variant="light"
							locale="es-PE"
							aria-controls="date-start"
							today-button
							label-today-button="Hoy"
							reset-button
							label-reset-button="Reiniciar"
							selected-variant="danger"
						></b-form-datepicker>
						<input
							type="date"
							autocomplete="off"
							class="datepicker form-control hasDatepicker"
							v-model="end"
						/>
					</div>
				</v-col>

				</v-row>

				<div class="my-4 d-flex justify-content-center">
					<v-btn color="primary" type="submit" class="col-4">
						<v-icon class="mr-2" small>mdi-download</v-icon>
						Descargar
					</v-btn>

				</div>

	        	<v-divider class=""></v-divider>
			</form>

			<div  class="d-flex justify-content-center py-10">
				<v-img max-width="300" class="text-center" src="/img/guides/restarts.svg"></v-img>
			</div>
		</v-card-text>
	</v-main>
</template>

<script>
import ListItem from "./partials/ListItem.vue";
import ResumenExpand from "./partials/ResumenExpand.vue";
export default {
	components: { ResumenExpand, ListItem },
	props: {
		Admins: Array,
		API_REPORTES: ""
	},
	data() {
		return {
			Tipos: [],
			admin: "",
			tipo: "",
			start: "",
			end: "",
			infoDialog: false,
			// API_URL: process.env.MIX_API_REPORTES,
		};
	},
	methods: {
		exportRenicios() {
			$("#pageloader").fadeIn();
			let params = {
				admin: this.admin,
				tipo: this.tipo,
				start: this.start,
				end: this.end
			};
			axios
				.post(this.API_REPORTES + "reinicios", params)
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
		}
	}
};
</script>

<style></style>
