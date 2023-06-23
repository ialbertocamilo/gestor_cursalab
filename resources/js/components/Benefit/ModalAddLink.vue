<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModalAddLink"
        :class="{}"
        content-class="br-dialog"
    >
        <v-card>
            <div class="bx_close_modal_activity">
                <v-btn icon :ripple="false" @click="closeModalAddLink">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <v-card-text class="py-3 text-center">
                <p class="title_act">Insertar/editar enlace</p>
                <div class="bx_items_links">
                    <v-row>
                        <v-col cols="12">
                            <DefaultInput
                                dense
                                label="URL"
                                placeholder="URL"
                                v-model="data.value"
                                :rules="rules.url"
                                show-required
                            />
                        </v-col>
                        <v-col cols="12">
                            <DefaultInput
                                dense
                                label="Texto que mostrar"
                                placeholder="Texto que mostrar"
                                v-model="data.name"
                                :rules="rules.name"
                                show-required
                            />
                        </v-col>
                        <v-col class="py-0">
                            <DefaultModalActionButton
                                @cancel="closeModalAddLink"
                                @confirm="confirmAddLink"
                            />
                        </v-col>
                    </v-row>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width", "title", "subtitle", "txt_btn_confirm", "txt_btn_cancel", "options", "content_modal", "title_modal", "data"],
    data() {
        return {
            dialog: false,
            rules: {
                name: this.getRules(['required']),
                url: this.getRules(['required']),
            },
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

        closeModalAddLink() {
            let vue = this
            vue.$emit('closeModalAddLink')
        },
        confirmAddLink(value) {
            let vue = this
            console.log(value);
            vue.$emit('confirmAddLink', value)
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
</style>
