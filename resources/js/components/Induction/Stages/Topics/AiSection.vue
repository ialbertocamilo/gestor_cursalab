<template>
    <div>
        <DefaultToggle activeLabel="¿Deseas generar evaluaciones automáticas con este contenido?" inactiveLabel="¿Deseas generar evaluaciones automáticas con este contenido?"
            @onChange="changeIaConvertValue" :disabled="limits.media_ia_converted >= limits.limit_allowed_media_convert" :value="false" />
        <!-- <div class="mt-4 d-flex align-items-center">
            <v-btn class="py-1" color="primary" outlined text
                style="border-radius: 15px;border-color: #5458ea;height: auto;">
                <span>{{limits.media_ia_converted}}/{{limits.limit_allowed_media_convert }}</span>
                <img width="22px" class="ml-2" src="/img/ia_convert.svg">
            </v-btn>
            <span class="ml-2">
                Para alcanzar la cantidad máxima de opciones para la transcripción de contenido multimedia a AI
            </span>
        </div> -->
        <div class="mt-4 d-flex align-items-center" v-if="limits.media_ia_converted >= limits.limit_allowed_media_convert">
            <span>
                Quieres seguir disfrutando de los beneficios que te brinda la AI
            </span>
            <v-btn class="ml-2" color="primary" outlined text style="border-color: #5458ea;" @click="openFormModal(ModalUpgradeOptions)">
                <img width="22px" src="/img/premiun.svg">
                <span class="text-bolder" >Solicítalo hoy</span>
            </v-btn>
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
    </div>
</template>

<script>
const img_rocket = '<img width="20px" class="mx-1" src="/img/rocket.svg">';
import ModalUpgrade from '../../../../layouts/ModalUpgrade';
import GeneralStorageEmailSendModal from '../../../../layouts/General/GeneralStorageEmailSendModal';
export default {
    components:{ModalUpgrade,GeneralStorageEmailSendModal},
    props:{
        limits:{
            type:Object,
            default: function () {
                return {
                    limit_allowed_media_convert:0,
                    limit_allowed_ia_evaluations:0,
                    media_ia_converted:0
                };
            }
        }
    },
    data(){
        return {
            ia_convert:null,
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
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel:'Entendido',
                persistent: false
            },
        }
    },
    methods: {
        changeIaConvertValue(value){
           this.$emit('onChange',value)
        }
    }
}
</script>
