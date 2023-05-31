<template>
	<div>
		<!-- OPT GENERAL BASIC -->
		<v-row class="my-2">
			<v-col cols="12" md="7">
				<v-row>
					<v-col cols="12" class="py-0">
						<v-alert v-show="validations.modules" text type="error">
							Por favor elija uno o varios modulos.
						</v-alert>
					</v-col>
					<v-col cols="12">
	      		<DefaultSelect label="Seleccione módulo *" return-object required item-text="name" 
	      			v-model="frm.modules" :loading="loader" :rules="rules.modules" :items="itemsSelect" :multiple="true" />
					</v-col>
					<v-col cols="12" class="pb-0">
	      		<DefaultInput label="Nombre de campaña *" required 
	      			v-model="frm.title" :rules="rules.title" :counter="70" maxlength="70"/>
					</v-col>
					<v-col cols="12" class="py-1">
					  <v-textarea label="Descripción"  
					  	class="custom-default-input" outlined rows="5" 
			  			v-model="frm.description" :rules="rules.description" :counter="200" maxlength="200"></v-textarea>
					</v-col>
				</v-row>
			</v-col>
			<v-col cols="12" md="5">
			
 			<vue-upload-content-multimedia label="Seleccione imagen (450 x 400)" 
 																		 sub-label="Seleccione imagen"
 																		 v-model="frm.image" 
 																		 icon-place-holder="fa-image"
 																		 :img-place-holder="provideData.localDefaults.announ"
 																		 :file-types="['image']"

 																		 :check-dimensions="true"
 																		 :stack-dimensions="{ width: 450, height: 400 }" 
 																		 />

			</v-col>
			<v-col cols="12" class="py-0">
				<v-alert v-show="checkDatesRange" text type="error">
					La fecha fin debe ser mayor a la fecha de inicio
				</v-alert>
			</v-col>
			<v-col cols="12" md="6" class="pt-0">
				<vue-date-mod v-model="frm.start_date" label="Fecha de inicio" name="v-star-date"></vue-date-mod>
			</v-col>
			<v-col cols="12" md="6" class="pt-0">
				<vue-date-mod v-model="frm.end_date" label="Fecha de fin" name="v-end-date"></vue-date-mod>
			</v-col>
		</v-row>
		<!-- OPT GENERAL BASIC -->

		<!-- OPT NOTIFICACIONES -->
		<v-expansion-panel>
			<v-expansion-panel-header class="text-primary grey lighten-5">
				<span>
					<span class="font-weight-bold">Notificaciones</span>
					<small class="ml-2">Opcional</small> 
				</span>
			</v-expansion-panel-header>
			<v-expansion-panel-content class="pb-0">

				<v-row class="mt-4">
					<v-col cols="12" class="py-0">
						<v-alert v-show="checkNotification" text type="error">
							Al ingresar notificación asegurese de rellenar asunto y mensaje correctamente.
						</v-alert>	
						<p>Notificaciones por email:</p>
					</v-col>
					<v-col cols="12" md="6" class="py-0">
						<DefaultInput label="Asunto" 
							v-model="frm.subject" :rules="rules.subject" :counter="70" maxlength="70" hide-details="auto"/>
					</v-col>
					<v-col cols="12" md="6" class="py-0">
       		  <v-textarea label="Mensaje" rows="2" class="custom-default-input" outlined 
       		  	v-model="frm.body" :rules="rules.body" :counter="200" maxlength="200"></v-textarea>
					</v-col>
				</v-row>

			</v-expansion-panel-content>
		</v-expansion-panel>
		<!-- OPT NOTIFICACIONES -->

		<!-- OPT BRANDING -->
		<v-expansion-panel>
			<v-expansion-panel-header class="text-primary grey lighten-5">
				<span>
					<span class="font-weight-bold">Branding</span>
					<small class="ml-2">Opcional</small> 
				</span>
			</v-expansion-panel-header>
			<v-expansion-panel-content>
				<v-row class="mt-4">
					<v-col cols="12" md="6">
						<v-row>
							<v-col cols="12" class="py-0">
								<vue-upload-content-multimedia label="Seleccione banner (1300 x 300)" 
 																		 sub-label="Seleccione imagen"
 																		 v-model="frm.banner" 
 																		 icon-place-holder="fa-image"
 																		 :img-place-holder="provideData.localDefaults.banner"
 																		 :file-types="['image']"

 																		 :check-dimensions="true"
 																		 :stack-dimensions="{ width: 1300, height: 300}" />
							</v-col>
							<v-col cols="12" class="mt-2">
								<vue-color v-model="frm.color" label="Color"></vue-color>
							</v-col>
						</v-row>
					</v-col>
					<!-- DINAMIYC CONTENT -->
					<v-col cols="12" md="6">
						<v-row>
							<v-col v-if="showCurrentBadged.first" cols="12" class="py-0 mb-4">
								<div class="d-flex">
									<span>1ª insignia</span> 
									<DefaultInfoTooltip text="Los participantes obtendrán esta insignia cuando completen la pregunta de encuesta." :right="true"/>
								</div>

									<vue-upload-content-multimedia label="Seleccione insignia (50 x 50)" 
 																		 sub-label="Seleccione insignia"
 																		 v-model="frm.badge_one" 
 																		 icon-place-holder="fa-image"
 																		 :img-place-holder="provideData.localDefaults.badge1"
 																		 :file-types="['image']"

 																		 :check-dimensions="true"
 																		 :height="90"
 																		 :stack-dimensions="{ width:50, height:50 }"
                                     :contain-image="true" />

							</v-col>
							<v-col v-if="showCurrentBadged.second" cols="12" class="py-0">
								<div class="d-flex">
									<span>2ª insignia</span> 
									<DefaultInfoTooltip text="Al ser seleccionado como candidato." :right="true"/>
								</div>

									<vue-upload-content-multimedia label="Seleccione insignia (50 x 50)" 
 																		 sub-label="Seleccione insignia"
 																		 v-model="frm.badge_two" 
 																		 icon-place-holder="fa-image"
 																		 :img-place-holder="provideData.localDefaults.badge2"
 																		 :file-types="['image']"

 																		 :check-dimensions="true"
 																		 :height="90"
 																		 :stack-dimensions="{ width:50, height:50 }"
                                     :contain-image="true" />
							</v-col>
						</v-row>
					</v-col>
				</v-row>
			</v-expansion-panel-content>
		</v-expansion-panel>
		<!-- OPT BRANDING -->

	</div>

