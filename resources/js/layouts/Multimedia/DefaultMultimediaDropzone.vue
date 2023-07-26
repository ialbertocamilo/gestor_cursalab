<template>
    <div>
        <v-alert
            border="left"
            dense
            outlined
            class="m-1"
            type="warning"
            v-if="errorFileType"
            transition="scale-transition"
        >
            Tipo de contenido no permitido
        </v-alert>
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
            <div class="dropzone-custom-content">
                <v-icon>mdi-upload</v-icon>
                <div class="subtitle">{{ hint }}</div>
                <br>
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

import DefaultStorageAlertModal from '../Default/DefaultStorageAlertModal.vue';
import GeneralStorageModal from '../General/GeneralStorageModal.vue';
import GeneralStorageEmailSendModal from '../General/GeneralStorageEmailSendModal.vue';

import vue2Dropzone from 'vue2-dropzone';
import 'vue2-dropzone/dist/vue2Dropzone.min.css';

let timeout;

export default {
    components: {
        vueDropzone: vue2Dropzone, 
        DefaultStorageAlertModal, GeneralStorageModal, GeneralStorageEmailSendModal
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
            errorFileType: false,
            types: this.typesAllowed,
            archivo: null,
            dropzoneOptions: {
                maxFilesize: 0,

                autoProcessQueue: false,
                // autoQueue: false,

                autoDiscover: false,
                // url: 'https://httpbin.org/post',
                url: (files) => {
                    console.log(files)
                    return files
                },
                thumbnailWidth: 250,
                addRemoveLinks: true,
                dictRemoveFile: 'Remover archivo',
                dictCancelUpload: 'Cancelar',
                maxFiles: null,
                timeout: {
                    default: 0
                }
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
            let validExt = this.validatedFileExtension(file, this.typesAllowed)

            if (!validExt) {
                this.$refs.myVueDropzone.removeAllFiles()
                this.errorFileType = true
                setTimeout(() => this.errorFileType = false, 10000)
            } else {
                this.errorFileType = false
                this.checkFileSizeStorageLimit(file);
                // this.$refs.myVueDropzone.manuallyAddFile(file)
            }
        },
        limpiarArchivo() {
            this.$refs.myVueDropzone.removeAllFiles()
            this.archivo = null;
        },
        uploadSuccess(file, response) {
            this.$emit("onUpload", file);
        },
        uploadError(file, message) {
            // console.log(file, message)
        },
        fileRemoved() {
            this.$emit("onUpload", null);
            this.$emit("emitir-alerta", 'Archivo removido');
        },
        removeAll() {
            this.$refs.myVueDropzone.removeAllFiles()
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
                },(err) => {
                    vue.hideLoader();
                    // console.log(err);
                });

            }, 100);
            // == La funcion se recallea ==
        }
    }
}
</script>

<style lang="scss">
#dropzone {
    //display: table-row;
    //justify-content: center;
}
.dz-progress{
    display: none !important;
}
</style>
