<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   :show-card-actions="false"
    >
        <template v-slot:content>

            <div class="px-5">

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong>Estado de ticket</strong>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row class="row-support">
                    <v-col class="">
                        <v-icon left :color="resource.status.color">fa-solid fa-bullseye</v-icon>
                        <a href="javascript:;" @click="openFormModal(modalSoporteOptions, resource, 'ticket', `Editar ticket #${resource.id}`)">
                            <span v-text="resource.status.text" :style="{ 'color': resource.status.color, 'font-weight': 'bold' } "></span>
                        </a>
                    </v-col>
                    <v-col class="">
                        <v-icon left color="primary">fa-solid fa-calendar</v-icon>
                        Creado el <span v-text="resource.created_at"></span>
                        <!-- <span v-text="resource.updated_at"></span> -->
                    </v-col>
                </v-row>

                 <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong>Datos de usuario</strong>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row class="row-support">

                    <v-col cols="6">
                        <v-icon left color="primary">fa-solid fa-th-large</v-icon>
                        <span v-text="resource.module"></span>
                        <!-- <v-img
                            max-height="60"
                            max-width="60"
                            :src="resource.image"
                        >
                            <template v-slot:placeholder>
                                <v-row
                                    class="fill-height ma-0"
                                    align="center"
                                    justify="center"
                                >
                                    <v-progress-circular
                                        indeterminate
                                        color="grey lighten-5"
                                    ></v-progress-circular>
                                </v-row>
                            </template>
                        </v-img> -->

                    </v-col>

                    <v-col cols="6" v-if="resource.user">
                        <v-icon left color="primary">fa-solid fa-id-card</v-icon>

                        <a :href="'/usuarios?document=' + resource.user.document" target="_blank">
                            <span v-text="resource.user.document"></span>
                        </a>
                    </v-col>

                    <v-col cols="6" v-else>
                        <v-icon left color="primary">fa-solid fa-id-card</v-icon>

                        <a :href="'/usuarios?document=' + resource.dni" target="_blank">
                            <span v-text="resource.dni"></span>
                        </a>
                    </v-col>

                    <v-col cols="6" v-if="resource.user">
                        <v-icon left color="primary">fa-solid fa-user</v-icon>
                        <span v-text="resource.user.fullname"></span>
                    </v-col>

                    <v-col cols="6" v-else>
                        <v-icon left color="primary">fa-solid fa-user</v-icon>
                        <span v-text="resource.nombre"></span>
                    </v-col>

                    <v-col cols="6" v-if="resource.user">
                        <v-icon left color="primary">fa-solid fa-circle</v-icon>
                        <span v-text="resource.user.active ? 'Activo' : 'Inactivo'"></span>
                    </v-col>

                    <v-col cols="6" class="" v-if="resource.email_user">
                        <v-icon left color="primary">fa-solid fa-envelope</v-icon>
                        <span v-text="resource.email_user"></span> <strong> (registrado)</strong>
                    </v-col>

                    <v-col cols="6" class="" v-if="resource.email_ticket">
                        <v-icon left color="primary">fa-regular fa-envelope</v-icon>
                        <span v-text="resource.email_ticket"></span>
                        <strong> (proporcionado) </strong><br>
                        <div v-if="resource.email_user.trim() === resource.email_ticket.trim()"
                             class="mt-3">
                            <v-icon left color="green">
                                fa-solid fa-check-circle
                            </v-icon>
                            Los correos coinciden
                        </div>
                        <div v-if="resource.email_user.trim() && (resource.email_user.trim() !== resource.email_ticket.trim())"
                            class="mt-3">
                            <v-icon left color="yellow">
                                fas fa-exclamation-triangle
                            </v-icon>
                            Los correos no coinciden
                        </div>

                    </v-col>

                     <v-col cols="6" class="">
                        <v-icon left color="primary" medium class="font-bold">fas fa-mobile</v-icon>
                        <!-- <v-icon left color="primary" medium class="font-bold">fab fa-whatsapp-square</v-icon> -->
                        <a target="_blank" :href="`https://wa.me/51${resource.contact}?text=¡Hola!,%20Vimos%20tu%20solicitud%20enviada%20desde%20la%20plataforma%20de%20capacitación`">{{resource.contact}}</a>
                    </v-col>
                    <v-col cols="6" class="" v-if="resource.last_login">
                        <v-icon left color="primary">fas fa-sign-in-alt</v-icon>
                        <span v-text="resource.last_login"></span> <strong> (última conexión)</strong>
                    </v-col>
                </v-row>

               <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong>Detalle de ticket</strong>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row class="row-support">

                    <v-col cols="12" v-if="resource.reason">
                        <v-icon left color="primary">fa-solid fa-flag</v-icon>
                        <span v-text="resource.reason"></span>
                    </v-col>

                    <v-col cols="12" v-if="resource.detail">
                        <span v-text="resource.detail"></span>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center" v-if="resource.info_support || resource.msg_to_user">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong>Información de soporte</strong>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row class="row-support" v-if="resource.info_support || resource.msg_to_user">

                    <v-col cols="12" v-if="resource.info_support">
                        <v-icon left color="primary">fa-solid fa-comment</v-icon>
                        <span v-text="resource.info_support"></span>
                    </v-col>

                    <v-col cols="12" v-if="resource.msg_to_user" >
                        <v-icon left color="primary">fas fa-comment</v-icon>
                        <span v-text="resource.msg_to_user"></span>
                    </v-col>
                </v-row>

            </div>

        </template>

        <template v-slot:card-actions>
            <v-card-actions
                :style="{ 'border-top': '1px solid rgba(0,0,0,.12)' }"
            >

               <v-row justify="center" class="mx-0 support-card-actions">
                    <v-col cols="12" class="d-flex justify-content-around" v-if="resource.user">
                        <v-btn
                            class="default-modal-action-button"
                            text
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="openFormModal(modalResetPasswordOptions, resource.user, 'user', `Restaurar contraseña de ${resource.user.name} - ${resource.user.document}`)"
                        >
                            <!-- :disabled="loading" -->
                            <v-icon left color="primary" small>fa-solid fa-key</v-icon>
                            Restaurar contraseña
                        </v-btn>

                        <v-btn
                            class="default-modal-action-button"
                            text
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="openFormModal(modalStatusOptions, resource.user, 'status', `Actualizar estado de usuario ${resource.user.name} - ${resource.user.document}`)"
                        >
                            <!-- :disabled="loading" -->
                            <v-icon left color="primary" small>fa-solid fa-circle</v-icon>
                            Actualizar estado
                        </v-btn>

                        <v-btn
                            class="default-modal-action-button"
                            text
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="openFormModal(modalOptions, resource.user, 'edit', `Editar usuario ${resource.user.name} - ${resource.user.document}`)"
                        >
                            <!-- :disabled="loading" -->
                            <v-icon left color="primary" small>fa-solid fa-pen</v-icon>
                            Editar usuario
                        </v-btn>

                        <v-btn
                            class="default-modal-action-button"
                            text
                            elevation="0"
                            :ripple="false"
                            color="primary"
                            @click="openFormModal(modalReiniciosOptions, resource.user, 'cursos', `Reiniciar avance de ${resource.user.name} - ${resource.user.document}`)"
                        >
                            <v-icon left color="primary" small>fa-solid fa-history</v-icon>
                            <!-- :disabled="loading" -->
                            Reiniciar avance
                        </v-btn>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-around" v-else>
                        <a :href="'/usuarios?document=' + resource.dni" target="_blank">
                            <v-icon left color="primary" small>fa-solid fa-search</v-icon>
                            Ver usuario
                        </a>
                    </v-col>
                </v-row>
            </v-card-actions>
            <UsuarioResetPasswordModal
                width="35vw"
                :ref="modalResetPasswordOptions.ref"
                :options="modalResetPasswordOptions"
                @onConfirm="closeFormModal(modalResetPasswordOptions)"
                @onCancel="closeFormModal(modalResetPasswordOptions)"
            />
            <UsuarioFormModal
                width="45vw"
                :ref="modalOptions.ref"
                :options="modalOptions"
                @onConfirm="closeFormModal(modalOptions); loadData(resource)"
                @onCancel="closeFormModal(modalOptions)"
            />
            <DefaultStatusModal
                width="40vw"
                :options="modalStatusOptions"
                :ref="modalStatusOptions.ref"
                @onConfirm="closeFormModal(modalStatusOptions); loadData(resource)"
                @onCancel="closeFormModal(modalStatusOptions)"
            />
            <UsuarioReiniciosModal
                width="40vw"
                :ref="modalReiniciosOptions.ref"
                :options="modalReiniciosOptions"
                @onReinicioTotal="closeFormModal(modalReiniciosOptions)"
                @onCancel="closeFormModal(modalReiniciosOptions)"
            />
            <UsuarioCursosModal
                width="35vw"
                :ref="modalCursosOptions.ref"
                :options="modalCursosOptions"
                @onCancel="closeFormModal(modalCursosOptions)"
            />

            <SoporteFormModal
                width="35vw"
                :ref="modalSoporteOptions.ref"
                :options="modalSoporteOptions"
                @onConfirm="closeFormModal(modalSoporteOptions); loadData(resource);  reloadGrid()"
                @onCancel="closeFormModal(modalSoporteOptions)"
            />
        </template>


    </DefaultDialog>

