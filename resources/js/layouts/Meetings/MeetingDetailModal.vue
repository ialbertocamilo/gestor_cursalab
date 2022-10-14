<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-card tile elevation="0" class="--my-3">
                <v-card-text class="pb-0">

                    <v-tabs
                        v-model="tab"
                        centered
                        class="tabs-detail"
                        grow
                    >
                        <!-- icons-and-text -->
                        <v-tabs-slider></v-tabs-slider>

                        <v-tab href="#main">
                            Detalle general
                            <!-- <v-icon>mdi-text-box-outline</v-icon> -->
                        </v-tab>

                        <v-tab href="#attendants">
                            Lista de invitados
                            <v-chip color="primary" x-small class="ml-3 px-2">
                                {{ resource.attendants
                                    ? resource.attendants.length + 1
                                    : 0
                                }}
                            </v-chip>
                            <!-- <v-icon>mdi-account-multiple</v-icon> -->
                        </v-tab>

                        <v-tab href="#stats" @click="getMeetingStats"
                               v-if="resource.status.code === 'finished'"
                        >
                            Estadísticas
                            <!-- <v-icon>mdi-poll</v-icon> -->
                        </v-tab>
                    </v-tabs>

                    <v-tabs-items v-model="tab" class="tabs-item-detail">

                        <v-tab-item
                            value="main"
                        >
                            <v-card flat>

                                <v-row>
                                    <v-col cols="12" class="text-center">
                                        <h5>{{ resource.name }} - {{ resource.prefix }}</h5>
                                    </v-col>
                                    <v-col cols="12" class="text-center">
                                        <h6>{{ resource.date_title }}</h6>
                                        <v-chip
                                            class="ma-2 white--text"
                                            :color="resource.status.color"
                                            small
                                        >
                                            {{ resource.status.name }}
                                        </v-chip>
                                    </v-col>
                                </v-row>

                                <v-row justify="center" align="center" align-content="center"
                                       v-if="resource.description">
                                    <v-col cols="12" class="d-flex justify-content-center pb-0 text-center"
                                           @click="showDescription = !showDescription"
                                           style="cursor: pointer">
                                        <strong>Ver detalle</strong>
                                        <v-icon v-text="showDescription ? 'mdi-chevron-up' : 'mdi-chevron-down'"/>
                                    </v-col>
                                </v-row>

                                <v-row justify="space-around" align="start" align-content="center"
                                       v-if="resource.description">
                                    <v-col cols="12" class="d-flex justify-content-center pt-0">

                                        <v-expand-transition>
                                            <v-row justify="space-around" align="start" align-content="center"
                                                   v-show="showDescription">
                                                <v-col cols="12" class="text-center" v-html="resource.description"/>
                                            </v-row>
                                        </v-expand-transition>

                                    </v-col>

                                </v-row>

                                <v-divider class="mt-0 mx-10"/>

                                <v-row justify="center" align="center">
                                    <v-col cols="10" class="text-center">
                                        <a v-if="resource.status.code == 'finished' && resource.download_ready"
                                           href="javascript:;"
                                           @click="downloadReport"
                                           class="no-hover-link mr-5"
                                           title="Descarga disponible en 10 minutos aprox.">
                                            <v-icon small >mdi-download</v-icon>
                                            Descargar reporte
                                        </a>

                                        <a href="javascript:;" class="no-hover-link text-right"
                                           @click="openFormDuplicateModal(resource)"
                                        >
                                            <v-icon color="primary" small>mdi-calendar</v-icon>
                                            Agendar nueva reunión
                                        </a>

                                    </v-col>

                                </v-row>

                                <v-row justify="center" align="center" v-if="resource.status.code === 'scheduled'">
                                    <v-col cols="3" class="text-center">

                                        <a href="javascript:;" class="no-hover-link text-right error--text"
                                           @click="openFormModal(modalCancelOptions, resource, 'cancelar', `Cancelar reunión: ${resource.name}`)"
                                        >
                                            <v-icon small color="error">mdi-cancel</v-icon>
                                            Cancelar reunión
                                        </a>
                                        <MeetingCancelModal
                                            :options="modalCancelOptions"
                                            width="60vw"
                                            :ref="modalCancelOptions.ref"
                                            @onCancel="closeSimpleModal(modalCancelOptions)"
                                            @onConfirm="confirmModal"
                                        />
                                    </v-col>

                                    <v-col cols="3" class="text-center">
                                        <a href="javascript:;" class="no-hover-link text-right error--text"
                                           @click="openFormModal(modalDeleteOptions, resource, 'eliminar', `Eliminar reunión: ${resource.name}`)"
                                        >
                                            <v-icon small color="error">mdi-trash-can</v-icon>
                                            Eliminar reunión
                                        </a>
                                        <MeetingDeleteModal
                                            :options="modalDeleteOptions"
                                            width="60vw"
                                            :ref="modalDeleteOptions.ref"
                                            @onCancel="closeSimpleModal(modalDeleteOptions)"
                                            @onConfirm="confirmModal"
                                        />
                                    </v-col>

                                </v-row>

                                <v-divider class="mt-0-- mx-10"/>

                                <v-row justify="space-around" align="start" align-content="center">
                                    <v-simple-table class="v-table-data-custom mt-0 mx-0 col-10">
                                        <template v-slot:default>
                                            <tbody>

                                            <tr v-if="resource.url_start">
                                                <th width="35%">Enlace de inicio</th>
                                                <td colspan="3" align="center" class="word-break">
                                                    <div class="d-flex justify-content-center align-content-center">

                                                        <MeetingStartModal
                                                            :options="modalStartOptions"
                                                            width="60vw"
                                                            :ref="modalStartOptions.ref"
                                                            @onCancel="closeSimpleModal(modalStartOptions)"
                                                            @onConfirm="confirmModal"
                                                        />

                                                        <a href="javascript:;"
                                                           v-if="resource.status.code == 'scheduled'"
                                                           class="no-hover-link"
                                                           @click="openFormModal(modalStartOptions, resource, 'iniciar', `Iniciar reunión: ${resource.name}`)"
                                                        >
                                                            <v-icon small color="primary">mdi-launch</v-icon>
                                                            Iniciar reunión
                                                        </a>

                                                        <span v-if="resource.status.code == 'in-progress'">
                                                            <a href="javascript:;"
                                                               class="no-hover-link float-right mr-2"
                                                               title="Copiar enlace"
                                                               @click="copyToClipboard(resource.url_start)"
                                                            >
                                                                <v-icon small color="primary">mdi-content-copy</v-icon>
                                                                Enlace de inicio manual
                                                            </a>
                                                        </span>
                                                        <span
                                                            v-if="['finished', 'cancelled', 'overdue'].includes(resource.status.code)">
                                                            Enlace no disponible
                                                        </span>
                                                        <v-icon v-if="resource.status.code == 'in-progress'"
                                                                style="right: 0; position: absolute;"
                                                                class="mx-2 mb-1"
                                                                color="primary"
                                                                title="Regenerar link" small
                                                                @click="updateMeetingUrlStart" v-text="'mdi-refresh'"/>
                                                    </div>

                                                </td>
                                            </tr>

                                            <tr v-if="resource.url">
                                                <th width="35%">Enlace de entrada general</th>
                                                <td colspan="3" align="center" class="word-break">

                                                    <!-- <a href="javascript:;" v-if="resource.status.code == 'in-progress'" class="no-hover-link"
                                                        @click="enterMeeting(resource.url)"
                                                    >
                                                        <v-icon small color="primary">mdi-launch</v-icon>
                                                        Ingresar a la reunión
                                                    </a> -->

                                                    <a href="javascript:;" class="no-hover-link" title="Copiar enlace"
                                                       @click="copyToClipboard(resource.url)"
                                                       v-if="resource.status.code == 'in-progress'"
                                                    >
                                                        <v-icon small color="primary">mdi-content-copy</v-icon>

                                                        Enlace para invitados
                                                    </a>
                                                    <span v-if="resource.status.code != 'in-progress'">
                                                        Enlace no disponible
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="35%">Cantidad de asistentes</th>
                                                <td align="center">{{ resource.real_attendants_count }} asistentes /
                                                    {{ resource.attendants_count }}
                                                    invitados
                                                    ({{ resource.real_percentage_attendees }}%)
                                                </td>
                                            </tr>
                                            <tr>
                                                <th width="35%">Fecha programada</th>
                                                <td align="center">{{ resource.starts_at_formatted }} -
                                                    {{ resource.finishes_at_formatted }} ({{ resource.duration }} min)
                                                </td>
                                            </tr>
                                            <tr v-if="resource.status.code == 'finished'">
                                                <th width="35%">Fecha real de reunión</th>
                                                <td align="center">{{ resource.started_at_formatted }} -
                                                    {{ resource.finished_at_formatted }} ({{ resource.real_duration }}
                                                    min)
                                                </td>
                                            </tr>

                                            </tbody>
                                        </template>
                                    </v-simple-table>
                                </v-row>


                            </v-card>
                        </v-tab-item>

                        <v-tab-item
                            value="attendants"
                        >
                            <v-card flat>
                                <v-row>
                                    <v-col v-if="resource.host" cols="12" >
                                        <div class="box-meeting-search-attendants"
                                             style="min-height: min-content !important;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="item-meeting-search-attendants-results">
                                                    <DefaultLogoImage
                                                        v-if="resource.host.config"
                                                        :image="resource.host.config.logo"
                                                        class="mx-2"
                                                        max-width="70"
                                                    />
                                                    <div class="clickable--">
                                                        {{ resource.host.document }} -
                                                        {{ resource.host.name }}
                                                    </div>
                                                </div>
                                                <div class="item-options-meeting-search-attendants-results"
                                                     style="padding-right: 1rem;">
                                                    <span>Anfitrión</span>
                                                </div>
                                            </div>
                                        </div>
                                    </v-col>
                                </v-row>

                                <v-row no-gutters class="px-3">
                                    <v-col cols="8">

                                    </v-col>
                                    <v-col cols="4" class="d-flex justify-content-end">
                                        <v-icon :title="'1ra asistencia'"
                                                class="mx-3 clickable"
                                                v-text="'mdi-numeric-1-circle'"
                                        />
                                        <v-icon :title="'2da asistencia'"
                                                class="mx-3 clickable"
                                                v-text="'mdi-numeric-2-circle'"
                                        />
                                        <v-icon :title="'3ra asistencia'"
                                                class="mx-3 clickable"
                                                v-text="'mdi-numeric-3-circle'"
                                        />
                                    </v-col>
                                </v-row>

                                <v-row justify="start" align="start">
                                    <v-col cols="12">
                                        <div class="box-meeting-search-attendants ">
                                            <DefaultOverlay :value="overlay.box_attendants"
                                                            absolute
                                                            :opacity="0.2"/>
                                            <v-virtual-scroll
                                                :bench="0"
                                                :items="resource.attendants"
                                                :height="`${ (resource.attendants && resource.attendants.length < 5) ? resource.attendants.length * 64 : '300' }`"
                                                item-height="64"
                                            >
                                                <template v-slot:default="{ item }">
                                                    <v-row no-gutters>
                                                        <v-col cols="8">
                                                            <div class="item-meeting-search-attendants-results">

                                                                <!-- User's module logo -->

                                                                <DefaultLogoImage
                                                                    :image="item.config ? item.config.logo : ''"
                                                                    class="mx-2"
                                                                    max-width="70"
                                                                />
                                                                <div class="clickable"
                                                                     @click="copyToClipboard(item.link)">
                                                                    {{ item.usuario ? item.usuario.dni : item.dni }} -

                                                                    {{
                                                                        item.usuario ? item.usuario.nombre : item.nombre
                                                                    }}
                                                                </div>

                                                                <v-icon v-if="resource.status.code === 'in-progress' && item.online == 1"
                                                                        class="ml-2"
                                                                        small
                                                                        v-text="'mdi-circle'"
                                                                        color="green"
                                                                        title="En línea"
                                                                />
                                                            </div>
                                                        </v-col>
                                                        <v-col cols="4" class="d-flex justify-content-end">
                                                            <v-icon :title="'1ra asistencia'"
                                                                    class="mx-3 clickable"
                                                                    :color="getColorAndIconByCall(item,'first').color"
                                                                    v-text="getColorAndIconByCall(item,'first').icon"
                                                            />
                                                            <v-icon :title="'2da asistencia'"
                                                                    class="mx-3 clickable"
                                                                    :color="getColorAndIconByCall(item,'middle').color"
                                                                    v-text="getColorAndIconByCall(item,'middle').icon"
                                                            />
                                                            <v-icon :title="'3ra asistencia'"
                                                                    class="mx-3 clickable"
                                                                    :color="getColorAndIconByCall(item,'last').color"
                                                                    v-text="getColorAndIconByCall(item,'last').icon"
                                                            />
                                                        </v-col>
                                                    </v-row>
                                                    <DefaultDivider/>
                                                </template>
                                            </v-virtual-scroll>
                                        </div>
                                    </v-col>
                                </v-row>

                                <v-row justify="start" align="start">
                                    <v-col cols="6" class="text-left pt-0">
                                        <v-icon :title="'Actualización cada 5 minutos'"
                                                class="clickable"
                                                color="primary"
                                                v-text="'mdi-information'"
                                                v-if="resource.status.code === 'in-progress'"
                                        />
                                        <small v-if="resource.report_generated_at_formatted"
                                               :title="resource.report_generated_at_formatted">
                                            Última actualización {{ resource.report_generated_at_diff }}
                                        </small>
                                        <v-tooltip
                                            v-if="resource.status.code === 'in-progress' || isMasterOrAdminCursalab"
                                            bottom>
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-btn
                                                    icon small @click="updateAttendanceData"
                                                    :ripple="false"
                                                    class="m-0 p-0"
                                                    :disabled="disable_btn_update_attendance_data"
                                                    v-bind="attrs"
                                                    v-on="on"
                                                >
                                                    <v-icon small v-text="'mdi-cached'"/>
                                                </v-btn>
                                            </template>
                                            <!--                                            <span v-text="disable_btn_update_attendance_data ?-->
                                            <!--                                               'Podrá actualizar los datos en un minuto'-->
                                            <!--                                               : 'Actualizar datos de asistencia'"/>-->
                                            <span v-text="'Actualizar datos de asistencia'"/>
                                        </v-tooltip>
                                    </v-col>

                                    <v-col cols="6" class="text-right pt-0">
                                        <small>
                                            <strong>Toma de asistencias: </strong>
                                            {{ resource.attendance_call_first_at_formatted }} -
                                            {{ resource.attendance_call_middle_at_formatted }} -
                                            {{ resource.attendance_call_last_at_formatted }}
                                        </small>

                                    </v-col>

                                </v-row>

                            </v-card>
                        </v-tab-item>

                        <v-tab-item
                            value="stats"
                            v-if="resource.status.code === 'finished'"
                        >
                            <v-card flat>
                                <v-row>
                                    <v-col cols="6">
                                        <GeneralGraphic
                                            :graphic_data="graphics.attendeesPerCallAssistance"
                                            @refreshCache=""
                                        />
                                    </v-col>
                                    <v-col cols="6">
                                        <GeneralGraphic
                                            :graphic_data="graphics.topGuestDurationInMeeting"
                                            @refreshCache=""
                                        />
                                    </v-col>

                                </v-row>

                                <v-row justify="center">
                                    <!--                                    <v-col cols="6">-->
                                    <!--                                        <GeneralGraphic-->
                                    <!--                                            :graphic_data="graphics.averageTimeInMeeting"-->
                                    <!--                                            @refreshCache=""-->
                                    <!--                                        />-->
                                    <!--                                    </v-col>-->
                                    <v-col cols="6">
                                        <GeneralGraphic
                                            :graphic_data="graphics.guestsVsAttendees"
                                            @refreshCache=""
                                        />
                                    </v-col>
                                </v-row>

                                <v-divider class="my-10" v-if="isMasterOrAdminCursalab"/>

                                <v-row v-if="isMasterOrAdminCursalab">
                                    <v-col cols="6">
                                        <GeneralGraphic
                                            :graphic_data="graphics.devicesStatsByPlatformFamily"
                                            @refreshCache=""
                                        />
                                    </v-col>
                                    <v-col cols="6">
                                        <GeneralGraphic
                                            :graphic_data="graphics.devicesStatsByBrowserFamily"
                                            @refreshCache=""
                                        />
                                    </v-col>

                                </v-row>
                                <v-row justify="center"
                                       v-if="isMasterOrAdminCursalab">
                                    <v-col cols="6">
                                        <GeneralGraphic
                                            :graphic_data="graphics.statsByDeviceFamily"
                                            @refreshCache=""
                                        />
                                    </v-col>
                                    <v-col cols="6">
                                        <GeneralGraphic
                                            :graphic_data="graphics.statsByDeviceModel"
                                            @refreshCache=""
                                        />
                                    </v-col>
                                </v-row>


                            </v-card>
                        </v-tab-item>

                    </v-tabs-items>

                </v-card-text>
            </v-card>
        </template>

    </DefaultDialog>

