<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <div
                v-if="['validateUpdateStatus', 'validateDeleteCurso', 'validateUpdateCurso', 'validate_mover_curso', 'validateMoverCurso', 'messagesActions'].includes(options.action)">
                <div
                    v-for="(validate,i) in validateData.data" :key="i">
                    <strong v-text="validate.title"/> <br>
                    {{ validate.subtitle || "" }}
                    <ul class="mt-1">
                        <li v-for="(item, i) in validate.list" :key="i" v-html="item"/>
                    </ul>
                </div>
            </div>
            <!--            <div v-if="options.action === 'validate_mover_curso'">-->
            <!--                <strong>No se puede mover  este curso.</strong> <br>-->
            <!--                Para poder mover este curso es necesario quitar el requisito en los siguientes cursos:-->
            <!--                <ul class="mt-1">-->
            <!--                    <li v-for="curso in validateData.data" v-html="curso"/>-->
            <!--                </ul>-->
            <!--            </div>-->
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
    },
    data() {
        return {
            validateData: null,
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
            // vue.validateData = null
        },
        resetSelects() {
            let vue = this
            vue.checkbox = false
        },
        confirmModal() {
            let vue = this
            let data = {
                checkbox: vue.checkbox,
                confirmMethod: vue.options.action
            }
            vue.$emit('onConfirm', data)
        },
        async loadData(validateData) {
            let vue = this
            // console.log('VALIDATE DATA :: ', validateData)
            vue.validateData = validateData

            return 0;
        },
        loadSelects() {
            let vue = this

        },
    },
}
</script>
