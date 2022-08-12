<template>
	<!-- <v-card elevation="0" style="padding-left: 14px !important; padding-right: 14px !important"> -->
	<div class="px-4">

<!-- 		<v-dialog persistent max-width="400" v-model="dialog_eliminar">
			<v-card>
				<v-card-title class="headline"> Eliminar currícula </v-card-title>
				<v-card-text>
					¿Está seguro de eliminar esta currícula? <br />
					Tenga en cuenta que este cambio no se puede revertir.</v-card-text
				>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn small  outlined class="font-bold" color="#796aee" @click="dialog_eliminar = false">
						Cancelar
					</v-btn>
					<v-btn small color="#727b84" class="txt-white-bold" @click="borrarCurricula()">
						Aceptar
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog> -->
<!-- 
		<v-dialog persistent max-width="400" v-model="dialog_guardar">
			<v-card>
				<v-card-title class="headline"> Guardar currícula </v-card-title>
				<v-card-actions>
					<v-spacer></v-spacer>
					<v-btn small color="#727b84" class="txt-white-bold" @click="dialog_guardar = false">
						Cancelar
					</v-btn>
					<v-btn small outlined class="font-bold" color="#796aee" @click="guardarCurricula()">
						Aceptar
					</v-btn>
				</v-card-actions>
			</v-card>
		</v-dialog> -->


		<!-- <v-row class="pt-2 pb-4"> -->

		<v-col cols="12" md="12" lg="12">
		
			<DefaultAutocomplete
			return-object
				dense
				label="Criterios"
				v-model="segment.criteria_selected"
				:items="new_criteria"
				multiple
				item-text="name"
				item-id="id"
				:count-show-values="4"
		    />

		</v-col>

	<!-- 		<v-col cols="12" md="2" lg="2" class="vertical-align justify-end">
				<v-btn color="#796aee" class="txt-white-bold" @click="dialog_guardar = true">
					<v-icon class="mr-1">mdi-content-save</v-icon> GUARDAR
				</v-btn>
			</v-col> -->

<!-- 			<v-col cols="12" md="1" lg="1" class="vertical-align justify-end">
				<v-btn class="mr-1" color="#796aee" outlined icon @click="dialog_eliminar = true">
					<v-icon>mdi-trash-can</v-icon>
				</v-btn>
			</v-col> -->

		<!-- </v-row> -->

<!-- 		<v-row justify="center">
			<v-overlay
				class="custom-overlay"
				:absolute="absolute"
				:value="segment.loading"
				color="#796aee"
				opacity="0.75"
			>
				<div
					style="display: flex; flex-direction: column; align-items: center"
					class="text-center justify-center overlay-segment"
				>
					<v-progress-circular indeterminate size="64"></v-progress-circular>
					<p class="text-h6" v-if="loading_guardar">
						Este proceso puede tomar más de un minuto, espere por favor.
					</p>
					<p class="text-h6" v-if="loading_guardar">No actualice la página.</p>
				</div>
			</v-overlay>
		</v-row> -->

			<v-col cols="12" md="12" lg="12">

				<segment-values
					v-for="(criterion, index) in segment.criteria_selected"
					:key="index"
					:criterion="criterion"
					@agregarRangoItems="agregarRango($event)"
				/>
			</v-col>
			<!-- :criteria="criteria" -->
			<!-- :segment_id="segment.id" -->
			<!-- :curso_id="segment.curso_id" -->

		<!-- <v-divider></v-divider> -->
		<!-- {{ addDissabledChild }} -->
	</div>
</template>

