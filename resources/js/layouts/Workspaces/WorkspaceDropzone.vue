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
            errorFileType: false,
            types: this.typesAllowed,
            archivo: null,
            dropzoneOptions: {
                maxFilesize: 0,

                autoProcessQueue: false,
                // autoQueue: false,

                autoDiscover: false,
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
                this.$emit("onUpload", file);
                // this.$refs.myVueDropzone.manuallyAddFile(file)
            }
        },
        limpiarArchivo() {
            this.$refs.myVueDropzone.removeAllFiles()
            this.archivo = null;
        },
        uploadSuccess(file, response) {
            console.log(file)
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
