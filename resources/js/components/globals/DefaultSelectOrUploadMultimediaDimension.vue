<template>
    <div>
        <fieldset class="editor position-relative">
            <legend>{{ label }}</legend>
           {{ getMediaPreview }} - {{ (TypeOf(fileSelected) !== 'undefined') }} - {{ (TypeOf(fileSelected) === 'object' && !fileSelected.ext) }}
            <v-card elevation="0" class="mx-4 mtb-10" v-if="description">
                <small class="text_desc" v-html="description"/>
            </v-card>

            <v-alert text v-model="alertErrorDimension" type="error" dismissible class="mx-2 mb-0">
                <span>Por favor ingrese las dimensiones correctas.</span>
            </v-alert>

            <!-- AAAA {{ fileSelected }}  -->

            <!-- <v-card elevation="0" class="my-1" v-if="TypeOf(fileSelected) === 'object' || fileSelected === null"> -->
            <v-card elevation="0" class="my-1"
                    v-if="(TypeOf(fileSelected) === 'object' && !fileSelected.ext ) || fileSelected === null">
                <v-card-text>
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
                    <v-img contain width="276" height="230"
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
            <v-btn class="mt-1" color="primary" block elevation="0"
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
            width="85vw"
            @onClose="closeSelectPreviewMultimediaModal"
            @onConfirm="onSelectMediaPreview"
        />
    </div>

</template>

<script>
import DropzoneDefault from "../forms/DropzoneDefault";
import SelectMultimedia from "../forms/SelectMultimedia";

export default {
    components: {SelectMultimedia, DropzoneDefault},
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
        checkDimensions: {
            type: Boolean,
            default: false
        },
        stackDimensions: {
            type: Object
        }
    },
    data() {
        return {
            bucketBaseUrl: '',
            fileSelected: null,
            dropzoneDefault: 'dropzoneDefault',
            alertErrorDimension: false,
            modalPreviewMultimedia: {
                ref: 'modalSelectPreviewMultimedia',
                open: false,
                title: 'Buscar multimedia',
                confirmLabel: 'Seleccionar',
                cancelLabel: 'Cerrar'
            },
        }
    },
    computed: {
        getMediaPreview() {
            console.log('computed watch');
            let vue = this
            // let preview = this.bucketBaseUrl + '/' + "images/default-scorm-img_116_360.png"
            let preview = "images/default-scorm-img_116_360.png"

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
                ? this.bucketBaseUrl + '/' + preview
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
        value:{
            handler(val) {
                let vue = this;
                console.log('DefaultSelect', val);

                if(vue.TypeOf(val) === 'object' && val) {
                    vue.$refs.dropzoneDefault.addManually(val); // al dropzone 
                } else {
                    vue.fileSelected = val;
                }
            },
            immediate: true
        }
    },
    methods: {
        async setMediaPreviewUpload(file) {
            let vue = this
            vue.fileSelected = file
            vue.$emit('onSelect', vue.fileSelected)

            await vue.checkFileDimensions(); 
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
        async onSelectMediaPreview(media) {
            let vue = this
            vue.closeSelectPreviewMultimediaModal()
            vue.fileSelected = media.file
            vue.$emit('onSelect', vue.fileSelected)

            await vue.checkFileDimensions();
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

        /* === CHECK DIMENSIONS === */
        async checkFileDimensions() {
            let vue = this;
            if(vue.checkDimensions && (vue.TypeOf(vue.fileSelected) === 'null')) return;
            if(!vue.checkDimensions) return; // sin verificacion de dimeniones

            const fileExtension = vue.getFileExtension(vue.fileSelected);
            // === verifica si es una imagen / y verifiacion activada ===
            if (['jpg','png','jpeg','gif'].includes(fileExtension)) {
                const fileCurrent = (vue.TypeOf(vue.fileSelected) === 'object') ? 
                                    vue.fileSelected : vue.getMediaPreview;
                const filePreviewUrl = vue.getFilepreviewUrl(fileCurrent);
                const { width, height } = await vue.createDomImagebyPath(filePreviewUrl);

                const checkWidth = (vue.stackDimensions.width === width);
                const checkHeight = (vue.stackDimensions.height === height);

                return (checkWidth && checkHeight) ? true : vue.showErrorAlert();
            } else {
                throw Error(`File can't be check dimension, please try again`);
            }
        },
        createDomImagebyPath(path){
            const domImage = document.createElement('img');
                  domImage.src = path;

            const promise = new Promise((resolve) => {
                domImage.addEventListener('load', () => {
                    const { naturalWidth: width,  naturalHeight: height } = domImage;
                    resolve({ width, height });
                });
            });

            return promise;
        },
        showErrorAlert() {
            let vue = this;
            vue.alertErrorDimension = true;
            vue.removeMediaPreview();
            vue.removeAllFilesFromDropzone();
        }
        /* === CHECK DIMENSIONS === */
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
</style>
