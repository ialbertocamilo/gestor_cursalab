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
                            v-model="resource.nombre"
                            label="Nombre"
                            :rules="rules.nombre"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.modulos"
                            v-model="resource.modulos"
                            label="Módulos"
                            multiple
                            return-object
                            :rules="rules.modulos"
                            :count-show-values="3"
                            :show-select-all="false"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.categorias"
                            v-model="resource.categoria"
                            label="Categoría"
                            return-object
                            :rules="rules.categorias"
                            @onChange="loadSubCategorias"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.subcategorias"
                            v-model="resource.subcategoria"
                            label="Sub Categoría"
                            return-object
                            :rules="rules.subcategorias"
                            no-data-text="Seleccione una categoría"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around" v-if="resource.scorm">
                    <v-col cols="12" class="">
                        <fieldset class="editor text-center p-2">
                            <legend>SCORM Actual</legend>

                            <a :href="resource.scorm" target="_blank">{{ resource.scorm }}</a>
                        </fieldset>
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputScorm"
                            v-model="resource.media"
                            label="SCORM"
                            :file-types="['scorm']"
                            @onSelect="setFile($event, resource, 'media')"/>
                    </v-col>
                </v-row>

                <!-- <iframe v-if="resource.scorm" id="ifrm" :src="resource.scorm" width="100%" height="300"></iframe> -->

                <v-row align="center" align-content="center">
                    <v-col cols="4" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.estado"/>
                    </v-col>
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['nombre', 'estado', 'categoria', 'subcategoria', 'modulos', 'media'];
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

                nombre: '',

                modulos: [],

                media: null,
                media_id: null,

                categoria: null,
                subcategoria: null,

                categoria_id: null,
                subcategoria_id: null,

                scorm: null,
                file_scorm: null,

                estado: true,
            },
            resource: {},
            selects: {
                modulos: [],
                categorias: [],
                subcategorias: [],
            },

            rules: {
                modulos: this.getRules(['required']),
                nombre: this.getRules(['required', 'max:100']),
                scorm: this.getRules(['required']),
                // dni: this.getRules(['required', 'number'])
            },
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
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('vademecumForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);

                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                    }).catch((error) => {
                    if (error && error.errors)
                        vue.errors = error.errors
                })

            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modulos = []
            vue.selects.categorias = []
            vue.selects.subcategorias = []
            vue.removeFileFromDropzone(vue.resource.scorm, 'inputScorm')
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.modulos = data.data.modulos
                vue.selects.categorias = data.data.categorias
                vue.selects.subcategorias = data.data.subcategorias

                if (resource) {
                    vue.resource = data.data.vademecum
                }
            })

            return 0;
        },
        loadSelects() {
            let vue = this

            if (vue.resource.categorias && vue.resource.categorias.id)
                vue.loadSubCategorias()
        },

        loadSubCategorias() {
            let vue = this
            let base = `${vue.options.base_endpoint}`

            vue.resource.subcategoria = null
            vue.selects.subcategorias = []

            if (vue.resource.categoria && vue.resource.categoria.id) {
                let url = base + `/get-subcategorias?categoria_id=${vue.resource.categoria.id}`

                vue.$http.get(url)
                    .then(({data}) => {
                        vue.selects.subcategorias = data.data.subcategorias
                    })
            }
        },
    }
}
</script>
