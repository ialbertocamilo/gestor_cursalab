<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="careerCategoryForm" class="mb-15">
                <v-tabs
                    v-model="tabs"
                    fixed-tabs
                >
                    <v-tabs-slider></v-tabs-slider>

                    <v-tab
                        :href="'#modulo-' + modulo.id"
                        class="primary--text"
                        v-for="modulo in selects.modulos"
                        :key="modulo.id"
                    >
                        <span>
                            {{ modulo.nombre.replace('Capacitación ', '') }}
                        </span>
                    </v-tab>

                </v-tabs>

                <v-tabs-items v-model="tabs">
                    <v-tab-item
                        v-for="(carreras, index) in resource.modulos_carreras"
                        :key="index"
                        :value="'modulo-' + index"
                    >
                        <v-card flat class="mt-5">
                            <v-row justify="space-around">
                                <v-col
                                   cols="6"
                                   class="d-flex justify-content-center"
                                   v-for="carrera in carreras"
                                   :key="'modulo-' + index + '-carrera-' + carrera.id"
                                >
                                    <DefaultSelect
                                        clearable
                                        :items="selects.categorias"
                                        v-model="carrera.glosario_categorias"
                                        :label="carrera.nombre"
                                        multiple
                                        return-object
                                        :count-show-values="3"
                                        :show-select-all="false"
                                    />
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-tab-item>
                </v-tabs-items>
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>


const fields = []
// const fields = ['modulos_carreras'];
// const array_fields = []
const array_fields = ['modulos_carreras']

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
            tabs: null,
            resourceDefault: {
                modulos_carreras: [],
            },
            resource: {},
            selects: {
                categorias: [],
                modulos: [],
                // all: [],
            },

            rules: {
                // modulo: this.getRules(['max:50']),
                // nombre: this.getRules(['required', 'max:200']),
            }
        }
    },
    methods: {
        log(a) {
            console.log(a);
            return '';
        },
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.careerCategoryForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            this.showLoader()

            const validateForm = vue.validateForm('careerCategoryForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url =`${base}/carreras-categorias`;

            let method = 'PUT';

            if (validateForm ) {

                // let formData = vue.getMultipartFormData(method, vue.resource, fields, [], array_fields);

                // vue.$http.post(url, formData)
                vue.$http.post(
                        url,
                    {
                        'modulos_carreras' : vue.resource.modulos_carreras,
                        '_method' : method
                    })
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modulos = []
            vue.selects.categorias = []
        }
        ,
        async loadData(resource) {

            let vue = this

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = `${base}/carreras-categorias`;

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modulos = data.data.modulos
                    vue.selects.categorias = data.data.categorias
                    vue.resource.modulos_carreras = data.data.carreras
                })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