</template>
<script>
import moment from 'moment'

moment.locale("es");
import DefaultLogoImage from "../../components/globals/DefaultLogoImage";
import MeetingCancelModal from "./MeetingCancelModal";
import MeetingDeleteModal from "./MeetingDeleteModal";
import MeetingStartModal from "./MeetingStartModal";
import GeneralGraphic from "../General/GeneralGraphic";

export default {
    components: {DefaultLogoImage, MeetingCancelModal, MeetingDeleteModal, MeetingStartModal, GeneralGraphic},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            overlay: {
                box_attendants: false
            },
            disable_btn_update_attendance_data: false,
            tab: 'main',
            showDescription: false,
            resourceDefault: {
                name: null,
                user: {
                    name: '',
                },
                status: {
                    name: '',
                },
                type: {
                    name: '',
                },
                host: {
                    name: '',
                    usuario: {
                        fullname: '',
                        dni: '',
                        config: {logo: ''}
                    }
                },
                description: null,
                date_title: null,
                date_start: null,

                link: null,
                tipo_evento_id: null,
                invitados: [],
                attendants: [],
            },
            resource: {
                user: {
                    name: '',
                },
                status: {
                    name: '',
                },
                type: {
                    name: '',
                },
                host: {
                    name: '',
                    usuario: {
                        fullname: '',
                        dni: '',
                        config: {logo: ''}
                    }
                },
            },
            isMasterOrAdminCursalab: false,
            // results: [],
            modalStartOptions: {
                ref: 'MeetingStartModal',
                open: false,
                base_endpoint: '/aulas-virtuales',
                cancelLabel: 'Cerrar',
                // hideConfirmBtn: true,
            },
            modalCancelOptions: {
                ref: 'MeetingCancelModal',
                open: false,
                base_endpoint: '/aulas-virtuales',
                cancelLabel: 'Cerrar',
                // hideConfirmBtn: true,
            },
            modalDeleteOptions: {
                ref: 'MeetingDeletelModal',
                open: false,
                base_endpoint: '/aulas-virtuales',
                cancelLabel: 'Cerrar',
                // hideConfirmBtn: true,
            },
            graphics: {

                averageTimeInMeeting: {
                    overlay: true,
                    ref: 'averageTimeInMeeting',
                    last_update: null,
                    series: [],
                    chartOptions: {
                        title: {
                            text: 'Minutos promedio total de asistentes',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Minutos promedio total de asistentes',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 20
                        },
                        chart: {
                            id: 'averageTimeInMeeting',
                            type: 'pie',
                            width: "100%",
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        labels: [],
                    },
                },

                guestsVsAttendees: {
                    overlay: true,
                    ref: 'guestsVsAttendees',
                    last_update: null,
                    series: [],
                    chartOptions: {
                        title: {
                            text: 'Asistentes / No Asistentes',
                            align: 'center',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Cantidad de invitados y cantidad de asistentes',
                            align: 'center',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 30
                        },
                        chart: {
                            id: 'guestsVsAttendees',
                            type: 'pie',
                            width: "100%",
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        tooltip: {
                            fixed: {
                                enabled: false,
                                position: 'topRight',
                                offsetX: 0,
                                offsetY: 0,
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        labels: [],
                    },
                },

                attendeesPerCallAssistance: {
                    overlay: true,
                    ref: 'attendeesPerCallAssitance',
                    last_update: null,
                    series: [{
                        name: "Cantidad de asistentes presentes",
                        data: []
                    }],
                    chartOptions: {
                        title: {
                            text: 'Asistencias',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Cantidad de asistentes por llamado de asistencia',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 20
                        },
                        chart: {
                            id: 'attendeesPerCallAssitance',
                            type: 'bar',
                            width: "100%",
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        tooltip: {
                            fixed: {
                                enabled: false,
                                position: 'topRight',
                                offsetX: 0,
                                offsetY: 0,
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        xaxis: {
                            categories: [],
                        },
                    },
                },

                topGuestDurationInMeeting: {
                    overlay: true,
                    ref: 'topGuestDurationInMeeting',
                    last_update: null,
                    series: [{
                        name: "Minutos en la reunión",
                        data: []
                    }],
                    chartOptions: {
                        title: {
                            text: 'Top asistentes',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Top de asistentes con más permanencia en la reunión (en minutos)',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 20
                        },
                        chart: {
                            id: 'topGuestDurationInMeeting',
                            type: 'bar',
                            width: "100%",
                            animations: {
                                speed: 200
                            },
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        tooltip: {
                            fixed: {
                                enabled: false,
                                position: 'topRight',
                                offsetX: 0,
                                offsetY: 0,
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        xaxis: {
                            categories: [],
                        },
                    },
                },

                devicesStatsByPlatformFamily: {
                    overlay: true,
                    ref: 'devicesStatsByPlatformFamily',
                    last_update: null,
                    series: [],
                    chartOptions: {
                        title: {
                            text: 'Asistentes por sistema de dispositivo',
                            align: 'center',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Cantidad de asistentes por sistema de dispositivo de ingreso',
                            align: 'center',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 20
                        },
                        chart: {
                            id: 'devicesStatsByPlatformFamily',
                            type: 'donut',
                            width: "100%",
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        tooltip: {
                            fixed: {
                                enabled: false,
                                position: 'topRight',
                                offsetX: 0,
                                offsetY: 0,
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        labels: [],
                    },
                },

                devicesStatsByBrowserFamily: {
                    overlay: true,
                    ref: 'devicesStatsByBrowserFamily',
                    last_update: null,
                    series: [],
                    chartOptions: {
                        title: {
                            text: 'Asistentes por navegador',
                            align: 'center',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Cantidad de asistentes por navegador de ingreso',
                            align: 'center',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 20
                        },
                        chart: {
                            id: 'devicesStatsByBrowserFamily',
                            type: 'donut',
                            width: "100%",
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        tooltip: {
                            fixed: {
                                enabled: false,
                                position: 'topRight',
                                offsetX: 0,
                                offsetY: 0,
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        labels: [],
                    },
                },

                statsByDeviceFamily: {
                    overlay: true,
                    ref: 'statsByDeviceFamily',
                    last_update: null,
                    series: [],
                    chartOptions: {
                        title: {
                            text: 'Asistentes por dispositivo',
                            align: 'center',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Cantidad de asistentes por dispositivo de ingreso',
                            align: 'center',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 20
                        },
                        chart: {
                            id: 'statsByDeviceFamily',
                            type: 'donut',
                            width: "100%",
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        tooltip: {
                            fixed: {
                                enabled: false,
                                position: 'topRight',
                                offsetX: 0,
                                offsetY: 0,
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        labels: [],
                    },
                },
                statsByDeviceModel: {
                    overlay: true,
                    ref: 'statsByDeviceModel',
                    last_update: null,
                    series: [],
                    chartOptions: {
                        title: {
                            text: 'Asistentes por modelo de dispositivo',
                            align: 'center',
                            style: {
                                fontWeight: 'bold',
                                fontFamily: 'Tahoma',
                            },
                        },
                        subtitle: {
                            text: 'Cantidad de asistentes por modelo de dispositivo de ingreso',
                            align: 'center',
                            style: {
                                fontFamily: 'Tahoma',
                            },
                            margin: 20
                        },
                        chart: {
                            id: 'statsByDeviceModel',
                            type: 'donut',
                            width: "100%",
                            toolbar: {
                                show: true,
                                tools: {
                                    download: false,
                                    selection: true,
                                    zoom: true,
                                    zoomin: true,
                                    zoomout: true,
                                    pan: false,
                                    reset: '<i class="mdi mdi-reload" style="font-size: 1.4rem; margiin-left: unset" ></i>',
                                },
                            },
                        },
                        tooltip: {
                            fixed: {
                                enabled: false,
                                position: 'topRight',
                                offsetX: 0,
                                offsetY: 0,
                            },
                        },
                        legend: {
                            position: 'bottom'
                        },
                        labels: [],
                    },
                },

            },
        }
    },
    methods: {
        updateAttendanceData() {
            let vue = this
            vue.overlay.box_attendants = true
            vue.disable_btn_update_attendance_data = true

            let url = `${vue.options.base_endpoint}/${vue.resource.id}/update-attendance-data`
            vue.$http.get(url)
                .then(({data}) => {
                    let _data = data.data
                    vue.resource.attendants = _data.attendants
                    vue.overlay.box_attendants = false
                    vue.isMasterOrAdminCursalab = _data.isMasterOrAdminCursalab
                    setTimeout(() => {
                        vue.disable_btn_update_attendance_data = false
                    }, 60000)
                })
                .catch((err) => {
                    vue.overlay.box_attendants = false
                    vue.disable_btn_update_attendance_data = false
                })
        },
        openCancelModal() {

        },
        openDeleteModal(recurso) {

        },
        updateMeetingUrlStart() {
            let vue = this

            let url = `${vue.options.base_endpoint}/${resource.id}/update-url-start`
            vue.$http.get(url)
                .then(({data}) => {
                    let _data = data.data
                    console.log(_data)
                    // vue.resource.url_start = _data.url_start
                })
            return 0;
        },
        openFormDuplicateModal(resource) {
            let vue = this
            vue.$emit('onDuplicate', resource)
        },
        isEndedOrCanceled(meeting) {
            if (!meeting.estado) return false
            return [4, 5].includes(meeting.estado);
        },
        closeModal() {
            let vue = this
            vue.tab = 'main'

            vue.resource.attendants = []
            setTimeout(() => {
            }, 200)

            vue.$emit('onCancel')
        },
        copyToClipboard(text) {
            navigator.clipboard.writeText(text);
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        },
        resetValidation() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this
            vue.disable_btn_update_attendance_data = false
            vue.tab = 'main'
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.options.base_endpoint}/${resource.id}/show`
            await vue.$http.get(url)
                .then(({data}) => {
                    let _data = data.data
                    vue.resource = Object.assign({}, _data.meeting)
                    vue.isMasterOrAdminCursalab = _data.meeting.isMasterOrAdminCursalab
                })
            return 0;
        },
        getMeetingStats() {
            let vue = this
            let url = `${vue.options.base_endpoint}/${vue.resource.id}/stats`
            vue.$http.get(url)
                .then(({data}) => {
                    let _data = data.data

                    vue.isMasterOrAdminCursalab = _data.isMasterOrAdminCursalab

                    let guestsVsAttendees = _data.stats['guestsVsAttendees']
                    let averageTimeInMeeting = _data.stats['averageTimeInMeeting']
                    let attendeesPerCallAssistance = _data.stats['attendeesPerCallAssistance']
                    let topGuestDurationInMeeting = _data.stats['topGuestDurationInMeeting']
                    let devicesStatsByPlatformFamily = _data.stats['devicesStatsByPlatformFamily'] || null
                    let devicesStatsByBrowserFamily = _data.stats['devicesStatsByBrowserFamily'] || null
                    let statsByDeviceFamily = _data.stats['statsByDeviceFamily'] || null
                    let statsByDeviceModel = _data.stats['statsByDeviceModel'] || null
                    // vue.setDataChart('stats1', guestsVsAttendees)
                    vue.setDataChart('averageTimeInMeeting', averageTimeInMeeting)
                    vue.setDataChart('guestsVsAttendees', guestsVsAttendees)
                    vue.setDataChart('attendeesPerCallAssistance', attendeesPerCallAssistance)
                    vue.setDataChart('topGuestDurationInMeeting', topGuestDurationInMeeting)
                    if (devicesStatsByPlatformFamily)
                        vue.setDataChart('devicesStatsByPlatformFamily', devicesStatsByPlatformFamily)
                    if (devicesStatsByBrowserFamily)
                        vue.setDataChart('devicesStatsByBrowserFamily', devicesStatsByBrowserFamily)
                    if (statsByDeviceFamily)
                        vue.setDataChart('statsByDeviceFamily', statsByDeviceFamily)
                    if (statsByDeviceModel)
                        vue.setDataChart('statsByDeviceModel', statsByDeviceModel)
                })
        },
        cleanGraph(chart_name) {
            let vue = this
            // console.log("cleangraph")
            let graphic = vue.graphics[chart_name]
            if (graphic) {
                graphic.series = []
                graphic.chartOptions = {xaxis: {categories: []}}
                graphic.chartOptions = {labels: []}
            }

        },
        setDataChart(chart_name, data) {
            let vue = this
            let graphic = vue.graphics[chart_name]
            if (graphic) {
                const {title, subtitle, chart, legend, ...rest} = graphic.chartOptions
                // graphic.last_update = response_data.last_update
                switch (graphic.chartOptions.chart.type) {
                    case 'line':
                    case 'bar':
                        graphic.series = [{
                            name: data.name || 'Cantidad',
                            data: data.values
                        }]
                        graphic.chartOptions = {
                            xaxis: {
                                categories: data.labels
                            }
                        }
                        break;
                    case 'donut':
                    case 'pie':
                        graphic.series = data.values
                        graphic.chartOptions = {labels: data.labels}

                        break;
                }

                graphic.chartOptions.title = title
                graphic.chartOptions.subtitle = subtitle
                graphic.chartOptions.chart = chart
                graphic.chartOptions.legend = legend

                graphic.overlay = false
            }
        },
        loadSelects() {

        },
        ingresar() {
            let vue = this
            window.open(vue.resource.link);
        },
        downloadReport() {
            let vue = this
            let url = `${vue.options.base_endpoint}/${vue.resource.id}/export-report`
            window.open(url, '_blank');
        },
        getColorAndIconByCall(attendant, type) {
            let vue = this
            const next_act = moment().add((5 - (moment().minute() % 5)), 'minutes').valueOf()
            // console.log("NEXT ACT", next_act)
            const minusObj = {icon: 'mdi-minus-circle', color: '#b5b5b5'}
            const checkObj = {icon: 'mdi-check-circle', color: 'green'}
            const closeObj = {icon: 'mdi-close-circle', color: 'red'}

            if (vue.resource.status.code === "scheduled" || vue.resource.status.code === 'overdue')
                return minusObj

            const now = new Date()
            const date_attendance_call_first_at = new Date(vue.resource.attendance_call_first_at)
            const date_attendance_call_middle_at = new Date(vue.resource.attendance_call_middle_at)
            const date_attendance_call_last_at = new Date(vue.resource.attendance_call_last_at)

            let condition, prop, attendance_time;

            switch (type) {
                case 'first':
                    attendance_time = date_attendance_call_first_at
                    condition = now.getTime() > date_attendance_call_first_at.getTime()
                    prop = 'present_at_first_call'
                    break
                case 'middle':
                    attendance_time = date_attendance_call_middle_at
                    condition = now.getTime() > date_attendance_call_middle_at.getTime()
                    prop = 'present_at_middle_call';
                    break
                case 'last':
                    attendance_time = date_attendance_call_last_at
                    condition = now.getTime() > date_attendance_call_last_at.getTime()
                    prop = 'present_at_last_call';
                    break
            }

            if (vue.resource.status.code === "finished")
                return attendant[prop] ? checkObj : closeObj

            // return condition ?
            //     (attendant[prop] ?
            //         checkObj :
            //         (attendance_time < next_act) ? closeObj : attendant[prop] ? checkObj : closeObj)
            //     : minusObj
            //
            return condition ? (attendant[prop] ? checkObj : closeObj) : minusObj
        }
    }
}
</script>

<style lang="scss">
//.apexcharts-tooltip {
//    left: unset !important;
//}
</style>
