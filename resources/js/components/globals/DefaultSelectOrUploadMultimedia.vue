<template>
    <div>
        <fieldset class="editor">
            <legend>{{ label }}</legend>

            <v-card elevation="0" class="mx-4 mtb-10" v-if="description">
                <small class="text_desc" v-html="description"/>
            </v-card>

            <!-- AAAA {{ fileSelected }}  -->

            <!-- <v-card elevation="0" class="my-1" v-if="TypeOf(fileSelected) === 'object' || fileSelected === null"> -->
            <v-card elevation="0" class="my-1"
                    v-if="(TypeOf(fileSelected) === 'object' && !fileSelected.ext ) || fileSelected === null">
                <v-card-text class="p-0" v-if="cropImage">
                    <div v-if="imageCropped">
                        <div class="preview_image_cropped" v-if="previewImageCropped">
                            <div class="bx_img">
                                <img :src="previewImageCropped">
                            </div>
                            <div class="text-center bx_replace_img">
                                <div @click="removeImage">
                                    <img src="/img/upload_ree.png">
                                    <span>Reemplazar imagen</span>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <cropper
                            ref="cropper"
                            class="box-cropper"
                            @change="change"
                            :stencil-size="sizeCropp"
                            :stencil-props="{
                                handlers: {},
                                movable: false,
                                resizable: false,
                            }"
                            image-restriction="stencil"
                            :src="imageCropped" />

                            <div class="text-center mt-1">
                                <v-btn
                                    class="mx-1"
                                    elevation="0"
                                    color="primary"
                                    @click="resizeImage"
                                    style="max-width: min-content"
                                >
                                Aplicar
                                </v-btn>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <DefaultDropzoneCrop
                            :ref="dropzoneDefault"
                            :types-allowed="fileTypes"
                            @onUpload="setMediaPreviewUpload"
                            @onPreview="setPreviewUpload"
                        />
                    </div>
                </v-card-text>
                <v-card-text v-else>
                    <DropzoneDefault
                        :ref="dropzoneDefault"
                        :types-allowed="fileTypes"
                        @onUpload="setMediaPreviewUpload"
                    />
                </v-card-text>
            </v-card>

            <v-card v-else elevation="0">
                <v-card-text class="d-flex justify-content-center align-items-center flex-column content_hover_file">
                    <!--     <span class="media-tag" @click="removeMediaPreview">
                            &nbsp;<v-icon small color="red" title="Quitar" v-text="'mdi-close-circle-outline'" style="font-size: 30px;"/>&nbsp;
                        </span>
                        <v-img v-if="TypeOf(fileSelected) === 'object'" contain width="276" max-height="200"
                                   :src="infoMedia(fileSelected).preview"
                        />
                        <v-img v-else contain width="276" height="200"
                               :src="bucketBaseUrl + '/' + fileSelected" -->
                    <v-img contain width="90%" height="226"
                           :src="getMediaPreview"
                           v-if="TypeOf(fileSelected) !== 'undefined'"
                    />
                    <div class="hover_upload" style="display: none;" @click="removeMediaPreview">
                        <div class="icon_upload">
                            <img class="img_hover" src="/img/upload_ree.png">
                        </div>
                        <span class="txt">Reemplazar archivo</span>
                    </div>
                </v-card-text>
            </v-card>

        </fieldset>

        <transition name="fade" v-if="showButton">
            <v-btn class="mt-1 border"  block elevation="0"
                   @click="openSelectPreviewMultimediaModal"
            >
                <v-icon class="mx-2" style="font-size: 0.95em;">fas fa-photo-video</v-icon>
                {{ labelButton }}
            </v-btn>
        </transition>

        <SelectMultimedia
            v-if="showButton"
            :ref="modalPreviewMultimedia.ref"
            :options="modalPreviewMultimedia"
            :custom-filter="fileTypes"
            :width="selectWidth"
            :height="selectHeight"
            @onClose="closeSelectPreviewMultimediaModal"
            @onConfirm="onSelectMediaPreview"
        />
    </div>

</template>

<script>
import DropzoneDefault from "../forms/DropzoneDefault";
import SelectMultimedia from "../forms/SelectMultimedia";
import { Cropper } from "vue-advanced-cropper";
import "vue-advanced-cropper/dist/style.css";

