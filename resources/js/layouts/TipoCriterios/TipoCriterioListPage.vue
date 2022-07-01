<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Tipo Criterios
                <v-spacer/>
                <!--  <DefaultActivityButton :label="'Actividad'"
                                        @click="activity"/> -->
                <DefaultModalButton
                    :label="'Tipo Criterio'"
                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
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
                @edit="openFormModal(modalOptions, $event)"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Cambiar de estado al tipo de criterio')"
            />

            <TipoCriterioFormModal
                width="40vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />

        </v-card>
    </section>
</template>


<script>
import TipoCriterioFormModal from "./TipoCriterioFormModal";

export default {
    components: {TipoCriterioFormModal},
    data() {
        return {
            dataTable: {
                endpoint: '/tipo-criterios/search',
                ref: 'TipoCriterioTable',
                headers: [
                    {text: "Orden", value: "orden", align: 'center', model: "TipoCriterio", sortable: false},
                    {text: "Nombre", value: "nombre"},
                    {text: "Nombre Plural", value: "nombre_plural"},
                    {text: "Tipo", value: "data_type"},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Criterios",
                        icon: 'mdi mdi-ruler',
                        // type: 'action',
                        // method_name: 'edit'
                        type: 'route',
                        // method_name: 'reset',
                        count: 'criterios_count',
                        route: 'criterios_route'
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

                ]
            },
            selects: {
                // modules: []
            },
            filters: {
                q: '',
                // module: null
            },
            modalOptions: {
                ref: 'TipoCriterioFormModal',
                open: false,
                base_endpoint: '/tipo-criterios',
                resource: 'Tipo Criterio',
                confirmLabel: 'Guardar',
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
