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
    >
        <v-card class="card_modal_select_template">
            <div class="bx_close_modal_activity">
                <v-btn icon :ripple="false" @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <v-card-text class="py-8 text-center">
                <p class="title_act">Selecciona tu proceso de inducción</p>
                <div class="bx_items_activitys">
                    <div class="bx_item_activity locked" @click="selectTemplateOrNewProcessModal('template')">
                        <div class="bx_tag">
                            <v-icon v-text="'mdi-hammer-wrench'"/>
                            <span class="text_default">En desarrollo</span>
                        </div>
                        <div class="img"><img src="/img/induccion/plantilla_locked.svg"></div>
                        <h5>Con plantilla</h5>
                        <p>Podrás seleccionar un modelo prediseñado de tu proceso de inducción.</p>
                    </div>
                    <div class="bx_item_activity" @click="selectTemplateOrNewProcessModal('new')">
                        <div class="img"><img src="/img/induccion/nuevo.svg"></div>
                        <h5>Nuevo proceso</h5>
                        <p>Podrás crear una proceso de inducción desde el inicio.</p>
                    </div>
                </div>
            </v-card-text>
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
        selectTemplateOrNewProcessModal(value) {
            let vue = this
            vue.$emit('selectTemplateOrNewProcessModal', value)
        }
    },
};
</script>

<style lang="scss">
.card_modal_select_template {
    .bx_close_modal_activity {
        position: absolute;
        right: 10px;
        top: 10px;
    }
    .bx_items_activitys {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin: 40px 0 20px;
    }
    .bx_item_activity {
        padding: 48px 26px 30px;
        border-radius: 10px;
        width: 218px;
        margin: 5px;
        cursor: pointer;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
        position: relative;
        .bx_tag {
            border: 1px solid #5458EA;
            border-radius: 27px;
            position: absolute;
            top: 8px;
            right: 10px;
            padding: 3px 10px;
            i, span {
                font-size: 11px !important;
                color: #5458EA !important;
            }
        }
        h5 {
            color: #2A3649;
            font-size: 16px;
            line-height: 20px;
            font-family: "Nunito", sans-serif;
            font-weight: bold;
            margin: 14px 0;
            min-height: 40px;
            display: inline-flex;
            align-items: center;
        }
        p {
            color: #2A3649;
            font-size: 13px;
            line-height: 17px;
            font-family: "Nunito", sans-serif;
        }
        &:hover {
            box-shadow: 0px 4px 15px rgba(194,194,194,1);
        }
        &.locked {
            h5, p {
                color: #C4C4C4;
            }
        }
    }
    .title_act {
        color: #2A3649;
        font-size: 19px;
        line-height: 21px;
        font-family: "Nunito", sans-serif;
        font-weight: bold;
        margin: 14px 0 25px;
    }
}
</style>
