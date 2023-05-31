<template>
    <section class="section-list">
	    <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Creacion de campaña
            </v-card-title>
        </v-card>

	<!-- <v-card-title class="d-flex primary text-white">
		<span :class="`fas ${currentTitleForm.icon} f-md mx-2`"></span>
		<span v-text="currentTitleForm.title"></span>
  	</v-card-title> -->

    <v-card-text class="pt-5">
  		<v-form ref="form" v-model="valid" lazy-validation>
    		
    		<v-expansion-panels v-model="panel" multiple :accordion="true" :hover="true">

      		<!-- ==== REQUIRED GENERAL CONFIG ==== -->
    			<v-expansion-panel id="panel-general">
				  	<v-expansion-panel-header class="text-primary grey lighten-5"> 
				  		<span class="font-weight-bold">General</span>
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
      		<v-expansion-panel id="panel-stages">
	        	<v-expansion-panel-header class="text-primary grey lighten-5">
	        		<div class="d-flex justify-content-between">
				  			<span class="font-weight-bold">Modalidad</span>
								<DefaultInfoTooltip :left="true" text="Define la cantidad de etapas que tendrá la campaña." />
	        		</div>
	        	</v-expansion-panel-header>
	        	<v-expansion-panel-content class="pt-2">
	        		<!-- {{ dat.currStages }} -->
	        		<div>
		        		<vue-alert-error :errors="val.currStages.errors">
		        			Por favor verifica esta sección y completa los campos correctamente.
		        		</vue-alert-error>
		        		<v-row class="my-2">
									<v-col v-for="(mod, index) of arrayModalities" :key="index" cols="12" md="3">
										<v-card class="pointer h-100 rounded" 
														:class="{'text-white': index === currIndex}" 
														:color="index === currIndex ? 'primary' : '' "
														:disabled="currentStateEdit && index !== currIndex"
														@click="selectModality(index)">

											<v-card-title class="justify-content-center" v-text="mod.title"></v-card-title>
											<v-card-text class="text-center" v-text="mod.text" :class="index === currIndex && 'text-white'"></v-card-text>
										</v-card>
									</v-col>
		        		</v-row>
		        		<v-row class="my-3 px-3">
		        			
		        			<div class="w-100 rounded border border-dark p-2" :type="currentInfoMod" elevation="2">
		        				<div v-if="currIndex === null">
	        						Recuerda elegir una modalidad para esta campaña, la modalidad te permitirá ver el alcance de la campaña y sus etapas.
		        				</div>
		        				<div v-else>
		        					<v-row>
		        						<v-col cols="4">
		        							<v-img class="m-auto" :src="currModality.image" max-width="300"></v-img>
		        						</v-col>
		        						<v-col cols="8">
				        					<p v-text="currModality.subinfo"></p>
				        					<div v-for="(text, index) of currModality.info" :key="index">
				        						<span class="font-weight-bold" v-text="text.split(':')[0] + ':'"></span>
														<span class="d-inline" v-text="text.split(':')[1]"></span>			
				        					</div>
		        						</v-col>
		        					</v-row>
		        				</div>
	  							</div>
	  							
		        		</v-row>
	        		</div>
	        	</v-expansion-panel-content>
      		</v-expansion-panel>
      		<!-- ==== REQUIRED MODALIDADES ==== -->


      		<!-- COMP CONTENIDO -->
      		<v-expansion-panel v-if="currModality.value.includes(0)" id="panel-contents">
	        	<v-expansion-panel-header class="text-primary grey lighten-5">
	        		<span class="font-weight-bold">Contenido</span>
	        	</v-expansion-panel-header>
	        	<v-expansion-panel-content class="pt-2">
	        		<!-- {{ dat.currContents }} -->
	        		<vue-alert-error :errors="val.currContents.errors">
				  			Por favor verifica esta sección y completa los campos correctamente
	        		</vue-alert-error>
        			<!-- <FrmAddContent v-model="data.currContents" /> -->
        			v-
	        		<component :is="currModality.value.includes(0) ? 'FrmAddContent' : null" 
	        							 :data="dat.currContents"
	        							 :mode="currentStateEdit" 
							        		
							        		@data="currentManageData('currContents', $event)"
							        		@valid="currentValidData('currContents', $event)"></component>		

	        	</v-expansion-panel-content>
      		</v-expansion-panel>

      		<!-- COMP POSTULACIONES -->
      		<v-expansion-panel v-if="currModality.value.includes(1)" id="panel-postulates">
	        	<v-expansion-panel-header class="text-primary grey lighten-5">
	        		<div>
	        			<span class="font-weight-bold">Postulantes :</span>
	        			<span> Sección para definir qué usuarios estarán en la(s) lista(s) de selección.</span>
	        		</div>
	        	</v-expansion-panel-header>
	        	<v-expansion-panel-content class="pt-2">
	        		<!-- {{ dat.currPostulates }} -->
	        		<vue-alert-error :errors="val.currPostulates.errors">
				  			Por favor verifica esta sección y completa los campos correctamente
	        		</vue-alert-error>
        			<!-- <FrmAddPostulates v-model="data.currPostulates" /> -->
	        		<component :is="currModality.value.includes(1) ? 'FrmAddPostulates' : null" 
	        							 :data="dat.currPostulates"
	        							 :mode="currentStateEdit" 

	        							 @data="currentManageData('currPostulates', $event)"></component>		
	        	
	        	</v-expansion-panel-content>
      		</v-expansion-panel>

      		<!-- COMP VOTACIONES-->
      		<v-expansion-panel v-if="currModality.value.includes(2)" id="panel-voters">
	        	<v-expansion-panel-header class="text-primary grey lighten-5">
	        		<div>
		        		<span class="font-weight-bold">Votantes :</span>
	        			<span>Sección para definir qué usuarios serán votantes y podrán elegir a sus postulantes/candidatos.</span>
	        		</div>
	        	</v-expansion-panel-header>

	        	<v-expansion-panel-content class="pt-2">	        		
	        		<!-- {{ dat.currVoters }} -->
				  		<vue-alert-error :errors="val.currVoters.errors">
				  			Por favor verifica esta sección y completa los campos correctamente
				  		</vue-alert-error> 
        			<!-- <FrmAddVoters v-model="data.currVoters" /> -->
	        		<component :is="currModality.value.includes(2) ? 'FrmAddVoters' : null" 
	        							 :data="dat.currVoters"
	        							 :mode="currentStateEdit" 

	        							 @data="currentManageData('currVoters', $event)"></component>		

	        	</v-expansion-panel-content>
      		</v-expansion-panel>

    		</v-expansion-panels>
			</v-form>
    </v-card-text>
    <v-card-actions class="d-flex justify-content-center pb-4">
			<v-btn text @click="clearCurrentData"> Cancelar </v-btn>
			<v-btn color="primary" :loading="disabled" :disabled="disabled" @click="sendCurrentData">
				<span :class="`fas ${currentTitleForm.icon} mr-2`"></span> 
				<span v-text="currentButtonForm.text"></span> 
			</v-btn>

    </v-card-actions>
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
			matchs:{ text: 'Por favor verifique que se encuentren usuarios.', required: true }
		},
		currVoters:{
			range:{ text: 'Por favor seleccione condición.', required: true },
			months:{ text: 'Por favor ingrese meses.', required: true },
			matchs:{ text: 'Por favor verifique que se encuentren usuarios.', required: true },

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
			state: { type: Object, default: null }
		}, //object or other data structure
		components:{ 
			FrmGeneralConfig, FrmAddContent, FrmAddPostulates, FrmAddVoters, VueAlertError 
		},
		data() {
			return {
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
			return {
				ModulePathResource: this.localModulePath,
			}
		},
		computed: {
			// === EDIT COMPUTEDS ===
			currentStateEdit() {
				return !(this.state === null);		
			},
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
		watch:{
			state: {
				handler(value) {
					const vm = this;
					
					// console.log('state data_watch', value);
					if(value !== null && !vm.localLoadData) {
						vm.localLoadData = true;

						const copyValue = { ...value };
						const { currGeneralConfig } = copyValue;
						const { id, 
										stage_id, 
										path_disk,
									  ...newCurrGeneralConfig } = currGeneralConfig;


						vm.currStateIndex = id; //index edit
						vm.localModulePath = path_disk; //path_disk
						vm.selectModality(stage_id); //select stage edit
						copyValue.currGeneralConfig = newCurrGeneralConfig; //newValue

						for(const [key, value] of Object.entries(copyValue)) {
							vm.dat[key] = value; 
						}
					}
				},
				deep: true,
				immediate: true
			}
		},
    methods: {
    	currentManageData(key, payload) {
    		const vm = this;
    		vm.dat[key] = payload;
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

			vm.disabled = true;
			
			let sendData;
			
			if(vm.currentStateEdit) {
				sendData = deepAppendFormData({...vm.dat, currIndex: vm.currStateIndex});
				vm.editStateForm(sendData);
			}else {
				sendData = deepAppendFormData(vm.dat);
				vm.addStateForm(sendData);
			}

			console.log('sending data', { data: vm.dat, index: vm.currStateIndex});
		},
		addStateForm(indata) {
			const vm = this;

			vm.$http.post('/votaciones/create', indata).then((res) => {
				const { data } = res;

				if(data === true) {
                	vm.queryStatus("reconocimiento", "crear_campana");
					vm.showAlert('Campaña registrada correctamente.');
				}else {
					vm.showAlert('Error al registrar campaña.', 'error');
					console.log(res);
				}

				vm.disabled = false;
				// return;
				vm.clearCurrentData();
			});
		},		
		editStateForm(indata){
			const vm = this;

			vm.$http.post('/votaciones/update', indata).then((res) => {
				const { data } = res;

				if(data === true) {
					vm.showAlert('Campaña actualizada correctamente.');
				}else {
					vm.showAlert('Error al actualizar campaña.', 'error');
					console.log(res);
				}
				vm.disabled = false;
				vm.clearCurrentData();
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
		clearCurrentData() {
			const vm = this;
			vm.localLoadData = false;
			vm.$emit('state', { payload: null, comp:'VotationsList'});
		}
	}
} 
</script>

