<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
    >
        <template v-slot:content>

            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong># Ticket</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.id }}
                </v-col>
            </v-row>

            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Usuario</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.nombre }}
                </v-col>
            </v-row>

            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>DNI</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.dni }}
                </v-col>
            </v-row>

            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Estado</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.status.text }}
                </v-col>
            </v-row>

            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Motivo</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.reason }}
                </v-col>
            </v-row>

            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Detalle</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.detail }}
                </v-col>
            </v-row>
            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Contacto</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    <a target="_blank" :href="`https://wa.me/51${resource.contact}?text=Hola,te%20hablamos%20de%20cursalab`">{{resource.contact}}</a> <v-icon small color="green" class="ml-2 pb-2">fab fa-whatsapp</v-icon>
                </v-col>
            </v-row>
            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Info Soporte</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.info_support }}
                </v-col>
            </v-row>

            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Mensaje a usuario</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.msg_to_user }}
                </v-col>
            </v-row>


            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Fecha de creación</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.created_at }}
                </v-col>
            </v-row>


            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-start">
                    <strong>Última actualización</strong>
                </v-col>
                <v-col cols="8" class="d-flex justify-content-center">
                    {{ resource.updated_at }}
                </v-col>
            </v-row>

        </template>

    </DefaultDialog>
</template>

<script>

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
                user: '',
                status: null,
                msg_to_user: '',
                info_support: '',
            },
            resource: {}
        }
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        confirmModal() {
            let vue = this

        },
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {

            let vue = this

            vue.$nextTick(() => {
                vue.resource = Object.assign(
                    {}, vue.resource, vue.resourceDefault
                )
            })

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/${resource.id}/show`;

            await vue.$http.get(url)
                .then(({data}) => {

                    if (resource) {
                        vue.resource = Object.assign({}, data.data.ticket);
                        vue.resource.user = Object.assign({}, data.data.ticket.user);
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
