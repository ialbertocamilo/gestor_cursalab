<template>
    <DefaultDialog
        :width="width"
        :options="options"
        :show-card-actions="false"
        persistent
        :cancel-label="'Cerrar'"
        :confirm-label="'Agregar'"
        @onCancel="onCancel"
    >
        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <v-row>
                <v-col cols="5">
                    <DefaultSelect
                        label="Módulo"
                        placeholder="Selecione un módulo"
                        :items="selects.modules"
                        clearable
                        v-model="config_id"
                    />
                    <!-- @onChange="getGrupos" -->
                </v-col>
                <v-col cols="6">
                  <DefaultInput
                        v-model="q"
                        label="Buscar asistentes"
                        placeholder="Buscar por nombre, DNI o correo electrónico"
                        append-icon="mdi-magnify"
                        @onEnter="searchAttendants"
                        @clickAppendIcon="searchAttendants"
                  />
                </v-col>
                <v-col cols="1" class="d-flex align-items-center">
                  <v-file-input
                        class="input-file-meeting-search-attendants"
                        v-model="file"
                        background-color="primary"
                        hide-details="auto"
                        dense
                        clearable
                        outlined
                        hide-input
                        prepend-icon="mdi-file-upload"
                        @change="uploadExcel"
                        title="Subir excel de usuarios"
                  />
                </v-col>

            <!-- <v-col cols="6">
                    <DefaultAutocomplete
                        label="Grupos"
                        placeholder="Selecionar grupos"
                        multiple
                        :items="selects.groups"
                        v-model="groups"
                        @onChange="searchAttendants"
                    />
                </v-col> -->
            </v-row>
            <DefaultDivider class="mx-3"/>
            <v-row>
                <v-col cols="12" class="d-flex justify-content-between align-items-center text-right">
                    <a class="no-hover-link" href="/templates/Plantilla-Asistentes-Reuniones.xlsx" download
                       title="Descargar plantilla">
                        <v-icon color="primary" small>mdi-download</v-icon>
                        Descargar plantilla
                    </a>
                    <a href="javascript:;" @click="selectAllResults" v-show="results.length > 0">
                        {{ allSelected ? 'Deseleccionar todos' : `Seleccionar todos (${results.length})` }}
                    </a>
                </v-col>
            </v-row>

            <div>
                <v-row>

                    <v-col cols="12">
                        <DefaultOverlay v-model="overlay_box_results" absolute
                                        :opacity="0.2"/>

                        <div class="box-meeting-search-attendants ">
                            <div v-if="results.length === 0"
                                 class="d-flex justify-content-center"
                                 v-text="'No hay asistentes disponibles.'"/>
                            <template v-else>
                                <v-virtual-scroll
                                    :bench="0"
                                    :items="results"
                                    :height="`${results.length < 5 ? results.length * 64 : '300' }`"
                                    item-height="64"
                                >
                                    <template v-slot:default="{ item }">
                                        <div class="item-meeting-search-attendants-results">
                                            <v-simple-checkbox
                                                v-model="item.checked"
                                                @input="changeDisableAddBtn(item)"
                                            />
                                            <DefaultLogoImage
                                                :image="item.config.logo"
                                                class="mx-2"
                                                max-width="70"
                                            />
                                            {{ item.dni }} - {{ item.nombre }}
                                            <v-chip
                                                v-if="item.show"
                                                class="default-chip default-chip-cursor-pointer ml-2"
                                                small
                                                color="error"
                                                active-class="default-chip"
                                                @click="
                                                modalScheduledMeetings.header = {title: `${item.dni} - ${item.nombre}`, logo: item.config.logo  };
                                                viewScheduledMeetings(item)"
                                            >
                                                {{ item.count }}
                                                <v-icon small class="ml-1">mdi-calendar</v-icon>
                                            </v-chip>
                                        </div>
                                        <DefaultDivider/>
                                    </template>
                                </v-virtual-scroll>

                            </template>
                        </div>
                    </v-col>
                    <v-col cols="12" class="py-0 m-0 px-3">
                        <transition name="fade">
                            <DefaultSimpleMessageAlert dense :msg="msgAttendantsAdded" v-if="msgAttendantsAdded"/>
                        </transition>
                    </v-col>
                </v-row>
            </div>

            <v-row justify="center" class="mx-0">
                <v-col cols="4" class="d-flex justify-content-around">
                    <v-btn
                        class="default-modal-action-button  mx-1"
                        text
                        elevation="0"
                        :ripple="false"
                        color="primary"
                        @click="onCancel"
                        v-text="'Cerrar'"
                    />
                    <v-btn
                        class="default-modal-action-button mx-1"
                        elevation="0"
                        :ripple="false"
                        color="primary"
                        @click="onConfirm"
                        :disabled="disable_add_btn"
                        v-text="'Agregar'"
                    />
                </v-col>
            </v-row>
            <ModalScheduledMeetings
                @onClose="modalScheduledMeetings.open=false; modalScheduledMeetings.meetings = []"
                :options="modalScheduledMeetings"
            />
        </template>
    </DefaultDialog>
</template>

<script>
import moment from 'moment'

moment.locale("es");

import DefaultLogoImage from "../../components/globals/DefaultLogoImage";
import ModalScheduledMeetings from "./ModalScheduledMeetings";

