<template>
    <v-dialog
        class="default-dialog"
        v-model="options.open"
        :width="width"
        scrollable
        @click:outside="closeModal"
        content-class="br-dialog"
    >
        <v-card>
            <v-card-title class="default-dialog-title">
                <span v-html="options.title"></span>
                <v-btn v-if="showCloseButton" icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text class="py-8 text-center pb-0">
                <slot name="content"/>
            </v-card-text>
            <v-card-actions>
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"
                    :cancelLabel="cancelLabel"
                    :confirmLabel="confirmLabel"
                    :hideCancelBtn="hideCancelBtn"
                    :hideConfirmBtn="hideConfirmBtn"
                />
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            default: '40vw'
        },
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
        showCloseButton:{
            type: Boolean,
            default: true
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        }
    }
}
</script>
<style lang="scss">
.v-card__title.default-dialog-title {
    display: flex;
    justify-content: center;
    position: relative;
    padding-right: 60px !important;
}
.v-card__title.default-dialog-title > button {
    position: absolute;
    right: 20px;
}
.v-card__title.default-dialog-title > span {
    text-align: center;
    font-family: "Nunito", sans-serif;
    font-size: 18px;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-weight: 400;
}
.v-card__title.default-dialog-title > span b {
    font-weight: 700;
}
.br-dialog, .br-dialog .v-sheet.v-card{
    border-radius: 16px !important;
}
</style>
