<template>
	<div>
		<!-- renderizado de la imagen dinamicamente -->
		<div v-if="currRestoreState" class="text-center">
			<span v-text="currRestoreFile.name"></span>
			<v-btn color="primary" text @click="setRestoreFile">
				<span class="fas fa-sync"></span>
			</v-btn>

			<v-btn color="red" text @click="removeStoreFile">
				<span class="fas fa-trash-alt"></span>
			</v-btn>

		</div>
		<!-- input para subir imagen -->
		<v-file-input class="mt-2" :label="label" :accept="accept" outlined dense 
									v-model="currFile" :rules="rules" @change="$emit('input', currFile)"></v-file-input>
	</div>
</template>

<script>

	import { checkType } from '../utils/UtlValidators.js';
	const { isType } = checkType;

	export default {
		name:'vue-file-input',
		props: {
			// required:{ type: Boolean, default: false },
			value:{ default: null },
			label:{ type: String, default:'Seleccione archivo.' },
			rules:{ type: Array },

			clear:{ type: Boolean, default: false },
			accept:{ type: String, default:'*' },
		},
		data() {
			return {
				currFile: null,

				currRestoreState: false,
				currRestoreFile: { name: null }
			}
		},
		watch:{
			value:{
				handler(val) {
					const vm = this;

					if(isType(val, 'string')) {
						vm.currRestoreFile.name = val;
						vm.currRestoreState = true;
						// console.log('image string', val)
					}else if(val !== null && vm.clear) {
						vm.currRestoreFile.name = null;
						vm.currRestoreState = false;
						vm.currFile = val;
						// console.log('image edit', val);
					}else if(val === null && !vm.clear) {
						vm.currRestoreFile.name = null;
						vm.currRestoreState = false;
						vm.currFile = null;
						// console.log('image null', val);
					}else {
						vm.currFile = null;
					}
				},
				immediate: true
			}
		},
		methods: {
			removeStoreFile() {
				const vm = this;
				vm.$emit('input', null);
			},
			setRestoreFile() {
				const vm = this;
				const { name } = vm.currRestoreFile;

				vm.currFile = null;
				vm.$emit('input', name);
			}
		}
	}

</script>