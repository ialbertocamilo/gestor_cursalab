<template>
    <div>
        <DefaultDialog 
            :options="options" 
            :width="width" 
            @onCancel="closeModal"
            @onConfirm="confirmModal"
        >
            <template v-slot:content>
                <v-row>
                    <v-col cols="12">
                        <span style="font-size: 18px;">Crea el listado de actividades para tu checklist</span>
                        <br>
                        <p>Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. 
                            Si un usuario ya completó un checklist se mantiene su estado y porcentaje, 
                            pero sí se actualiza para usuarios que aún no completan el checklist cuando el entrenador lo califique.</p>
                    </v-col>
                </v-row>
                <v-row v-if="show_activities">
                    <v-card style="height: 100%;overflow: auto;" class="bx_steps bx_step3">
                        <v-card-text>
                            <v-row>
                                <v-col cols="8">
                                    <span>Crea el listado de actividades para tu checklist</span>
                                    <br>
                                    <span>
                                        Tener en cuenta que al agregar o quitar actividades a un checklist completado por un usuario no tendrá efectos en su avance. 
                                        Si un usuario ya completó un checklist se mantiene su estado y porcentaje, 
                                        pero sí se actualiza para usuarios que aún no completan el checklist cuando el entrenador lo califique.
                                    </span>
                                </v-col>
                                <v-col cols="4">
                                    <DefaultSelect 
                                        :items="checklist_actions" 
                                        dense 
                                        item-text="name"
                                        return-object show-required v-model="resource.qualification_type"
                                        label="Calificar usuario"
                                        disabled
                                    />
                                    <br>
                                    <DefaultButton
                                        rounded
                                        outlined
                                        label="Subida masiva de actividades"
                                        icon="mdi mdi-plus"
                                    />
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="1" class="d-flex align-center justify-content-center ">
                                    <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                    </v-icon>
                                </v-col>
                                <v-row class="col-11">
                                    <v-col cols="12">
                                        <DefaultRichText
                                            clearable
                                            :height="150"
                                            v-model="resource.description"
                                            label="Actividad de checklist"
                                            :ignoreHTMLinLengthCalculation="true"
                                            :key="`${showButtonIaGenerate}-editor`"
                                            ref="descriptionRichText1"
                                        />
                                    </v-col>
                                    <v-col cols="4">
                                        <DefaultSelect 
                                            :items="checklist_type_response" 
                                            dense 
                                            item-text="name"
                                            show-required v-model="resource.checklist_response"
                                            label="Tipo de respuesta"
                                        />
                                    </v-col>
                                    <v-col cols="4" class="d-flex align-items-center">
                                        <v-checkbox
                                            class="my-0 mr-2 checkbox-label"
                                            label="Se usará foto como respuesta"
                                            color="primary"
                                            v-model="resource.photo_response"
                                            hide-details="false"
                                        />
                                    </v-col>
                                    <v-col cols="4" class="d-flex align-items-center">
                                        <v-checkbox
                                            class="my-0 mr-2 checkbox-label"
                                            label="Aplicar visión computacional"
                                            color="primary"
                                            v-model="resource.computational_vision"
                                            hide-details="false"
                                        />
                                    </v-col>
                                </v-row>
                            </v-row>
                        </v-card-text>
                    </v-card>
                </v-row>
                <v-row v-else>
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
            show_activities:false,
            resource:{
                qualification_type:0,
                description:'',
                checklist_response:false,
                photo_response:false,
                computational_vision:false,
            },
            checklist_actions :[
                {id:1,icon:'mdi mdi-home-city',code:'calificate_entity',name:'Calificar entidad',description:'Con este tipo de checklist se revisará a la entidad (tienda, oficina,etc)',color:'#57BFE3'},
                {id:2,icon:'mdi mdi-clipboard-account',code:'calificate_user',name:'Calificar al usuario',description:'El supervisor podrá evaluar a personalmente a cada uno de los usuarios asignados',color:'#CE98FE'},
                {id:3,icon:'mdi mdi-account-multiple-check',code:'autocalificate',name:'Autoevaluación',description:'Sube actividades para guiar a tus usuarios en sus primeros pasos.',color:'#547AE3'},
            ],
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
    }
}
</script>
<style scoped>
.max-height-card {
    height: 100%;
    cursor: pointer;
}

</style>
