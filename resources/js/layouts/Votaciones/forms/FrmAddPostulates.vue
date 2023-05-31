<template>
	
	<div>
		<v-row class="my-2">
			<v-col cols="12" class="p-0">
				<v-alert class="mb-0 mx-3" v-model="alert" text type="error" dismissible>
					Por favor seleccione condición e ingrese meses.
				</v-alert>	
			</v-col>
			<v-col cols="8" class="pr-0">
				<p>Agregar requisito</p>
				<v-row>
					<v-col cols="5" class="pr-0">
						<span class="text-muted">Fecha de ingreso (Antigüedad):</span>
					</v-col>
					<v-col cols="7">
						<div class="d-flex">
							<v-row>
								<v-col cols="7" class="px-0">
									<DefaultSelect return-object item-text="name" label="Condición"
																 v-model="frm.range" :items="itemsRange" :rules="rules.range" />
								</v-col>
								<v-col cols="5" class="pl-0">
                  <v-text-field class="custom-default-input" outlined dense
                                label="Meses" type="number" v-model="frm.months"
                                :rules="rules.months" min="1" step="1"
                                @keypress="passOnlyNumbers" />
								</v-col>
							</v-row>

							<v-btn class="ml-1" color="primary" 
										:loading="disabled" :disabled="disabled" 
										@click.prevent="checkAndGet(true)"> 
								<span class="fas fa-check mr-2"></span> Verificar 
							</v-btn>
							
						</div>
					</v-col>
				</v-row>
			</v-col>

			<v-col cols="4" class="align-self-center">
				<v-card class="border rounded">
					<v-card-text>
						<div class="text-center d-flex justify-content-center align-items-center">
							<span class="font-weight-bold">Resultados:</span>
						  <span class="mx-2">
						  	<span v-if="!frm.matchs && !disabled">
						  		<span class="fas fa-ban"></span> Sin datos.
						  	</span>
						  	<span v-if="!frm.matchs && disabled">
						  		 <v-progress-circular indeterminate color="primary"></v-progress-circular>
						  	</span>
						  	<span v-if="frm.matchs" v-text="frm.matchs"></span>
						  </span>
							<span class="text-primary f-lg fas fa-user"></span>
						</div>
					</v-card-text>
				</v-card>
			</v-col>

			<v-col cols="12" class="d-flex">
				<vue-switch :value="frm.state" @change="frm.state = !frm.state" />
				<span>Revisión de postulaciones</span>
				<DefaultInfoTooltip :right="true" text="Revisa los argumentos de postulación, enviados por los votantes para cada postulante." />
			</v-col>
		</v-row>
	</div>

</template>

<script>
	// components
	import VueSwitch from '../components/VueSwitch.vue';

	// === utils ===
	import { getStaticParams } from '../utils/UtlComponents.js';
	import { createDinamycPayload } from '../utils/UtlComponents.js';
	import { setRules, Stackvalidations, passOnlyNumbers } from '../utils/UtlValidators.js';
	const { valObjNull, valNumberInt } = Stackvalidations;
	// === utils ===

	// === local data ===
	const ITEMS_RANGE = [
		{ id:0, name:'Máximo (hasta)' },
		{ id:1, name:'Mínimo (desde)' }
	];
	// === local data ===

	export default {
  	name: 'FrmPostulates',
  	components: { VueSwitch },
  	props:{
			data: { type: Object },
			mode: { type: Boolean, default: false },
  	},
  	data() {
  		return {
  			// data

  			itemsRange: ITEMS_RANGE,
  			// itemsMonths: ITEMS_MONTHS,

  			frm: {
	  			range: null,
	  			months: null,
	  			state: false,
	  			matchs: 0
  			},
				disabled: false,
				alert: false,

  			// validations
				rules:{
					range: setRules('required'), 
					months: setRules('required','min:3'), 
				}
  		}
  	},
  	watch:{
  		'frm.range':{
  			handler(range) {
  				const vm = this;
					
					vm.frm.matchs = 0;
					vm.emitData();
  				// if(range && vm.frm.months) vm.checkAndGet();
  				// else vm.emitData();
  			}
  		},
  		'frm.months':{
  			handler(months) {
  				const vm = this;

					// vm.frm.months = months == null ? 0 : Math.abs(months);  
					
					vm.frm.matchs = 0;  
					vm.emitData();

  				// if(months) vm.checkAndGet();
  				// else vm.emitData();
  			}
  		},
  		'frm.state':{
  			handler(state){
  				const vm = this;
  				if(state && vm.frm.matchs) vm.emitData();
  				else vm.emitData();
  			}
  		}
  	},
  	methods:{
      passOnlyNumbers,
  		emitData(){
  			const vm = this;
				const currentPayload = createDinamycPayload(vm.frm);

				vm.$emit('data', currentPayload);
  		},
  		checkAndGet(check = true) {
  			const vm = this;
  			if(!valObjNull(vm.frm, ['state'])) return vm.alert = check;
  			if(!valNumberInt(vm.frm.months, [1, 100])) return vm.alert = check;
  			
  			vm.alert = false;

  			const { range:{ id: condition }, months } = vm.frm;
  			const currParams = getStaticParams({condition, months});

				// vm.frm.matchs = 0;
  			vm.disabled = true;
  			vm.$http.get(`/votaciones/announ/verify?section=postulates${currParams}`).then((res) => {
  			// 	// console.log(res);
  				// vm.frm.matchs = (vm.frm.months) ? res.data : 0;
  				vm.frm.matchs = res.data;
	  			vm.disabled = false;
	  			vm.emitData();
  			});
  		}
  	},
  	mounted(){
  		const vm = this;

			if(vm.mode) {
				const initialDataEdit = Object.entries(vm.data);

				for(const [key, value] of initialDataEdit) {
					vm.frm[key] = value;
				}
				vm.checkAndGet(false);
				// console.log('edit CurrPostulates', vm.data);						
				// console.log('edit CurrPostulates', vm.frm);						
			}
  	}
	}

</script>
