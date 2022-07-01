<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="ayudaForm">

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex-- justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.nombre"
                                      label="Nombre"
                                      :rules="rules.nombre"
                        />
                    </v-col>

                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.orden"
                            label="Orden"
                            type="number"
                            min="1"
                            :max="resource.default_order"
                            :rules="rules.orden"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-start">
                            
                            <!-- label="Agregar Detalle" -->
                        <DefaultToggle v-model="resource.check_text_area"
                            active-label="Con detalle"
                            inactive-label="Sin detalle"
                        />
                    </v-col>
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

// import DefaultRichText from "../../components/globals/DefaultRichText";

const fields = ['nombre', 'check_text_area', 'orden'];
const file_fields = [];

export default {
    // components: {DefaultRichText},
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
                nombre: '',
                orden: 1,
                default_order: 1,
                check_text_area: true,
            },
            resource: {},
            selects: {
                // modules: [],
            },

            rules: {
                // modules: this.getRules(['required']),
                nombre: this.getRules(['required', 'max:250']),
                orden: this.getRules(['required', 'number']),
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
            vue.$refs.ayudaForm.resetValidation()
        },
        confirmModal() {
            let vue = this
            this.showLoader()

            const validateForm = vue.validateForm('ayudaForm')
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
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            // vue.selects.destinos = []
            // vue.removeFileFromDropzone(vue.resource.imagen, 'inputImagen')
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                // vue.selects.modules = data.data.modules

                if (resource) {
                    vue.resource = data.data.ayuda_app
                } else {
                    vue.resource.orden = data.data.default_order
                    vue.resource.default_order = data.data.default_order
                }
            })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
