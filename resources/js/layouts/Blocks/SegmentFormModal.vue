<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
                   :persistent="true"
    >
        <template v-slot:content>
            <v-form ref="segmentForm" class="--mb-15">

                <DefaultErrors :errors="errors" />

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-end">
                        <v-btn
                            class="-"
                            @click="addSegmentation()"
                        >
                            Agregar bloque
                        </v-btn>
                    </v-col>
                </v-row>

                <v-row justify="space-around">

                    <v-col cols="12" class="d-flex justify-content-center">

                        <!-- hide-delimiter-background -->
                        <v-carousel
                            height="100%"
                            show-arrows-on-hover
                            light
                            v-model="steps"
                            hide-delimiters
                          >
                            <v-carousel-item
                              v-for="(row, i) in segments"
                              :key="i"
                            >
                                <v-sheet
                                    height="100%"
                                >
                                    <div class="text-h6 text-center">
                                        Bloque {{ i + 1 }} / {{ segments.length }} 
                                    </div>

                                    <v-divider class="mx-12" /> 

                                    <segment :segments="segments" :segment="row" :criteria="criteria" class="mx-5" :options="options" @borrar_segment="borrarBloque"/>
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

                <!-- <v-subheader class="mt-5"><strong>Datos adicionales</strong></v-subheader> -->


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
        width: String,
        model_type: String,
        model_id: Number,
    },
    data() {
        return {
            steps: 0,

            errors: [],
            showConfigTokens: false,
            resourceDefault: {
                id: null,
                name: null,
            },
            // resource: {},
            segments: [],
            criteria: [],

            rules: {
                // name: this.getRules(['required', 'max:255']),
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
            vue.$refs.segmentForm.resetValidation()
        },
        getNewSegment() {
            return {
                criteria_selected: [],
            }
        },
        async addSegmentation() {
            let vue = this;
            vue.segments.push(this.getNewSegment());

            vue.steps = vue.segments.length - 1
        },
        borrarBloque(segment) {

            let vue = this;
            
            vue.segments = vue.segments.filter((obj, idx) => {
                 return obj.id != segment.id
            });

        },
        confirmModal() {
            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('segmentForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            // let url = vue.resource.id ? `${base}/${vue.resource.id}/update` : `${base}/store`;
            let url = `${base}/store`;

            // let method = edit ? 'PUT' : 'POST';
            let method = 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                // let formData = vue.getMultipartFormData(method, vue.segments, fields);
                let formData = JSON.stringify({ 
                    model_type: vue.model_type,
                    model_id: vue.resource.id,
                    segments: vue.segments
                });

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
        },
        async loadData(resource) {
            let vue = this
            vue.errors = []

            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })

            vue.resource = resource

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            url = url + '?model_type=' + vue.model_type + '&model_id=' + resource.id

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
