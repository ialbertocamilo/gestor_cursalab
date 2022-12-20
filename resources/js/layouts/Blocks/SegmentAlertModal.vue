<template>
    <DefaultDialog
        width="30vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <span class="text-start">
                        {{ options.resource }}
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
            let vue = this
            vue.$emit('onConfirm', true);
        },
        loadData(resource) {
        },
        loadSelects() {

        }
    }
}
</script>
