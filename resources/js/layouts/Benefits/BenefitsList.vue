<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Beneficios
                <v-spacer/>
                <DefaultModalButton :label="'Beneficios'" @click="openModalSelectActivitys()"/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start" class="align-items-center">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar beneficios"
                            append-icon="mdi-magnify"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="4">
                        <div class="bx_max_colaboradores">
                            <p>Max cant. permitida por colaborador</p>
                            <span>{{ max_benefits_x_users }}</span>
                            <div class="btns_change_max_col">
                                <div class="btn_change_max_col_up" @click="openModalMaxColaborador">
                                    <img src="/img/benefits/create.svg" style="width: 15px; margin-top: -2px;">
                                </div>
                            </div>
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>

            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @segmentation="openModalSegment($event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Cambio de estado de un beneficio')"
                @delete="openFormModal(modalDeleteOptions,$event,'delete','Cambio de estado de un beneficio')"
                @logs="openFormModal(modalLogsOptions,$event,'logs',`Logs del Beneficio - ${$event.title}`)"
                @addSpeaker="addSpeaker($event)"
                @gestion_colab="openModalGestionColab($event)"
                @send_emails="openModalCorreoSegmentados($event)"
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

        <ModalSelectActivity
                :ref="modalSelectActivity.ref"
                v-model="modalSelectActivity.open"
                width="650px"
                @onCancel="modalSelectActivity.open = false"
                @selectTypeActivityModal="selectTypeActivityModal"
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

    </section>
</template>

<script>
import DefaultStatusModal from "../Default/DefaultStatusModal";
import DefaultDeleteModal from "../Default/DefaultDeleteModal";
import ModalSelectActivity from "../../components/Benefit/ModalSelectActivity";
import ModalSelectSpeaker from "../../components/Benefit/ModalSelectSpeaker";
import ModalGestorColaboradores from "../../components/Benefit/ModalGestorColaboradores";
import ModalCorreosSegmentados from "../../components/Benefit/ModalCorreosSegmentados";
import ModalMaxColaborador from "../../components/Benefit/ModalMaxColaborador";

import ModalSegment from "./ModalSegment";

export default {
    components: {
    DefaultStatusModal,
    DefaultDeleteModal,
    ModalSelectActivity,
    ModalSegment,
    ModalSelectSpeaker,
    ModalGestorColaboradores,
    ModalCorreosSegmentados,
    ModalMaxColaborador
},
    mounted() {
        let vue = this
        vue.loadInfo();
    },
    data() {
        return {
            dataTable: {
                endpoint: '/beneficios/search',
                ref: 'BenefitTable',
                headers: [
                    {text: "Nombre", value: "title", align: 'start', sortable: true},
                    {text: "Facilitador(a)", value: "benefit_speaker", align: 'center', sortable: false},
                    {text: "Tipo", value: "benefit_type", align: 'center', sortable: false},
                    {text: "Opciones", value: "actions", align: 'center', sortable: false},
                ],
                actions: [
                    {
                        text: "Editar",
                        icon: 'mdi mdi-pencil',
                        type: 'route',
                        route: 'edit_route'
                    },
                    {
                        text: "Segmentación",
                        icon: 'fa fa-square',
                        type: 'action',
                        count: 'segments_count',
                        method_name: 'segmentation'
                    },
                ],
                more_actions: [
                    {
                        text: "Actualizar Estado",
                        icon: 'fa fa-circle',
                        type: 'action',
                        method_name: 'status'
                    },
                    // {
                    //     text: "Duplicar",
                    //     icon: 'far fa-trash-alt',
                    //     type: 'action',
                    //     method_name: 'delete'
                    // },
                    {
                        text: "Gestión de colab.",
                        icon: 'fas fa-user-cog',
                        type: 'action',
                        method_name: 'gestion_colab'
                    },
                    {
                        text: "Envio de correos",
                        icon: 'fas fa-envelope',
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

            modalSelectActivity: {
                ref: 'ModalSelectActivity',
                open: false,
                endpoint: '',
            },

            filters: {
                q: null
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
                base_endpoint: '/beneficios',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
                content_modal: {
                    delete: {
                        title: '¡Estás por eliminar un Beneficio!',
                        details: [
                            'Este beneficio no podrá ser visto por los usuarios.',
                            'La información eliminada no podra recuperarse'
                        ],
                    }
                },
                width: '408px'
            },
            modalStatusOptions: {
                ref: 'BenefitStatusModal',
                open: false,
                base_endpoint: '/beneficios',
                contentText: '¿Desea cambiar de estado a este registro?',
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un Beneficio!',
                        details: [
                            'Este beneficio ahora no podrá ser visto por los usuarios.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un Beneficio!',
                        details: [
                            'Este beneficio ahora podrá ser visto por los usuarios.'
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

                        }
                        else {
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
            vue.modalSelectActivity.open = true
        },
        selectTypeActivityModal( value ) {
            window.location.href = '/beneficios/create?type=' + value;
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
        }
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
