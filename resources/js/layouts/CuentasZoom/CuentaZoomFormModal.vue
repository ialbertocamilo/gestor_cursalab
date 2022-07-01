<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="cuentaZoomForm" class="mb-15">

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.usuario"
                                      label="Nombre"
                                      :rules="rules.usuario"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.correo"
                                      label="Correo"
                                      :rules="rules.correo"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.zoom_userid"
                                      label="User ID"
                                      :rules="rules.zoom_userid"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.pmi"
                                      label="Zoom PMI"
                                      :rules="rules.pmi"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.api_key"
                                      label="API Key"
                                      :rules="rules.api_key"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.client_secret"
                                      label="Client Secret"
                                      :rules="rules.client_secret"
                        />
                    </v-col>
                </v-row>


                <v-row align="center" align-content="center">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.tipos"
                                       v-model="resource.tipo"
                                       label="Tipo"
                                       return-object
                                       :rules="rules.tipos"
                        />
                    </v-col>
                    <v-col cols="6" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.estado" />
                    </v-col>
                </v-row>

                <v-row v-show="resource.id" align="center" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultTextArea
                                       v-model="resource.sdk_token"
                                       label="SDK Token"
                                       :disabled="true"
                                       :rows="4"
                        />
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-start">
                        <DefaultTextArea
                                       v-model="resource.zak_token"
                                       label="ZAK Token"
                                       :disabled="true"
                                       :rows="5"
                        />
                    </v-col>
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['usuario', 'correo', 'zoom_userid', 'pmi', 'tipo', 'estado', 'api_key', 'client_secret'];

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
                usuario: null,
                correo: '',
                zoom_userid: null,
                pmi: null,
                tipo: null,
                estado: true,
                api_key: null,
                client_secret: null,

                sdk_token: null,
                zak_token: null,
            },
            resource: {},
            selects: {
                tipos: [],
            },

            rules: {
                text: this.getRules(['required', 'max:255']),
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
            vue.$refs.cuentaZoomForm.resetValidation()
        },
        confirmModal() {
            let vue = this
            this.showLoader()

            const validateForm = vue.validateForm('cuentaZoomForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields);

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
            vue.selects.tipos = []
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.tipos = data.data.tipos

                if (resource)
                    vue.resource = data.data.cuenta
            })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
