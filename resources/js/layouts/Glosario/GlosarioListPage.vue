<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Glosario
                <v-spacer/>
                <DefaultActivityButton :label="'Importar'"
                                       @click="openFormModal(modalImportOptions, null, null, 'Importar Glosario')"/>

                <DefaultActivityButton :label="'Carreras - Categorías'"
                                       @click="openFormModal(modalCareerCategoryOptions, null, null, 'Actualizar Carreras - Categorías')"/>

                <!-- <DefaultActivityButton :label="'Actividad'"
                                       @click="activity"/> -->
                <DefaultModalButton :label="'Glosario'"
                                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultSelect clearable dense
                                       :items="selects.modules"
                                       v-model="filters.modulo_id"
                                       label="Módulos"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect clearable dense
                                       :items="selects.categories"
                                       v-model="filters.categoria_id"
                                       label="Categorías"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput clearable dense
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
                @reset="reset"
                @edit="openFormModal(modalOptions, $event)"
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
                        'Eliminar registro'
                    )
                "
                @logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs de Glosario - ${$event.name}`
                    )
                "
            />

            <GlosarioFormModal width="50vw"
                              :ref="modalOptions.ref"
                              :options="modalOptions"
                              @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalOptions)"
            />

            <GlosarioImportFormModal width="40vw"
                              :ref="modalImportOptions.ref"
                              :options="modalImportOptions"
                              @onConfirm="closeFormModal(modalImportOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalImportOptions)"
            />

            <CareerCategoryFormModal width="60vw"
                              :ref="modalCareerCategoryOptions.ref"
                              :options="modalCareerCategoryOptions"
                              @onConfirm="closeFormModal(modalCareerCategoryOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalCareerCategoryOptions)"
            />

            <DefaultStatusModal :options="modalStatusOptions"
                                :ref="modalStatusOptions.ref"
                                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                                @onCancel="closeFormModal(modalStatusOptions)"
            />

            <DefaultDeleteModal :options="modalDeleteOptions"
                                :ref="modalDeleteOptions.ref"
                                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                                @onCancel="closeFormModal(modalDeleteOptions)"
            />
            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Glossary"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
        </v-card>
    </section>
</template>

<script>
import GlosarioFormModal from "./GlosarioFormModal";
import GlosarioImportFormModal from "./GlosarioImportFormModal";
import CareerCategoryFormModal from "./CareerCategoryFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import LogsModal from "../../components/globals/Logs";

export default {
    components: {
        GlosarioFormModal,
        GlosarioImportFormModal,
        CareerCategoryFormModal,
        DefaultStatusModal,
        LogsModal,
        DefaultDeleteModal
    },
    data() {
        return {
            dataTable: {
                endpoint: '/glosario/search',
                ref: 'GlosarioTable',
                headers: [
                    // {text: "Banner", value: "image", align: 'center', sortable: false},
                    {text: "Módulos", value: "images", align: 'center', sortable: false},
                    {text: "Nombre", value: "name"},
                    {text: "Código", value: "module", align: 'left', sortable: false},
                    {text: "Categoría", value: "categoria_id", align: 'center'},
                    // {text: "Módulo", value: "module"},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
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
                ],
                more_actions: [
                    // {
                    //     text: "Actividad",
                    //     icon: 'fas fa-file',
                    //     type: 'action',
                    //     method_name: 'activity'
                    // },
                    // {
                    //     text: "Actualizar Estado",
                    //     icon: 'fa fa-circle',
                    //     type: 'action',
                    //     method_name: 'status'
                    // },
                ]
            },
            selects: {
                modules: [],
                categories: [],
            },
            filters: {
                q: '',
                modulo_id: null,
                categoria_id: null,
            },
            modalOptions: {
                ref: 'GlosarioFormModal',
                open: false,
                base_endpoint: '/glosario',
                resource: 'Glosario',
                confirmLabel: 'Guardar',
            },
            modalImportOptions: {
                ref: 'GlosarioImportFormModal',
                open: false,
                base_endpoint: '/glosario',
                resource: 'Importar',
                confirmLabel: 'Importar',
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalCareerCategoryOptions: {
                ref: "CareerCategoryFormModal",
                open: false,
                base_endpoint: "/glosario",
                resource: "Carreras - Categorías",
                confirmLabel: "Actualizar"
            },
            modalStatusOptions: {
                ref: 'GlosarioStatusModal',
                open: false,
                base_endpoint: '/glosario',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'GlosarioDeleteModal',
                open: false,
                base_endpoint: '/glosario',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/glosario/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules
                    vue.selects.categories = data.data.categories
                })
        },
        reset(user) {
            let vue = this
            vue.consoleObjectTable(user, 'User to Reset')
        },
        activity() {
            console.log('activity')
        },
        goToCareerCategories() {
            window.location.href = "/glosario/career";
        },
        confirmModal() {
            // TODO: Call store or update USER
        },
    }
}
</script>
