<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="UsuarioForm">

                <DefaultErrors :errors="errors"/>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <div class="header_inf">
                            <strong class="cg">Información personal</strong>
                            <span>*Datos obligatorios</span>
                        </div>
                    </v-col>
                    <v-col cols="12" class="py-0 separated">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            dense
                            v-model="resource.name"
                            label="Nombres*"
                            :rules="rules.name"
                            placeholder="Ingrese un nombre"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            dense
                            v-model="resource.lastname"
                            label="Apellido Paterno*"
                            :rules="rules.lastname"
                            placeholder="Ingrese un apellido"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            dense
                            v-model="resource.surname"
                            label="Apellido Materno"
                            placeholder="Ingrese un apellido"
                            :rules="rules.surname"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center pb-0">
                        <!-- :rules="rules.email" -->
                        <DefaultInput
                            clearable
                            dense
                            v-model="resource.email_gestor"
                            label="Correo electrónico*"
                            autocomplete="new-email"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center pb-0">
                        <DefaultInput
                            clearable
                            dense
                            v-model="resource.document"
                            label="Identificador"
                            autocomplete="new-document"
                            :rules="rules.document"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center pb-0">
                        <DefaultInput
                            clearable
                            dense
                            v-model="resource.password"
                            label="Contraseña"
                            autocomplete="new-password"
                            type="password"
                            ref="passwordRefModal"
                            :rules="options.action === 'edit' ? rules.password_not_required : rules.password"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">

                    <v-col cols="12" class="d-flex justify-content-end py-0">
                        <a href="javascript:;" @click="openFormModal(modalPasswordOptions, null, 'status', 'Generador de contraseñas')"><small>¿Generar contraseña?</small></a>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <div class="header_inf">
                            <strong class="cg">Configuración por workspace</strong>
                            <!-- <span>Configuración por workspace</span> -->
                        </div>
                    </v-col>
                    <v-col cols="12" class="py-0 separated">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <section class="py-3 section-roles-tabs my-3">

                    <!-- v-scroll:#scroll-target="onScroll" -->
                    <v-row
                        align="center"
                        justify="center"
                        class="mx-1"
                    >
                        <!-- style="height: 380px" -->

                        <v-tabs vertical hide-slider centered center-active class="roles-tabs">

                            <v-container
                                id="scroll-target"
                                class="overflow-y-auto py-0 px-1"
                                style="min-height: 380px; max-height: 400px"
                            >
                                <v-tab v-for="workspace in workspaces" :key="'wrkspc-tab-' + workspace.id">

                                    <v-icon left>
                                        fas fa-th-large
                                    </v-icon>

                                    {{ workspace.name }}
                                </v-tab>

                            </v-container>

                          <v-tab-item v-for="(workspace, i) in resource.selected_workspaces" :key="'wrkspc-role-' + workspace.id">
                            <v-card flat>
                              <v-card-text>

                                <v-row justify="space-around" align="start" align-content="center">
                                
                                    <v-col cols="11" class="d-flex justify-content-center pt-0">
                                        <img
                                            v-if="workspace.logo_is_loaded"
                                            :src="workspace.full_logo"
                                            :alt="workspace.name"
                                            @error="workspace.logo_is_loaded = false"
                                            height="40"
                                        />

                                        <div v-if="!workspace.logo_is_loaded" >
                                            <h5>{{ workspace.name }}</h5>
                                        </div>
                                    </v-col>

                                    <v-col cols="11" class="">

                                        <p>
                                            Seleccione los módulos a los que tendrá acceso el administrador.    
                                        </p>

                                        <DefaultAutocomplete
                                            :rules="rules.subworkspaces"
                                            dense
                                            label="Módulos"
                                            v-model="workspace.selected_subworkspaces"
                                            :items="workspace.subworkspaces"
                                            item-text="name"
                                            item-value="id"
                                            multiple
                                            :show-select-all="false"
                                            :count-show-values="3"
                                        />
                                    </v-col>

                                    <v-col cols="11" class="">

                                        <p>
                                            Seleccione los roles que tendrá el administrador en este workspace.    
                                        </p>

                                        <DefaultAutocomplete
                                            :rules="rules.roles"
                                            dense
                                            label="Roles"
                                            v-model="workspace.selected_roles"
                                            :items="roles"
                                            item-text="title"
                                            item-value="id"
                                            multiple
                                            :show-select-all="false"
                                            :count-show-values="3"
                                        />
                                    </v-col>

                                    <v-col cols="11" class="">

                                        <p>
                                            Seleccione los tipos de correo que se notificarán al administrador.
                                        </p>

                                        <DefaultAutocomplete
                                            :rules="rules.emails"
                                            dense
                                            label="Correos"
                                            v-model="workspace.selected_emails"
                                            :items="emails"
                                            item-text="name"
                                            item-value="id"
                                            multiple
                                            :show-select-all="false"
                                            :count-show-values="3"
                                        />
                                    </v-col>

                                </v-row>


                              </v-card-text>
                            </v-card>
                          </v-tab-item>
                       
                        </v-tabs>
                      </v-row>

                </section>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong class="cg">Configuración general</strong>
                    </v-col>
                    <v-col cols="12" class="py-0 separated">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="center">
                    <v-col cols="6" class="d-flex justify-content-center align-items-center pt-0">
                        <DefaultToggle v-model="resource.enable_2fa"  active-label="Two Factor Autentication habilitado" inactive-label="Two Factor Autentication deshabilitado" dense />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center align-items-center pt-0">
                        <DefaultToggle v-model="resource.active"  active-label="Usuario activo" inactive-label="Usuario inactivo" dense />
                    </v-col>
                </v-row>

            </v-form>

            <PasswordGeneratorModal
                width="40vw"
                :ref="modalPasswordOptions.ref"
                :options="modalPasswordOptions"
                @onConfirm="closeFormModal(modalPasswordOptions)"
                @onCancel="closeFormModal(modalPasswordOptions)"
            />

        </template>
    </DefaultDialog>
