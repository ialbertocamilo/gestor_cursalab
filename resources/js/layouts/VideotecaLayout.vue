<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Videoteca
                <v-spacer/>
                <v-btn
                    elevation="0"
                    class="mx-2"
                    @click="openTags"
                >
                    <v-icon v-text="'mdi-notebook-edit'"/>
                    Tags
                </v-btn>
                <v-btn
                    elevation="0"
                    class="mx-2"
                    @click="openCategorias"
                >
                    <v-icon v-text="'mdi-notebook-edit'"/>
                    Categorías
                </v-btn>
                <DefaultModalButton label="Nuevo"
                                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>

        <v-card elevation="0" class="mb-4">
        <!--     <v-card-text class="pb-0">
                <v-row>
                    <v-col cols="12" md="12" lg="12">
                        <v-btn
                            elevation="0"
                            color="primary"
                            class="mx-2"
                            @click="create"
                        >
                            <v-icon v-text="'mdi-plus'"/>
                            Nuevo Título
                        </v-btn>
                        <v-btn
                            elevation="0"
                            class="mx-2"
                            @click="openTags"
                        >
                            <v-icon v-text="'mdi-notebook-edit'"/>
                            Tags
                        </v-btn>
                        <v-btn
                            elevation="0"
                            class="mx-2"
                            @click="openCategorias"
                        >
                            <v-icon v-text="'mdi-notebook-edit'"/>
                            Categorías
                        </v-btn>
                    </v-col>
                </v-row>
            </v-card-text> -->
            <v-card-text class="--pt-0">
               
                <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event)"
                          @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar')"
			  @logs="
		                openFormModal(
		                    modalLogsOptions,
		                    $event,
		                    'logs',
		                    `Logs de Videos - ${$event.title}`
		                )
		            "
                />
            
            </v-card-text>
        </v-card>

        <ModalTags
            ref="modalTags"
            :modal-data="modalTags"
            @onClose="closeTagModal"/>
        <ModalCategorias
            ref="modalCategorias"
            :modal-data="modalCategorias"
            @onClose="closeCategoriasModal"/>

        <ModalCreateEditVideoteca width="60vw"
                              :ref="modalOptions.ref"
                              :options="modalOptions"
                              @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalOptions)"
            />

        <DefaultStatusModal :options="modalStatusOptions"
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
            model_type="App\Models\Videoteca"
            :ref="modalLogsOptions.ref"
            @onCancel="closeSimpleModal(modalLogsOptions)"
        />
    </section>
</template>

<script>
import ModalCreateEditVideoteca from "../components/Videoteca/ModalCreateEditVideoteca.vue";
import ModalTags from "../components/Videoteca/ModalTags.vue";
import ModalCategorias from "../components/Videoteca/ModalCategorias";

import DefaultStatusModal from "./Default/DefaultStatusModal";
import DefaultDeleteModal from "./Default/DefaultDeleteModal";

import LogsModal from "../components/globals/Logs";

export default {
    components: {
        ModalCreateEditVideoteca,
        ModalTags,
        ModalCategorias,
        DefaultStatusModal,
        DefaultDeleteModal,
        LogsModal
    },
    data() {
        return {
            dataTable: {
                endpoint: '/videoteca/search',
                ref: 'VideotecaTable',
                headers: [
                    {text: "Media", value: "image", align: 'center', sortable: false},
                    {text: "Título", value: "title"},
                    {text: "Tags", value: "tags", align: 'center', sortable: false},
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
                ]
            },
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalOptions: {
                // ref: 'VideotecaFormModal',
                ref: 'ModalCreateEditVideoteca',
                open: false,
                base_endpoint: '/videoteca',
                resource: 'Videoteca',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'VideotecaStatusModal',
                open: false,
                base_endpoint: '/videoteca',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'VideotecaDeleteModal',
                open: false,
                base_endpoint: '/videoteca',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
            btn_reload_data: false,
            mostrarFiltros: true,
            modalData: {
                open: false,
                modules: [],
                categories: [],
                tags: []
            },
            formResource: {},
            dataModal: {},
            dataModalVerItems: {},
            // videoteca: [],
            // paginate: {page: 1, total_paginas: 1},
            // txt_filter: "",
            file: null,
            deleteItem: null,
            // modalDelete: {
            //     open: false
            // },
            modalTags: {
                open: false
            },
            modalCategorias: {
                open: false
            },
            filters: {
                q: '',
            },
        };
    },
    mounted() {
        let vue = this;
        // vue.getData();
    },

    methods: {
        openTags() {
            let vue = this;
            vue.$refs.modalTags.getData()
            vue.modalTags.open = true;
        },
        openCategorias() {
            let vue = this;
            vue.$refs.modalCategorias.getData()
            vue.modalCategorias.open = true;
        },
        closeTagModal() {
            let vue = this;
            vue.modalTags.open = false;
            vue.getData();
        },
        closeCategoriasModal() {
            let vue = this;
            vue.modalCategorias.open = false;
            vue.getData();
        },
        // clearAndGetData() {
        //     let vue = this;
        //     vue.txt_filter = "";
        //     vue.getData();
        // },
        getData() {

        },
        // cambiar_pagina(page) {
        //     let vue = this;
        //     vue.getData(page);
        // },
        edit(recurso) {
            let vue = this;
            this.showLoader()
            let url = `/videoteca/show/${recurso.id}`
            vue.$http.get(url)
                .then((res) => {
                    vue.formResource = res.data.videoteca;
                    vue.modalData.open = true;
                    this.hideLoader()
                    if (vue.$refs.modalCreateEdit)
                        vue.$refs.modalCreateEdit.resetForm()
                })
                .catch((err) => {
                    this.hideLoader()
                    console.log(err)
                })
        },
        openDeleteModal(recurso) {
            let vue = this;
            vue.modalDelete.open = true
            vue.deleteItem = recurso
        },
        deleteResource() {
            let vue = this;
            let url = `/videoteca/delete/${vue.deleteItem.id}`
        },
        // openDeleteModal(recurso) {
        //     let vue = this;
        //     vue.modalDelete.open = true
        //     vue.deleteItem = recurso
        // },
        // deleteResource() {
        //     let vue = this;
        //     let url = `/videoteca/delete/${vue.deleteItem.id}`

        //     vue.$http.delete(url)
        //         .then(() => {
        //             vue.getData();
        //         })
        //     vue.modalDelete.open = false
        //     vue.deleteItem = null
        // },
        // create() {
        //     let vue = this;
        //     vue.formResource = {
        //         id: 0,
        //         title: '',
        //         description: '',
        //         modules: [],
        //         tags: [],
        //         active: false,
        //         category_id: null,
        //         media_type: 'youtube',
        //         media_video: '',
        //         preview: null,
        //         media: null,
        //     };
        //     vue.modalData.open = true;
        //     if (vue.$refs.modalCreateEdit)
        //         vue.$refs.modalCreateEdit.resetForm()

        // },
        // async closeModalCreateEditChecklist() {
        //     let vue = this;
        //     await vue.getData();
        //     await vue.$refs.modalTags.getData();
        //     vue.dataModal = {};
        //     vue.modalData.open = false;
        // },
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
