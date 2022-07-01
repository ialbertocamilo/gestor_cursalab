<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="encuestaForm">
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.titulo"
                            label="Título"
                            :rules="rules.titulo"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.tipos"
                            v-model="resource.anonima"
                            label="Tipo"
                            return-object
                            :rules="rules.tipo"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.secciones"
                            v-model="resource.tipo"
                            return-object
                            label="Sección"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputImagen"
                            v-model="resource.imagen"
                            label="Imagen"
                            :file-types="['image']"
                            @onSelect="setFile($event, resource, 'imagen')"/>
                    </v-col>
                </v-row>

                <v-row align="start" align-content="center">
                    <v-col cols="4" class="d-flex justify-content-start">
                        <DefaultToggle v-model="resource.estado"/>
                    </v-col>
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['tipo', 'anonima', 'titulo', 'estado'];
const file_fields = ['imagen'];


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
            resourceDefault: {
                id: null,
                titulo: '',
                anonima: null,
                tipo: [],
                // secciones: [],
                estado: null,
            },
            resource: {},
            selects: {
                tipos: [],
                secciones: [],
            },

            rules: {
                // modulo: this.getRules(['required']),
                titulo: this.getRules(['required', 'max:100']),
                // dni: this.getRules(['required', 'number'])
            }
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
            // if (vue.options.action !== 'edit') {
            vue.$refs.inputImagen.removeAllFilesFromDropzone()
            // }
            vue.$refs.encuestaForm.resetValidation()
        },
        confirmModal() {
            let vue = this
            const validateForm = vue.validateForm('encuestaForm')
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
                        this.hideLoader()
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.tipos = []
            vue.selects.secciones = []
            vue.removeFileFromDropzone(vue.resource.imagen, 'inputImagen')
        },
        async loadData(resource) {
            let vue = this

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            // let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/search` : `form-selects`}`
            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.tipos = data.data.tipos
                    vue.selects.secciones = data.data.secciones

                    if (resource) {
                        vue.resource = data.data.encuesta
                    }

                })
            return 0;
        },
        loadSelects() {
            let vue = this
            // if (vue.resource.modulo && vue.resource.modulo.id)
            //     vue.loadBoticas()

            // if (vue.resource.botica && vue.resource.botica.criterio)
            //     vue.loadCarreras()
        },
    }
}
</script>
