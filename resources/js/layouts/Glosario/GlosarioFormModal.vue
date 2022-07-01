<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="glosarioForm" class="mb-15">

                <DefaultErrors :errors="errors" />
                
                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.nombre"
                                      label="Nombre"
                                      :rules="rules.nombre"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="4" class="d-flex justify-content-center" v-for="modulo in resource.modulos" :key="modulo.id">
                        <DefaultInput clearable
                                      v-model="modulo.codigo"
                                      :label="'Código - ' + modulo.nombre.replace('Capacitación ', '')"
                                      :rules="rules.modulo"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around" v-for="select in selects.all" :key="select.id">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelectOrCreate clearable
                                             :items="select.list"
                                             v-model="resource[select.relation]"
                                             return-object
                                             :multiple="select.multiple"
                                             :count-show-values="select.show_values"
                                             :label="select.name"
                                             :show-select-all="false"
                        />
                    </v-col>
                </v-row>

                <v-row align="start" align-content="center">
                    <v-col cols="4" class="d-flex justify-content-start">
                        <DefaultToggle v-model="resource.estado"/>
                    </v-col>
                </v-row>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['nombre', 'estado', 'laboratorio', 'categoria', 'jerarquia', 'advertencias', 'condicion_de_venta', 'via_de_administracion', 'grupo_farmacologico', 'dosis_adulto', 'dosis_nino', 'recomendacion_de_administracion', 'principios_activos', 'contraindicaciones', 'interacciones', 'reacciones'];

const array_fields = ['modulos']

export default {
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
            resourceDefault: {
                id: null,
                nombre: '',

                modulos: [],

                laboratorio: null,
                categoria: null,
                jerarquia: null,
                advertencias: null,
                condicion_de_venta: null,
                via_de_administracion: null,
                grupo_farmacologico: null,
                dosis_adulto: null,
                dosis_nino: null,
                recomendacion_de_administracion: null,

                principios_activos: [],
                contraindicaciones: [],
                interacciones: [],
                reacciones: [],

            },
            resource: {},
            selects: {
                modulos: [],
                all: [],
            },

            rules: {
                // modulo: this.getRules(['max:50']),
                nombre: this.getRules(['required', 'max:200']),
            }
        }
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
            vue.$refs.glosarioForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('glosarioForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            if (validateForm ) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields, [], array_fields);

                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                    }).catch((error) => {
                        if (error && error.errors)
                            vue.errors = error.errors
                    })

            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modulos = []
            vue.selects.all = []
        },
        async loadData(resource) {
            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            // let url = `${vue.options.base_endpoint}/${resource ? `${resource.id}/search` : `form-selects`}`
            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.all = data.data.selects

                    if (resource) {
                        vue.resource = data.data.glosario
                        vue.resource.modulos = data.data.modulos

                        // console.log(vue.resource)
                    }else{
                        vue.resource.modulos = data.data.modulos
                    }

                })
            return 0;
        },
        loadSelects() {
            let vue = this
            // if (vue.resource.modulo && vue.resource.modulo.id)
            //     vue.loadBoticas()
        },
    }
}
</script>
