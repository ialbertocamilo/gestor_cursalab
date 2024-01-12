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
                <DefaultModalButton :label="'Asignar Supervisores'" @click="alert('sss')" :icon_name="'fa fa-portrait'" :outlined="true"/>
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
                @segmentation="openModalSegment($event)"
                @edit="openModalEditProcess($event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de un proceso')"
                @delete="openFormModal(modalDeleteOptions,$event,'delete','Cambio de estado de un proceso')"
                @logs="openFormModal(modalLogsOptions,$event,'logs',`Logs del proceso - ${$event.title}`)"
                @addSpeaker="addSpeaker($event)"
                @gestion_colab="openModalGestionColab($event)"
                @send_emails="openModalCorreoSegmentados($event)"
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

        <ModalMaxColaborador
            :ref="modalMaxColaborador.ref"
            v-model="modalMaxColaborador.open"
            width="560px"
            :max_benefits="max_benefits_x_users"
            @closeModalMaxColaborador="modalMaxColaborador.open = false"
            @confirmModalMaxColaborador="confirmModalMaxColaborador"
        />

        <ModalSelectTemplate
                :ref="modalSelectTemplate.ref"
                v-model="modalSelectTemplate.open"
                width="650px"
                @onCancel="modalSelectTemplate.open = false"
                @selectTemplateOrNewProcessModal="selectTemplateOrNewProcessModal"
            />

        <ModalSegment
            ref="ModalSegment"
            v-model="modalSegment.open"
            :width="'870px'"
            @onClose="closeModalSegment"
            @onConfirm="confirmModalSegment"
            :segmentdata="dataModalSegment"
        />
        <ModalSelectSpeaker
            :ref="modalSelectSpeaker.ref"
            v-model="modalSelectSpeaker.open"
            :data="modalSelectSpeaker.data"
            width="650px"
            @closeModalSelectSpeaker="modalSelectSpeaker.open = false"
            @confirmSelectSpeaker="confirmSelectSpeaker"
            @saveSelectSpeaker="saveSelectSpeaker"
            @newSpeaker="newSpeaker"
            :show_button="true"
            />
        <ModalGestorColaboradores
            :ref="modalGestorColaboradores.ref"
            v-model="modalGestorColaboradores.open"
            :data="modalGestorColaboradores.data"
            :segmentados="modalGestorColaboradores.segmentados"
            :seleccionados="modalGestorColaboradores.seleccionados"
            :benefit_id="modalGestorColaboradores.benefit_id"
            :benefit_name="modalGestorColaboradores.benefit_name"
            width="850px"
            @closemodalGestorColaboradores="modalGestorColaboradores.open = false"
            @confirmModalGestorColaboradores="confirmModalGestorColaboradores"
            />
        <ModalCorreosSegmentados
            :ref="modalCorreosSegmentados.ref"
            v-model="modalCorreosSegmentados.open"
            :data="modalCorreosSegmentados.data"
            :users="modalCorreosSegmentados.users"
            :benefit_id="modalCorreosSegmentados.benefit_id"
            width="560px"
            @closeModalCorreoSegmentados="modalCorreosSegmentados.open = false"
            @confirmModalCorreoSegmentados="confirmModalCorreoSegmentados"
            />

        <ModalCreateProcess
            :ref="modalCreateProcess.ref"
            v-model="modalCreateProcess.open"
            :width="'870px'"
            @onCancel="modalCreateProcess.open = false"
            @onConfirm="saveNewProcessModal"
        />

        <ModalEditProcess
            :ref="modalEditProcess.ref"
            v-model="modalEditProcess.open"
            :width="'870px'"
            @onCancel="modalEditProcess.open = false"
            @onConfirm="saveEditProcessModal"
            :process="modalEditProcess.process"
        />
    </section>
</template>

<script>
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import ModalSelectTemplate from "../../components/Process/ModalSelectTemplate";
import ModalCreateProcess from "../../components/Process/ModalCreateProcess";
import ModalEditProcess from "../../components/Process/ModalEditProcess";

import ModalSelectSpeaker from "../../components/Benefit/ModalSelectSpeaker";
import ModalGestorColaboradores from "../../components/Benefit/ModalGestorColaboradores";
import ModalCorreosSegmentados from "../../components/Benefit/ModalCorreosSegmentados";
import ModalMaxColaborador from "../../components/Benefit/ModalMaxColaborador";

import ModalSegment from "./ModalSegment";

