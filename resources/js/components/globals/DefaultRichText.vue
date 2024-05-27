<template>
    <div class="w-100">
        <div class="d-flex justify-content-center mb-2" v-if="title">
            <label class="default-rich-text-title">{{ title }}</label>
        </div>
        <fieldset class="editor">
            <legend v-if="label">{{ label }}
                <RequiredFieldSymbol v-if="showRequired"/>
            </legend>
            <!-- api key test: nsw7a23axxk8mjk3ibgzh0z6h2ef5d7xcuckp0cjdugrywug-->
            
            <!-- api key prod:  6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85-->
            <editor
                api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                v-model="localText"
                :init="{
                    content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                    height: height,
                    menubar: false,
                    language: 'es',
                    force_br_newlines: true,
                    force_p_newlines: false,
                    forced_root_block: '',
                    plugins: ['lists anchor', 'code', 'paste','link','image','preview','emoticons'],
                    toolbar:
                        ` undo redo | styleselect | ${showGenerateIaDescription ? ' customButton | ' : ''} emoticons |bold italic underline | alignleft aligncenter alignright alignjustify |bullist numlist | code | link ${showIconAddImage ? `| ${customSelectorImage ? 'selectorImage | uploadImage' : 'image'}  | preview` : ''}`,
                    images_file_types: 'jpg,svg,webp,gif',
                    images_upload_handler: images_upload_handler,
                    setup: function (editor) {
                        // if(showGenerateIaDescription){
                        editor.ui.registry.addButton('customButton', {
                            text: getIconText('jarvis'), // Ruta de la imagen para el botón personalizado
                            tooltip: 'Generar descripción con IA', // Texto que se muestra cuando se pasa el ratón sobre la imagen
                            onAction: function (_) {
                                generateIaDescription();
                            },
                        });
                        editor.ui.registry.addButton('selectorImage', {
                            text: getIconText('multimedia'), // Ruta de la imagen para el botón personalizado
                            tooltip: 'Seleccionar imagen', // Texto que se muestra cuando se pasa el ratón sobre la imagen
                            onAction: function (_) {
                                openModalMedia();
                            },
                        });
                        editor.ui.registry.addButton('uploadImage', {
                            text: getIconText('upload-image'), // Ruta de la imagen para el botón personalizado
                            tooltip: 'Subir imagen', // Texto que se muestra cuando se pasa el ratón sobre la imagen
                            onAction: function (_) {
                                openUploadlMedia();
                            },
                        });
                    }
                }"
                @input="updateValue"
            />
            <v-progress-linear
                indeterminate
                color="primary"
                v-if="loading"
            ></v-progress-linear>
        </fieldset>
        <div v-if="showValidateRequired"
             class="v-messages__message mt-2"
             v-text="'Campo requerido'"
             style="color: #FF5252; font-size: 12px"
        />
        <div v-if="showAlertLength"
             class="v-messages__message mt-2"
             v-text="`El campo debe tener menos de ${maxLength} caracteres`"
             style="color: #FF5252; font-size: 12px"
        />
        <SelectMultimedia
            :ref="modalPreviewMultimedia.ref"
            :options="modalPreviewMultimedia"
            width="85vw"
            @onClose="closeSimpleModal(modalPreviewMultimedia)"
            @onConfirm="loadImage"
            :custom-filter="fileTypes"
        />
        <DefaultDialog
            :options="modalOptions"
            width="500px"
            @onCancel="closeSimpleModal(modalOptions)"
            @onConfirm="uploadImage()"
        >
            <template v-slot:content>
                <v-form ref="TemaMultimediaTextForm" @submit.prevent="null">
                    <v-row>
                        <v-col cols="12">
                            <DefaultInput
                                label="Título"
                                placeholder="Ingresar título"
                                v-model="resource.title"
                                :rules="rules.titulo"
                                dense
                            />
                        </v-col>
                        <v-col cols="12">
                            <DefaultSelectOrUploadMultimedia
                                ref="inputLogo"
                                v-model="resource.multimedia"
                                label="Sube una imagen"
                                :file-types="['image']"
                                @onSelect="setMultimedia"
                                select-width="55vw"
                                select-height="55vh"
                                :showButton="false"
                            />
                        </v-col>
                    </v-row>
                </v-form>
            </template>
        </DefaultDialog>
        
    </div>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";
