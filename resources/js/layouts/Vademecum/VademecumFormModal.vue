<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="vademecumForm">

                <DefaultErrors :errors="errors"/>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Nombre"
                            :rules="rules.name"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.modules"
                            v-model="resource.modules"
                            :itemText="'name'"
                            label="Módulos"
                            multiple
                            return-object
                            :rules="rules.modules"
                            :count-show-values="3"
                            :show-select-all="false"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.categories"
                            v-model="resource.category"
                            label="Categoría"
                            return-object
                            :rules="rules.categories"
                            @onChange="loadSubCategories"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.subcategories"
                            v-model="resource.subcategory"
                            label="Sub Categoría"
                            return-object
                            :rules="rules.subcategories"
                            no-data-text="Seleccione una categoría"
                        />
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            :itemText="'name'"
                            :items="media_types"
                            v-model="resource.media_type"
                            label="Tipo de contenido"
                            :rules="rules.media_type"
                            class="mt-2 mb-2"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around"
                       v-if="resource.scorm && (resource.media_type === 'scorm' || resource.media_type == null)">
                    <v-col cols="12" class="">
                        <fieldset class="editor text-center p-2">
                            <legend>SCORM Actual</legend>

                            <a :href="resource.scorm" target="_blank">
                                {{ resource.scorm }}
                            </a>
                        </fieldset>
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputScorm"
                            v-model="resource.media"
                            :label="getMediaTypeName(resource.media_type)"
                            :file-types="[resource.media_type]"
                            @onSelect="setFile($event, resource, 'media')"/>
                    </v-col>
                </v-row>

                <!-- <iframe v-if="resource.scorm" id="ifrm" :src="resource.scorm" width="100%" height="300"></iframe> -->

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

const fields = [
    'name', 'active', 'category', 'subcategory', 'modules', 'media', 'media_type'
];

const file_fields = ['scorm', 'media'];

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            errors: [],
            resourceDefault: {
                id: null,

                name: '',

                modules: [],

                media: null,
                media_id: null,
                media_type: null,

                category: null,
                subcategory: null,

                category_id: null,
                subcategory_id: null,

                scorm: null,
                file_scorm: null,

                active: true,
            },
            resource: {},
            selects: {
                modules: [],
                categories: [],
                subcategories: [],
            },

            rules: {
                name: this.getRules(['required', 'max:100']),
                modules: this.getRules(['required']),
                scorm: this.getRules(['required']),
                media_type: this.getRules(['required'])
            },
            media_types: [
                {name: 'PDF', id: 'pdf'},
                {name: 'SCORM', id: 'scorm'},
                {name: 'Imagen', id: 'image'},
            ]
        }
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.inputScorm.removeAllFilesFromDropzone()
            vue.$refs.vademecumForm.resetValidation()
        },
        getMediaTypeName (mediaTypeId) {

            if (!mediaTypeId) return 'SCORM';

            let mediaType = this.media_types.find(
                mediaType => mediaType.id === mediaTypeId
            );

            return mediaType ? mediaType.name : '';
        },
        confirmModal() {

            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('vademecumForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id
                        ? `${base}/${vue.resource.id}/update`
                        : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            if (validateForm) {

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
                );

                vue.$http
                    .post(url, formData)
                    .then(({data}) => {
                        if(vue.resource.category != null && (vue.resource.media_type === 'pdf' || vue.resource.media_type === 'scorm')){
                            vue.queryStatus("vademecum", "crear_contenido");
                        }
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')

                    }).catch((e) => {

                        if (e.response.data.msg) {
                            vue.showAlert(e.response.data.msg, 'warning')
                        }

                        if (e && e.errors)
                            vue.errors = e.errors
                })

            }

            this.hideLoader()
        }
        ,
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modules = []
            vue.selects.categories = []
            vue.selects.subcategories = []
            vue.removeFileFromDropzone(vue.resource.scorm, 'inputScorm')
        }
        ,
        async loadData(resource) {

            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign(
                    {}, vue.resource, vue.resourceDefault
                )
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource
                        ? `${base}/${resource.id}/edit`
                        : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.modules = data.data.modules
                vue.selects.categories = data.data.categories
                vue.selects.subcategories = data.data.subcategories

                if (resource) {
                    vue.resource = Object.assign({}, data.data.vademecum);
                }
            })

            return 0;
        },
        loadSelects() {

            let vue = this

            if (vue.resource.categories && vue.resource.categories.id)
                vue.loadSubCategories()
        },

        loadSubCategories() {

            let vue = this
            let base = `${vue.options.base_endpoint}`

            vue.resource.subcategory = null
            vue.selects.subcategories = []

            if (vue.resource.category && vue.resource.category.id) {

                let url = base + `/get-subcategories?category_id=${vue.resource.category.id}`

                vue.$http.get(url)
                    .then(({data}) => {

                        vue.selects.subcategories = data.data.subcategories
                    })
            }
        },
    }
}
</script>
