<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <v-tabs
                    v-model="tabs_sup"
                    fixed-tabs
                    slider-color="primary"

                    class="col-10 offset-1"
                >
                    <v-tab>
                        Vincular por criterios
                    </v-tab>
                    <!-- <v-tab>
                        Elegir supervisor(es)
                    </v-tab> -->
                </v-tabs>
                <v-tabs-items v-model="tabs_sup">
                    <v-tab-item>
                        <v-row justify="space-around">
                            <v-col cols="11">
                                <span class="text_default lbl_tit">Define los criterios que se aplicarán para hacer la relación entre los supervisores y los checklist.</span>
                            </v-col>
                            <v-col cols="11">
                                <DefaultAutocompleteOrder
                                    dense
                                    label="Criterios"
                                    v-model="list_criteria_selected"
                                    :items="list_criteria"
                                    multiple
                                    item-text="name"
                                    item-id="id"
                                    return-object
                                    :showSelectAll="true"
                                    :loading-state="true"
                                    :count-show-values="Infinity"
                                    placeholder="Indicar criterios aquí"
                                />
                                <div class="bx_criteria_img text-center">
                                    <img src="/img/induccion/segment_supervisor.png" alt="supervisors">
                                </div>
                            </v-col>
                        </v-row>
                    </v-tab-item>
                    <!-- <v-tab-item>
                        <v-row justify="space-around">
                            <v-col cols="11" class="step_modalAsignacionXDni">
                                <AsignacionXDni
                                    description='Selecciona los supervisores que estarán dentro del checklist'
                                    apiSearchUser="/supervisores/search-instructors"
                                    apiUploadPlantilla="/supervisores/subir-excel-usuarios"
                                    :showSubidaMasiva="true"
                                    ref="AsignacionSupervisores"
                                    :load_data_default="true"
                                    :list_users_selected="supervisor_assigned_directly"
                                >
                                </AsignacionXDni>
                            </v-col>
                        </v-row>
                    </v-tab-item> -->
                </v-tabs-items>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>


export default {
    components: {  },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            tabs_sup: 0,
            resourceDefault: {
               
            },
            resource: {
                
            },
            list_criteria_selected:[],
            list_criteria:[]
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
        }
        ,
        async confirmModal() {

            let vue = this
            vue.$emit('onCancel')
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this
            // let url =  `/segments/${resource.id}/edit;`
            let url =  `/segments/${resource.id}/edit`;

            const model_type="App\\Models\\CheckList"

            url = url + "?model_type=" + model_type +
                "&model_id=" + resource.id;
            await vue.$http.get(url).then(({data}) => {
                let _data = data.data;
                if(_data.segments.length > 0) {
                    _data.segments.forEach(element => {
                        if(element.criteria_selected.length > 0) {
                            element.criteria_selected.forEach(item => {
                                vue.$nextTick(() => {
                                    vue.list_criteria.push(item)
                                })
                            });
                        }
                    });
                }
            })
            // let url = 'procesos/supervisors_users'
            // let formData = JSON.stringify({
            //             model_type: vue.model_type,
            //             model_id: null,
            //             code: vue.code,
            //             segments: vue.segments,
            //             segment_by_document: vue.segment_by_document,
            //             segments_supervisors: vue.list_criteria_selected
            //         });

            // await vue.$http
            //     .post(url, formData)
            //     .then(({data}) => {
            //         console.log(data);

            //     })
            //     .catch(error => {
            //         if (error && error.errors) vue.errors = error.errors;
            //         vue.hideLoader();
            //     });
           
        },
        async loadSelects() {
            let vue = this;
        },
    }
}
</script>
