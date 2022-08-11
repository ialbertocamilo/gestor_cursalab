<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="accountForm" class="--mb-15">

                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <!-- <v-col cols="6" class="d-flex justify-content-center"> -->
                        <!-- <DefaultInput v-model="resource.name" label="Nombre" :rules="rules.name" /> -->
                    <!-- </v-col> -->
                    <v-col cols="12" class="d-flex justify-content-center">
                        <!-- <DefaultInput v-model="resource.email" label="Correo" :rules="rules.email" /> -->

                            <!-- hide-delimiter-background -->
                <v-carousel
                    height="500"
                    show-arrows-on-hover
                    light
                  >
                    <v-carousel-item
                      v-for="(segment, i) in segments"
                      :key="i"
                    >

                        <!-- :color="colors[0]" -->
                        <v-sheet
                            height="100%"
                        >
                            <segment :segment="segment" :criteria="criteria" class="mx-5" />
                       <!--  <v-row
                          class="fill-height"
                          align="center"
                          justify="center"
                        >
                          <div class="text-h2">
                            {{ slide }} Slide
                          </div>
                        </v-row> -->
                      </v-sheet>
                    </v-carousel-item>
                  </v-carousel>
                    </v-col>
                </v-row>

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <v-btn
                            class="white-text font-bold btn-agregar-curricula"
                            color="#1867c0"
                            @click="addSegmentation()"
                        >
                            <!-- :disabled="
                                escuela.curso_seleccionado == null ||
                                escuela.curso_seleccionado == '' 
                            " -->
                            Agregar segmentación
                        </v-btn>
                    </v-col>
                </v-row>

               

             <!--    <v-subheader class="mt-5 px-0"><strong>Configuración de tokens</strong></v-subheader>

                <v-divider class="mt-0" />

                <v-row justify="space-around">
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.key" label="API Key" :rules="rules.key" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.secret" label="API Secret" :rules="rules.secret" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.token" label="Token" :rules="rules.token" />
                    </v-col>
                    <v-col cols="6" class="d-flex justify-content-center">
                        <DefaultInput v-model="resource.refresh_token" label="Refresh token" :rules="rules.refresh_token" />
                    </v-col>
                </v-row> -->

                <!-- <v-subheader class="mt-5"><strong>Datos adicionales</strong></v-subheader> -->

  <!--               <v-divider class="mx-3" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultTextArea v-model="resource.description" label="Descripción" :rows="4" />
                    </v-col>
                </v-row> -->

<!--                 <v-row align="center" align-content="center">
                    <v-col cols="6" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.active" />
                    </v-col>
                </v-row>
 -->
            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['name', 'email', 'username', 'password', 'key', 'secret', 'token', 'active'];

import Segment from "./Segment";

export default {

    components: {
        Segment,
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
            colors: [
              'indigo',
              'warning',
              'pink darken-2',
              'red lighten-1',
              'deep-purple accent-4',
            ],
            slides: [
              'First',
              'Second',
              'Third',
              'Fourth',
              'Fifth',
            ],


            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,

                name: null,

                email: '',
                username: '',
                password: '',

                key: null,
                secret: null,
                identifier: null,

                type: null,
                service: null,
                plan: null,

                token: null,
                refresh_token: null,

                active: true,

                description: '',

                sdk_token: null,
                zak_token: null,
            },
            // resource: {},
            segments: [],
            segment: {
                name: 'XXX',
                model_type: 'XX',
                model_id: 'X',
                criteria_selected: [],
            },
            criteria: [],

            rules: {
                // text: this.getRules(['required', 'max:255']),
                name: this.getRules(['required', 'max:255']),
                // name: this.getRules(['required', 'max:255']),
                service: this.getRules(['required']),
                plan: this.getRules(['required']),
                type: this.getRules(['required']),
            },
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
            vue.$refs.accountForm.resetValidation()
        },
        async addSegmentation() {
            let vue = this;
            // let curso_seleccionado = vue.modulos[index1].categorias[index2].curso_seleccionado;
            // // 
            // let modulo_id = vue.modulos[index1].id;
            // let tipos_criterios = [];
            // await axios
            //     .get(`/curricula/tc_s/${vue.modulos[index1].id}`)
            //     .then((res) => {
            //         tipos_criterios = res.data.data;
            //     })
            //     .catch((err) => {
            //         console.log(err);
            //     });

            // let id = vue.modulos[index1].categorias[index2].curricula.length + 1;

            // vue.nuevaCurricula = Object.assign({}, this.nuevaCurricula, {
            //     curricula_id: `n-${id}`,
            //     curso_id: curso_seleccionado,
            //     tipos_criterio_seleccionado: [],
            //     tipos_criterios: tipos_criterios,
            //     modulo_id: modulo_id,
            //     loading: false,
            // });

            vue.segments.push(vue.segment);
            // vue.nuevaCurricula = {};
        },
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('accountForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                let formData = vue.getMultipartFormData(method, vue.resource, fields);

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
            vue.selects.types = []
            vue.selects.selects = []
            vue.selects.plans = []
        },
        async loadData(resource) {
            let vue = this
            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                let _data = data.data

                vue.segments = _data.segments
                vue.criteria = _data.criteria
            })

            return 0;
        },
        loadSelects() {
            let vue = this
        },
    }
}
</script>
