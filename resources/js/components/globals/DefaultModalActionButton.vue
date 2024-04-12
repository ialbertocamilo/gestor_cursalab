<template>
    <v-row justify="center" class="mx-0">
        <v-col cols="12" class="d-flex justify-content-center">
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
                :disabled="loading || confirmDisabled"
            >
                {{ confirmLabel }}
            </v-btn>
        </v-col>

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
        confirmDisabled: {
            type: Boolean,
            default: false
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
</style>