</template>

<script>
import PasswordGeneratorModal from "./PasswordGeneratorModal";

export default {
    components: { PasswordGeneratorModal },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            errors: [],
            workspaces: [],
            roles: [],
            emails: [],
            // sections: {
            //     showCriteria: false
            // },
            resourceDefault: {
                id: null,
                name: '',
                lastname: '',
                surname: '',
                email_gestor: '',
                document: '',
                username: '',
                password: '',
                active: true,
                enable_2fa: true,
                workspaces: [],
                roles: [],
                emails: [],
            },

            resource: {},
            selects: {},
            rules: {
                name: this.getRules(['required', 'max:100', 'text']),
                lastname: this.getRules(['required', 'max:100', 'text']),
                email_gestor: this.getRules(['required', 'max:100', 'text']),
                // document: this.getRules(['required', 'min:8']),
                password: this.getRules(['required', 'min:8']),
                // email: this.getRules(['required', 'min:8']),
                password_not_required: this.getRules([]),
            },
            show_lbl_error_cri: false,
            modalPasswordOptions: {
                ref: 'PasswordFormModal',
                open: false,
                base_endpoint: '/password',
                resource: 'Password',
                confirmLabel: 'Cerrar',
                showCloseIcon: true,
            },
            // confirmModalInfo: true,
            modalUsuarioFormInfoOptions: {
                ref: 'UsuarioFormInfoModal',
                open: false,
                confirmLabel: 'Confirmar',
                subTitle:'¡Estás por actualizar los criterios de un usuario!',
                showCloseIcon: true,
                resource: {
                    changes_criterios: [],
                    changes_data: []
                }
            },
            updateStatusModal: {
                ref: 'UsuarioUpdateStatusModal',
                title: 'Actualizar usuario',
                open: false,
                endpoint: '',
                title_modal: 'Cambio de estado de <b>usuario</b>',
                type_modal: 'status',
                status_item_modal: null,
                contentText: '¿Desea cambiar de estado a este registro?',
                content_modal: {
                    inactive: {
                        title: '¡Estás por desactivar un usuario!',
                        details: [
                            'El usuario no podrá ingresar a la plataforma.',
                            'Podrá enviar solicitudes desde la sección de ayuda del Log in.',
                            'Aparecerá en los reportes y consultas con el estado inactivo.'
                        ],
                    },
                    active: {
                        title: '¡Estás por activar un usuario!',
                        details: [
                            'El usuario ahora podrá ingresar a la plataforma.',
                            'Podrá rendir los cursos, de estar segmentado.'
                        ]
                    }
                },
            },
       }
    },
    mounted() {
        let vue = this
        // vue.sections.showCriteria = vue.options.action === 'edit'
    },
    methods: {
        closeModal() {
            let vue = this;
            // vue.options.open = false
            vue.resetSelects();
            vue.resetValidation();
            vue.$emit('onCancel');
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('UsuarioForm');
            vue.errors = [];
            vue.$refs.passwordRefModal.resetTypePassword();
        },
       
        async confirmModal() {
            let vue = this;
            vue.showLoader();

            const validateForm = vue.validateForm('UsuarioForm')

            const edit = vue.options.action === 'edit'
            const base = `${vue.options.base_endpoint}`
            const method = edit ? 'put' : 'post';

            if (validateForm) {
                let url = edit ? `${base}/${vue.resource.id}/update` : `${base}/store`;
                let data = vue.resource

                vue.$http[method](url, data)
                    .then(({data}) => {
                        vue.errors = []
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        vue.hideLoader()
                        // vue.queryStatus("usuarios", "crear_usuario");
                    })
                    .catch((error) => {
                        // vue.resource.criterion_list_final = {}
                        vue.hideLoader()

                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            } else {
                vue.hideLoader()
            }
        },

        resetSelects() {
            let vue = this
            // CLOSE CRITERIA SECTION
            // vue.sections.showCriteria = false
            if (vue.resource)
                vue.resource.password = null;
        },

        async loadData(resource) {
            let vue = this

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/edit` : `create`}`
            await vue.$http.get(url)
                .then(({data}) => {

                    vue.workspaces = data.data.workspaces;
                    vue.roles = data.data.roles;
                    vue.emails = data.data.emails;

                    if (resource) {
                        vue.resource = data.data.user;
                    }

                    // return 0;
                })
        },
        loadSelects() {
            let vue = this

        },
    }
}
</script>
<style lang="scss">
.header_inf {
    display: flex;
    justify-content: space-between;
    width: 100%;
}
.header_inf span {
    color: #434D56;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 400;
}
// span.lbl_mas_cri {
//     font-family: "Nunito", sans-serif;
//     font-size: 13px;
//     font-weight: 400;
//     color: #434D56;
// }
// .rem-m .v-input.default-toggle {
//     margin-top: 0 !important;
//     width: 100%;
// }
// .lbl_error_cri{
//     color: #FF5252;
//     font-family: "Nunito", sans-serif;
//     font-weight: 700;
//     font-size: 13px;
// }
// .v-application .error--text .v-text-field__details {
//     display: none;
// }
.separated hr.v-divider {
    // border-color: #94DDDB !important;
    margin-top: 1px !important;
}
.cg {
    color: #434D56;
    font-family: "Nunito", sans-serif;
    font-size: 14px;
    font-weight: 700;
}
// .v-dialog .v-text-field .v-input__control,
// .v-dialog .v-text-field .v-input__slot,
// .v-dialog .v-text-field fieldset{
//     border-radius: 10px;
//     border-color: #D9D9D9;
// }
// .v-dialog .v-input .v-label {
//     font-family: "Nunito", sans-serif;
//     font-size: 14px;
// }
// .v-dialog .theme--light.v-input,
// .v-dialog .theme--light.v-input input,
// .v-dialog .theme--light.v-input textarea {
//     font-family: "Nunito", sans-serif;
//     font-size: 14px;
// }
// // .v-dialog.v-dialog--active.v-dialog--scrollable .v-card__actions {
// //     border-color: #94DDDB !important;
// // }
// .v-dialog button.v-icon.notranslate.v-icon--link.mdi.mdi-close.theme--light {
//     font-size: 18px;
// }
// .v-dialog .v-card__title.default-dialog-title ::-webkit-scrollbar-thumb {
//     background-color: #7DDBD8E5 !important;
// }
// .v-dialog .v-text-field--filled>.v-input__control>.v-input__slot,
// .v-dialog .v-text-field--full-width>.v-input__control>.v-input__slot,
// .v-dialog .v-text-field--outlined>.v-input__control>.v-input__slot {
//     min-height: 50px;
// }
// .v-dialog .v-text-field--outlined .v-label {
//     top: 16px;
// }
// .v-dialog .v-list-item .v-list-item__subtitle,
// .v-dialog .v-list-item .v-list-item__title {
//     font-family: "Nunito", sans-serif;
//     font-size: 13px;
// }
// .v-dialog .v-text-field--outlined .v-label--active {
//     color: #434D56 !important;
// }
// .v-dialog .v-text-field.error--text fieldset {
//     border-color: #ff5252;
// }
// .v-text-field--enclosed .v-input__append-inner button.v-icon.mdi.mdi-close{
//     top: -3px;
// }
</style>
