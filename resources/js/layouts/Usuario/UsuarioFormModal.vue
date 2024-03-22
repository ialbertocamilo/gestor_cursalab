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
                            <strong class="cg">Información del usuario</strong>
                            <span>*Criterios obligatorios</span>
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
                            v-model="resource.name"
                            label="Nombres*"
                            :rules="rules.name"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.lastname"
                            label="Apellido Paterno*"
                            :rules="rules.lastname"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.surname"
                            label="Apellido Materno*"
                            :rules="rules.surname"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.username"
                            label="Nombre de usuario"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.phone_number"
                            label="Número de teléfono"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.person_number"
                            label="Número de colaborador"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.email"
                            label="Correo electrónico"
                            autocomplete="new-email"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.document"
                            label="Identificador*"
                            autocomplete="new-document"
                            :rules="rules.document"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.password"
                            :label="options.action === 'edit' ? 'Contraseña' : 'Contraseña*'"
                            autocomplete="new-password"
                            type="password"
                            ref="passwordRefModal"
                            :rules="options.action === 'edit' ? rules.password_not_required : rules.password"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">

                    <v-col cols="12" class="d-flex justify-content-end py-0">
                        <a href="javascript:;" @click="openFormModal(modalPasswordOptions, null, 'status', 'Generador de contraseñas')">¿Generar contraseña?</a>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong class="cg">Criterios obligatorios para la creación de un usuario</strong>
                    </v-col>
                    <v-col cols="12" class="py-0 separated">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-center pt-0">
                            <UsuarioCriteriaSection
                                ref="CriteriaSection"
                                :options="options"
                                :user="resource"
                                :criterion_list="criterion_list_req"
                            />
                    </v-col>
                </v-row>
                <!-- DATOS PARA DC3 y DC4 -->
                <div v-if="has_DC3_functionality">
                    <v-row justify="space-around" align="start" align-content="center">
                        <v-col cols="12" class="d-flex justify-content-between pb-0" style="cursor: pointer">
                            <strong class="cg">Datos para STPS</strong>
                        </v-col>
                        <v-col cols="12" class="py-0 separated">
                            <DefaultDivider/>
                        </v-col>
                    </v-row>
                    <v-row justify="space-around" align="start" align-content="center">
                        <v-col cols="4">
                            <DefaultInput
                                clearable
                                v-model="resource.curp"
                                label='CURP'
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultAutocomplete
                                placeholder=""
                                label="Ocupación"
                                :items="national_occupations_catalog"
                                v-model="resource.national_occupation_id"
                                item-text="name"
                                clearable
                            />
                        </v-col>
                        <v-col cols="4">
                            <DefaultAutocomplete
                                placeholder=""
                                :label="position_dc3.code"
                                :items="position_dc3.values"
                                v-model="resource.criterion_list[position_dc3.code]"
                                item-text="value_text"
                                clearable
                            />
                        </v-col>
                    </v-row>
                </div>

                <v-row justify="space-around" align="start" align-content="center" v-if="criterion_list_opt.length > 0">
                    <v-col cols="12" class="d-flex justify-content-between pb-0"
                        @click="sections.showCriteria = !sections.showCriteria"
                        style="cursor: pointer">
                        <strong class="cg">Criterios generales para la creación de un usuario</strong>
                        <!-- <strong class="cg">Más Criterios</strong> -->
                        <v-icon v-text="sections.showCriteria ? 'mdi-chevron-up' : 'mdi-chevron-down'"/>
                    </v-col>
                    <v-col cols="12" class="py-0 separated">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center" v-if="criterion_list_opt.length > 0">
                    <!-- <v-col cols="12" class="pb-0 pt-0" v-show="sections.showCriteria">
                        <span class="lbl_mas_cri">Criterios generales para la creación de un usuario.</span>
                    </v-col> -->
                    <v-col cols="12" class="d-flex justify-content-center pt-0">
                        <v-expand-transition>
                            <UsuarioCriteriaSection
                                v-show="sections.showCriteria"
                                ref="CriteriaSection"
                                :options="options"
                                :user="resource"
                                :criterion_list="criterion_list_opt"
                            />
                        </v-expand-transition>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="3" class="rem-m">
                        <DefaultToggle v-model="resource.active" dense
                                active-label="Usuario activo" inactive-label="Usuario inactivo" @onChange="modalStatusEdit" />
                    </v-col>
                    <v-col cols="9">
                        <span class="lbl_error_cri" v-show="show_lbl_error_cri">*Debes completar todos los criterios obligatorios.</span>
                    </v-col>
                </v-row>

            </v-form>

            <UsuarioFormInfoModal
              width="30vh"
              :ref="modalUsuarioFormInfoOptions.ref"
              :options="modalUsuarioFormInfoOptions"
              @onCancel="closeFormModal(modalUsuarioFormInfoOptions), confirmModalInfo = true"
              @onConfirm="confirmByModalInfo()"
            />

            <PasswordGeneratorModal
                width="40vw"
                :ref="modalPasswordOptions.ref"
                :options="modalPasswordOptions"
                @onConfirm="closeFormModal(modalPasswordOptions)"
                @onCancel="closeFormModal(modalPasswordOptions)"
            />


            <DialogConfirm
                :ref="updateStatusModal.ref"
                v-model="updateStatusModal.open"
                :options="updateStatusModal"
                width="408px"
                title="Cambiar de estado al usuario"
                subtitle="¿Está seguro de cambiar de estado al usuario?"
                @onConfirm="updateStatusModal.open = false"
                @onCancel="closeModalStatusEdit"
            />

            <!-- MODAL ALMACENAMIENTO -->
            <GeneralStorageModal
                :ref="modalGeneralStorageOptions.ref"
                :options="modalGeneralStorageOptions"
                width="45vw"
                @onCancel="closeFormModal(modalGeneralStorageOptions)"
                @onConfirm="closeFormModal(modalGeneralStorageOptions),
                            openFormModal(modalGeneralStorageEmailSendOptions, null, 'status', 'Solicitud enviada')"
            />
            <!-- MODAL ALMACENAMIENTO -->

            <!-- MODAL EMAIL ENVIADO -->
            <GeneralStorageEmailSendModal
                :ref="modalGeneralStorageEmailSendOptions.ref"
                :options="modalGeneralStorageEmailSendOptions"
                width="35vw"
                @onCancel="closeFormModal(modalGeneralStorageEmailSendOptions)"
                @onConfirm="closeFormModal(modalGeneralStorageEmailSendOptions)"
            />
            <!-- MODAL EMAIL ENVIADO -->

            <!-- === MODAL ALERT STORAGE === -->
            <DefaultStorageUserAlertModal
                :ref="modalAlertStorageUsersOptions.ref"
                :options="modalAlertStorageUsersOptions"
                width="25vw"
                @onCancel="closeFormModal(modalAlertStorageUsersOptions)"
                @onConfirm="openFormModal(modalGeneralStorageOptions, null, 'status', 'Aumentar mi plan'),
                            closeFormModal(modalAlertStorageUsersOptions)"
            />
            <!-- === MODAL ALERT STORAGE === -->

        </template>
    </DefaultDialog>
