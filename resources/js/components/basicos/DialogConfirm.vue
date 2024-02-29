<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModal"
        :class="{}"
        content-class="br-dialog"
        :overlay-opacity="overlay_opacity"
    >
        <v-card v-if="options" :class="[(options.type_modal == 'upload') ? 'bx_alert_upload' : '', (options.type_modal == 'upload' && options.content_modal.upload && options.content_modal.upload.status == 'success') ? 'border_success' : 'border_error']">
            <v-card-title class="default-dialog-title mod_head" v-if="!options.hide_header">
                <span v-html="options.title_modal ? options.title_modal : title"></span>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text class="text-center pbrmv" :class="[(options.type_modal == 'upload') ? '' : 'py-8']">
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
                <div class="bx_content dialog_upload" v-else-if="options.type_modal == 'upload'">
                    <div class="error_upload" v-if="options.content_modal.upload.status == 'error'">
                        <div class="icon_upload_error">
                            <img src="/img/upload_error.png">
                        </div>
                        <div class="text_error_upload">El archivo no se ha podido cargar correctamente.</div>
                        <span class="label_error_upload"
                            v-if="options.content_modal.upload.error_text"
                            v-html="options.content_modal.upload.error_text">
                        </span>
                    </div>
                    <div class="success_upload" v-else-if="options.content_modal.upload.status == 'success'">
                        <div class="icon_upload_success">
                            <img src="/img/upload_success.png">
                        </div>
                        <div class="text_success_upload">El archivo se ha cargado correctamente.</div>
                        <span class="label_success_upload"
                            v-if="options.content_modal.upload.success_text"
                            v-html="options.content_modal.upload.success_text">
                        </span>
                    </div>
                </div>
            </v-card-text>
            <v-card-actions v-if="!options.hide_btns">
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"
                    cancelLabel="Cancelar"/>
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
                    :cancelLabel="txt_btn_cancel"
                    :confirmLabel="txt_btn_confirm"
                    @cancel="closeModal"
                    @confirm="confirmModal"/>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width", "title", "subtitle", "txt_btn_confirm", "txt_btn_cancel", "options", "content_modal", "title_modal","overlay_opacity"],
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
.br-dialog, .br-dialog .v-sheet.v-card{
    border-radius: 16px !important;
}

.dialog_upload .success_upload,
.dialog_upload .error_upload {
    opacity: 1;
    background: none;
    color: red;
    text-align: center;
    height: 100%;
    top: 0;
    display: flex !important;
    justify-content: center;
    align-items: center;
    flex-direction: column;
}
.dialog_upload .success_upload .icon_upload_success,
.dialog_upload .error_upload .icon_upload_error {
    margin-top: 10px;
}
.dialog_upload .success_upload .text_success_upload,
.dialog_upload .error_upload .text_error_upload {
    font-family: "Nunito", sans-serif;
    margin-top: 25px;
    display: flex;
    font-size: 16px;
    color: #FF4560;
    line-height: 20px;
    max-width: 80%;
}
.dialog_upload .success_upload,
.dialog_upload .success_upload .text_success_upload{
    color: #4CAF50;
}
.dialog_upload .success_upload span.label_success_upload,
.dialog_upload .error_upload span.label_error_upload {
    font-family: "Nunito", sans-serif;
    margin-top: 6px;
    display: flex;
    font-size: 12px;
    color: #A9B2B9;
    line-height: 20px;
    font-weight: 400;
    min-height: 42px;
    max-width: 80%;
}
.bx_content.dialog_upload {
    padding: 20px 0;
}
.bx_alert_upload {
    border-radius: 8px !important;
    border: 1px solid #fff;
}
.bx_alert_upload .border_success {
    border-color: #4CAF50;
}
.bx_alert_upload .border_error {
    border-color: #FF4560;
}
</style>
