<template>
    <section class="section-list ">
        <DefaultFilter v-model="open_advanced_filter"
                       @filter="advanced_filter(dataTable, filters, 1)"
        >
            <template v-slot:content>
                <v-row justify="center">

                    <!--                    <v-col cols="12">-->
                    <!--                        <DefaultAutocomplete-->
                    <!--                            clearable-->
                    <!--                            placeholder="Seleccione una Carrera"-->
                    <!--                            label="Carrera"-->
                    <!--                            :items="selects.carreras"-->
                    <!--                            v-model="filters.carrera"-->
                    <!--                        />-->
                    <!--                    </v-col>-->
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>
                <!--                <DefaultActivityButton-->
                <!--                    :label="'Actividad'"-->
                <!--                    @click="activity"/>-->
                <DefaultModalButton
                    :label="'Curso'"
                    @click="openCRUDPage(`/escuelas/${escuela_id}/cursos/create`)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="4">
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>

                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @encuesta="openFormModal(modalCursoEncuesta, $event, 'encuesta', `Encuesta del Curso - ${$event.name}`)"
                @mover_curso="openFormModal(modalMoverCurso, $event, 'mover_curso', 'Mover Curso')"
                @delete="deleteCurso($event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"

            />
            <CursosEncuestaModal
                width="50vw"
                :ref="modalCursoEncuesta.ref"
                :options="modalCursoEncuesta"
                @onCancel="closeFormModal(modalCursoEncuesta)"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
            />
            <MoverCursoModal
                width="50vw"
                :ref="modalMoverCurso.ref"
                :options="modalMoverCurso"
                @onCancel="closeFormModal(modalMoverCurso)"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
                :modulo_id="modulo_id"
                :curso_escuela="escuela_id"
            />
            <DialogConfirm
                :ref="modalDeleteOptions.ref"
                v-model="modalDeleteOptions.open"
                width="450px"
                title="Eliminar Curso"
                subtitle="¿Está seguro de eliminar el curso?"
                @onConfirm="confirmDelete"
                @onCancel="modalDeleteOptions.open = false"
            />
            <CursoValidacionesModal
                width="50vw"
                :ref="modalCursosValidaciones.ref"
                :options="modalCursosValidaciones"
                @onCancel="closeFormModal(modalCursosValidaciones); closeFormModal(modalStatusOptions); closeFormModal(modalDeleteOptions)"
                @onConfirm="callFunction"
                :resource="delete_model"
            />
            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters); openCursoValidacionesModal($event)"
                @onCancel="closeFormModal(modalStatusOptions, dataTable, filters); closeFormModal(modalStatusOptions); closeFormModal(modalDeleteOptions)"
                @onError="onErrorUpdateStatusCurso"
            />
        </v-card>
    </section>
</template>

<script>
import CursosEncuestaModal from "./CursosEncuestaModal";
import MoverCursoModal from "./MoverCursoModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import CursoValidacionesModal from "./CursoValidacionesModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";

