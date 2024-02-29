

<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModalCorreoSegmentados"
        :class="{}"
        content-class="br-dialog"
    >
        <v-card class="modal_correos_segmentados">
            <v-card-title class="default-dialog-title px-0" style="padding-right: 0 !important;">
                Confirmar notificaciones por correo
            </v-card-title>
            <v-card-text class="pt-0 pb-0">
                <v-row>
                    <v-col cols="12" md="12" lg="12" class="pb-0 text-center">
                        <div class="bx_txts">
                            <p class="text_default fw-bold">Resultado de segmentación</p>
                            <p class="text_default lbl_users">{{ users }}</p>
                            <p class="text_default fw-bold">Podrás notificar a los colaboradores, por correo, que tiene un nuevo beneficio disponible.</p>
                            <p class="text_default lbl_aviso">Recuerda hacer este envío con anticipación. Este proceso podría tomar un maximo de 12 hrs.</p>
                        </div>
                    </v-col>
                </v-row>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <DefaultModalActionButton
                    @cancel="closeModalCorreoSegmentados"
                    @confirm="confirmModalCorreoSegmentados"
                    cancelLabel="Cancelar"
                    confirmLabel="Enviar correo"
                    />
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
        benefit_id: Number,
        users: Number,
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
        closeModalCorreoSegmentados() {
            let vue = this;
            vue.$emit("closeModalCorreoSegmentados");
        },
        confirmModalCorreoSegmentados() {
            let vue = this;
            vue.$emit("confirmModalCorreoSegmentados",vue.$props.benefit_id);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
    }
};
</script>
<style lang="scss">
.modal_correos_segmentados{
    .bx_txts {
        margin: 3em;
        p.text_default{
            font-size: 16px;
        }
        .fw-bold {
            font-weight: 700;
        }
        p.text_default.lbl_users {
            font-size: 60px;
            font-weight: 700;
            margin: 20px 0 30px;
            line-height: 1;
        }
    }
}
</style>
