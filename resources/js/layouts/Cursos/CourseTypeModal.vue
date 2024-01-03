<template>
    <div>
        <DefaultDialog :options="options" :width="width" :showCardActions="false" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <div class="d-flex">
                    <v-row>
                        <v-col cols="4" v-for="type_course in types_courses" :key="type_course.key">
                            <v-card  class="max-height-card">
                                <div class="">
                                    <v-icon large>{{ type_course.icon }}</v-icon>
                                </div>
                                <v-card-title class="text-center">{{ type_course.title }}</v-card-title>
                                <v-card-subtitle>{{ type_course.description }}</v-card-subtitle>
                            </v-card>
                        </v-col>
                    </v-row>
                </div>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel:'Entendido',
                persistent: false
            },
            types_courses:[
                {
                    key:'asynchronous',
                    title:'Asincrono',
                    description:'Curso con contenido pedagógico que no están integrados en el currículo escolar de los niveles formales de la educación',
                    icon:'mdi-book-education'
                },
                {
                    key:'in-person',
                    title:'Presencial',
                    description:'Curso donde los estudiantes acuden a un aula física donde transcurre la enseñanza y gran parte del aprendizaje.',
                    icon:'mdi-book-plus'
                },
                {
                    key:'virtual',
                    title:'Virtual',
                    description:'Curso con contenido de enseñanza que especifica lo que hay que aprender durante su desarrollo',
                    icon:'mdi-human-male-board-poll'
                },
            ]
        };
    },

    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
        }
        ,
        async confirmModal() {

        },
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this
            return 0;
        },
        async loadSelects() {
           
        },
    }
}
</script>
<style scoped>
    .max-height-card {
        max-height: 200px; 
        height: 100%;
    }
</style>
