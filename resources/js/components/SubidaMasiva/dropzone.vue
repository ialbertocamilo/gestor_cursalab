<template>
    <vue-dropzone ref="myVueDropzone" id="dropzone"
                  :options="dropzoneOptions"
                  :useCustomSlot="true"
                  v-on:vdropzone-success="uploadSuccess"
                  v-on:vdropzone-error="uploadError"
                  v-on:vdropzone-removed-file="fileRemoved"
    >
        <div class="dropzone-custom-content" >
            <v-icon>mdi-upload</v-icon><h3 class="dropzone-custom-title">Subida masiva</h3>
            <div class="subtitle">Suba o arrastre el archivo</div><br>
        </div>
    </vue-dropzone>
</template>
<script>
    import vue2Dropzone from 'vue2-dropzone'
    import 'vue2-dropzone/dist/vue2Dropzone.min.css'
    export default {
        components:{ vueDropzone: vue2Dropzone},
        data () {
            return {
                archivo:null,
                dropzoneOptions: {
                    url: 'https://httpbin.org/post',
                    height: 400,
                    thumbnailWidth: 400,
                    headers: { "My-Awesome-Header": "header value" },
                    addRemoveLinks: true,
                    dictRemoveFile: 'Quitar archivo',
                    maxFiles:1,
                    accept: function(file, done) {
                        console.log(file);
                        if (file.type != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                            done("Error! Files of this type are not accepted");
                        }
                        else { done(); }
                    },
                    init: function() {
                        this.on("maxfilesexceeded", function(file) {
                                this.removeAllFiles();
                                this.addFile(file);
                        });
                    }
                },
            }
        },
        methods: {
            limpiarArchivo() {
                this.$refs.myVueDropzone.removeAllFiles()
                this.archivo = null;
            },
            uploadSuccess(file, response) {
                console.log();
                console.log(file.response);
                this.$emit("emitir-archivo", file);
            },
            uploadError(file, message) {
                this.$emit("emitir-alerta", 'Ocurrió un error');
            },
            fileRemoved() {
                this.$emit("emitir-archivo", null);
                this.$emit("emitir-alerta", 'Archivo removido');
            }
        }
    }
</script>
<style>
    .vue-dropzone{
        border:2px solid #e5e5e5 !important;
    }
</style>
