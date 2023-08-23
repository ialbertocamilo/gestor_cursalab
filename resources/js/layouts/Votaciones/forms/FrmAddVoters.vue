<template>
	<div>
		<v-row class="my-2">
			<v-col cols="12" class="p-0">
				<v-alert class="mb-3 mx-3" v-model="alert" text type="error" dismissible>
					Por favor seleccione: Módulo, condición, ingrese meses y seleccione un creterio.
				</v-alert>	

				<span class="ml-3">Sección para definir qué usuarios serán votantes y podrán elegir a sus postulantes/candidatos.</span>
			</v-col>
			<v-col cols="12" class="d-flex">
				<v-row>
					<v-col cols="4" class="d-flex flex-column align-self-start">
						<div class="mb-3">
							<span>Asociación</span>
							<DefaultInfoTooltip :right="true" text="Relaciona a los votantes con los postulantes/candidatos." />
						</div>
						<DefaultAutocomplete 
							dense
							return-object 
							clearable
							item-text="name" 
							label="Seleccione criterio *"
							:loading="true" 
							v-model="frm.requirement" 
							:items="itemsRequirement" 
							:rules="rules.requirement" 
							@input="getRequirementValues"
						/>
					</v-col>
					<v-col cols="4" class="d-flex flex-column">
						<div class="mb-4">
							<span>Valor de asociación (Opcional) </span>
						</div>
						<DefaultAutocomplete 
							dense
							multiple 
							return-object 
							clearable
							item-text="value_text"
							label="Seleccione valor de criterio"
							:loading="subloader" 
							v-model="frm.requirement_values" 
							:items="itemsRequirement_Values" 
						/>
					</v-col>
					<v-col cols="4">
						<div class="mb-3">
							<span> Validez </span>
							<DefaultInfoTooltip :right="true" text="Porcentaje (%) de votaciones que deben ser realizadas por criterio." />
						</div>
						<v-text-field 
							outlined 
							dense
							class="custom-default-input" 
							label="Ingrese porcentaje *"  
							type="number" 
							v-model="frm.porcent"
							:rules="rules.porcent" 
							min="1" step="1"
							@keypress="passOnlyNumbers" 
						/>
					</v-col>
				</v-row>
			</v-col>
			<v-col cols="12" class="py-0">
				<v-row>
					<v-col cols="4">
						<DefaultSelect 	
							clearable
							dense
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
							clearable
							outlined 
							dense
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
	name: 'FrmAddVoters',
	props:{ 
		data: { type: Object }, //readonly
		mode: { type: Boolean, default: false },
	},
	inject:['ModulesProvide'],
	data() {
		return {
			// data
			itemsRange: ITEMS_RANGE,

			loader: false,
			subloader: false,
			itemsRequirement: [],
			itemsRequirement_Values:[],
			// lista de procentajes
			// itemsPorcent: [ { id: 10, name:'10 %'}, { id: 20, name:'20 %'},
			// 								{ id: 30, name:'30 %'}, { id: 40, name:'40 %'},
			// 								{ id: 50, name:'50 %'}, { id: 60, name:'60 %'},
			// 								{ id: 70, name:'70 %'}, { id: 80, name:'80 %'},
			// 								{ id: 90, name:'90 %'}, { id: 100, name:'100 %'},
			// 							],
			frm: {
				range: null,
				months: null,
				matchs: 0,

				requirement: null,
				requirement_values: [],
				porcent: null
			},
				disabled: false,
				alert: false,

			// validations
				rules:{
					range: setRules('required'), 
					months: setRules('required','min:3'), 

					requirement: setRules('required'),
					porcent: setRules('required','min:3')
				}
		}
	},
	watch: {
		'frm.range':{
			handler() {
				const vm = this;
					vm.frm.matchs = 0;
					vm.emitData();
				// if(range && vm.frm.months) vm.checkAndGet();
				// else vm.emitData();
			}
		},
		'frm.months':{
			handler() {
				const vm = this;

					vm.frm.matchs = 0;
					vm.emitData();
				// if(months) vm.checkAndGet();
				// else vm.emitData();
				}
			},
		'frm.requirement':{
			handler(state) {
				const vm = this;

				if(state && vm.frm.matchs) vm.emitData();
				else vm.emitData();
			}
		},
		frm:{
			handler(data) {
				const vm = this;
				const { porcent } = data;
				if(!valNumberInt(porcent, [1, 100])) vm.frm.porcent = null;

				vm.emitData();
			},
			deep: true
			// immediate: true
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
			// if(!valObjNull(vm.frm, ['porcent','requirement'])) return vm.alert = true;
			if(!valObjNull(vm.frm, ['matchs', 'months','porcent','requirement','requirement_values'])) return vm.alert = check;
			if(!valNumberInt(vm.frm.months, [1, 100])) return vm.alert = check;
			if(!valObjNull(vm.frm, ['porcent','requirement_values'])) return vm.alert = check;
			if(!vm.ModulesProvide.modules) return vm.alert = check;

			vm.alert = false;

			let {   range:{ id: condition }, 
					months, 
					requirement,
					requirement_values } = vm.frm; //check index

			if(requirement_values.length > 0) requirement_values = requirement_values.map((ele) => ele.id);

			const currParams = getStaticParams({
				condition, months, requirement: requirement.id, requirement_values,
				modules: vm.ModulesProvide.modules
			});
			vm.disabled = true;

			// console.log('currParams', currParams);

			vm.$http.get(`/votaciones/verify?section=voters${currParams}`)
				.then((res) => {
				console.log(res);
				vm.frm.matchs = res.data;
				vm.disabled = false;
			});
		},
		getRequirementValues(payload, clean = true) {
			const vm = this;
			// console.log(payload);
			vm.itemsRequirement_Values = [];
			if (payload) {
				vm.subloader = true;
				
				vm.$http.get(`/votaciones/criterion/values/${payload.id}`)
					.then((res) => {
						const { data } = res.data;
						vm.itemsRequirement_Values = data;
						vm.subloader = false;
						if(clean) vm.frm.requirement_values = [];
					});
			}else{
				vm.frm.requirement_values = [];
			}
		},
	},
	mounted() {
		const vm = this;
		if(vm.mode) {
			const initialDataEdit = Object.entries(vm.data);

			for(const [key, value] of initialDataEdit) {
				vm.frm[key] = value;
			}
			vm.checkAndGet(false);
			vm.getRequirementValues(vm.frm.requirement, false);
			// console.log('edit CurrVoters', vm.frm);						
		}

	},
	created() {
		const vm = this;
		vm.loader = true;

		vm.$http('/votaciones/criterion/get-data')
			.then((res) => {
				const { data } = res.data;
				vm.itemsRequirement = data;
				vm.loader = false;
			});
	}
}
</script>
