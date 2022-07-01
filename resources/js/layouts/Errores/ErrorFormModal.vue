<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"

    >
        <template v-slot:content>
            <v-form ref="errorForm">

                <v-row justify="space-around">
                    <v-col cols="8" class="d-flex justify-content-start">
                        <v-row justify="space-around">
                            <v-col cols="12" class="d-flex justify-content-start py-1">
                                <strong>
                                    <span :title="resource.platform ? resource.platform.nombre : 'Unknown'">

                                        {{ resource.platform &&  resource.platform.code == 'app'? '📱' : '💻' }}
                                    </span>

                                    <span class="-" v-if="resource.user">{{ resource.user.name }}</span>
                                    <span class="-" v-if="resource.usuario">{{ resource.usuario.nombre }} - DNI {{ resource.usuario.dni }}</span>
                                    <span class="-" v-if="!resource.usuario && !resource.user">Anónimo</span>

                                    - IP {{ resource.ip }}
                                </strong>
                            </v-col>
                            <v-col cols="12" class="d-flex justify-content-start py-1">
                                <p class="w-100">{{ resource.url }}</p>
                            </v-col>
                        </v-row>
                    </v-col>

                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.statuses"
                                       v-model="resource.status"
                                       label="Estado"
                                       return-object
                                       :rules="rules.status"
                                       :show-select-all="false"
                        />
                    </v-col>
                </v-row>

                <v-divider class="mt-0"  />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-start py-1">
                        <strong>Mensaje</strong>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-start py-1">
                        <p class="w-100">{{ resource.message }}</p>
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-start py-1">
                        <strong>Ruta de archivo</strong> &nbsp; <small> {{ resource.file }} [{{ resource.line }}]</small>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-start py-1">
                        <p class="w-100">{{ resource.file_path }}</p>
                    </v-col>
                </v-row>

                <v-divider class="mt-0"  />

                <v-row justify="space-around">

                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <strong>Navegador</strong>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <strong>Dispositivo</strong>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <strong>Sistema Operativo</strong>
                    </v-col>

                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <p class="w-100">{{ resource.browser_name || 'No definido' }}</p>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <p class="w-100">{{ resource.device_family }}</p>
                        <p class="w-100">{{ resource.device_version }}</p>
                        <p class="w-100">{{ resource.device_model }}</p>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <p class="w-100">{{ resource.platform_name || 'Unknown' }}</p>
                    </v-col>

                </v-row>

                <v-divider class="mt-0"  />

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <strong>Fecha de origen</strong>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <strong>Última actualización</strong>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <!-- <strong>Última actualización</strong> -->
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <p class="w-100">{{ resource.created_at }}</p>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                        <p class="w-100">{{ resource.updated_at }}</p>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-start py-1">
                    </v-col>
                </v-row>

                <v-divider class="mt-0"  />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-start py-1">
                        <strong>Rastro de error</strong>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-start py-1">
                        <pre class="w-100" style="max-height: 150px;">{{ resource.stack_trace }}</pre>
                    </v-col>
                </v-row>

            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

import DefaultRichText from "../../components/globals/DefaultRichText";

const fields = ['status'];
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
            resourceDefault: {
                id: null,

                status: null,
            },
            resource: {},
            selects: {
                statuses: [],
            },

            rules: {
                status: this.getRules(['required']),
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
            vue.$refs.errorForm.resetValidation()
        },
        confirmModal() {
            let vue = this
            this.showLoader()

            const validateForm = vue.validateForm('errorForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/${vue.resource.id}/update`;

            let method = 'PUT';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields, []);

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
            vue.selects.statuses = []
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.statuses = data.data.statuses

                if (resource) {
                    vue.resource = data.data.error
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
