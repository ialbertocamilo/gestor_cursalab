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
                <div v-if="validation.type == 'has_active_topics'">
                    <span class="lbl_pre">Al desactivar el curso, los usuarios tampoco podrán acceder a los temas. Ten en cuenta, que con esta acción, los estados de los temas no cambiarán.</span>
                </div>
                <div v-else-if="validation.type == 'check_if_is_required_course'">
                    <span class="lbl_pre">Para desactivarlo, no debe ser requisito de:</span>
                </div>
                <div v-else>
                    <strong v-text="validation.title ||'' "/> <br>
                    {{ validation.subtitle || "" }}
                </div>
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
            // console.log('emit Cancel modal')
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
<style lang="scss">
span.lbl_pre {
    margin-top: 10px;
    margin-bottom: 10px;
    font-family: "Nunito", sans-serif;
    font-size: 16px;
    color: #2A3649;
}
</style>