export default {
    components: {
    DefaultStatusModal,
    DefaultDeleteModal,
    ModalSelectTemplate,
    ModalCreateProcess,
    ModalEditProcess,

    ModalSegment,
    ModalSelectSpeaker,
    ModalGestorColaboradores,
    ModalCorreosSegmentados,
    ModalMaxColaborador,
},
    mounted() {
        let vue = this
        vue.loadInfo();
    },
    data() {
        return {
            dataTable: {
                endpoint: '/procesos/search',
                ref: 'BenefitTable',
                headers: [
                    {text: "Título", value: "title_process", align: 'start', sortable: true, width: '40%'},
                    {text: "Progreso", value: "progress_process", align: 'center', sortable: false, width: '30%'},
                    {text: "Edición", value: "actions", align: 'center', sortable: false},
                    {text: "Extras", value: "actions_extras", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'action',
                        method_name: 'edit',
                        complete: false
                    },
                    {
                        text: "Segmentación",
                        icon: 'mdi mdi-clipboard-text',
                        type: 'action',
                        count: 'segments_count',
                        method_name: 'segmentation',
                        complete: false
                    },
                    {
                        text: "Actividades",
                        icon: 'mdi mdi-folder-star',
                        type: 'route',
                        route: 'stages_route',
                        complete: false
                    },
                    {
                        text: "Certificado",
                        icon: 'mdi mdi-file-document-check',
                        type: 'action',
                        count: 'segments_count',
                        method_name: 'segmentation',
                        complete: false
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
                        type: 'action',
                        method_name: 'gestion_colab'
                    },
                    {
                        text: "Duplicar",
                        icon: 'far fa-clone',
                        type: 'action',
                        method_name: 'delete'
                    },
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
            modalSegment: {
                open: false,
                ver_items: false,
                asignar: false,
                subida_masiva: false
            },
            // modal speaker

            modalSelectSpeaker: {
                ref: 'modalSelectSpeaker',
                open: false,
                data: [],
                benefit_id: null,
                speaker_id: null,
                endpoint: '',
            },
            modalGestorColaboradores: {
                ref: 'modalGestorColaboradores',
                open: false,
                data: [],
                segmentados: [],
                seleccionados: [],
                benefit_id: null,
                benefit_name: null,
                speaker_id: null,
                endpoint: '',
            },
            modalCorreosSegmentados: {
                ref: 'modalCorreosSegmentados',
                open: false,
                data: [],
                benefit_id: null,
                users: null,
                endpoint: '',
            },
            modalMaxColaborador: {
                ref: 'BenefitModalMaxColab',
                open: false,
            },
            dataModalSegment: {},

            modalSelectTemplate: {
                ref: 'ModalSelectTemplate',
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
            max_benefits_x_users: 0,
        }
    },
    methods: {
        confirmModalSegment() {
            let vue = this;
            this.showLoader()
            vue.$http.post(`/beneficios/segments/save`, vue.dataModalSegment)
                .then((res) => {
                    if (res.data.type == "success") {
  						vue.$toast.success(`${res.data.data.msg}`, {position: 'bottom-center'});
                        vue.closeModalSegment();
                        vue.refreshDefaultTable(vue.dataTable, vue.filters);
  					}
                    this.hideLoader()
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });
        },
        loadInfo() {
            let vue = this
            const url = `/beneficios/max_benefits_x_users`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.max_benefits_x_users = data.data.max_benefits_x_users
                })
        },
        confirmModalMaxColaborador(value = null) {
            let vue = this;
            if(value != null)
            {
                this.showLoader()
                vue.$http.post(`/beneficios/max_benefits_x_users/update`, {'value': value})
                    .then((res) => {
                        vue.max_benefits_x_users = res.data.data.max_benefits;
                        if (res.data.type == "success") {
                            vue.$notification.success(`${res.data.data.msg}`, {
                                timer: 6,
                                showLeftIcn: false,
                                showCloseIcn: true
                            });
                        }
                        this.hideLoader()
                        vue.modalMaxColaborador.open = false
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                        vue.modalMaxColaborador.open = false
                    });
            }
        },
        closeModalSegment() {
            let vue = this

            vue.dataModalSegment.segments = [];
            vue.dataModalSegment.segmentation_by_document = {
                    segmentation_by_document:[]
                }
            vue.modalSegment.open = false;
        },
        async openModalSegment(benefit, edit = false) {
            let vue = this;

            this.showLoader()

            vue.$http.get(`/beneficios/segments/${benefit.id}`)
                .then((res) => {
                    let res_benefit = res.data.data.benefit;
                    console.log(res);
                    console.log(res_benefit);
                    if (res_benefit != null) {

                        benefit.segmentation_by_document = res_benefit.segmentation_by_document;

                        if(res_benefit.segments != null && res_benefit.segments.length > 0)
                        {
                            benefit.segments = res_benefit.segments;

                            // if no direct segmentation exists, adds one

                            if (!benefit.segments.find(s => s.type_code === 'direct-segmentation')) {
                                benefit.segments.push({
                                    id: `new-segment-${Date.now()}`,
                                    type_code: 'direct-segmentation',
                                    criteria_selected: [],
                                    direct_segmentation: [null]
                                })
                            }

                        } else {
                            benefit.segments = [{
                                id: `new-segment-${Date.now()}`,
                                type_code: 'direct-segmentation',
                                criteria_selected: [],
                                direct_segmentation: [null]
                            }];
                        }

                        vue.dataModalSegment = {...benefit};

                    }else{
                        vue.$notification.warning(`No se pudo obtener datos del beneficio`, {
                            timer: 6,
                            showLeftIcn: false,
                            showCloseIcn: true
                        });
                        vue.closeModalSegment();
                        vue.refreshDefaultTable(vue.dataTable, vue.filters);
                    }
                    this.hideLoader()
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });

            await vue.$refs.ModalSegment.resetValidation()

            vue.modalSegment.open = true;
        },
        addSpeaker( item ) {
            console.log(item.id);
            this.openModalSelectSpeaker(item.id)
        },
        async openModalMaxColaborador() {
            let vue = this
            vue.modalMaxColaborador.open = true
        },
        async openModalSelectActivitys() {
            let vue = this
            vue.modalSelectTemplate.open = true
        },
        selectTemplateOrNewProcessModal( value ) {
            let vue = this
            // window.location.href = '/beneficios/create?type=' + value;
            console.log(value);
            if(value == 'new')
            {
                vue.modalCreateProcess.open = true
                vue.modalSelectTemplate.open = false
            }
        },
        async openModalEditProcess(process, edit = false) {
            let vue = this;

            console.log(process);
            // this.showLoader()
            vue.modalEditProcess.process = process;
            if(process.instructions.length == 0)
            {
                const newID = `n-${Date.now()}`;
                const newInstruction = {
                    id: newID,
                    title: "",
                    active: 1,
                    hasErrors: false
                };
                vue.modalEditProcess.process.instructions.push(newInstruction);
            }
            vue.modalEditProcess.open = true;
        },
        confirmModalCorreoSegmentados( benefit_id = null ) {
            let vue = this;

            if( benefit_id != null )
            {
                vue.showLoader();

                vue.$http.post(`/beneficios/segments/enviar_correo`, {'benefit_id': benefit_id})
                    .then((res) => {
                        if (res.data.type == "success") {
                            vue.$notification.success(`${res.data.data.msg}`, {
                                timer: 6,
                                showLeftIcn: false,
                                showCloseIcn: true
                            });

                            vue.modalCorreosSegmentados.benefit_id = null
                            vue.modalCorreosSegmentados.open = false
                            vue.modalCorreosSegmentados.users = null
                        }
                        this.hideLoader()
                        vue.refreshDefaultTable(vue.dataTable, vue.filters);
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }

        },
        async openModalCorreoSegmentados( benefit = null) {
            let vue = this;

            if(benefit != null)
            {
                vue.showLoader();

                vue.modalCorreosSegmentados.open = true
                vue.modalCorreosSegmentados.benefit_id = benefit.id

                await vue.$http.post(`/beneficios/segments/users`, {'benefit_id': benefit.id})
                    .then((res) => {
                        let users = res.data.data.users;
                        vue.modalCorreosSegmentados.users = users
                        console.log(users);
                        console.log(vue.modalCorreosSegmentados);
                        this.hideLoader()
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        confirmModalGestorColaboradores( benefit_id = null, seleccionados = null) {
            let vue = this;

            if(benefit_id != null && seleccionados != null)
            {
                vue.showLoader();

                vue.$http.post(`/beneficios/colaboradores/update`, {'benefit_id': benefit_id, 'seleccionados': seleccionados})
                    .then((res) => {
                        if (res.data.type == "success") {
                            vue.$notification.success(`${res.data.data.msg}`, {
                                timer: 6,
                                showLeftIcn: false,
                                showCloseIcn: true
                            });

                            vue.modalGestorColaboradores.benefit_id = null
                            vue.modalGestorColaboradores.benefit_name = null
                            vue.modalGestorColaboradores.seleccionados = null
                            vue.modalGestorColaboradores.segmentados = null
                            vue.modalGestorColaboradores.open = false
                        }
                        this.hideLoader()
                        vue.refreshDefaultTable(vue.dataTable, vue.filters);
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        async openModalGestionColab( benefit = null) {
            let vue = this;

            if(benefit != null)
            {
                vue.showLoader();

                vue.modalGestorColaboradores.open = true
                vue.modalGestorColaboradores.benefit_id = benefit.id
                vue.modalGestorColaboradores.benefit_name = benefit.title

                await vue.$http.post(`/beneficios/colaboradores/suscritos`, {'benefit_id': benefit.id})
                    .then((res) => {
                        let res_seleccionados = res.data.data.users;
                        let res_segmentados = res.data.data.segmentados;
                        vue.modalGestorColaboradores.seleccionados = res_seleccionados
                        vue.modalGestorColaboradores.segmentados = res_segmentados
                        console.log(res_seleccionados);
                        this.hideLoader()
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        async openModalSelectSpeaker( benefit_id = null) {
            let vue = this;

            vue.showLoader();

            vue.modalSelectSpeaker.open = true
            vue.modalSelectSpeaker.benefit_id = benefit_id

                await vue.$http.get(`/beneficios/speakers/search`)
                    .then((res) => {
                        let res_speakers = res.data.data.data;
                        vue.modalSelectSpeaker.data = res_speakers
                        this.hideLoader()
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
        },
        confirmSelectSpeaker( value ){
            let vue = this;
            console.log(value.id);
            vue.modalSelectSpeaker.speaker_id = value.id

            console.log(vue.modalSelectSpeaker.benefit_id );
            // vue.modalSelectSpeaker.open = false
            // vue.modalSelectSpeaker.benefit_id = null
        },
        saveSelectSpeaker() {
            let vue = this;
            console.log(vue.modalSelectSpeaker.speaker_id );
            console.log(vue.modalSelectSpeaker.benefit_id );
            let speaker_id = vue.modalSelectSpeaker.speaker_id;
            let benefit_id = vue.modalSelectSpeaker.benefit_id;

            if(benefit_id != null && speaker_id != null)
            {
                this.showLoader()
                vue.$http.post(`/beneficios/assigned_speaker`, {'benefit_id': benefit_id, 'speaker_id': speaker_id})
                    .then((res) => {
                        if (res.data.type == "success") {
                            vue.$notification.success(`${res.data.data.msg}`, {
                                timer: 6,
                                showLeftIcn: false,
                                showCloseIcn: true
                            });

                            vue.modalSelectSpeaker.speaker_id = null
                            vue.modalSelectSpeaker.benefit_id = null
                            vue.modalSelectSpeaker.open = false
                        }
                        this.hideLoader()
                        vue.refreshDefaultTable(vue.dataTable, vue.filters);
            console.log(vue.modalSelectSpeaker.speaker_id );
            console.log(vue.modalSelectSpeaker.benefit_id );
                    })
                    .catch((err) => {
                        console.log(err);
                        this.hideLoader()
                    });
            }
        },
        newSpeaker(){
            window.location.href = `/speakers/create`;
        },
        saveNewProcessModal( item ) {

            console.log(item);
            console.log(item.title);
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
                };
                const fields = ['title', 'description', 'limit_absences', 'absences'];
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
        saveEditProcessModal( item ) {

            console.log(item);
            console.log(item.title);
            let vue = this

            vue.showLoader()

            let url = `${vue.base_endpoint}/update/${item.id}`
            let method = 'PUT';

            if(item.title != '')
            {
                const resource = {
                    'title' : item.title,
                    'description' : item.description,
                    'limit_absences' : item.limit_absences,
                    'absences' : item.absences,
                };
                const fields = ['title', 'description', 'limit_absences', 'absences'];
                const formData = vue.getMultipartFormData(method, resource, fields);
                // formData.append('validateForm', validateForm ? "1" : "0");

                vue.$http.post(url, formData)
                        .then(async ({data}) => {
                            this.hideLoader()
                            vue.showAlert(data.data.msg)
                            vue.modalEditProcess.open = false
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
        saveNewProcessInline( item ) {

            console.log(item);
            console.log(item.title);
            let vue = this

            vue.showLoader()

            const edit = vue.benefit_id !== ''
            let url = `${vue.base_endpoint}/store_inline`
            let method = 'POST';

            if(item.title != '')
            {
                const resource = { 'title' : item.title };
                const fields = ['title'];
                const formData = vue.getMultipartFormData(method, resource, fields);
                // formData.append('validateForm', validateForm ? "1" : "0");

                vue.$http.post(url, formData)
                        .then(async ({data}) => {
                            this.hideLoader()
                            vue.showAlert(data.data.msg)
                            // setTimeout(() => vue.closeModal(), 2000)
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
