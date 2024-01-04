<template>
    <div>
        <DefaultDialog :options="options" :width="width" :showCardActions="false" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <div class="d-flex">
                    <v-row>
                        <v-col cols="4" v-for="modality in modalities" :key="modality.code">
                            <v-hover v-slot="{ hover }">
                                <v-card
                                :elevation="hover ? 16 : 2"
                                    :class="{ 'on-hover': hover }"
                                    class="mx-auto max-height-card py-8"  
                                    @click="confirmModal(modality)"
                                >
                                    <div class="d-flex justify-content-center">
                                        <div 
                                            class="d-flex justify-content-center"
                                            :style="`width: 70px;height: 70px;border-radius: 50%;background-color: ${modality.color}`"
                                        >
                                            <v-icon color="white" large>{{ modality.icon }}</v-icon>
                                        </div>
                                    </div>
                                    <v-card-title class="d-flex justify-content-center">{{ modality.name }}</v-card-title>
                                    <v-card-subtitle>{{ modality.description }}</v-card-subtitle>
                                </v-card>
                            </v-hover>
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
        modalities:[],
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
            // modalities:[
            //     {
            //         key:'asynchronous',
            //         title:'Asincrono',
            //         description:'Curso con contenido pedagógico que no están integrados en el currículo escolar de los niveles formales de la educación',
            //         icon:'mdi-book-education',
            //         icon_color:'#CE98FE',
            //     },
            //     {
            //         key:'in-person',
            //         title:'Presencial',
            //         description:'Curso donde los estudiantes acuden a un aula física donde transcurre la enseñanza y gran parte del aprendizaje.',
            //         icon:'mdi-book-plus',
            //         icon_color:'#5357E0',
            //     },
            //     {
            //         key:'virtual',
            //         title:'Virtual',
            //         description:'Curso con contenido de enseñanza que especifica lo que hay que aprender durante su desarrollo',
            //         icon:'mdi-human-male-board-poll',
            //         icon_color:'#57BFE3',
            //     },
            // ]
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
        async confirmModal(modality_course) {
            let vue = this;
            vue.$emit('onConfirm',modality_course)
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
        max-height: 250px; 
        height: 100%;
        cursor: pointer;
    }
</style>
