<template>
	<section class="section-list">
		<v-card flat class="elevation-0 mb-4">
			<v-card-title>
                Campaña: {{ campaign_id ? 'Editar' : 'Crear' }}

                <!-- {{ dat.currGeneralConfig }} -->
                <v-spacer/>

                <DefaultButton 
                    :label="'Listar campañas'"
                    @click="openCRUDPage(`/votaciones`)"
                />

			</v-card-title>
		</v-card>

	<!-- <v-card-title class="d-flex primary text-white">
		<span :class="`fas ${currentTitleForm.icon} f-md mx-2`"></span>
		<span v-text="currentTitleForm.title"></span>
	</v-card-title> -->

			<v-form ref="form" v-model="valid" lazy-validation>
				<v-expansion-panels v-model="panel" multiple :accordion="true" :hover="true">

					<!-- ==== REQUIRED GENERAL CONFIG ==== -->
					<v-expansion-panel id="panel-general" class="no-shadow">
						<v-expansion-panel-header color="primary" class="text-white">
							<span class="font-weight-bold">General</span>
							<template v-slot:actions>
								<v-icon color="white">$expand</v-icon>
							</template>
						</v-expansion-panel-header>

						<v-expansion-panel-content class="pt-2">
						<!-- {{ dat.currGeneralConfig }} -->
							<vue-alert-error :errors="val.currGeneralConfig.errors">
								Por favor verifica esta sección y completa los campos correctamente.
							</vue-alert-error>

							<FrmGeneralConfig :data="dat.currGeneralConfig" 
															  :mode="currentStateEdit" 
																:badge="currIndex"
																@data="currentManageData('currGeneralConfig', $event)"
																@valid="currentValidData('currGeneralConfig', $event)"/>
																<!-- badge: currIndex - sirve para validar insignias-->
						</v-expansion-panel-content>
					</v-expansion-panel>
					<!-- ==== REQUIRED GENERAL CONFIG ==== -->

					<!-- ==== REQUIRED MODALIDADES ==== -->
					<v-expansion-panel id="panel-stages" class="no-shadow">
						<v-expansion-panel-header color="primary" class="text-white">
							<div class="d-flex justify-content-between">
								<span class="font-weight-bold">Modalidad</span>
								<DefaultInfoTooltip :left="true" text="Define la cantidad de etapas que tendrá la campaña." />
							</div>
							<template v-slot:actions>
								<v-icon color="white">$expand</v-icon>
							</template>
						</v-expansion-panel-header>

						<v-expansion-panel-content class="pt-2">
							<!-- {{ dat.currStages }} -->

							<v-row>
								<v-col cols="12" class="py-0"> 
									<vue-alert-error :errors="val.currStages.errors">
										Por favor verifica esta sección y completa los campos correctamente.
									</vue-alert-error>
								</v-col>
								<v-col cols="3" class="pl-5">
									<div>
										<v-row class="flex-column">
											<div class="d-flex flex-column gap-md">
												<v-card v-for="(mod, index) of arrayModalities" :key="index"
														class="pointer card-modality rounded-md" 
														:class="{'border-solid-primary': index === currIndex}" 
														:disabled="currentStateEdit && index !== currIndex"
														@click="selectModality(index)">

													<v-card-title 
														class="justify-content-center" 
														:class="index === currIndex && 'text-primary-sub'" 
														v-text="mod.title"/>
													<v-card-text 
														class="text-center"
														:class="index === currIndex && 'text-primary-sub'" 
														v-text="mod.text"/>
													
													<!-- INFO MODALIDADES -->
													<div v-show="index === currIndex" class="px-3 pb-3">
														<span class="ml-1 mb-2">Los usuarios podran: </span>
														<ol class="p-0 ml-5">
															<li class="mb-2" v-for="text of mod.info" :key="text">
																<span :class="index === currIndex && 'text-primary-sub'" 
																	  v-text="text.split(':')[0] + ':'"></span>
																<span class="d-inline" v-text="text.split(':')[1]"></span>
															</li>
														</ol>
													</div>
													<!-- INFO MODALIDADES -->
												</v-card>
											</div>
										</v-row>
									</div>
								</v-col>
								<v-col cols="9">
									<section class="border-solid-primary rounded-md py-0 h-100">
										
										<div v-show="currModality.value.length === 0" class="w-100 h-100">
											<div class="h-100 d-flex justify-content-center align-items-center">
												<div class="text-center">
													<span class="mdi mdi-file-search text-primary-sub fa-4x"></span>
												 	<p class="m-0"> Selecciona una de las cuatro modalidades para tu campaña.</p>
												 	<p class="m-0"> Recuerda elegir una modalidad para esta campaña, la modalidad te permitirá ver el alcance de la campaña y sus etapas.</p>
												</div>
											</div>
										</div>

										<!-- COMP CONTENIDO -->
										<v-expansion-panel v-if="currModality.value.includes(0)" id="panel-contents" class="mt-0 rounded-md">
											<v-expansion-panel-header class="text-primary-sub grey lighten-5">
												<span class="font-weight-bold">Contenido</span>
											</v-expansion-panel-header>
											<v-expansion-panel-content class="pt-2">
												<!-- {{ dat.currContents }} -->
												<vue-alert-error :errors="val.currContents.errors">
														Por favor verifica esta sección y completa los campos correctamente
												</vue-alert-error>
												<!-- <FrmAddContent v-model="data.currContents" /> -->
												<component :is="currModality.value.includes(0) ? 'FrmAddContent' : null" 
															:data="dat.currContents"
														    :mode="currentStateEdit" 
															@data="currentManageData('currContents', $event)"
															@valid="currentValidData('currContents', $event)"></component>		

											</v-expansion-panel-content>
										</v-expansion-panel>

										<!-- COMP POSTULACIONES -->
										<v-expansion-panel v-if="currModality.value.includes(1)" id="panel-postulates" class="mt-0 rounded-md">
											<v-expansion-panel-header class="text-primary-sub grey lighten-5">
												<div>
													<span class="font-weight-bold">Postulantes</span>
												</div>
											</v-expansion-panel-header>
											<v-expansion-panel-content class="pt-2">
												<!-- {{ dat.currPostulates }} -->
												<vue-alert-error :errors="val.currPostulates.errors">
														Por favor verifica esta sección y completa los campos correctamente
												</vue-alert-error>
												<!-- <FrmAddPostulates v-model="data.currPostulates" /> -->
												<component  :is="currModality.value.includes(1) ? 'FrmAddPostulates' : null" 
														  	:data="dat.currPostulates"
															:mode="currentStateEdit" 
															@data="currentManageData('currPostulates', $event)"></component>		
											
											</v-expansion-panel-content>
										</v-expansion-panel>

										<!-- COMP VOTACIONES-->
										<v-expansion-panel v-if="currModality.value.includes(2)" id="panel-voters" class="mt-0 rounded-md">
											<v-expansion-panel-header class="text-primary-sub grey lighten-5">
												<div>
													<span class="font-weight-bold">Votantes</span>
												</div>
											</v-expansion-panel-header>

											<v-expansion-panel-content class="pt-2">	        		
												<!-- {{ dat.currVoters }} -->
													<vue-alert-error :errors="val.currVoters.errors">
														Por favor verifica esta sección y completa los campos correctamente
													</vue-alert-error> 
												<!-- <FrmAddVoters v-model="data.currVoters" /> -->
												<component  :is="currModality.value.includes(2) ? 'FrmAddVoters' : null" 
															:data="dat.currVoters"
															:mode="currentStateEdit" 
															@data="currentManageData('currVoters', $event)"></component>		

											</v-expansion-panel-content>
										</v-expansion-panel>

									</section>
								</v-col>
							</v-row>
						</v-expansion-panel-content>
					</v-expansion-panel>
					<!-- ==== REQUIRED MODALIDADES ==== -->

				</v-expansion-panels>


			</v-form>

		<v-card flat class="elevation-0 mb-4">
			<v-card-actions class="d-flex justify-content-center pb-6">
				<!-- <v-btn text @click="clearCurrentData"> Cancelar </v-btn> -->
				<v-btn color="primary" :loading="disabled" :disabled="disabled" @click="sendCurrentData">
					<!-- <span :class="`fas ${currentTitleForm.icon} mr-2`"></span>  -->
					<span v-text="currentButtonForm.text"></span> 
				</v-btn>
			</v-card-actions>
		</v-card>
	</section>
