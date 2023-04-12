<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Encuestas
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'"
                                       @click="activity"/> -->
                <DefaultModalButton
                    :label="'Encuesta'"
                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <!-- <v-col cols="4">
                        <DefaultSelect clearable dense
                                       :items="selects.modules"
                                       v-model="filters.module"
                                       label="Módulos"
                                       @onChange="refreshDefaultTable(dataTable, filters)"
                        />
                    </v-col> -->
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por título..."
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
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambiar de estado a las <b>encuestas</b>')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', `Eliminar encuesta: ${$event.titulo}` )"
            />

            <EncuestaFormModal
                width="45vw"
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

        </v-card>
    </section>
</template>


<script>
import EncuestaFormModal from "./EncuestaFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";

export default {
    components: {EncuestaFormModal, DefaultStatusModal, DefaultDeleteModal},
    data() {
        return {
            dataTable: {
                endpoint: '/encuestas/search',
                ref: 'EncuestaTable',
                headers: [
                    {text: "Orden", value: "position",  align: 'center', model: "Poll", sortable: false},
                    {text: "Portada", value: "image", align: 'center', sortable: false},
                    {text: "Título", value: "titulo", sortable: false},
                    {text: "Sección", value: "tipo", align: 'center', sortable: false},
                    {text: "Tipo", value: "anonima", align: 'center', sortable: false},
                    // {text: "Módulo", value: "module"},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Preguntas",
                        icon: 'mdi mdi-comment-question-outline',
                        // type: 'action',
                        // method_name: 'edit'
                        type: 'route',
                        // method_name: 'reset',
                        count: 'preguntas_count',
                        route: 'encuestas_preguntas_route'
                    },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                ],
                more_actions: [
                    // {
                    //     text: "Actividad",
                    //     icon: 'fas fa-file',
                    //     type: 'action',
                    //     method_name: 'activity'
                    // },
                    {
                        text: "Actualizar Estado",
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
                ]
            },
            selects: {
                modules: []
            },
            filters: {
                q: '',
                module: null
            },
            modalOptions: {
                ref: 'EncuestaFormModal',
                open: false,
                base_endpoint: '/encuestas',
                resource: 'Encuesta',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'EncuestaStatusModal',
                open: false,
                base_endpoint: '/encuestas',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
                content_modal: {
                    inactive: {
                        title: '¡Estás a punto de desactivar una encuesta!',
                        details: [
                            'Los usuarios no la podrán visualizar en la plataforma ni cursos.'
                        ],
                    },
                    active: {
                        title: '¡Estás a punto de activar una encuesta!',
                        details: [
                            'Los usuarios la podrán visualizar en la plataforma o cursos.'
                        ]
                    }
                },
                width: '408px'
            },
            modalDeleteOptions: {
                ref: 'EncuestaDeleteModal',
                open: false,
                base_endpoint: '/encuestas',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        // vue.getSelects();
    },
    methods: {
        // getSelects() {
        //     let vue = this
        //     const url = `/encuestas/get-list-selects`
        //     vue.$http.get(url)
        //         .then(({data}) => {
        //             vue.selects.modules = data.data.modules
        //         })
        // },
        // reset(user) {
        //     let vue = this
        //     vue.consoleObjectTable(user, 'User to Reset')
        // },
        activity() {
            console.log('activity')
        },
        confirmModal() {
            // TODO: Call store or update USER
        },
    }

}
</script>
