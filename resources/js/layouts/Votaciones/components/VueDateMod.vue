<template>
	<div>
		<!-- usa length como validacion -->
		<label v-show="label" :for="name" v-text="label"></label>
		<input :id="name" :value="value" :type="type" class="text-center form-control" placeholder="Seleccione fecha" 
					 @input="emitCurrentDate">
	</div>
</template>

<script>

	// funciones local para date
	const currentDateType = (val) => ['date','datetime-local'].includes(val);
	const stcParsedDate = (val, flag = false) => {
		const date = new Date(val);
		const currdata = flag ? date.toLocaleTimeString('en-US') : 
														date.toLocaleDateString('es-ES', { timeZone:'America/Lima'});
		return currdata;
	}; 


	export default {
		name:'vue-date-mod',
		props: {
			label:{ type: String },
			value:{ type: String },
			name:{ type: String, default:'v-date'},
			parse:{ type: Boolean, default:false }, /*el parseo es por dia/mes/año - o dia/mes/año hrs:min:secs*/
			type:{ type: String, default:'date',
				validator(val) {
					return currentDateType(val); /*valida el tipo date o datetime-local*/
				}
			}
		},
		methods: {
			emitCurrentDate(evt) {
				const { value } = evt.target;
				const vm = this; //vue ref

				if(!vm.parse) return vm.$emit('input', value);

				const currentValue = (vm.type === 'date') ? stcParsedDate(value) :
																								 `${stcParsedDate(value)} ${stcParsedDate(value, true)}` ;
				return vm.$emit('input', currentValue);
			}
		}
	}
</script>
