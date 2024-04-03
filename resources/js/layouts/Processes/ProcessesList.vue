<template>
    <section class="section-list">
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
                            dense
                            :items="selects.progress"
                            v-model="filters.progress"
                            label="Progreso"
                            @onChange="refreshDefaultTable(dataTable, filters, 1)"
                            item-text="name"
                        />
                    </v-col>
                </v-row>
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
                Proceso de Inducción
                <v-spacer/>
                <DefaultModalButton :label="'Asignar Supervisores'" @click="openPageSupervisores()" :icon_name="'fa fa-portrait'" :outlined="true"/>
                <DefaultModalButton :label="'Proceso de Inducción'" @click="openModalSelectActivitys()"/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por título o descripción..."
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="4">
                    </v-col>
                    <v-col cols="4" class="d-flex justify-end">

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
                type_table="process"
                @segmentation="
                    openFormModal(
                        modalFormSegmentationOptions,
                        $event,
                        'segmentation',
                        `Segmentación de usuarios`
                    )
                "
                @edit="openFormModal(modalEditProcess, $event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de un proceso')"
                @delete="openFormModal(modalDeleteOptions,$event,'delete','Cambio de estado de un proceso')"
                @logs="openFormModal(modalLogsOptions,$event,'logs',`Logs del proceso - ${$event.title}`)"
                @saveNewProcessInline="saveNewProcessInline($event)"
            />
        </v-card>

        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />

        <DefaultStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalStatusOptions)"
        />

        <ModalSelectTemplate
                :ref="modalSelectTemplate.ref"
                v-model="modalSelectTemplate.open"
                width="650px"
                @onCancel="closeFormModal(modalSelectTemplate)"
                @selectTemplateOrNewProcessModal="selectTemplateOrNewProcessModal"
            />

        <ModalSegment
            :options="modalFormSegmentationOptions"
            width="870px"
            model_type="App\Models\Process"
            :model_id="null"
            :ref="modalFormSegmentationOptions.ref"
            @onCancel="closeSimpleModal(modalFormSegmentationOptions)"
            @onConfirm="closeFormModal(modalFormSegmentationOptions, dataTable, filters)"
        />
        <ModalCreateProcess
            :ref="modalCreateProcess.ref"
            v-model="modalCreateProcess.open"
            :width="'870px'"
            @onCancel="closeFormModal(modalCreateProcess)"
            @onConfirm="saveNewProcessModal"
        />

        <ModalEditProcess
            :ref="modalEditProcess.ref"
            v-model="modalEditProcess.open"
            :width="'870px'"
            @onCancel="closeFormModal(modalEditProcess)"
            @onConfirm="saveEditProcessModal"
            :process="modalEditProcess.process"
        />
        <ModalSelectConfigProcess
                :ref="modalSelectConfigProcess.ref"
                v-model="modalSelectConfigProcess.open"
                width="800px"
                @onCancel="closeFormModal(modalSelectConfigProcess)"
                @selectNextConfigProcess="selectNextConfigProcess"
            />

    </section>
</template>

<script>
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import ModalSelectTemplate from "../../components/Induction/Process/ModalSelectTemplate";
import ModalSelectConfigProcess from "../../components/Induction/Process/ModalSelectConfigProcess";
import ModalCreateProcess from "../../components/Induction/Process/ModalCreateProcess";
import ModalEditProcess from "../../components/Induction/Process/ModalEditProcess";

import ModalSegment from "./ModalSegment";

