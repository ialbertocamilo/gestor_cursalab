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
                    :label="'Módulo'"
                    @click="openFormModal(modalOptions, null, 'create')"/>
            </v-card-title>
        </v-card>
        <!--        FILTROS-->
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row class="justify-content-start">
                    <!--         <v-col cols="4">
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
                            label="Buscar por nombre..."
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            append-icon="mdi-magnify"
                        />
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                @reset="reset"
                @edit="openFormModal(modalOptions, $event, 'edit')"
            />
            <ModuloFormModal
                width="60vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="refreshDefaultTable(dataTable, filters, 1)"
                @onCancel="closeFormModal(modalOptions)"
            />
            <DefaultAlertDialog
                :ref="modalDeleteOptions.ref"
                :options="modalDeleteOptions">
                <template v-slot:content> {{ modalDeleteOptions.contentText }}</template>
            </DefaultAlertDialog>
        </v-card>
    </section>
</template>


<script>
import ModuloFormModal from "./ModuloFormModal";

export default {
    props: ['config_id'],
    components: {ModuloFormModal},
    data() {
        return {
            breadcrumbs: [
                {title: 'Módulos', text: null, disabled: true, href: 'null'},
            ],
            dataTable: {
                endpoint: '/modulos/search',
                ref: 'modulosTable',
                headers: [
                    {text: "Portada", value: "image", align: 'center', sortable: false},
                    {text: "Nombres", value: "name"},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    // {
                    //     text: "Escuelas",
                    //     icon: 'fas fa-school',
                    //     type: 'route',
                    //     // method_name: 'reset',
                    //     count: 'escuelas_count',
                    //     route: 'escuelas_route'
                    // },
                    {
                        text: "Usuarios",
                        icon: 'fas fa-user',
                        type: 'route',
                        route: 'usuarios_route',
                        count: 'usuarios_count'
                    },
                    // {
                    //     text: "Carreras",
                    //     icon: 'fas fa-th-large',
                    //     type: 'route',
                    //     route: 'carreras_route',
                    //     count: 'carreras_count'
                    // },
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit'
                    },
                ],
                // more_actions: [
                //     {
                //         text: "Actividad",
                //         icon: 'fas fa-file',
                //         type: 'action',
                //         method_name: 'activity'
                //     },
                // ]
            },
            selects: {
                modules: []
            },
            filters: {
                q: '',
                module: null,
            },
            modalOptions: {
                ref: 'ModuloFormModal',
                open: false,
                base_endpoint: '/modulos',
                confirmLabel: 'Guardar',
                resource: 'Módulo',
                title: '',
                action: null,
                selects: {
                    modules: [],
                    // boticas: [],
                    // groups: [],
                    // cargos: [],
                }
            },
            modalDeleteOptions: {
                ref: 'ModuloDeleteModal',
                title: 'Eliminar Módulo',
                contentText: '¿Desea eliminar este registro?',
                open: false,
                endpoint: ''
            },
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();

        vue.filters.module = vue.config_id
    },
    methods: {
        getSelects() {
            let vue = this
            // const url = `/modulos/get-selects`
            // vue.$http.get(url)
            //     .then(({data}) => {
            //         vue.selects.modules = data.data.modules
            //         vue.modalOptions.selects.modules = data.data.modules
            //     })
        },
        reset(user) {
            let vue = this
            // vue.consoleObjectTable(user, 'User to Reset')
        },
        activity() {
            console.log('activity')
        },
    }

}
</script>
