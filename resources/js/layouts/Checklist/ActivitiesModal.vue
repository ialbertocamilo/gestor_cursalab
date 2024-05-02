<template>
    <div>
        <DefaultDialog 
            :options="options" 
            :width="width" 
            @onCancel="closeModal"
            @onConfirm="confirmModal"
        >
            <template v-slot:content>
                <div v-if="show_activities" style="position: relative;">
                    <v-row>
                        <v-col cols="12" class="d-flex align-items-center justify-space-between">
                            <div class="d-flex align-items-center">
                                <span style="font-size: 16px;"><b>Crea el listado de actividades para tu checklist</b></span>
                                <DefaultInfoTooltip
                                    class="ml-2"
                                    text="Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. 
                                    Si un usuario ya completó un checklist se mantiene su estado y porcentaje, 
                                    pero sí se actualiza para usuarios que aún no completan el checklist cuando el entrenador lo califique."
                                    top
                                />   
                            </div>
                            <!-- <span>Tipo: <b>Calificar usuario</b></span> -->
                            <div>
                                <DefaultButton
                                    rounded
                                    outlined
                                    label="Subida masiva de actividades"
                                    icon="mdi mdi-plus"
                                />
                                <DefaultButton
                                    icon="mdi-file-download"
                                    isIconButton
                                    @click="downloadTemplate()"
                                />
                            </div>
                        </v-col>
                    </v-row>
                    <v-row v-for="(activity,index_activity) in activities" :key="activity.id" class="elevation-1 my-4">
                        <v-col cols="12" class="d-flex justify-end">
                            <DefaultButton
                                icon="mdi-delete"
                                isIconButton
                                @click="removeActivity(index_activity)"
                            />
                        </v-col>
                        <v-col cols="1" class="d-flex align-center justify-content-center ">
                            <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                            </v-icon>
                        </v-col>
                        <v-row class="col-11">
                            <v-col cols="12">
                                <DefaultRichText
                                    clearable
                                    :height="150"
                                    v-model="activity.activity"
                                    label="Actividad de checklist"
                                    :ignoreHTMLinLengthCalculation="true"
                                    :key="`${showButtonIaGenerate}-editor`"
                                    ref="descriptionRichText1"
                                />
                            </v-col>
                            <v-expansion-panels flat class="custom-expansion-block">
                                <v-expansion-panel >
                                    <v-expansion-panel-header flat>
                                        <span style="color:#5458EA">
                                            <i class="pr-1 mdi mdi-cog"></i>
                                            Configuración
                                        </span>
                                        <v-chip small v-if="activity.checklist_response" color="#9A98F7" class="mx-1" style="max-width: min-content;color: white;">
                                            <i class="pr-1 mdi mdi-file-document-check"></i>
                                            <!-- <v-icon>{{ mdi-file-document-check  }}</v-icon>  -->
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
                                                    label="Aplicar visión computacional"
                                                    color="primary"
                                                    v-model="activity.extra_attributes.computational_vision"
                                                    :disabled="!is_checklist_premiun"
                                                    hide-details="false"
                                                />
                                                <div v-if="!is_checklist_premiun" class="ml-1 tag_beta_upgrade d-flex align-items-center">
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
                    </v-row>
                    <!-- style="position: sticky; bottom: 0px;z-index: 10;right: 0;" -->
                    <v-row>
                        <v-col cols="12" class="d-flex justify-end pb-0">
                            <DefaultButton
                                rounded
                                label="Agregar una actividad para tu checklist"
                                icon="mdi mdi-plus"
                                @click="addActivity()"
                            />
                        </v-col>
                    </v-row>
                </div>
                <v-row v-else>
                    <v-col cols="12" class="d-flex">
                        <div>
                            <span style="font-size: 16px;"><b>Crea el listado de actividades para tu checklist</b></span>
                            <DefaultInfoTooltip
                                class="ml-2"
                                text="Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. 
                                Si un usuario ya completó un checklist se mantiene su estado y porcentaje, 
                                pero sí se actualiza para usuarios que aún no completan el checklist cuando el entrenador lo califique."
                                top
                            />   
                        </div> 
                    </v-col>
                    <v-col cols="12" class="pb-1">
                        <span>¿Cómo deseas <b>empezar con tu checklist?</b></span>
                        <br>
                        <p class="p-0 m-0">Escoge una opción</p>
                    </v-col>
                    <v-col cols="4" v-for="(modality,index) in modalities" :key="index">
                        <DefaultCardAction 
                            @clickCard="verifyCardActivity"
                            :card_properties="modality" 
                        /> 
                    </v-col>
                </v-row>
            </template>
        </DefaultDialog>
        <ActivitiesIAModal 
            :options="modalActivitiesIAOptions"
            width="55vw"
            model_type="App\Models\Checklist"
            :model_id="null"
            :ref="modalActivitiesIAOptions.ref"
            @onCancel="closeSimpleModal(modalActivitiesIAOptions)"
            @onConfirm="closeFormModal(modalActivitiesIAOptions, dataTable, filters)"
            @activities="addActivities"
        />
    </div>
</template>

<script>
import DefaultCardAction from "../../components/globals/DefaultCardAction"
import DefaultRichText from "../../components/globals/DefaultRichText";
import ActivitiesIAModal from "./ActivitiesIAModal";

