<template>
    <div>
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
                <v-form ref="TemaMultimediaTextForm" @submit.prevent="null">
                    <v-row>
                        <v-col cols="5">
                            <div>
                                <span class="rounded-border-primary">1</span>
                                <span class="ml-1 color-default-primary">Indicaciones</span>
                            </div>
                            <v-divider class="border-color-divider"></v-divider>
                            <ul>
                                <li>Crea la preguntas para tu examen con Inteligencia Artificial.</li>
                                <li>Puedes elegir el tipo de preguntas que deseas que contenga tu examen.</li>
                                <li>Al dar clic en el botón <b>Generar evaluación</b> se crearán las preguntas según la configuración indicada.</li>
                                <li>Podrás editar, eliminar y seleccionar la preguntas obligatorias de tu evaluación y luego dar clic en el botón <b>Guadar evaluación</b>.</li>
                            </ul>
                            <v-divider class="border-color-divider"></v-divider>
                            <div>
                                <span class="rounded-border-primary">2</span> 
                                <span class="ml-1 color-default-primary">Configuración</span>
                            </div>
                            <v-divider class="border-color-divider"></v-divider>
                            <div class="d-flex justify-space-between align-items-center">
                                <span>Dificultad</span>
                                <v-chip-group
                                    mandatory
                                    active-class="primary--text"
                                    v-model="configuration.level"
                                >
                                    <v-chip
                                        v-for="level in levels"
                                        :key="level"
                                        :style="configuration.level != level ? 'color:gray' : '' "
                                    >
                                        {{ level }}
                                    </v-chip>
                                </v-chip-group>
                            </div>
                            <div class="d-flex justify-space-between align-items-center">
                                <span>Generar el tipo de pregunta</span>
                                <div class="pt-4">
                                    <v-switch
                                        v-model="generate_quantity"
                                        @change="changeConfiguration"
                                        inset
                                    ></v-switch>
                                </div>
                            </div>
                            <div>
                                <v-row>
                                    <v-col cols="8" class="pb-0 pl-10">Tipos de preguntas</v-col>
                                    <v-col cols="4" class="pb-0">Cantidad</v-col>
                                </v-row>
                                <v-divider class="border-color-divider"></v-divider>
                                <v-row v-for="(question,index) in configuration.type_questions" :key="index">
                                    <v-col cols="8" class="d-flex py-0">
                                        <v-checkbox
                                            v-model="question.checked"
                                            :label="question.name"
                                        ></v-checkbox>
                                        <!-- <span v-text="question.name"></span> -->
                                    </v-col>
                                    <v-col cols="4" class="py-0">
                                        <DefaultInput dense outlined v-model="question.quantity" :disabled="!question.checked"></DefaultInput>
                                    </v-col>
                                </v-row>
                                <v-divider class="border-color-divider my-0"></v-divider>
                                <v-row>
                                    <v-col cols="8" class="pt-0 pl-10">Total</v-col>
                                    <v-col cols="4" class="pt-0">{{ configuration.type_questions.filter(tq => tq.checked).reduce((sum, question) => parseInt(sum) + (parseInt(question.quantity) || 0 ), 0) }}</v-col>
                                </v-row>
                            </div>
                        </v-col>
                        <v-col cols="7">
                            <div>
                                <span class="rounded-border-primary">3</span>
                                <span class="ml-1 color-default-primary">Resultado</span>
                            </div>
                            <div class="container-questions py-1 my-4">
                                <!-- <transition-group name="fade" mode="out-in"> -->
                                <div class="m-3 px-5 py-2 card-question typing-animation" v-for="(question,index) in questions" :key="index">
                                    <span v-text="question.question" class="mb-1"></span>
                                    <span class="ml-3" v-for="(option,index) in question.options" :key="index" v-text="option.text" :style="option.isCorrect ? 'background:#ddddfa' :''"></span>
                                    <div class="d-flex justify-end">
                                        <v-btn color="primary" icon @click="openEditQuestionModal(question,index)">
                                            <v-icon 
                                                small 
                                            >
                                                 mdi mdi-pencil
                                            </v-icon> 
                                        </v-btn>
                                        <!-- <v-btn color="primary" icon><v-icon small> mdi-rotate-45 mdi-pin</v-icon> </v-btn> -->
                                        <v-btn color="primary" @click="openDeleteModal(index)" icon><v-icon small> far fa-trash-alt</v-icon> </v-btn>
                                    </div>
                                </div>
                                <!-- </transition-group> -->
                            </div>
                        </v-col>
                    </v-row>
                </v-form>
            </template>
            <template v-slot:card-actions>
                <v-row>
                    <v-col cols="5" class="d-flex justify-space-between align-items-center">
                        <DefaultButton @click="resetValues()" text label="Resetear" v-if="questions.length == 0" />
                        <v-btn
                            class="mx-1"
                            elevation="0"
                            color="primary"
                            outlined
                            @click="generateQuestions()"
                            v-if="questions.length == 0"
                        >
                            Generar evaluación con AI
                            <img width="22px" 
                                class="ml-2" 
                                style="filter: hue-rotate(360deg);"
                                src="/img/ia_convert.svg"
                            >
                        </v-btn>
    
                    </v-col>
                    <v-col cols="7" class="d-flex justify-end">
                        <DefaultButton class="mr-12" v-if="questions.length > 0"  @click="saveQuestions()" label="Guardar evaluación" />
                    </v-col>
                </v-row>
            </template>
        </DefaultDialog>
        <EditAIQuestionsModal
            :ref="modalQuestionOptions.ref"
            :options="modalQuestionOptions"
            @onConfirm="confirmQuestionModal "
            @onCancel="closeFormModal(modalQuestionOptions)"
        />
        <!-- <DefaultDeleteModal 
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        /> -->
        <DefaultAlertDialog 
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onCancel="closeFormModal(modalDeleteOptions)"
            @onConfirm="closeFormModal(modalDeleteOptions),deleteQuestion()"
        >
            <template v-slot:content>
                <p>Una vez eliminada la pregunta no podrá recuperarse.</p>
                <p>
                    <strong>¿Está seguro de eliminar esta pregunta?</strong>
                </p>
            </template>
        </DefaultAlertDialog>
    </div>
