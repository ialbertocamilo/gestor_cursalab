<template>
    <DefaultDialog
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
        :width="'45vw'"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <span class="text-start">
                     Recuerda cumplir con el horario de inicio y final de tu reunión.
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
