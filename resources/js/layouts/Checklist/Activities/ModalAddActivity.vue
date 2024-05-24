<template>
    <div>
        <DefaultDialog :options="options" width="900px" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <v-form ref="activityForm">
                    <v-row>
                        <v-col cols="12" class="px-0">
                            <DefaultRichText
                                clearable
                                :height="150"
                                v-model="activity.activity"
                                label="Actividad de checklist"
                                :ignoreHTMLinLengthCalculation="true"
                                key="nueva_actividad_editor"
                                ref="nueva_actividad_editor"
                                customSelectorImage
                            />
                        </v-col>
                        <v-expansion-panels flat class="custom-expansion-block" v-model="activity.panel">
                            <v-expansion-panel >
                                <v-expansion-panel-header flat>
                                    <span style="color:#5458EA" class="d-flex">
                                        <i class="pr-1 mdi mdi-cog"></i>
                                        Configuración avanzada
                                    </span>
                                    <div class="d-flex">
                                        <v-chip small v-if="activity.checklist_response" color="#9A98F7" class="mx-1" style="max-width: min-content;color: white;">
                                            <i class="pr-1 mdi mdi-file-document-check"></i>
                                            Tipo de repuesta: {{ activity.checklist_response.name }}
                                        </v-chip>
                                        <v-chip small v-if="activity.extra_attributes.is_evaluable" color="#E57A9B" class="mx-1" style="max-width: min-content;color: white;">
                                            <i class="pr-1 mdi mdi-file-chart"></i>
                                            Será evaluable
                                        </v-chip>
                                        <v-chip small v-if="activity.extra_attributes.photo_response" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
                                            <i class="pr-1 mdi mdi-image"></i>
                                            Se agregará foto
                                        </v-chip>
                                        <v-chip small v-if="activity.extra_attributes.comment_activity" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
                                            <!-- <v-icon>{{ mdi-message-image  }}</v-icon>  -->
                                            <i class="pr-1 mdi mdi-comment-outline"></i>
                                            Se agregará comentario
                                        </v-chip>
                                    </div>
                                </v-expansion-panel-header>
                                <v-expansion-panel-content class="row">
                                    <v-row>
                                        <v-col cols="12" class="d-flex align-items-center">
                                            <span>
                                                General / tipo de respuesta
                                            </span>
                                            <v-divider></v-divider>
                                        </v-col>
                                        <v-col cols="4">
                                            <DefaultSelect 
                                                :items="checklist_type_response" 
                                                dense 
                                                item-text="name"
                                                show-required 
                                                v-model="activity.checklist_response"
                                                return-object
                                                label="Tipo de respuesta"
                                            />
                                        </v-col>
                                        <v-col cols="2" class="d-flex align-items-center">
                                            <v-checkbox
                                                class="my-0 mr-2 checkbox-label"
                                                label="Evaluable"
                                                color="primary"
                                                v-model="activity.extra_attributes.is_evaluable"
                                                hide-details="false"
                                            />
                                        </v-col>
                                        <v-col cols="3" class="d-flex align-items-center">
                                            <v-checkbox
                                                class="my-0 mr-2 checkbox-label"
                                                label="Se usará foto como respuesta"
                                                color="primary"
                                                v-model="activity.extra_attributes.photo_response"
                                                hide-details="false"
                                            />
                                        </v-col>
                                        <v-col cols="3" class="d-flex align-items-center">
                                            <v-checkbox
                                                class="my-0 mr-2 checkbox-label"
                                                label="Actividad acepta comentario"
                                                color="primary"
                                                v-model="activity.extra_attributes.comment_activity"
                                                hide-details="false"
                                            />
                                        </v-col>
                                        <v-col cols="12" v-if="activity.checklist_response.code == 'custom_option'">
                                            Respuestas personalizadas:
                                            <v-divider></v-divider>
                                            <div v-for="(option,index_option) in activity.custom_options" class="col col-12 d-flex" :key="index_option">
                                                <DefaultInput
                                                    clearable
                                                    v-model="activity.custom_options[index_option].name"
                                                    :label="`Opción ${index_option+1}`"
                                                    dense
                                                />
                                                <DefaultButton
                                                    icon="mdi-delete"
                                                    isIconButton
                                                    @click="deleteCustomOption(index_activity,index_option)"
                                                />
                                            </div>
                                            <span style="color: #5757EA;cursor: pointer;" @click="addCustomOption(index_activity)">Agregar una respuesta personalizada +</span>
                                        </v-col>
                                        <v-col cols="12" class="d-flex align-items-center">
                                            <span>
                                                Inteligencia artificial
                                            </span>
                                            <v-divider></v-divider>
                                        </v-col>
                                        <v-col cols="4" class="d-flex align-items-center">
                                            <v-checkbox
                                                class="my-0 mr-2 checkbox-label"
                                                label="Procesar imágenes con IA"
                                                color="primary"
                                                v-model="activity.extra_attributes.computational_vision"
                                                :disabled="!is_checklist_premium"
                                                hide-details="false"
                                            />
                                            <div v-if="!is_checklist_premium" class="ml-1 tag_beta_upgrade d-flex align-items-center">
                                                    <span class="d-flex beta_upgrade">
                                                        <img src="/img/premiun.svg"> Upgrade
                                                    </span>
                                                </div>
                                        </v-col>
                                        <v-col cols="4" v-if="activity.extra_attributes.computational_vision ">
                                            <DefaultSelect 
                                                v-model="activity.extra_attributes.type_computational_vision"
                                                :items="types_computational_vision" 
                                                dense 
                                                item-text="name"
                                                item-value="id"
                                                show-required 
                                                label="Selecciona una opción"
                                                :openUp="true"
                                            />  
                                        </v-col>
                                        <v-col cols="4"
                                            v-if="activity.extra_attributes.type_computational_vision && activity.extra_attributes.type_computational_vision != 'simil'"
                                        >
                                            <DefaultInput
                                                clearable
                                                v-model="activity.extra_attributes.type_computational_vision_value"
                                                :label="`${activity.extra_attributes.type_computational_vision == 'counter' ? 'Indica la cantidad a verificar' : 'Indicar el texto a verificar'}`"
                                                dense
                                            />
                                        </v-col>
                                    </v-row>
                                </v-expansion-panel-content>
                            </v-expansion-panel>
                        </v-expansion-panels>
                    </v-row>
                </v-form>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import DefaultRichText from "../../../components/globals/DefaultRichText";

