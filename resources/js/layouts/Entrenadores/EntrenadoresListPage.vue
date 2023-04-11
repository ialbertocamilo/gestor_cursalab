<template>
    <section class="section-list">
        <DefaultFilter
            v-model="open_advanced_filter"
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
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Entrenadores
                <v-spacer />
                <!-- <DefaultActivityButton :label="'Actividad'"/> -->
                <DefaultModalButton
                    :label="'Asignar alumnos'"
                    @click="modal.asignar_usuarios = true"
                />
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start">
                    <v-col cols="4">
                        <DefaultInput
                            clearable
                            dense
                            v-model="filters.q"
                            label="Buscar entrenadores por nombre o DNI..."
                            append-icon="mdi-magnify"
                            @clickAppendIcon="
                                refreshDefaultTable(dataTable, filters, 1)
                            "
                            @onEnter="
                                refreshDefaultTable(dataTable, filters, 1)
                            "
                        />
                    </v-col>
                    <v-col cols="4">
                        <!-- <DefaultButton label="Filtros"
                                       icon="mdi-filter"
                                       @click="open_advanced_filter = !open_advanced_filter"/> -->
                    </v-col>
                    <v-col cols="4">
                        <DefaultInput
                            clearable
                            dense
                            v-model="txt_filtrar_alumnos"
                            label="Buscar alumnos por DNI"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="buscarAlumno"
                            @onEnter="buscarAlumno"
                        />
                        <!--                        <v-text-field-->
                        <!--                            outlined-->
                        <!--                            dense-->
                        <!--                            hide-details="auto"-->
                        <!--                            v-model="txt_filtrar_alumnos"-->
                        <!--                            label="Buscar alumnos por DNI"-->
                        <!--                            clearable-->
                        <!--                            @keyup.enter="buscarAlumno"-->
                        <!--                        >-->
                        <!--                            <template v-slot:append>-->
                        <!--                                <v-tooltip attach="" bottom>-->
                        <!--                                    <template v-slot:activator="{ on, attrs }">-->
                        <!--                                        <v-icon-->
                        <!--                                            v-bind="attrs"-->
                        <!--                                            v-on="on"-->
                        <!--                                        >-->
                        <!--                                            mdi-magnify-->
                        <!--                                        </v-icon>-->
                        <!--                                    </template>-->
                        <!--                                    <span>Tooltip</span>-->
                        <!--                                </v-tooltip>-->
                        <!--                            </template>-->
                        <!--                        </v-text-field>-->
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @alumnos="abrirModalVerAlumnos($event)"
                @logs="
                    openFormModal(
                        modalLogsOptions,
                        $event,
                        'logs',
                        `Logs del Entrenadores - ${$event.name}`
                    )
                "
            />
            <!-- @alumnos="openFormModal(modalOptions, $event, 'ver_alumnos', 'Alumnos')" -->
            <EntrenadorVerAlumnosModal
                :ref="modalOptions.ref"
                :options="modalOptions"
                width="60vw"
            />
        </v-card>
        <StepperSubidaMasiva
            urlPlantilla="/templates/Plantilla-Asignar_Entrenador.xlsx"
            urlSubida="/entrenamiento/entrenadores/asignar_masivo"
            v-model="modal.asignar_usuarios"
            @onClose="closeModalSubidaMasiva"
            typeForm="asigna_alumnos"
        />

        <ModalVerAlumnos
            v-model="modal.asignar_ver_alumnos"
            :width="'60%'"
            @close="closeModalVerAlumnos"
            @showSnackbar="mostrarSnackBar($event)"
            @refreshTable="refreshDefaultTable(dataTable, filters, 1)"
            :entrenador="dataModalVerAlumnos"
            :isMaster="isMaster"
        />

        <ModalFiltroUsuario
            :alumno="filtroAlumnoTemp"
            :modal-data="modalDataModalFiltroaLumno"
            @onClose="closeModalFiltroUsuario"
        />
        <LogsModal
            :options="modalLogsOptions"
            width="55vw"
            :model_id="null"
            model_type="App\Models\EntrenadorUsuario"
            :ref="modalLogsOptions.ref"
            @onCancel="closeSimpleModal(modalLogsOptions)"
        />

        <v-snackbar
            v-model="snackbar.show"
            :timeout="3500"
            top
            :color="snackbar.color"
            elevation="24"
            transition="fade-transition"
            class="text-center"
        >
            {{ snackbar.text }}
        </v-snackbar>
    </section>
