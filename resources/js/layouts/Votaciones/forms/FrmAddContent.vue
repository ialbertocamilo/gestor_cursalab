<template>
	<div>
		<v-row class="my-2">
			<v-col cols="12" md="8">
				<v-card class="pr-2">
					<v-card-title>Lista de contenidos</v-card-title>
					<v-card-text :class="frmadd.content_list_overflow">
						<v-row class="mt-2">
							<v-col v-for="(item, index) of contents" :key="index" cols="12" class="pt-0">
								<div class="border rounded border-primary p-2 d-flex justify-content-between align-items-center"
										 :class="item.state ? frmadd.bg_primary : '' " draggable="true">
									<div>
										<span class="text-primary" :class="`fas ${setCurrentIcon(item).icon} f-md`"></span> 
										<span class="ml-2 font-w" v-text="item.title"></span>
									</div>
									<div>
										<span class="text-uppercase" 
													v-text="(setCurrentIcon(item).ext === 'html') ? 'SCORM' :
																	setCurrentIcon(item).ext"></span> <!-- opcional -->
										
										<v-btn text color="primary" @click="editCurrentData(index)"> 
											<span class="fas fa-pen"></span>
										</v-btn>
										<v-btn text color="red" @click="deleteCurrentData(index)"> 
											<span class="fas fa-trash"></span> 
										</v-btn>
									</div>
								</div>
							</v-col>
						</v-row>
						<p v-show="!contents.length" class="text-center mt-4">
							<span class="fas fa-ban"></span> Por favor agrega contenido.
						</p>
					</v-card-text>
				</v-card>
			</v-col>
			<v-col cols="12" md="4">
				<v-btn block color="primary" @click="openDialogCard(true)">
					<span class="fas fa-plus mr-2"></span> Agregar
				</v-btn>
			</v-col>
			<v-col cols="12">
				<DefaultInput label="Pregunta para encuesta" v-model="question" 
											maxlength="70" :counter="70"></DefaultInput>	
				<span>
					<b>Nota:</b> La "Encuesta" será parte de la campaña solo si tiene texto el campo "Pregunta de encuesta". 
				</span>			
				<!-- 
					<DefaultInput label="Pregunta para encuesta" v-model="question" :rules="rules.question" 
											maxlength="70" :counter="70" required></DefaultInput>				
				 -->

			</v-col>
		</v-row>

		<v-dialog v-model="dialogCard" persistent scrollable max-width="800px">
			<v-card>
		    <v-card-title class="d-flex justify-content-between primary text-white">
		    	<span v-text="dialogState.title"></span> <!-- titulo dinamico -->
		    	<v-btn icon @click="clearCurrentData">
		  		  <v-icon class="text-white"> mdi-close </v-icon>
		    	</v-btn>
		    </v-card-title>
		    <v-card-text class="pt-5">
					 <v-form v-model="valid" ref="form" lazy-validation>
					 	<v-row>
					 		<v-col cols="12">
					 			<v-alert v-model="alertForm" text type="error" dismissible>
					 				El título del contenido se repite.
					 			</v-alert>
					 		</v-col>
							<v-col cols="12" md="6">
								<div v-if="!frm.linked" class="mb-4">
									<vue-upload-content-multimedia 
																		label="Seleccione contenido" 
																		sub-label="Seleccione archivo"
 																		v-model="frm.file" 
 																		icon-place-holder="fa-file"
 																		:file-types="['image', 'video', 'audio', 'pdf', 'scorm', 'office']" 
 																		:default-size="300"
 																		:clearable="clearableMultimedia" />
								</div>
								
								<div v-if="!frm.file">
									<DefaultInput v-model="frm.linked" label="Link (Youtube o Vimeo)" :rules="rules.linked"/>
								</div>

								<div class="mt-2">
									<v-switch v-model="frm.state" label="Estado"></v-switch>
								</div>

							</v-col>				 		
							<v-col cols="12" md="6">
								<DefaultInput label="Título de contenido *" 
															:rules="rules.title"  
															:counter="70" maxlength="70" 
															required
															v-model="frm.title" />

							  <v-textarea label="Descripción de contenido *" 
							  						:rules="rules.description" 
							  						:counter="200" maxlength="200"
							  					  class="custom-default-input" 
							  					  outlined 
							  					  rows="6" 
										  			v-model="frm.description" required />
							</v-col>
					 	</v-row>
					 </v-form>
		    </v-card-text>
		    <v-card-actions class="d-flex justify-content-center pb-4">
		      <v-btn color="gray" class="mr-2" @click="clearCurrentData"> Cancelar </v-btn>
		      <v-btn color="primary" @click="saveCurrentData" v-text="dialogState.btn"></v-btn> <!-- Boton dianmico dinamico -->
		    </v-card-actions>
	    </v-card>
		</v-dialog>

	</div>

</template>

