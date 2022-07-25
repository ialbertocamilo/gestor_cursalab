<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Preguntas Frecuentes
                <v-spacer/>
               <!--  <DefaultActivityButton :label="'Actividad'"
                                       @click="activity"/> -->
                <DefaultModalButton :label="'Pregunta'"
                                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">

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

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event)"
                          @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar anuncio')"
            />

            <PreguntaFrecuenteFormModal width="45vw"
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

            <DefaultDeleteModal :options="modalDeleteOptions"
                                :ref="modalDeleteOptions.ref"
                                @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
                                @onCancel="closeFormModal(modalDeleteOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import PreguntaFrecuenteFormModal from "./PreguntaFrecuenteFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";

export default {

    components: {
        PreguntaFrecuenteFormModal, DefaultStatusModal, DefaultDeleteModal
    }
    ,
    data() {
        return {
            dataTable: {
                endpoint: '/preguntas-frecuentes/search',
                ref: 'PreguntaFrecuenteTable',
                headers: [
                    {text: "Orden", value: "position",  align: 'center', model: "Pregunta_frecuente"},
                    {text: "Pregunta", value: "title"},
                    {text: "Respuesta", value: "content"},
                    {text: "Fecha de creación", value: "created_at", align: 'center'},
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
            selects: {
                modules: []
            },
            filters: {
                q: '',
                module: null
            },
            modalOptions: {
                ref: 'PreguntaFrecuenteFormModal',
                open: false,
                base_endpoint: '/preguntas-frecuentes',
                resource: 'Pregunta Frecuente',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'PreguntaFrecuenteStatusModal',
                open: false,
                base_endpoint: '/preguntas-frecuentes',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'PreguntaFrecuenteDeleteModal',
                open: false,
                base_endpoint: '/preguntas-frecuentes',
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

        },
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