export default {
    components: {DefaultLogoImage, ModalScheduledMeetings},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        host_config_id: {
            type: String | Number,
            default: null
        },
        data: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            overlay_box_results: false,
            errors: [],
            resource: null,
            q: null,
            q_results: null,
            groups: [],
            results: [],
            selects: {
                groups: [],
                modules: []
            },
            config_id: null,
            file: null,
            modalScheduledMeetings: {
                open: false,
                meetings: []
            },
            msgAttendantsAdded: null,
            allSelected: false,
            disable_add_btn: true
        }
    },
    methods: {
        onCancel() {

            let vue = this
            vue.errors = []
            vue.resetValidation()
            vue.$emit('onCancel')

        },
        resetValidation() {

            let vue = this

            vue.resource = null
            vue.q = null
            vue.q_results = null
            vue.results = []
            vue.groups = []
            vue.config_id = null
            vue.file = null
            vue.disable_add_btn = true
        },
        onConfirm() {

            let vue = this
            vue.errors = []

            const selected_attendants = vue.results.filter(el => el.checked)
            const count_selected_attendants = selected_attendants.length

            if (count_selected_attendants > 0) {

                vue.msgAttendantsAdded = `Se agregó <strong>${count_selected_attendants}</strong> asistente${count_selected_attendants > 0 ? 's' : ''} `
                setTimeout(() => {
                    vue.msgAttendantsAdded = null
                }, 2000)
                vue.$emit('onConfirm', selected_attendants)
                vue.disable_add_btn = true

            }
        },
        loadData(resource) {
            let vue = this
            vue.resetValidation()
        },
        loadSelects() {

            let vue = this

            let url = `${vue.options.base_endpoint}/get-selects-search-filters?`
            vue.$http.get(url)
                .then(({data}) => {

                    let _data = data.data
                    vue.selects.modules = _data.modulos

                    vue.config_id = vue.host_config_id

                    // if (vue.config_id)
                        // vue.getGrupos()
                })
        },
        getGrupos() {
            let vue = this
            let url = `${vue.options.base_endpoint}/get-selects-search-filters?`
            console.log(`CONFIG_ID => ${vue.config_id}`)
            if (vue.config_id)
                url += `config_id=${vue.config_id}`
            vue.groups = []

            vue.$http.get(url)
                .then(({data}) => {
                    let _data = data.data
                    vue.selects.groups = _data.grupos
                })
        },
        searchAttendants() {
            let vue = this

            vue.errors = []

            if (!vue.q && vue.groups.length === 0) {
                vue.results = []
                return
            }

            vue.activateOverlay('overlay_box_results')

            const tempData = {
                date: vue.data.date,
                time: vue.data.time,
                duration: vue.data.duration,
                q: vue.q,
                meeting_id: vue.data.meeting_id,
                grupos_id: vue.groups,
                config_id: vue.config_id,
                exclude_host_id: vue.data.host_id,
            }

            let filters = vue.addParamsToURL("", tempData)
            const url = `${vue.options.base_endpoint}/search-attendants?${filters}`

            vue.$http.get(url)
                .then(({data}) => {
                    let _data = data.data
                    if (_data.attendants.length > 0) {
                        vue.results = []
                        vue.disable_add_btn = false
                    }
                    vue.results = vue.parseResponseAttendants(_data.attendants)

                    vue.inactivateOverlay('overlay_box_results')
                })
                .catch((error) => {
                    vue.inactivateOverlay('overlay_box_results')
                    if (error && error.errors)
                        vue.errors = error.errors
                })
        },
        uploadExcel() {
            let vue = this
            if (vue.file) {
                vue.showLoader()
                let formData = new FormData()
                formData.append("file", vue.file)
                formData.append("date", vue.data.date)
                formData.append("time", vue.data.time)
                formData.append("duration", vue.data.duration)
                if(vue.data.host_id)
                    formData.append("exclude_host_id", vue.data.host_id)
                if (vue.data.meeting_id)
                    formData.append("meeting_id", vue.data.meeting_id)

                const url = `${vue.options.base_endpoint}/upload-attendants`
                vue.$http.post(url, formData)
                    .then(({data}) => {
                        console.log('upload_excel', data);

                        let _data = data.data
                        vue.file = null
                        vue.results = vue.parseResponseAttendants(_data.attendants, true)

                        vue.hideLoader()
                        vue.disable_add_btn = false
                    })
                    .catch(e => {
                        console.log(e)
                        vue.hideLoader()
                        vue.file = null
                        vue.disable_add_btn = false
                    })
            }

        },
        parseResponseAttendants(attendants, checked = false) {
            let temp = []
            attendants.forEach(el => {
                let total_invitations = Object.keys(el.invitations).length
                const new_attendant = Object.assign({}, el, {
                    checked: checked,
                    // menu: false,
                    show: total_invitations > 0,
                    count: total_invitations,
                    isCoHost: el.isCoHost
                })
                temp.push(new_attendant)
            })
            return temp
        },
        getScheduledMeetings(attendant) {
            return Object.values(attendant.invitations)
            // return attendant.invitations.filter(el => el.meeting !== null)
        },
        viewScheduledMeetings(attendant) {
            let vue = this

            const invitations = vue.getScheduledMeetings(attendant)

            vue.modalScheduledMeetings.meetings = invitations.map(el => {
                return {
                    name: el.meeting ? el.meeting.name : "Sin título",
                    date: vue.getRangeTimeWithDuration(el.meeting),
                }
            })
            vue.modalScheduledMeetings.open = true
        },
        getRangeTimeWithDuration(meeting) {
            const starts = moment(new Date(meeting.starts_at)).format("HH:mm a")
            const finishes = new moment(new Date(meeting.finishes_at)).format("HH:mm a")

            return `${starts} a ${finishes} (${meeting.duration} minutos) ` || ""
        },
        selectAllResults() {
            let vue = this
            vue.allSelected = !vue.allSelected
            vue.results.forEach(el => el.checked = vue.allSelected)
        },
        changeDisableAddBtn(attendant) {
            let vue = this
            console.log(attendant)
            if (attendant.checked)
                vue.disable_add_btn = false
        }
    }
}
</script>
<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
