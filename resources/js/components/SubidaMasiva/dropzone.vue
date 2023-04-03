<template>
    <div>
        <span class="mb-4">Subida de archivos de base de datos.</span>
        <vue-dropzone ref="myVueDropzone" id="dropzone"
                    :options="dropzoneOptions"
                    :useCustomSlot="true"
                    v-on:vdropzone-success="uploadSuccess"
                    v-on:vdropzone-error="uploadError"
                    v-on:vdropzone-removed-file="fileRemoved"
        >
            <div class="dropzone-custom-content" >
                <div class="icon_upload">
                    <img class="img_init" src="/img/upload.png">
                    <img class="img_load" style="display:none;" src="/img/upload_load.png">
                    <img class="img_hover" style="display:none;" src="/img/upload_load_hover.png">
                </div>
                <div class="subtitle">Sube o arrastra el archivo<br><b>excel</b> con los datos</div><br>
            </div>
        </vue-dropzone>
    </div>
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
                    dictRemoveFile: 'Reemplazar archivo',
                    maxFiles:1,
                    previewTemplate: this.template(),
                    accept: function(file, done) {
                        console.log(file);
                        if (file.type != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
                            done("El archivo no es del formato permitido");
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
                // this.$emit("emitir-alerta", 'Ocurrió un error');
            },
            fileRemoved() {
                this.$emit("emitir-archivo", null);
                // this.$emit("emitir-alerta", 'Archivo removido');
            },
            template: function () {
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
    .dropzone .dz-preview.dz-error .dz-image {
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
    }
    .dropzone .dz-preview.dz-error:hover .dz-error-message {
        display: none !important;
    }
</style>
