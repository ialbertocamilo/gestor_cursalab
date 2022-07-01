<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="UsuarioForm">
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-end py-0">
                        <p class="mb-0" style="font-size: .9375rem; line-height: 1;">{{
                                resource.grupo_sistema_nombre
                            }}</p>
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.modules"
                            v-model="resource.modulo"
                            label="Módulos"
                            @onChange="loadBoticas"
                            :disabled="options.action === 'edit'"
                            return-object
                            :rules="rules.modulo"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.boticas"
                            v-model="resource.botica"
                            return-object
                            label="Botica"
                            :disabled="options.action === 'edit'"
                            @onChange="loadCiclos"
                            no-data-text="Seleccione un módulo"
                            :rules="rules.botica"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.nombre"
                            label="Nombres y Apellidos"
                            :rules="rules.nombre"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.dni"
                            label="Documento de identidad"
                            :rules="rules.dni"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput
                            clearable
                            v-model="resource.password"
                            label="Contraseña"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultSelect
                            clearable
                            :items="selects.genres"
                            v-model="resource.sexo"
                            label="Género"
                            :rules="rules.genero"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.cargos"
                            v-model="resource.cargo"
                            open-up
                            label="Cargo"
                            :rules="rules.cargo"
                        />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-start flex-column">
                        <DefaultToggle v-model="resource.estado"/>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-between pb-0"
                           @click="showMatricula = !showMatricula"
                           style="cursor: pointer">
                        <strong>Configurar Matrícula</strong>
                        <v-icon v-text="showMatricula ? 'mdi-chevron-up' : 'mdi-chevron-down'"/>
                    </v-col>
                    <v-col cols="12" class="py-0">
                        <DefaultDivider/>
                    </v-col>
                </v-row>

                <v-row justify="space-around" align="start" align-content="center">
                    <v-col cols="12" class="d-flex justify-content-center pt-0">

                        <v-expand-transition>
                            <UsuarioMatriculaSection
                                v-show="showMatricula"
                                ref="MatriculaSection"
                                :options="options"
                                :usuario="resource"
                                :carreras="selects.carreras"
                            />
                        </v-expand-transition>

                    </v-col>

                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>

<script>
import UsuarioMatriculaSection from "./UsuarioMatriculaSection";

export default {
    components: {UsuarioMatriculaSection},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            showMatricula: false,
            resourceDefault: {
                id: null,
                config_id: null,
                botica_id: null,
                nombre: '',
                dni: null,
                password: null,
                sexo: null,
                cargo: null,
                estado: false,
                grupo_nombre: null,
                grupo_sistema_nombre: '',
                matriculas_pasadas: [],
                matricula_presente: null,
                carrera: null,
                modulo: null,
                botica: null,
                ciclos: [],
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
        vue.showMatricula = vue.options.action === 'edit' ? false : true;
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
            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })
            let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/search` : `form-selects`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data.modules
                    vue.selects.cargos = data.data.cargos
                    if (resource) {
                        vue.resource = data.data.usuario
                        vue.resource.ciclos = vue.resource.matriculas_pasadas
                    }

                })
            return 0;
        },
        loadSelects() {
            let vue = this
            if (vue.resource.modulo && vue.resource.modulo.id)
                vue.loadBoticas()

            if (vue.resource.botica && vue.resource.botica.criterio)
                vue.loadCarreras()
        },
        loadBoticas() {
            let vue = this
            if (!vue.resource.modulo) {
                vue.selects.boticas = []
                vue.selects.carreras = []
                return
            }
            let url = `/boticas/search-no-paginate?config_id=${vue.resource.modulo.id}`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.boticas = data.data.boticas
                })

            this.loadCarreras()
        },
        loadCarreras() {
            let vue = this
            // if (!vue.resource.botica || !vue.resource.botica.criterio) {
            // if (!vue.resource.botica || !vue.resource.botica.criterio) {
            // vue.selects.carreras = []
            // return
            // }

            const modulo_id = vue.resource.modulo.id
            const botica_id = vue.resource.botica ? vue.resource.botica.id : 0
            let url = `${vue.options.base_endpoint}/${modulo_id}/${botica_id}/carreras-x-grupo`
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.carreras = data.carreras
                })


        },
        loadCiclos() {
            let vue = this
            vue.$refs['MatriculaSection'].loadCiclos();
        }
    }
}
</script>
