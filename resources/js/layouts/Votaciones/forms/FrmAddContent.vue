<template>
	<div>
		<v-row>
			<v-col cols="12" class="d-flex justify-content-between">
				<span>Agrega contenido multimedia para tu campaña.</span>
				<v-btn color="primary" @click="openDialogCard(true)">
					<span class="fas fa-plus mr-2"></span> Contenido
				</v-btn>
			</v-col>
			<v-col cols="12">

				<table class="table table-primary-sub table-scroll table-bordered-rounded table-hover mb-0">
					<thead>
						<tr>
							<th scope="col" class="text-left">Archivo</th>
							<th scope="col" class="text-center">Formato</th>
							<th scope="col" class="text-right">Opciones</th>
						</tr>
					</thead>
					<tbody class="scroll-default" style="max-height: 20rem;">
						<tr v-for="(item, index) of contents" :key="index">
							<td class="text-left">
								<div class="d-flex align-items-center mt-2">
									<span :class="`fas ${setCurrentIcon(item.file_media, item.linked).icon} fa-2x fa-fw`"></span>
									<span class="ml-2" v-text="item.title"></span>
								</div>
							</td>
							<td class="text-center">
								<span class="mt-3 text-uppercase" 
									  v-text="setCurrentIcon(item.file_media, item.linked).ext === 'html' ? 'SCORM': setCurrentIcon(item.file_media, item.linked).ext"></span>
							</td>
							<td class="text-right">
								<v-btn text color="primary" @click="editCurrentData(index)"> 
									<div class="d-flex flex-column">
										<span class="fas fa-pen"></span>
										<small>Editar</small>
									</div>
								</v-btn>
								<v-btn text color="red" @click="deleteCurrentData(index)"> 
									<div class="d-flex flex-column">
										<span class="fas fa-trash"></span>
										<small>Eliminar</small>										
									</div>
								</v-btn>
							</td>
						</tr>
						<!-- sin contenido -->
						<tr class="no-border" v-show="!contents.length">
							<td class="border-0"></td>
							<td class="text-center d-flex flex-column border-0"> 
								<span class="mdi mdi-file-search text-primary-sub fa-3x"></span>
								<p class="mb-0">En esta sección se listarán los contenidos de la campaña.</p>
							</td>
							<td class="border-0"></td>
						</tr>
					</tbody>
				</table>
			</v-col>
			<v-col cols="12">
				<div class="d-flex align-items-center">
					<div>
						<DefaultToggle 
							class="mt-0" 
							v-model="showQuestion" 
							no-label 
							@onChange="(val) => {
								if(!val) question = null;
							}"
						/>
					</div>	
					<span class="mt-3">Incluir pregunta de encuesta para la campaña.</span>
				</div>

				<div v-show="showQuestion" class="mt-6">
					<DefaultInput
						dense 
						clearable
						label="Pregunta para encuesta" 
						v-model="question" 
						maxlength="70" 
						:counter="70"
					/>	
				</div>
				<!-- 
					<DefaultInput label="Pregunta para encuesta" v-model="question" :rules="rules.question" 
											maxlength="70" :counter="70" required></DefaultInput>				
				 -->
			</v-col>
		</v-row>

		<v-dialog v-model="dialogCard" persistent scrollable max-width="600px">
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
							<v-col cols="12" v-show="alertForm">
								<v-alert v-model="alertForm" text type="error" dismissible>
									El título del contenido se repite.
								</v-alert>
							</v-col>
							<v-col cols="12" class="pb-0">
								<DefaultInput 
									dense
									required
									clearable
									label="Título de contenido" 
									:rules="rules.title"  
									:counter="70" maxlength="70" 
									v-model="frm.title" 
								/>
							</v-col>
							<v-col cols="12" class="pb-0">
								<v-textarea 
									dense
									outlined 
									clearable
									required 
									label="Descripción de contenido" 
									:rules="rules.description" 
									:counter="200" maxlength="200"
									class="custom-default-input" 
									rows="6" 
									v-model="frm.description" 
								/>
							</v-col>

							<v-col cols="12" class="py-0">
								<v-row>
									<v-col cols="6" class="d-flex justify-content-center">
										<v-card class="w-100" 
												:class="{'border-solid-primary': showLinkVideo }"
												@click="showMultimedia = false, showLinkVideo = true">
											<v-card-text 
												class="text-center d-flex flex-column"
												:class="{'text-primary-sub': showLinkVideo }"
												>
												<span class="mdi mdi-youtube fa-2x mb-2"></span>
												<span>Link/Código de video</span>
											</v-card-text>
										</v-card>
									</v-col>
									<v-col cols="6" class="d-flex justify-content-center">
										<v-card class="w-100" 
												:class="{'border-solid-primary': showMultimedia }"
												@click="showLinkVideo = false, showMultimedia = true">
											<v-card-text 
												class="text-center d-flex flex-column"
												:class="{'text-primary-sub': showMultimedia }"
												>
												<span class="mdi mdi-folder-multiple-image fa-2x mb-2"></span>
												<span>Multimedia</span>
											</v-card-text>
										</v-card>
									</v-col>
								</v-row>
							</v-col>

							<v-col cols="12" v-show="(showMultimedia || showLinkVideo)">
								<!-- <div v-show="!frm.linked" class="mb-4"> -->
								<div v-show="showMultimedia">
									<p>Selecciona un archivo desde multimedia.</p>
	
									<DefaultSelectOrUploadMultimediaDimension
										ref="inputContenidoCampaign"
										v-model="frm.file_media"
										label="Selecciona contenido"
										label-button="Seleccionar multimedia"
										:file-types="['image', 'video', 'audio', 'pdf', 'scorm', 'office']" 
										@onSelect="setFileOnly($event, frm, 'media')"
									/>

								</div>
								<div v-show="showLinkVideo">
									<DefaultInput 
										clearable
										dense 
										v-model="frm.linked" 
										label="Link/Código (Youtube o Vimeo)" 
										:rules="rules.linked"
									/>
								</div>
							</v-col>				 		

						</v-row>
					 </v-form>
			</v-card-text>
			<v-card-actions class="d-flex justify-content-center pb-4">
			  <v-btn text color="primary" class="mr-2" @click="clearCurrentData"> Cancelar </v-btn>
			  <v-btn color="primary" @click="saveCurrentData" v-text="dialogState.btn"></v-btn> <!-- Boton dianmico dinamico -->
			</v-card-actions>
		</v-card>
		</v-dialog>

	</div>

