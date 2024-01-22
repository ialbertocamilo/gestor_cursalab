<template>
    <div>
        <DefaultDialog :options="options" :width="width" :showCardActions="false" @onCancel="closeModal"
            @onConfirm="confirmModal">
            <template v-slot:content>
                <div class="d-flex">
                    <v-row>
                        <v-col cols="4">
                            <v-hover v-slot="{ hover }">
                                <v-card :elevation="hover ? 16 : 2" :class="{ 'on-hover': hover }"
                                    class="mx-auto max-height-card py-8" @click="confirmModal(modality_asynchronus)">
                                    <div>
                                        <div class="mr-4 d-flex justify-content-end align-items-center">
                                            <img src="/img/star.png">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="d-flex justify-content-center"
                                            :style="`width: 90px;height: 90px;border-radius: 50%;background-color: ${modality_asynchronus.color}`">
                                            <v-icon color="white" large>{{ modality_asynchronus.icon }}</v-icon>
                                        </div>
                                    </div>
                                    <v-card-title class="d-flex justify-content-center">{{ modality_asynchronus.name
                                    }}</v-card-title>
                                    <v-card-subtitle v-html="modality_asynchronus.description"></v-card-subtitle>
                                </v-card>
                            </v-hover>
                        </v-col>
                        <v-col cols="8" class="p-0">
                            <v-col cols="12" style="height: 50%;">
                                <v-hover v-slot="{ hover }">
                                    <v-card :elevation="hover ? 16 : 2" :class="{ 'on-hover': hover }"
                                        class="mx-auto max-height-card py-4 px-4 d-flex"
                                        @click="confirmModal(modality_virtual)">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="d-flex justify-content-center"
                                                :style="`width: 90px;height: 90px;border-radius: 50%;background-color: ${modality_virtual.color}`">
                                                <v-icon color="white" large>{{ modality_virtual.icon }}</v-icon>
                                            </div>
                                        </div>
                                        <div>
                                            <v-card-title class="d-flex justify-content-between" style="width: 100%;">
                                                {{ modality_virtual.name }}
                                                <div class="ml-1 tag-premium d-flex align-items-center"><img
                                                        src="/img/premiun.svg"> Pro</div>
                                            </v-card-title>
                                            <v-card-subtitle v-html="modality_virtual.description"></v-card-subtitle>
                                        </div>
                                    </v-card>
                                </v-hover>
                            </v-col>
                            <v-col cols="12" style="height: 50%;">
                                <v-hover v-slot="{ hover }">
                                    <v-card :elevation="hover ? 16 : 2" :class="{ 'on-hover': hover }"
                                        class="mx-auto max-height-card py-4 px-4 d-flex"
                                        @click="confirmModal(modality_in_person)">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="d-flex justify-content-center"
                                                :style="`width: 90px;height: 90px;border-radius: 50%;background-color: ${modality_in_person.color}`">
                                                <v-icon color="white" large>{{ modality_in_person.icon }}</v-icon>
                                            </div>
                                        </div>
                                        <div>
                                            <v-card-title class="d-flex justify-content-between" style="width: 100%;">
                                                {{ modality_in_person.name }}
                                                <div class="ml-1 tag-premium d-flex align-items-center"><img
                                                        src="/img/premiun.svg"> Pro</div>
                                            </v-card-title>
                                            <v-card-subtitle v-html="modality_in_person.description"></v-card-subtitle>
                                        </div>
                                    </v-card>
                                </v-hover>
                            </v-col>
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
        modalities: [],
        width: String
    },
    data() {
        return {
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel: 'Entendido',
                persistent: false
            },
            modality_asynchronus: {
                id: 1,
                title: '',
                description: '',
                icon: '',
                icon_color: '',
            },
            modality_in_person: {
                id: 2,
                title: '',
                description: '',
                icon: '',
                icon_color: '',
            },
            modality_virtual: {
                id: 3,
                title: '',
                description: '',
                icon: '',
                icon_color: '',
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
            vue.$emit('onConfirm', modality_course)
        },
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this
            vue.modality_asynchronus = vue.modalities.find(m => m.code == 'asynchronous');
            vue.modality_asynchronus.description = `
                <ul>
                    <li><b>Tu curso clásico de la plataforma.</b></li>
                    <li>Sube de manera asíncrona un curso para tus usuarios.</li>
                    <li>Brinda de manera fácil videos, ppts, scorms, etc a tus colaboradores para que lo vean en cualquier momento.</li>    
                </ul>
            `
            vue.modality_in_person = vue.modalities.find(m => m.code == 'in-person');
            vue.modality_in_person.description = `
            <ul>
                <li>Indica a tus usuario de manera sencilla la ubicación y horario de las sesiones a tus colaboradores.</li>
                <li>Brinda elementos digitales como apoyo a la sesión.</li>
                <li>Toma asistencia en vivo de la sesión.</li>
            </ul>
            `;
            vue.modality_virtual = vue.modalities.find(m => m.code == 'virtual');
            vue.modality_virtual.description = `
                <ul>
                    <li>Ten una sesión con un líder de manera virtual.</li>
                    <li>El líder puede manejar la sesión bajo una cuenta de zoom.</li>
                    <li>Maneja un sistema automatizado de asistencia.</li>
                </ul>
            `
            return 0;
        },
        async loadSelects() {

        },
    }
}
</script>
<style scoped>
.max-height-card {
    height: 100%;
    cursor: pointer;
}

.tag-premium {
    display: inline-flex;
    padding: 2px 12px;
    justify-content: center;
    align-items: center;
    gap: 3px;
    flex-shrink: 0;
    border-radius: 23px;
    border: 1px solid hsl(43, 100%, 50%);
    color: #5458EA;
    font-family: Nunito;
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    line-height: 20px;
    /* 200% */
    letter-spacing: 0.1px;
}
</style>
