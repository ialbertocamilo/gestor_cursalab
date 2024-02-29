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
            v-on:vdropzone-thumbnail="thumbnail"
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

        <!-- MODAL ALMACENAMIENTO -->
        <GeneralStorageModal
            :ref="modalGeneralStorageOptions.ref"
            :options="modalGeneralStorageOptions"
            width="45vw"
            @onCancel="closeFormModal(modalGeneralStorageOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageOptions),
                        openFormModal(modalGeneralStorageEmailSendOptions, null, 'status', 'Solicitud enviada')"
        />
        <!-- MODAL ALMACENAMIENTO -->

        <!-- MODAL EMAIL ENVIADO -->
        <GeneralStorageEmailSendModal
            :ref="modalGeneralStorageEmailSendOptions.ref"
            :options="modalGeneralStorageEmailSendOptions"
            width="35vw"
            @onCancel="closeFormModal(modalGeneralStorageEmailSendOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageEmailSendOptions)"
        />
        <!-- MODAL EMAIL ENVIADO -->

        <!-- === MODAL ALERT STORAGE === -->
        <DefaultStorageAlertModal
            :ref="modalAlertStorageOptions.ref"
            :options="modalAlertStorageOptions"
            width="25vw"
            @onCancel="closeFormModal(modalAlertStorageOptions), removeAll()"
            @onConfirm="openFormModal(modalGeneralStorageOptions, null, 'status', 'Aumentar mi plan'),
                        closeFormModal(modalAlertStorageOptions),
                        removeAll()"
        />
        <!-- === MODAL ALERT STORAGE === -->

    </div>
</template>
<script>

import DefaultStorageAlertModal from '../../layouts/Default/DefaultStorageAlertModal.vue';
import GeneralStorageModal from '../../layouts/General/GeneralStorageModal.vue';
import GeneralStorageEmailSendModal from '../../layouts/General/GeneralStorageEmailSendModal.vue';

import vue2Dropzone from 'vue2-dropzone'
import 'vue2-dropzone/dist/vue2Dropzone.min.css'

let timeout;

export default {
    components: {
        vueDropzone: vue2Dropzone,
        DefaultStorageAlertModal, GeneralStorageModal, GeneralStorageEmailSendModal,

    },
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
            image_cropped: '',
            modalAlertStorageOptions: {
                ref: 'AlertStorageModal',
                open: false,
                showCloseIcon: true,
                base_endpoint: '/general',
                confirmLabel:'Solicitar',
                persistent: true,
            },
            modalGeneralStorageOptions: {
                ref: 'GeneralStorageModal',
                open: false,
                showCloseIcon: true,
                base_endpoint: '/general',
                confirmLabel:'Enviar',
                persistent: true
            },
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel:'Entendido',
                persistent: false
            },
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
                    console.log(files)
                    console.log(test)
                    return files[0]
                },
                thumbnailWidth: 400,
                thumbnailMethod: "contain",
                addRemoveLinks: true,
                dictRemoveFile: 'Reemplazar archivo',
                dictCancelUpload: 'Cancelar',
                maxFiles: 1,
                previewTemplate: this.template(),
                timeout: {
                    default: 0
                },
                uploadMultiple: false,
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

            // console.log(file);
            this.$emit("onUpload", file);
            // if(file.size > 0 && (file.size/1024/1024) > 302) {
            //     vue.error_upload = true;
            //     vue.error_size = true;
            //     this.$refs.myVueDropzone.removeAllFiles()
            // }
            // else {
            //     if (!validExt) {
            //         vue.error_upload = true;
            //         this.$refs.myVueDropzone.removeAllFiles()
            //     } else {
            //         vue.error_upload = false;
            //         vue.error_size = false;
            //         // this.$emit("onUpload", file);
            //         vue.checkFileSizeStorageLimit(file);
            //         console.log('logger check');
            //         // this.$refs.myVueDropzone.manuallyAddFile(file)
            //     }
            // }

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
            console.log('logger check');
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
        addManually(file, url = '') {
            const files = this.$refs.myVueDropzone.dropzone.files;

            if(files.length === 0) {
                this.$refs.myVueDropzone.manuallyAddFile(file, url);
            }
        },
        template() {
            return `<div class="dz-preview dz-file-preview">
                        <div class="dz-image">
                            <div data-dz-thumbnail-bg></div>
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
        },
        checkFileSizeStorageLimit(file) {
            let vue = this;

            vue.showLoader();

            const dropzoneDom = vue.$refs.myVueDropzone.$refs.dropzoneElement;
            const dropFiles = dropzoneDom.dropzone.files;

            // == La funcion se recallea ==
            if(timeout) clearTimeout(timeout);

            timeout = setTimeout(() => {
                const currentSizes = dropFiles.reduce((acc, {size}) => acc + size, 0);

                vue.$http.put('/general/workspaces-storage', { size: currentSizes })
                .then((res) => {
                    const data = res.data.data;

                    if(data.file_storage_check) {
                        vue.openFormModal(vue.modalAlertStorageOptions, null, null, 'Alerta de almacenamiento');
                    }else {
                        vue.hideLoader();
                        vue.$emit("onUpload", file);
                    }

                    // console.log(res);
                },(err) => {
                    vue.hideLoader();
                    // console.log(err);
                });

            }, 100);
            // == La funcion se recallea ==
        },
        thumbnail: function(file, dataUrl) {
            let vue = this;
            var j, len, ref, thumbnailElement;
            if (file.previewElement) {
                file.previewElement.classList.remove("dz-file-preview");
                ref = file.previewElement.querySelectorAll("[data-dz-thumbnail-bg]");
                for (j = 0, len = ref.length; j < len; j++) {
                    thumbnailElement = ref[j];
                    thumbnailElement.alt = file.name;
                    thumbnailElement.style.backgroundImage = 'url("' + dataUrl + '")';
                }
                vue.$emit("onPreview", dataUrl);
                return setTimeout(((function(_this) {
                    return function() {
                        return file.previewElement.classList.add("dz-image-preview");
                    };
                })(this)), 1);
            }
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
    .modal_edit_process {
        .dropzone .dz-preview {
            min-height: 140px;
            padding: 0 !important;
        }
        .dropzone .dz-preview.dz-image-preview .dz-image {
            height: auto !important;
            overflow: initial !important;
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            margin: 0;
            div {
                background-size: contain;
                background-repeat: no-repeat;
                position: absolute;
                width: 100%;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-position: center;
            }
        }
        .vue-dropzone > .dz-preview .dz-remove {
            padding-top: 90px;
        }
        .vue-dropzone > .dz-preview:hover .dz-remove:before {
            width: 50px;
            height: 50px;
            top: 34px;
        }
        .dropzone .dz-preview:hover .dz-image,
        .dropzone .dz-preview:hover .dz-details {
            transform: scale(.8);
        }
    }
</style>
