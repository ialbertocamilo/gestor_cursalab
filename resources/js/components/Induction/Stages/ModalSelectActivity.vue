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
            <v-card-title class="default-dialog-title mod_head">
                <span>Selecciona el tipo de actividad a realizar</span>
                <v-btn icon :ripple="false" color="white"
                        @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text class="py-8 text-center">
                <div class="bx_items_activitys">
                    <div class="bx_item_activity" @click="selectActivityModal('tareas')" @mouseover="item_tareas = true" @mouseleave="item_tareas = false">
                        <div class="img"><img src="/img/induccion/activities/tareas.svg"></div>
                        <h5>Subida y/o firma de documentos</h5>
                        <p>Con esta actividad se podrá subir documentos, fotos, etc, al sistema.</p>
                    </div>
                    <div class="bx_item_activity" @click="selectActivityModal('sesiones')">
                        <div class="img"><img src="/img/induccion/activities/sesiones.svg"></div>
                        <h5>Sesiones en vivo</h5>
                        <p>Actividad que se puede realizar tanto de manera virtual como presencial.</p>
                    </div>
                    <div class="bx_item_activity" @click="selectActivityModal('temas')">
                        <div class="img"><img src="/img/induccion/activities/temas.svg"></div>
                        <h5>Subir curso o contenido</h5>
                        <p>Con esta actividad podrás guiar a tus trabajadores en sus primeros pasos.</p>
                    </div>
                    <div class="bx_item_activity" @click="selectActivityModal('evaluaciones')">
                        <div class="img"><img src="/img/induccion/activities/pruebas.svg"></div>
                        <h5>Evaluación</h5>
                        <p>Comprueba si aprendieron durante el proceso de inducción</p>
                    </div>
                    <div class="bx_item_activity" @click="selectActivityModal('checklist')">
                        <div class="img"><img src="/img/induccion/activities/checklist.svg"></div>
                        <h5>Checklist</h5>
                        <p>Se evalúa los procesos que realiza la empresa o procesos que realizara el colaborador</p>
                    </div>
                    <div class="bx_item_activity" @click="selectActivityModal('encuestas')">
                        <div class="img"><img src="/img/induccion/activities/encuestas.svg"></div>
                        <h5>Encuesta del proceso</h5>
                        <p>Encuesta que se puede agregar luego de terminado el proceso de inducción.</p>
                    </div>
                    <div class="bx_item_activity" @click="selectActivityModal('pasantia')" v-if="show_pasantia">
                        <div class="img"><img src="/img/induccion/activities/pasantia.svg"></div>
                        <h5>Pasantía</h5>
                        <p>Selecciona y gestiona los participantes de pasantías al ingresar a la empresa</p>
                    </div>
                </div>
            </v-card-text>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props:{
        value: null,
        width: null,
        show_pasantia: false,
        // process_id: {
        //     type: Number,
        //     required: true
        // },
        // stage_id: {
        //     type: Number,
        //     required: true
        // },
        // course_id: {
        //     type: Number,
        //     required: true
        // }
    },
    data() {
        return {
            dialog: false,
            item_tareas: false
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
        async confirmModal() {
            let vue = this
        },
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetSelects() {
            let vue = this
        },
        resetValidation() {
            let vue = this
        },
        selectActivityModal(value) {
            let vue = this
            vue.$emit('selectActivityModal', value)
        },
        async loadData(resource) {
            let vue = this
        },
        async loadSelects() {
            let vue = this;
        },

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
    border: 1px solid transparent;
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
        border: 1px solid #5458ea;
        h5, p {
            color: #5458ea !important;
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
</style>
