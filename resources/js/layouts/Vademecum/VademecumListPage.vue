<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Protocolos y Documentos
                <v-spacer/>
                <DefaultActivityButton label="Categorías"
                                       @click="goToCategorias"/>
                <!-- <DefaultActivityButton :label="'Actividad'"
                                       @click="activity"/> -->
                <DefaultModalButton :label="'Agregar'"
                                    @click="openFormModal(modalOptions,null,null,'Crear Documento')"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.modules"
                            v-model="filters.module_id"
                            :itemText="'name'"
                            label="Módulos"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.categories"
                            v-model="filters.category_id"
                            :itemText="'name'"
                            label="Categorías"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions, $event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar registro')"
		@logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs del Curso - ${$event.name}`
                    )
                "
            />

            <VademecumFormModal
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
            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Vademecum"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
        </v-card>
    </section>
</template>

<script>
import VademecumFormModal from "./VademecumFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import LogsModal from "../../components/globals/Logs";

export default {
    components: {
        VademecumFormModal,
        DefaultStatusModal,
        DefaultDeleteModal,
        LogsModal
    },
    data() {
        return {
            dataTable: {
                endpoint: '/vademecum/search',
                ref: 'VademecumTable',
                headers: [
                    {text: "Módulos", value: "images", align: 'left', sortable: false},
                    {text: "Nombre", value: "name"},
                    {text: "Categoría", value: "category_id", align: 'center'},
                    {text: "Sub Categoría", value: "subcategory_id", align: 'center', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "SCORM",
                        icon: 'mdi mdi-web',
                        type: 'route',
                        route: 'scorm_route',
                        route_type: 'external',
                        show_condition: 'has_scorm_route',
                        disable_btn: true
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
                    {
                        text: "Eliminar",
                        icon: "far fa-trash-alt",
                        type: "action",
                        method_name: "delete"
                    },
                    {
                        text: "Logs",
                        icon: "mdi mdi-database",
                        type: "action",
                        show_condition: "is_super_user",
                        method_name: "logs"
                    }
                ],
                more_actions: [
                    // {
                    //     text: "Actividad",
                    //     icon: 'fas fa-file',
                    //     type: 'action',
                    //     method_name: 'activity'
                    // },
                ]
            },
            selects: {
                modules: [],
                categories: [],
            },
            filters: {
                q: '',
                module_id: null,
                category_id: null,
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalOptions: {
                ref: 'VademecumFormModal',
                open: false,
                base_endpoint: '/vademecum',
                resource: 'Vademecum',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'VademecumStatusModal',
                open: false,
                base_endpoint: '/vademecum',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'VademecumDeleteModal',
                open: false,
                base_endpoint: '/vademecum',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    }
    ,
    mounted() {
        let vue = this
        vue.getSelects();
    }
    ,
    methods: {
        getSelects() {
            let vue = this
            const url = `/vademecum/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules
                    vue.selects.categories = data.data.categories
                })
        },
        goToCategorias() {
            window.location.href = `/vademecum/categorias`
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