export default {
    components:{DefaultRichText},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            checklist:null,
            resource: {
                name: null,
            },
            checklist_type_response:[],
            is_checklist_premium:false,
            area_id:null,
            tematica_id:null,
            rules: {
                required: this.getRules(['required']),
            },
            types_computational_vision:[
                {id:'simil',name:'Porcentaje de similitud'},
                {id:'text',name:'Verificar texto'},
                {id:'counter',name:'Contador de objetos'},
            ],
            activity:{
                id:'insert-1',
                position:1,
                activity:'Actividad Nueva',
                checklist_response:false,
                custom_options:[],
                extra_attributes:{
                    is_evaluable:false,
                    comment_activity:false,
                    photo_response:false,
                    computational_vision:false,
                    type_computational_vision:'',
                    type_computational_value:'',
                },
            }
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
            vue.$refs.activityForm.resetValidation()
        }
        ,
        async confirmModal() {
            let vue = this;
            vue.$http.post(`/entrenamiento/checklist/v2/${vue.checklist.id}/activity/save`,{
                activity:activity
            }).then(({data})=>{
                vue.showAlert('Se guardó la actividad correctamente.','success');
            })
            vue.$emit('onConfirm')
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData({area_id,tematica_id}){
            let vue = this;
            vue.area_id = area_id;
            vue.tematica_id = tematica_id;
        },
        async loadSelects() {
            let vue = this;
            const checklist_id = window.location.pathname.split('/')[4];
            const url = `/entrenamiento/checklist/v2/${checklist_id}/activity/form-selects`;
            await vue.$http.get(url).then(({data})=>{
                vue.checklist_type_response = data.data.checklist_type_response;
                vue.is_checklist_premium = data.data.is_checklist_premium;
                vue.checklist = {
                    id: checklist_id,
                    tematica_id:vue.tematica_id,
                    area_id:vue.area_id,
                    name:data.data.checklist_name
                }
                const checklist_response = vue.checklist_type_response.find(ctr => ctr.code == 'scale_evaluation');
                vue.activity.checklist_response = checklist_response;
            })
        },
    }
}
</script>
