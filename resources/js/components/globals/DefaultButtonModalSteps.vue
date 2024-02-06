<template>
    <v-row justify="center" class="mx-0 position-relative">
        <v-col cols="4" class="d-flex justify-content-around">
            <v-btn
                class="default-modal-action-button  mx-1 btn_back"
                text
                elevation="0"
                :ripple="false"
                color="primary"
                @click="onCancel"
                v-if="!hideCancelBtn"
                :disabled="loading"
            >
                {{ cancelLabel }}
            </v-btn>
            <v-btn
                class="default-modal-action-button mx-1"
                elevation="0"
                :ripple="false"
                color="primary"
                @click="onConfirm"
                v-if="!hideConfirmBtn"
                :disabled="loading || disabled_next"
            >
                {{ confirmLabel }}
            </v-btn>
        </v-col>
        <div class="bx_btn_extra" v-if="showBtnExtra">
            <v-btn
                class="default-modal-action-button mx-1"
                elevation="0"
                :ripple="false"
                outlined
                color="primary"
                @click="onConfirmExtra"
                :disabled="loading || disabled_btn_extra"
            >
                {{ labelBtnExtra }}
            </v-btn>
        </div>
    </v-row>

</template>


<script>
export default {
    props: {
        cancelLabel: {
            type: String,
            default: 'Cancelar'
        },
        confirmLabel: {
            type: String,
            default: 'Confirmar'
        },
        hideCancelBtn: {
            type: Boolean,
            default: false
        },
        hideConfirmBtn: {
            type: Boolean,
            default: false
        },
        loading: {
            type: Boolean,
            default: false
        },
        disabled_next: {
            type: Boolean,
            default: false
        },
        showBtnExtra: {
            type: Boolean,
            default: false
        },
        disabled_btn_extra: {
            type: Boolean,
            default: false
        },
        labelBtnExtra: {
            type: String,
            default: ''
        },
    },
    methods: {
        onCancel() {
            let vue = this;
            vue.$emit('cancel')
        },
        onConfirm() {
            let vue = this;
            vue.$emit('confirm')
        },
        onConfirmExtra(){
            let vue = this;
            vue.$emit('confirmBtnExtra')
        }
    }
}
</script>
<style lang="scss">
button.default-modal-action-button span {
    font-family: "Nunito", sans-serif;
    font-weight: 400;
    font-size: 13px;
}
button.default-modal-action-button.btn_back span {
    color: #6C757D;
}
.bx_btn_extra{
    position: absolute;
    right: 12px;
    top: 12px;
}
</style>
