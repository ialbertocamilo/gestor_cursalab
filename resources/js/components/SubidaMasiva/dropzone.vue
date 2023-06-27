<template>
    <div>
        <span class="mb-4" v-if="title">{{ title }}</span>
        <vue-dropzone ref="myVueDropzone" id="dropzone"
                    :options="dropzoneOptions"
                    :useCustomSlot="true"
                    v-on:vdropzone-queue-complete="onQueueComplete"
                    v-on:vdropzone-upload-progress="viewProgress"
                    v-on:vdropzone-success="uploadSuccess"
                    v-on:vdropzone-complete="uploadComplete"
                    v-on:vdropzone-error="uploadError"
                    v-on:vdropzone-removed-file="fileRemoved"
        >
            <div class="dropzone-custom-content" >
                <div class="error_upload" v-if="error_file">
                    <div class="icon_upload_error">
                        <img src="/img/upload_error.png">
                    </div>
                    <div class="text_error_upload">El archivo no se ha podido cargar correctamente.</div>
                    <span class="label_error_upload">{{error_text}}</span>
                </div>
                <div class="success_upload" v-else-if="success_file">
                    <div class="icon_upload_success">
                        <img src="/img/upload_success.png">
                    </div>
                    <div class="text_success_upload">El archivo se ha cargado correctamente.</div>
                    <span class="label_success_upload" v-if="success_text" v-html="success_text"></span>
                </div>
                <div class="init_upload" v-else>
                    <div class="icon_upload">
                        <img class="img_init" src="/img/upload.png">
                        <img class="img_load" style="display:none;" src="/img/upload_load.png">
                        <img class="img_hover" style="display:none;" src="/img/upload_load_hover.png">
                    </div>
                    <div class="subtitle" v-if="subtitle" v-html="subtitle"></div><br>
                </div>
                <div v-if="hasObservation" class="mx-8 mt-4">
                    <div class="text-subtitle-2" style="color:red;">Sin embargo el archivo tuvo observaciones que no se pudieron cargar.</div><br>
                    <div class="mt-4 text-subtitle-2">
                        Descargar <span style="color:red;font-weight: bolder; cursor: pointer;" @click="downloadObservationsFile()">el archivo</span> con observaciones.
                    </div>
                </div>
                <br>
            </div>
        </vue-dropzone>
    </div>
