<template>
    <DefaultDialog
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <p class="text-start">Zoom</p>
                    <span class="text-start">
                        Para poder hacer uso de una cuenta de Zoom es necesario configurar las aplicaciones JWT y SDK
                        en el espacio para Developers, en la siguiente página de Notion se explica el paso a paso para poder hacerlo.
                        <a href="https://www.notion.so/cursalab/Zoom-11ec2bad43264921af0c471b3448b56e" target="_blank">¿Cómo configurar una cuenta de Zoom?</a>
                    </span>
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
        }
    },
    data() {
        return {
            resource: null
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {

        },
        loadData(resource) {
        },
        loadSelects() {

        }
    }
}
</script>