import SelectMultimedia from '../forms/SelectMultimedia';
export default {
    components: {Editor,SelectMultimedia},
    props: {
        value: {
            required: true
        },
        placeholder: {
            type: String
        },
        label: {
            type: String
        },
        title: {
            type: String
        },
        height: {
            type: Number,
            default: 170
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        showValidateRequired: {
            type: Boolean,
            default: false,
        },
        maxLength: {
            type: Number,
            default: 200
        },
        ignoreHTMLinLengthCalculation: {
            type: Boolean,
            default: false
        },
        showGenerateIaDescription:{
            type:Boolean,
            default:false
        },
        showIconAddImage:{
            type:Boolean,
            default:true
        },
        loading:{
            type:Boolean,
            default:false
        },
        customSelectorImage:{
            type:Boolean,
            default:false
        },
    },
    data() {
        return {
            localText: '',
            showAlertLength: false,
            modalPreviewMultimedia: {
                ref: 'modalSelectPreviewMultimedia',
                open: false,
                title: 'Buscar multimedia',
                confirmLabel: 'Seleccionar',
                cancelLabel: 'Cerrar'
            },
            fileTypes: ['image'],
            modalOptions: {
                ref: 'mediaFormModal',
                open: false,
                base_endpoint: '/media',
                resource: 'Media',
                confirmLabel: 'Guardar',
                showCloseIcon: true,
            },
            resource:{
                title:'',
                multimedia:null
            },
            rules: {
                titulo: this.getRules(['required']),
            }
        }
    },
    created() {
        if (this.value) {
            this.localText = this.value // set initial value
        }

        document.addEventListener('focusin', (e) => {
          if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
            e.stopImmediatePropagation();
            console.log('entra');
          }
        });
    },
    watch: {
        value(val) {
            // console.log("cambio desde el parent :: ", val)
            if (!val)
                this.$emit('setVaidateRequired', true)

            this.localText = val // watch change from parent component
        }

    },
    methods: {
        emitLengthState(st) {
            let vue = this

            vue.showAlertLength = st;
            vue.$emit('stateLength', st);
        },
        async images_upload_handler(blobInfo, success, failure) {
            let formdata = new FormData();
            formdata.append("file", blobInfo.blob(), blobInfo.filename());
            formdata.append("model_id", null);

            await axios
                .post("/media/media/fileupload", formdata)
                .then(({data}) => {
                    success(data.data.media.location);
                })
                .catch((err) => {
                    console.log(err)
                    failure("upload failed!");
                });
        },
        updateValue(value) {
            let vue = this

            if (value !== ""){
                vue.$emit('setVaidateRequired', false)
            }

            // check length
            if (value) {

                const str = vue.ignoreHTMLinLengthCalculation
                    ? vue.removeHTML(value)
                    : value;

                if(str.length > vue.maxLength) {
                    vue.emitLengthState(true)
                } else {
                    vue.emitLengthState(false)
                    vue.$emit('input', value || null)
                }
            } else {
                vue.emitLengthState(false);
                vue.$emit('input', value || null)
            }
        },
        removeHTML(str) {
            let tmp = document.createElement('div');
            tmp.innerHTML = str;
            return tmp.textContent || tmp.innerText || '';
        },
        onClear() {
            let vue = this
            vue.localText = ''
            vue.updateValue()
        },
        getIconText(icon){
            switch (icon) {
                case 'jarvis':
                return `
                    <div>
                        <image src="/img/ia_convert.svg" class="mt-2" style="width: 22px;cursor: pointer;"/ >
                        <span class="badge_custom"><span id="ia_descriptions_generated">0</span>/
                        <span id="limit_descriptions_jarvis">0</span></span>
                    </div>
                    `
                    break
                case 'multimedia':
                    return `<i class='mdi mdi-file-image'></i>`
                    break;
                case 'upload-image':
                    return `<i class='mdi mdi-file-upload'></i>`
                    break;
            }
           
        },
        // changeLimits(ia_descriptions_generated,limit_descriptions_jarvis){
        //     let html_ia_descriptions_generated = document.getElementById("ia_descriptions_generated");
        //     let html_limit_descriptions_jarvis = document.getElementById("limit_descriptions_jarvis");
        //     html_ia_descriptions_generated && (html_ia_descriptions_generated.textContent = ia_descriptions_generated);
        //     html_limit_descriptions_jarvis && (html_limit_descriptions_jarvis.textContent =  limit_descriptions_jarvis);
        // },
        generateIaDescription(){
            this.$emit('generateIaDescription')
        },
        openModalMedia(){
            let vue = this;
            vue.$refs[vue.modalPreviewMultimedia.ref].getData()
            vue.modalPreviewMultimedia.open= true;
        },
        loadImage(image){
            let vue = this;
            const text_value = this.localText+`<br><img src="${image.url}" style="height: 150px;width: auto;"  />`
            this.updateValue(text_value);
            vue.modalPreviewMultimedia.open= false;
        },
        openUploadlMedia(){
            let vue = this;
            vue.modalOptions.open = true;
        },
        setMultimedia(multimedia) {
            let vue = this
            vue.resource.multimedia = multimedia
        },
        uploadImage(){
            let vue = this;
            let formData = new FormData(); 
            if(!vue.resource.title || !vue.resource.multimedia){
                vue.showAlert('Es necesario añadir un título y una imagen.','warning');
                return;
            }
            vue.showLoader();
            formData.append(`file[]`, vue.resource.multimedia);
            formData.append(`title`, vue.resource.title);
            vue.$http.post('/multimedia/upload', formData)
                    .then(({data}) => {
                        const text_value = vue.localText+`<br><img src="${data.data.medias_saved[0].url}" style="height: 150px;width: auto;"  />`
                        vue.updateValue(text_value);
                    })
            vue.hideLoader();
            vue.closeSimpleModal(vue.modalOptions);
        }
    }
}
</script>
<style lang="scss" scoped>
.badge_custom{
    position: absolute !important;
    color: white !important;
    background: rgb(87, 191, 227) !important;
    padding: 5px !important;
    border-radius: 16px !important;
    margin-right: 8px !important;
    margin-left: 2px !important;
    font-size:9px !important;
}
</style>
