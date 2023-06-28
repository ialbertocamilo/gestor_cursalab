<template>
	<div>
		<v-row>
			<v-col cols="12" class="px-5 mx-2">
				Lista de postulantes en el proceso de postulación. 
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
                    		<v-col cols="6" v-if="availableStageVotation">
		                        <DefaultSelect
		                            clearable dense
		                            :items="selects.criterio_values"
		                            v-model="filters.criterio_value_id"
		                            :label="labelSelectCriterio"
		                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
		                            item-text="name"
		                        />
                    		</v-col>
                    		<v-col 
                    			cols="6" 
                				:offset="availableStageVotation ? '' : '6' "
                				>
		                        <DefaultSelect
		                            clearable dense
		                            :items="selects.status"
		                            v-model="filters.active"
		                            label="Estado"
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
                :data-object="{ availableStageVotation }"
                @sustent="validateSustents($event)"
            />

	        <!-- === STAGES MODAL === -->
            <VotacionesValidateSustentsModal
				:ref="modalValidateSustentOptions.ref"
	            :options="modalValidateSustentOptions"
    	        @onCancel="closeFormModal(modalValidateSustentOptions)"
	            @onConfirm="closeFormModal(modalValidateSustentOptions, dataTable, filters)"
            />

        </v-card>
	</div>
</template>	

<script>

	import VotacionesValidateSustentsModal from './VotacionesValidateSustentsModal.vue';

	const CRITERIO_HEADERS = [
		{text: "Nombres y Apellidos", value: "fullname", align: 'left', sortable: false },
		{text: "Valor de criterio", value: "criterio_value", align: 'left', sortable: false },
		{text: "Nro documento", value: "document", sortable: false},
		{text: "Total", value: "total", align: 'center' },
		{text: "Aprobados", value: "approveds", align: 'center'},
		{text: "Pendientes", value: "pendings", align: 'center'},
		{text: "Acciones", value: "actions_sustent", align: 'center', sortable: false},
	];
	const NORMAL_HEADERS = [
		{text: "Nombres y Apellidos", value: "fullname", align: 'left', sortable: false },
		{text: "Nro documento", value: "document", sortable: false},
		{text: "Total", value: "total", align: 'center'},
		{text: "Aprobados", value: "approveds", align: 'center'},
		{text: "Pendientes", value: "pendings", align: 'center'},
		{text: "Acciones", value: "actions_sustent", align: 'center', sortable: false},
	];

	export default {
		name: 'VotPostulates',
		inject: ['CampaignProvide'],
		components: {
			VotacionesValidateSustentsModal
		},
		data() {
			return {
				base_endpoint:`/votaciones/${this.CampaignProvide.campaign.id}/postulacion`,
				dataTable: {
					endpoint:`/votaciones/${this.CampaignProvide.campaign.id}/postulacion/search`,
					ref: 'VotacionesTable',
					headers: (this.CampaignProvide.campaign.stage_votation === null) ? NORMAL_HEADERS : CRITERIO_HEADERS,
				},
				selects: {
					modules: [],
					status: [
						{id: null, name: 'Todos'},
						{id: '0', name: 'Sin validar'},
						{id: '1', name: 'Validados'},
					],
					criterio_values:[],
				},
				filters: {
					q: '',
					subworkspace_id: null,
					active: null,
					criterio_value_id: null
				},
 				modalValidateSustentOptions: {
	                ref: 'VotacionValidateSustentsModal',
	                open: false,
	                confirmLabel: 'Guardar',
	                base_endpoint:`/votaciones/${this.CampaignProvide.campaign.id}/postulacion`,
	                contentText:'Aqui puedes habilitar o deshabilitar las etapas de tu campaña.',
	                showCloseIcon: true,
	                hideCancelBtn: true,
	                hideConfirmBtn: true,
	                resource:{
	                	campaign: {
	                		state_postulate_support: this.CampaignProvide.campaign.state_postulate_support,
	                	},
	                	summoned: {
	                		user:{}
	                	}
                	}
            	},
				itemSelectCriterio: [],
				labelSelectCriterio: 'Cargando criterio ...',
			
				// dialogs
				dialogModalPost: { state: false, data: {} },
				dialogModalPostConfirm: { state: false, data: {} }

			}
		},
		computed:{
			availableSearchPostulates() {
				const vue = this;
				return (vue.filters.criterio_id === null);
			},
			availableStageVotation() {
				const vue = this;
				const { stage_votation } = vue.CampaignProvide.campaign;
				return (stage_votation === null) ? false : true;
			},
			availableSupportSustent() {
				const vue = this;
				const { state_postulate_support } = vue.CampaignProvide.campaign;
				return state_postulate_support;
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
			},
			validateSustents(evt){
				const vue = this;

				vue.modalValidateSustentOptions.resource.campaign = vue.CampaignProvide.campaign;
				vue.modalValidateSustentOptions.resource.summoned = evt;

				vue.openFormModal(vue.modalValidateSustentOptions, evt, 'status', 'Validación de postulante');
			}
		},
		mounted() {
			const vue = this;
			if(vue.availableStageVotation) vue.getSelectRequirement();
		}
	}

</script>
