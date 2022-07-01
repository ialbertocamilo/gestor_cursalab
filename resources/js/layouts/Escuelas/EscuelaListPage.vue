<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs"/>
                <v-spacer/>
                <!--                <DefaultActivityButton-->
                <!--                    :label="'Actividad'"-->
                <!--                    @click="activity"/>-->
                <DefaultModalButton
                    :label="'Escuela'"
                    @click="openCRUDPage(`/modulos/${modulo_id}/escuelas/create`)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="4">
                        <DefaultInput
                            learable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                </v-row>
            </v-card-text>
            <!-- </v-card> -->
            <!--        Contenido-->
            <!-- <v-card flat class="elevation-0 mb-4"> -->
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @delete="deleteEscuela($event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @duplicate="openDuplicarModal($event)"
            />

            <DialogConfirm
                v-model="modalDeleteOptions.open"
                width="450px"
                title="Eliminar Escuela"
                subtitle="¿Está seguro de eliminar la escuela?"
                @onConfirm="confirmDelete"
                @onCancel="modalDeleteOptions.open = false"
            />

            <EscuelaValidacionesModal
                width="50vw"
                :ref="modalEscuelasValidaciones.ref"
                :options="modalEscuelasValidaciones"
                @onCancel="closeFormModal(modalEscuelasValidaciones)"
                :resource="delete_model"
            />

            <DefaultStatusModal
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalStatusOptions, dataTable, filters)"
            />
            <DuplicarCursos
                :ref="modalCursosDuplicar.ref"
                :modalCursosDuplicar="modalCursosDuplicar"
                @onCancel='closeFormModalDuplicarCursos'
            />
        </v-card>
    </section>
</template>


<script>
import EscuelaFormModal from "./EscuelaFormModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import EscuelaValidacionesModal from "./EscuelaValidacionesModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DuplicarCursos from './DuplicarCursos';

export default {
    props: ['modulo_id', 'modulo_name'],
    components: {EscuelaFormModal, EscuelaValidacionesModal, DialogConfirm, DefaultStatusModal, DuplicarCursos},
    data() {
        let vue = this
        return {
            breadcrumbs: [
                {title: 'Módulos', text: `${this.modulo_name}`, disabled: false, href: '/modulos'},
                {title: 'Escuelas', text: null, disabled: true, href: ''},
            ],
            dataTable: {
                endpoint: '/modulos/' + vue.modulo_id + '/escuelas/search',
                ref: 'escuelasTable',
                headers: [
                    {text: "Orden", value: "orden", align: 'center', model: 'Categoria'},
                    {text: "Portada", value: "image", align: 'center', sortable: false},
                    {text: "Nombres", value: "nombre"},
                    {text: "Modalidad", value: "modalidad"},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Cursos",
                        icon: 'fas fa-book',
                        type: 'route',
                        count: 'cursos_count',
                        route: 'cursos_route'
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'route',
                        route: 'edit_route'
                    },
                    {
                        text: "Duplicar",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'duplicate'
                    },
                    // {
                    //     text: "Eliminar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
                    // {
                    //     text: "Actualizar Estado",
                    //     icon: 'fa fa-circle',
                    //     type: 'action',
                    //     method_name: 'status'
                    // },
                ],
            },
            modalDeleteOptions: {
                open: false,
            },
            selects: {
                modules: []
            },
            filters: {
                q: '',
                module: null,
            },
            modalEscuelasValidaciones: {},
            modalEscuelasValidacionesDefault: {
                ref: 'TemaValidacionesModal',
                open: false,
                base_endpoint: '',
                hideConfirmBtn: false,
                hideCancelBtn: false,
                confirmLabel: 'Confirmar',
                cancelLabel: 'Cancelar',
                resource: 'TemasValidaciones',
            },
            delete_model: null,
            modalStatusOptions: {
                ref: 'EscuelaStatusModal',
                open: false,
                base_endpoint: '/modulos/' + vue.modulo_id + '/escuelas',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalCursosDuplicar: {
                categoria_id: 0,
                dialog: false,
                ref: 'CursosDuplicarModal',
            },
        }
    },
    mounted() {
        let vue = this
        // vue.getSelects();

        vue.filters.module = vue.modulo_id
    },
    methods: {
        getSelects() {
            let vue = this
            const url = `/escuelas/get-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules
                })
        },
        activity() {
            console.log('activity')
        },
        deleteEscuela(tema) {
            let vue = this
            vue.delete_model = tema
            vue.modalDeleteOptions.open = true
        },
        async cleanModalEscuelasValidaciones() {
            let vue = this
            await vue.$nextTick(() => {
                vue.modalEscuelasValidaciones = Object.assign({}, vue.modalEscuelasValidaciones, vue.modalEscuelasValidacionesDefault)
            })
        },
        confirmDelete() {
            let vue = this
            let url = `/modulos/${vue.modulo_id}/escuelas/${vue.delete_model.id}`

            vue.$http.delete(url)
                .then(({data}) => {
                    vue.showAlert(data.data.msg)
                    vue.refreshDefaultTable(vue.dataTable, vue.filters)
                    vue.delete_model = null
                    vue.modalDeleteOptions.open = false
                })
                .catch(async ({data}) => {
                    await vue.cleanModalEscuelasValidaciones()
                    vue.loadingActionBtn = false
                    vue.modalEscuelasValidaciones.hideConfirmBtn = true
                    vue.modalEscuelasValidaciones.cancelLabel = 'Entendido'
                    await vue.openFormModal(vue.modalEscuelasValidaciones, data, data.type, data.title)
                })
        },
        openDuplicarModal(event) {
            let vue = this;
            vue.showLoader();
            vue.modalCursosDuplicar.dialog = true;
            vue.modalCursosDuplicar.categoria_id = event.id;
            vue.$refs.CursosDuplicarModal.get_data();
        },
        closeFormModalDuplicarCursos() {
            let vue = this;
            vue.modalCursosDuplicar.dialog = false;
            vue.modalCursosDuplicar.categoria_id = 0;
        }
    }

}
</script>
