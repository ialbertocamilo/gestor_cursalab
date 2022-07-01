<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-form ref="MoverCursoForm">

                <v-row justify="space-around" class="mt-6" no-gutters>
                    <v-col cols="12">
                        <DefaultAutocomplete
                            dense
                            label="Mover Curso"
                            v-model="escuela_id"
                            :items="selects.escuelas"
                            :rules="rules.escuelas"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <img src="/svg/mover_curso.svg" width="350" class="my-7">
                </v-row>
            </v-form>
            <CursoValidacionesModal
                width="40vw"
                :ref="modalCursosValidaciones.ref"
                :options="modalCursosValidaciones"
                @onCancel="closeFormModal(modalCursosValidaciones)"
            />
        </template>
    </DefaultDialog>

</template>

<script>
import CursoValidacionesModal from "./CursoValidacionesModal";
export default {
    components: {CursoValidacionesModal},
    props: {
        options: {
            type: Object,
            required: true
        },
        curso_escuela: String | Number,
        width: String,
        modulo_id: String | Number,
    },
    data() {
        return {
            selects: {
                escuelas: []
            },
            escuela_id: null,
            curso_id: null,
            rules: {
                escuelas: this.getRules(['required']),
            },
            modalCursosValidaciones: {
                ref: 'CursoValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'CursosValidaciones',
            },
            modalCursosValidacionesDefault: {
                ref: 'CursoValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'CursosValidaciones',
            },
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.resetValidation()

            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            const validateForm = vue.validateForm('MoverCursoForm')
            if (validateForm) {
                const data = {
                    escuela_id: vue.escuela_id
                }
                const url = `${vue.options.base_endpoint}/${vue.curso_id}/mover_curso`
                vue.$http.post(url, data)
                    .then(({data}) => {
                        // console.log(data)
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
                    .catch(async ({data}) => {
                        console.log("DATA VALIDATE :: ", data.validate)
                        await vue.cleanModalCursosValidaciones()
                        vue.loadingActionBtn = false
                        if (data.validate.show_confirm) {
                            vue.modalCursosValidaciones.hideConfirmBtn = false
                            vue.modalCursosValidaciones.hideCancelBtn = false
                            vue.modalCursosValidaciones.cancelLabel = 'Cancelar'
                            vue.modalCursosValidaciones.confirmLabel = 'Confirmar'
                        } else {
                            vue.modalCursosValidaciones.hideConfirmBtn = true
                            vue.modalCursosValidaciones.cancelLabel = 'Entendido'
                        }
                        await vue.openFormModal(vue.modalCursosValidaciones, data.validate, 'validateDeleteCurso', data.validate.title)
                        vue.hideLoader()
                    })
            } else {
                this.hideLoader()
            }
        },
        resetValidation() {
            let vue = this
            vue.escuela_id = null
            vue.resetFormValidation('MoverCursoForm')
        },
        loadData(resource) {
            let vue = this
            vue.curso_id = resource.id
            vue.listEscuelasxModulo(resource.config_id, [vue.curso_escuela])
                .then((data) => {
                    vue.selects.escuelas = data
                })
        },
        loadSelects() {

        },
        async cleanModalCursosValidaciones() {
            let vue = this
            await vue.$nextTick(() => {
                vue.modalCursosValidaciones = Object.assign({}, vue.modalCursosValidaciones, vue.modalCursosValidacionesDefault)
            })
        },
    }
}
</script>
