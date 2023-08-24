<template>
    <DefaultDialog
        :options="options"
        title=""
        styleTitle="width:100vw !important"
        :width="options.width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>

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
            question:{}
        }
    },
    methods:{
        closeModal() {
            let vue = this
            vue.$emit('close')
        },
        loadData(question){
            this.question = question;
        },
        loadSelects (){

        },
        resetValidation (){

        },
        confirmModal() {
        }
    }
}
</script>