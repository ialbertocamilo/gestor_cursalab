<template>
    <v-dialog
        class="default-dialog"
        v-model="options.open"
        :width="width"
        scrollable
        @click:outside="closeModal"
    >
        <v-card>
            <v-card-title class="default-dialog-title">
                {{ options.title }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text class="py-8 text-center">
                <slot name="content"/>
            </v-card-text>
            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
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
