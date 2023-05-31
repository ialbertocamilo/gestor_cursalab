<template>
	<div>
		<!-- usa length como validacion -->
		<label v-show="label.length" :for="name" v-text="label"></label>
		<select :disabled="disabled" v-model="currentOption" class="form-control" :id="name" @change.prevent="$emit('change', currentOption)"> <!-- emitira el value -->
			<option v-if="selected.length" value selected v-text="selected"></option>

			<!-- aplica para array de objetos que cumplan con las llaves ejem: {value:'all', text:'Todos'} -->
			<option v-if="itemKeys" v-for="(item, index) of items" :key="index" 
							:value="item.value" v-text="item.text"></option> 

			<!-- aplica para arrays ejem: ['Todos','Activos','Inactivos'] -->
			<option v-if="!itemKeys" v-for="item of items" :key="item"
							:value="item" v-text="item"></option>
		</select>
	</div>
</template>

<script>
	export default {
		name:'vue-select',
		props: {
			label:{ type: String, default:'Seleccione opción.' },
			name:{ type: String, default:'v-select' },
			value:{ type: String, default: '' },
			items:{ type: Array, require: true },
			disabled:{ type: Boolean, default:false },
			selected:{ type: String, default:'Seleccione una opción' },
			itemKeys:{ type: Boolean, default:false }
		},
		data(){
			return {
				currentOption: this.value
			}
		}
	}
</script>
