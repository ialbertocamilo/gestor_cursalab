<template>

    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="videotecaForm" class="--mb-15">

                <v-row justify="space-around">
                    <v-col cols="12" md="6" lg="6" class="mx-auto">
                        <DefaultSelect
                            clearable
                            :items="selects.modules"
                            v-model="resource.modules"
                            label="Módulos"
                            multiple
                            return-object
                            :rules="rules.modules"
                            :count-show-values="1"
                            :show-select-all="false"
                        />
                    </v-col>
                    <v-col cols="12" md="6" lg="6" class="mx-auto">
                        <DefaultSelect
                            clearable
                            :items="selects.categorias"
                            v-model="resource.category_id"
                            label="Categoría"
                            return-object
                            :rules="rules.categorias"
                            :count-show-values="3"
                            :show-select-all="false"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.title"
                            label="Título"
                            :rules="rules.title"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultRichText
                            v-model="resource.description"
                            label="Descripción"
                            @stateLength="checkDescription = $event"
                        />
                    </v-col>


                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelectOrCreate
                            clearable
                            :items="selects.tags"
                            v-model="resource.tags"
                            return-object
                            multiple
                            :count-show-values="5"
                            label="Tags"
                            :show-select-all="false"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" md="6" lg="6">

                        <DefaultSelect
                            clearable
                            :items="media_types_id"
                            v-model="resource.media_type"
                            label="Tipo de contenido"
                            return-object
                            :rules="rules.media_type"
                            class="mt-2 mb-2"
                        />

                        <div v-if="['youtube', 'vimeo'].includes(resource.media_type ? resource.media_type.id : [])">
                            <v-textarea outlined dense hide-details clearable no-resize rows="6"
                                        :label="media_type_selected==='youtube' ? 'Link' : 'Código'"
                                        v-model="resource.media_video"
                            />
                        </div>

                        <div class="d-flex flex-column h-dropzone" v-else>

                            <DefaultSelectOrUploadMultimedia
                                ref="inputMedia"
                                v-model="resource.media"
                                label="Multimedia"
                                :file-types="[resource.media_type ? resource.media_type.id : '']"
                                @onSelect="setFile($event, resource, 'media')"/>
                        </div>

                    </v-col>


                    <v-col cols="12" md="6" lg="6" class="d-flex-- justify-content-center p-dropzone">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputImage"
                            v-model="resource.preview"
                            label="Previsualización"
                            :file-types="['image']"
                            description="Tiene una portada por defecto. Puedes asignar otra de <b>500 x 350 px - max. 10 Mb.</b>"
                            @onSelect="setFile($event, resource, 'preview')"/>
                    </v-col>

                </v-row>

                <v-row align="center" align-content="center">
                    <v-col cols="4" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.active"/>
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>

import SelectMultimedia from '../../components/forms/SelectMultimedia';
import DefaultRichText from "../../components/globals/DefaultRichText";
import DropzoneDefault from "../forms/DropzoneDefault";

const fields = ['title', 'description', 'category_id', 'media_type', 'media_video', 'active', 'tags', 'modules',
    'preview', 'media'];
const file_fields = ['preview', 'media'];

