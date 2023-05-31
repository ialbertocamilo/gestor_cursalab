<template>
	<div>
		<!-- renderizado de la imagen dinamicamente -->
		<div v-if="currRestoreState" 
				class="position-relative text-center d-flex flex-column">
			<span v-text="currRestoreFile.name"></span>

			<div class="position-absolute btn-control-vue-image">
				<v-btn color="primary" text class="min-w-2 px-1" @click="setRestoreFile">
					<span class="fas fa-sync"></span>
				</v-btn>

				<v-btn color="red" text class="min-w-2 px-1" @click="removeStoreFile">
					<span class="fas fa-trash-alt"></span>
				</v-btn>
			</div>
		</div>

		<!-- render errors  -->
		<div v-if="validImageSize && alert">
			<v-alert text v-model="alert" type="error" dismissible>
				<span>Por favor ingrese las dimensiones correctas.</span>
			</v-alert>
		</div>

		<div v-if="validFileSize && alert">
			<v-alert text v-model="alert" type="error" dismissible>
				<span>Has superado el tamaño limite permitido.</span>
			</v-alert>
		</div>
		<!-- render errors  -->
		<v-img v-if="preview" alt="alt-imagen"
           class="border"
           :contain="containImage"
           :class="containImage && 'bg-no-repeat' "
           :src="previewFile" :height="height">
			<template v-slot:default>
	      <v-row v-show="previewFile === null" 
	      			 class="fill-height ma-0" 
	      			 align="center" 
	      			 justify="center">
	      	
	      	<div v-if="imgPlaceHolder">
	      			<v-img class="bg-no-repeat opacity-70" 
	      						 :src="imgPlaceHolder" 
	      						 :height="height">
	      				<template v-slot:default>
	      					<v-row class="fill-height ma-0" 
	      			 					 align="center" 
	      			 					 justify="center">
	      			 					<div class="font-weight-bold text-white">
	      			 					 	<span class="mr-2 f-md fas" :class="iconPlaceHolder"></span> 
		      								<span>Imagen por defecto</span>
	      			 					</div> 	
	      					</v-row>
	      				</template>
	      			</v-img>
	      	</div>
	      	<div v-else>
		      	<span class="mr-2 f-md text-primary fas" :class="iconPlaceHolder"></span> 
		      	<span v-text="placeholder"></span>
	      	</div>

	    	</v-row>

	    	<v-row v-if="previewFileIcon"
	    				 class="fill-height ma-0" 
	      			 align="center" 
	      			 justify="center">
	      		<vue-card-file :data="dataPreviewFile" /> 
	    	</v-row>
	  	</template>
		</v-img>

		<!-- upload input file -->
		<v-file-input class="mt-2" :label="label" :accept="accept" outlined dense 
									v-model="currFile" @change="previewCurrFile"></v-file-input>
	</div>
</template>

