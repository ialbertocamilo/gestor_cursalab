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
                <DefaultInput label="Pregunta" dense outlined v-model="localQuestion.question"></DefaultInput>
            </v-row>
            <v-row class="my-2 d-flex align-items-center" v-for="(options,index) in localQuestion.options" :key="index">
                <DefaultInput label="Alternativa" dense outlined v-model="options.text"></DefaultInput>
                <v-checkbox
                    @change="updateCorrectAnswer(index)"
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
            localQuestion:{}
        }
    },
    methods:{
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        loadData(question){
            // this.localQuestion = Object.assign({}, question);
            this.localQuestion = JSON.parse(JSON.stringify(question));
        },
        loadSelects (){

        },
        resetValidation (){

        },
        confirmModal() {
            let vue = this;
            vue.$emit('onConfirm',vue.localQuestion)
        },
        addOption(){
            this.localQuestion.options.push(
                { text: "", isCorrect: false }
            );
        },
        deleteOption(index){
            this.localQuestion.options.splice(index,1);
        },
        updateCorrectAnswer(index){
            this.localQuestion.correctAnswer = index;
            this.localQuestion.options.map((option,idx)=>{
                option.isCorrect = (idx == index);
            })
        }

    }
}
</script>