</template>

<script>
	import VueAlertError from './components/VueAlertError.vue';

	// === components ===
	import FrmGeneralConfig from './forms/FrmGeneralConfig.vue';
	import FrmAddContent from './forms/FrmAddContent.vue';
	import FrmAddPostulates from './forms/FrmAddPostulates.vue';
	import FrmAddVoters from './forms/FrmAddVoters.vue';
	// === components ===

	// === utils ===
	import { checkType } from './utils/UtlValidators.js';
	import { checkValidModules, checkValidEmpty } from './utils/UtlComponents.js';
	import { deepAppendFormData } from './utils/UtlData.js';
	const { isType } = checkType;
	// === utils ===

	// ==== LOCAL DATA ====
	const GROUP_VALIDATIONS = {
		currGeneralConfig: {
			modules:{ text: 'Por favor seleccione módulo.', required: true },
			title:{ text: 'Por favor ingrese nombre a la campaña.', required: true },
			description:{ text: 'Por favor ingrese la descripciòn correctamente.', required: false },
			dates:{ text: 'Por favor ingrese las fechas correctamente.', required: false },
			notifications:{ text: 'Por favor ingrese asunto y mensaje correctamente.', required: false }
		},
		currStages: {
			stages:{ text: 'Por favor seleccione una modalidad.', required: true }
		},
		currContents:{
			contents:{ text: 'Por favor añade contenido a la lista.', required: true },
			question:{ text: 'Por favor ingrese pregunta para encuesta.', required: false }
		},
		currPostulates:{
			range:{ text: 'Por favor seleccione condición.', required: true },
			months:{ text: 'Por favor ingrese meses.', required: true },
			matchs:{ text: 'Por favor verifique que se encuentren usuarios.', required: false }
		},
		currVoters:{
			range:{ text: 'Por favor seleccione condición.', required: true },
			months:{ text: 'Por favor ingrese meses.', required: true },
			matchs:{ text: 'Por favor verifique que se encuentren usuarios.', required: false },

			requirement:{ text: 'Por favor seleccione criterio.', required: true },
			porcent:{ text: 'Por favor seleccione porcentaje de validez.',  required: true}
		}
	};

	const GROUP_MODALITIES = [ 
		{ id:0, title: 'Modalidad 1', text:'Contenido - Postulaciones - Votaciones', value:[0, 1, 2],
			subinfo: 'Presenta los tres bloques del proceso de selección, donde los usuarios podrán:',
			image: '',
			info: [ 'Contenido: Podrán ver contenido referente a la campaña y contestar una pregunta.',
					'Postulaciones: Postular a varios usuarios del mismo criterio y brindar un sustento por cada uno que podrá ser revisado y filtrado desde el gestor.',
					'Votaciones: Realizar una votación final, los resultados solo se verán en el gestor.'] },
		{ id:1, title: 'Modalidad 2', text:'Contenido - Postulaciones', value:[0, 1],
			subinfo:'Presenta 2 bloques del proceso de selección, donde los usuarios:',
			image: '',
			info: [ 'Contenido: Podrán ver contenido referente a la campaña y contestar una pregunta.',
							'Postulaciones: Postular a varios usuarios del mismo criterio y brindar un sustento por cada uno que podrá ser revisado y filtrado desde el gestor.'] },
		{ id:2, title: 'Modalidad 3', text:'Postulaciones - Votaciones', value:[1, 2],
			subinfo:'Presenta 2 bloques del proceso de selección, donde los usuarios podrán:',
			image: '',
			info: [ 'Postulaciones: Postular a varios usuarios del mismo criterio y brindar un sustento por cada uno que podrá ser revisado y filtrado desde el gestor.',
							'Votaciones: Realizar una votación final, los resultados solo se verán en el gestor.'] },
		{ id:3, title: 'Modalidad 4', text:'Postulaciones', value:[1],
			subinfo: 'Cuenta con un solo bloque de actividad donde los usuarios podrán:',
			image: '',
			info: [ 'Postulaciones: Postular a varios usuarios del mismo criterio y brindar un sustento por cada uno que podrá ser revisado y filtrado desde el gestor.'] }
	];
	
	const GROUP_PANELS = {
		'panel-general': 0,
		'panel-stages': 3,
		'panel-contents': 4,
		'panel-postulates': 5,
		'panel-voters': 6
	};
	// ==== LOCAL DATA ====

	export default {
		name: 'VotationsAddForm',
		// inject: ['provideData'],
		props: {
			state: { type: Object, default: null },
			modulo_id: { type: String, default: null, },
			campaign_id: { type: Number|String, default: null, }
		}, //object or other data structure
		components:{ 
			FrmGeneralConfig, FrmAddContent, FrmAddPostulates, FrmAddVoters, VueAlertError 
		},
		data() {
			return {
				base_endpoint: '/votaciones',
				localModulePath: null,
				localLoadData: false,

				disabled: false,
				panel: [0, 3], //abre primer slide por index
				valid: true,
				// stages - modalidades
				// arrayModalities: this.provideData.localModules,
				arrayModalities: GROUP_MODALITIES,
				currStateIndex: null,

				currIndex: null,
				currModality: { value: [] },

				provideModules: [],
				currentStateEdit: false,
				// data dinamica
				dat: {
					currGeneralConfig: null,
					currStages: null,

					// por etapas - dinamico
					currContents: null,
					currPostulates: null,
					currVoters: null
				},
				val: {
					currGeneralConfig: { valid: false, attrs:{}, errors:[] }, 
					currStages: { valid: false, attrs:{}, errors:[] }, 

					currContents: { valid: false, attrs:{}, errors:[] },
					currPostulates: { valid: false, attrs:{}, errors:[] },
					currVoters: { valid: false, attrs:{}, errors:[] }
				}
			}
		},
		provide() {
	        const ModulesProvide = {};

	        Object.defineProperty(ModulesProvide, "modules", {
	            enumerable: true,
	            get: () => this.provideModules,
	        });

			return {
				ModulePathResource: this.localModulePath,
				ModulesProvide
			}
		},
		computed: {
			// === EDIT COMPUTEDS ===
			currentTitleForm() {
				const vm = this;

				const title = vm.currentStateEdit ? 'Editar campaña' : 'Agregar campaña'; 
				const icon = vm.currentStateEdit ? 'fa-edit' : 'fa-plus';

				return { title, icon };
			},
			currentButtonForm() {
				const vm = this;

				const text = vm.currentStateEdit ? 'Guardar' : 'Agregar'; 
				const icon = vm.currentStateEdit ? 'fa-edit' : 'fa-plus';

				return { text, icon };
			},
			// === LOCAL COMPUTEDS ===
			currentInfoMod() {
				return (this.currIndex === null) ? 'info' : '';
			}
		},
	mounted() {
		let vm = this;
        if(vm.campaign_id) vm.loadData();
	},
	methods: {
		leavePage() {
            const vm = this
            window.location.href = vm.base_endpoint;
        },
		currentManageData(key, payload) {
			const vm = this;
			// console.log('currentManageData', key, payload);
			vm.dat[key] = payload;

			// extraer modulos y proveer
			if(key === 'currGeneralConfig') {
				vm.provideModules = payload.modules ? payload.modules.map(ele => ele.id) : payload.modules;
			} 

			// console.log('manage MAIN', payload, vm.dat);
		},
		currentValidData(key, { attr, state }){
			const vm = this;
			let { attrs: valAttrs } = vm.val[key];

			if(state) valAttrs[attr] = attr; // añade attr
			else {
				const { [attr]: val, ...newValAttrs } = valAttrs;
				vm.val[key].attrs = newValAttrs;  //remueve attr
			}
			vm.val[key].valid = state; // añade state segun validacion
		},

		checkAndSuccess(key) {
			const vm = this;
				vm.val[key].errors = [];
		},
		openCurrPanel(key) {
			const vm = this;

				const currLimit = GROUP_PANELS[key];
				let  currPushed = [];

				for(const prop in GROUP_PANELS) {
					const value = GROUP_PANELS[prop];
					if(value <= currLimit) currPushed.push(value); 
				}

				vm.panel = [...currPushed, 3].sort();
		},
		moveToSection({key, id}, errors){
			const vm = this;
			vm.openCurrPanel(id);

			setTimeout(() => vm.val[key].errors = errors, 200); //los errores son dinamicos
			document.getElementById(id).scrollIntoView( { behavior:'smooth' } ); // scroll a la seccion con error
		},
		checkAndVerify(key, id, flag = false) { 
			const vm = this;

			const currValidation = GROUP_VALIDATIONS[key];
			const currData = vm.dat[key];
			const currValKey = Object.keys(vm.val[key].attrs);

			//usamos flag para validacion en null
			if(flag && currData) return true;

				// !null        !0
			const current = !currData || !Object.keys(currData).length;

			const ArrayErrors = current ? checkValidEmpty(currValidation) :
																	checkValidModules({currData, currValidation}, currValKey);
			const ArrayErrorsExist = ArrayErrors.length > 0;   

			if(ArrayErrorsExist) vm.moveToSection({key, id}, ArrayErrors);
			return !ArrayErrorsExist;
		},
		sendCurrentData() {
			const vm = this;

			/* === VAL GENERAL CONFIG === */
			const respGeneralConfig = vm.checkAndVerify('currGeneralConfig', 'panel-general');
			if(!respGeneralConfig) return console.log('currGeneralConfig - error', respGeneralConfig);
			vm.checkAndSuccess('currGeneralConfig');

			/* === VAL STAGES === */
			const respCurrStages = vm.checkAndVerify('currStages', 'panel-stages', true);
			if(!respCurrStages) return console.log('currStages - error', respCurrStages);
			vm.checkAndSuccess('currStages');

			const { value } = vm.currModality;
			/* === VAL CONTENTS === */
			if(value.includes(0)) {
				const respCurrContents = vm.checkAndVerify('currContents', 'panel-contents')
				if(!respCurrContents) return console.log('currContents - error', respCurrContents)
			
				vm.if_badges_visibility = 0;
				vm.checkAndSuccess('currContents');
			}

			/* === VAL POSTULATES === */
			if(value.includes(1)) {
				const respCurrPostulates = vm.checkAndVerify('currPostulates', 'panel-postulates');
				if(!respCurrPostulates) return console.log('currPostulates - error', respCurrPostulates)

				vm.if_badges_visibility = 1;
				vm.checkAndSuccess('currPostulates');
			}

			/* === VAL VOTERS === */
			if(value.includes(2)){
				const respCurrVoters = vm.checkAndVerify('currVoters', 'panel-voters');
				if(!respCurrVoters) return console.log('currVoters - error', respCurrVoters);

				vm.if_badges_visibility = 2;
				vm.checkAndSuccess('currVoters');
			}

			vm.showLoader();

			vm.disabled = true;
			
			let sendData;
			
			if(vm.currentStateEdit) {
				sendData = deepAppendFormData({...vm.dat, currIndex: vm.currStateIndex});
				vm.editStateForm(sendData);
			}else {
				sendData = deepAppendFormData(vm.dat);
				vm.addStateForm(sendData);
			}

			// console.log('sending data', { data: vm.dat, index: vm.currStateIndex});
		},
		addStateForm(indata) {
			const vm = this;
			vm.$http.post('/votaciones/store', indata).then((res) => {

				vm.disabled = false;
				vm.hideLoader();

				if(res.data.error) {
                    vm.showAlert('Hubo un problema al guardar campaña.', 'error');
                } else {
                	vm.queryStatus("reconocimiento", "crear_campana");

                    vm.showAlert('La campaña fue guardada correctamente.');
                    setTimeout(() => vm.leavePage(), 1200);
                }
			});
		},		
		editStateForm(indata){
			const vm = this;
			vm.$http.post('/votaciones/update/'+vm.currStateIndex, indata).then((res) => {

				vm.disabled = false;
				vm.hideLoader();

				if(res.data.error) {
                    vm.showAlert('Hubo un problema al actualizar campaña.', 'error');
                } else {
                	vm.queryStatus("reconocimiento", "crear_campana");

                    vm.showAlert('La campaña fue actualizada correctamente.');
                    setTimeout(() => vm.leavePage(), 1200);
                }
			});
		},
		selectModality(index) {
			const vm = this;
			const stcModality = vm.arrayModalities[index];
			vm.currModality = stcModality;
			vm.currIndex = index; //modalidad seleccionada

			const { id, title, value } = stcModality;
			vm.dat.currStages = { id, title, value };

			// stado segun modalidad
			!value.includes(0) && (vm.dat.currContents = null);
			!value.includes(1) && (vm.dat.currPostulates = null);
			!value.includes(2) && (vm.dat.currVoters = null);
		},
		// form send gooooooooo
		loadData() {
			const vm = this;

			vm.localLoadData = true;
			vm.showLoader();

			vm.$http.get('/votaciones/edit/'+vm.campaign_id)
			.then((res) => {
				const { data } = res.data;

				const { currGeneralConfig: { id, stage_id } } = data;
				vm.currStateIndex = id; //index edit
				vm.selectModality(stage_id); //select stage edit

				setTimeout(() =>{

					for(const [key, value] of Object.entries(data)) {
						vm.dat[key] = { ...value }; 
					}

		        	vm.currentStateEdit = true;

				}, 300);

				vm.hideLoader();
			});
		},
		clearCurrentData() {
			const vm = this;
			vm.localLoadData = false;
			vm.$emit('state', { payload: null, comp:'VotationsList'});
		}
	}
} 
</script>
