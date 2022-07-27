<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Formulario Ayuda APP
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'" @click="activity"/> -->
                <DefaultModalButton :label="'Ayuda'" @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event)"
                          @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar registro')"
            />

            <AyudaFormModal width="35vw"
                              :ref="modalOptions.ref"
                              :options="modalOptions"
                              @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                              @onCancel="closeFormModal(modalOptions)"
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

import AyudaFormModal from "./AyudaFormModal";
import DefaultDeleteModal from "../../Default/DefaultDeleteModal";

export default {
    components: { AyudaFormModal, DefaultDeleteModal
    },
    data() {
        return {
            dataTable: {
                endpoint: '/soporte/formulario-ayuda/search',
                ref: 'AyudaTable',
                headers: [
                    {text: "Orden", value: "position",  align: 'center', model: "AyudaApp"},
                    {text: "Nombre", value: "title", align: 'left', sortable: true},
                    {text: "Detalle", value: "check_text_area", align: 'center', sortable: true},
                    {text: "Fecha de creación", value: "created_at", align: 'center', sortable: true},
                    // {text: "Fecha de actualización", value: "updated_at", align: 'center', sortable: true},
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
                // modules: []
            },
            filters: {
                q: '',
                // module: null
            },
            modalOptions: {
                ref: 'AyudaFormModal',
                open: false,
                base_endpoint: '/soporte/formulario-ayuda',
                resource: 'Ayuda',
                confirmLabel: 'Guardar',
            },

            modalDeleteOptions: {
                ref: 'AyudaDeleteModal',
                open: false,
                base_endpoint: '/soporte/formulario-ayuda',
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
            // const url = `/ayudas/get-list-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modules = data.data.modules
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