<script>
	// === components ===
	import VueUploadContentMultimedia from '../components/VueUploadContentMultimedia.vue'
	// === components ===

	// === utils ===
	import { setRules, Stackvalidations } from '../utils/UtlValidators.js';
	import { getFileExtension } from '../utils/UtlGeneral.js';
	const { valObjNull, valString, valDomains } = Stackvalidations;
	// === utils ===

	export default {
		name: 'FrmAddContent',
		props: { 
			data: { type: Object },
			mode: { type: Boolean, require: true }
	  },
	  components:{ VueUploadContentMultimedia },
		data() {
			return {
				valid: false,
				alertForm: false,

				dialogCard: false,
				dialogMode: false,
				dialogIndex: null, // se requerira con dialogCard = true
				
				clearableMultimedia: true,

				// content fprm
				contents: [],
				question: null,
				frm: {
					title: null,
					description: null,
					file: null,
					linked: null,
					state: true
				},
				rules:{
					title: setRules('required','max:5', 'min:70'),
					description: setRules('required','max:5', 'min:200'),
					file: setRules('required'),
					linked: setRules('required', 'max:5'),
					// question: setRules('required','max:10', 'min:70')
				}
			}
		},
		computed:{
			dialogState() {
				return this.dialogMode ? { title: 'Editar contenido', btn: 'Editar' } :
																 { title: 'Agregar contenido', btn: 'Agregar' };
			}
		},
		watch:{
			question() {
				this.emitData();
			}
		},
		methods: {
			emitData() {
				const vm = this;
				const payload = {};

				// if(vm.question !== null) vm.emitValid('question', !valString(vm.question, [10, 70]));
				if(vm.question !== null) vm.emitValid('question', !valString(vm.question, [0, 70]));
				const ArrayKeys = ['contents', 'question'];

				for(const key of ArrayKeys) {
					const current = vm[key];
					const currentParse = (typeof current === 'string') ? current.trim() : current;

					if(current) {
						if(Array.isArray(current)) {
							if(current.length) payload[key] = current;//salto de flujo 
						} else payload[key] = current;
					}
				}

				// console.log('emit content', payload);
				vm.$emit('data', payload);

			},
			emitValid(attr, state) {
				const vm = this;
				vm.$emit('valid', { attr, state } );
			},

			setCurrentIcon( { file, linked } ) {
				const vm = this;
				const fileString = (typeof file === 'string');

				let name;

				if(fileString) name = file;
				else if(file === null) name = `${linked}.link`;
				else name = file.name;

				return getFileExtension(name); //si quisieramos por tipo debe pasar file.type
			},
			openDialogCard(state, sbstate = false) {
				const vm = this;
				vm.dialogCard = state;
				vm.dialogMode = sbstate;

			},
			clearCurrentData() {
				const vm = this;

				vm.$refs.form.reset();

				vm.frm.title = null;
				vm.frm.description = null;
				vm.frm.file = null;
				vm.frm.linked = null;

				vm.frm.state = false;
				setTimeout(() => vm.frm.state = true, 100);

				vm.dialogIndex = null;
				vm.alertForm = false;
				vm.clearableMultimedia = !vm.clearableMultimedia;

				vm.openDialogCard(false); //devuelve el estado original de dialogMode y dialogCard
			},
			editCurrentData(index) {
				const vm = this;
				const data = vm.contents[index];

				for(const prop in vm.frm) {
					vm.frm[prop] = data[prop];
				}
				vm.dialogIndex = index;
				vm.openDialogCard(true, true);
			},
			saveCurrentData() {
				const vm = this;	
				const counter = vm.contents.length;
				vm.$refs.form.validate(); //para validar formulario

				const checkIfExistContent = (state = false) => {
					const { title: currTitle } = vm.frm;
					const localContents = [...vm.contents];
					(state && localContents.splice(vm.dialogIndex, 1));
					
					return localContents.some(({title}) => title === currTitle);
				};

				const stateVal = (counter > 0 && !vm.dialogMode) ? checkIfExistContent() :
												 (counter > 1 && vm.dialogMode) ? checkIfExistContent(true) : false;
				if(stateVal) return vm.alertForm = true;

				const { title, description, file, linked } = vm.frm;
				if(!file && !linked) return; //console.log('ambos nulos')
				if(!valObjNull(vm.frm, ['file', 'linked'])) return; //console.log('jumped valObjNull')
				if(!valString(title,[5, 70])) return;
				if(linked && !valDomains(linked, ['youtube','youtu','vimeo'])) return;
				if(!valString(description,[5, 200])) return;

				// console.log(vm.frm, vm.dialogIndex);
				// validamos el dialogMode 
				if(vm.dialogMode) vm.contents[vm.dialogIndex] = { ...vm.frm };
				else vm.contents.push({...vm.frm});

				vm.clearCurrentData();
				vm.emitData();
			},
			deleteCurrentData(index) {
				const vm = this;
				vm.contents.splice(index, 1);
				vm.emitData();
			}
		},
		mounted(){
			const vm = this;
			if(vm.mode) {
				const initialDataEdit = Object.entries(vm.data);

				for(const [key, value] of initialDataEdit) {
					vm[key] = value;
				}
				// console.log('edit CurrContents', vm.contents, vm.question);						
			}
		}
	}

</script>

<style module="frmadd">

	.content_list_overflow{
		max-height: 13.5rem;
		overflow-y: auto;
	}

	.bg_primary{
		background-color: rgba(138, 43, 226, 0.2);
	}
</style>

