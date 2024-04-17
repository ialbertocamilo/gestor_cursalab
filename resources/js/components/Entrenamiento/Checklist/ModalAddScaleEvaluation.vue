<template>
    <div>
        <DefaultDialog :options="options" :width="width" :showCardActions="false" @onCancel="closeModal"
            @onConfirm="confirmModal">
            <template v-slot:content>
                <v-row>
                    <v-col cols="12">
                        <span>Escoge o crea una escala de evaluación para este checklist</span>
                        <div>
                            <i class="mdi mdi-account-multiple-check"></i>
                            <span>Max: {{evaluation_types_length}}/5</span>
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
                                        <v-row class="align-items-center px-2">
                                            <v-col cols="1" class="d-flex align-center justify-content-center ">
                                                <v-icon class="ml-0 mr-2 icon_size">mdi-drag-vertical
                                                </v-icon>
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
                                            <v-col cols="3">
                                                <DefaultInput 
                                                    suffix="%"
                                                    dense
                                                    v-model="evaluation_type.extra_attributes.percent"
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
                                        :disabled="resource.evaluation_types.length >= 5"
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

export default {
    components:{ButtonEmojiPicker},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            evaluation_types:[],
            evaluation_types_length:{},
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
        async loadData(evaluation_types_length) {
            let vue = this
            vue.evaluation_types_length = evaluation_types_length;
            return 0;
        },
        async loadSelects() {

        },
    }
}
</script>
