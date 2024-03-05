

<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModalMaxColaborador"
        :class="{}"
        content-class="br-dialog"
    >
        <v-card class="modal_max_colaboradores">
            <v-card-title class="default-dialog-title">
                Máximo de beneficios permitidos
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModalMaxColaborador">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-0 pb-0">
                <v-row>
                    <v-col cols="12" md="12" lg="12" class="pb-0 text-center">
                        <div class="bx_txts">
                            <p class="text_default fw-bold">El colaborador podra acceder a:</p>
                            <div class="bx_max_benefits">
                                <span class="lbl_max_benefits">{{ max_benefits }}</span>
                                <div class="btns_change_max_col" v-if="max_benefits != null">
                                    <div class="btn_change_max_col_up" @click="max_benefits = max_benefits + 1">
                                        <img src="/img/benefits/chevron_top.svg">
                                    </div>
                                    <div class="btn_change_max_col_down" @click="max_benefits = max_benefits - 1" v-if="max_benefits > 0">
                                        <img src="/img/benefits/chevron_bottom.svg">
                                    </div>
                                </div>
                            </div>
                            <p class="text_default fw-bold">beneficios como máximo por año.</p>
                            <p class="text_default lbl_aviso"><i class="fas fa-exclamation-triangle" style="color: #FF9800;"></i> <b>Recuerda que</b> en caso de reducir la cantidad de beneficios seleccionados, esto retiraría a los usuarios de los últimos beneficios a los que se hayan registrado.</p>
                        </div>
                    </v-col>
                </v-row>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid #94DDDB" class="actions_btn_modal">
                <v-row justify="center" class="mx-0">
                    <v-col cols="4" class="d-flex justify-content-around">
                        <v-btn
                            class="default-modal-action-button mx-1"
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="closeModalMaxColaborador"
                        >
                            Cancelar
                        </v-btn>
                        <v-btn
                            class="default-modal-action-button  mx-1 btn_back"
                            text
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="confirmModalMaxColaborador"
                        >
                            Confirmar
                        </v-btn>
                    </v-col>

                </v-row>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>

export default {
    components: {
    },
    props: {
        data: [Object, Array],
        value: Boolean,
        width: String,
        max_benefits: Number,
    },
    data() {
        return {
            isLoading: false,
            resourceDefault: {
                id: null,
                name: null,
            },
            resource: {},
            autocomplete_loading: false,
        };
    },
    async mounted() {
        // this.addActividad()
    },
    methods: {
        closeModalMaxColaborador() {
            let vue = this;
            vue.$emit("closeModalMaxColaborador");
        },
        confirmModalMaxColaborador() {
            let vue = this;
            vue.$emit("confirmModalMaxColaborador", vue.max_benefits);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
    }
};
</script>
<style lang="scss">
.modal_max_colaboradores {
    .bx_max_benefits {
        display: inline-flex;
        margin-bottom: 12px;
    }
    .lbl_max_benefits {
        font-size: 60px;
        font-family: "Nunito", sans-serif;
        color: #5458EA;
        font-weight: bold;
        line-height: 1;
    }
    .btns_change_max_col {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        line-height: 1;
        margin-left: 5px;
        cursor: pointer;
    }
    .bx_txts p.text_default {
        font-size: 16px;
    }
}
</style>
