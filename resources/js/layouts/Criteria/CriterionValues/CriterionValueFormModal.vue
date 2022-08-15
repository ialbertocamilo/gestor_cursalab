<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="criterioForm">
                <DefaultErrors :errors="errors"/>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInputDate
                            v-if="data_type === 'date'"
                            clearable
                            :referenceComponent="'modalDateFilter1'"
                            :options="modalDateFilter1"
                            v-model="resource.name"
                            label="Valor de Criterio"
                        />
                        <DefaultInput
                            v-else
                            clearable
                            v-model="resource.name"
                            label="Valor de Criterio"
                            :rules="rules.name"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <img src="/svg/criterios.svg" width="350" class="my-7">
                </v-row>

            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['name'];
const file_fields = [];

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
            modalDateFilter1: {
                open: false,
            },
            resourceDefault: {
                id: null,
                name: null,
            },
            resource: {},
            data_type: 'default',
            selects: {
                // tipos: [],
            },

            rules: {
                // tipo: this.getRules(['required']),
                name: this.getRules(['required', 'max:100']),
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
            vue.$refs['criterioForm'].resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            const validateForm = vue.validateForm('criterioForm')
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
                    .catch(async (error) => {
                        vue.hideLoader()

                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this

            vue.selects.tipos = []
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/form-selects`;

            await vue.$http.get(url).then(({data}) => {

                vue.data_type = data.data.data_type

                if (resource) {
                    vue.resource = data.data.criterion_value
                }
            })

            return 0;
        },
        loadSelects() {
        },


    }
}
</script>
