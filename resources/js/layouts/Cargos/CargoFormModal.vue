<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="cargoForm">

                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.nombre"
                                      label="Nombre"
                                      :rules="rules.nombre"
                        />
                    </v-col>
                </v-row>


            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['nombre'];
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
            resourceDefault: {
                id: null,
                nombre: '',
            },
            resource: {},
            selects: {
                modules: [],
                destinos: [],
            },

            rules: {
                nombre: this.getRules(['required', 'max:100']),
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
            vue.$refs.cargoForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            const validateForm = vue.validateForm('cargoForm')
            vue.errors = []

            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields, file_fields);

                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
                    .catch((error) => {
                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this

            // vue.selects.destinos = []
            // vue.removeFileFromDropzone(vue.resource.imagen, 'inputImagen')
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            vue.errors = []

            if (resource && resource.id)
            {
                let base = `${vue.options.base_endpoint}`
                let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

                await vue.$http.get(url).then(({data}) => {

                    // vue.selects.modules = data.data.modules

                    if (resource) {
                        vue.resource = data.data.cargo
                    }
                })
            }


            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
