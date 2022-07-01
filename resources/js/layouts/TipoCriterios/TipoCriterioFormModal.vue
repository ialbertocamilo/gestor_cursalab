<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="tipoCriterioForm">
                
                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.nombre"
                            label="Nombre"
                            :rules="rules.nombre"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.nombre_plural"
                            label="Plural"
                            :rules="rules.nombre"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.tipos"
                            v-model="resource.data_type"
                            label="Destino"
                            return-object
                            :rules="rules.tipos"
                        />
                    </v-col>
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

                </v-row>

                <v-row align="start" align-content="center">

                    <v-col cols="4" class="--d-flex --justify-content-start">
                        <DefaultToggle
                            v-model="resource.obligatorio"
                            type="oligatorio"
                            label="¿Obligatorio?"/>
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

const fields = ['nombre', 'obligatorio', 'data_type', 'nombre_plural', 'orden'];
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
                nombre_plural: null,
                tipo: null,
                obligatorio: true,
                orden: 1,
                default_order: 1,
            },
            resource: {},
            selects: {
                tipos: [],
            },

            rules: {
                tipos: this.getRules(['required']),
                nombre: this.getRules(['required', 'max:150']),
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
            vue.$refs.tipoCriterioForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            const validateForm = vue.validateForm('tipoCriterioForm')
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
                    .catch((error) => {
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
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.tipos = data.data.tipos

                if (resource) {
                    vue.resource = data.data.tipo_criterio
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
