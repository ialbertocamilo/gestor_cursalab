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
        <v-card>
            <div class="bx_close_modal_activity">
                <v-btn icon :ripple="false" @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </div>
            <v-card-text class="py-8 text-center">
                <p class="title_act">Selecciona el tipo de actividad a realizar</p>
                <div class="bx_items_activitys">
                    <div class="bx_item_activity" @click="selectTypeActivityModal('sesion_presencial')">
                        <div class="img"><img src="/img/benefits/sesion_presencial.svg"></div>
                        <h5>Sesiones presenciales</h5>
                    </div>
                    <div class="bx_item_activity" @click="selectTypeActivityModal('sesion_online')">
                        <div class="img"><img src="/img/benefits/sesion_online.svg"></div>
                        <h5>Sesiones virtuales</h5>
                    </div>
                    <div class="bx_item_activity" @click="selectTypeActivityModal('sesion_hibrida')">
                        <div class="img"><img src="/img/benefits/sesion_hibrido.svg"></div>
                        <h5>Sesiones híbridas</h5>
                    </div>
                    <div class="bx_item_activity" @click="selectTypeActivityModal('descuento')">
                        <div class="img"><img src="/img/benefits/descuento.svg"></div>
                        <h5>Beneficio</h5>
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
        selectTypeActivityModal(value) {
            let vue = this
            vue.$emit('selectTypeActivityModal', value)
        }
    },
};
</script>

<style lang="scss">
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
    padding: 26px;
    border-radius: 10px;
    width: 218px;
    margin: 5px;
    cursor: pointer;
    box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.15);
}
.bx_item_activity:hover {
    box-shadow: 0px 4px 15px rgba(194,194,194,1);
}
.bx_item_activity h5 {
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
.bx_item_activity p {
    color: #2A3649;
    font-size: 13px;
    line-height: 17px;
    font-family: "Nunito", sans-serif;
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