<script>
  import SegmentValues from "./SegmentValues";
  export default {
  	components: {
  		SegmentValues,
  	},
  	props: ["segment", 'criteria', 'options'],
  	data() {
  		return {
  			new_criteria: [],
  			absolute: true,
  			loading_guardar: false,
  			dialog_eliminar: false,
  			dialog_guardar: false,
  		};
  	},
  	computed: {
  		
  		// addDissabledChild() {
  		// 	let vue = this;
  		// 	let map = vue.segment.criteria_selected.map((obj) => obj.id);
  		// 	let filter = vue.segment.criteria.filter((tc) => map.includes(tc.id));
  		// 	filter.forEach((tc) => {
  		// 		tc.disabled_child = true;
  		// 	});
  		// },

  		// detalleOrderByCountCriterios() {
  		// 	let vue = this;
  		// 	return vue.segment.criteria_selected.sort(function (a, b) {
  		// 		var keyA = a.criterios_count,
  		// 			keyB = b.criterios_count;
  		// 		if (keyA < keyB) return -1;
  		// 		if (keyA > keyB) return 1;
  		// 		return 0;
  		// 	});
  		// },
  	},
  	mounted() {
  		let vue = this;
  		vue.segment.loading = true;
  		setTimeout(() => {
  			vue.segment.loading = false;
  		}, 1200);

  		vue.loadData()

  		// let x = Object.assign({}, vue.criteria)

  		// console.log('vue.criteria mounted')
  		// console.log(vue.criteria)
  		// vue.new_criteria = x
  	},
  	methods: {
  		agregarRango(data) {
  			let vue = this;
  			let criterion = vue.segment.criteria.find(
  				(obj) => obj.id == data.id
  			);
  			criterion.rangos_seleccionados = data.rangos_seleccionados;
  		},
  		async loadData(resource) {
            let vue = this
            // vue.errors = []

            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                let _data = data.data

                // vue.segments = _data.segments
                vue.new_criteria = _data.criteria
            })

            return 0;
        },
  		// borrarCurricula() {
  		// 	let vue = this;
  		// 	if (vue.segment.segment_id[0] == "n") {
  		// 		vue.$notification.success(`Curricula eliminada correctamente.`, {
  		// 			timer: 10,
  		// 			showLeftIcn: false,
  		// 			showCloseIcn: true,
  		// 		});
  		// 		vue.$emit("borrar_segment");
  		// 		return;
  		// 	}
  		// 	vue.dialog_eliminar = false;
  		// 	vue.segment.loading = true;
  		// 	vue.loading_guardar = true;

  		// 	axios
  		// 		.delete(`/segment/eliminar/${vue.segment.segment_id}`)
  		// 		.then((res) => {
  		// 			vue.$notification.success(`${res.data.msg}`, {
  		// 				timer: 10,
  		// 				showLeftIcn: false,
  		// 				showCloseIcn: true,
  		// 			});
  		// 			vue.segment.loading = false;
  		// 			vue.loading_guardar = false;

  		// 			vue.$emit("borrar_segment");
  		// 		})
  		// 		.catch((err) => {
  		// 			console.log(err);
  		// 		});
  		// },
  		async guardarCurricula() {
  			let vue = this;
  			vue.segment.loading = true;
  			vue.loading_guardar = true;
  			vue.dialog_guardar = false;
  			let data = {
  				curso_id: vue.segment.curso_id,
  				segment_id: vue.segment.segment_id,
  				criteria: vue.segment.criteria_selected,
  			};
				let hay_criterios_vacios = false;
				if(data.criteria.length>0){
					console.log(data.criteria);
					data.criteria.forEach(e => {
						if(e.input_type == 'Fecha'){
							if(e.criterios_seleccionados.length==0 && e.rangos_seleccionados.length==0){
								hay_criterios_vacios = true; 
							}
						}else{
							if(e.criterios_seleccionados.length==0){
								hay_criterios_vacios = true; 
							}
						}
					});
				}else{
					hay_criterios_vacios = true; 
				}
				if(!hay_criterios_vacios){
					await axios
						.post(`/segment/guardarCurricula`, data)
						.then((res) => {
							vue.$notification.success(`${res.data.msg}`, {
								timer: 10,
								showLeftIcn: false,
								showCloseIcn: true,
							});
							vue.segment.segment_id = res.data.segment_id;
							vue.segment.loading = false;
							vue.loading_guardar = false;
							vue.$emit('update_criteria')
						})
	  
						.catch((err) => {
							console.log(err);
							vue.segment.loading = false;
							vue.loading_guardar = false;
						});
				}else{
					vue.segment.loading = false;
	  				vue.loading_guardar = false;
					vue.$notification.warning('No se pueden enviar segments vacías', {
						timer: 10,
						showLeftIcn: false,
						showCloseIcn: true,
					});
				}
  		},
  	},
  };
</script>
