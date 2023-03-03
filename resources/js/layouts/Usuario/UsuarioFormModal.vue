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
                            <strong>Información del usuario</strong>
                            <span>*Criterios obligatorios</span>
                        </div>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.name"
                            label="Nombres"
                            :rules="rules.name"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.lastname"
                            label="Apellido Paterno"
                            :rules="rules.lastname"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.surname"
                            label="Apellido Materno"
                            :rules="rules.surname"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center">
                        <!-- :rules="rules.email" -->
                        <DefaultInput
                            clearable
                            v-model="resource.email"
                            label="Correo electrónico"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.document"
                            label="Identificador"
                            :rules="rules.document"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.password"
                            label="Contraseña"
                            type="password"
                            ref="passwordRefModal"
                            :rules="options.action === 'edit' ? rules.password_not_required : rules.password"
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

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong>Criterios obligatorios para la creación de un usuario.</strong>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-center pt-0">
                            <UsuarioCriteriaSection
                                ref="CriteriaSection"
                                :options="options"
                                :user="resource"
                                :criterion_list="criterion_list"
                                :only_req="true"
                            />
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0"
                        @click="sections.showCriteria = !sections.showCriteria"
                        style="cursor: pointer">
                        <strong>Más Criterios</strong>
                        <v-icon v-text="sections.showCriteria ? 'mdi-chevron-up' : 'mdi-chevron-down'"/>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="pb-0 pt-0" v-show="sections.showCriteria">
                        <span class="lbl_mas_cri">Criterios generales para la creación de un usuario.</span>
                    </v-col>
                    <v-col cols="12" class="d-flex justify-content-center pt-0">
                        <v-expand-transition>
                            <UsuarioCriteriaSection
                                v-show="sections.showCriteria"
                                ref="CriteriaSection"
                                :options="options"
                                :user="resource"
                                :criterion_list="criterion_list"
                                :only_req="false"
                            />
                        </v-expand-transition>
                    </v-col>
                </v-row>

                <v-row>
                    <v-col cols="9">
                        <span class="lbl_error_cri" v-show="show_lbl_error_cri">*Debes completar todos los criterios obligatorios.</span>
                    </v-col>
                    <v-col cols="3" class="rem-m">
                        <DefaultToggle v-model="resource.active" pre_label="Usuario"/>
                    </v-col>
                </v-row>

            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>
import UsuarioCriteriaSection from "./UsuarioCriteriaSection";

export default {
    components: {UsuarioCriteriaSection},
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
            sections: {
                showCriteria: false
            },
            criterion_list: [],
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
            },
            resource: {},
            selects: {},
            rules: {
                name: this.getRules(['required', 'max:100', 'text']),
                lastname: this.getRules(['required', 'max:100', 'text']),
                surname: this.getRules(['required', 'max:100', 'text']),
                document: this.getRules(['required', 'min:8']),
                password: this.getRules(['required', 'min:8']),
                // email: this.getRules(['required', 'min:8']),
                password_not_required: this.getRules([]),
            },
            show_lbl_error_cri: false
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
        async confirmModal() {
            let vue = this
            vue.showLoader()
            const validateForm = vue.validateForm('UsuarioForm')
            vue.show_lbl_error_cri = !validateForm

            const edit = vue.options.action === 'edit'
            const base = `${vue.options.base_endpoint}`
            const method = edit ? 'put' : 'post';

            if (validateForm && vue.isValid()) {
                let url = edit ? `${base}/${vue.resource.id}/update` : `${base}/store`;
                let data = vue.resource
                vue.parseCriterionValues()

                vue.$http[method](url, data)
                    .then(({data}) => {
                        vue.errors = []
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
                    vue.criterion_list = data.data.criteria
                    vue.criterion_list.forEach(criterion => {
                        // console.log(criterion)
                        // vue.resource.criterion_list[`${criterion.code}`] = null
                        let criterion_default_value = criterion.multiple ? [] : null
                        Object.assign(vue.resource.criterion_list, {[`${criterion.code}`]: criterion_default_value})
                    })

                    if (resource)
                        vue.resource = data.data.usuario

                    return 0;
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
</style>
