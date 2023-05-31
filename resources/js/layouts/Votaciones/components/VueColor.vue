<template>
	<div>
		<label v-show="label.length" :for="name" v-text="label"></label>
		<div class="d-flex">
			<input :value="value" :id="name" type="color" class="form-control" @input="$emit('input', $event.target.value)">
			<v-btn text @click="$emit('input', '')">
				<span class="fas fa-times"></span>
			</v-btn>
		</div>
	</div>
</template>

<script>
	export default {
		name:'vue-color',
		props:{ 
			value: { type: String }, 
			label: { type: String },
			name: { type: String, default:'v-color'} 
		}
	}
</script>