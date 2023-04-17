<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>
                <!--                <DefaultActivityButton-->
                <!--                    :label="'Actividad'"-->
                <!--                    @click="activity"/>-->
                <DefaultModalButton
                    :label="'Escuela'"
                    @click="openCRUDPage(`/escuelas/create`)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <!-- <DefaultSelect
                            clearable dense
                            :items="selects.modules"
                            v-model="filters.module"
                            label="Módulo"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        /> -->
                        <DefaultAutocomplete
                            dense
                            label="Módulos"
                            :items="selects.modules"
                            v-model="filters.modules"
                            item-text="name"
                            item-value="id"
                            multiple
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInputDate
                            clearable
                            dense
                            range
                            :referenceComponent="'modalDateFilter1'"
                            :options="modalDateFilter1"
                            v-model="filters.dates"
                            label="Fecha de creación"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
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
            <!-- </v-card> -->
            <!--        Contenido-->
            <!-- <v-card flat class="elevation-0 mb-4"> -->
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs de la escuela - ${$event.name}`
                    )
                "
                @status="
                    openFormModal(
                        modalStatusOptions,
                        $event,
                        'status',
                        'Actualizar estado'
                    )
                "
                @delete="
                    openFormModal(
                        modalDeleteOptions,
                        $event,
                        'delete',
                        'Eliminar escuela'
                    )
                "
                @duplicate="openDuplicarModal($event)"
            />
                <!-- @delete="deleteEscuela($event)" -->

           <!--  <DialogConfirm
                v-model="modalDeleteOptions.open"
                width="450px"
                title="Eliminar Escuela"
                subtitle="¿Está seguro de eliminar la escuela?"
                @onConfirm="confirmDelete"
                @onCancel="modalDeleteOptions.open = false"
            /> -->

            <EscuelaValidacionesModal
                width="50vw"
                :ref="modalEscuelasValidaciones.ref"
                :options="modalEscuelasValidaciones"
                @onCancel="closeFormModal(modalEscuelasValidaciones)"
                :resource="delete_model"
            />

         <!--    <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalStatusOptions, dataTable, filters)"
            /> -->
          <!--   <DuplicarCursos
                :ref="modalCursosDuplicar.ref"
                :modalCursosDuplicar="modalCursosDuplicar"
                @onCancel='closeFormModalDuplicarCursos'
            /> -->

            <DefaultDeleteModal
                :options="modalDeleteOptions"
                :ref="modalDeleteOptions.ref"
                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalDeleteOptions)"
            />
            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\School"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
        </v-card>
    </section>
</template>

<script>
import EscuelaFormModal from "./EscuelaFormModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import EscuelaValidacionesModal from "./EscuelaValidacionesModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DuplicarCursos from './DuplicarCursos';
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import LogsModal from "../../components/globals/Logs";

export default {
    props: ["workspace_id", "workspace_name"],
    components: {
        EscuelaFormModal,
        EscuelaValidacionesModal,
        DialogConfirm,
        DefaultStatusModal,
        DuplicarCursos,
        DefaultDeleteModal,
        LogsModal
    },
    data() {
        let vue = this

        return {
            breadcrumbs: [
                {title: 'Escuelas', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: '/escuelas/search',
                ref: 'escuelasTable',
                headers: [
                    // {text: "Orden", value: "position", align: 'center', model: 'School', sortable: false},
                    {text: "Portada", value: "image", align: 'center', sortable: false},
                    {text: "Nombres", value: "name"},
                    // {text: "Módulos", value: "modules", sortable: false},
                    {text: "Módulos", value: "images", align: 'center', sortable: false},
                    {text: "Fecha de creación", value: "created_at", align: 'center', sortable: true},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Cursos",
                        icon: 'fas fa-book',
                        type: 'route',
                        count: 'cursos_count',
                        route: 'cursos_route'
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'route',
                        route: 'edit_route'
                    },
                    // {
                    //     text: "Duplicar",
                    //     icon: 'fa fa-circle',
                    //     type: 'action',
                    //     method_name: 'duplicate'
                    // },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        show_condition: 'has_no_courses',
                        method_name: 'delete'
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",

                        method_name: "logs"
                    }

                    // {
                    //     text: "Actualizar Estado",
                    //     icon: 'fa fa-circle',
                    //     type: 'action',
                    //     method_name: 'status'
                    // },
                ],
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalDeleteOptions: {
                ref: 'EscuelaDeleteModal',
                open: false,
                base_endpoint: '/escuelas',
                contentText: '¿Desea eliminar esta escuela?',
                endpoint: '',
            },
            selects: {
                modules: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ],
            },
            filters: {
                q: '',
                modules: [],
                active: 1,
            },
            modalEscuelasValidaciones: {},
            modalEscuelasValidacionesDefault: {
                ref: 'TemaValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
            },
            delete_model: null,
            // modalStatusOptions: {
            //     ref: 'EscuelaStatusModal',
            //     open: false,
            //     base_endpoint: '/modulos/' + vue.workspace_id + '/escuelas',
            //     contentText: '¿Desea cambiar de estado a este registro?',
            //     endpoint: '',
            // },
            modalCursosDuplicar: {
                categoria_id: 0,
                dialog: false,
                ref: 'CursosDuplicarModal',
            },
            modalDateFilter1: {
                open: false,
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
        // vue.selectDefaultModule(vue.selects.modules)
    },
    methods: {
        getSelects() {
            let vue = this

            const url = `/escuelas/form-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules
                    vue.selectDefaultModule(vue.selects.modules)
                })
        },
        activity() {
            console.log('activity')
        },
        deleteEscuela(school) {
            // let vue = this
            // vue.delete_model = school
            // vue.modalDeleteOptions.open = true
        },
        selectDefaultModule(modules) {
            let vue = this

            let uri = window.location.search.substring(1); 
            let params = new URLSearchParams(uri);
            let param_module_id = params.get("module_id");

            // await vue.$nextTick(() => {
                if (param_module_id) {

                    let module_idx = null

                    modules.forEach(row => {

                        if ( row.id == param_module_id ) {

                            vue.filters.modules.push(row)

                            vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                        }
                    });
                }
            // })
        },
        async cleanModalEscuelasValidaciones() {
            let vue = this
            await vue.$nextTick(() => {
                vue.modalEscuelasValidaciones = Object.assign({}, vue.modalEscuelasValidaciones, vue.modalEscuelasValidacionesDefault)
            })
        },
        confirmDelete() {
            let vue = this
            // let url = `/modulos/${vue.workspace_id}/escuelas/${vue.delete_model.id}`
            // let url = `/escuelas/${vue.delete_model.id}`

            // vue.$http.delete(url)
            //     .then(({data}) => {
            //         vue.showAlert(data.data.msg)
            //         vue.refreshDefaultTable(vue.dataTable, vue.filters)
            //         vue.delete_model = null
            //         vue.modalDeleteOptions.open = false
            //     })
            //     .catch(async ({data}) => {
            //         await vue.cleanModalEscuelasValidaciones()
            //         vue.loadingActionBtn = false
            //         vue.modalEscuelasValidaciones.hideConfirmBtn = true
            //         vue.modalEscuelasValidaciones.cancelLabel = 'Entendido'
            //         await vue.openFormModal(vue.modalEscuelasValidaciones, data, data.type, data.title)
            //     })
        },
        openDuplicarModal(event) {
            let vue = this;
            vue.showLoader();
            vue.modalCursosDuplicar.dialog = true;
            vue.modalCursosDuplicar.categoria_id = event.id;
            vue.$refs.CursosDuplicarModal.get_data();
        },
        closeFormModalDuplicarCursos() {
            let vue = this;
            vue.modalCursosDuplicar.dialog = false;
            vue.modalCursosDuplicar.categoria_id = 0;
        }
    }
}
</script>