</template>
<script>
import EditAIQuestionsModal from './EditAIQuestionsModal';

export default {
    components:{EditAIQuestionsModal},
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            jarvisBaseUrl:'',
            levels:['Alta','Media','Baja'],
            generate_quantity:false,
            configuration:{
                level:1,
                type_questions: [
                    { checked: true, type: 'single-choice', name: 'Selección única', quantity: 5 },
                    { checked: true, type: 'true_or_false', name: 'Verdadero o Falso', quantity: 5 },
                    { checked: true, type: 'fill-in-the-blank', name: 'Completar el espacio en blanco', quantity: 5 },
                    { checked: true, type: 'analogies', name: 'Analogías', quantity: 5 },
                ],
            },
            questions:[],

            modalQuestionOptions: {
                ref: 'EditModalQuestion',
                title_modal:'Edición de preguntas',
                width:'45vw',
                open: false,
                base_endpoint: `question`,
                resource: 'Question',
                confirmLabel: 'Guardar',
                showCloseIcon: true,
            },
            modalDeleteOptions: {
                open: false,
                title: 'Eliminación de pregunta</b>',
                type_modal: 'delete',
                media_index: null,
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar una pregunta!',
                        details: [
                            'Esta pregunta se eliminará y no podra recuperarse.'
                        ],
                    }
                },
                index_question:null,
            }
        }
    },
    mounted(){
        this.jarvisBaseUrl = this.getJarvisUrl();
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('close')
        },
        loadData(){

        },
        loadSelects (){

        },
        resetValidation (){

        },
        async generateQuestions(){
            let vue = this;
            const url = `${vue.jarvisBaseUrl}/generate-questions`;
            vue.showLoader();
            const data = {
                topic_id: vue.options.topic_id,
                workspace_id: vue.options.workspace_id,
                configuration: vue.configuration
            }
            await vue.$http.post(url, data).then(async ({data}) => {
                vue.hideLoader();
                // 
                const questionsTemplate = data.message
                this.addItemWithTimeout(questionsTemplate,0);
            }).catch(async (error) => {
                vue.hideLoader();
            })
            
        },
        async saveQuestions(){
            let vue = this;
            vue.showLoader();
            const url = `${vue.options.base_endpoint}/store-ai-question`;
            await vue.$http.post(url, vue.questions).then(async ({data}) => {
                vue.hideLoader();
                vue.$emit('onConfirm');
            }).catch(async (error) => {
                vue.hideLoader();
            })
        },
        addItemWithTimeout(new_questions,index) {
            this.questions.push(new_questions[index]);
            if (this.questions.length != new_questions.length && new_questions[index+1]) {
                setTimeout(() => {
                    this.addItemWithTimeout(new_questions,index+1);
                }, 1000);
            }else{
                setTimeout(() => {
                    const card_questions = document.querySelectorAll(".typing-animation");
                    card_questions.forEach(function(card) {
                        card.classList.remove("typing-animation");
                    });
                }, 1100);
            }
        },
        confirmModal() {
            let vue = this
            
        },
        changeConfiguration(value){
            let vue = this;
            let quantities = [5,5,5,5];
            if(value){
                quantities = vue.generateRandomQuantities();
            }
            vue.changeQuantities(quantities);
        },
        changeQuantities(quantities){
            let vue = this;
            vue.configuration.type_questions = this.configuration.type_questions.map((question, index) => {
                return {
                ...question, 
                checked: true, 
                quantity: quantities[index],
                };
            });
        },
        resetValues(){
            let vue = this;
            vue.changeQuantities([5,5,5,5]);
            vue.configuration.level = 1;
            vue.generate_quantity = 0;
        },
        generateRandomQuantities() {
            let numbers = [];
            for (let i = 0; i < 3; i++) {
                let number = Math.floor(Math.random() * 10) + 1; 
                numbers.push(number);
            }
            let numberLess = 20 - numbers.reduce((sum, num) => sum + num, 0);
            numberLess = Math.min(Math.max(numberLess, 1), 6);
            numbers.push(numberLess);
            return numbers;
        },
        openEditQuestionModal(question,index){
            let vue = this;
            question.index = index;
            vue.openFormModal(vue.modalQuestionOptions, question,null, 'Edición de preguntas');
        },
        openDeleteModal(index_question){
            let vue = this;
            vue.modalDeleteOptions.index_question = index_question;
            console.log('delete open',index_question);
            vue.openSimpleModal(vue.modalDeleteOptions);
        },
        deleteQuestion(){
            let vue = this;
            vue.questions.splice(vue.modalDeleteOptions.index_question,1);
        },
        confirmQuestionModal(question){
            let vue = this;
            vue.questions[question.index].question=question.question;
            vue.questions[question.index].options=question.options;
            vue.questions[question.index].correctAnswer=question.correctAnswer;
            vue.closeFormModal(vue.modalQuestionOptions)
        }
    }
}
</script>
<style lang="scss">
@import "resources/sass/variables";

.rounded-border-primary{
    width: 24px;
    height: 24px;
    border-radius: 50%;
    border: 2px solid $primary-default-color;
    color: $primary-default-color;
    text-align: center;
    font-weight: bolder;
}
.border-color-divider{
    border-color: $primary-default-color !important;
}
.container-questions{
    background-color: #eaeaea;
    border-radius: 8px;
    max-height: 100vh;
    overflow: auto;
}
.card-question{
    background-color: white;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    transition: all 500ms ease-in;
}

.typing-animation span {
  display: inline-block;
  overflow: hidden;
  white-space: nowrap;
//   border-right: 0.15em solid black; /* Línea de tipeo */
  animation: typing 3s steps(40, end), blink-caret 0.75s step-end infinite;
}

@keyframes typing {
  from {
    width: 0;
  }
  to {
    width: 100%;
  }
}

@keyframes blink-caret {
  from, to {
    border-color: transparent;
  }
  50% {
    border-color: orange;
  }
}

</style>