<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Sedes
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'"
                                       @click="activity"/> -->
                <DefaultModalButton
                    :label="'Sede'"
                    @click="openFormModal(modalOptions)"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <v-col cols="3">
                        <DefaultSelect
                            clearable dense
                            :items="selects.modulos"
                            v-model="filters.modulo"
                            label="Módulos"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre..."
                            append-icon="mdi-magnify"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
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
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar Sede')"
            />

            <BoticaFormModal
                width="50vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
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
import BoticaFormModal from "./BoticaFormModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";

export default {
    components: {BoticaFormModal, DefaultDeleteModal},
    data() {
        return {
            dataTable: {
                endpoint: '/boticas/search',
                ref: 'BoticaTable',
                headers: [
                    {text: "Módulo", value: "image", align: 'center', sortable: false},
                    {text: "Nombre", value: "nombre"},
                    {text: "Grupo", value: "criterio_id", align: 'center'},
                    {text: "Código Local", value: "codigo_local", align: 'center'},
                    {text: "Cantidad de Usuarios", value: "usuarios_count", align: 'center', sortable: false},
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
                    // {
                    //     text: "Eliminar",
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
                modulo: null
            },
            modalOptions: {
                ref: 'BoticaFormModal',
                open: false,
                base_endpoint: '/boticas',
                resource: 'Sede',
                confirmLabel: 'Guardar',
            },
            modalDeleteOptions: {
                ref: 'BoticaDeleteModal',
                open: false,
                base_endpoint: '/boticas',
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
            const url = `/boticas/get-list-selects`
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