</template>

<script>

import UsuarioFormModal from "../Usuario/UsuarioFormModal";
// import UsuarioStatusModal from "../Usuario/UsuarioStatusModal";
import UsuarioCursosModal from "../Usuario/UsuarioCursosModal";
import UsuarioReiniciosModal from "../Usuario/UsuarioReiniciosModal";
import UsuarioResetPasswordModal from "../Usuario/UsuarioResetPasswordModal";
import DefaultStatusModal from "../Default/DefaultStatusModal";
import SoporteFormModal from "./SoporteFormModal";

export default {
    components: {UsuarioFormModal, UsuarioCursosModal, UsuarioReiniciosModal, DefaultStatusModal, UsuarioResetPasswordModal, SoporteFormModal},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            resourceDefault: {
                id: null,
                user: {
                    id: null,
                    document: null
                },
                status: {
                    color: null,
                    text: null,
                },
                msg_to_user: '',
                info_support: '',
            },
            resource: {
                user: {
                    id: null,
                    document: null
                },
                status: {
                    color: null,
                    text: null,
                },
            },
            modalResetPasswordOptions: {
                ref: 'UsuarioResetPasswordModal',
                open: false,
                base_endpoint: '/usuarios',
                // cancelLabel: 'Cerrar',
                // hideConfirmBtn: true,
            },
            modalOptions: {
                ref: 'UsuarioFormModal',
                open: false,
                base_endpoint: '/usuarios',
                resource: 'Usuario',
                confirmLabel: 'Guardar',
            },

            modalSoporteOptions: {
                ref: 'SoporteFormModal',
                open: false,
                base_endpoint: '/soporte',
                resource: 'Soporte',
                confirmLabel: 'Guardar',
            },

            modalCursosOptions: {
                ref: 'UsuarioCursosModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
            },
            modalReiniciosOptions: {
                ref: 'UsuarioReiniciosModal',
                open: false,
                base_endpoint: '/usuarios',
                cancelLabel: 'Cerrar',
                hideConfirmBtn: true,
            },
            modalStatusOptions: {
                ref: 'UsuarioStatusModal',
                open: false,
                base_endpoint: '/usuarios',
                contentText: '¿Desea cambiar de estado a este usuario?',
                endpoint: '',
                width: '35vw'
            },
        }
    },
    methods: {
        reloadGrid() {
            let vue = this
            // vue.options.open = false
            vue.$emit('onConfirm')
        },
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        confirmModal() {
            let vue = this

        },
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {

            let vue = this

            vue.$nextTick(() => {
                vue.resource = Object.assign(
                    {}, vue.resource, vue.resourceDefault
                )
            })

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/${resource.id}/show`;

            await vue.$http.get(url)
                .then(({data}) => {

                    if (resource) {
                        vue.resource = Object.assign({}, data.data.ticket);
                        vue.resource.user = Object.assign({}, data.data.ticket.user);
                    }

                })
            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
