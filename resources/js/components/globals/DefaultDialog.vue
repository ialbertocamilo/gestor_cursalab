<template>
    <v-dialog
        class="default-dialog"
        v-model="options.open"
        :width="width"
        scrollable
        :persistent="options.persistent"
        @click:outside="closeModalOutside"
    >
        <v-card>
            <v-card-title class="default-dialog-title" v-show="showTitle">
                {{ options.title }}
                <v-spacer/>
                <v-btn
                    v-show="options.showCloseIcon"
                    icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text :class="{'py-5': !noPaddingCardText}">
                <slot name="content"/>
                <slot name="more-content"/>
            </v-card-text>
            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" v-if="showCardActions">
                <DefaultModalActionButton
                    :cancel-label="options.cancelLabel"
                    :confirm-label="options.confirmLabel"
                    :hide-cancel-btn="options.hideCancelBtn"
                    :hide-confirm-btn="options.hideConfirmBtn"
                    @cancel="closeModal"
                    @confirm="confirmModal"/>
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
            default: '70vw'
        },
        noPaddingCardText: {
            type: Boolean,
            default: false
        },
        showTitle: {
            type: Boolean,
            default: true
        },
        showCloseIcon: {
            type: Boolean,
            default: true
        },
        showCardActions: {
            type: Boolean,
            default: true
        },
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        closeModalOutside() {
            let vue = this
            if (!vue.options.persistent)
                vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        }
    }
}
</script>