export default {
    components: {CursosEncuestaModal, MoverCursoModal, DialogConfirm, CursoValidacionesModal, DefaultStatusModal},
    props: ['modulo_id', 'modulo_name', 'escuela_id', 'escuela_name'],
    data() {
        let vue = this

        return {
            breadcrumbs: [
                {title: 'Escuelas', text: `${this.escuela_name}`, disabled: false, href: `/escuelas`},
                {title: 'Cursos', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `/escuelas/${vue.escuela_id}/cursos/search`,
                ref: 'cursosTable',
                headers: [
                    {text: "Portada", value: "image", align: 'center', sortable: false},
                    {text: "Nombres", value: "custom_curso_nombre", sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Temas",
                        icon: 'fas fa-book',
                        type: 'route',
                        count: 'temas_count',
                        route: 'temas_route'
                    },
                    {
                        text: "Encuesta",
                        icon: 'mdi mdi-poll',
                        type: 'action',
                        count: 'encuesta_count',
                        method_name: 'encuesta'
                    },
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'route',
                        route: 'edit_route'
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                ],
            },
            selects: {
                modules: []
            },
            filters: {
                q: '',
                // module: null,
                // category: null
            },
            modalCursoEncuesta: {
                ref: 'CursoEncuestaModal',
                open: false,
                base_endpoint: `/modulos/${this.modulo_id}/escuelas/${this.escuela_id}/cursos`,
            },
            modalMoverCurso: {
                ref: 'MoverCursoModal',
                open: false,
                base_endpoint: `/modulos/${this.modulo_id}/escuelas/${this.escuela_id}/cursos`,
            },
            modalDeleteOptions: {
                ref: 'EscuelaDeleteModal',
                title: 'Eliminar Escuela',
                contentText: '¿Desea eliminar este registro?',
                open: false,
                endpoint: ''
            },
            modalStatusOptions: {
                ref: 'CursoStatusModal',
                open: false,
                base_endpoint: `/modulos/${this.modulo_id}/escuelas/${this.escuela_id}/cursos`,
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            delete_model: null,
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
    mounted() {
        let vue = this
        // vue.getSelects();

        vue.filters.module = vue.modulo_id
        vue.filters.category = vue.escuela_id
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/escuelas/get-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules
                    vue.modalOptions.selects.modules = data.data.modules
                })
        },
        activity() {
            console.log('activity')
        },
        deleteCurso(tema) {
            let vue = this
            vue.delete_model = tema
            vue.modalDeleteOptions.open = true
        },
        async cleanModalCursosValidaciones() {
            let vue = this
            await vue.$nextTick(() => {
                vue.modalCursosValidaciones = Object.assign({}, vue.modalCursosValidaciones, vue.modalCursosValidacionesDefault)
            })
        },
        confirmDelete(withValidations = true) {
            let vue = this
            vue.showLoader()
            let url = `/modulos/${vue.modulo_id}/escuelas/${vue.escuela_id}/cursos/${vue.delete_model.id}`

            if (!withValidations) {
                url += '?withValidations=1'
            } else {
                url += '?withValidations=0'
            }

            vue.$http.post(url)
                .then(async ({data}) => {
                    vue.refreshDefaultTable(vue.dataTable, vue.filters)
                    vue.delete_model = null
                    vue.modalDeleteOptions.open = false
                    const messages = data.data.messages

                    if (messages.data.length > 0) {
                        // console.log(messages.data)
                        await vue.cleanModalCursosValidaciones()
                        vue.modalCursosValidaciones.hideCancelBtn = true
                        vue.modalCursosValidaciones.confirmLabel = 'Entendido'
                        vue.modalCursosValidaciones.persistent = true
                        await vue.openFormModal(vue.modalCursosValidaciones, messages, 'messagesActions', 'Aviso')
                    } else {
                        vue.showAlert(data.data.msg)
                        vue.hideLoader()
                    }
                })
                .catch(async ({data}) => {
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
        },
        async onErrorUpdateStatusCurso(data) {
            let vue = this
            console.log('onErrorUpdateStatusCurso', data)
            await vue.cleanModalCursosValidaciones()

            if (data.validate.show_confirm) {
                vue.modalCursosValidaciones.hideConfirmBtn = false
                vue.modalCursosValidaciones.hideCancelBtn = false
                vue.modalCursosValidaciones.cancelLabel = 'Cancelar'
                vue.modalCursosValidaciones.confirmLabel = 'Confirmar'
            } else {
                vue.modalCursosValidaciones.hideConfirmBtn = true
                vue.modalCursosValidaciones.cancelLabel = 'Entendido'
            }
            await vue.openFormModal(vue.modalCursosValidaciones, data.validate, 'validateUpdateStatus', data.validate.title)
        },
        async callFunction(data) {
            let vue = this
            console.log('double confirm')
            if (data.confirmMethod === "validateDeleteCurso") {
                vue.confirmDelete(false);
            } else if (['updateStatus', 'validateUpdateStatus'].includes(data.confirmMethod)) {
                vue.$refs[vue.modalStatusOptions.ref].onConfirm(false)
            }
            await vue.cleanModalCursosValidaciones()
            vue.modalCursosValidaciones.open = false
        },
        async openCursoValidacionesModal(data) {
            let vue = this
            console.log("openCursoValidacionesModal", data)
            if (data.hasOwnProperty('confirmMethod')) {
                await vue.cleanModalCursosValidaciones()
                vue.modalCursosValidaciones.hideCancelBtn = true
                vue.modalCursosValidaciones.confirmLabel = 'Entendido'
                vue.modalCursosValidaciones.persistent = true
                await vue.openFormModal(vue.modalCursosValidaciones, data.data.data.messages, 'messagesActions', 'Aviso')
            }
        }
    }

}
</script>