</template>
<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'
    export default {
        components:{ vueDropzone: vue2Dropzone},
        props: {
            hasObservation:{
                type: Boolean,
                default:false
            },
            error_file: {
                type: Boolean,
                default: false
            },
            error_text: {
                type: String,
                default: ''
            },
            success_file: {
                type: Boolean,
                default: false
            },
            success_text: {
                type: String,
                default: ''
            },
            title:{
                type:String,
                default: 'Subida de archivos de base de datos.'
            },
            subtitle:{
                type:String,
                default: 'Sube o arrastra el archivo'
            }
        },
        data () {
            return {
                archivo:null,
                dropzoneOptions: {
                    url: (files, test) => {
                        return files[0]
                    },
                    autoProcessQueue: false,
                    autoDiscover: false,

                    height: 400,
                    thumbnailWidth: 400,
                    headers: { "My-Awesome-Header": "header value" },
                    addRemoveLinks: true,
                    dictRemoveFile: 'Reemplazar archivo',
                    maxFiles:1,
                    previewTemplate: this.template(),
                    timeout: {
                        default: 0
                    },
                    accept: this.validateFile,
                    init: function() {
                        this.on("maxfilesexceeded", function(file) {
                                this.removeAllFiles();
                                this.addFile(file);
                        });
                    }
                },
            }
        },
        watch: {
            error_file: function(newVal, oldVal) {
                this.$refs.myVueDropzone.removeAllFiles()
                this.archivo = null;
            },
            success_file: function(newVal, oldVal) {
                this.$refs.myVueDropzone.removeAllFiles()
                this.archivo = null;
            },
            hasObservation: function(newVal, oldVal) {
                if(this.hasObservation)
                    this.$refs.myVueDropzone.disable()
                else
                    this.$refs.myVueDropzone.enable()
                this.archivo = null;
            }
        },
        methods: {
            viewProgress(file, progress, bytesSent) {
            // console.log(file, progress, bytesSent)
            },
            onComplete(response) {
                // console.log(response)
            },
            onQueueComplete() {
                // console.log('queue complete')
                // this.$emit("onUpload", file);
            },
            limpiarArchivo() {
                this.$refs.myVueDropzone.removeAllFiles()
                this.archivo = null;
            },
            validateFile(file,done){
                console.log('accept',file);
                if (file.type != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                    done("El archivo no es del formato permitido");
                }
                else {
                    this.$emit("emitir-archivo", file);
                    done();
                }
            },
            uploadSuccess(file, response) {
                this.$emit("emitir-archivo", file);
            },
            uploadComplete(file, response) {
                this.$emit("emitir-archivo-completo", file);
            },
            uploadError(file, message) {
                // this.$emit("emitir-alerta", 'Ocurrió un error');
            },
            fileRemoved() {
                this.$emit("emitir-archivo", null);
                // this.$emit("emitir-alerta", 'Archivo removido');
            },
            downloadObservationsFile(){
                this.$emit("emitir-download-file", null);
            },
            template() {
                return `<div class="dz-preview dz-file-preview">
                        <div class="dz-image">
                            <div data-dz-thumbnail-bg></div>
                            <div class="icon_upload">
                                <img class="img_init" src="/img/upload.png">
                                <img class="img_load" style="display:none;" src="/img/upload_load.png">
                                <img class="img_hover" style="display:none;" src="/img/upload_load_hover.png">
                            </div>
                        </div>
                        <div class="dz-details">
                            <div class="dz-filename"><span data-dz-name></span></div>
                            <div class="dz-size"><span data-dz-size></span></div>
                            <div class="dz-inf"><span>Archivo listo para ser cargado, haz clic en confirmar</span></div>
                        </div>
                        <div class="dz-progress"><span class="dz-upload" data-dz-uploadprogress></span></div>
                        <div class="dz-error-message">
                            <div class="icon_upload_error">
                                <img src="/img/upload_error.png">
                            </div>
                            <div class="text_error_upload">El archivo no se ha podido cargar correctamente.</div>
                            <span class="label_error_upload" data-dz-errormessage></span>
                        </div>
                        <div class="dz-success-mark"><i class="fa fa-check"></i></div>
                        <div class="dz-error-mark"><i class="fa fa-close"></i></div>
                    </div>
                `;
            }
        }
    }
