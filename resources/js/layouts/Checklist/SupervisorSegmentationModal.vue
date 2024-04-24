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
                    <v-tab>
                        Elegir supervisor(es)
                    </v-tab>
                </v-tabs>
                <v-tabs-items v-model="tabs_sup">
                    <v-tab-item>
                        <v-row justify="space-around">
                            <v-col cols="11">
                                <span class="text_default lbl_tit">Define los criterios que se aplicarán para hacer la relación entre los supervisores y los checklist.</span>
                            </v-col>
                            <v-col cols="11">
                                <DefaultAutocomplete
                                    dense
                                    label="Criterios"
                                    v-model="supervisor_criteria"
                                    :items="list_criteria"
                                    multiple
                                    item-text="name"
                                    item-id="id"
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
                    <v-tab-item>
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
                    </v-tab-item>
                </v-tabs-items>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import AsignacionXDni from "../Supervisores/AsignacionXDni";


export default {
    components: { AsignacionXDni },
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
            list_criteria:[],
            supervisor_criteria:[],
            supervisor_assigned_directly:[]
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetValidation();
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        async confirmModal() {
            let vue = this
            const url = `${vue.options.base_endpoint}/${vue.resource.id}/save-supervisor-segmentation`
            vue.showLoader();
            const segments_supervisors_direct = vue.$refs.AsignacionSupervisores ? vue.$refs.AsignacionSupervisores.usuarios_ok : [];
            const supervisor_ids = segments_supervisors_direct.map((s) => s.id)
            await vue.$http
                .post(url, {
                    supervisor_criteria:vue.supervisor_criteria,
                    supervisor_ids
                })
                .then(({data}) => {
                    vue.hideLoader();
                    if(data.data.msg){
                        vue.showAlert(data.data.msg);
                    }
                    vue.$emit('onConfirm',{
                        checklist: data.data.checklist,
                        next_step: data.data.next_step
                    });
                vue.resetSelects();
                })
                .catch(error => {
                    if (error && error.errors) vue.errors = error.errors;
                    vue.hideLoader();
                });
        }
        ,
        resetSelects() {
            let vue = this;
            vue.list_criteria = [];
            vue.supervisor_criteria = [];
        },
        async loadData(resource) {
            let vue = this;
            vue.resource = resource;
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

            url = `${vue.options.base_endpoint}/${resource.id}/supervisor-segmentation`
            await vue.$http
                .get(url)
                .then(({data}) => {
                    vue.supervisor_criteria = data.data.supervisor_criteria;
                    vue.supervisor_assigned_directly = data.data.supervisor_assigned_directly;
                })
                .catch(error => {
                    if (error && error.errors) vue.errors = error.errors;
                    vue.hideLoader();
                });
        },
        async loadSelects() {
            let vue = this;
        }
    }
}
</script>
