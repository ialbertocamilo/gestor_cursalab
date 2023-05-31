<template>
  <div>

    <fieldset class="editor px-3 pt-2">
		<!-- {{ currentFile }} -->      
 				<legend v-text="label"></legend>
      	<vue-image v-model="currentFile" 
      						 :label="subLabel" 
      						 :placeholder="currentPlaceHolder"
      						 :icon-place-holder="iconPlaceHolder"
      						 :img-place-holder="imgPlaceHolder"

									 :preview="filePreview" 
									 :accept="currentExtensions" 
								 	 :clearable="clearable"

									 :check-dimensions="checkDimensions"
									 :stack-dimensions="stackDimensions"
                   :contain-image="containImage"
							 		 :height="height"
							 		 :size="defaultSize" 
							 		 @input="$emit('input', $event)"
							 		 
							 		 />
    </fieldset>

    <v-btn v-if="showButton" class="mt-1" color="primary" block elevation="0"
           @click="openSelectPreviewMultimediaModal">
           <span class="fas fa-photo-video mr-2"></span> Seleccionar multimedia
    </v-btn>

    <SelectMultimedia v-if="showButton"
					            :ref="modalPreviewMultimedia.ref"
					            :options="modalPreviewMultimedia"
					            :custom-filter="fileTypes"
					
					            width="85vw"
					            @onClose="closeSelectPreviewMultimediaModal"
					            @onConfirm="onSelectMediaPreview" />
	
	</div>
</template>

<script>

	// === components ===
	import SelectMultimedia from "../../../components/forms/SelectMultimedia.vue";
	import VueImage from '../components/VueImage.vue';
	// === components ===

	// === utils ===
	import { getCurrentExtensionsTypes } from '../utils/UtlComponents.js';
	import { getParseByUrl } from '../utils/UtlPathUrls.js';
	import { checkType } from '../utils/UtlValidators.js';
	const { isType } = checkType;
	// === utils ===

	export default {
    name: 'vue-upload-content-multimedia',
    components: { SelectMultimedia, VueImage },
    props: {
      label: { type: String, required: true },
      subLabel: { type: String, required: true },
      showButton: { type: Boolean, default: true },
      iconPlaceHolder: { type: String },
      imgPlaceHolder: { typ: String },

      value: { required: true },
      clearable: { type: Boolean, default: false },
	    fileTypes: { type: Array, default() { 
	    														return []; 
	    													} },

			filePreview: { type: Boolean, default: true }, 
			checkDimensions: { type: Boolean, default: false },
			stackDimensions: { type: Object, default() {
																				return { width: 100, height: 100};
																			} },
      containImage:{ type: Boolean, default: false },
			height:{ type: Number, default: 120 },
			defaultSize:{ type: Number, default: 10 } // size in MB
    },
    data() {
      return {
      	currentFile: null,
        fileSelected: null,

        modalPreviewMultimedia: {
          ref: 'modalSelectPreviewMultimedia',
          open: false,
          title: 'Buscar multimedia',
          confirmLabel: 'Seleccionar',
          cancelLabel: 'Cerrar'
        }
      }
    },
  	watch: {
			value: {
				handler(val) {
					const vm = this;

					if(val === null) {
						vm.currentFile = null;
						vm.fileSelected = null;
					}
	
					if(isType(val, 'string')) {
						vm.currentFile = val;
						vm.fileSelected = val;
					}

					if(isType(val, 'object')){
						vm.currentFile = val;
						vm.fileSelected = null;
					}
				},
				immediate: true
			}
		},
    computed: {
    	// only for type images
    	checkTypeImage() {
    		const { fileTypes, checkDimensions } = this;
    		return (fileTypes.includes('images') && checkDimensions) ? true : false;
    	},
    	currentPlaceHolder() {
    		const { fileTypes } = this;
    		const count = fileTypes.length;
    		const checkKeyImages = (fileTypes.includes('images') && count === 1); // check images key only

				return (count > 1) ? '(Sin archivo).' : '(Sin imagen).';    		
    	},
    	currentExtensions() {
    		const { fileTypes, mixin_extensiones } = this;
    		return getCurrentExtensionsTypes(fileTypes, mixin_extensiones); //f: mixin.js
    	}
    },
    methods: {
      openSelectPreviewMultimediaModal() {
        const { $refs, modalPreviewMultimedia } = this;
        const { [modalPreviewMultimedia.ref]: { getData } } = $refs; // data by ref

				modalPreviewMultimedia.open = true; 
				modalPreviewMultimedia.ref && getData();
      },
      onSelectMediaPreview(media) {
      	const vm = this; 
				// console.log('media',  media);
				const { ext, file, image, id } = media;

				const instance = getParseByUrl(image);
				instance.pathname = file;

				const currentMedia = (ext === 'scorm') ? file : instance.href;

        vm.fileSelected = currentMedia; // index to string
        vm.currentFile = currentMedia; //preview linked

				// console.log('media',  { currentMedia, media, selected: vm.fileSelected });

        vm.$emit('input', vm.fileSelected);
        vm.closeSelectPreviewMultimediaModal();
      },
      closeSelectPreviewMultimediaModal() {
      	const { modalPreviewMultimedia } = this;
        modalPreviewMultimedia.open = false
      }
    }
}

</script>

<style>

	.btn-close-clean {
		top: 0;
		right: 0;
	}	

</style>



