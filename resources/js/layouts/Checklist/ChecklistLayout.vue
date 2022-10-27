<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Checklist
                <v-spacer/>
            </v-card-title>
        </v-card>
        <v-card elevation="0">
            <v-card-text class="pb-0">
                <v-row>
                    <v-col cols="12" md="12" lg="12">
                        <v-btn
                            elevation="0"
                            color="primary"
                            class="mx-1"
                            @click="
                                abrirModalCreateEditChecklist({ id: 0, title: 'Título', description: 'Descripción', active: true, checklist_actividades: [], cursos: [] })
                            "
                        >
                            <v-icon>mdi-plus</v-icon>
                            Checklist
                        </v-btn>

                        <v-btn @click="modal.subida_masiva= true">
                            Subida masiva
                        </v-btn>
                        <StepperSubidaMasiva
                            urlPlantilla="/templates/Plantilla_Checklist.xlsx"
                            urlSubida="/entrenamiento/checklist/import"
                            v-model="modal.subida_masiva"
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
                                v-model="txt_filter_checklist"
                                label="Filtrar por título o descripción"
                                clearable
                                @keyup.enter="getData"
                                @click:clear="clearAndGetData"
                            ></v-text-field>
                        </v-col>
                        <v-col cols="12" md="3" lg="3">
                            <v-btn class="mx-1" @click="getData" :disabled="btn_reload_data"> Filtrar</v-btn>
                            <v-btn icon class="mx-1" @click="getData" :disabled="btn_reload_data">
                                <v-icon> mdi-refresh</v-icon>
                            </v-btn>
                        </v-col>
                    </v-row>
                </v-expand-transition>
            </v-card-text>
            <v-card-text class="pt-0">
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
                    <th class="text-left" style="width: 75%">Checklist</th>
                    <th class="text-center" style="width: 15%">Estado</th>
                    <th class="text-center" style="width: 15%">Acciones</th>
                    </thead>
                    <tbody>
                    <tr v-if="checklists.length === 0 && btn_reload_data">
                        <td class="text-center text-h6" colspan="3" style="color: #b0bec5">
                            Cargando
                            <v-progress-circular indeterminate color="#B0BEC5"></v-progress-circular>
                        </td>
                    </tr>
                    <tr v-if="checklists.length === 0 && !btn_reload_data">
                        <td class="text-center text-h6" colspan="4" style="color: #b0bec5">
                            No se encontraron resultados
                        </td>
                    </tr>
                    <tr v-else v-for="checklist in checklists" :key="checklist.id">
                        <td class="text-left">
                            <v-list-item two-line>
                                <v-list-item-content>
                                    <v-list-item-title style="width: 100px">{{
                                            checklist.title
                                        }}
                                    </v-list-item-title>
                                    <v-list-item-subtitle style="width: 100px">{{
                                            checklist.description
                                        }}
                                    </v-list-item-subtitle>
                                </v-list-item-content>
                            </v-list-item>
                        </td>
                        <td class="text-center">
                            <v-list-item>
                                <v-list-item-content>
                                    <v-list-item-title>
                                        <v-chip :color="checklist.active ? 'green': 'red' " class="white--text">
                                            {{ checklist.active ? 'Activo' : 'Inactivo' }}
                                        </v-chip>
                                    </v-list-item-title>
                                </v-list-item-content>
                            </v-list-item>
                        </td>
                        <td class="d-flex justify-space-around">
                            <div
                                class="d-flex justify.center"
                                style="flex-direction: column; align-items: center"
                            >
                                <v-btn large icon class="ml-1">
                                    <v-icon color="primary" @click="abrirModalCreateEditChecklist(checklist)">
                                        mdi-pencil
                                    </v-icon>
                                </v-btn>
                                <p class="mb-0">Editar</p>
                            </div>
                        </td>
                    </tr>
                    <ModalCreateEditChecklist
                        v-model="modal.crear_editar_checklist"
                        :width="'95%'"
                        @onClose="closeModalCreateEditChecklist"
                        @onConfirm="saveChecklist"
                        :checklist="dataModalChecklist"
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
    </section>
</template>

<script>
import ModalCreateEditChecklist from "../../components/Entrenamiento/Checklist/ModalCreateEditChecklist.vue";
import ModalAsignarChecklistCurso from "../../components/Entrenamiento/Checklist/ModalAsignarChecklistCurso.vue";

import StepperSubidaMasiva from "../../components/SubidaMasiva/StepperSubidaMasiva.vue";

export default {
    components: {
        ModalCreateEditChecklist,
        ModalAsignarChecklistCurso,
        StepperSubidaMasiva
    },
    data() {
        return {
            headers: [
                {
                    text: 'Titulo',
                    align: 'start',
                    value: 'title',
                },
                {
                    text: 'Estado',
                    align: 'center',
                    value: 'active'
                },
                {
                    text: 'Acciones',
                    align: 'center',
                    value: 'acciones'
                }
            ],
            btn_reload_data: false,
            mostrarFiltros: true,
            modal: {
                crear_editar_checklist: false,
                ver_items: false,
                asignar: false,
                subida_masiva: false
            },
            dataModalChecklist: {},
            dataModalVerItems: {},
            checklists: [],
            paginate: {page: 1, total_paginas: 1},
            txt_filter_checklist: "",
            file: null,
        };
    },
    mounted() {
        let vue = this;
        vue.getData();
    },
    computed: {},
    methods: {
        clearAndGetData() {
            let vue = this;
            vue.txt_filter_checklist = "";
            vue.getData();
        },
        getData() {
            let vue = this;
            vue.btn_reload_data = true;
            vue.checklists = [];

            vue.$http.post("/entrenamiento/checklist/listar_checklist?page=" + vue.paginate.page, {
                filtro: vue.txt_filter_checklist
            })
                .then((res) => {
                    setTimeout(() => {
                        vue.checklists = res.data.data;
                        vue.paginate.total_paginas = res.data.lastPage;
                        vue.btn_reload_data = false;
                    }, 200);
                })
                .catch((err) => {
                    console.log(err);
                    setTimeout(() => {
                        vue.checklists = [];
                        vue.btn_reload_data = false;
                    }, 200);
                });
        },
        cambiar_pagina(page) {
            let vue = this;
            vue.getData(page);
        },
        abrirModalCreateEditChecklist(checklist) {
            let vue = this;
            vue.dataModalChecklist = checklist;
            vue.modal.crear_editar_checklist = true;
        },
        saveChecklist() {
            let vue = this;
            console.log(vue.dataModalChecklist);
            vue.$http.post(`/entrenamiento/checklist/save_checklist`, vue.dataModalChecklist)
                .then((res) => {
                    console.log(res);
                    vue.closeModalCreateEditChecklist();
                })
                .catch((err) => {
                    console.log(err);
                });
        },
        async closeModalCreateEditChecklist() {
            let vue = this;
            await vue.getData();
            vue.dataModalChecklist = {};
            vue.modal.crear_editar_checklist = false;
        },
        closeModalAsignarChecklistCurso() {
            let vue = this;
            vue.modal.asignar = false;
        },
        async closeModalSubidaMasiva() {
            let vue = this;
            await vue.getData();
            vue.modal.subida_masiva = false;
        },

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
