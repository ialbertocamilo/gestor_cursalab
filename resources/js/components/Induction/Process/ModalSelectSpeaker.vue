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
        <v-card class="card_modal_select_speaker">
            <p class="title_act">Elige un facilitador(a)</p>
            <div class="bx_close_modal">
                <v-btn icon :ripple="false" @click="closeModalSelectSpeaker">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <div class="bx_text_search">
                <v-text-field
                    outlined
                    dense
                    hide-details="auto"
                    label="Nombre"
                    placeholder="Nombre"
                    v-model="txt_filter_speaker"
                    autocomplete="off"
                    clearable
                    append-icon="mdi-magnify"
                >
                </v-text-field>
            </div>
            <v-card-text class="pb-8 pt-4">
                <div class="bx_items_speakers" v-if="list_filter_speakers != null && list_filter_speakers.length > 0">
                    <div class="bx_item_speaker" @click="confirmSelectSpeaker(item)" v-for="(item, i) in list_filter_speakers" :key="item.id">
                        <div class="bis_img">
                            <img :src="item.image">
                        </div>
                        <div class="bis_content">
                            <h5>{{item.name}}</h5>
                            <p>{{item.specialty}}</p>
                        </div>
                    </div>
                </div>
                <div v-else>
                    <p class="txt_nf pt-4 text-center" v-if="txt_filter_speaker != null">No se encontraron facilitadores</p>
                </div>
            </v-card-text>
            <div v-if="show_button">
                <v-col class="py-0">
                    <DefaultModalActionButton
                        @cancel="closeModalSelectSpeaker"
                        @confirm="saveSelectSpeaker"
                    />
                </v-col>
            </div>
            <div class="row_new_speaker">
                <span @click="newSpeaker">Crear nuevo facilitador</span>
            </div>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    // props: ["value", "width", "title", "subtitle", "txt_btn_confirm", "txt_btn_cancel", "options", "content_modal", "title_modal","data","show_button"],
    props: {
        value: Boolean,
        width: String,
        data: [Object, Array],
        show_button: {
            type: Boolean,
            default: false
        },
    },
    data() {
        return {
            dialog: false,
            txt_filter_speaker: null,
        };
    },
    computed: {
        list_filter_speakers() {
            let vue = this;
            if (vue.txt_filter_speaker === null) {
                return vue.$props.data;
            }
            return vue.$props.data.filter((speaker) => {
                if(speaker.name != '' && speaker.name != null && speaker.email != '' && speaker.email != null)
                return (
                    speaker.name.toLowerCase().includes(vue.txt_filter_speaker.toLowerCase()) ||
                    speaker.email.toLowerCase().includes(vue.txt_filter_speaker.toLowerCase())
                );
            });
        }
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
        },
        saveSelectSpeaker(value) {
            let vue = this
            vue.$emit('saveSelectSpeaker', value)
        },
        newSpeaker(value) {
            let vue = this
            vue.$emit('newSpeaker', value)
        }
    },
};
</script>

<style lang="scss">
.br-dialog, .br-dialog .v-sheet.v-card{
    border-radius: 16px !important;
}
.card_modal_select_speaker {
    .title_act {
        color: #2A3649;
        font-size: 19px;
        line-height: 21px;
        font-family: "Nunito", sans-serif;
        font-weight: bold;
        margin: 25px 0 25px;
        padding: 0 30px;
    }
    .bx_close_modal {
        position: absolute;
        right: 10px;
        top: 17px;
    }
    .bx_text_search {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        padding: 0 24px;
    }
}
.bx_items_speakers {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    overflow-y: auto;
    height: 260px;
    align-items: start;
    .bx_item_speaker {
        display: flex;
        align-items: center;
        border: 1px solid #D9D9D9;
        border-radius: 4px;
        padding: 20px 30px;
        cursor: pointer;

        &:hover {
            border-color: #5458ea;
        }
        .bis_img {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            img {
                max-width: 100%;
            }
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
.row_new_speaker {
    text-align: center;
    margin: 10px 0 30px;
    span {
        font-family: "Nunito", sans-serif;
        color: #5458EA;
        cursor: pointer;
        transition: .5s;
        font-size: 14px;;
        &:hover {
            text-decoration: underline;
            transition: .5s;
        }
    }
}
</style>
