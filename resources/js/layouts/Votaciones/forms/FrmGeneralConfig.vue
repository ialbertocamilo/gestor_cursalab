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
						<DefaultSelect 
							dense 
							return-object 
							required 
							multiple
							label="Seleccione módulo *" 
							item-text="name" 
							v-model="frm.modules" 
							:loading="loader" 
							:rules="rules.modules" 
							:items="itemsSelect" 
						/>
					</v-col>
					<v-col cols="12" class="pb-0">
						<DefaultInput dense label="Nombre de campaña *" required 
						v-model="frm.title" :rules="rules.title" :counter="70" maxlength="70"/>
					</v-col>
					<v-col cols="12" class="py-1">
					  <v-textarea dense label="Descripción"  
						class="custom-default-input" outlined rows="3" 
						v-model="frm.description" :rules="rules.description" :counter="200" maxlength="200"></v-textarea>
					</v-col>
					<v-col cols="12" class="p-0">
						<v-alert v-show="checkDatesRange" text type="error" class="mx-2">
							La fecha fin debe ser mayor a la fecha de inicio
						</v-alert>
					</v-col>
					<v-col cols="6">
						<DefaultInputDate
							dense 
							clearable
							:referenceComponent="'modalDateFilterStart'"
							:options="modalDateFilterStart"
							v-model="frm.start_date"
							label="Fecha de inicio"
						/>
					</v-col>
					<v-col cols="6">
						<DefaultInputDate 
							dense
							clearable
							:referenceComponent="'modalDateFilterEnd'"
							:options="modalDateFilterEnd"
							v-model="frm.end_date"
							label="Fecha de fin"
						/>
					</v-col>
				</v-row>
			</v-col>
			<v-col cols="12" md="5">

				<DefaultSelectOrUploadMultimediaDimension
					ref="inputFondoCampaign" 
					v-model="frm.file_image"
					label="Tamaño máximo (450 x 400)"
					label-button="Seleccione imagen"
					:file-types="['image']"
					@onSelect="setFileOnly($event, frm,'image')"
					check-dimensions
					:stack-dimensions="{ width: 450, height: 400 }"
				/>
			</v-col>
		</v-row>
		<!-- OPT GENERAL BASIC -->

		<!-- OPT NOTIFICACIONES -->
		<v-expansion-panel>
			<v-expansion-panel-header class="grey lighten-5">
				<span class="text-primary-sub">
					<span class="font-weight-bold">Notificaciones</span>
					<small class="ml-2">(Opcional)</small> 
				</span>
			</v-expansion-panel-header>
			<v-expansion-panel-content class="pb-0">

				<v-row class="mt-4">
					<v-col cols="12">
						Configura el asunto y cuerpo del correo que podrás enviar a los postulados.
					</v-col>
					<v-col cols="12" class="py-0">
						<v-alert v-show="checkNotification" text type="error">
							Al ingresar notificación asegurese de rellenar asunto y mensaje correctamente.
						</v-alert>  
					</v-col>
					<v-col cols="12" md="6" class="py-0">
						<DefaultInput 
							dense
							label="Asunto" 
							v-model="frm.subject" 
							:rules="rules.subject" 
							:counter="70" 
							maxlength="70" 
							hide-details="auto"
							/>
					</v-col>
					<v-col cols="12" md="6" class="py-0">
						<v-textarea 
							label="Mensaje" 
							rows="2" 
							class="custom-default-input" 
							outlined 
							v-model="frm.body" 
							:rules="rules.body" 
							:counter="200" 
							maxlength="200" />
					</v-col>
				</v-row>

			</v-expansion-panel-content>
		</v-expansion-panel>
		<!-- OPT NOTIFICACIONES -->

		<!-- OPT BRANDING -->
		<v-expansion-panel>
			<v-expansion-panel-header class="grey lighten-5">
				<span class="text-primary-sub">
					<span class="font-weight-bold">Branding</span>
					<small class="ml-2">(Opcional)</small> 
				</span>
			</v-expansion-panel-header>
			<v-expansion-panel-content>
				<v-row class="mt-4">
					<v-col cols="12">
						<v-row>
							<v-col cols="6" class="align-self-center py-0">
								<span>Personaliza tu campaña de reconocimiento con el banner, insignias y color temático</span>
							</v-col>
							<v-col cols="6" class="py-0">
								<v-row>
									<v-col cols="5">
										<DefaultInput 
											dense
											clearable 
											v-model="frm.color"
											type="color" 
											label="Color" 
										/>
									</v-col>
								</v-row>
							</v-col>
						</v-row>
					</v-col>

					<v-col cols="4" class="py-0">
						<DefaultSelectOrUploadMultimediaDimension
							ref="inputFondoBanner" 
							v-model="frm.file_banner"
							label="Seleccione banner (1300 x 300)"
							label-button="Seleccione imagen"
							:file-types="['image']"
							@onSelect="setFileOnly($event, frm,'banner')"
							check-dimensions
							:stack-dimensions="{ width: 1300, height: 300 }"
						/>
					</v-col>

					<!-- === INSIGNIAS SEGUN MODALIDAD === -->
					<v-col v-show="showCurrentBadged.first" cols="4" class="py-0">
						<DefaultSelectOrUploadMultimediaDimension
							ref="inputFondoBadgeOne" 
							v-model="frm.file_badge_one"
							label="1° insignia (50 x 50)"
							label-button="Seleccione imagen"
							:file-types="['image']"
							@onSelect="setFileOnly($event, frm,'badge_one')"
							check-dimensions
							:stack-dimensions="{ width: 50, height: 50 }"
						/>
					</v-col>
					<v-col v-show="showCurrentBadged.second" cols="4" class="py-0">
						<DefaultSelectOrUploadMultimediaDimension
							ref="inputFondoBadgeTwo" 
							v-model="frm.file_badge_two"
							label="2° insignia (50 x 50)"
							label-button="Seleccione imagen"
							:file-types="['image']"
							@onSelect="setFileOnly($event, frm,'badge_two')"
							check-dimensions
							:stack-dimensions="{ width: 50, height: 50 }"
						/>
					</v-col>
					<!-- === INSIGNIAS SEGUN MODALIDAD === -->

				</v-row>
			</v-expansion-panel-content>
		</v-expansion-panel>
		<!-- OPT BRANDING -->

	</div>

