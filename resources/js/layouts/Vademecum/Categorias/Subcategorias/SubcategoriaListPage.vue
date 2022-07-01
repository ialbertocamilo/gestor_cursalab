<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <!--            Título con breadcumb-->
            <!--            TODO: Add breadcumb-->
            <v-card-title>
                Vademecum / Categorías / Sub-Categorías
                <v-spacer/>
                <!-- <DefaultActivityButton :label="'Actividad'" @click="activity"/> -->
                <DefaultModalButton :label="'Subcategoria'" @click="openFormModal(modalOptions)"/>
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
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable :ref="dataTable.ref"
                          :data-table="dataTable"
                          :filters="filters"
                          @edit="openFormModal(modalOptions, $event)"
                          @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar registro')"
            />

            <SubcategoriaFormModal width="40vw"
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
import SubcategoriaFormModal from "./SubcategoriaFormModal";
import DefaultDeleteModal from "../../../Default/DefaultDeleteModal";

export default {
    props: ['categoria_id'],
    components: {SubcategoriaFormModal, DefaultDeleteModal},
    data() {
        let vue = this

        return {
            dataTable: {
                endpoint: '/vademecum/categorias/' + vue.categoria_id + '/subcategorias/search',
                ref: 'SubcategoriaTable',
                headers: [
                    {text: "Nombre", value: "nombre"},
                    {text: "Fecha de creación", align: 'center', value: "created_at"},
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
                    //     text: "Activar/Inactivar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
                ]
            },
            selects: {
            },
            filters: {
                q: '',
                categoria: null
            },
            modalOptions: {
                ref: 'SubcategoriaFormModal',
                open: false,
                base_endpoint: '/vademecum/categorias/' + vue.categoria_id + '/subcategorias',
                resource: 'Subcategoria',
                confirmLabel: 'Guardar',
            },
            modalDeleteOptions: {
                ref: 'SubcategoriaDeleteModal',
                open: false,
                base_endpoint: '/vademecum/categorias/' + vue.categoria_id + '/subcategorias',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
        vue.filters.categoria = vue.categoria_id
    },
    methods: {
        getSelects() {
            let vue = this
            // const url = '/vademecum/categorias/' + vue.categoria_id + `/subcategorias/get-list-selects`
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
