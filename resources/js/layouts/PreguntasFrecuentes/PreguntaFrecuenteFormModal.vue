<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="preguntaFrecuenteForm">

                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.title"
                                      label="Pregunta"
                                      :rules="rules.title"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.content"
                                      label="Respuesta"
                                      :rules="rules.content"
                        />
                    </v-col>
                </v-row>

                <v-row align="start" align-content="center">
                    <v-col cols="4" class="d-flex justify-content-start">
                        <DefaultInput clearable
                                      v-model="resource.position"
                                      label="Orden"
                                      type="number"
                                      min="1"
                                      :max="resource.default_order"
                                      :rules="rules.position"
                        />
                    </v-col>
                    <v-col cols="4" class="--d-flex justify-content-start">
                        <DefaultToggle v-model="resource.active" />
                    </v-col>
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

import DefaultRichText from "../../components/globals/DefaultRichText";

const fields = ['title', 'active', 'content', 'position'];
const file_fields = [];

export default {
    components: {DefaultRichText},
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
                title: '',
                content: null,
                active: true,
                position: 1,
                default_order: 1,
            },
            resource: {},
            selects: {
                // destinos: [],
            },

            rules: {
                // modules: this.getRules(['required']),
                title: this.getRules(['required', 'max:250']),
                content: this.getRules(['required', 'max:5000']),
                position: this.getRules(['required', 'number']),
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
            vue.$refs.preguntaFrecuenteForm.resetValidation()
        },
        confirmModal() {

            let vue = this
            vue.errors = []
            this.showLoader()

            const validateForm = vue.validateForm('preguntaFrecuenteForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id
                        ? `${base}/${vue.resource.id}/update`
                        : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, file_fields
                );

                vue.$http
                    .post(url, formData)
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
            // vue.selects.modules = []
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource
                        ? `${base}/${resource.id}/edit`
                        : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                if (resource) {

                    vue.resource = data.data.post

                } else {

                    vue.resource.position = data.data.default_order
                    vue.resource.default_order = data.data.default_order
                }
            })

            return 0;
        },
        loadSelects() {
            let vue = this
            // if (vue.resource.modules && vue.resource.modules.id)
            //     vue.loadBoticas()

            // if (vue.resource.botica && vue.resource.botica.criterio)
            //     vue.loadCarreras()
        },
    }
}
</script>
