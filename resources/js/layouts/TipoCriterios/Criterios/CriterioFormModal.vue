<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="criterioForm">
                <v-row justify="space-around">

                    <DefaultErrors :errors="errors" />

                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.modulos"
                                       v-model="resource.modulo"
                                       label="Módulo"
                                       return-object
                                       :rules="rules.modulos"
                        />
                    </v-col>

                   <!--  <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.tipos"
                                       v-model="resource.tipo"
                                       label="Tipo"
                                       return-object
                                       :rules="rules.tipos"
                        />
                    </v-col>
         -->
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.valor"
                                      label="Nombre"
                                      :rules="rules.valor"
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

const fields = ['valor', 'tipo', 'modulo'];
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
                valor: '',
                modulo: null,
                tipo: null,
            },
            resource: {},
            selects: {
                modulos: [],
                tipos: [],
            },

            rules: {
                modulo: this.getRules(['required']),
                tipo: this.getRules(['required']),
                valor: this.getRules(['required', 'max:100']),
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
            vue.$refs.criterioForm.resetValidation()
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
            // Selects independientes
            vue.selects.modulos = []
            vue.selects.tipos = []
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
                vue.selects.tipos = data.data.tipos

                if (resource) {
                    vue.resource = data.data.criterio
                }
            })

            return 0;
        },
        loadSelects() {
        },

    }
}
</script>
