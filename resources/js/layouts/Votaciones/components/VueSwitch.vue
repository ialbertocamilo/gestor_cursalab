<template>
	<div class="d-flex justify-content-center">
		<v-switch class="w-3 h-1" :color="color" value :input-value="value" :disabled="disabled" 
															@click="$emit('change')"></v-switch>
	</div>
</template>

<script>
	export default {
		name: 'vue-switch',
		props:{ 
					value:{ type:Boolean, default:false },
					color:{ type:String, default:'primary' },
					disabled:{ type:Boolean, default: false }
					}
	}
</script>

