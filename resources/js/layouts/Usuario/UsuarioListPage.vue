<template>
    <section class="section-list ">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
            @cleanFilters="clearObject(filters)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row>
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            :items="selects.modules"
                            @onChange="setCarreras"
                            v-model="filters.module"
                            label="Módulos"
                            item-text="name"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultAutocomplete
                            clearable
                            placeholder="Seleccione una Carrera"
                            label="Carrera"
                            @onChange="setCiclos"
                            :items="selects.carreras"
                            v-model="filters.carrera"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultAutocomplete
                            placeholder="Seleccione una Ciclo"
                            label="Ciclo"
                            :items="selects.ciclos"
                            v-model="filters.ciclos"
                            multiple
                            :count-show-values="3"
                        />
                    </v-col>
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Usuarios
                <v-spacer/>
                <!--       <DefaultActivityButton
                          :label="'Actividad'"
                          @click="activity"/> -->

                <DefaultActivityButton :label="'Reinicios masivos'"
                                       @click="goToReiniciosMasivos"/>
                <DefaultModalButton
                    :label="'Usuario'"
                    @click="openFormModal(modalOptions, null, 'create')"/>
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
                            @onChange="refreshDefaultTable(dataTable, filters, 1); setCarreras()"
                            item-text="name"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre o DNI..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-end">
                        <DefaultButton
                            label="Ver Filtros"
                            icon="mdi-filter"
                            @click="open_advanced_filter = !open_advanced_filter"/>
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="openFormModal(modalOptions, $event, 'edit')"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Confirmación de cambio de estado')"
                @cursos="openFormModal(modalCursosOptions, $event, 'cursos', `CURSOS DE ${$event.nombre} - ${$event.dni}`)"
                @reset="openFormModal(modalReiniciosOptions, $event, 'cursos', `REINICIAR AVANCE DE ${$event.nombre}`)"
            />
            <UsuarioFormModal
                width="60vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />
            <UsuarioStatusModal
                :options="modalDeleteOptions"
                :ref="modalDeleteOptions.ref"
                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalDeleteOptions)"
            />
            <UsuarioReiniciosModal
                width="55vw"
                :ref="modalReiniciosOptions.ref"
                :options="modalReiniciosOptions"
                @onReinicioTotal="refreshDefaultTable(dataTable, filters)"
                @onCancel="closeFormModal(modalReiniciosOptions)"
            />
            <UsuarioCursosModal
                width="55vw"
                :ref="modalCursosOptions.ref"
                :options="modalCursosOptions"
                @onCancel="closeFormModal(modalCursosOptions)"
            />

            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalStatusOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import UsuarioFormModal from "./UsuarioFormModal";
import UsuarioStatusModal from "./UsuarioStatusModal";
import UsuarioCursosModal from "./UsuarioCursosModal";
import UsuarioReiniciosModal from "./UsuarioReiniciosModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";

export default {
    components: {UsuarioFormModal, UsuarioStatusModal, UsuarioCursosModal, UsuarioReiniciosModal, DefaultStatusModal},
    data() {
        return {
            dataTable: {
                endpoint: '/usuarios/search',
                ref: 'UsuarioTable',
                headers: [
                    // {text: "Módulo", value: "image", align: 'center', sortable: false},
                    {text: "Nombres y Apellidos", value: "nombre"},
                    // {text: "Documento", value: "dni", align: 'left', sortable: false},
                    // {text: "Carrera", value: "carrera", sortable: false},
                    // {text: "Ciclo", value: "ciclo_actual", align: 'center', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Reporte",
                        type: 'route',
                        icon: 'mdi mdi-file-document-multiple',
                        route: 'reporte_route',
                        route_type: 'external'
                    },

                    {
                        text: "Cursos",
                        icon: 'mdi mdi-notebook-multiple',
                        type: 'action',
                        method_name: 'cursos'
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                ],
                more_actions: [
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    {
                        text: "Reiniciar",
                        icon: 'fas fa-history',
                        type: 'action',
                        method_name: 'reset',
                        show_condition: 'pruebas_desaprobadas'
                    },
                ]
            },
            selects: {
                modules: [],
                carreras: [],
                ciclos: []
            },
            filters: {
                q: '',
                // module: param_modulo || null,
                module: null,
                carrera: null,
                ciclos: []
            },
            modalOptions: {
                ref: 'UsuarioFormModal',
                open: false,
                base_endpoint: '/usuarios',
                resource: 'Usuario',
                confirmLabel: 'Guardar',
            },
            modalDeleteOptions: {
                ref: 'UsuarioDeleteModal',
                open: false,
                base_endpoint: '/usuarios',
                contentText: '¿Desea eliminar este registro?',
            },
            modalCursosOptions: {
                ref: 'UsuarioCursosModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
            },
            modalReiniciosOptions: {
                ref: 'UsuarioReiniciosModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
            },
            modalStatusOptions: {
                ref: 'UsuarioStatusModal',
                open: false,
                base_endpoint: '/usuarios',
                contentText: '¿Desea cambiar de estado a este registro?',
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

            let uri = window.location.search.substring(1);
            let params = new URLSearchParams(uri);
            let param_modulo = params.get("modulo");

            const url = `/usuarios/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules

                    vue.filters.module = parseInt(param_modulo)

                    if (param_modulo)
                        this.setCarreras()
                        vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                })

        },
        reset(user) {
            let vue = this
            vue.consoleObjectTable(user, 'User to Reset')
        },
        goToReiniciosMasivos() {
            window.location.href = "/masivo/usuarios/index_reinicios";
        },
        activity() {
            console.log('activity')
        },
        setCarreras() {
            let vue = this
            vue.selects.ciclos = []
            vue.filters.ciclos = []
            vue.filters.carrera = null

            if (!vue.filters.module) {
                vue.selects.carreras = []
                return
            }

            vue.getCarrerasByModulo(vue.filters.module)
                .then(data => {
                    vue.selects.carreras = data
                })

        },
        setCiclos(){
            let vue = this
            vue.filters.ciclos = []

            if (!vue.filters.carrera) {
                vue.selects.ciclos = []
                return
            }

            vue.getCiclosByCarrera(vue.filters.carrera)
                .then(data => {
                    vue.selects.ciclos = data
                })
        },
    }

}
</script>
