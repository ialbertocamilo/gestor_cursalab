<template>
    <v-dialog
        class="default-dialog"
        v-model="options.open"
        :width="width"
        :persistent="options.persistent"
        scrollable
        @click:outside="closeModalOutside"
        :content-class="contentClass"
        :fullscreen="fullscreen"
    >
        <v-card :height="height">
            <div v-if="customTitle">
                <slot name="card-title"/>
            </div>
            <div v-else>
                <v-card-title class="default-dialog-title mod_head" v-if="options.title_modal">
                    <span v-html="options.title_modal ? options.title_modal : options.title"></span>
                    <v-btn icon :ripple="false" color="white"
                           @click="closeModal" v-show="showCloseIcon">
                        <v-icon v-text="'mdi-close'"/>
                    </v-btn>
                </v-card-title>
                <v-card-title :class="headerClass" v-show="showTitle" v-else>
                    <div v-html="options.title"></div>
                    <slot name="title-icon"/>
                    <v-spacer/>
                    <v-btn
                        v-show="showCloseIcon"
                        icon :ripple="false" :color="colorCloseIcon"
                           @click="closeModalFromIcon">
                        <v-icon v-text="'mdi-close'"/>
                    </v-btn>
                </v-card-title>
            </div>
            <v-card-text :class="[{'py-3': !noPaddingCardText}, vCardClass]">
                <div class="bx_content" v-if="options.type_modal == 'requirement'">
                    <div class="bx_header">
                        <div class="img"><img src="/img/modal_alert.png"></div>
                        <div class="cont">
                            <span>{{ options.content_modal.requirement.title }}</span>
                        </div>
                    </div>
                    <div class="bx_details">
                        <slot name="content"/>
                        <slot name="more-content"/>
                    </div>
                </div>
                <div v-else>
                    <slot name="content"/>
                    <slot name="more-content"/>
                </div>
            </v-card-text>
            <slot name="card-actions"/>
            <v-card-actions
                :style="showCardActionsBorder ? { 'border-top': '1px solid rgba(0,0,0,.12)' } : null"
                v-if="showCardActions">
                <DefaultModalActionButton
                    :cancel-label="options.cancelLabel"
                    :confirm-label="options.confirmLabel"
                    :hide-cancel-btn="options.hideCancelBtn"
                    :hide-confirm-btn="options.hideConfirmBtn"
                    :loading="options.loading"
                    :confirm-disabled="options.confirmDisabled"
                    @cancel="steps ? closeModalSteps() : closeModal()"
                    @confirm="confirmModal"/>
            </v-card-actions>
        </v-card>
        <v-icon
            v-if="options.showFloatingCloseButton"
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
        height: {
            default: ''
            // default: '85vh'
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
        },
        headerClass: {
            type: String,
            default:'default-dialog-title'
        },
        styleTitle:{
            type: String,
            default:''
        },
        colorCloseIcon:{
            type: String,
            default: 'white'
        },
        customTitle:{
            type: Boolean,
            default: false
        },
        vCardClass:{
            type: String,
            default: ''
        },
        steps:{
            type: Boolean,
            default: false
        },
        contentClass: {
            type: String,
            default: ''
        },
        fullscreen:{
            type: Boolean,
            default: false
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        closeModalSteps() {
            let vue = this
            vue.$emit('onCancelSteps')
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
        },
        onScroll() {
            console.log('scrolled')
        }
    }
}
</script>
<style>
.notificationCenter{
    z-index: 300 !important;
}

.v-dialog {
    position: fixed;
    overflow-y: unset;
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
    box-shadow: 0 2px 6px 0px rgba(0,0,0,0.75);
    z-index: 999;
}
</style>

<style lang="scss">
.bx_header {
    display: flex;
    align-items: center;
}
.bx_header .cont span {
    color: #2A3649;
    font-size: 20px;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    margin-left: 29px;
    text-align: left;
    line-height: 25px;
}
.bx_details {
    border-top: 1px solid #D9D9D9;
    padding-top: 15px;
    margin-top: 20px;
    padding-left: 20px;
    padding-right: 20px;
}
.bx_details ul{
    margin-bottom: 0;
}
.bx_details ul li,
.bx_details ul li a {
    text-align: left;
    font-family: "Nunito", sans-serif;
    font-size: 16px;
    font-weight: 400;
    line-height: 20px;
    color: #2A3649;
    position: relative;
    list-style: none;
    margin-bottom: 4px;
}
.bx_details ul li a {
    color: #5558EA;
}
.bx_details ul li:before {
    content: '';
    position: absolute;
    height: 5px;
    width: 5px;
    background: black;
    left: -17px;
    top: 8px;
    border-radius: 50%;
}
.mod_head.v-card__title.default-dialog-title {
    display: flex;
    justify-content: center;
    position: relative;
    padding-right: 60px !important;
}
.mod_head.v-card__title.default-dialog-title > button {
    position: absolute;
    right: 20px;
}
.mod_head.v-card__title.default-dialog-title > span {
    text-align: center;
    font-family: "Nunito", sans-serif;
    font-size: 18px;
    line-height: 20px;
    letter-spacing: 0.1px;
    font-weight: 400;
}
.mod_head.v-card__title.default-dialog-title > span b {
    font-weight: 700;
}
</style>
