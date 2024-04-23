<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Procesos masivos
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row>
                    <v-col cols="12">
                        <v-alert
                            dense
                            type="info"
                            v-show="s_alert"
                            v-html="msg_alert"
                        >
                        </v-alert>
                    </v-col>
                </v-row>
                <v-row class="justify-content-start">
                    <v-col cols="12" md="5" sm="5" class="bx_sel">
                        <DefaultSelect
                            label="Selecciona el proceso a realizar"
                            class="ml-4 bx_select"
                            @onChange="change_select"
                            item-text="nombre"
                            item-value="id"
                            :items="list_massive_processes"
                            v-model="process_id"
                        />
                    </v-col>
                </v-row>
                <mUsuarios :number_socket="number_socket"
                           :key="1"
                           :url_template="url_template"
                           :process="list_massive_processes.find(obj => obj.id === process_id)"
                           v-show="process_id==1 || process_id==6"
                           @emitir-alert="show_alert_msg"
                           @download-excel-observations="downloadExcelObservations"
                           @show-modal-limit-allowed-users="openModalLimitAllowedUsers($event)"/>

                <ActivarUsuarios :number_socket="number_socket" :key="2" :url_template="url_template"
                                 :process="list_massive_processes[2]"
                                 v-show="process_id==2"
                                 @emitir-alert="show_alert_msg"
                                 @show-modal-limit-allowed-users="openModalLimitAllowedUsers($event)"/>

                <InactivarUsuarios :number_socket="number_socket" :key="3" :url_template="url_template"
                                   :process="list_massive_processes[3]"
                                   v-show="process_id==3"
                                   @emitir-alert="show_alert_msg"/>

                <DefaultDialog
                    :options="modalOptions"
                    :width="400"
                    @onConfirm="closeSimpleModal(modalOptions)"
                >
                    <template v-slot:content>
                        El excel cargado cuenta con <strong>{{ modalOptions.users_to_activate }}</strong> usuarios
                        activos que sobrepasan la cantidad permitida.<br><br>
                        El plan de tu workspace es de <strong>{{ modalOptions.workspace_limit }}</strong> usuarios
                        activos.
                        <br><br>
                        Para realizar la carga puedes:
                        <ul>
                            <li>Desactivar usuarios en la plataforma.</li>
                            <li>Reducir la cantidad de usuarios activos en tu excel.</li>
                            <li><strong>Contáctarnos y ampliar tu plan de usuarios.</strong></li>
                        </ul>
                    </template>
                </DefaultDialog>
            </v-card-text>
        </v-card>
    </section>
</template>
<script>
import mUsuarios from "../../components/SubidaMasiva/usuarios/SubidaUsuarios.vue";
import ActivarUsuarios from "../../components/SubidaMasiva/activar/ActivarUsuarios.vue";
import InactivarUsuarios from "../../components/SubidaMasiva/desactivar/DesactivarUsuarios.vue"

const percentLoader = document.getElementById('percentLoader');
export default {
    components: {mUsuarios, ActivarUsuarios, InactivarUsuarios},
    props: {
        user_id: {
            required: true
        }
    },
    data() {
        return {
            number_socket: Math.floor(Math.random(6) * 1000000),
            info_error: 0,
            overlay: true,
            s_alert: false,
            url_template: '/procesos-masivos/download-template-user',
            msg_alert: '',
            loader_text: "Cargando",
            process_id: 1,
            list_massive_processes: [
                {id: 1, nombre: 'Creación/Actualización de usuarios', url_template: '/procesos-masivos/download-template-user'},
                {id: 6, nombre: 'Actualización de usuarios', url_template: '/procesos-masivos/download-template-user'},
                {id: 2, nombre: 'Activar Usuarios', url_template: '/templates/Plantilla_activar_usuarios.xlsx'},
                {id: 3, nombre: 'Desactivar(Cesar) usuarios', url_template: '/templates/Plantilla_cesar_usuarios.xlsx'},
                //   {id:4,nombre:'Subida de cursos',url_template:''},
            ],
            modalOptions: {
                ref: 'modalLimitAllowedUsers',
                title: 'Limite de Usuarios excedidos',
                contentText: ``,
                open: false,
                confirmLabel: "Entendido",
                hideCancelBtn: true,
                workspace_limit: null,
                users_to_activate: null
            }
        };
    },
    mounted() {
        window.Echo.channel(`upload-massive.${this.user_id}.${this.number_socket}`).listen('MassiveUploadProgressEvent', result => {
            if (percentLoader) {
                if (result.percent) {
                    percentLoader.innerHTML = `${result.percent}%`;
                }
            }
        })
    },
    methods: {
        openModalLimitAllowedUsers(data) {
            let vue = this;
            const {workspace_limit, users_to_activate} = data;

            vue.modalOptions.workspace_limit = workspace_limit;
            vue.modalOptions.users_to_activate = users_to_activate;

            vue.openSimpleModal(vue.modalOptions)
        },
        show_alert_msg(message) {
            this.s_alert = true;
            this.msg_alert = message;
        },
        change_select() {
            let vue = this;
            this.s_alert = false;
            this.msg_alert = '';
            vue.url_template = vue.list_massive_processes.find(mp => mp.id == vue.process_id).url_template;
        },
        downloadExcelObservations({errores, headers}) {
            let vue = this;
            const values = errores.map(error => error.row.map(row => row || ''));
            let comments = [];
            errores.forEach((error, index) => {
                error.errors_index.forEach((error_index) => {
                    comments.push({
                        cell_name: `${this.abc[error_index.index]}${index + 2}`,
                        message: error_index.message
                    });
                });
            });
            vue.descargarExcelwithValuesInArray({
                headers,
                values,
                comments,
                filename: "No procesados_" + Math.floor(Math.random() * 1000),
            });
        }
    }
}
</script>
<style lang="scss">
.bx_sel{
    position: relative;
    padding: 0;
}
.bx_select{
    position: absolute;
    top: 20px;
}
</style>