<script>
	// === components ===
	import VueCardFile from './VueCardFile.vue';
	// === components ===

	// === utils ===
	import { getFileUrl, getParseByUrl } from '../utils/UtlPathUrls.js';
	import { getImageDimensions, getFileExtension, checkFileSize } from '../utils/UtlGeneral.js';
	import { checkType } from '../utils/UtlValidators.js';
	const { isType } = checkType;
	// === utils ===

	function getNameByPathName(pathname, prefix) {
		const stcNamePath = pathname.split(prefix);
		return stcNamePath[stcNamePath.length - 1];
	}

	function isAvailableToIcon({ image }, extension){
		return image.includes(extension);
	}

	export default {
		name:'vue-image',
		props: {
			label:{ type: String, required: true },
			placeholder: { type: String, required: true },
			iconPlaceHolder: { type: String, required: true, default:'fa-file' },
			imgPlaceHolder: { type: String },
			size:{ type: Number, required: true },

			value:{ required: true  },
			clearable: { required: true },
			preview:{ type: Boolean, default: false },
			accept:{ type: String, default:'*' },

			height:{ type: Number, default: 120 },
			checkDimensions: { type: Boolean },
      containImage:{ type:Boolean },
			stackDimensions: { type: Object }
		},
		components:{ VueCardFile },
		data() {
			return {
				// stack valid alert
				validFileSize: false,
				validImageSize: false,
				alert: false,

				// current data
				currFile: null,
				previewFile: null,

				previewFileIcon: false,
				dataPreviewFile: null,

				// temporal store file by url
				currRestoreState: false,
				currRestoreFile: { name: null, path: null }
			}
		},
		watch: {
			value: {
				handler(val) {
					const vm = this;

					if(val === null) {
						vm.currFile = null;
						vm.previewFile = null;
					}

					if(isType(val, 'string')) {
						const { currRestoreFile, mixin_extensiones } = vm;
						const { pathname } = getParseByUrl(val);
						const extension = getFileExtension(val).ext;

						const	currentNameFile = (extension === 'html') ? 
																		getNameByPathName(pathname, 'scorm') : 
																		getNameByPathName(pathname, '/');


						// vm.currFile = val; //error en value reference
						vm.currFile = null;
						vm.currRestoreState = true;

						const currStateExt = isAvailableToIcon(mixin_extensiones, extension);

						if(currStateExt) {

							const currStateExt = ['jpeg', 'jpg', 'png', 'gif'].includes(extension); 
							if(currStateExt && vm.checkDimensions) {
								vm.resolveStcDimensions(val).then((resolveDim) => {
									
									if(!resolveDim) {
										currRestoreFile.name = currentNameFile;
										currRestoreFile.path = val;

										vm.previewFile = val;
										vm.previewFileIcon = false;
									}else {
										vm.currFile = null;
										vm.currRestoreState = false;
										vm.$emit('input', null);
									}
								});

							} else {
								currRestoreFile.name = currentNameFile;
								currRestoreFile.path = val;

								vm.previewFile = val;
								vm.previewFileIcon = false;
							} 

						} else {
							currRestoreFile.name = currentNameFile;
							currRestoreFile.path = val;

							vm.previewFile = '';

							vm.dataPreviewFile = val;
							vm.previewFileIcon = true;
						}
					}

					if(isType(val, 'object')) {
						const { mixin_extensiones } = vm;
						const extension = getFileExtension(val.name).ext;
						const currStateExt = isAvailableToIcon(mixin_extensiones, extension);

						if(currStateExt) {
							vm.previewFile = getFileUrl(val);

							vm.previewFileIcon = false;
						} else {
							vm.previewFile = '';

							vm.dataPreviewFile = val;
							vm.previewFileIcon = true;
						}
						// console.log('file is here from father', val);
					}
				},
				immediate: true
			},
			clearable: {
				handler() {
					const vm = this;
			
					vm.currRestoreState = false;
					vm.currRestoreFile.name = null;
					vm.currRestoreFile.path = null;
				}
			}
		},
		methods: {
			removeStoreFile() {
				const vm = this;

				vm.previewFile = null;
				vm.alert = false;
				vm.$emit('input', null);
			},
			setRestoreFile() {
				const vm = this;
				const { path } = vm.currRestoreFile;

				vm.previewFile = path;
				vm.currFile = null;
				vm.alert = false;

				vm.$emit('input', path);
			},

			async resolveStcDimensions(currPathFile) {
				const vm = this;
				const { 
								stackDimensions: {
									width, height
								} 
							} = vm;

				const { width : resWidth, 
								height: resHeight } = await getImageDimensions(currPathFile);

				if(width !== resWidth || height !== resHeight) {
					vm.validImageSize = true;
					vm.alert = true;
					vm.currFile = null;
				
					return true;

				} else return false;
			},
			async checkImageDimensions(extension, file) {
				const vm = this;
				const currPathFile = getFileUrl(file);
				const currStateExt = ['jpeg', 'jpg', 'png', 'gif'].includes(extension); 

				if(currStateExt) {
					const resolveDim = await vm.resolveStcDimensions(currPathFile); 
					if(resolveDim) return resolveDim;
				} 
				return currPathFile;
			},
			sendEmitValueFile(extension, preview, file) {
				const vm = this;
				const { currRestoreState, mixin_extensiones } = vm;

				const currStateExt = isAvailableToIcon(mixin_extensiones, extension);

				if(currStateExt) {
					vm.previewFile = preview;
					
					vm.previewFileIcon = false;
				} else {
					vm.previewFile = '';

					vm.previewFileIcon = true;
					vm.dataPreviewFile = file;
				}


				// console.log('edit', { currStateExt, currRestoreState, preview, file } );
				if(!currRestoreState) {
					vm.$emit('input', file);
				} else if (currRestoreState && preview) {
					vm.$emit('input', file);
				} else vm.setRestoreFile(); 

			},
			resetStatusValues() {
				const vm = this;

				vm.validFileSize = false;
				vm.validImageSize = false;

				vm.alert = false;
				// vm.$emit('input', null);
			},
			
			async previewCurrFile(file) {
				const vm = this;
				if(file === null || file === undefined) return vm.$emit('input', null);

				const { checkDimensions, size } = vm;

				vm.resetStatusValues();
				if(checkFileSize(file, size)) return ( vm.validFileSize = true, 
																							 vm.alert = true,
																							 vm.currFile = null ); 
				vm.resetStatusValues();

				const extension = getFileExtension(file.name).ext;
				let contextScope;

				if(checkDimensions) {
					contextScope = await vm.checkImageDimensions(extension, file);
					if(contextScope === true) return;

				} else contextScope = getFileUrl(file);

				// console.log('holaa 3', contextScope);
				vm.resetStatusValues();
				vm.sendEmitValueFile(extension, contextScope, file);
			}

		}
	}

</script>

<style>
	.btn-control-vue-image {
		right: 0;
		top: -3.5rem;
	}
  .bg-no-repeat{
    background-repeat: no-repeat !important;
  }
  .opacity-70{
  	opacity: .7;
  }
</style>


