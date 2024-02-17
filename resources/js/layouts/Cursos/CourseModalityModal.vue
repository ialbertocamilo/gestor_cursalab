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
                                    <v-card-title class="d-flex justify-content-center mb-3">{{ modality_asynchronus.name
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
                                            <v-card-title class="d-flex justify-content-between mb-3" style="width: 100%;">
                                                {{ modality_virtual.name}}
                                                <div class="ml-1 tag-premium d-flex align-items-center" v-if="!modality_virtual.has_functionality">
                                                    <img src="/img/premiun.svg"> 
                                                    Pro
                                                </div>
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
                                            <v-card-title class="d-flex justify-content-between mb-3" style="width: 100%;">
                                                {{ modality_in_person.name }}
                                                <div class="ml-1 tag-premium d-flex align-items-center" v-if="!modality_in_person.has_functionality">
                                                    <img src="/img/premiun.svg"> 
                                                    Premiun
                                                </div>
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
        <ModalUpgrade
            :options="ModalUpgradeOptions"
            width="55vw"
            :model_id="null"
            :ref="ModalUpgradeOptions.ref"
            @onCancel="closeSimpleModal(ModalUpgradeOptions)"
            @onConfirm="closeFormModal(ModalUpgradeOptions),openFormModal(modalGeneralStorageEmailSendOptions, null, 'status', 'Solicitud enviada')"
        />
        <GeneralStorageEmailSendModal
            :ref="modalGeneralStorageEmailSendOptions.ref"
            :options="modalGeneralStorageEmailSendOptions"
            width="35vw"
            @onCancel="closeFormModal(modalGeneralStorageEmailSendOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageEmailSendOptions)"
        />
    </div>
</template>

<script>
import ModalUpgrade from '../ModalUpgrade';
import GeneralStorageEmailSendModal from '../General/GeneralStorageEmailSendModal';
const img_rocket = '<img width="20px" class="mx-1" src="/img/rocket.svg">';

export default {
    components:{ModalUpgrade,GeneralStorageEmailSendModal},
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
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel:'Entendido',
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
            ModalUpgradeOptions:{
                ref: 'ModalUpgradeModal',
                open: false,
                base_endpoint: '/upgrade',
                confirmLabel: 'Solicítalo hoy',
                resource: 'Upgrade',
                width:'70vw',
                title_modal: `${img_rocket}Accede a más soluciones${img_rocket}`,
                action: null
            },
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
            if(!modality_course.has_functionality){
                return vue.openFormModal(vue.ModalUpgradeOptions);
            }
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
