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
        <v-card class="card_modal_select_config">
            <p class="title_act fw-bold">Continúa configurando tu proceso de inducción</p>
            <div class="bx_close_modal_activity">
                <v-btn icon :ripple="false" @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <v-card-text class="py-8 text-center">
                <p class="title_act mt-0">¿Qué deseas configurar ahora?</p>
                <div class="bx_items_config">
                    <div class="bxic_col_large">
                        <div class="bx_item_config" @click="selectNextConfigProcess('activity')">
                            <div class="bx_star"><v-icon color="#5458EA">mdi-star</v-icon></div>
                            <div class="img">
                                <img src="/img/induccion/config/activities.svg">
                                <v-icon color="#039855" v-if="process.activities">mdi-check-circle</v-icon>
                            </div>
                            <h5>Asignar actividades</h5>
                            <p>Procesos que desarrollaran los colaboradores dentro de su inducción</p>
                            <span class="text_default" style="color: #5458EA;">Siguiente proceso recomendado</span>
                        </div>
                    </div>
                    <div class="bxic_col">
                        <div class="bx_item_config" @click="selectNextConfigProcess('segment')">
                            <div class="img">
                                <img src="/img/induccion/config/segments.svg">
                                <v-icon color="#039855" v-if="process.segments">mdi-check-circle</v-icon>
                            </div>
                            <div class="ms-2">
                                <h5 class="m-0">Segmentar</h5>
                                <p>Selecciona criterios que filtraran a los colaboradores que realizaran la inducción</p>
                            </div>
                        </div>
                        <div class="bx_item_config" @click="selectNextConfigProcess('certificate')">
                            <div class="img">
                                <img src="/img/induccion/config/certificate.svg">
                                <v-icon color="#039855" v-if="process.certificate">mdi-check-circle</v-icon>
                            </div>
                            <div class="ms-2">
                                <h5 class="m-0">Agregar certificado</h5>
                                <p>Define el entregable que se brindara a tus colaboradores al finalizar su inducción</p>
                            </div>
                        </div>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width"],
    data() {
        return {
            dialog: false,
            process : {
                activities: false,
                edit: false,
                certificate: false,
                segments: false
            },
            item: {}
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
        selectNextConfigProcess(value) {
            let vue = this
            vue.$emit('selectNextConfigProcess', value, vue.item)
        },
        loadSelects(resource) {
            let vue = this
        },
        async loadData(resource) {
            let vue = this
            vue.$nextTick(() => {
                vue.item = resource
                // vue.process = resource.config_process
            });
        },
        resetValidation() {
            let vue = this
            vue.process = {
                activities: false,
                edit: false,
                certificate: false,
                segments: false
            }
        },
    },
};
</script>

<style lang="scss">
.card_modal_select_config {
    .title_act {
        color: #2A3649;
        font-size: 16px;
        // line-height: 21px;
        font-family: "Nunito", sans-serif;
        margin: 25px 0 25px;
        padding: 0 30px;
    }
    .bx_close_modal_activity {
        position: absolute;
        right: 10px;
        top: 10px;
    }
    .bx_items_config {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        margin: 40px 0 20px;
    }
    .bx_item_config {
        padding: 26px;
        border-radius: 10px;
        width: 218px;
        margin: 5px;
        cursor: pointer;
        box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
    }
    .bx_item_config:hover {
        box-shadow: 0px 4px 15px rgba(194,194,194,1);
    }
    .bx_item_config h5 {
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
    .bx_item_config p {
        color: #2A3649;
        font-size: 13px;
        line-height: 17px;
        font-family: "Nunito", sans-serif;
    }
    .bxic_col_large .bx_item_config {
        border: 1px solid #5458EA;
        position: relative;
        padding-top: 49px;
        .bx_star {
            text-align: right;
            position: absolute;
            right: 10px;
            top: 10px;
        }
    }
    .bxic_col .bx_item_config {
        display: flex;
        align-items: center;
        width: 320px;
        text-align: left;
    }
    .bx_item_config .img {
        position: relative;
        width: 71px;
        margin: 0 auto;
        i.v-icon {
            position: absolute;
            bottom: 0px;
            right: 4px;
        }
    }
}
</style>
