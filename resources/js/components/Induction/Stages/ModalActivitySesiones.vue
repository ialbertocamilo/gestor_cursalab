<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        content-class="br-dialog"
    >
        <template v-slot:content>
            <v-form ref="meetingForm" class="--mb-15">

                <DefaultErrors :errors="errors"/>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex py-1">
                        <span class="text-bold">Información de la sesión</span>
                    </v-col>
                    <v-col cols="12" class="py-0 separated">
                        <DefaultDivider class="--divider_light primary"/>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput
                            v-model="resource.name"
                            label="Nombre"
                            :dense="true"
                            :rules="rules.name"/>
                    </v-col>
                    <v-col cols="12">
                        <DefaultTextArea label="Descripción" v-model="resource.upload_name" />
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="8">
                        <DefaultAutocomplete
                            :items="selects.hosts"
                            v-model="resource.host"
                            label="Anfitrión"
                            item-text="fullname"
                            return-object
                            :rules="rules.host"
                            @onChange="changeHost"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around" class="mb-2">
                    <v-col cols="4">
                        <DefaultInputDate
                            placeholder="Fecha"
                            reference-component="EventoEnVivoInputDate1"
                            show-required
                            :options="modalDateOptions"
                            label="Fecha"
                            v-model="resource.date"
                            :rules="rules.date"
                            :disabled="resource.status && resource.status.code == 'in-progress'"
                        />
                    </v-col>
                    <v-col cols="4">
                        <DefaultInput
                            type="time"
                            show-required
                            label="Hora de inicio"
                            v-model="resource.time"
                            :rules="rules.time"
                            step="60"
                            :disabled="resource.status && resource.status.code == 'in-progress'"
                        />
                    </v-col>
                    <v-col cols="4">
                        <DefaultInput
                            :clearable="false"
                            show-required
                            label="Duración"
                            v-model="resource.duration"
                            :rules="rules.duration"
                            suffix="min."
                            placeholder="0"
                            type="number"
                            step="15"
                            min="15"
                            max="360"
                            :disabled="resource.status && resource.status.code == 'in-progress'"
                        />
                    </v-col>
                </v-row>


                <DefaultModalSectionExpand
                    title="Avanzado"
                    :expand="sections.showSectionAdvanced"
                    class="my-4 bg_card_none"
                >
                    <template slot="content">

                        <v-row>
                            <v-col cols="6" class="p-0">
                                <DefaultAutocomplete
                                    dense
                                    label="Indicar actividad requisito"
                                    v-model="resource.requirement"
                                    :items="selects.requirement_list"
                                    item-text="name"
                                    item-value="code"
                                    :openUp="true"
                                />
                            </v-col>
                        </v-row>

                    </template>
                </DefaultModalSectionExpand>
            </v-form>

        </template>
    </DefaultDialog>
</template>

<script>

import moment from 'moment'

moment.locale("es");

import Editor from "@tinymce/tinymce-vue";
import DefaultLogoImage from "../../../components/globals/DefaultLogoImage";
import VueTimepicker from 'vue2-timepicker'
import 'vue2-timepicker/dist/VueTimepicker.css'


const fields = ['name', 'starts_at', 'date','model_id' ,'time', 'duration', 'attendants', 'type','model_type', 'host', 'description','requirement'];

