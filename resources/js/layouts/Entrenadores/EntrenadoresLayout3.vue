<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Entrenadores
                <v-spacer/>
            </v-card-title>
        </v-card>
            <v-card elevation="0">
                <v-card-text>
                    <v-row>
                        <v-col cols="12" md="12" lg="12">
                            <v-btn @click="modal.asignar_usuarios = true" color="primary">
                                <v-icon class="mr-1">
                                    mdi-plus
                                </v-icon>
                                Asignar usuarios
                            </v-btn>
                            <StepperSubidaMasiva
                                urlPlantilla="/templates/Plantilla-Asignar_Entrenador.xlsx"
                                urlSubida="/entrenamiento/entrenador/asignar_masivo"
                                v-model="modal.asignar_usuarios"
                                @onClose="closeModalSubidaMasiva"
                            />
                        </v-col>
                    </v-row>
                    <v-expand-transition>
                        <v-row v-show="mostrarFiltros" class="mx-3 mt-3">
                            <v-col cols="12" md="3" lg="3">
                                <v-text-field
                                    outlined
                                    dense
                                    hide-details="auto"
                                    v-model="txt_filtrar_entrenadores"
                                    label="Filtrar por nombre o DNI"
                                    clearable
                                    @keyup.enter="getData"
                                    @click:clear="clearAndGetData"
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" md="3" lg="3">
                                <v-btn icon class="mx-1" @click="getData" :disabled="btn_reload_data">
                                    <v-icon> mdi-refresh</v-icon>
                                </v-btn>
                                <v-btn class="mx-1" @click="getData" :disabled="btn_reload_data"> Filtrar</v-btn>
                            </v-col>
                            <v-col cols="12" md="1" lg="1">

                            </v-col>
                            <v-col cols="12" md="5" lg="5" class="d-flex justify-end">
                                <v-text-field
                                    outlined
                                    dense
                                    hide-details="auto"
                                    v-model="txt_filtrar_alumnos"
                                    label="Buscar alumnos por DNI"
                                    clearable
                                    @keyup.enter="buscarAlumno"
                                >
                                    <template v-slot:append>
                                        <v-tooltip attach="" bottom>
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-icon
                                                    v-bind="attrs"
                                                    v-on="on"
                                                >
                                                    mdi-information
                                                </v-icon>
                                            </template>
                                            <span>Tooltip</span>
                                        </v-tooltip>
                                    </template>
                                </v-text-field>
                                <v-btn class="mx-1" @click="buscarAlumno">Buscar</v-btn>
                                <ModalFiltroUsuario
                                    :alumno="filtroAlumnoTemp"
                                    :modal-data="modalDataModalFiltroaLumno"
                                    @onClose="closeModalFiltroUsuario"
                                />
                            </v-col>
                        </v-row>
                    </v-expand-transition>
                </v-card-text>
                <v-card-text>
                    <div class="text-center pt-2">
                        <v-pagination
                            circle
                            :total-visible="7"
                            @input="cambiar_pagina"
                            v-model="paginate.page"
                            :length="paginate.total_paginas"
                        ></v-pagination>
                    </div>
                    <table class="table table-hover">
                        <thead class="bg-dark">
                        <th class="text-left">DNI</th>
                        <th class="text-left">Entrenador</th>
                        <th class="text-center">Cant. Alumnos (Activos)</th>
                        <th class="text-center">Acciones</th>
                        </thead>
                        <tbody>
                        <tr v-if="entrenadores.length == 0 && btn_reload_data">
                            <td class="text-center text-h6" colspan="4" style="color: #b0bec5">
                                Cargando
                                <v-progress-circular indeterminate color="#B0BEC5"></v-progress-circular>
                            </td>
                        </tr>
                        <tr v-if="entrenadores.length == 0 && !btn_reload_data">
                            <td class="text-center text-h6" colspan="4" style="color: #b0bec5">
                                No se encontraron resultados
                            </td>
                        </tr>
                        <tr v-else v-for="(entrenador) in entrenadores" :key="entrenador.dni">
                            <td class="text-left">{{ entrenador.dni }}</td>
                            <td class="text-left">{{ entrenador.nombre }}</td>
                            <td class="text-center">{{ entrenador.alumnos.length }}</td>
                            <td class="text-center">
                                <v-tooltip top attach="">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-icon
                                            color="primary"
                                            v-bind="attrs"
                                            v-on="on"
                                            @click="abrirModalVerAlumnos(entrenador)"
                                        >
                                            mdi-eye
                                        </v-icon>
                                    </template>
                                    <span>Ver alumnos</span>
                                </v-tooltip>
                            </td>
                        </tr>
                        <ModalVerAlumnos
                            v-model="modal.asignar_ver_alumnos"
                            :width="'60%'"
                            @close="closeModalVerAlumnos"
                            @showSnackbar="mostrarSnackBar($event)"
                            :entrenador="dataModalVerAlumnos"
                            :isMaster="isMaster"
                        />
                        </tbody>
                    </table>
                    <div class="text-center pt-2">
                        <v-pagination
                            circle
                            :total-visible="7"
                            @input="cambiar_pagina"
                            v-model="paginate.page"
                            :length="paginate.total_paginas"
                        ></v-pagination>
                    </div>
                </v-card-text>
            </v-card>
            <v-snackbar
                v-model="snackbar.show"
                :timeout="3500"
                top
                :color="snackbar.color"
                elevation="24"
                transition="fade-transition"
            >
                {{ snackbar.text }}
            </v-snackbar>
    </section>
