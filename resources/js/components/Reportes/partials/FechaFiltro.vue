<template>
	<div>
		<div class="mb-3">

			<!-- label - tooltip -->
			<div class="d-flex justify-content-between">
				<small class="text-muted" v-text="labelStart"></small>
	         <div v-show="tooltipStart.length" 
	         	  :tooltip="tooltipStart"
	              :tooltip-position="tooltipPosition"
	              class="z-100">
		          <v-icon class="info-icon">mdi-information-outline</v-icon>
		      </div>
			</div>
			<!-- label - tooltip -->
	
			<div class="input-group">
				<input
					type="date"
					autocomplete="off"
					class="datepicker form-control hasDatepicker"
					v-model="start"
					@change=" $emit('update-start')"
				/>
				<b-form-datepicker
					v-model="start"
					button-only
					button-variant="light"
					locale="es-PE"
					aria-controls="date-start"
					today-button
                    right
					label-today-button="Hoy"
					reset-button
					label-reset-button="Reiniciar"
					selected-variant="danger"
				></b-form-datepicker>
			</div>
		</div>
		<div class="mb-3">

			<!-- label - tooltip -->
			<div class="d-flex justify-content-between">
				<small class="text-muted" v-text="labelEnd"></small>
			   <div v-show="tooltipEnd.length" 
	         	  :tooltip="tooltipEnd"
	              :tooltip-position="tooltipPosition"
	              class="z-100">
		          <v-icon class="info-icon">mdi-information-outline</v-icon>
		      </div>
			</div>
			<!-- label - tooltip -->

			<div class="input-group">
				<input
					type="date"
					autocomplete="off"
					class="datepicker form-control hasDatepicker"
					v-model="end"
					@change=" $emit('update-end')"
				/>
				<b-form-datepicker
					v-model="end"
					button-only
					button-variant="light"
					locale="es-PE"
					aria-controls="date-start"
					today-button
                    right
					label-today-button="Hoy"
					reset-button
					label-reset-button="Reiniciar"
					selected-variant="danger"
				></b-form-datepicker>
			</div>
		</div>
	</div>
</template>
<script>
export default {
	props: {
		// label
		labelStart: { 
			type: String, 
			default:'Fecha inicial'
		},
		labelEnd:{
			type: String,
			default:'Fecha final'
		},
		// tooltip
		tooltipStart: {
			type: String,
			default:''
		},
		tooltipEnd: {
			type: String,
			default:''
		},
		tooltipPosition:{
			type: String,
			default: 'left'
		}
	},
	data() {
		return {
			start: undefined,
			end: undefined
		};
	}
};
</script>
<style scoped>
	.z-100 {
		z-index: 100;
	}

</style>
