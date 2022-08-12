<template>
	<div>
		<v-row style="padding: 10px 0px 10px 0px !important" v-if="criterion.input_type == 'Fecha'">
			<v-col cols="12" md="2" lg="2" class="p-0 vertical-align">
				<div class="label-tipo_criterio">{{ criterion.name }}</div>
			</v-col>
			<v-col cols="12" md="1" lg="1" class="p-0 vertical-align">
				<div class="label-fecha">Rangos:</div>
			</v-col>

			<v-col cols="12" md="2" lg="2" class="p-0 vertical-align">
				<date-picker
					confirm
					confirm-text="Agregar rango"
					attach
					v-model="value1"
					type="date"
					range
					placeholder="Seleccione el rango de fechas"
					:lang="lang"
					@confirm="agregarRango()"
					style="width: 100% !important"
					value-type="YYYY-MM-DD"
				></date-picker>
			</v-col>
			<v-col cols="12" md="7" lg="7" class="p-0 vertical-align">
				<default-autocomplete
					attach
					dense
					outlined
					color="#796aee"
					hide-details="auto"
					:menu-props="{ top: true, offsetY: true }"
					return-object
					multiple
					chips
					:items="criterion.rangos_seleccionados"
					v-model="criterion.rangos_seleccionados"
					item-text="name"
					item-value="curricula_criterion_id"
				>
					<template v-slot:selection="{ item, index }">
						<v-chip
							style="font-size: 0.9rem !important; color: white !important"
							color="#796aee"
							v-if="index < 3"
							small
						>
							{{ item.name }}
						</v-chip>
						<span v-if="index === 3" class="grey--text caption">
							(+{{ criterion.rangos_seleccionados.length - 3 }} seleccionado{{
								criterion.rangos_seleccionados.length - 3 > 1 ? "s" : ""
							}})
						</span>
					</template>
				</default-autocomplete>
			</v-col>
			<v-col cols="12" md="2" lg="2" class="p-0 vertical-align"> </v-col>

			<v-col cols="12" md="1" lg="1" class="p-0 vertical-align">
				<div class="label-fecha">Fechas:</div>
			</v-col>
			<v-col cols="12" md="9" lg="9" class="p-0 vertical-align">
				<default-autocomplete
					v-model="criterion.values_selected"
					:items="criterion.values"
					attach
					dense
					outlined
					color="#796aee"
					hide-details="auto"
					clear-icon="mdi-cancel"
					placeholder="Escriba un texto para filtrar los criterios"
					item-text="name"
					item-value="id"
					multiple
					return-object
					:search-input.sync="search"
					:loading="criterion.loading"
					no-data-text="No hay criterios disponibles."
					chips
				>
					<template v-slot:selection="{ item, index }">
						<v-chip
							style="font-size: 0.9rem !important; color: white !important"
							color="#796aee"
							v-if="index < 5"
							small
						>
							{{ item.name }}
						</v-chip>
						<span v-if="index === 5" class="grey--text caption">
							(+{{ criterion.values_selected.length - 5 }} seleccionado{{
								criterion.values_selected.length - 5 > 1 ? "s" : ""
							}})
						</span>
					</template>
				</default-autocomplete>
			</v-col>
		</v-row>

		<v-row style="padding: 10px 0px 10px 0px !important" v-else>
		<!-- 	<v-col cols="12" md="2" lg="2" class="p-0 vertical-align">
				<div class="label-tipo_criterio">{{ criterion.name }}</div>
			</v-col> -->
			<v-col cols="12" md="12" lg="12" class="p-0 vertical-align">
				<!-- <default-autocomplete
					v-model="criterion.values_selected"
					:items="criterion.values"
					attach
					dense
					outlined
					color="#796aee"
					hide-details="auto"
					clear-icon="mdi-cancel"
					placeholder="Escriba un texto para filtrar los criterios X"
					item-text="name"
					item-value="id"
					multiple
					return-object
					:search-input.sync="search"
					:loading="criterion.loading"
					no-data-text="No hay criterios que coincidan con el filtro, prueba con otro."
					chips
				>
					<template v-slot:selection="{ item, index }">
						<v-chip
							style="font-size: 0.9rem !important; color: white !important"
							color="#796aee"
							v-if="index < 3"
							small
						>
							{{ item.name }}
						</v-chip>
						<span v-if="index === 3" class="grey--text caption">
							(+{{ criterion.values_selected.length - 3 }} seleccionado{{
								criterion.values_selected.length - 3 > 1 ? "s" : ""
							}})
						</span>
					</template>
				</default-autocomplete> -->

				<DefaultAutocomplete
              dense
              :label="criterion.name"
              v-model="criterion.values_selected"
              :items="criterion.values"
              multiple
              item-text="value_text"
              item-id="id"
              :count-show-values="4"
         />

			</v-col>
		</v-row>
	</div>
</template>

<script>
  
  import DatePicker from "vue2-datepicker";
  import "vue2-datepicker/index.css";
  import lang from "./../../plugins/lang_datepicker";

  export default {
  	components: { DatePicker },
  	props: ["criterion"],
  	data() {
  		return {
  			search: null,
  			debounce: null,
  			value1: [new Date().toISOString().substr(0, 10), new Date().toISOString().substr(0, 10)],
  			lang: lang,
  			tipo_seleccion: [
  				{ id: 1, text: "Por rango" },
  				{ id: 2, text: "Por fechas" },
  				{ id: 3, text: "Ambos" },
  			],
  		};
  	},
  	mounted() {
  		let vue = this;
  	},
  	watch: {},
  	methods: {
  		eliminarRangoFecha(index) {
  			let vue = this;
  			vue.criterion.rangos_seleccionados.splice(index, 1);
  		},
  		agregarRango() {
  			let vue = this;

  			vue.criterion.rangos_seleccionados.push({
  				curricula_criterion_id: `n-${vue.criterion.rangos_seleccionados.length + 1}`,
  				nombre: `${vue.value1[0]} - ${vue.value1[1]}`,
  				f_inicio: vue.value1[0],
  				f_fin: vue.value1[1],
  			});
  			let data = {
  				rangos_seleccionados: vue.criterion.rangos_seleccionados,
  				tipo_criterio_id: vue.criterion.tipo_criterio_id,
  			};
  			vue.$emit("agregarRangoItems", data);
  		},

  	},
  };
</script>

<style>
  .mx-input {
  	height: 40px !important;
  	border-color: #dee2e6 !important;
  	border-radius: 0px !important;
  	color: black !important;
  }
  .mx-calendar-content .cell.active {
  	color: #fff;
  	background-color: #796aee !important;
  }
  .mx-calendar-content .cell.in-range,
  .mx-calendar-content .cell.hover-in-range {
  	color: #fff !important;
  	background-color: #aba2f1 !important;
  }
  .mx-datepicker-main {
  	/* left: calc(100% - 850px) !important; */
  }
  .label-fecha {
  	height: 39px;
  	border-radius: 0;
  	border-color: #dee2e6;
  	border-width: thin;
  	width: 100%;
  	border-style: solid;
  	display: flex;
  	justify-content: flex-end;
  	align-items: center;
  	font-size: 16px;
  	padding-right: 10px;
  }
  .label-tipo_criterio {
  	height: 39px;
  	border-radius: 0;
  	border-color: #dee2e6;
  	border-width: thin;
  	width: 100%;
  	border-style: solid;
  	display: flex;
  	justify-content: start;
  	align-items: center;
  	font-size: 16px;
  	padding-left: 10px;
  	text-transform: uppercase;
  }
</style>