</template>

<script>
import ModalAsignar from "../../components/Entrenamiento/Entrenadores/ModalAsignar.vue";
import ModalVerAlumnos from "../../components/Entrenamiento/Entrenadores/ModalVerAlumnos.vue"
import StepperSubidaMasiva from "../../components/SubidaMasiva/StepperSubidaMasiva.vue";

import ModalFiltroUsuario from "../../components/Entrenamiento/Entrenadores/ModalFiltroUsuario";

export default {
    props: ['roles'],
    components: {
        ModalAsignar,
        ModalVerAlumnos,
        StepperSubidaMasiva,
        ModalFiltroUsuario
    },
    data() {
        return {
            snackbar: {
                show: false,
                text: '',
                color: 'success'
            },
            btn_reload_data: false,
            mostrarFiltros: true,
            modal: {
                asignar_usuarios: false,
                asignar_ver_alumnos: false
            },
            dataModalVerAlumnos: {},
            entrenadores: [],
            paginate: {page: 1, total_paginas: 1},
            txt_filtrar_entrenadores: "",
            txt_filtrar_alumnos: "",
            isMaster: false,
            filtroAlumnoTemp: {
                dni: '',
                nombre: '',
                cargo: '',
                bnotica: '',
                grupo_nombre: '',
                checklists: [],
                entrenador: ''
            },
            modalDataModalFiltroaLumno: {
                open: false,
                title: ''
            }
        };
    },
    mounted() {
        let vue = this;
        vue.validateRoleMaster();
        vue.getData();
    },
    computed: {},
    methods: {
        clearAndGetData() {
            let vue = this;
            vue.txt_filtrar_entrenadores = "";
            vue.getData();
        },
        getData() {
            let vue = this;
            vue.btn_reload_data = true;
            vue.entrenadores = [];

            vue.$http
                .post("/entrenamiento/entrenador/listar_entrenadores?page=" + vue.paginate.page, {
                    filtro: vue.txt_filtrar_entrenadores
                })
                .then((res) => {
                    setTimeout(() => {
                        vue.entrenadores = res.data.data;
                        vue.paginate.total_paginas = res.data.lastPage;
                        vue.btn_reload_data = false;
                    }, 200);
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        validateRoleMaster() {
            let vue = this
            let index = vue.roles.findIndex(role => role.name === 'Master')
            if (index !== -1) {
                vue.isMaster = true
            }
        },
        cambiar_pagina(page) {
            let vue = this;
            vue.getData(page);
        },
        async closeModalAsignar() {
            let vue = this;
            await vue.getData();
            vue.modal.asignar_usuarios = false;
        },
        abrirModalVerAlumnos(entrenador) {
            let vue = this;
            vue.dataModalVerAlumnos = entrenador;
            vue.modal.asignar_ver_alumnos = true;
        },
        async closeModalVerAlumnos() {
            let vue = this;
            await vue.getData();
            vue.dataModalVerAlumnos = {};
            vue.modal.asignar_ver_alumnos = false;
        },
        async closeModalSubidaMasiva() {
            let vue = this;
            await vue.getData();
            vue.modal.asignar_usuarios = false;

        },
        mostrarSnackBar(data) {
            let vue = this;
            vue.snackbar.show = true
            vue.snackbar.text = data.msg
            vue.snackbar.color = data.color
        },
        buscarAlumno() {
            let vue = this;
            vue.$http.get(`/entrenamiento/alumno/filtrar_alumno/${vue.txt_filtrar_alumnos}`)
                .then((res) => {
                    let response = res.data
                    if (response.error) {
                        let errorData = {
                            msg: response.msg,
                            color: 'warning'
                        }
                        vue.mostrarSnackBar(errorData)
                    } else {
                        vue.filtroAlumnoTemp = response.alumno
                        vue.modalDataModalFiltroaLumno.open = true
                        vue.modalDataModalFiltroaLumno.title = `Alumno: ${response.alumno.dni} - ${response.alumno.nombre}`
                    }
                })
        },
        closeModalFiltroUsuario() {
            let vue = this
            vue.modalDataModalFiltroaLumno.open = false
            vue.modalDataModalFiltroaLumno.title = ``
            vue.filtroAlumnoTemp = {
                dni: '',
                nombre: '',
                cargo: '',
                bnotica: '',
                grupo_nombre: '',
                checklists: [],
                entrenador: ''
            }
        }
    }
};
</script>

<style>
.v-application--wrap {
    min-height: 0;
}

v-text-field .v-input__control,
.v-text-field .v-input__slot,
.v-text-field fieldset {
    border-radius: 0 !important;
    border-color: #dee2e6;
}

.box-filtros {
    border: 1.5px solid;
    border-radius: 5px;
    color: #dee2e6;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 1s;
}

.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */
{
    opacity: 0;
}
</style>