export default {
    components: {
        DropzoneDefault,
        SelectMultimedia,
        DefaultRichText,
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String

    },
    data() {
        return {
            searchTag: null,
            modalMultimedia: {
                open: false,
            },
            modalPreviewMultimedia: {
                open: false,
            },
            fileMediaSelected: null,
            fileMediaPreviewSelected: null,
            contentMissing: false,
            isLoading: false,

            resourceDefault: {
                id: null,
                title: '',
                description: '',
                modules: [],
                category_id: null,
                tags: [],

                media_type: null,
                media_video: '',

                preview: null,
                file_preview: null,

                media: null,
                file_media: null,


                active: true,
            },
            resource: {},
            selects: {
                modules: [],
                categorias: [],
                tags: [],
            },
            // rules: {
            //     modules: [
            //         v => !!v || 'Campo requerido',
            //         v => (v && v.length !== 0) || 'Debe seleccionar al menos un módulo.',
            //     ],
            //     categorias: [
            //         v => !!v || 'Debes seleccionar una categoría',
            //     ],
            //     title: [
            //         v => !!v || 'Campo requerido',
            //         v => (v && v.length <= 100) || 'Máximo 280 caracteres.',
            //     ],
            //     description: [
            //         v => !!v || 'Campo requerido',
            //         v => (v && v.length <= 280) || 'Máximo 280 caracteres.',
            //     ],
            //     tags: [
            //         v => !!v || 'Campo requerido',
            //         v => (v && v.length !== 0) || 'No olvides agregar al menos un tag para mejorar la relación entre los contenidos.',
            //     ]
            // },
            rules: {
                modules: this.getRules(['required']),
                categorias: this.getRules(['required']),
                tags: this.getRules(['required']),
                media_type: this.getRules(['required']),

                title: this.getRules(['required', 'max:100']),
                description: this.getRules(['required', 'max:280']),
            },
            checkDescription: false,
            media_type_selected: this.resource ? this.resource.media_type : null,
            media_types: [
                {text: 'Youtube', value: 'youtube'},
                {text: 'Vimeo', value: 'vimeo'},
                {text: 'Audio', value: 'audio'},
                {text: 'Video', value: 'video'},
                {text: 'PDF', value: 'pdf'},
                {text: 'SCORM', value: 'scorm'},
                {text: 'Imagen', value: 'image'},
            ],
            media_types_id: [
                {nombre: 'Youtube', id: 'youtube'},
                {nombre: 'Vimeo', id: 'vimeo'},
                {nombre: 'Audio', id: 'audio'},
                {nombre: 'Video', id: 'video'},
                {nombre: 'PDF', id: 'pdf'},
                {nombre: 'SCORM', id: 'scorm'},
                {nombre: 'Imagen', id: 'image'},
            ]
        };
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.fileMediaSelected = null
            vue.fileMediaPreviewSelected = null
            vue.contentMissing = false
            // vue.$refs.form.reset()
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit("onCancel");
        },
        resetForm() {
            let vue = this;
            if (vue.$refs.form)
                vue.$refs.form.reset()
        },
        resetValidation() {
            let vue = this
            vue.$refs.videotecaForm.resetValidation()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modules = []
            vue.selects.categorias = []
            vue.selects.tags = []

            vue.removeFileFromDropzone(vue.resource.media, 'inputMedia')
            vue.removeFileFromDropzone(vue.resource.preview, 'inputImage')
        },
        async confirmModal() {
            let vue = this;

            this.showLoader()

            const validateForm = vue.validateForm('videotecaForm')

            //val description
            if(vue.checkDescription) return this.hideLoader();

            if (validateForm) {
                let base = `${vue.options.base_endpoint}`
                let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

                let method = vue.resource.id ? 'PUT' : 'POST';

                let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);
                // vue.logFormDataValues(formData)
                // return
                vue.$http.post(url, formData)
                    .then((res) => {
                        if(vue.resource.tags.length>0){
                            vue.queryStatus("videoteca", "crear_contenido");
                        }
                        vue.showAlert(res.data.msg)
                        vue.closeModal()
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
                    .catch(e => {
                        this.hideLoader()
                        console.log(e)

                        if (e.response.data.msg) {
                            vue.showAlert(e.response.data.msg, 'warning')
                        }
                    })

            } else {
                this.hideLoader()
            }

        },
        // validateContent() {
        //     let vue = this;
        //     if (['youtube', 'vimeo'].includes(vue.resource.media_type))
        //         return !!vue.resource.media_video

        //     return !(!vue.fileMediaSelected && !vue.resource.media)
        // },
        // prepareData() {
        //     let vue = this;
        //     // vue.resource.media_type = vue.media_type_selected
        //     let formData = new FormData();
        //     formData.append('id', vue.resource.id);
        //     formData.append('title', vue.resource.title);
        //     formData.append('description', vue.resource.description);
        //     formData.append('category_id', vue.resource.category_id);
        //     formData.append('media_type', vue.resource.media_type);
        //     vue.resource.tags.forEach(el => formData.append(`tags[]`, el.nombre || el))
        //     // formData.append('tags', vue.resource.tags);
        //     // formData.append('modules', vue.resource.modules);
        //     vue.resource.modules.forEach(el => formData.append(`modules[]`, el.id))

        //     formData.append('media_video', vue.resource.media_video);
        //     formData.append('active', vue.resource.active ? '1' : '0');

        //     if (vue.fileMediaPreviewSelected) {
        //         formData.append('preview', vue.fileMediaPreviewSelected);
        //     } else if (vue.resource.preview) {
        //         formData.append('preview_id', vue.resource.preview.id);
        //     }

        //     if (!['youtube', 'vimeo'].includes(vue.resource.media_type)) {
        //         if (vue.fileMediaSelected) {
        //             formData.append('media', vue.fileMediaSelected);
        //         } else {
        //             formData.append('media_id', vue.resource.media.id);
        //         }
        //     }

        //     return formData
        // },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        // validateTag(tag, wordsAllowed = 3) {
        //     let text = tag.nombre ? tag.nombre : tag;
        //     let wordsCount = (text.split(" ").length);
        //     return wordsCount <= wordsAllowed
        // },
        // changeTags() {
        //     let vue = this
        //     let lastTag = vue.resource.tags.at(-1)
        //     let valid = vue.validateTag(lastTag)
        //     if (!valid) {
        //         // vue.$notification.warning(`El tag tiene más de 3 palabras`, {
        //         //     timer: 6,
        //         //     showLeftIcn: true,
        //         //     showCloseIcn: true
        //         // });
        //         vue.resource.tags.splice(-1, 1)
        //     }
        //     // else {
        //     //     vue.resource.tags.forEach(el => {
        //     //         if (!el.id) {
        //     //             el = el.toLowerCase()
        //     //         }
        //     //     })
        //     //
        //     // }
        // },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.categorias = data.data.categorias
                vue.selects.modules = data.data.modules
                vue.selects.tags = data.data.tags

                if (resource) {
                    vue.resource = data.data.videoteca
                }
            })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }

};
</script>
<style lang="scss">
.h-dropzone .editor .dropzone .dz-preview,
.p-dropzone .editor .dropzone .dz-preview {
    min-height: 210px;
    padding: 20px 20px;
}
.h-dropzone .dropzone.dz-clickable,
.p-dropzone .dropzone.dz-clickable {
    min-height: 220px;
    align-items: center;
    display: grid;
}
.h-dropzone .dropzone.dz-clickable.dz-started.dz-max-files-reached,
.p-dropzone .dropzone.dz-clickable.dz-started.dz-max-files-reached {
    max-height: initial;
}
.h-dropzone .dropzone.dz-clickable.dz-started.dz-max-files-reached:hover,
.p-dropzone .dropzone.dz-clickable.dz-started.dz-max-files-reached:hover {
    border: none !important;
}
.h-dropzone .dropzone .dz-message,
.p-dropzone .dropzone .dz-message {
    margin: 10px 0;
}
.h-dropzone .vue-dropzone > .dz-preview .dz-remove,
.p-dropzone .vue-dropzone > .dz-preview .dz-remove {
    padding-top: 135px;
}
.h-dropzone .vue-dropzone > .dz-preview:hover .dz-remove:before,
.p-dropzone .vue-dropzone > .dz-preview:hover .dz-remove:before {
    top: 70px;
}
.h-dropzone .dropzone .dz-preview.dz-file-preview .dz-details .dz-inf,
.h-dropzone .dropzone .dz-preview.dz-image-preview .dz-details .dz-inf,
.p-dropzone .dropzone .dz-preview.dz-file-preview .dz-details .dz-inf,
.p-dropzone .dropzone .dz-preview.dz-image-preview .dz-details .dz-inf {
    display: none;
}
.h-dropzone .dropzone .dz-preview.dz-file-preview .dz-image,
.h-dropzone .dropzone .dz-preview.dz-image-preview .dz-image,
.p-dropzone .dropzone .dz-preview.dz-file-preview .dz-image,
.p-dropzone .dropzone .dz-preview.dz-image-preview .dz-image {
    margin-top: 36px;
}
</style>
