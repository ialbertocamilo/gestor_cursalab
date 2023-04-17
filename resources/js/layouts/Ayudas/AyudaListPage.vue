<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Ayuda
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
			@logs="
	                    openFormModal(
        	                modalLogsOptions,
        	                $event,
        	                'logs',
        	                `Logs de Ayuda - ${$event.title}`
        	            )
        	        "
            />

            <LogsModal
                :options="modalLogsOptions"
                width="55vw"
                :model_id="null"
                model_type="App\Models\Post"
                :ref="modalLogsOptions.ref"
                @onCancel="closeSimpleModal(modalLogsOptions)"
            />
            <AyudaFormModal width="35vw"
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
import AyudaFormModal from "./AyudaFormModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import LogsModal from "../../components/globals/Logs";

export default {
    components: {AyudaFormModal, DefaultStatusModal, DefaultDeleteModal, LogsModal },
    data() {
        return {
            dataTable: {
                endpoint: '/ayudas/search',
                ref: 'AyudaTable',
                headers: [
                    {text: "Orden", value: "orden",  align: 'center', model: "Ayuda"},
                    {text: "Imagen", value: "image", align: 'center', sortable: false},
                    {text: "Fecha de creación", value: "created_at", align: 'center', sortable: true},
                    {text: "Fecha de actualización", value: "updated_at", align: 'center', sortable: true},
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
                base_endpoint: '/ayudas',
                resource: 'Ayuda',
                confirmLabel: 'Guardar',
            },
            modalStatusOptions: {
                ref: 'AyudaStatusModal',
                open: false,
                base_endpoint: '/ayudas',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            modalDeleteOptions: {
                ref: 'AyudaDeleteModal',
                open: false,
                base_endpoint: '/ayudas',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
	    modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                base_endpoint: "/search",
                persistent: true
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
