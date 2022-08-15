<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <div
                v-for="(validation,i) in validations.list" :key="i">
                <strong v-text="validation.title"/> <br>
                {{ validation.subtitle || "" }}
                <ul class="mt-1">
                    <li v-for="(item, i) in validation.list" :key="i" v-html="item"/>
                </ul>
            </div>
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
        width: {
            type: String,
            required: false
        },
        resource: {
            required: true
        },
    },
    data() {
        return {
            validations: [],
            checkbox: false
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.checkbox = false
        },
        resetSelects() {
            let vue = this
            vue.checkbox = false
        },
        confirmModal() {
            let vue = this
            // let data = {
            //     checkbox: vue.checkbox,
            //     confirmMethod: vue.options.action
            // }
            // vue.$emit('onConfirm', data)
            vue.$emit('onConfirm')
        },
        async loadData(validations) {
            let vue = this
            vue.validations = Object.assign({}, validations)

            return 0;
        },
        loadSelects() {
            let vue = this

        },
    },
}
</script>
