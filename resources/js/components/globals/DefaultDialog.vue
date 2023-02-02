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
                <div v-html="options.title"></div>
                <v-spacer/>
                <v-btn
                    v-show="options.showCloseIcon"
                    icon :ripple="false" color="white"
                       @click="closeModalFromIcon">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text :class="{'py-5': !noPaddingCardText}">
                <slot name="content"/>
                <slot name="more-content"/>
            </v-card-text>
            <v-card-actions
                :style="showCardActionsBorder ? { 'border-top': '1px solid rgba(0,0,0,.12)' } : null"
                v-if="showCardActions">
                <DefaultModalActionButton
                    :cancel-label="options.cancelLabel"
                    :confirm-label="options.confirmLabel"
                    :hide-cancel-btn="options.hideCancelBtn"
                    :hide-confirm-btn="options.hideConfirmBtn"
                    :loading="options.loading"
                    @cancel="closeModal"
                    @confirm="confirmModal"/>
            </v-card-actions>
        </v-card>
        <v-icon
            @click="closeModal()"
            small
            color="white"
            class="floating-close-button">
            mdi-close
        </v-icon>
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
        showCardActionsBorder: {
            type: Boolean,
            default: true
        },
        eventCloseModalFromIcon:{
            type:String,
            default:'onCancel'
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        closeModalFromIcon(){
            let vue = this;
            vue.$emit(vue.eventCloseModalFromIcon)
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
<style>
.notificationCenter{
    z-index: 300 !important;
}
</style>

<style scoped>
.floating-close-button {
    position: absolute;
    top: -15px;
    right: -15px;
    background: #5457E7;
    padding: 5px;
    height: 30px;
    width: 30px;
    border-radius: 50%;
    z-index: 999;
}
</style>
