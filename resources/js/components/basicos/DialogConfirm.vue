<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModal"
        :class="{}"
    >
        <v-card v-if="options">
            <v-card-title class="default-dialog-title mod_head">
                <span v-html="options.title_modal ? options.title_modal : title"></span>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text class="py-8 text-center pbrmv">
                <div class="bx_content" v-if="options.type_modal == 'status'">
                    <div class="bx_header">
                        <div class="img"><img src="/img/modal_alert.png"></div>
                        <div class="cont">
                            <span v-if="options.status_item_modal">{{ options.content_modal.inactive.title }}</span>
                            <span v-if="!options.status_item_modal">{{ options.content_modal.active.title }}</span>
                        </div>
                    </div>
                    <div class="bx_details">
                        <ul v-if="options.status_item_modal">
                            <li v-for="(item, index) in options.content_modal.inactive.details" :key="index">
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                        <ul v-if="!options.status_item_modal">
                            <li v-for="(item, index) in options.content_modal.active.details" :key="index">
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="bx_content" v-else-if="options.type_modal == 'delete'">
                    <div class="bx_header">
                        <div class="img"><img src="/img/modal_alert.png"></div>
                        <div class="cont">
                            <span>{{ options.content_modal.delete.title }}</span>
                        </div>
                    </div>
                    <div class="bx_details">
                        <ul>
                            <li v-for="(item, index) in options.content_modal.delete.details" :key="index">
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="bx_content" v-else-if="options.type_modal == 'confirm'">
                    <div class="bx_header">
                        <div class="img"><img src="/img/modal_alert.png"></div>
                        <div class="cont">
                            <span>{{ options.content_modal.confirm.title }}</span>
                        </div>
                    </div>
                    <div class="bx_details">
                        <ul>
                            <li v-for="(item, index) in options.content_modal.confirm.details" :key="index">
                                <span>{{ item }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </v-card-text>
            <v-card-actions>
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"
                    cancelLabel="Retroceder"/>
            </v-card-actions>
        </v-card>
        <v-card v-else>
            <v-card-title class="default-dialog-title">
                {{ title }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text class="py-8 text-center">
                <div v-html="subtitle"></div>
            </v-card-text>
            <v-card-actions>
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"/>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width", "title", "subtitle", "txt_btn_confirm", "txt_btn_cancel", "options", "content_modal", "title_modal"],
    data() {
        return {
            dialog: false,
        };
    },
    methods: {
        confirm() {
            let vue = this;
            vue.$emit("onConfirm");
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },

        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        }
    },
};
</script>

<style lang="scss">
.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}
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
.v-card__text.pbrmv {
    padding-bottom: 5px !important;
}
.mod_head.v-card__title.default-dialog-title .v-btn:not(.v-btn--text):not(.v-btn--outlined):focus:before {
    background: none !important;
}
</style>
