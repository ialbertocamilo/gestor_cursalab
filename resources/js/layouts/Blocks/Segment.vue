<template>
	<div class="px-4">

		<v-dialog persistent max-width="400" v-model="dialog_eliminar">
			<v-card>
				<v-card-title class="default-dialog-title"> Eliminar bloque </v-card-title>
				<v-card-text class="py-5">
					¿Está seguro de eliminar esta bloque de segmentación? <br />
					Esta acción no puede revertirse.
				</v-card-text>
				<v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
					<DefaultModalActionButton
                    	@cancel="dialog_eliminar = false"
                    	@confirm="borrarBloque(segment)"
                    />
				</v-card-actions>
			</v-card>
		</v-dialog>


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
    
    	<v-divider class="mx-3"/> 

		<v-col cols="12" md="12" lg="12">

			<segment-values
				v-for="(criterion, index) in segment.criteria_selected"
				:key="index"
				:criterion="criterion"
				@agregarRangoItems="agregarRango($event)"
			/>

			<v-divider class=""/> 

			<v-col cols="12" md="12" lg="12" class="d-flex justify-content-center">
				<v-btn class="mr-1" color="#796aee" outlined icon @click="dialog_eliminar = true">
					<v-icon>mdi-trash-can</v-icon>
				</v-btn>
			</v-col>
		</v-col>

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
  		borrarBloque(segment) {
  			let vue = this;

            let key = segments.find(obj => {
			  return obj.id === segment.id
			});

			delete segments[key]

  			// if (vue.segment.segment_id[0] == "n") {
  			// 	vue.$notification.success(`Bloque eliminado correctamente.`, {
  			// 		timer: 10,
  			// 		showLeftIcn: false,
  			// 		showCloseIcn: true,
  			// 	});
  			// 	vue.$emit("borrar_segment");
  			// 	return;
  			// }
  			// vue.dialog_eliminar = false;
  			// vue.segment.loading = true;
  			// vue.loading_guardar = true;

  			// axios
  			// 	.delete(`/segment/eliminar/${vue.segment.segment_id}`)
  			// 	.then((res) => {
  			// 		vue.$notification.success(`${res.data.msg}`, {
  			// 			timer: 10,
  			// 			showLeftIcn: false,
  			// 			showCloseIcn: true,
  			// 		});
  			// 		vue.segment.loading = false;
  			// 		vue.loading_guardar = false;

  			// 		vue.$emit("borrar_segment");
  			// 	})
  			// 	.catch((err) => {
  			// 		console.log(err);
  			// 	});
  		},
  	},
  };
</script>
