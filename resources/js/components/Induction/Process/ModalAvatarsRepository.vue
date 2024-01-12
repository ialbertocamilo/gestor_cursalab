

<template>
    <v-dialog :max-width="width" v-model="value" scrollable @click:outside="closeModal">
        <v-card class="modal_avatars_repository">
            <v-card-text class="pt-0">
                <v-card style="box-shadow:none !important;" class="bx_steps bx_step1">
                    <v-card-text>
                        <v-row class="mt-3">
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <h4 class="text_default lbl_tit">Repositorio de avatars</h4>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <span class="text_default">Cambiar foto de perfil de tu empresa.</span>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" md="12" lg="12" class="pb-0">
                                <div class="box_avatars_img">
                                    <div class="item_avatar_img">
                                        <img src="/img/induccion/personalizacion/perfil-hombre.png">
                                    </div>
                                    <div class="item_avatar_img">
                                        <img src="/img/induccion/personalizacion/perfil-mujer.png">
                                    </div>
                                    <div class="item_avatar_img"></div>
                                    <div class="item_avatar_img"></div>
                                    <div class="item_avatar_img"></div>
                                    <div class="item_avatar_img"></div>
                                    <div class="item_avatar_img"></div>
                                    <div class="item_avatar_img"></div>
                                </div>
                            </v-col>
                        </v-row>
                        <v-row class="mt-4 text-center">
                            <v-col cols="12" md="12" lg="12" class="pb-0 pt-0">
                                <DefaultButton :label="'Agregar otra foto de perfil personalizada'"
                                    @click="null"
                                    :outlined="true"
                                    style="border-radius: 20px;"
                                    />
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

            </v-card-text>

            <v-card-actions class="actions_btn_modal">
                <DefaultButtonModalSteps
                    @cancel="prevStep"
                    @confirm="nextStep"
                    :cancelLabel="cancelLabel"
                    confirmLabel="Aplicar"
                    :disabled_next="disabled_btn_next"
                    />
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
import draggable from 'vuedraggable'
import DefaultButtonModalSteps from '../globals/DefaultButtonModalSteps.vue';

export default {
    components: {
        draggable,
        DefaultButtonModalSteps
    },
    props: {
        value: Boolean,
        width: String,
    },
    data() {
        return {
            disabled_btn_next: true,
            process: {
                limit_absences: false,
            },
            limit_absences: false,
            cancelLabel: "Cancelar",
            isLoading: false,
        };
    },
    watch: {
        process: {
            handler(n, o) {
                let vue = this;
                vue.disabled_btn_next = !(vue.validateRequired(vue.process.title) && vue.validateRequired(vue.process.description));
            },
            deep: true
        }
    },
    methods: {
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        nextStep(){
            let vue = this;
            vue.cancelLabel = "Cancelar";

            vue.confirm();
        },
        prevStep(){
            let vue = this;
            vue.closeModal();
        },
        closeModal() {
            let vue = this;
            vue.process = {}
            vue.$emit("onCancel");
        },
        confirm() {
            let vue = this;
            vue.$emit("onConfirm", vue.process);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
    }
};
</script>
<style lang="scss">

.modal_avatars_repository {
    h4.text_default.lbl_tit {
        font-size: 16px;
    }
    .box_avatars_img {
        display: flex;
        column-gap: 20px;
        row-gap: 20px;
        background-color: #F3F3F3;
        padding: 30px 20px;
        border-radius: 8px;
        flex-wrap: wrap;
        .item_avatar_img {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            overflow: hidden;
            cursor: pointer;
            background-color: #D9D9D9;
            img {
                max-width: 100%;
            }
            &:hover {
                outline: 2px solid #5458EA;
            }
        }
    }
}
</style>
