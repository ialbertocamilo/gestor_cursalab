<template>
    <div>
        <section class="section-list">
            <!--        FILTROS-->
            <DefaultFilter v-model="open_advanced_filter" @filter="advanced_filter(dataTable, filters, 1)"
                @cleanFilters="clearObject(filters)" :disabled-confirm-btn="isValuesObjectEmpty(filters)">
                <template v-slot:content>
                    <v-row justify="center">
                        <v-col cols="12">
                            <DefaultSelect clearable dense :items="selects.sub_workspaces" v-model="filters.subworkspace_id"
                                label="Módulos" item-text="name" />
                        </v-col>

                        <template v-for="(value, selectKey, index) in selects">
                            <v-col :key="index" cols="12"
                                v-if="!['sub_workspaces', 'active'].includes(selectKey) && criteria_template[index-2]">
                                <DefaultInputDate
                                    v-if="criteria_template[index-2].field_type.code === 'date'"
                                    clearable
                                    dense
                                    :referenceComponent="'modalDateFilter1'"
                                    :options="{ open: false, }"
                                    v-model="filters[selectKey]"
                                    :label="criteria_template[index-2].name"
                                />

                                <DefaultAutocomplete
                                    v-else
                                    clearable
                                    dense
                                    :items="value"
                                    v-model="filters[selectKey]"
                                    :label="criteria_template[index-2].name"
                                    item-text="name"
                                    :multiple="criteria_template[index-2].multiple"
                                    :show-select-all="false"
                                />
                            </v-col>
                        </template>
                        <v-col cols="12">
                            <DefaultSelect clearable dense :items="selects.status" v-model="filters.status" item-text="name"
                                item-value="id" multiple label="Estados" />
                        </v-col>
                    </v-row>
                </template>
            </DefaultFilter>
            <v-card flat class="elevation-0 mb-4">
                <v-card-title>
                    <DefaultBreadcrumbs :breadcrumbs="breadcrumbs" />
                </v-card-title>
            </v-card>
            <v-card flat class="elevation-0 mb-4">
                <v-card-text>
                    <v-row class="justify-content-start">
                        <v-col cols="4">
                            <DefaultInput learable dense v-model="filters.q" label="Buscar usuario"
                                @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                                @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)" append-icon="mdi-magnify" />
                        </v-col>

                        <v-col cols="8" class="d-flex justify-end">
                            <DefaultButton text label="Aplicar filtros" icon="mdi-filter"
                                @click="open_advanced_filter = !open_advanced_filter" class="btn_filter" />
                        </v-col>
                    </v-row>
                </v-card-text>
                <!-- TABLE -->
                <DefaultTable 
                    :ref="dataTable.ref" 
                    :data-table="dataTable" 
                    :filters="filters"
                    @download_all_files="download_all_files($event)"
                    @check_tarea="openFormModal(modalUsuarioCheckTarea, $event, null, `Revisar Tarea - Documento: ${$event.document}`)"
                    @openModalDeleteFiles="openModalDeleteFiles"
                    @downloadFilesUsers="downloadFilesUsers"
                >
                    <template v-slot:custom_slot="{ item }">
                        <div v-if="item.resources_user.length > 0" class="d-flex justify-center">
                            <span v-for="(resources_user, idx) in item.resources_user" :key="idx">
                                <v-btn icon @click="openFormModal(modalUsuarioResource, resources_user, null, 'Recurso')"
                                    color="primary">
                                    <v-icon>{{ getIcon(resources_user.type_media)  }}</v-icon>
                                </v-btn>
                            </span>
                        </div>
                        <div v-else>
                            <p>-</p>
                        </div>
                    </template>
                </DefaultTable>
            </v-card>
        </section>
        <UsuarioResourceModal 
            :ref="modalUsuarioResource.ref"
            :options="modalUsuarioResource"
            @onConfirm="closeFormModal(modalUsuarioResource, dataTable, filters),refreshDefaultTable(dataTable, filters)"
            @onCancel="closeFormModal(modalUsuarioResource)"
        />
        <UsuarioCheckTareaModal 
            :ref="modalUsuarioCheckTarea.ref"
            :options="modalUsuarioCheckTarea"
            @onConfirm="closeFormModal(modalUsuarioCheckTarea, dataTable, filters),refreshDefaultTable(dataTable, filters)"
            @onCancel="closeFormModal(modalUsuarioCheckTarea)"
        />
        <DialogConfirm
            v-model="deleteFilesConfirmationDialog.open"
            width="270px"
            title="Eliminar archivos"
            txt_btn_confirm="Cancelar"
            txt_btn_cancel="Confirmar"
            :subtitle="deleteFilesConfirmationDialog.subtitle"
            @onConfirm="deleteFilesConfirmationDialog.open = false"
            @onCancel="deleteFilesConfirmationDialog.open = false,deleteFiles()"
        />
    </div>
