<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="UsuarioForm">

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0">
                        <strong>Información de usuario</strong>
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
                            :rules="rules.nombre"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.lastname"
                            label="Apellido Paterno"
                            :rules="rules.nombre"
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.surname"
                            label="Apellido Materno"
                            :rules="rules.nombre"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center">
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
                        />
                    </v-col>
                    <v-col cols="4" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.password"
                            label="Contraseña"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0"
                           @click="sections.showCriteria = !sections.showCriteria"
                           style="cursor: pointer">
                        <strong>Criterios</strong>
                        <v-icon v-text="sections.showCriteria ? 'mdi-chevron-up' : 'mdi-chevron-down'"/>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-center pt-0">

                        <v-expand-transition>
                            <UsuarioCriteriaSection
                                v-show="sections.showCriteria"
                                ref="CriteriaSection"
                                :options="options"
                                :usuario="resource"
                                :criteria_list="criteria_list"
                            />
                        </v-expand-transition>

                    </v-col>

                </v-row>

                <div style="height: 500px"></div>

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
            sections: {
                showCriteria: false
            },
            showMatricula: false,
            criteria_list: [],
            resourceDefault: {
                id: null,
                name: '',
                lastname: '',
                surname: '',
                email: '',
                document: '',

                criteria: {}
            },
            resource: {},
            selects: {
                genres: ['M', 'F'],
                modules: [],
                boticas: [],
                groups: [],
                cargos: [],
                carreras: []
                // [
                //     {id: 1, nombre: "MASCULINO"},
                //     {id: 2, nombre: "FEMENINO"}
                // ]
            },
            matriculaModalOptions: {
                open: false,
                ref: 'UsuarioMatriculaModal',
                base_endpoint: '/usuarios',
                title: 'Matrícula del Usuario',
                hideCancelBtn: true,
                confirmLabel: 'Cerrar',
            },
            rules: {
                modulo: this.getRules(['required']),
                botica: this.getRules(['required']),
                genero: this.getRules(['required']),
                cargo: this.getRules(['required']),
                nombre: this.getRules(['required', 'max:100', 'text']),
                dni: this.getRules(['required', 'number', 'min:8'])
            }
        }
    },
    mounted() {
        let vue = this
        vue.sections.showCriteria = vue.options.action === 'edit'
    },
    methods: {
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.resetFormValidation('UsuarioForm')
        },
        async confirmModal() {
            let vue = this
            vue.showLoader()
            const validateForm = vue.validateForm('UsuarioForm')

            if (validateForm) {

                let url = `${vue.options.base_endpoint}/crear`
                let data = {
                    accion: vue.options.action === 'edit' ? 1 : 0,
                    usuario: vue.resource,
                    ciclos: vue.resource.ciclos,
                    carrera_id: vue.resource.carrera ? vue.resource.carrera.id : 0,
                    grupo: vue.resource.botica.criterio.id
                }
                vue.$http.post(url, data)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        vue.hideLoader()
                    })
            } else {
                const validateMatricula = !!vue.resource.carrera
                if (!validateMatricula)
                    vue.showMatricula = true

                vue.hideLoader()
            }
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modules = []
            vue.selects.cargos = []
            // Selects dependientes
            vue.selects.boticas = []
            vue.selects.groups = []
            vue.selects.carreras = []
            // CLOSE MATRICULA SECTION
            vue.showMatricula = false
        },
        async loadData(resource) {
            let vue = this

            let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/search` : `form-selects`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.criteria_list = data.data.criteria

                    if (resource) {
                        vue.resource = data.data.usuario
                        vue.resource.ciclos = vue.resource.matriculas_pasadas
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
