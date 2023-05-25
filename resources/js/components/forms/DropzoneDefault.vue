<template>
    <div>
        <vue-dropzone
            ref="myVueDropzone" id="dropzone"
            :options="dropzoneOptions"
            :useCustomSlot="true"
            v-on:vdropzone-file-added="addedFile"
            v-on:vdropzone-queue-complete="onQueueComplete"
            v-on:vdropzone-upload-progress="viewProgress"
            v-on:vdropzone-success="uploadSuccess"
            v-on:vdropzone-complete="onComplete"
            v-on:vdropzone-error="uploadError"
            v-on:vdropzone-removed-file="fileRemoved"
        >
            <div class="dropzone-custom-content" >
                <div class="error_upload" v-if="error_upload">
                    <div class="icon_upload_error">
                        <img src="/img/upload_error.png">
                    </div>
                    <div class="text_error_upload">El archivo no se ha podido cargar correctamente.</div>
                    <span class="label_error_upload" v-if="error_size">El archivo supera el peso maximo<br>permitido. (máximo 300 Mb)</span>
                    <span class="label_error_upload" v-else>El archivo no es del formato permitido</span>
                </div>
                <div class="init_upload" v-else>
                    <div class="icon_upload">
                        <img class="img_init" src="/img/upload.png">
                        <img class="img_load" style="display:none;" src="/img/upload_load.png">
                        <img class="img_hover" style="display:none;" src="/img/upload_load_hover.png">
                    </div>
                    <div class="subtitle">Sube o arrastra el archivo</div><br>
                </div>
            </div>
        </vue-dropzone>
    </div>
</template>
<script>
import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'

export default {
    components: {vueDropzone: vue2Dropzone},
    props: {
        hint: {
            type: String,
            default: 'Suba o arrastre el archivo'
        },
        typesAllowed: {
            type: Array,
            default() {
                return ['image']
            }
        }
    },
    data() {
        return {
            types: this.typesAllowed,
            archivo: null,
            error_size: false,
            error_upload: false,
            dropzoneOptions: {
                maxFilesize: 0,

                autoProcessQueue: false,
                // autoQueue: false,

                autoDiscover: false,
                // url: 'https://httpbin.org/post',
                url: (files, test) => {
                    console.log(test)
                    return files[0]
                },
                thumbnailWidth: 250,
                addRemoveLinks: true,
                dictRemoveFile: 'Reemplazar archivo',
                dictCancelUpload: 'Cancelar',
                maxFiles: 1,
                previewTemplate: this.template(),
                timeout: {
                    default: 0
                },
                uploadMultiple: false
                // accept: function (file, done) {
                //     // console.log(file);
                //     if (file.type != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                //         done("Error! Files of this type are not accepted");
                //     } else {
                //         // done();
                //     }
                // },
                // init: function () {
                //     this.on("maxfilesexceeded", function (file) {
                //         this.removeAllFiles();
                //         this.addFile(file);
                //     });
                //
                // }
            },
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
        addedFile(file) {

            let validExt = (this.typesAllowed[0] != '') ? this.validatedFileExtension(file, this.typesAllowed) : true;
            let vue = this;

            if(file.size > 0 && (file.size/1024/1024) > 302) {
                vue.error_upload = true;
                vue.error_size = true;
                this.$refs.myVueDropzone.removeAllFiles()
            }
            else {
                if (!validExt) {
                    vue.error_upload = true;
                    this.$refs.myVueDropzone.removeAllFiles()
                } else {
                    vue.error_upload = false;
                    vue.error_size = false;
                    this.$emit("onUpload", file);
                    // this.$refs.myVueDropzone.manuallyAddFile(file)
                }
            }

        },
        limpiarArchivo() {
            this.$refs.myVueDropzone.removeAllFiles()
            this.archivo = null;
        },
        uploadSuccess(file, response) {
            // console.log(response)
            // console.log('emit onUpload');
            // console.log(file.response);
            this.$emit("onUpload", file);
        },
        uploadError(file, message) {
            // console.log(file, message)
            this.limpiarArchivo()
        },
        fileRemoved() {
            this.$emit("onUpload", null);
        },
        removeAll() {
            this.$refs.myVueDropzone.removeAllFiles()
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
<!--
<style lang="scss">
#dropzone {
    display: flex;
    justify-content: center;
}
.dz-progress{
    display: none !important;
}
</style> -->
<style lang="scss">
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
    .dropzone .dz-preview.dz-file-preview,
    .dropzone .dz-preview.dz-image-preview {
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
            opacity: 1;
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
    .dropzone .error_upload .icon_upload_error {
        margin-top: 25px;
    }
    .dropzone .error_upload .text_error_upload {
        font-family: "Nunito", sans-serif;
        margin-top: 25px;
        display: flex;
        font-size: 16px;
        color: #FF4560;
        line-height: 20px;
    }
    .dropzone .error_upload span.label_error_upload {
        font-family: "Nunito", sans-serif;
        margin-top: 6px;
        display: flex;
        font-size: 12px;
        color: #A9B2B9;
        line-height: 20px;
        font-weight: 400;
        min-height: 42px;
    }
</style>