</template>

<script>
import EntrenadorVerAlumnosModal from "./EntrenadorVerAlumnosModal";
import ModalVerAlumnos from "../../components/Entrenamiento/Entrenadores/ModalVerAlumnos.vue";
import ModalFiltroUsuario from "../../components/Entrenamiento/Entrenadores/ModalFiltroUsuario";
import StepperSubidaMasiva from "../../components/SubidaMasiva/StepperSubidaMasiva.vue";
import LogsModal from "../../components/globals/Logs";

export default {
    props: ["roles"],
    components: {
        EntrenadorVerAlumnosModal,
        StepperSubidaMasiva,
        ModalVerAlumnos,
        ModalFiltroUsuario,
        LogsModal
    },
    mounted() {
        let vue = this;
        vue.validateRoleMaster();
    },
    data() {
        return {
            dataTable: {
                endpoint: "/entrenamiento/entrenadores/search",
                ref: "EntrenadorTable",
                headers: [
                    {
                        text: "DNI",
                        value: "document",
                        align: "start",
                        sortable: false
                    },
                    { text: "Entrenador", value: "name", align: "start" },
                    {
                        text: "Cant. Alumnos (Activos)",
                        value: "alumnos_count",
                        align: "center",
                        sortable: false
                    },
                    {
                        text: "Opciones",
                        value: "actions",
                        align: "center",
                        sortable: false
                    }
                ],
                actions: [
                    {
                        text: "Alumnos",
                        icon: "mdi mdi-eye",
                        type: "action",
                        method_name: "alumnos"
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
            modalOptions: {
                ref: "ModalVerAlumnos",
                open: false,
                base_endpoint: "/entrenamiento/entrenadores",
                confirmLabel: "Guardar"
            },
            filters: {
                q: null
            },
            modal: {
                asignar_usuarios: false,
                asignar_ver_alumnos: false
            },
            dataModalVerAlumnos: {},
            isMaster: false,
            txt_filtrar_alumnos: "",
            filtroAlumnoTemp: {
                dni: "",
                nombre: "",
                cargo: "",
                bnotica: "",
                grupo_nombre: "",
                checklists: [],
                entrenador: ""
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalDataModalFiltroaLumno: {
                open: false,
                title: ""
            },
            snackbar: {
                show: false,
                text: "",
                color: "success"
            }
        };
    },
    methods: {
        async closeModalSubidaMasiva() {
            let vue = this;
            // await vue.getData();
            vue.modal.asignar_usuarios = false;
            vue.refreshDefaultTable(vue.dataTable, vue.filters, 1);
        },
        async closeModalVerAlumnos() {
            let vue = this;
            // await vue.getData();
            vue.dataModalVerAlumnos = {};
            vue.modal.asignar_ver_alumnos = false;
        },
        abrirModalVerAlumnos(entrenador) {
            let vue = this;
            vue.dataModalVerAlumnos = entrenador;
            vue.modal.asignar_ver_alumnos = true;
        },
        mostrarSnackBar(data) {
            let vue = this;
            vue.snackbar.show = true;
            vue.snackbar.text = data.msg;
            vue.snackbar.color = data.color;
        },
        validateRoleMaster() {
            let vue = this;
            if (vue.roles) {
                vue.isMaster = true;
            }
        },
        buscarAlumno() {
            let vue = this;
            if (!vue.txt_filtrar_alumnos) return;
            vue.$http
                .get(
                    `/entrenamiento/alumno/filtrar_alumno/${vue.txt_filtrar_alumnos}`
                )
                .then(res => {
                    let response = res.data;
                    if (response.error) {
                        let errorData = {
                            msg: response.msg,
                            color: "warning"
                        };
                        vue.mostrarSnackBar(errorData);
                    } else {
                        vue.filtroAlumnoTemp = response.alumno;
                        vue.modalDataModalFiltroaLumno.open = true;
                        vue.modalDataModalFiltroaLumno.title = `Alumno: ${response.alumno.document} - ${response.alumno.name}`;
                    }
                });
        },
        closeModalFiltroUsuario() {
            let vue = this;
            vue.modalDataModalFiltroaLumno.open = false;
            vue.modalDataModalFiltroaLumno.title = ``;
            vue.filtroAlumnoTemp = {
                dni: "",
                nombre: "",
                cargo: "",
                bnotica: "",
                grupo_nombre: "",
                checklists: [],
                entrenador: ""
            };
        }
    }
};
</script>
