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
                            <span>Tipo: <b>Calificar usuario</b></span>
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
                                    v-model="activity.description"
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
                                        <v-chip small v-if="activity.is_evaluable" color="#E57A9B" class="mx-1" style="max-width: min-content;color: white;">
                                            <i class="pr-1 mdi mdi-file-chart"></i>
                                            Será evaluable
                                        </v-chip>
                                        <v-chip small v-if="activity.photo_response" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
                                            <i class="pr-1 mdi mdi-image"></i>
                                            Se agregará foto
                                        </v-chip>
                                        <v-chip small v-if="activity.comment_activity" color="#67CB91" class="mx-1" style="max-width: min-content;color: white;">
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
                                                    show-required v-model="activity.checklist_response"
                                                    return-object
                                                    label="Tipo de respuesta"
                                                />
                                            </v-col>
                                            <v-col cols="2" class="d-flex align-items-center">
                                                <v-checkbox
                                                    class="my-0 mr-2 checkbox-label"
                                                    label="Evaluable"
                                                    color="primary"
                                                    v-model="activity.is_evaluable"
                                                    hide-details="false"
                                                />
                                            </v-col>
                                            <v-col cols="3" class="d-flex align-items-center">
                                                <v-checkbox
                                                    class="my-0 mr-2 checkbox-label"
                                                    label="Se usará foto como respuesta"
                                                    color="primary"
                                                    v-model="activity.photo_response"
                                                    hide-details="false"
                                                />
                                            </v-col>
                                            <v-col cols="3" class="d-flex align-items-center">
                                                <v-checkbox
                                                    class="my-0 mr-2 checkbox-label"
                                                    label="Actividad acepta comentario"
                                                    color="primary"
                                                    v-model="activity.comment_activity"
                                                    hide-details="false"
                                                />
                                            </v-col>
                                            <v-col cols="12" v-if="activity.checklist_response.code == 'custom_option'">
                                                Respuestas personalizadas:
                                                <v-divider></v-divider>
                                                <div v-for="(option,index_option) in activity.custom_options" class="col col-12 d-flex" :key="index_option">
                                                    <DefaultInput
                                                        clearable
                                                        v-model="activity.custom_options[index_option].value"
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
                                                    v-model="activity.computational_vision"
                                                    hide-details="false"
                                                />
                                            </v-col>
                                            <v-col cols="4" v-if="activity.computational_vision ">
                                                <DefaultSelect 
                                                    v-model="activity.type_computational_vision.type"
                                                    :items="types_computational_vision" 
                                                    dense 
                                                    item-text="name"
                                                    item-value="id"
                                                    show-required 
                                                    label="Selecciona una opción"
                                                />  
                                            </v-col>
                                            <v-col cols="4"
                                                v-if="activity.type_computational_vision.type && activity.type_computational_vision.type != 'simil'"
                                            >
                                                <DefaultInput
                                                    clearable
                                                    v-model="activity.type_computational_vision.value"
                                                    :label="`${activity.type_computational_vision.type == 'counter' ? 'Indica la cantidad a verificar' : 'Indicar el texto a verificar'}`"
                                                    dense
                                                />
                                            </v-col>
                                        </v-row>
                                    </v-expansion-panel-content>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </v-row>
                    </v-row>
                    <v-row style="position: sticky; bottom: 0px;z-index: 10;right: 0;">
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
                            @clickCard="show_activities = true"
                            :card_properties="modality" 
                        /> 
                    </v-col>
                </v-row>
            </template>
        </DefaultDialog>

    </div>
</template>

<script>
import DefaultCardAction from "../../components/globals/DefaultCardAction"
import DefaultRichText from "../../components/globals/DefaultRichText";

export default {
    components:{DefaultCardAction,DefaultRichText},
    props: {
        options: {
            type: Object,
            required: true
        },
        
        width: String
    },
    data() {
        return {
            modalities: [
                {
                    icon:'mdi-file-edit',
                    color:'#F5539B',
                    icon_color:'white',
                    name:'Crear actividades',
                    description:'Crea las actividades de tu checklist desde cero tu mismo',
                },
                {
                    icon:'mdi-file-upload',
                    color:"#5357E0",
                    icon_color:'white',
                    name:'Importar actividades',
                    description:'Sube tus actividades con una plantilla de excel',
                },
                {
                    image:'/img/robot_jarvis.png',
                    icon_color:'white',
                    color:'#9B98FE',
                    name:'Crear con IA',
                    description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',
                }
            ],
            types_computational_vision:[
                {id:'simil',name:'Porcentaje de similitud'},
                {id:'text',name:'Verificar texto'},
                {id:'counter',name:'Contador de objetos'},
            ],
            show_activities:false,
            activities:[
                {
                    id:1,
                    orden:1,
                    qualification_type:0,
                    description:'',
                    checklist_response:false,
                    photo_response:false,
                    computational_vision:false,
                    custom_options:[],
                    type_computational_vision:{
                        type:'',
                        value:''
                    },
                },
                {
                    id:2,
                    orden:2,
                    qualification_type:0,
                    description:'',
                    checklist_response:false,
                    photo_response:false,
                    computational_vision:false,
                    custom_options:[],
                    type_computational_vision:{
                        type:'',
                        value:''
                    },
                },
                {
                    id:3,
                    orden:3,
                    qualification_type:0,
                    description:'',
                    checklist_response:false,
                    photo_response:false,
                    computational_vision:false,
                    custom_options:[],
                    type_computational_vision:{
                        type:'',
                        value:''
                    },
                }
            ],
            resource:{
                qualification_type:0,
                description:'',
                checklist_response:false,
                photo_response:false,
                computational_vision:false,
                custom_options:[],
                type_computational_vision:{
                    type:'',
                    value:''
                },
            },
            checklist_actions :[
                {id:1,icon:'mdi mdi-home-city',code:'calificate_entity',name:'Calificar entidad',description:'Con este tipo de checklist se revisará a la entidad (tienda, oficina,etc)',color:'#57BFE3'},
                {id:2,icon:'mdi mdi-clipboard-account',code:'calificate_user',name:'Calificar al usuario',description:'El supervisor podrá evaluar a personalmente a cada uno de los usuarios asignados',color:'#CE98FE'},
                {id:3,icon:'mdi mdi-account-multiple-check',code:'autocalificate',name:'Autoevaluación',description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',color:'#547AE3'},
            ],
            // {id:2,name:'Selecciona'},
            checklist_type_response:[
                {id:1,code:'scale_evaluation',name:'Por escala de ev.'},
                {id:3,code:'custom_option',name:'Desplegable'},
            ],
            //Jarvis
            loading_description: false,
            limits_descriptions_generate_ia: {
                ia_descriptions_generated: 0,
                limit_descriptions_jarvis: 0
            },
            showButtonIaGenerate: true,
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
            let vue = this;
            vue.$emit('onConfirm', )
        },
        resetSelects() {
            let vue = this
        },
        async loadData(card_name) {
            let vue = this
            return 0;
        },
        async loadSelects() {

        },
        addCustomOption(index){
            let vue = this;
            vue.activities[index].custom_options.push({
                id:vue.resource.custom_options.length + 1,
                value:''
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
                id:'insert-'+vue.activities.length+1,
                orden:vue.activities.length+1,
                qualification_type:0,
                description:'',
                checklist_response:false,
                photo_response:false,
                computational_vision:false,
                custom_options:[],
                type_computational_vision:{
                    type:'',
                    value:''
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
        }
    }
}
</script>
<style scoped>
.max-height-card {
    height: 100%;
    cursor: pointer;
}

</style>