</template>

<script>
import GeneralStorageModal from '../General/GeneralStorageModal.vue';
import GeneralStorageEmailSendModal from '../General/GeneralStorageEmailSendModal.vue';
import DefaultStorageUserAlertModal from '../Default/DefaultStorageUserAlertModal.vue';

import UsuarioCriteriaSection from "./UsuarioCriteriaSection";
import PasswordGeneratorModal from "./PasswordGeneratorModal";
import DialogConfirm from "../../components/basicos/DialogConfirm";
import UsuarioFormInfoModal from './UsuarioFormInfoModal.vue';

export default {
    components: {
        UsuarioCriteriaSection, PasswordGeneratorModal, DialogConfirm,
        DefaultStorageUserAlertModal, GeneralStorageModal, GeneralStorageEmailSendModal, UsuarioFormInfoModal
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
            modalAlertStorageUsersOptions: {
                ref: 'AlertStorageUsersModal',
                open: false,
                showCloseIcon: true,
                base_endpoint: '/general',
                confirmLabel:'Solicitar',
                persistent: true,
            },
            modalGeneralStorageOptions: {
                ref: 'GeneralStorageModal',
                open: false,
                showCloseIcon: true,
                base_endpoint: '/general',
                confirmLabel:'Enviar',
                persistent: true
            },
            modalGeneralStorageEmailSendOptions: {
                ref: 'GeneralStorageEmailSendModal',
                open: false,
                showCloseIcon: true,
                hideCancelBtn: true,
                confirmLabel:'Entendido',
                persistent: false
            },
            errors: [],
            sections: {
                showCriteria: false
            },
            criterion_list: [],
            criterion_list_req: [],
            criterion_list_opt: [],
            resourceDefault: {
                id: null,
                name: '',
                lastname: '',
                surname: '',
                email: '',
                document: '',
                username: '',
                person_number: '',
                phone_number: '',

                criterion_list: {},
                criterion_list_final: {},
                active: true,
                national_occupation_id:null,
                curp:''
            },
            resource_criterion_static: {
                usuario: {},
                criterios: [],
                criterion_values: {}
            },
            resource: {},
            selects: {},
            rules: {
                name: this.getRules(['required', 'max:100', 'text']),
                lastname: this.getRules(['required', 'max:100', 'text']),
                surname: this.getRules(['required', 'max:100', 'text']),
                document: this.getRules(['required', 'min:5']),
                password: this.getRules(['required', 'min:8']),
                email: this.getRules(['required','min:4' ,'email']),
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
            confirmModalInfo: true,
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
            /*DC3 - DC4*/
            national_occupations_catalog:[],
            position_dc3:{values:[],code:''},
            has_DC3_functionality:false
       }
    },
    mounted() {
        let vue = this
        vue.sections.showCriteria = vue.options.action === 'edit'
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
        closeModalStatusEdit(){
            let vue = this
            vue.updateStatusModal.open = false
            vue.resource.active = !vue.resource.active
        },
        modalStatusEdit(){
            let vue = this
            const edit = vue.options.action === 'edit'
            if(edit){
                vue.updateStatusModal.open = true
                vue.updateStatusModal.status_item_modal = !vue.resource.active
            }
        },
        checkBuildFinalIds() {
            let vue = this;
            const { criterios, criterion_values } = vue.resource_criterion_static;

            const criterios_ids = Object.entries(criterios).map(([, ele]) => ele.id);
            const criterios_array = Object.entries(criterion_values).filter(([, ele]) => criterios_ids.includes(ele.criterion_id) );

            return criterios_array.map(([, ele]) => ele.id );
        },
        checkChangesAtArrays(criterion_list_final, criterios_final_ids) {
            let stack_criterios = [];

            const { loop, compare } = (criterion_list_final.length >= criterios_final_ids.length)
                                ? { loop: criterion_list_final, compare: criterios_final_ids } :
                                  { loop: criterios_final_ids, compare: criterion_list_final };
            loop.forEach((id) => {
                if(!compare.includes(id)) {
                    stack_criterios.push(id);
                }
            });

            return stack_criterios;
        },
        checkChangesAtCriterios(criterion_list_final) {
            let vue = this;

            const { criterios, criterion_values } = vue.resource_criterion_static;
            const criterios_final_ids = vue.checkBuildFinalIds();
            const stack_criterios = vue.checkChangesAtArrays(criterion_list_final, criterios_final_ids);

            let same_criterios = (stack_criterios.length === 0);
            let changes_criterios = [];

            if(!same_criterios) {
                const criterios_list = Object.entries(criterios);

                for (let i = 0; i < criterios_list.length; i++) {
                    const [, ele] = criterios_list[i];
                    const criterio_values = ele.values.map((val) => val.id);
                    const criterio_match  = stack_criterios.some((id) => criterio_values.includes(id));

                    if(criterio_match) {
                        const { code, name, id } = ele;
                        changes_criterios.push({code, name, id});
                    }
                }
            }

            return { same_criterios, changes_criterios };
        },
        checkChangesAtUserData(data, stack_keys) {
            let vue = this;
            const { usuario } = vue.resource_criterion_static;

            const changes_data = stack_keys.filter((ele) => {
                const current_value = data[ele.key];
                const origin_value = usuario[ele.key];

                return (current_value !== origin_value);
            });

            const same_data = (changes_data.length === 0);
            return { same_data, changes_data };
        },
        confirmByModalInfo() {
            let vue = this;
            vue.confirmModalInfo = false;
            vue.confirmModal();
        },
        async confirmModal() {
            let vue = this;
            vue.showLoader();

            const validateForm = vue.validateForm('UsuarioForm')
            vue.show_lbl_error_cri = !validateForm

            const edit = vue.options.action === 'edit'
            const base = `${vue.options.base_endpoint}`
            const method = edit ? 'put' : 'post';

            if (validateForm && vue.isValid()) {

                // === verificar limite usuarios ===
                let check_users = false;
                if (!edit) {
                    check_users = await vue.checkUsersStorageLimit();
                    // console.log('check_users limit')
                    if(check_users) return;
                }
                // === verificar limite usuarios ===


                let url = edit ? `${base}/${vue.resource.id}/update` : `${base}/store`;
                let data = vue.resource
                vue.parseCriterionValues()

                // === validar cambio de criterios
                if (vue.confirmModalInfo && edit) {
                    const checkCriterios = vue.checkChangesAtCriterios(data.criterion_list_final); // criterios
                    const checkData = vue.checkChangesAtUserData(data, [{key:'document', label:'Identificador'}]); // documento - dni

                    console.log('checkCriterios')
                    console.log(checkCriterios)
                    console.log('checkData')
                    console.log(checkData)

                    if((!checkCriterios.same_criterios && checkCriterios.changes_criterios.length > 0) || !checkData.same_data) {
                        vue.hideLoader();
                        vue.modalUsuarioFormInfoOptions.resource = { changes_criterios: checkCriterios.changes_criterios ,
                                                                     changes_data: checkData.changes_data };
                        vue.openFormModal(vue.modalUsuarioFormInfoOptions, null, 'status', 'Actualización de datos')
                        return;
                    }
                }
                // === validar cambio de criterios

            /*  console.log('sending form user', data);
                vue.errors = [];
                vue.hideLoader(); */

                vue.$http[method](url, data)
                    .then(({data}) => {
                        vue.errors = []
                        vue.confirmModalInfo = true;
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        vue.hideLoader()
                        vue.queryStatus("usuarios", "crear_usuario");
                    })
                    .catch((error) => {
                        vue.resource.criterion_list_final = {}
                        vue.hideLoader()

                        if (error && error.errors)
                            vue.errors = error.errors
                    })
            } else {
                vue.hideLoader()
            }
        }
        ,
        parseCriterionValues() {
            let vue = this
            let temp = []

            vue.criterion_list.forEach(criterion => {
                const user_criterion_value = vue.resource.criterion_list[criterion.code]

                if (criterion.multiple) user_criterion_value.forEach(val => temp.push(val))
                else if (user_criterion_value) temp.push(user_criterion_value)
            })

            vue.resource.criterion_list_final = temp;
        },
        resetSelects() {
            let vue = this
            // CLOSE CRITERIA SECTION
            vue.sections.showCriteria = false
            if (vue.resource)
                vue.resource.password = null;
        }
        ,
        isValid() {

            let valid = true;
            let errors = [];

            // Validation: module is required

            if (this.criterion_list.length === 0) {

                errors.push({
                    message: 'El criterio módulo es obligatorio'
                })
                valid = false;
            }

            if (!valid) {
                this.errors = errors;
            }

            return valid;
        }
        ,
        async loadData(resource) {
            let vue = this

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/edit` : `form-selects`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.criterion_list_req = [];
                    vue.criterion_list_opt = [];
                    vue.has_DC3_functionality = data.data.has_DC3_functionality;
                    if(vue.has_DC3_functionality){
                        vue.national_occupations_catalog = data.data.national_occupations_catalog;
                        vue.position_dc3 = data.data.position_dc3;
                    }


                    vue.criterion_list = data.data.criteria
                    vue.criterion_list.forEach(criterion => {
                        // console.log(criterion)
                        // vue.resource.criterion_list[`${criterion.code}`] = null
                        let criterion_default_value = criterion.multiple ? [] : null
                        Object.assign(vue.resource.criterion_list, {[`${criterion.code}`]: criterion_default_value})

                        if(criterion.required)
                            vue.criterion_list_req.push(criterion)
                        else
                            vue.criterion_list_opt.push(criterion)
                    })

                    if (resource) {
                        //console.log('loadData', { url, criterion_list: data.data.usuario.criterion_list });
                        vue.resource = data.data.usuario;
                        const { document: documento } = data.data.usuario
                        vue.resource_criterion_static.usuario = { document: documento }; // copy
                        vue.resource_criterion_static.criterios = { ...data.data.criteria }; // copy
                        vue.resource_criterion_static.criterion_values = { ...data.data.usuario.criterion_values }; // copy
                    }

                    return 0;
                })
        },
        loadSelects() {
            let vue = this

        },
        async checkUsersStorageLimit() {
            let vue = this;
            const response = await vue.$http.get('/general/workspaces-users');
            const data = response.data.data;

            if (data.user_storage_check) {
                vue.openFormModal(vue.modalAlertStorageUsersOptions, null, null, 'Alerta de capacidad de usuarios');
                return data.user_storage_check;
            }
            vue.hideLoader();
            return false;
        }
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
span.lbl_mas_cri {
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 400;
    color: #434D56;
}
.rem-m .v-input.default-toggle {
    margin-top: 0 !important;
    width: 100%;
}
.lbl_error_cri{
    color: #FF5252;
    font-family: "Nunito", sans-serif;
    font-weight: 700;
    font-size: 13px;
}
.v-application .error--text .v-text-field__details {
    display: none;
}
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
.v-dialog .v-text-field .v-input__control,
.v-dialog .v-text-field .v-input__slot,
.v-dialog .v-text-field fieldset{
    border-radius: 10px;
    border-color: #D9D9D9;
}
.v-dialog .v-input .v-label {
    font-family: "Nunito", sans-serif;
    font-size: 14px;
}
.v-dialog .theme--light.v-input,
.v-dialog .theme--light.v-input input,
.v-dialog .theme--light.v-input textarea {
    font-family: "Nunito", sans-serif;
    font-size: 14px;
}
// .v-dialog.v-dialog--active.v-dialog--scrollable .v-card__actions {
//     border-color: #94DDDB !important;
// }
.v-dialog button.v-icon.notranslate.v-icon--link.mdi.mdi-close.theme--light {
    font-size: 18px;
}
.v-dialog .v-card__title.default-dialog-title ::-webkit-scrollbar-thumb {
    background-color: #7DDBD8E5 !important;
}
.v-dialog .v-text-field--filled>.v-input__control>.v-input__slot,
.v-dialog .v-text-field--full-width>.v-input__control>.v-input__slot,
.v-dialog .v-text-field--outlined>.v-input__control>.v-input__slot {
    min-height: 50px;
}
.v-dialog .v-text-field--outlined .v-label {
    top: 16px;
}
.v-dialog .v-list-item .v-list-item__subtitle,
.v-dialog .v-list-item .v-list-item__title {
    font-family: "Nunito", sans-serif;
    font-size: 13px;
}
.v-dialog .v-text-field--outlined .v-label--active {
    color: #434D56 !important;
}
.v-dialog .v-text-field.error--text fieldset {
    border-color: #ff5252;
}
.v-text-field--enclosed .v-input__append-inner button.v-icon.mdi.mdi-close{
    top: -3px;
}
</style>
