<template>
    <div>
        <DefaultDialog :options="options" :width="width" :height="height" :showCardActions="false" @onCancel="closeModal"
            @onConfirm="confirmModal">
            <template v-slot:content>
                <v-row>
                    <v-col cols="12" class="d-flex justify-space-between">
                        <span style="font-size: 16px;">Escoge o crea una escala de evaluación para este checklist</span>
                        <div style="color: #5458EA;">  
                            <i class="mdi mdi-account-multiple-check"></i>
                            <span>Max: {{evaluation_types_checked.length}}/{{ max_limit_select_evaluation_types }}</span>
                        </div>
                    </v-col>
                    <!-- <v-col cols="12">
                        <DefaultSimpleSection title="agrega una escala personalizada" marginy="my-1 px-2 py-4" marginx="mx-0">
                            <template slot="content">
                                <v-col cols="1">
                                    <v-menu v-model="evaluation_type.menu_picker"
                                            bottom
                                            :close-on-content-click="false"
                                            offset-y
                                            right
                                            nudge-bottom="10"
                                            min-width="auto">
                                            <template v-slot:activator="{ on, attrs }">
                                                <div class="container-evaluation-type"  v-bind="attrs" v-on="on" :style="`background:${evaluation_type.color};`">
                                                </div>
                                            </template>
                                        <v-card>
                                            <v-card-text class="pa-0">
                                                <v-color-picker v-model="evaluation_type.color" mode="hexa" flat />
                                            </v-card-text>
                                        </v-card>
                                    </v-menu>
                                </v-col>
                                <v-col cols="1" class="p-0 pb-2">
                                    <ButtonEmojiPicker
                                        v-model="evaluation_type.extra_attributes.emoji"
                                    ></ButtonEmojiPicker>
                                </v-col>
                                <v-col cols="5">
                                    <DefaultInput 
                                        dense
                                        v-model="evaluation_type.name"
                                        appendIcon="mdi mdi-pencil"
                                    />
                                </v-col>
                                <v-col cols="3">
                                    <DefaultInput 
                                        suffix="%"
                                        dense
                                        v-model="evaluation_type.extra_attributes.percent"
                                    />
                                </v-col>
                            </template>
                        </DefaultSimpleSection>
                    </v-col> -->
                    <v-col cols="12">
                        <DefaultSimpleSection title="Selecciona una escala previa o crea una nueva" marginy="my-1 px-2 py-4" marginx="mx-0">
                            <template slot="content">
                                <div v-for="(evaluation_type,index) in evaluation_types" :key="`key-${index}`">
                                    <div class="activities">
                                        <v-row class="align-items-center">
                                            <v-col cols="1" class="d-flex align-center justify-content-center mt-5 mr-2">
                                                <!-- @input="verifyLimits($event,index)" -->
                                                <DefaultCheckbox
                                                    v-model="evaluation_type.checked"
                                                    :disabled="
                                                        evaluation_types_checked.length >= max_limit_select_evaluation_types 
                                                        && !evaluation_type.checked
                                                    "
                                                    labelTrue=''
                                                    labelFalse=''
                                                />
                                            </v-col>
                                            <!-- COLOR EDITABLE -->
                                            <v-col cols="1">
                                                <v-menu 
                                                    v-model="evaluation_type.menu_picker"
                                                    bottom
                                                    :close-on-content-click="false"
                                                    offset-y
                                                    right
                                                    nudge-bottom="10"
                                                    min-width="auto"
                                                >
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <div class="container-evaluation-type"  v-bind="attrs" v-on="on" :style="`background:${evaluation_type.color};`">
                                                            </div>
                                                        </template>
                                                    <v-card>
                                                        <v-card-text class="pa-0">
                                                            <v-color-picker v-model="evaluation_type.color" mode="hexa" flat />
                                                        </v-card-text>
                                                    </v-card>
                                                </v-menu>
                                            </v-col>
                                            <v-col cols="1" class="p-0 pb-2">
                                                <ButtonEmojiPicker
                                                    v-model="evaluation_type.extra_attributes.emoji"
                                                ></ButtonEmojiPicker>
                                            </v-col>
                                            <v-col cols="5">
                                                <DefaultInput 
                                                    dense
                                                    v-model="evaluation_type.name"
                                                    appendIcon="mdi mdi-pencil"
                                                />
                                            </v-col>
                                            <v-col cols="2">
                                                <DefaultInput 
                                                    suffix="%"
                                                    dense
                                                    v-model="evaluation_type.extra_attributes.percent"
                                                />
                                            </v-col>
                                            <v-col cols="1" class="d-flex justify-content-center">
                                                <DefaultButton
                                                    label=""
                                                    icon="mdi-minus-circle"
                                                    isIconButton
                                                    @click="removeScaleEvaluation(index)"
                                                    :disabled="!evaluation_type.can_delete"
                                                />
                                            </v-col>
                                        </v-row>
                                    </div>
                                </div>
                                <div class="my-2">
                                    <DefaultButton
                                        label="Agregar escala"
                                        icon="mdi-plus"
                                        :outlined="true"
                                        @click="addScaleEvaluation()"
                                    />
                                </div>
                            </template>
                        </DefaultSimpleSection>
                    </v-col>
                </v-row>
            </template>
        </DefaultDialog>

    </div>
</template>

<script>
import ButtonEmojiPicker from '../../basicos/ButtonEmojiPicker';
import DefaultCheckbox from "../../globals/DefaultCheckBox.vue";

export default {
    components:{
        ButtonEmojiPicker,DefaultCheckbox
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String,
        height:String,
    },
    data() {
        return {
            evaluation_types:[],
            max_limit_select_evaluation_types:3,
            // evaluation_type:{
            //     menu_picker:false,
            //     name:'',
            //     color:'#00E396',
            //     extra_attributes:{
            //         emoji:'',
            //         percent:'',
            //     }
            // }
        };
    },
    computed: {
        evaluation_types_checked: function () {
            return this.evaluation_types.filter((e) => e.checked);
        }
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
            vue.$emit('onConfirm')
        },
        resetSelects() {
            let vue = this
        },
        async loadData(max_limit_select_evaluation_types) {
            let vue = this
            vue.max_limit_select_evaluation_types = max_limit_select_evaluation_types;
            return 0;
        },
        async loadSelects() {

        },
        addScaleEvaluation(){
            let vue = this;
            if( vue.evaluation_types.length < 5){
                vue.evaluation_types.push(
                    {
                        id:'insert-'+vue.evaluation_types.length+1,
                        name:'',
                        color:'#FF4560',
                        extra_attributes:{
                            percent:'0',
                            emoji:''
                        },
                        can_delete:true
                    },
                )
            }
        },
        removeScaleEvaluation(index){
            let vue = this;
            vue.evaluation_types.splice(index, 1);
        },
        verifyLimits(val,index){
            let vue = this;
            console.log(val,index,vue.evaluation_types_checked.length , vue.max_limit_select_evaluation_types);
            if(val && vue.evaluation_types_checked.length >= vue.max_limit_select_evaluation_types){
                console.log(vue.evaluation_types[index].checked);
                vue.evaluation_types[index].checked = false;
                console.log(vue.evaluation_types[index].checked);
            }
        }
    }
}
</script>
