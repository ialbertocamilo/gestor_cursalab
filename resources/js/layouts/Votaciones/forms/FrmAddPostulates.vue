<template>
	<div>
		<v-row class="my-2">
			<v-col cols="12" class="p-0">
				<v-alert class="mb-3 mx-3" v-model="alert" text type="error" dismissible>
					Por favor seleccione: Módulo, condición, ingrese meses.
				</v-alert>	
				<span class="ml-3">Sección para definir qué usuarios estarán en la(s) lista(s) de selección.</span>
			</v-col>
			<v-col cols="12">
				<v-row>
					<v-col cols="4">
						<DefaultSelect 
							dense
							clearable
							return-object 
							item-text="name" 
							label="Seleccione condición"
							v-model="frm.range" 
							:items="itemsRange" 
							:rules="rules.range" 
						/>
					</v-col>
					<v-col cols="4">
						<v-text-field 
							outlined 
							dense
							clearable
							class="custom-default-input" 
							label="Meses" 
							type="number" 
							v-model="frm.months"
							:rules="rules.months" 
							min="1" 
							step="1"
							@keypress="passOnlyNumbers" 
						/>
					</v-col>
					<v-col cols="4">
						<div class="rounded border-solid-primary d-flex">
							<div class="w-25">
								<v-btn 
									class="p-3"
									color="primary" 
									:loading="disabled" 
									:disabled="disabled" 
									@click.prevent="checkAndGet(true)"
								> 
									<span class="fas fa-check mr-2"></span> Verificar 
								</v-btn>
							</div>
							<div class="w-75 align-self-center text-center">
								<div>
									<span v-if="!frm.matchs && !disabled">
										<span class="fas fa-ban"></span> Sin datos.
									</span>
									<span v-if="!frm.matchs && disabled">
										 <v-progress-circular indeterminate color="primary" size="20" width="2"></v-progress-circular>
									</span>
									<span v-if="frm.matchs">
										<span class="text-primary-sub fa-lg fas fa-users"></span>
										<span v-text="frm.matchs"></span>
									</span>
								</div>
							</div>
						</div>
					</v-col>
				</v-row>
			</v-col>
			<v-col cols="12" class="py-0">
				<div class="d-flex align-items-center">
					<div>
						<DefaultToggle 
							class="mt-0" 
							v-model="frm.state" 
							no-label
						/>
					</div>	
					<span class="mt-4">Revisión de postulaciones.</span>
					<div class="mt-3 ml-2">
						<DefaultInfoTooltip 
							:right="true" 
							text="Revisa los argumentos de postulación, enviados por los votantes para cada postulante." 
						/>
					</div>
				</div>
			</v-col>
		</v-row>
	</div>

</template>

<script>
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
		props:{
			data: { type: Object },
			mode: { type: Boolean, default: false },
		},
		inject: ['ModulesProvide'],
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
				if(!vm.ModulesProvide.modules) return vm.alert = check;

				vm.alert = false;

				const { range:{ id: condition }, months } = vm.frm;
				const currParams = getStaticParams({ condition, months, modules: vm.ModulesProvide.modules });

				vm.disabled = true;

				vm.$http.get(`/votaciones/verify?section=postulates${currParams}`)
				.then((res) => {
					// vm.frm.matchs = (vm.frm.months) ? res.data : 0;
					// console.log(res);
					vm.frm.matchs = res.data;
					vm.disabled = false;
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
