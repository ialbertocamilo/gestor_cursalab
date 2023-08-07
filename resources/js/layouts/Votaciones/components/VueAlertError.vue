<template>
	
	<v-alert v-model="alert" type="error" class="my-1" dismissible>
		<div>
			<slot></slot>
		</div>
		<ul class="mb-0">
			<li v-for="(err, index) of errors" :key="index" 
					v-text="err.text">
			</li>
		</ul>
	</v-alert>

</template>

<script>
	
	export default {
		name: 'vue-alert-error',
		props: { 
			errors: { type: Array, default: [] }
		},
		watch:{
			errors(arr) {
				const vm = this;
				vm.alert = arr.length > 0; 
			}
		},
		data() {
			return {
				alert : false
			}
		}
	}

</script>