export default {
    components: {
    DefaultStatusModal,
    DefaultDeleteModal,
    ModalSelectTemplate,
    ModalCreateProcess,
    ModalEditProcess,
    ModalSegment,
    ModalSelectConfigProcess
},
    mounted() {
        let vue = this
        vue.loadInfo();
    },
    data() {
        return {
            dataForModalSegment: null,
            modalFormSegmentationOptions: {
                ref: 'ModalSegment',
                open: false,
                persistent: true,
                base_endpoint: "/segments",
                cancelLabel: "Cancelar",
                confirmLabel: "Continuar",
                resource: "segmentación"
            },

            dataTable: {
                endpoint: '/procesos/search',
                ref: 'BenefitTable',
                headers: [
                    {text: "Título", value: "title_process", align: 'start', sortable: true, width: '40%'},
                    {text: "Progreso", value: "progress_process", align: 'center', sortable: false, width: '30%'},
                    {text: "Edición", value: "actions", align: 'center', sortable: false},
                    {text: "Opciones", value: "actions_extras", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit',
                        conditionalBadgeIcon: [{
                            message: 'Aún no terminas de personalizar el proceso de inducción',
                            minValue: 0,
                            propertyCond: 'config_completed',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                        },
                            {
                            message: 'Personaliza tu proceso de inducción',
                            minValue: 1,
                            propertyCond: 'config_completed',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle',
                            colorActive: true
                        }]
                    },
                    {
                        text: "Segmentación",
                        icon: 'mdi mdi-account-group segmentation-icon',
                        type: 'action',
                        method_name: 'segmentation',
                        conditionalBadgeIcon: [{
                            message: 'No tienes colaboradores participantes en el proceso',
                            minValue: 0,
                            propertyCond: 'assigned_users',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                        },
                            {
                            message: 'Selecciona a los colaboradores que participarán en el proceso',
                            minValue: 1,
                            propertyCond: 'assigned_users',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle',
                            colorActive: true
                        }]
                    },
                    {
                        text: "Actividades",
                        icon: 'mdi mdi-folder-star',
                        type: 'route',
                        route: 'stages_route',
                        conditionalBadgeIcon: [{
                            message: 'No tienes actividades creadas en el proceso',
                            minValue: 0,
                            propertyCond: 'stages_count',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                        },
                            {
                            message: 'Crea actividades para el proceso',
                            minValue: 1,
                            propertyCond: 'stages_count',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle',
                            colorActive: true
                        }]
                    },
                    {
                        text: "Certificado",
                        icon: 'mdi mdi-file-document-check',
                        type: 'route',
                        route: 'certificate_route',
                        conditionalBadgeIcon: [{
                            message: 'Aún no creas un certificado',
                            minValue: 0,
                            propertyCond: 'certificate_template_id',
                            color: 'red',
                            icon: 'fas fa-exclamation-triangle',
                            iconSize: '12px'
                        },
                            {
                            message: 'Certificado que se entrega al usuario al terminar el proceso',
                            minValue: 1,
                            propertyCond: 'certificate_template_id',
                            color: '#7fbade',
                            icon: 'mdi mdi-check-circle',
                            colorActive: true
                        }]
                    },
                ],
                actions_extras: [
                    {
                        text: "Activo",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    {
                        text: "Asistencia",
                        icon: 'fas fa-user-friends',
                        type: 'route',
                        route: 'assistans_route'
                    },
                    // {
                    //     text: "Duplicar",
                    //     icon: 'far fa-clone',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
                    {
                        text: "Listado",
                        icon: 'fas fa-file-alt',
                        type: 'action',
                        method_name: 'send_emails'
                    },
                    {
                        text: "Eliminar",
                        icon: 'far fa-trash-alt',
                        type: 'action',
                        method_name: 'delete'
                    },
                ]
            },
            base_endpoint: '/procesos',
            dataModalSegment: {},

            modalSelectTemplate: {
                ref: 'ModalSelectTemplate',
                open: false,
                endpoint: '',
            },

            modalSelectConfigProcess: {
                ref: 'ModalSelectConfigProcess',
                open: false,
                endpoint: '',
            },
            modalCreateProcess: {
                ref: 'ModalCreateProcess',
                open: false,
                endpoint: '',
            },
            modalEditProcess: {
                ref: 'ModalEditProcess',
                open: false,
                endpoint: '',
                process: {
                    title: '',
                    description: '',
                    limit_absences: false,
                    instructions: []
                }
            },

            selects: {
                sub_workspaces: [],
                statuses: [
                    {id: null, name: 'Todos'},
                    {id: 1, name: 'Activos'},
                    {id: 2, name: 'Inactivos'},
                ],
                progress: [
                    {id: 1, name: 'Pendiente de asignación'},
                    {id: 2, name: 'En curso'},
                    {id: 3, name: 'Terminado'},
                ],
                // progress: [
                //     null => 'Todos',
                //     1 => 'Activos',
                //     0 => 'Inactivos',
                // ],
            },
            filters: {
                q: '',
                subworkspace_id: null,
                active: 1,
            },
            btn_reload_data: false,
            mostrarFiltros: true,
            modalLogsOptions: {
                ref: "LogsModal",
                open: false,
                showCloseIcon: true,
                persistent: true,
                base_endpoint: "/search"
            },
            modalDeleteOptions: {
                ref: 'BenefitDeleteModal',
                open: false,
                base_endpoint: '/procesos',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un Proceso!',
                        details: [
                            'Este proceso no podrá ser visto por los usuarios.',
                            'La información eliminada no podra recuperarse'
                        ],
                    }
                },
                width: '408px'
            },
            modalStatusOptions: {
                ref: 'BenefitStatusModal',
                open: false,
                base_endpoint: '/procesos',
                contentText: '¿Desea cambiar de estado a este registro?',
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un Proceso!',
                        details: [
                            'Este proceso ahora no podrá ser visto por los usuarios.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un Proceso!',
                        details: [
                            'Este proceso ahora podrá ser visto por los usuarios.'
                        ]
                    }
                },
                endpoint: '',
                width: '408px'
            },
            file: null,
        }
    },
    methods: {
        openPageSupervisores() {
            window.location.href = 'supervisores';
        },
        openFormModalSegment(modalFormSegmentationOptions, event = null, action = null, title = null) {
            let vue = this
            vue.dataForModalSegment = event

            vue.openFormModal(
                        modalFormSegmentationOptions,
                        event,
                        action,
                        title
                    )
        },
        loadInfo() {
        },
        async openModalSelectActivitys() {
            let vue = this
            vue.modalSelectTemplate.open = true
        },
        selectTemplateOrNewProcessModal( value ) {
            let vue = this
            // window.location.href = '/beneficios/create?type=' + value;
            if(value == 'new')
            {
                vue.openFormModal(vue.modalEditProcess)
                // vue.modalCreateProcess.open = true
                vue.modalSelectTemplate.open = false
            }
        },
        selectNextConfigProcess( value, item ) {
            let vue = this
            vue.closeFormModal(vue.modalSelectConfigProcess)

            if(value == 'activity')
            {
                window.location.href = item.stages_route
            }
            else if(value == 'segment')
            {
                vue.openFormModal(
                        vue.modalFormSegmentationOptions,
                        item,
                        'segmentation',
                        `Segmentación de usuarios`
                    )
            }
            else if(value == 'certificate')
            {
                window.location.href = item.certificate_route
            }
        },
        async openModalEditProcess(process, edit = false) {
            let vue = this;
            // this.showLoader()
            vue.modalEditProcess.process = process;
            if(process.instructions.length == 0)
            {
                const newID = `n-${Date.now()}`;
                const newInstruction = {
                    id: newID,
                    description: "",
                    active: 1,
                    hasErrors: false
                };
                vue.modalEditProcess.process.instructions.push(newInstruction);
            }
            vue.modalEditProcess.open = true;
        },
        saveNewProcessModal( item ) {
            let vue = this

            vue.showLoader()

            const edit = vue.benefit_id !== ''
            let url = `${vue.base_endpoint}/store`
            let method = 'POST';

            if(item.title != '')
            {
                const resource = {
                    'title' : item.title,
                    'description' : item.description,
                    'limit_absences' : item.limit_absences,
                    'absences' : item.absences,
                    'count_absences' : item.count_absences,
                    'starts_at' : item.starts_at,
                    'finishes_at' : item.finishes_at,
                };
                const fields = ['title',
                                'description',
                                'limit_absences',
                                'absences',
                                'count_absences',
                                'starts_at',
                                'finishes_at',];
                const formData = vue.getMultipartFormData(method, resource, fields);
                // formData.append('validateForm', validateForm ? "1" : "0");

                vue.$http.post(url, formData)
                        .then(async ({data}) => {
                            this.hideLoader()
                            vue.showAlert(data.data.msg)
                            vue.modalCreateProcess.open = false
                            vue.refreshDefaultTable(vue.dataTable, vue.filters);
                        })
                        .catch(error => {
                            if (error && error.errors){
                                vue.errors = error.errors
                            }
                            // vue.loadingActionBtn = false
                        })
            }
        },
        saveEditProcessModal( item )
        {
            let vue = this

            vue.showLoader()

            let url = item.id ? `${vue.base_endpoint}/update/${item.id}` : `${vue.base_endpoint}/store`
            let method = item.id ? 'PUT' : 'POST';

            if(item.title != '')
            {
                const resource = {
                    'title' : item.title,
                    'description' : item.description,
                    'limit_absences' : item.limit_absences,
                    'absences' : item.absences,
                    'count_absences' : item.count_absences,
                    'block_stages': item.block_stages,
                    'migrate_users': item.migrate_users,
                    'starts_at' : item.starts_at,
                    'finishes_at' : item.finishes_at,
                    'color' : item.color_selected,
                    'color_map_even' : item.color_map_even,
                    'config_completed' : item.config_completed ? item.config_completed : false,
                    'color_map_odd' : item.color_map_odd,
                    'active': item.active,
                    'subworkspaces': item.subworkspaces
                };

                const fields = ['title',
                                'description',
                                'limit_absences',
                                'absences',
                                'count_absences',
                                'block_stages',
                                'migrate_users',
                                'starts_at',
                                'finishes_at',
                                'instructions',
                                'color',
                                'color_map_even',
                                'config_completed',
                                'color_map_odd',
                                'logo',
                                'background_mobile',
                                'background_web',
                                'active',
                                'image_guia',
                                'icon_finished',
                                'icon_finished_name',
                                'image_guide_name',
                                'subworkspaces'
                            ];
                const file_fields = [
                                'logo',
                                'background_mobile',
                                'background_web',
                                'image_guia',
                                'icon_finished'
                            ];

                if(item.logotipo) {
                    resource.logo = item.logotipo
                    resource.file_logo = item.logotipo
                }
                if(item.fondo_mobile) {
                    resource.background_mobile = item.fondo_mobile
                    resource.file_background_mobile = item.fondo_mobile
                }
                if(item.fondo_web) {
                    resource.background_web = item.fondo_web
                    resource.file_background_web = item.fondo_web
                }
                if(item.img_guia_blob) {
                    resource.image_guia = item.img_guia_blob
                    resource.file_image_guia = item.img_guia_blob
                    resource.image_guide_name = item.image_guide_name
                }
                else {
                    if(item.image_guia) {
                        resource.image_guia = item.image_guia
                        resource.image_guide_name = item.image_guide_name
                    }
                }
                if(item.icon_finished_blob) {
                    resource.icon_finished = item.icon_finished_blob
                    resource.file_icon_finished = item.icon_finished_blob
                    resource.icon_finished_name = item.icon_finished_name
                }
                else {
                    if(item.icon_finished)
                        resource.icon_finished = item.icon_finished
                }

                const formData = vue.getMultipartFormData(method, resource, fields, file_fields);

                let instructions = JSON.stringify(item.instructions)
                formData.append('instructions', instructions)

                // formData.append('validateForm', validateForm ? "1" : "0");

                vue.$http.post(url, formData)
                        .then(async ({data}) => {

                            vue.showAlert(data.data.msg)

                            vue.$refs.ModalEditProcess.closeModal()
                            vue.refreshDefaultTable(vue.dataTable, vue.filters);

                            if(!item.assistans_route)
                                item.assistans_route = data.data.process.assistans_route
                            if(!item.certificate_route)
                                item.certificate_route = data.data.process.certificate_route
                            if(!item.stages_route)
                                item.stages_route = data.data.process.stages_route

                            if(item.config_process && !item.config_process.activities)
                                vue.openFormModal(vue.modalSelectConfigProcess, item);
                        })
                        .catch(error => {
                            if (error && error.errors){
                                vue.errors = error.errors
                            }
                            // vue.loadingActionBtn = false
                        })
            }

        },
        saveNewProcessInline( item )
        {
            let vue = this

            vue.openFormModal(vue.modalEditProcess)

            // vue.showLoader()

            // const edit = vue.benefit_id !== ''
            // let url = `${vue.base_endpoint}/store_inline`
            // let method = 'POST';

            // if(item.title != '')
            // {
            //     const resource = { 'title' : item.title };
            //     const fields = ['title'];
            //     const formData = vue.getMultipartFormData(method, resource, fields);
            //     // formData.append('validateForm', validateForm ? "1" : "0");

            //     vue.$http.post(url, formData)
            //             .then(async ({data}) => {
            //                 this.hideLoader()
            //                 vue.showAlert(data.data.msg)
            //                 // setTimeout(() => vue.closeModal(), 2000)
            //                 vue.refreshDefaultTable(vue.dataTable, vue.filters);
            //             })
            //             .catch(error => {
            //                 if (error && error.errors){
            //                     vue.errors = error.errors
            //                 }
            //                 // vue.loadingActionBtn = false
            //             })
            // }
        },
    }
};
</script>
<style lang="scss">
.bx_max_colaboradores {
    display: flex;
    align-items: center;
}
.bx_max_colaboradores p {
    font-size: 14px;
    margin: 0 6px;
    font-family: 'open sans', "Nunito", sans-serif;
    color: #5458EA;
}
.bx_max_colaboradores span {
    font-size: 16px;
    font-family: 'open sans', "Nunito", sans-serif;
    color: #5458EA;
    font-weight: bold;
}
.btns_change_max_col {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    line-height: 1;
    margin-left: 5px;
    cursor: pointer;
}
</style>
