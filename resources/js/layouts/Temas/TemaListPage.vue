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
                    @click="openCRUDPage(`/escuelas/${school_id}/cursos/${course_id}/temas/create`)"
                    :label="'Tema'"/>
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
                @delete="deleteTema($event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
            />

            <DialogConfirm
                v-model="modalDeleteOptions.open"
                width="450px"
                title="Eliminar Tema"
                subtitle="¿Está seguro de eliminar el tema?"
                @onConfirm="confirmDelete"
                @onCancel="modalDeleteOptions.open = false"
            />

            <TemaValidacionesModal
                width="50vw"
                :ref="modalTemasValidaciones.ref"
                :options="modalTemasValidaciones"
                @onCancel="closeFormModal(modalTemasValidaciones); closeFormModal(modalStatusOptions); closeFormModal(modalDeleteOptions)"
                @onConfirm="callFunction"
                :resource="delete_model"
            />

            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters); openTemaValidacionesModal($event)"
                @onCancel="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onError="onErrorUpdateStatusTema"
            />
        </v-card>
    </section>
</template>

<script>
import DialogConfirm from "../../components/basicos/DialogConfirm";
import TemaValidacionesModal from "./TemaValidacionesModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
export default {
    components: {DialogConfirm, TemaValidacionesModal, DefaultStatusModal},
    props: ['school_id', 'school_name', 'course_id', 'course_name'],
    data() {
        let vue = this
        return {
            breadcrumbs: [
                {title: 'Escuelas', text: `${this.school_name}`, disabled: false, href: `/escuelas`},
                {title: 'Cursos', text: `${this.course_name}`, disabled: false, href: `/escuelas/${this.school_id}/cursos`},
                {title: 'Temas', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: `/escuelas/${vue.school_id}/cursos/${vue.course_id}/temas/search`,
                ref: 'cursosTable',
                headers: [
                    {text: "Orden", value: "orden", align: 'center', model: 'Curso'},
                    {text: "Portada", value: "image", align: 'center', sortable: false},
                    {text: "Nombre", value: "nombre"},
                    {text: "Tipo de evaluación", value: "tipo_evaluacion", sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Evaluación",
                        icon: 'fas fa-tasks',
                        show_condition: 'es_evaluable',
                        type: 'route',
                        count: 'preguntas_count',
                        route: 'evaluacion_route'
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
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                ],
                // more_actions: [
                //     {
                //         text: "Actividad",
                //         icon: 'fas fa-file',
                //         type: 'action',
                //         method_name: 'activity'
                //     },
                // ]
            },
            modalDeleteOptions: {
                open: false,
            },
            selects: {
                modules: []
            },
            filters: {
                q: '',
                module: null,
                category: null,
                curso: null
            },
            delete_model: null,
            modalTemasValidaciones: {
                ref: 'TemaValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
            },
            modalTemasValidacionesDefault: {
                ref: 'TemaValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
            },
            modalStatusOptions: {
                ref: 'CursoStatusModal',
                open: false,
                base_endpoint: `/escuelas/${vue.school_id}/cursos/${vue.course_id}/temas`,
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        // vue.getSelects();

        // vue.filters.module = vue.modulo_id
        vue.filters.category = vue.school_id
        vue.filters.curso = vue.course_id
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
        deleteTema(tema) {
            let vue = this
            vue.delete_model = tema
            vue.modalDeleteOptions.open = true
        },
        async cleanModalTemasValidaciones() {
            let vue = this
            await vue.$nextTick(() => {
                vue.modalTemasValidaciones = Object.assign({}, vue.modalTemasValidaciones, vue.modalTemasValidacionesDefault)
            })
        },
        confirmDelete(withValidations = true) {
            let vue = this
            vue.showLoader()
            let url = `/escuelas/${vue.school_id}/cursos/${vue.course_id}/temas/${vue.delete_model.id}`

            if (!withValidations){
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
                        await vue.cleanModalTemasValidaciones()
                        vue.modalTemasValidaciones.hideCancelBtn = true
                        vue.modalTemasValidaciones.confirmLabel = 'Entendido'
                        vue.modalTemasValidaciones.persistent = true
                        await vue.openFormModal(vue.modalTemasValidaciones, messages, 'messagesActions', 'Aviso')
                    } else {
                        vue.showAlert(data.data.msg)
                        vue.hideLoader()
                    }
                })
                .catch(async ({data}) => {
                    await vue.cleanModalTemasValidaciones()
                    vue.loadingActionBtn = false
                    // vue.modalTemasValidaciones.hideConfirmBtn = true
                    // vue.modalTemasValidaciones.cancelLabel = 'Entendido'
                    if (data.validate.show_confirm) {
                        vue.modalTemasValidaciones.hideConfirmBtn = false
                        vue.modalTemasValidaciones.hideCancelBtn = false
                        vue.modalTemasValidaciones.cancelLabel = 'Cancelar'
                        vue.modalTemasValidaciones.confirmLabel = 'Confirmar'
                    } else {
                        vue.modalTemasValidaciones.hideConfirmBtn = true
                        vue.modalTemasValidaciones.cancelLabel = 'Entendido'
                    }
                    await vue.openFormModal(vue.modalTemasValidaciones, data.validate, 'validateDeleteTema', data.validate.title)
                    vue.hideLoader()
                })
        },
        async onErrorUpdateStatusTema(data){
            let vue = this
            // console.log('onErrorUpdateStatusTema :: ', data)

            await vue.cleanModalTemasValidaciones()

            if (data.validate.show_confirm) {
                vue.modalTemasValidaciones.hideConfirmBtn = false
                vue.modalTemasValidaciones.hideCancelBtn = false
                vue.modalTemasValidaciones.cancelLabel = 'Cancelar'
                vue.modalTemasValidaciones.confirmLabel = 'Confirmar'
            } else {
                vue.modalTemasValidaciones.hideConfirmBtn = true
                vue.modalTemasValidaciones.cancelLabel = 'Entendido'
            }

            await vue.openFormModal(vue.modalTemasValidaciones, data.validate, 'validateUpdateStatus', data.validate.title)
        },
        async callFunction(data){
            let vue = this
            if (data.confirmMethod === "validateDeleteTema"){
                vue.confirmDelete(false);
            } else if(['updateStatus', 'validateUpdateStatus'].includes(data.confirmMethod) ){
                vue.$refs[vue.modalStatusOptions.ref].onConfirm(false)
            }
            await vue.cleanModalTemasValidaciones()
            vue.modalTemasValidaciones.open = false
        },
        async openTemaValidacionesModal(data) {
            let vue = this
            console.log("openCursoValidacionesModal", data)
            if (data.hasOwnProperty('confirmMethod')) {
                await vue.cleanModalTemasValidaciones()
                vue.modalTemasValidaciones.hideCancelBtn = true
                vue.modalTemasValidaciones.confirmLabel = 'Entendido'
                vue.modalTemasValidaciones.persistent = true
                await vue.openFormModal(vue.modalTemasValidaciones, data.data.data.messages, 'messagesActions', 'Aviso')
            }
        }
    }

}
</script>
