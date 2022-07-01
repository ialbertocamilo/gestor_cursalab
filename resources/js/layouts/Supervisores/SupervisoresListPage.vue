<template>
    <section class="section-list ">
        <!--        <DefaultFilter v-model="open_advanced_filter"-->
        <!--                       @filter="advanced_filter(dataTable, filters, 1)"-->
        <!--        >-->
        <!--            <template v-slot:content>-->
        <!--                <v-row justify="center">-->

        <!--                    &lt;!&ndash;                    <v-col cols="12">&ndash;&gt;-->
        <!--                    &lt;!&ndash;                        <DefaultAutocomplete&ndash;&gt;-->
        <!--                    &lt;!&ndash;                            clearable&ndash;&gt;-->
        <!--                    &lt;!&ndash;                            placeholder="Seleccione una Carrera"&ndash;&gt;-->
        <!--                    &lt;!&ndash;                            label="Carrera"&ndash;&gt;-->
        <!--                    &lt;!&ndash;                            :items="selects.carreras"&ndash;&gt;-->
        <!--                    &lt;!&ndash;                            v-model="filters.carrera"&ndash;&gt;-->
        <!--                    &lt;!&ndash;                        />&ndash;&gt;-->
        <!--                    &lt;!&ndash;                    </v-col>&ndash;&gt;-->
        <!--                </v-row>-->
        <!--            </template>-->
        <!--        </DefaultFilter>-->
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Supervisores
                <v-spacer/>
                <DefaultActivityButton
                    :label="'Subida Masiva'"
                    @click="openFormModal(modalSupervisorCargaMasiva,null,null,modalSupervisorCargaMasiva.title)"
                />
                <DefaultModalButton
                    @click="openFormModal(modalOptions, null, 'create', 'Asignar a supervisor')"
                    :label="'Asignar Supervisor'"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.modules"
                            v-model="filters.module"
                            label="Módulos"
                            @onChange="refreshDefaultTable(dataTable, filters, 1); getAreas()"
                            @onClickClear="refreshDefaultTable(dataTable, filters, 1);"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            multiple
                            :items="selects.areas"
                            v-model="filters.areas"
                            label="Áreas asignadas"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            @onClickClear="refreshDefaultTable(dataTable, filters, 1);"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters)"
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
                @delete="deleteSupervisor($event)"
            />
            <SupervisorFormModal
                width="60vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalOptions);refreshDefaultTable(dataTable, filters) "
            />
            <SupervisorCargaMasivaModal
                width="50vw"
                :ref="modalSupervisorCargaMasiva.ref"
                :options="modalSupervisorCargaMasiva"
                @onConfirm="closeFormModal(modalSupervisorCargaMasiva, dataTable, filters);refreshDefaultTable(dataTable, filters) "
                @onCancel="closeFormModal(modalSupervisorCargaMasiva);refreshDefaultTable(dataTable, filters) "
            />
            <DialogConfirm
                v-model="modalDeleteOptions.open"
                width="450px"
                title="Eliminar Supervisor"
                subtitle="¿Está seguro de remover al supervisor?"
                @onConfirm="confirmDeleteSupervisor"
                @onCancel="modalDeleteOptions.open = false"
            />
        </v-card>
    </section>
</template>

<script>
import SupervisorFormModal from "./SupervisorFormModal";
import SupervisorCargaMasivaModal from "../../components/Supervisores/SupervisorCargaMasivaModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";

export default {
    components: {SupervisorFormModal, SupervisorCargaMasivaModal, DialogConfirm},
    data() {
        return {
            dataTable: {
                endpoint: `/supervisores/search`,
                ref: 'supervisoresTable',
                headers: [
                    {text: "Módulo", value: "modulo", align: 'start', sortable: false},
                    {text: "Área asignada", value: "area", align: 'start', sortable: false},
                    {text: "Supervisor", value: "nombre", align: 'start', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                ],
            },
            modalOptions: {
                ref: 'SupervisorFormModal',
                open: false,
                base_endpoint: '/supervisores',
                resource: 'Supervisor',
                confirmLabel: 'Guardar',
            },
            modalSupervisorCargaMasiva: {
                ref: 'SupervisorCargaMasivaModal',
                open: false,
                base_endpoint: '/supervisores',
                resource: '',
                confirmLabel: 'Guardar',
                title: 'Subida Masiva de Supervisores',
                hideCancelBtn: true,
                hideConfirmBtn: true,
            },
            modalDeleteOptions: {
                open: false,
            },
            del_usuario: null,
            filters: {
                q: null,
                module: null,
                areas: [],
            },
            selects: {
                modules: [],
                areas: [],
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects()
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/supervisores/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data
                    // vue.selects.carreras = data.data.carreras
                })
        },
        getAreas() {
            let vue = this
            vue.filters.areas = []
            vue.selects.areas = []
            if (!vue.filters.module) return false
            let url = `/supervisores/get-areas/${vue.filters.module}?type=only_selected`

            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.areas = data.data
                })

        },
        deleteSupervisor(usuario) {
            let vue = this
            vue.del_usuario = usuario
            vue.modalDeleteOptions.open = true
        },
        confirmDeleteSupervisor() {
            let vue = this
            let url = `/supervisores/delete-supervisor`
            const data = {
                usuario_id: vue.del_usuario.usuario_id,
                criterio_id: vue.del_usuario.area_id,
            }

            vue.$http.post(url, data)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    vue.refreshDefaultTable(vue.dataTable, vue.filters)
                    vue.del_usuario = null
                    vue.modalDeleteOptions.open = false
                })
        }

    }
}
</script>
