<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Criterios
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'" @click="activity"/> -->
                <DefaultModalButton :label="'Criterio'" @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">

                    <v-col cols="3">
                        <DefaultSelect clearable dense
                                       :items="selects.modulos"
                                       v-model="filters.modulo"
                                       label="Módulos"
                                       @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput clearable dense
                                      v-model="filters.q"
                                      label="Buscar por nombre..."
                                      append-icon="mdi-magnify"
                                      @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                                      @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event)"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Cambiar de estado al criterio')"
            />

            <CriterioFormModal width="40vw"
                              :ref="modalOptions.ref"
                              :options="modalOptions"
                              @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import CriterioFormModal from "./CriterioFormModal";

export default {
    props: ['tipo_criterio_id'],
    components: {CriterioFormModal},
    data() {
        let vue = this

        return {
            dataTable: {
                endpoint: '/tipo-criterios/' + vue.tipo_criterio_id + '/criterios/search',
                ref: 'CriterioTable',
                headers: [
                    // {text: "Módulo", value: "order", align: 'center', sortable: false},
                    {text: "Módulo", value: "image", align: 'center', sortable: false},
                    {text: "Nombre", value: "valor"},
                    {text: "Tipo", align: 'center', value: "type", sortable: false},
                    {text: "Cantidad de Usuarios", align: 'center', value: "usuarios_count"},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    
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
                    // {
                    //     text: "Activar/Inactivar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
                ]
            },
            selects: {
                modulos: []
            },
            filters: {
                q: '',
                modulo: null,
                tipo_criterio: null
            },
            modalOptions: {
                ref: 'CriterioFormModal',
                open: false,
                base_endpoint: '/tipo-criterios/' + vue.tipo_criterio_id + '/criterios',
                resource: 'Criterio',
                confirmLabel: 'Guardar',
            },
            modalDeleteOptions: {
                ref: 'CriterioDeleteModal',
                open: false,
                base_endpoint: '/tipo-criterios/' + vue.tipo_criterio_id + '/criterios',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
        vue.filters.tipo_criterio = vue.tipo_criterio_id
    },
    methods: {
        getSelects() {
            let vue = this
            const url = '/tipo-criterios/' + vue.tipo_criterio_id + `/criterios/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modulos = data.data.modulos
                })
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