export default {
    components:{DefaultCardAction,DefaultRichText,ActivitiesIAModal},
    props: {
        options: {
            type: Object,
            required: true
        },
        
        width: String
    },
    data() {
        return {
            is_checklist_premiun:false,
            checklist:{

            },
            modalities: [
                {
                    icon:'mdi-file-edit',
                    color:'#F5539B',
                    icon_color:'white',
                    name:'Crear actividades',
                    code:'create_activities',
                    description:'Crea las actividades de tu checklist desde cero tu mismo',
                },
                {
                    icon:'mdi-file-upload',
                    color:"#5357E0",
                    icon_color:'white',
                    name:'Importar actividades',
                    code:'create_activities',
                    description:'Sube tus actividades con una plantilla de excel',
                },
                {
                    image:'/img/robot_jarvis.png',
                    icon_color:'white',
                    color:'#9B98FE',
                    name:'Crear con IA',
                    code:'create_ia_activities',
                    description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',
                }
            ],
            types_computational_vision:[
                {id:'simil',name:'Porcentaje de similitud'},
                {id:'text',name:'Verificar texto'},
                {id:'counter',name:'Contador de objetos'},
            ],
            show_activities:false,
            defaultActivities:[
                {
                    id:'insert-1',
                    position:1,
                    activity:'',
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
                },
            ],
            activities:[
                // {
                //     id:'insert-1',
                //     position:1,
                //     activity:'',
                //     checklist_response:false,
                //     custom_options:[],
                //     extra_attributes:{
                //         is_evaluable:false,
                //         comment_activity:false,
                //         photo_response:false,
                //         computational_vision:false,
                //         type_computational_vision:'',
                //         type_computational_value:'',
                //     },
                // },
            ],
            checklist_type_response:[],
            //Jarvis
            loading_description: false,
            limits_descriptions_generate_ia: {
                ia_descriptions_generated: 0,
                limit_descriptions_jarvis: 0
            },
            showButtonIaGenerate: true,
            modalActivitiesIAOptions:{
                ref: 'ActvitiesIAFormModal',
                open: false,
                persistent: true,
                base_endpoint: "/entrenamiento/checklist/v2",
                confirmLabel: "Continuar",
                resource: "checklist",
                title:'Selecciona los cursos para conseguir información'
            },
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
        },
        async confirmModal() {
            let vue = this;
            vue.showLoader();
            const url = `${vue.options.base_endpoint}/${vue.checklist.id}/activities/save`;
            await vue.$http.post(url,vue.activities).then(({data})=>{
                vue.hideLoader();
                vue.resetValidation();
                vue.$emit('onConfirm',{
                    checklist: data.data.checklist,
                    next_step: data.data.next_step
                });
            }).catch(()=>{
                vue.hideLoader();
            })
        },
        resetSelects() {
            let vue = this;
            vue.activities = [...vue.defaultActivities];
        },
        async loadData(checklist) {
            let vue = this
            vue.checklist = checklist;
            //Get activities by checklist
            const url = `${vue.options.base_endpoint}/${checklist.id}/activities`;
            vue.showLoader();
            await vue.$http.get(url).then(({data})=>{
                if(data.data.activities.length >0 ){
                    vue.$nextTick(() => {
                        vue.activities =  [...data.data.activities];
                        vue.show_activities = true;
                    });
                }else{
                    vue.$nextTick(() => {
                        vue.activities = [...vue.defaultActivities];
                        vue.show_activities = false;
                    });
                }
                vue.hideLoader();
            }).catch(()=>{
                vue.hideLoader();
            })
        },
        async loadSelects() {
            let vue = this;
            const url = `${vue.options.base_endpoint}/activity/form-selects`;
            await vue.$http.get(url).then(({data})=>{
                vue.checklist_type_response = data.data.checklist_type_response;
                vue.is_checklist_premiun = data.data.is_checklist_premiun;
            })
        },
        addCustomOption(index){
            let vue = this;
            vue.activities[index].custom_options.push({
                id:'insert'+(vue.activities[index].custom_options.length + 1),
                name:''
            })
        },
        deleteCustomOption(index_activity,index_option){
            let vue = this;
            vue.activities[index_activity].custom_options.splice(index_option,1);
        },
        removeActivity(index_activity){
            let vue = this;
            vue.activities.splice(index_activity,1);
        },
        addActivity(){
            let vue = this;
            vue.activities.push({
                id:'insert-'+(vue.activities.length+1),
                activity:'',
                position: vue.activities.length,
                checklist_response:false,
                custom_options:[],
                extra_attributes:{
                    is_evaluable:false,
                    photo_response:false,
                    comment_activity:false,
                    computational_vision:false,
                    type_computational_vision:'',
                    type_computational_value:'',
                },
            })
        },
        downloadTemplate(){
            let vue = this;
            vue.descargarExcelwithValuesInArray({
                headers:['TÍTULO','DESCRIPCIÓN','ACTIVIDAD 1','ACTIVIDAD 2','ACTIVIDAD 3','ACTIVIDAD 4'],
                values:[],
                comments:[],
                filename: "Plantilla Checklist",
                confirm:true
            });
        },
        verifyCardActivity(card){
            let vue = this;
            switch (card.code) {
                case 'create_ia_activities':
                    vue.modalActivitiesIAOptions.open = true;
                break;
                case 'create_activities':
                    vue.show_activities = true;
                break;
            }
            console.log(card);
        },
        addActivities(activities){
            vue.modalActivitiesIAOptions.open = false;
        }
    }
}
</script>
<style scoped>
.max-height-card {
    height: 100%;
    cursor: pointer;
}
.beta_upgrade{
    border: 1px solid #FFB700;
    border-radius: 8px;
    padding: 1px 4px;
}
</style>
