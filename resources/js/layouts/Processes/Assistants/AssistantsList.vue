<template>
    <section class="section-list ">
        <DefaultFilter
            v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
            @cleanFilters="clearObject(filters)"
            :disabled-confirm-btn="isValuesObjectEmpty(filters)"
        >
            <template v-slot:content>
                <v-row justify="center">
                    <v-col cols="12">
                        <DefaultSelect
                            clearable
                            dense
                            :items="selects.statuses"
                            v-model="filters.active"
                            label="Estado"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                <DefaultBreadcrumbs :breadcrumbs="breadcrumbs" class="breadcrumbs_line"/>
                <v-spacer/>
                <DefaultModalButton
                    label="Generar reporte"
                    @click="null"/>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="3">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar usuario"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="3">
                        <div class="bx_count_users">
                            <span class="text_default">Usuarios: {{users}}</span>
                        </div>
                    </v-col>
                    <v-col cols="6" class="d-flex justify-end align-items-center">
                        <div class="bx_count_absences">
                            <div class="bx_icon">
                                <v-icon class="icon_size" small color="#E01717" style="font-size: 30px !important;">
                                    mdi-account
                                </v-icon>
                                <div class="bx_icon_count_absences">{{absences}}</div>
                            </div>
                            <span class="text_default" style="color: #E01717;">Inasistencias</span>
                        </div>
                        <DefaultButton
                            text
                            label="Aplicar filtros"
                            icon="mdi-filter"
                            @click="open_advanced_filter = !open_advanced_filter"
                            class="btn_filter"
                            />
                    </v-col>
                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @delete="openFormModal(modalDeleteOptions, $event, 'delete', 'Eliminar registro')"
            />
        </v-card>

        <!-- === DELETE MODAL ===  -->
        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />

        <!-- === STATUS MODAL ===  -->
        <DefaultStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalStatusOptions)"
        />

    </section>
</template>

<script>
import DefaultStatusModal from "../../Default/DefaultStatusModal";
import DefaultDeleteModal from "../../Default/DefaultDeleteModal";

export default {
    components: { DefaultStatusModal, DefaultDeleteModal },
    props: {
        process_id: {
            type: Number,
            required: true
        },
        process_name: {
            type: String,
            required: true
        }
    },
    data() {
        return {
            dataTable: {
                endpoint: `/procesos/${this.process_id}/assistants/search`,
                ref: 'DiplomaTable',
                headers: [
                    {text: "Nombres y Apellidos", value: "fullname", sortable: false },
                    {text: "Inasistencia", value: "absences", align: 'center', sortable: false},
                    {text: "Módulo", value: "module", align: 'center', sortable: false},
                    {text: "Documento", value: "document", align: 'center', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},

                ],
                actions: [
                    {
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    }
                ]
            },
            breadcrumbs: [
                {title: 'Proceso de inducción', text: `${this.process_name}`, disabled: false, href: `/procesos`, tooltip:true},
                {title: '', text: 'Lista de Usuarios', disabled: true, href: '', tooltip:false},
            ],
            selects: {
                modules: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 0, name: 'Inactivos'},
                ],
            },
            filters: {
                q: '',
                subworkspace_id: null,
                active: null,
            },
            modalDeleteOptions: {
                ref: 'DiplomaDeleteModal',
                open: false,
                base_endpoint: '/diplomas',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
            modalStatusOptions: {
                ref: 'DiplomaStatusModal',
                open: false,
                base_endpoint: '/diplomas',
                contentText: '¿Desea cambiar de estado a este registro?',
                endpoint: '',
            },
            users: 0,
            absences: 0
        }
    },
    mounted() {
        let vue = this
        vue.loadInfo();
    },
    methods: {
        loadInfo() {
            let vue = this
            console.log("....");
            const url = `/procesos/${this.process_id}/assistants/info`
            vue.$http.get(url)
                .then(({data}) => {
                    console.log(data.data);
                    vue.users = data.data.users
                    vue.absences = data.data.absences
                })
        },
    },
}

/*
certifications: [
    {id: 1, name: 'Diploma Cursalab - Default'},
    {id: 2, name: 'Diploma Cursalab - [Con fecha]'},
    {id: 3, name: 'Diploma Cursalab - [Con fecha y nota]'},
],
*/
</script>
<style lang="scss">
.bx_count_users span.text_default {
    color: #5458EA;
    font-size: 15px;
}
.bx_count_absences {
    margin-right: 10px;
    display: flex;
    align-items: center;
    .bx_icon {
        position: relative;
        margin-right: 6px;
        .bx_icon_count_absences {
            position: absolute;
            background-color: #e01717;
            height: 15px;
            min-width: 15px;
            text-align: center;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 10px;
            color: #fff;
            bottom: -7px;
            right: -5px;
            line-height: 1;
            font-family: "Nunito", sans-serif;
        }
    }
    span.text_default {
        font-size: 15px;
    }
}
</style>