</template>

<script>

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
		data() {
			return {
				menu: false,
				provideData:{ localDefaults: {} },
				modalDateFilterStart:{ open: true },
				modalDateFilterEnd:{ open: true },
				frm: {
					// modules: [ { id:2 } ],
					modules: null,

					title: null,
					description: null,
					file_image: null,

					start_date: null,
					end_date: null,

					subject: null,
					body: null,

					file_banner: null,
					color: null,
					file_badge_one: null,
					file_badge_two: null,
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
	 //     ref: 'modalSelectPreviewMultimedia',
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

					(val === 1) && (vm.frm.file_badge_two = null);
				    (val === 2) && (vm.frm.file_badge_one = null);
				  	(val === 3) && (vm.frm.file_badge_one = null, vm.frm.file_badge_two = null);
				  	console.log('badge')
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

					// console.log('GeneralConfig', frm);

					vm.emitValid('dates', vm.checkDatesRange);
					vm.emitValid('notifications', vm.checkNotification);
					vm.emitData(frm);
				  	// console.log('frm')
				},
				deep: true
			},
			mode:{
				handler(val) {
					let vm = this;

					if(val) {
						const initialDataEdit = Object.entries(vm.data);
						for(const [key, value] of initialDataEdit) {
								const checkDates = (['start_date','end_date'].includes(key) && value);
								vm.frm[key] = checkDates ? transDate(value) : value;
						}
					}
				}
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
		created() {
			const vm = this;

			vm.loader = true;
			vm.$http.get('/votaciones/modules/get-data')
							.then((res) => {
								const { data } = res.data
								vm.loader = false;
								vm.itemsSelect = data.modules;
							});
		}
	}
</script>
