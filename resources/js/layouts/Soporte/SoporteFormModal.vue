<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="encuestaForm">

                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultAutocomplete clearable
                                             :items="selects.estados"
                                             v-model="resource.status"
                                             return-object
                                             label="Estado"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around" v-show="resource.estado.id == 'solucionado'">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.info_soporte"
                                      label="Info Soporte"
                                      :rules="resource.estado.id == 'solucionado' ? rules.info_soporte : []"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around" v-show="resource.estado.id == 'solucionado'">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.msg_to_user"
                                      label="Mensaje al usuario"
                                      :rules="resource.estado.id == 'solucionado' ? rules.msg_to_user : []"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <img src="/svg/ticket_status.svg" width="350" class="my-7">
                </v-row>

            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['status', 'info_soporte', 'msg_to_user'];
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
                status: {nombre: ''},
                msg_to_user: '',
                info_soporte: '',
            },
            resource: {
                status: {nombre: ''},},
            selects: {
                estados: [],
            },

            rules: {
                status: this.getRules(['required']),
                msg_to_user: this.getRules(['required', 'max:250']),
                info_soporte: this.getRules(['required', 'max:250']),
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
            vue.$refs.encuestaForm.resetValidation()
        },
        confirmModal() {
            let vue = this
            vue.errors = []
            const validateForm = vue.validateForm('encuestaForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/${vue.resource.id}/update`;

            let method = 'PUT';

            // if (validateForm && validateSelectedModules) {
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

                        if (error && error.error)
                            vue.errors.push({error: error.error})

                        if (error.response)
                        {
                            let data = error.response.data
                            vue.showAlert(data.msg, data.type)
                            this.hideLoader()
                        }
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.estados = []
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/${resource.id}/edit`;

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.estados = data.data.estados

                    if (resource) {
                        vue.resource = Object.assign({}, data.data.ticket)
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
