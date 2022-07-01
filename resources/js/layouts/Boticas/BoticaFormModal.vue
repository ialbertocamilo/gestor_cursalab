<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="boticaForm" class="--mb-15">
                
                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.modulos"
                            v-model="resource.modulo"
                            label="Módulos"
                            return-object
                            :rules="rules.modulo"
                            @onChange="loadGrupos"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.grupos"
                            v-model="resource.grupo"
                            :rules="rules.grupo"
                            return-object
                            label="Grupo"
                            no-data-text="Seleccione un módulo"
                        />
                    </v-col>
                </v-row>
                <!-- <v-row> -->
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
                            v-model="resource.codigo_local"
                            label="Código Local"
                            :rules="rules.codigo_local"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <img src="/svg/sedes.svg" width="350" class="my-7">
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['nombre', 'grupo', 'modulo', 'codigo_local'];
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
                codigo_local: '',
                modulo: null,
                grupo: null,
            },
            resource: {},
            selects: {
                modulos: [],
                grupos: [],
            },

            rules: {
                nombre: this.getRules(['required', 'max:200']),
                modulo: this.getRules(['required']),
                grupo: this.getRules(['required']),
                codigo_local: this.getRules(['required', 'max:100']),
                // imagen: this.getRules(['required']),
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
            vue.$refs.boticaForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            const validateForm = vue.validateForm('boticaForm')
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
            // Selects independientes
            vue.selects.modulos = []
            vue.selects.grupos = []
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            vue.errors = []

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.modulos = data.data.modulos
                vue.selects.grupos = data.data.grupos

                if (resource) {
                    vue.resource = data.data.botica
                }
            })

            return 0;
        },
        loadSelects() {
            let vue = this

            if (vue.resource.modulos && vue.resource.modulos.id)
                vue.loadGrupos()
        },

        loadGrupos() {
            let vue = this
            let base = `${vue.options.base_endpoint}`

            vue.selects.grupos = []
            vue.resource.grupo = null

            if (vue.resource.modulo && vue.resource.modulo.id) {
                let url = base + `/get-groups?config_id=${vue.resource.modulo.id}`

                vue.$http.get(url)
                    .then(({data}) => {
                        vue.selects.grupos = data.data.grupos
                    })
            }
        },
    }
}
</script>
