<template>
	<div>
		<v-row>
			<v-col cols="12" class="px-5 mx-2">
				<div class="d-flex justify-content-between">
					<span>
						Lista de candidatos con su número de votos en el proceso de votación.
					</span>
					<DefaultButton 
						class="mr-5"
						label="Estado de votación"
						@click="openFormModal(modalVotacionStatusOptions, null, 'status', 
						`Estado de votación al ${CampaignProvide.campaign.porcent} %`)" 
					/>
				</div>
			</v-col>
		</v-row>

		<v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>

                    <v-col cols="5" offset="3">
                    	<v-row>
                    		<v-col cols="6" offset="6">
		                        <DefaultSelect
		                            clearable dense
		                            :items="selects.criterio_values"
		                            v-model="filters.criterio_value_id"
		                            :label="labelSelectCriterio"
		                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
		                            item-text="name"
		                        />
                    		</v-col>
                    	</v-row>
                    </v-col>
                </v-row>
            </v-card-text>
            
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
            />

            <!-- VOTACONES STATUS -->
			<VotacionesVotationStatusModal
				:ref="modalVotacionStatusOptions.ref"
	            :options="modalVotacionStatusOptions"
    	        @onConfirm="closeFormModal(modalVotacionStatusOptions)"
			 />

        </v-card>
	</div>
</template>	

<script>

	import VotacionesVotationStatusModal from './VotacionesVotationStatusModal.vue';

	export default {
		name: 'VotPostulates',
		inject: ['CampaignProvide'],
		components: { VotacionesVotationStatusModal },
		data() {
			return {
				base_endpoint:`/votaciones/${this.CampaignProvide.campaign.id}/postulacion`, // para los criterio_values
				dataTable: {
					endpoint:`/votaciones/${this.CampaignProvide.campaign.id}/votacion/search`,
					ref: 'VotacionesTable',
					headers: [
						{text: "Nombres y Apellidos", value: "fullname", align: 'left', sortable: false },
						{text: "Valor de criterio", value: "criterio_value", align: 'left', sortable: false },
						{text: "Nro documento", value: "document", sortable: false},
						{text: "Votos", value: "votes", align: 'center',  sortable: false},
					],
				},
				selects: {
					criterio_values:[],
				},
				filters: {
					q: '',
					subworkspace_id: null,
					active: null,
					criterio_value_id: null
				},
				modalVotacionStatusOptions: {
	                ref: 'VotacionStatusModal',
	                open: false,
	                confirmLabel: 'Aceptar',
	                subTitle:'',
	                hideCancelBtn: true,
            	},
				itemSelectCriterio: [],
				labelSelectCriterio: 'Cargando criterio ...',
			}
		},
		computed:{
			availableStageVotation() {
				const vue = this;
				const { stage_votation } = vue.CampaignProvide.campaign;
				return (stage_votation === null) ? false : true;
			}
		},
		methods:{
			//  ==== VOTATIONS AVAILABLE ====
			getSelectRequirement() {
				const vue = this;

				vue.$http.get(`${vue.base_endpoint}/requirements?stage=VOTERS`)
					.then((res) => {

					const { data } = res.data;

					vue.filters.criterio_id = data.criterio.id;
					vue.labelSelectCriterio = `Seleccione ${ data.criterio.name.toLowerCase()}`;

					if(data.criterio_values.length) {
						vue.selects.criterio_values = data.criterio_values;
					}
				});
			}
		},
		mounted() {
			const vue = this;
			if(vue.availableStageVotation) vue.getSelectRequirement();
		}
	}

</script>