</script>
<style lang="scss">
    .vue-dropzone{
        border: 1px solid #2A3649 !important;
        border-radius: 5px;
        width: 360px;
        max-width: 360px;
        padding: 0;
    }
    .vue-dropzone:hover {
        border: 1px dashed #5458EA !important;
        background: none !important;
    }
    .icon_upload {
        margin-bottom: 30px;
    }
    .vue-dropzone:hover .icon_upload  .img_init{
        display: none;
    }
    .vue-dropzone:hover .icon_upload  .img_hover{
        display: initial  !important;
    }
    .vue-dropzone:hover .dz-preview.dz-file-preview .icon_upload  .img_hover,
    .vue-dropzone:hover .dz-preview.dz-image-preview .icon_upload  .img_hover {
        display: none !important;
    }
    .icon_upload img {
        max-width: 60px;
        height: auto;
    }
    .dropzone-custom-content .subtitle {
        font-family: "Nunito", sans-serif;
        font-size: 16px;
    }
    .vue-dropzone:hover .dropzone-custom-content .subtitle {
        color: #5458EA;
    }
    .dropzone .dz-preview {
        width: 100%;
        min-height: 240px;
        margin: 0;
        padding: 30px 20px;
    }
    .dropzone.dz-clickable.dz-started.dz-max-files-reached {
        padding: 0 !important;
    }
    .dropzone .dz-preview.dz-file-preview {
        .dz-progress {
            display: none !important;
        }
        .dz-details {
            background: none;
            text-align: center;
            color: #2A3649;
            font-family: "Nunito", sans-serif;
            position: relative;
            padding-top: 20px;
            padding-bottom: 0 !important;
            .dz-filename {
                font-size: 20px;
                font-weight: 700;
                margin-bottom: 10px;
            }
            .dz-size {
                font-size: 15px;
                font-weight: 400;
                strong {
                    font-weight: 400;
                }
            }
            .dz-inf {
                font-size: 16px;
                font-weight: 400;
            }
        }
        .dz-image {
            background: none;
            min-height: 40px;
            margin-top: 10px;
            .icon_upload {
                display: flex;
                justify-content: center;
                margin-bottom: 0;
                .img_init {
                    display: none !important;
                }
                .img_load {
                    display: initial !important;
                }
            }
        }
    }
    .vue-dropzone>.dz-preview .dz-remove {
        background: rgba(42, 54, 73, 0.91);
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
        padding-top: 150px;
        font-family: "Nunito", sans-serif;
        color: #fff;
        text-transform: inherit;
        font-size: 14px;
        text-decoration: none !important;
        outline: none;
        border: none;
        font-weight: 400;
    }
    .vue-dropzone>.dz-preview:hover .dz-remove:before {
        background-image: url('/img/upload_ree.png');
        content: '';
        width: 60px;
        height: 60px;
        position: absolute;
        top: 80px;
        background-repeat: no-repeat;
        background-size: contain;
        background-position: center;
        left: 50%;
        transform: translateX(-50%);
    }
    .dropzone .dz-preview:hover  {
        .dz-image, .dz-details{
            filter: blur(5px);
        }
    }
    .dz-success-mark, .dz-error-message {
        display: none !important;
    }
    .dropzone .dz-preview.dz-error .dz-image,
    .dropzone .dz-preview.dz-error .dz-details {
        display: none;
    }
    .dropzone .dz-preview.dz-error .dz-error-message {
        opacity: 1;
        background: none;
        color: red;
        text-align: center;
        height: 100%;
        top: 0;
        display: flex !important;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    .dropzone .dz-preview.dz-error .dz-error-message .icon_upload_error {
        margin-top: 25px;
    }
    .dropzone .dz-preview.dz-error .dz-error-message .text_error_upload {
        font-family: "Nunito", sans-serif;
        margin-top: 25px;
        display: flex;
        font-size: 16px;
        color: #FF4560;
        line-height: 20px;
    }
    .dropzone .dz-preview.dz-error .dz-error-message span.label_error_upload {
        font-family: "Nunito", sans-serif;
        margin-top: 6px;
        display: flex;
        font-size: 12px;
        color: #A9B2B9;
        line-height: 20px;
        font-weight: 400;
        min-height: 42px;
    }
    .dropzone .dz-preview.dz-error:hover .dz-error-message {
        display: none !important;
    }
    .dropzone .success_upload,
    .dropzone .error_upload {
        opacity: 1;
        background: none;
        color: red;
        text-align: center;
        height: 100%;
        top: 0;
        display: flex !important;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    .dropzone .success_upload .icon_upload_success,
    .dropzone .error_upload .icon_upload_error {
        margin-top: 25px;
    }
    .dropzone .success_upload .text_success_upload,
    .dropzone .error_upload .text_error_upload {
        font-family: "Nunito", sans-serif;
        margin-top: 25px;
        display: flex;
        font-size: 16px;
        color: #FF4560;
        line-height: 20px;
        max-width: 80%;
    }
    .dropzone .success_upload,
    .dropzone .success_upload .text_success_upload{
        color: #4CAF50;
    }
    .dropzone .success_upload span.label_success_upload,
    .dropzone .error_upload span.label_error_upload {
        font-family: "Nunito", sans-serif;
        margin-top: 6px;
        display: flex;
        font-size: 12px;
        color: #A9B2B9;
        line-height: 20px;
        font-weight: 400;
        min-height: 42px;
        max-width: 80%;
    }
    .dropzone .success_upload span.label_success_upload ul {
        text-align: left;
    }
    .init_upload {
        margin-top: 35px;
    }
</style>
