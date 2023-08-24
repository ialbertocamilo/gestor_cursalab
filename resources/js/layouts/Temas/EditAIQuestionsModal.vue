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
            <v-row class="mt-2 mb-6">
                Modifica el contenido y luego guarda los cambios
            </v-row>
            <v-row class="my-2">
                <DefaultInput label="Pregunta" dense outlined v-model="question.question"></DefaultInput>
            </v-row>
            <v-row class="my-2 d-flex align-items-center" v-for="(options,index) in question.options" :key="index">
                <DefaultInput label="Alternativa" dense outlined v-model="options.text"></DefaultInput>
                <v-checkbox
                    class="mt-6"
                    v-model="options.isCorrect"
                ></v-checkbox>
                <v-btn @click="deleteOption(index)" color="primary" icon><v-icon small>mdi-close</v-icon> </v-btn>
            </v-row>
            <v-row>
                <v-btn  @click="addOption()" color="primary" text><v-icon small>mdi-playlist-plus</v-icon> Añadir alternativa</v-btn>
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
            question:{}
        }
    },
    methods:{
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        loadData(question){
            this.question = question;
        },
        loadSelects (){

        },
        resetValidation (){

        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm',vue.question)
        },
        addOption(){
            this.question.options.push(
                { text: "", isCorrect: false }
            );
        },
        deleteOption(index){
            console.log(index);
            this.question.options.splice(index,1);
        },

    }
}
</script>