</template>

<script>
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
		data() {
			return {
				valid: false,
				alertForm: false,
				showQuestion: false,

				dialogCard: false,
				dialogMode: false,
				dialogIndex: null, // se requerira con dialogCard = true
				
				clearableMultimedia: true,

				showMultimedia: false,
				showLinkVideo: false,

				// content fprm
				contents: [],
				question: null,
				frm: {
					title: null,
					description: null,
					file_media: null,
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

			setCurrentIcon( file, linked ) {
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

				vm.removeFileFromDropzone(vm.frm.file_media, 'inputContenidoCampaign');
				vm.$refs.inputContenidoCampaign.removeAllFilesFromDropzone();
				vm.frm.file_media = null;

				vm.frm.linked = null;

				vm.frm.state = false;
				setTimeout(() => vm.frm.state = true, 100);

				vm.dialogIndex = null;
				vm.alertForm = false;
				vm.clearableMultimedia = !vm.clearableMultimedia;

				vm.showMultimedia =  false;
				vm.showLinkVideo = false;

				vm.openDialogCard(false); //devuelve el estado original de dialogMode y dialogCard
			},
			editCurrentData(index) {
				const vm = this;
				const data = vm.contents[index];

				for(const prop in vm.frm) {
					vm.frm[prop] = data[prop];
				}
				// link video
				if(vm.frm.linked) {
					vm.showMultimedia =  false;
					vm.showLinkVideo = true;
				}

				// multimedia 
				if(vm.frm.file_media) {
					vm.showMultimedia =  true;
					vm.showLinkVideo = false;
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

				const { title, description, file_media, linked } = vm.frm;

				if(file_media && vm.showMultimedia) vm.frm.linked = null;
				else vm.frm.file_media = null;

				if(!file_media && !linked) return; //console.log('ambos nulos')
				if(!valObjNull(vm.frm, ['file_media', 'linked'])) return; //console.log('jumped valObjNull')
				if(!valString(title,[5, 70])) return;
				// if(linked && !valDomains(linked, ['youtube','youtu','vimeo'])) return;
				if(!valString(description,[5, 200])) return;

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
					if(key === 'question' && value) vm.showQuestion = true;
				}
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

