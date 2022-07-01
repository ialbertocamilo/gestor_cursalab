<template>
    <div>
        <fieldset class="editor">
            <legend>{{ label }}</legend>

            <v-card elevation="0" class="mx-4" v-if="description">
                <small v-text="description" />
            </v-card>

            <!-- AAAA {{ fileSelected }}  -->

            <!-- <v-card elevation="0" class="my-1" v-if="TypeOf(fileSelected) === 'object' || fileSelected === null"> -->
            <v-card elevation="0" class="my-1" v-if="(TypeOf(fileSelected) === 'object' && !fileSelected.ext ) || fileSelected === null">
                <v-card-text>
                    <DropzoneDefault
                        :ref="dropzoneDefault"
                        :types-allowed="fileTypes"
                        @onUpload="setMediaPreviewUpload"
                    />
                </v-card-text>
            </v-card>

            <v-card v-else elevation="0">
                <v-card-text class="d-flex justify-content-center align-items-center flex-column">
                <!--     <span class="media-tag" @click="removeMediaPreview">
                        &nbsp;<v-icon small color="red" title="Quitar" v-text="'mdi-close-circle-outline'" style="font-size: 30px;"/>&nbsp;
                    </span>
                    <v-img v-if="TypeOf(fileSelected) === 'object'" contain width="276" max-height="200"
                               :src="infoMedia(fileSelected).preview"
                    />
                    <v-img v-else contain width="276" height="200"
                           :src="'https://static.universidadcorporativafp.com.pe/'+fileSelected" -->
                <span class="media-tag"
                      @click="removeMediaPreview"
                >&nbsp;<v-icon small color="red" title="Quitar" v-text="'mdi-close-circle-outline'"
                               style="font-size: 30px;"/>&nbsp;</span>
                    <v-img contain width="276" height="200"
                           :src="getMediaPreview"
                           v-if="TypeOf(fileSelected) !== 'undefined'"
                    />
                </v-card-text>
            </v-card>

        </fieldset>

        <transition name="fade" v-if="showButton">
            <v-btn class="mt-1" color="primary" block elevation="0"
                   @click="openSelectPreviewMultimediaModal"
                   v-show="!fileSelected"
            >
                <v-icon class="mx-2" style="font-size: 0.95em;">fas fa-photo-video</v-icon>
                Seleccionar multimedia
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
        }
    },
    data() {
        return {
            fileSelected: null,
            dropzoneDefault: 'dropzoneDefault',
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
            let vue = this
            let preview = "https://static.universidadcorporativafp.com.pe/images/default-scorm-img_116.png"

            if (vue.TypeOf(this.fileSelected) === 'string') {
                const extension = this.fileSelected.split('.').at(-1).toLowerCase()
                if (vue.mixin_extensiones.image.includes(extension)) {
                    return 'https://static.universidadcorporativafp.com.pe/' + vue.fileSelected
                } else {
                    for (const mixinExtensionesKey in vue.mixin_extensiones) {
                        if (vue.mixin_extensiones[mixinExtensionesKey].includes(extension)) {
                            preview = vue.mixin_default_media_images[mixinExtensionesKey]
                        }
                    }
                }
            } else if (vue.TypeOf(this.fileSelected) === 'object' && this.fileSelected.ext) {
                const extension = this.fileSelected.ext
                if (vue.mixin_extensiones.image.includes(extension)) {
                    return 'https://static.universidadcorporativafp.com.pe/' + vue.fileSelected.file
                } else {
                    for (const mixinExtensionesKey in vue.mixin_extensiones) {
                        if (vue.mixin_extensiones[mixinExtensionesKey].includes(extension)) {
                            preview = vue.mixin_default_media_images[mixinExtensionesKey]
                        }
                    }
                }
            }
            return vue.fileSelected ? 'https://static.universidadcorporativafp.com.pe/' + preview : null
        }
    },
    created() {
        if (this.path)
            this.fileSelected = this.path
    },
    watch: {
        value(val) {
            console.log('NEW VAL :: ', val)
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
        removeAllFilesFromDropzone(){
            let vue = this
            if (vue.$refs.dropzoneDefault)
                vue.$refs.dropzoneDefault.limpiarArchivo()
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

</style>