export default {
    components: {
        DefaultLogoImage,
        VueTimepicker,
        editor: Editor
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            sections: {
                showSectionAdvanced: {status: false},
            },
            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,

                name: null,

                // date: this.options.action === 'edit' ? null : moment().format("YYYY-MM-DD"),
                // time: this.options.action === 'edit' ? null : moment().add((10 - (moment().minute() % 10)), 'minutes').format("HH:mm"),
                // duration: this.options.action === 'edit' ? null : 30,

                date: null,
                time: null,
                duration: null,

                starts_at: null,
                type: null,
                model_id:null,
                model_type:null,
                host: null,
                status: {code: null},
                description: '',

                attendants: [],
            },
            resource: {},
            modalDateOptions: {
                ref: 'DateEvent',
                open: false,
            },
            selects: {
                types: [],
                benefits :[],
                silabos:[],
                hosts: [],
                requirement_list: [{'code':'none', 'name': 'Sin requisito'}]
            },
            modalScheduledMeetings: {
                open: false,
                meetings: []
            },
            modalSearchAttendants: {
                ref: 'MeetingSearchAttendants',
                open: false,
                base_endpoint: '/aulas-virtuales',
                resource: 'meetingAttendants',
                confirmLabel: 'Agregar',
                cancelLabel: 'Cerrar',
            },
           /* modalInfoCreateMeeting:{
                ref: 'MeetingInfoCreateModal',
                open: false,
                base_endpoint: '/aulas-virtuales/cuentas',
                resource:{ status: {color: null} },
                hideConfirmBtn: true,
                cancelLabel: 'Cerrar',
            },*/
            rules: {
                name: this.getRules(['required', 'max:255']),
                type: this.getRules(['required']),
                host: this.getRules(['required']),
                date: this.getRules(['required']),
                time: this.getRules(['required']),
                duration: this.getRules(['required']),
            },
        }
    },
    computed: {
        getCoHostAttendant() {
            let vue = this
            return vue.selects.user_types.find(el => el.code === 'cohost') || null
        },
        getNormalAttendant() {
            let vue = this
            return vue.selects.user_types.find(el => el.code === 'normal') || null
        },
        // getTitleModalSearchAttendants() {
        //     let vue = this
        //     if (!vue.resource || !vue.resource.date || !vue.resource.time || !vue.resource.duration)
        //         return 'Buscar asistentes'

        //     const date = moment(`${vue.resource.date}`).format("DD/MM/YYYY")

        //     const starts = moment(`${vue.resource.date} ${vue.resource.time}`).format("HH:mm a")
        //     const finishes = moment(`${vue.resource.date} ${vue.resource.time}`)
        //         .add(vue.resource.duration, 'minutes')
        //         .format("HH:mm a")

        //     return `Buscar asistentes - ${date} ${starts} a ${finishes}` || ""
        // },
    },
    methods: {
        changeHost() {
            let vue = this

            let attendants = vue.resource.attendants
            let host = vue.resource.host

            let hostAsAttendantIndex = attendants.findIndex(el => el.usuario_id === (host ? host.id : null))

            if (hostAsAttendantIndex !== -1) {
                attendants.splice(hostAsAttendantIndex, 1)
            }
        },
        async changeType(){
            let vue = this;
            if(vue.resource.type && vue.resource.type.code == 'benefits'){
                await vue.$http.get("/beneficios/search?types[]=sesion_online&types[]=sesion_hibrida")
                .then(({data}) => {
                    vue.selects.benefits = data.data.data;
                    this.hideLoader()
                })
                .catch((err) => {
                    console.log(err);
                    this.hideLoader()
                });
            }else{
                vue.selects.benefits = [];
                vue.selects.silabos = [];
                vue.resource.benefit = null;
                vue.resource.model_id = null;
                vue.resource.name = null;
                vue.resource.date = null;
                vue.resource.time = null;
                vue.resource.starts_at = null;
            }
        },
        async changeBenefit(){
            let vue = this;
            vue.selects.silabos = [];
            vue.resource.model_id = null;
            await vue.$http.get(`/beneficios/search/${vue.resource.benefit.id}`).then(({data}) => {
                vue.selects.silabos = data.data.data.silabo;
                this.hideLoader()
            })
            .catch((err) => {
                console.log(err);
                this.hideLoader()
            });
        },
        changeSilabo(){
            let vue = this;
            const date = vue.resource.model_id.value_date;
            const time = vue.resource.model_id.value_time;
            vue.resource.name = vue.resource.model_id.name;
            vue.resource.date = moment(date).format("YYYY-MM-DD");
            vue.resource.time = moment(`${date} ${time}`).format("HH:mm") ;
            vue.resource.starts_at = `${vue.resource.date} ${vue.resource.time}`
        },
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('meetingForm')
            // vue.$refs.meetingForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('meetingForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`

            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';


            vue.resource.model_id = vue.options.model_id;
            vue.resource.model_type = 'App\\Models\\Stage';

            // if (validateForm && validateSelectedModules) {
            if (validateForm) {
                let formData = vue.getMultipartFormData(method, vue.resource, fields);

                vue.parseAttendants(formData)
                // this.hideLoader();

                vue.$http.post(url, formData)
                    .then(({data}) => {

                      // console.log('data_post_meeting', data);

                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                        vue.queryStatus("aulas_virtuales", "crear_reunion");

                       /* setTimeout(() => {
                          vue.openFormModal(vue.modalInfoCreateMeeting, null, null, 'Reunión creada correctamente')
                          vue.modalInfoCreateMeeting.resource = data.data.status
                        }, 500)*/

                    }).catch((error) => {
                    this.hideLoader()

                    if (error && error.errors)
                        vue.errors = error.errors
                })
            } else {
                this.hideLoader()
            }
        },
        parseAttendants(formData) {
            let vue = this
            // const normal_attendant = vue.selects.user_types.find(el => el.code === 'normal')
            const normal_attendant_id = vue.getNormalAttendant.id
            // const co_host_attendant = vue.selects.user_types.find(el => el.code === 'cohost')
            const co_host_attendant_id = vue.getCoHostAttendant.id
            // const host_attendant = vue.selects.user_types.find(el => el.code === 'hostl')

            vue.resource.attendants.forEach((el, index) => {
                formData.append(`list_attendants[${index}][usuario_id]`, el.usuario_id)
                formData.append(`list_attendants[${index}][type_id]`, el.isCoHost ? co_host_attendant_id : normal_attendant_id)
                formData.append(`list_attendants[${index}][id]`, el.id || null)
            })
        },
        resetSelects() {
            let vue = this

            vue.selects.types = []
            vue.selects.hosts = []
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                // vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
                // vue.resource = Object.assign({}, {...vue.resource}, {...vue.resourceDefault})
                // vue.resource = {...vue.resourceDefault, ...vue.resource}
                vue.resource = JSON.parse(JSON.stringify(vue.resourceDefault))
            })

            let base = `${vue.options.base_endpoint}`

            // let url = resource ? `${base}/${resource.id}/edit` : `${base}/form-selects`;
            let url = `${base}/form-selects`;

            if (vue.options.action == 'edit') url = `${base}/edit/${resource.id}`
            if (vue.options.action == 'duplicate') url = `${base}/${resource.id}/duplicate-data`


            await vue.$http.get(url).then(({data}) => {

                let _data = data.data

                // vue.selects.types = _data.types
                vue.selects.hosts = _data.hosts
                vue.selects.user_types = _data.user_types
                // vue.selects.benefits = _data.benefits ? _data.benefits : [];
                // vue.selects.silabos = _data.silabos ? _data.silabos : [];
                // // TODO: Por ahora
                // if (_data.default_meeting_type)
                //     vue.resource.type = _data.default_meeting_type.id
                if (resource) {
                    // vue.resource = vue.options.action == 'duplicate' ? _data.duplicate : _data.meeting

                    if (vue.options.action == 'duplicate') {

                        Object.assign(vue.resource, _data.duplicate)

                    } else {

                        vue.resource = _data.meeting
                    }

                }
            })

            if (!resource || (vue.resource && !vue.resource.id)) {
                vue.resource.date = moment().format("YYYY-MM-DD");
                vue.resource.time = moment().add((10 - (moment().minute() % 10)), 'minutes').format("HH:mm");
                vue.resource.duration = 30;
            }
            return 0;
        },
        async loadSelects()
        {
            let vue = this;
            let url = `${vue.options.base_endpoint}/form-selects`

            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.requirement_list = data.data.requirements
                })
        },
        // addAttendants(attendants) {
        //     let vue = this
        //     if (attendants.length > 0)
        //         attendants.forEach(el => {
        //             const isAttendantAdded = vue.resource.attendants.findIndex(attendant => attendant.usuario_id == el.id)
        //             // console.log(isAttendantAdded)

        //             if (isAttendantAdded === -1) {
        //                 vue.resource.attendants.push({
        //                     usuario_id: el.id,
        //                     type_id: null,
        //                     nombre: el.nombre,
        //                     dni: el.dni,
        //                     apellido_paterno: el.apellido_paterno,
        //                     apellido_materno: el.apellido_materno,
        //                     config: el.config,
        //                     isCoHost: el.isCoHost
        //                 })
        //             } else {
        //                 // Notificar que ya se encuentra agendado?
        //             }
        //         })

        // },
        // viewScheduledMeetings(attendant) {
        //     let vue = this

        //     const invitations = vue.getScheduledMeetings(attendant)

        //     vue.modalScheduledMeetings.meetings = invitations.map(el => {
        //         return {
        //             name: el.meeting.name || "Sin título",
        //             date: vue.getRangeTimeWithDuration(el.meeting),
        //         }
        //     })
        //     vue.modalScheduledMeetings.open = true
        // },
        // removeAttendant(attendantIndex) {
        //     let vue = this
        //     vue.resource.attendants.splice(attendantIndex, 1)

        // }
    }
}
</script>


<style lang="scss">
//.display-time all-selected{
//    border-color: #dee2e6 !important;
//    width: 100% !important;
//    height: 100% !important;
//}
.custom-vue-timepicker {
    width: 100% !important;
    height: 100% !important;

    input {
        border-color: #dee2e6 !important;
        width: 100% !important;
        height: 100% !important;

        font-size: 1.15em !important;
    }

    ul li:not([disabled]).active, .vue__time-picker-dropdown ul li:not([disabled]).active:focus, .vue__time-picker-dropdown ul li:not([disabled]).active:hover, .vue__time-picker .dropdown ul li:not([disabled]).active, .vue__time-picker .dropdown ul li:not([disabled]).active:focus, .vue__time-picker .dropdown ul li:not([disabled]).active:hover {
        background: #796AEE !important;
    }

}
</style>
