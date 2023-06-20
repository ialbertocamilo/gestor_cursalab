<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModalSelectSpeaker"
        :class="{}"
        content-class="br-dialog"
    >
        <v-card>
            <div class="bx_close_modal_activity">
                <v-btn icon :ripple="false" @click="closeModalSelectSpeaker">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <v-card-text class="py-8">
                <p class="title_act">Elije un speaker</p>
                        <div class="bx_items_speakers">
                            <div class="bx_item_speaker" @click="confirmSelectSpeaker(item)" v-for="(item, i) in data" :key="item.id">
                                <div class="bis_img">
                                    <img src="/img/benefits/sesion_presencial.svg">
                                </div>
                                <div class="bis_content">
                                    <h5>{{item.name}}</h5>
                                    <p>{{item.specialty}}</p>
                                </div>
                            </div>
                        </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width", "title", "subtitle", "txt_btn_confirm", "txt_btn_cancel", "options", "content_modal", "title_modal","data"],
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

        closeModalSelectSpeaker() {
            let vue = this
            vue.$emit('closeModalSelectSpeaker')
        },
        confirmSelectSpeaker(value) {
            let vue = this
            vue.$emit('confirmSelectSpeaker', value)
        }
    },
};
</script>

<style lang="scss">
.br-dialog, .br-dialog .v-sheet.v-card{
    border-radius: 16px !important;
}
.bx_close_modal_activity {
    position: absolute;
    right: 10px;
    top: 10px;
}
.title_act {
    color: #2A3649;
    font-size: 19px;
    line-height: 21px;
    font-family: "Nunito", sans-serif;
    font-weight: bold;
    margin: 14px 0 25px;
}
.bx_items_speakers {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    overflow-y: auto;
    min-height: 100px;
    max-height: 300px;
    align-items: start;
    .bx_item_speaker {
        display: flex;
        align-items: center;
        border: 1px solid #D9D9D9;
        border-radius: 4px;
        padding: 20px 30px;
        cursor: pointer;
        .bis_img {
            width: 70px;
        }
        .bis_content {
            margin-left: 10px;
            h5, p {
                color: #1A2128;
                font-family: "Nunito", sans-serif;
                font-size: 14px;
                margin: 0;
            }
            h5 {
                font-size: 15px;
                font-weight: bold;
            }
        }
    }
}
</style>
