<template>
    <section class="section-list">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
            @cleanFilters="clearObject(filters)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.sub_workspaces"
                            v-model="filters.subworkspace_id"
                            label="Módulos"
                            item-text="name"
                            @onChange="loadSchools()"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.schools"
                            v-model="filters.school_id"
                            label="Escuelas"
                            item-text="name"
                            @onChange="loadCourses()"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.courses"
                            v-model="filters.course_id"
                            label="Cursos"
                            item-text="name"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado"
                            item-text="name"
                        />
                    </v-col>
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Tareas
                <v-spacer/>
                <DefaultModalButton
                    label="Tarea"
                    icon_name="mdi-plus"
                    @click="openFormModal(modalOptions)"
                />

            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre del curso"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="8" class="d-flex justify-end">
                        <DefaultButton
                            text
                            label="Aplicar filtros"
                            icon="mdi-filter"
                            @click="open_advanced_filter = !open_advanced_filter"
                            class="btn_filter"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions,$event,'edit',`Editar Tarea - ${$event.course}`)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de una tarea')"
                @delete="openFormModal(modalDeleteOptions,$event,'delete','Eliminar tarea')"
            />
            <ProjectFormModal
                width="50vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />
            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalStatusOptions)"
            />
            <DefaultDeleteModal
                :options="modalDeleteOptions"
                :ref="modalDeleteOptions.ref"
                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalDeleteOptions)"
            />
        </v-card>
    </section>
</template>
<script>
import ProjectFormModal from "./ProjectFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";


export default {
    components: {ProjectFormModal,DefaultStatusModal,DefaultDeleteModal},
    data() {
        return {
            dataTable: {
                avoid_first_data_load: false,
                endpoint: '/projects/search',
                ref: 'ProjectTable',
                headers: [
                    {text: "Módulo", value: "subworkspaces",sortable: false},
                    {text: "Escuela", value: "school",sortable: false},
                    {text: "Curso", value: "course",sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Usuarios",
                        icon: 'mdi mdi-account',
                        type: 'route',
                        // count: 'usuarios_count',
                        route: 'usuarios_route',
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                    {
                        text: "Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                ],
                more_actions: [
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",
                        method_name: "logs"
                    }
                ]
            },
            selects: {
                sub_workspaces: [],
                schools:[],
                courses:[],
                statuses: [
                    {id: 3, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ]
            },
            filters: {
                q: '',
                active: 3,
                subworkspace_id:null,
                school_id:null,
                course_id:null
            },
            modalOptions: {
                ref: 'ProjectFormModal',
                open: false,
                base_endpoint: '/projects',
                resource: 'Tarea',
                confirmLabel: 'Guardar',
                action:'create',
                create_from_course_list:false,
            },
            modalStatusOptions: {
                ref: 'ProjectStatusModal',
                open: false,
                base_endpoint: '/projects',
                contentText: '¿Desea cambiar de estado a este registro?',
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar una Tarea!',
                        details: [
                            'Esta Tarea ahora no podrá ser visto por los usuarios.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar una Tarea!',
                        details: [
                            'Este Tarea ahora podrá ser visto por los usuarios.'
                        ]
                    }
                },
                endpoint: '',
                width: '408px'
            },
            modalDeleteOptions: {
                ref: 'ProjectDeleteModal',
                open: false,
                base_endpoint: '/projects',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar una Tarea!',
                        details: [
                            'Esta tarea no podrá ser visto por los usuarios.',
                            'La información eliminada no podra recuperarse'
                        ],
                    }
                },
                width: '408px'
            }
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
    },
    created() {
        let vue = this;
    },
    methods: {
        async getSelects() {
            let vue = this
            let url = `/projects/get-selects?type=module`
            vue.$http.get(url)
                .then(({ data }) => {
                    vue.selects.sub_workspaces = data.data;
                }).catch((error) => {
                });
        },
        async loadSchools(){
            let vue = this
            vue.selects.schools = [];
            vue.selects.courses = [];

            let url = `/projects/get-selects?type=school&subworkspace_id=${vue.filters.subworkspace_id}`
            vue.$http.get(url)
                .then(({ data }) => {
                    vue.selects.schools = data.data;
                }).catch((error) => {
                });
        },
        async loadCourses(){
            let vue = this
            let url = `/projects/get-selects?type=course&school_id=${vue.filters.school_id}`
            vue.selects.courses = [];
            vue.$http.get(url)
                .then(({ data }) => {
                    vue.selects.courses = data.data;
                }).catch((error) => {

                });
        },
        activity() {
            console.log('activity')
        },
        confirmModal() {
            // TODO: Call store or update USER
        },
    }
}
</script>
