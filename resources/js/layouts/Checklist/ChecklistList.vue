<template>
    <section class="section-list">

        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Checklists
                <v-spacer/>
                <DefaultActivityButton :label="'Subida masiva'" @click="modal.subida_masiva= true"/>
                <DefaultModalButton :label="'Checklist'" @click="
                                abrirModalCreateEditChecklist({ id: 0, title: '', description: '', active: true, checklist_actividades: [], courses: [] })
                            "/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por título o descripción..."
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @edit="abrirModalCreateEditChecklist($event)"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar Checklist')"
            />
            <!-- @alumnos="openFormModal(modalOptions, $event, 'ver_alumnos', 'Alumnos')" -->

        </v-card>
        <StepperSubidaMasiva
            urlPlantilla="/templates/Plantilla_Checklist.xlsx"
            urlSubida="/entrenamiento/checklists/import"
            v-model="modal.subida_masiva"
            @onClose="closeModalSubidaMasiva"
        />

        <ModalCreateEditChecklist
            ref="ModalCreateEditChecklist"
            v-model="modal.crear_editar_checklist"
            :width="'65%'"
            @onClose="closeModalCreateEditChecklist"
            @onConfirm="saveChecklist"
            :checklist="dataModalChecklist"
        />

        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />

    </section>

</template>

<script>

import ModalCreateEditChecklist from "../../components/Entrenamiento/Checklist/ModalCreateEditChecklist.vue";
import ModalAsignarChecklistCurso from "../../components/Entrenamiento/Checklist/ModalAsignarChecklistCurso.vue";

import StepperSubidaMasiva from "../../components/SubidaMasiva/StepperSubidaMasiva.vue";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";

export default {
    components: {
        ModalCreateEditChecklist,
        ModalAsignarChecklistCurso,
        StepperSubidaMasiva,
        DefaultDeleteModal
    },
    mounted() {
        let vue = this;
    },
    data() {
        return {
            dataTable: {
                endpoint: '/entrenamiento/checklists/search',
                ref: 'ChecklistTable',
                headers: [
                    {text: "Título", value: "title", align: 'start', sortable: true},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                    // {
                    //     text: "Eliminar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
                ],
            },

            filters: {
                q: null
            },
            btn_reload_data: false,
            mostrarFiltros: true,
            modal: {
                crear_editar_checklist: false,
                ver_items: false,
                asignar: false,
                subida_masiva: false
            },
            modalDeleteOptions: {
                ref: 'ChecklistDeleteModal',
                open: false,
                base_endpoint: '/checklists',
                endpoint: '',
            },
            dataModalChecklist: {},
            dataModalVerItems: {},
            checklists: [],
            txt_filter_checklist: "",
            file: null,
        }
    },
    methods: {
        async closeModalSubidaMasiva() {
            let vue = this;
            vue.modal.subida_masiva = false;

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
        },
        async abrirModalCreateEditChecklist(checklist) {
            let vue = this;
            vue.dataModalChecklist = checklist;
            await vue.$refs.ModalCreateEditChecklist.resetValidation()

            vue.$refs.ModalCreateEditChecklist.setActividadesHasErrorProp()

            vue.modal.crear_editar_checklist = true;
        },
        saveChecklist() {
            let vue = this;
            // console.log(vue.dataModalChecklist);
            this.showLoader()
            vue.$http.post(`/entrenamiento/checklists/save_checklist`, vue.dataModalChecklist)
                .then((res) => {
                    // console.log(res);
                    vue.closeModalCreateEditChecklist();
                    vue.refreshDefaultTable(vue.dataTable, vue.filters);
                    // $("#pageloader").fadeOut();
                    this.hideLoader()
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });
        },
        async closeModalCreateEditChecklist() {
            let vue = this;
            // await vue.getData();
            vue.$refs.ModalCreateEditChecklist.resetValidation()
            vue.dataModalChecklist = {};
            vue.modal.crear_editar_checklist = false;
        },
        closeModalAsignarChecklistCurso() {
            let vue = this;
            vue.modal.asignar = false;
        },
    }

};
</script>
