<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>
        
                <DefaultModalButton
                    :label="'Crear escuela'"
                    @click="openFormModal(modalSchoolOptions, null, 'create')"
                />
                    <!-- v-if="$root.isSuperUser" -->
                <!-- <DefaultModalButton
                    :label="'Escuela'"
                    @click="openCRUDPage(`/escuelas/create`)"
                /> -->
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3" v-if="!param_module_id">
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
                            @onChange="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado"
                            @onChange="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
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
                            @onChange="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1),changeHeaders()"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
            <!-- </v-card> -->
            <!--        Contenido-->
            <!-- <v-card flat class="elevation-0 mb-4"> -->
            <DefaultTable
                :avoid_first_data_load="getUrlParamsTotal() > 0"
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
                @duplicate="
                    openFormModal(
                        duplicateFormModalOptions,
                        $event,
                        'duplicate',
                        `Copiar cursos de escuela - ${$event.name}`
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

                @edit="openFormModal(modalSchoolOptions, $event, 'edit', `Editar escuela - ${$event.name}`)"
            />
            
            <SchoolFormModal
                width="65vw"
                :ref="modalSchoolOptions.ref"
                :options="modalSchoolOptions"
                :subworkspace_id="param_module_id"
                @onConfirm="closeFormModal(modalSchoolOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalSchoolOptions)"
            />

            <EscuelaValidacionesModal
                width="50vw"
                :ref="modalEscuelasValidaciones.ref"
                :options="modalEscuelasValidaciones"
                @onCancel="closeFormModal(modalEscuelasValidaciones)"
                :resource="delete_model"
            />

            <DuplicateForm
                :options="duplicateFormModalOptions"
                width="50vw"
                duplicate_level="school"
                :ref="duplicateFormModalOptions.ref"
                @onConfirm="closeFormModal(duplicateFormModalOptions, dataTable, filters)"
                @onCancel="closeFormModal(duplicateFormModalOptions)"
            />

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
import SchoolFormModal from "./SchoolFormModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import EscuelaValidacionesModal from "./EscuelaValidacionesModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
// import DuplicarCursos from './DuplicarCursos';
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import LogsModal from "../../components/globals/Logs";
import DuplicateForm from "./DuplicateForm";

export default {
    props: ["workspace_id", "workspace_name"],
    components: {
        SchoolFormModal,
        EscuelaValidacionesModal,
        DialogConfirm,
        DefaultStatusModal,
        DuplicateForm,
        DefaultDeleteModal,
        LogsModal
    },
    data() {
        let vue = this

        return {
            param_module_id: null,
            breadcrumbs: [
                {title: 'Escuelas', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: '/escuelas/search',
                ref: 'escuelasTable',
                headers: [
                    // {text: "Orden", value: "position", align: 'center', model: 'School', sortable: false},
                    {text: "Portada", value: "new_image", align: 'center', sortable: false},
                    {text: "Nombre", value: "escuela_nombre", sortable: false},
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
                        type: 'action',
                        method_name: 'edit',
                        // show_condition: "is_cursalab_super_user"
                    },
                    // {
                    //     text: "Editar",
                    //     icon: 'mdi mdi-pencil',
                    //     type: 'route',
                    //     route: 'edit_route'
                    // },
                ],
                more_actions: [
                    {
                        text: "Copiar cursos",
                        icon: 'mdi mdi-content-copy',
                        type: 'action',
                        method_name: 'duplicate'
                    },
                        // show_condition: "is_cursalab_super_user",
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
                ],
            },
            duplicateFormModalOptions: {
                ref: 'DuplicateForm',
                open: false,
                action: 'duplicate',
                base_endpoint: 'escuelas',
                showCloseIcon: true,
                confirmLabel: 'Copiar contenidos'
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

            modalSchoolOptions: {
                ref: 'SchoolFormModal',
                open: false,
                base_endpoint: '/escuelas',
                confirmLabel: 'Guardar',
                resource: 'escuela',
                title: '',
                action: null,
                persistent: true,
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
        changeHeaders(){
            let vue = this;
            const indexOrden = vue.dataTable.headers.findIndex(h => h.text == 'Orden');
            // console.log('indexOrden',indexOrden);
            if(vue.filters.modules.length ==1 && !vue.filters.q && !vue.filters.active &&  !vue.filters.dates){
                // console.log('entra if');
                vue.$nextTick(() => {
                    if(indexOrden == -1){
                        vue.dataTable.headers.unshift({text: "Orden", value: "position", align: 'center', model: 'SchoolSubworkspace', sortable: false}, 1);
                        // console.log('entra set');
                    }
                });
            }else{
                // console.log('entra else');
                if(indexOrden != -1){
                    vue.$nextTick(() => {
                        vue.dataTable.headers.splice(indexOrden, 1);
                        // console.log('entra delete');
                    })
                }
            }
        },
        selectDefaultModule(modules) {
            let vue = this

            let uri = window.location.search.substring(1); 
            let params = new URLSearchParams(uri);
            vue.param_module_id = params.get("module_id");

            // await vue.$nextTick(() => {
                if (vue.param_module_id) {

                    let module_idx = null

                    modules.forEach(row => {

                        if ( row.id == vue.param_module_id ) {

                            vue.breadcrumbs = [
                                {title: 'Módulos', text: row.name, disabled: false, href: '/modulos'},
                                {title: 'Escuelas', text: null, disabled: true, href: ''},
                            ];

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