export default {
    components: {SelectMultimedia, DropzoneDefault, Cropper},
    props: {
        label: {
            type: String,
            required: true
        },
        labelButton:{
            type: String,
            required: false,
            default:'Seleccionar multimedia'
        },
        selectWidth:{
            type: String,
            required: false,
            default:'85vw'
        },
        selectHeight:{
            type: String,
            required: false,
            default:'70vh'
        },
        description: {
            type: String,
            required: false,
            default: ''
        },
        showButton: {
            type: Boolean,
            default: true
        },
        value: {
            required: true
        },
        fileTypes: {
            type: Object | Array,
            default: function () {
                return []
            }
        },
        cropImage: {
            type: Boolean,
            default: false
        },
        sizeCropp: {
            type: Object,
            default: function () {
                return {
                    width: 500,
                    height: 500
                }
            }
        },
    },
    data() {
        return {
            bucketBaseUrl: '',
            fileSelected: null,
            dropzoneDefault: 'dropzoneDefault',
            modalPreviewMultimedia: {
                ref: 'modalSelectPreviewMultimedia',
                open: false,
                title: 'Buscar multimedia',
                confirmLabel: 'Seleccionar',
                cancelLabel: 'Cerrar'
            },
            imageCropped: null,
            previewImageCropped: null,
        }
    },
    computed: {
        getMediaPreview() {
            let vue = this
            // let preview = this.bucketBaseUrl + '/' + "images/default-scorm-img_116_360.png"
            let preview = "/images/default-scorm-img_116_360.png"

            if (vue.TypeOf(this.fileSelected) === 'string') {
                const extension = this.fileSelected.split('.').at(-1).toLowerCase()

                if (vue.mixin_extensiones.image.includes(extension))
                    return this.bucketBaseUrl + '/' + vue.fileSelected
                else {
                    for (const mixinExtensionesKey in vue.mixin_extensiones) {
                        if (vue.mixin_extensiones[mixinExtensionesKey].includes(extension))
                            preview = vue.mixin_default_media_images[mixinExtensionesKey]
                    }
                }

            } else if (vue.TypeOf(this.fileSelected) === 'object' && this.fileSelected.ext) {
                const extension = this.fileSelected.ext

                if (vue.mixin_extensiones.image.includes(extension))
                    return this.bucketBaseUrl + '/' + vue.fileSelected.file
                else {
                    for (const mixinExtensionesKey in vue.mixin_extensiones) {
                        if (vue.mixin_extensiones[mixinExtensionesKey].includes(extension))
                            preview = vue.mixin_default_media_images[mixinExtensionesKey]
                    }
                }
            }

            return vue.fileSelected
                ? preview
                : null
        }
    },
    created() {
        if (this.path)
            this.fileSelected = this.path
    },
    mounted() {

        // Initialize bucket URL

        this.bucketBaseUrl = this.getBucketBaseUrl();

    },
    watch: {
        value(val) {
            // console.log('NEW VAL :: ', val)
            this.fileSelected = val // watch change from parent component
        }
    },
    methods: {
        setMediaPreviewUpload(file) {
            let vue = this
            vue.fileSelected = file
            vue.$emit('onSelect', vue.fileSelected)
        },
        removeMediaPreview() {
            let vue = this
            vue.fileSelected = null
            vue.$emit('onSelect', vue.fileSelected)
        },
        openSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = true
            vue.$refs[vue.modalPreviewMultimedia.ref].getData()
        },
        onSelectMediaPreview(media) {
            let vue = this
            vue.closeSelectPreviewMultimediaModal()
            vue.fileSelected = media.file
            vue.$emit('onSelect', vue.fileSelected)
        },
        closeSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = false
        },
        removeAllFilesFromDropzone() {
            let vue = this
            if (vue.$refs.dropzoneDefault)
                vue.$refs.dropzoneDefault.limpiarArchivo()
        },
        removeImage() {
            let vue = this
            vue.previewImageCropped = null
            vue.imageCropped = null
            vue.removeAllFilesFromDropzone()
            vue.$emit('removeImage', null)
        },
        change({ coordinates, canvas }) {
		},
        resizeImage() {
            let vue = this
            const result = this.$refs.cropper.getResult();
            let data_url_canvas = result.canvas.toDataURL(
                "image/png"
            );
            vue.previewImageCropped = data_url_canvas;
            vue.$emit('croppedImage', data_url_canvas)
        },
        setPreviewUpload(file) {
            let vue = this
            vue.imageCropped = file;
            vue.$emit('onPreview', file)
        }
    }
}
</script>

<style lang="scss">

span.media-tag {
    z-index: 7;
    //border-radius: 0 !important;
    font-size: 11px;
    position: absolute;
    top: 0;
    right: .8rem;
    padding: 2px 4px;
    color: white;
    //width: min-content;
    text-align: center;
    cursor: pointer !important;
    border-radius: 3px;
}
.text_desc {
    font-family: "Nunito", sans-serif;
    font-size: 12px;
    line-height: 20px;
    color: #434D56;
    min-height: 45px;
    display: inline-block;
}
.mtb-10 {
    margin-top: 16px;
    margin-bottom: 16px;
}
.content_hover_file {
    position: relative;
}
.content_hover_file:hover .hover_upload {
    display: flex !important;
}
.content_hover_file .hover_upload {
    position: absolute;
    width: 100%;
    height: 100%;
    display: none;
    background: rgba(42, 54, 73, 0.91);
    justify-content: center;
    align-items: center;
    color: #fff;
    font-size: 14px;
    font-family: "Nunito", sans-serif;
    flex-direction: column;
}
.content_hover_file .hover_upload .icon_upload {
    margin-bottom: 10px;
    margin-top: 20px;
}

.box-cropper {
    border: solid 1px #eee;
    min-height: 160px;
    width: 100%;
}
.preview_image_cropped {
    margin: 0 auto;
    text-align: center;
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    .bx_img {
        max-height: 150px;
        img {
            max-width: 100%;
            height: auto;
            max-height: inherit;
        }
    }
    .bx_replace_img {
        display: none;
        position: absolute;
        background-color: #000000c2;
        width: 100%;
        height: 100%;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    }
    &:hover .bx_replace_img {
        display: flex;
        div {
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            img {
                max-width: 35px;
                margin-bottom: 4px;
            }
        }
    }
}
</style>
