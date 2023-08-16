<template>
	<div>
		<div class="d-flex align-items-center justify-content-end">
			<v-checkbox class="h-1 mx-3" v-model="checkSuccess" 
						   :disabled="checkRejected" @change="emitStateData" />
			<v-checkbox class="h-1 mx-3" v-model="checkRejected" 
							:disabled="checkSuccess" @change="emitStateData" />
		</div>
	</div>
</template>

<script>

	export default {
		name: 'CheckBoxSustent',
		props: { item: Object },
		data() {
			return {
				checkSuccess: this.item.state === true,
				checkRejected: this.item.state === false
			}
		},
		methods:{
			emitStateData() {
				const vm = this;

				const { id } = vm.item;
				const payload = { id, accepted: vm.checkSuccess, 
											 rejected: vm.checkRejected };
				const state = { id, 
									 accepted: (vm.item.state === true),
									 rejected: (vm.item.state === false)
									}

				vm.$emit('data', { payload, state }); 
				// payload : data del check
				// state: el valor inicial
			}
		}
	}

</script>