</template>


<script>

import UsuarioResourceModal from './UsuarioResourceModal.vue';
import UsuarioCheckTareaModal from './UsuarioCheckTareaModal.vue'
import DialogConfirm from '../../components/basicos/DialogConfirm.vue'
export default {
    components: { UsuarioResourceModal, UsuarioCheckTareaModal, DialogConfirm},
    props: ['project_id', 'course_name'],
    data() {
        let vue = this
        return {
            selects: {
                status: [],
                sub_workspaces: []
            },
            criteria_template:[],
            breadcrumbs: [
                { title: 'Tareas', text: 'tareas', disabled: false, href: `/projects` },
                { title: 'Curso', text: vue.course_name, disabled: false, href: `/cursos?q=${vue.course_name}` },
            ],
            dataTable: {
                endpoint: '/projects/' + vue.project_id + '/users/search',
                ref: 'UsuarioTareasTable',
                headers: [
                    { text: "custom-select", value: "custom-select", align: 'left', model: 'Categoria', sortable: false },
                    { text: "Módulo", value: "subworkspace", align: 'left', model: 'Categoria', sortable: false },
                    { text: "Nombres", value: "name", align: 'left', model: 'Categoria', sortable: false },
                    { text: "Apellidos", value: "lastname", align: 'left', sortable: false },
                    { text: "Documento", value: "document", align: 'left', sortable: false },
                    { text: "Estado", value: "status_project", align: 'left', sortable: false },
                    { text: "Tareas Cargados", value: "custom_slot", align: 'center', sortable: false },
                    { text: "Opciones", value: "actions", align: 'center', sortable: false },
                ],
                customSelectActions:[
                    {text:'Eliminar archivos',method_name:'openModalDeleteFiles'},
                    {text:'Descargar archivos',method_name:'downloadFilesUsers'},
                ],
                actions: [
                    {
                        text: "Descarga",
                        icon: 'mdi mdi-download',
                        type: 'action',
                        method_name: 'download_all_files',
                        show_condition: 'has_resource'
                    },
                    {
                        text: "Revisar",
                        icon: 'mdi mdi-check',
                        type: 'action',
                        method_name: 'check_tarea',
                        show_condition: 'has_resource'
                    },
                ],
                more_actions: [],
            },
            filters: {
                q: '',
                estado: null,
                subworkspace_id: null,
                status: [],
                q_group_dnis: ''
            },
            modalUsuarioResource: {
                ref: 'UsuarioResourceModal',
                open: false,
                showCloseIcon: true,
                showTitle: false,
                width: '30vw',
                showCardActions: false,
                noPaddingCardText: true
            },
            modalUsuarioCheckTarea: {
                ref: 'UsuarioCheckTareaModal',
                open: false,
                showCloseIcon: true,
                width: '30vw',
                showCardActions: true,
            },
            deleteFilesConfirmationDialog: {
                open: false,
                title_modal: 'Archivos eliminados',
                subtitle:'Estás a punto eliminar x archivos de',
                content_modal :''
            },
            open_advanced_filter: false,
            selectedItems:[],
            user_resources_to_delete:[]
        }
    },
    mounted() {
        let vue = this
        vue.loadSelects();
    },
    methods: {
        async loadSelects() {
            let vue = this;
            vue.showLoader();
            await Promise.all([vue.loadStatusList(), vue.loadSubworkspaces()])
            .then(()=>{
                vue.hideLoader();
            })
        },
        async loadStatusList(){
            let vue = this;
            await vue.$http.get('/projects/users/status-list/list').then(({ data }) => {
                vue.selects.status = data.data;
            })
        },
        async loadSubworkspaces(){
            let vue = this;
                const url = `/usuarios/get-list-selects`
            vue.$http.get(url)
                .then(({data}) => {

                    vue.selects.sub_workspaces = data.data.sub_workspaces;
                    vue.criteria_template = data.data.criteria_template;

                    data.data.criteria_workspace.forEach(criteria => {

                        const new_select_obj = {[criteria.code]: criteria.values,};
                        vue.selects = Object.assign({}, vue.selects, new_select_obj);

                        const value = criteria.multiple ? [] : null;
                        const new_filter_obj = {[criteria.code]: value};
                        vue.filters = Object.assign({}, vue.filters, new_filter_obj);

                    });

                    // if (param_subworkspace)
                    //     vue.filters.subworkspace_id = param_subworkspace

                })
        },
        async loadData() {
            let vue = this;
            vue.showLoader();
            axios.get('/projects/users/status-list/list').then(({ data }) => {
                vue.selects.status = data.data;
                vue.hideLoader();
            })
        },
        getIcon(type) {
            const types = [
                { type: 'pdf', icon: 'mdi mdi-file-pdf-box' },
                { type: 'image', icon: 'mdi-file-image' },
                { type: 'office', icon: 'mdi mdi-file' },
                { type: 'video', icon: 'mdi-file-video' }
            ]
            const find_type = types.find(e => e.type == type);
            return find_type.icon || 'mdi-file';
        },
        download_all_files({ resources_user, project_user_id }) {
            let url = `/projects/${project_user_id}/download-zip-files?`;
            resources_user.forEach((resource, index) => {
                url += (index > 0) ? '&rutas[]=' + resource.path_file : 'rutas[]=' + resource.path_file;
            });
            // window.location.href = url;
            window.open(url, '_blank');
        },
        downloadFilesUsers(users){
            let vue = this;
            const tempString = users
            .filter(u => u.project_user_id)
            .map(u => `&puid[]=${u.project_user_id}`)
            .join('');         
            console.log(tempString);
            let url = `/projects/download-massive-zip-files?project_id=${vue.project_id}${tempString}`
            console.log(url);
            axios.get(url).then(({ data }) => {
                vue.hideLoader();
            })
            window.open(url, '_blank');
        },
        remove_on_paste(event, localText) {
            event.preventDefault();
            let main_text = event.clipboardData.getData("text");
            if (main_text) {
                main_text = main_text.replace(/\r?\n/g, ' ');
                if (this.filters.q_group_dnis) {
                    (this.filters.q_group_dnis.length > 0) ? this.filters.q_group_dnis += ' ' + main_text : this.filters.q_group_dnis += main_text;
                } else {
                    this.filters.q_group_dnis = main_text;
                }
            }
        },
        selectionChange(rows) {
            this.selectedItems = rows
            console.log(rows);
            // Update selection flag
            if (this.selectedItems.length < this.totalCount) {
                this.allSelected = false
            }
            if (this.selectedItems.length === this.totalCount) {
                this.allSelected = true
            }
        },
        openModalDeleteFiles(users){
            let vue = this;
            vue.user_resources_to_delete = users;
            if(users.length>0){
                let quantity_resources = 0;
                users.forEach(user => quantity_resources  += user.resources_user.length);
                vue.deleteFilesConfirmationDialog.subtitle = `Estás a punto eliminar ${quantity_resources} archivo(s) de ${users.length} usuario(s) seleccionado(s). ¿Estás seguro de hacerlo?`;
                vue.openSimpleModal(vue.deleteFilesConfirmationDialog);
            }
        },
        async deleteFiles(){
            let vue = this;
            await vue.$http.get('/projects/users/delete-files',vue.user_resources_to_delete).then(({ data }) => {
                vue.showAlert('Los archivos han sido eliminados.', 'success', '')
            })
        }
    }

}
</script>
