<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Preguntas
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'" @click="activity"/> -->
                <DefaultModalButton :label="'Pregunta'" @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">

                    <v-col :cols="is_modal ? '6':'3'">
                        <DefaultInput clearable dense
                                      v-model="filters.q"
                                      label="Buscar por nombre..."
                                      @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event)"
                          @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar pregunta')"
            />

            <PreguntaFormModal width="40vw"
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
import PreguntaFormModal from "./PreguntaFormModal";
import DefaultStatusModal from "../../Default/DefaultStatusModal";
import DefaultDeleteModal from "../../Default/DefaultDeleteModal";

export default {
    props: ['encuesta_id', 'is_modal'],
    components: {PreguntaFormModal, DefaultStatusModal, DefaultDeleteModal},
    data() {
        let vue = this

        return {
            dataTable: {
                endpoint: '/encuestas/' + vue.encuesta_id + '/preguntas/search',
                ref: 'PreguntaTable',
                headers: [
                    {text: "Pregunta", value: "titulo"},
                    {text: "Tipo", value: "tipo_pregunta", align: 'center'},
                    {text: "Nº Opciones", value: "cantidad", align: 'center', sortable: false},
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
                modulos: []
            },
            filters: {
                q: '',
                // modulo: null,
                // encuesta: null
            },
            modalOptions: {
                ref: 'PreguntaFormModal',
                open: false,
                base_endpoint: '/encuestas/' + vue.encuesta_id + '/preguntas',
                resource: 'Pregunta',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'PreguntaStatusModal',
                open: false,
                base_endpoint: '/encuestas/' + vue.encuesta_id + '/preguntas',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'PreguntaDeleteModal',
                open: false,
                base_endpoint: '/encuestas/' + vue.encuesta_id + '/preguntas',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
        vue.filters.encuesta = vue.encuesta_id
    },
    methods: {
        getSelects() {
            let vue = this
            // const url = '/encuestas/' + vue.encuesta_id + `/preguntas/get-list-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modulos = data.data.modulos
            //     })
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
