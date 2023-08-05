<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Proyectos
                <v-spacer/>
                <DefaultModalButton 
                    label="Proyecto"
                    icon_name="mdi-plus"
                    @click="openFormModal(modalOptions)"
                />

            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <!-- <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar beneficios"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        /> -->
                    </v-col>
                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
            />
            <ProjectFormModal
                width="50vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
                @onCancel="closeFormModal(modalOptions)"
            />
        </v-card>
    </section>
</template>
<script>
import ProjectFormModal from "./ProjectFormModal";

export default {
    components: {ProjectFormModal},
    data() {
        return {
            dataTable: {
                avoid_first_data_load: false,
                endpoint: '/projects/search',
                ref: 'ProjectTable',
                headers: [
                    {text: "Empresa", value: "workspace"},
                    {text: "Escuela", value: "school"},
                    {text: "Curso", value: "course"},
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
                ],
                more_actions: [
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
                ]
            },
            selects: {
                modules: [],
            },
            filters: {
                q: '',
                active: 1
            },
            modalOptions: {
                ref: 'ProjectFormModal',
                open: false,
                base_endpoint: '/projects',
                resource: 'Proyecto',
                confirmLabel: 'Guardar',
            }
        }
    },
    mounted() {
        let vue = this
        vue.getSelects();
        // // === check localstorage anuncio ===
        // if(vue.dataTable.avoid_first_data_load) {
        //     vue.refreshDefaultTable(vue.dataTable, vue.filters, 1);
        //     const { storage: vademecumStorage } = vue.getStorageUrl('anuncio');
        //     vue.openFormModal(vue.modalOptions, { id: vademecumStorage.id });
        // }
        // === check localstorage anuncio ===
    },
    created() {
        let vue = this;

        // // === check localstorage anuncio ===
        // const { status, storage: anuncioStorage } = vue.getStorageUrl('anuncio');
        // if(status) {
        //     vue.filters.q = anuncioStorage.q;
        //     vue.filters.module = anuncioStorage.module[0]; // considerar que puede ser multimple
        //     vue.filters.active = anuncioStorage.active;

        //     vue.dataTable.avoid_first_data_load = true;
        // }
        // === check localstorage anuncio ===
    },
    methods: {
        getSelects() {
            let vue = this
            // const url = `/anuncios/get-list-selects`
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