</template>

<script>

	// components
	import VueImage from '../components/VueImage.vue';
	import VueDateMod from '../components/VueDateMod.vue';
	import VueColor from '../components/VueColor.vue';
	const LocalComponents = { VueImage, VueDateMod, VueColor };

	import VueUploadContentMultimedia from '../components/VueUploadContentMultimedia.vue';

	// functions
	import { createDinamycPayload } from '../utils/UtlComponents.js';
	import { setRules, Stackvalidations } from '../utils/UtlValidators.js';
  	import { transDate } from '../utils/UtlFilters.js';

	const { valString, valOptString } = Stackvalidations; 

	export default {
		name:'FrmGeneralConfig',
		inject:[ 'ModulePathResource'],
		props: { 
			data: { type: Object }, //readonly
			mode: { type: Boolean, default: false },

			validation: { type: Boolean, default: false },
			badge: { require: true }
		},
		components:{ ...LocalComponents, VueUploadContentMultimedia },
		data() {
			return {
				menu: false,
				provideData:{ localDefaults: {} },
				frm: {
					// modules: [ { id:2 } ],
					modules: [],
					modules: null,

					title: null,
					description: null,
					image: null,

					start_date: null,
					end_date: null,

					subject: null,
					body: null,

					banner: null,
					color: null,
					badge_one: null,
					badge_two: null,
				},
				loader: false,
				itemsSelect:[],

				validations: {
					modules: false
				},
				rules:{
					modules: setRules('required'), // opcional 
					title: setRules('required', 'max:10', 'min:70'),
					description: setRules('minmax:10-200'),
					subject: setRules('minmax:10-70'),
					body: setRules('minmax:10-200')
				},

				// modalPreviewMultimedia 
     //    fileTypes:['image'],
			  // modalPreviewMultimedia: {
     //    	ref: 'modalSelectPreviewMultimedia',
     //      open: false,
     //      title: 'Buscar multimedia',
     //      confirmLabel: 'Seleccionar',
     //      cancelLabel: 'Cerrar'
     //    },

			}
		},
		watch:{
			badge:{
				handler(val) {
					const vm = this;

					(val === 1) && (vm.frm.badge_two = null);
  			  (val === 2) && (vm.frm.badge_one = null);
  			  (val === 3) && (vm.frm.badge_one = null,
  			  								vm.frm.badge_two = null);

					vm.emitData(vm.frm);
				}
			},
			frm: {
				handler(frm) {
					const vm = this;
					// validaciones
					const { title, description } = frm;

					if(title) vm.emitValid('title', !valString(title, [10, 70]) );
					if(description !== null) vm.emitValid('description', valOptString(description, [10, 200]) );

					vm.emitValid('dates', vm.checkDatesRange);
					vm.emitValid('notifications', vm.checkNotification);
					// console.log('emit general config', frm);
					vm.emitData(frm);
				},
				deep: true
			}
		},
		computed:{
			showCurrentBadged() {
				const vm = this;

				const setBoolLayout = (first, second) => ({first, second});
				const showLayout = ([null, 0].includes(vm.badge)) ? setBoolLayout(true, true) : 
												   (vm.badge === 1) ? setBoolLayout(true, false) :  
												   (vm.badge === 2) ? setBoolLayout(false, true) : 
												   										setBoolLayout(false, false);

				return showLayout;
			},
			checkDatesRange() {
				const vm = this;
				const { start_date, end_date } = vm.frm;

				if(!start_date && !end_date) return false; //ambos nulos
				if(!start_date && end_date) return true; // a nulo
				if(start_date && !end_date) return true; // b nulo

				const dt_one = new Date(start_date),
							dt_two = new Date(end_date);

				return dt_one >= dt_two; // comparacion
			},
			checkNotification() {
				const vm = this;
				let { subject, body } = vm.frm;

				subject = (subject) ? !valString(subject, [10, 70]) : null;
				body = (body) ? !valString(body, [10, 200]) : null;

				return (subject === null && body === null) ? false :
							 (subject === false && body === false) ? false : true;
			}
		},
		methods: {
			emitData(form) {
				const vm = this;

				const currentPayload = createDinamycPayload(form);
				vm.$emit('data', currentPayload); //actualizamos nuestra data en el componente padre
			},
			emitValid(attr, state) {
				const vm = this;
				
				vm.$emit('valid', { attr, state } );
			} 
		},
		mounted(){
			const vm = this;

			if(vm.mode) {
				const initialDataEdit = Object.entries(vm.data);

				for(const [key, value] of initialDataEdit) {
						const checkDates = (['start_date','end_date'].includes(key) && value);
						vm.frm[key] = checkDates ? transDate(value) : value;
				}
				// console.log('edit CurrGeneralConfig', vm.frm);						
			}
		},
		created() {
			const vm = this;

			vm.loader = true;
			vm.$http.get('/votaciones/announ/data?module=modules')
							.then((res) => {
								// console.log(res);
								vm.loader = false;
								vm.itemsSelect = res.data;
							});
		}
	}
</script>
