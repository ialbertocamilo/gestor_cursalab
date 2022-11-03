<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="glosarioImportForm">

                <DefaultErrorsImport :errors="errors" />
                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.modulos"
                                       v-model="resource.modulo_id"
                                       label="Módulos"
                                       return-object
                                       :rules="rules.modulos"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.categorias"
                                       v-model="resource.categoria_id"
                                       label="Categorías"
                                       return-object
                                       :rules="rules.categorias"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center">
                        <DefaultSelectOrUploadMultimedia
                            ref="inputExcel"
                            v-model="resource.excel"
                            label="Excel"
                            :rules="rules.excel"
                            :show-button="false"
                            :file-types="['excel']"
                            @onSelect="setFile($event, resource, 'excel')"/>
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <a download href="/templates/Plantilla-Glosario.xlsx">Descargar plantilla</a>
                </v-row>

            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

import DefaultErrorsImport from "../../components/globals/DefaultErrorsImport";

const fields = ['modulo_id', 'categoria_id'];

const file_fields = ['excel'];

export default {
    components: {
        DefaultErrorsImport
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
            errors: [],
            resourceDefault: {
                excel: null,
                file_excel: null,
                modulo_id: null,
                categoria_id: null,

            },
            resource: {},
            selects: {
                modulos: [],
                categorias: [],
            },

            rules: {
                excel: this.getRules(['required']),
                modulos: this.getRules(['required']),
                categorias: this.getRules(['required']),
                // modulo: this.getRules(['max:50']),
                // nombre: this.getRules(['required', 'max:200']),
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
            vue.$refs.glosarioImportForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('glosarioImportForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/import`;
            let method = 'POST';

            if (validateForm ) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);

                vue.$http.post(url, formData)
                    .then(({data}) => {

                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()

                    }).catch((error) => {

                        if (error && error.errors)
                            vue.errors = error.errors

                        if (error.response)
                        {
                            // vue.$emit('onCancel')
                            let data = error.response.data
                            vue.showAlert(data.msg, data.type)
                            this.hideLoader()
                        }
                    })
            }else{
                this.hideLoader()
            }

        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modulos = []
            vue.selects.categorias = []

            vue.removeFileFromDropzone(vue.resource.file_excel, 'inputExcel')
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/import`;

            // let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/search` : `form-selects`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modulos = data.data.modulos
                    vue.selects.categorias = data.data.categorias
                })
            return 0;
        },
        loadSelects() {
            let vue = this
            // if (vue.resource.modulo && vue.resource.modulo.id)
            //     vue.loadBoticas()
        },
    }
}
</script>
