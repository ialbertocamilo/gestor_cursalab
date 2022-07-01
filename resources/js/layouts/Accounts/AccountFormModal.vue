<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="accountForm" class="--mb-15">

                <DefaultErrors :errors="errors"/>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.name" label="Nombre" :rules="rules.name" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.email" label="Correo" :rules="rules.email" />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.username" label="User" :rules="rules.username" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.password" label="Contraseña" :rules="rules.password" type="password" />
                    </v-col>
                </v-row>

                <v-row align="center" align-content="center">
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultSelect :items="selects.services" v-model="resource.service" label="Servicio" item-text="name" return-object :rules="rules.service" />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultSelect :items="selects.plans" v-model="resource.plan" label="Plan" item-text="name" return-object :rules="rules.plan" />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultSelect :items="selects.types" v-model="resource.type" label="Tipo" item-text="name" return-object :rules="rules.type" />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.identifier" label="Identificador" :rules="rules.identifier" />
                    </v-col>
                </v-row>


                <v-subheader class="mt-5"><strong>Configuración de tokens</strong></v-subheader>

                <v-divider class="mx-3" />

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.key" label="API Key" :rules="rules.key" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.secret" label="API Secret" :rules="rules.secret" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.token" label="Token" :rules="rules.token" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.refresh_token" label="Refresh token" :rules="rules.refresh_token" />
                    </v-col>
                </v-row>

                <!-- <v-subheader class="mt-5"><strong>Datos adicionales</strong></v-subheader> -->

                <v-divider class="mx-3" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultTextArea v-model="resource.description" label="Descripción" :rows="4" />
                    </v-col>
                </v-row>

               <!--  <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0"
                           @click="showConfigTokens = !showConfigTokens"
                           style="cursor: pointer">
                        <strong>Configurar SDK y ZAK Tokens</strong>
                        <v-icon v-text="showConfigTokens ? 'mdi-chevron-up' : 'mdi-chevron-down'"/>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row> -->

     <!--            <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-center pt-0">

                        <v-expand-transition>
                            <v-row justify="space-around" align="start" align-content="center" v-show="showConfigTokens">
                                <v-col cols="12" class="d-flex justify-content-center">
                                    <DefaultTextArea v-model="resource.sdk_token" label="SDK Token" :disabled="true" :rows="4" />
                                </v-col>
                                <v-col cols="12" class="d-flex justify-content-start">
                                    <DefaultTextArea v-model="resource.zak_token" label="ZAK Token" :disabled="true" :rows="5"/>
                                </v-col>
                            </v-row>
                        </v-expand-transition>

                    </v-col>

                </v-row> -->

                <v-row align="center" align-content="center">
                    <v-col cols="6" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.active" />
                    </v-col>
                </v-row>

            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['name', 'email', 'username', 'password', 'key', 'secret', 'token', 'refresh_token', 'identifier', 'sdk_token', 'zak_token', 'service', 'plan', 'type', 'description', 'active'];

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
            showConfigTokens: false,
            resourceDefault: {
                id: null,

                name: null,

                email: '',
                username: '',
                password: '',

                key: null,
                secret: null,
                identifier: null,

                type: null,
                service: null,
                plan: null,

                token: null,
                refresh_token: null,

                active: true,

                description: '',

                sdk_token: null,
                zak_token: null,
            },
            resource: {},
            selects: {
                types: [],
                services: [],
                plans: [],
            },

            rules: {
                // text: this.getRules(['required', 'max:255']),
                name: this.getRules(['required', 'max:255']),
                // name: this.getRules(['required', 'max:255']),
                service: this.getRules(['required']),
                plan: this.getRules(['required']),
                type: this.getRules(['required']),
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
            vue.$refs.accountForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('accountForm')
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
                    }).catch((error) => {
                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.types = []
            vue.selects.selects = []
            vue.selects.plans = []
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

                let _data = data.data

                vue.selects.types = _data.types
                vue.selects.plans = _data.plans
                vue.selects.services = _data.services

                if (resource)
                    vue.resource = _data.account
            })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
