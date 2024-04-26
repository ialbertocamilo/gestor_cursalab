<template>
    <div>
        <DefaultDialog 
            :options="options" 
            :width="width" 
            :showCardActions="false" 
            @onCancel="closeModal"
            @onConfirm="confirmModal"
            :customTitle="true"
        >
            <template v-slot:card-title>
                <v-row>
                    <v-col cols="12" class="ml-6 mt-2">
                        <span style="font-size: 20px;"><b>¡Haz creado la base del checklist!</b></span>
                    </v-col>
                </v-row>
            </template>
            <template v-slot:content>
                <v-row>
                    <v-col cols="12" align="center">
                        <span style="font-size: 18px;">¿Qué deseas configurar ahora?</span>
                    </v-col>
                </v-row>
                <v-row style="margin: 0px 50px 0px 50px;">
                    <v-col cols="4">
                        <DefaultCardAction
                            :card_properties="activity_card"
                            @clickCard="clickCard"
                        />
                    </v-col>
                    <v-col cols="8" class="py-0">
                        <v-col cols="12">
                            <DefaultCardAction
                                :card_properties="segmentation_card"
                                horizontal
                                @clickCard="clickCard"
                            />
                        </v-col>
                        <v-col cols="12">
                            <DefaultCardAction
                                :card_properties="supervisor_card"
                                horizontal
                                @clickCard="clickCard"
                            />
                        </v-col>
                    </v-col>
                </v-row>
            </template>
        </DefaultDialog>

    </div>
</template>

<script>
import DefaultCardAction from "../../components/globals/DefaultCardAction"

export default {
    components:{DefaultCardAction},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            cheklist:{

            },
            next_step:'',
            activity_card:{
                    icon:'mdi-file-account',
                    icon_color:'black',
                    name:'Asignar actividades',
                    description:'<span>Procesos que desarrollaran los colaboradores dentro de su checklist</span>',
                    show_border:true
            },
            segmentation_card:{
                    icon:'mdi-clipboard-file',
                    icon_color:'black',
                    name:'Segmentar',
                    description:'<span>Selecciona criterios que filtraran a los colaboradores que realizaran el checklist</span>'
            },
            supervisor_card:{
                    icon:'mdi-account-details',
                    icon_color:'black',
                    name:'Asignar supervisores',
                    description:'<span>Selecciona a los colaboradores que supervisaran este checklist</span>'
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
        }
        ,
        async confirmModal() {
            let vue = this;
            vue.$emit('onConfirm',{
                checklist:vue.checklist
            })
        },
        resetSelects() {
            let vue = this
        },
        async loadData({checklist,next_step}) {
            let vue = this;
            vue.checklist = checklist;
            vue.next_step = next_step;
            const addText = '<br> <span class="text-center mt-4 color-default-primary">Siguiente proceso recomendado</span>';
            const activity_card_description = '<span>Procesos que desarrollaran los colaboradores dentro de su checklist</span>';
            const segmentation_card_description = '<span>Selecciona criterios que filtraran a los colaboradores que realizaran el checklist</span>';
            const supervisor_card_description = '<span>Selecciona a los colaboradores que supervisaran este checklist</span>';
            switch (next_step) {
                case 'create_activities':
                    vue.activity_card.show_border = true;
                    vue.activity_card.description = activity_card_description+ addText;
                    //clear text
                    vue.segmentation_card.description = segmentation_card_description;
                    vue.supervisor_card.description = supervisor_card_description;
                    vue.segmentation_card.show_border = false;
                    vue.supervisor_card.show_border = false;

                break;
                case 'segmentation_card':
                    vue.segmentation_card.show_border = true;
                    vue.segmentation_card.description = segmentation_card_description + addText;
                    //clear text
                    vue.activity_card.description = activity_card_description;
                    vue.supervisor_card.description = supervisor_card_description;
                    vue.activity_card.show_border = false;
                    vue.supervisor_card.show_border = false;
                break;
                case 'supervisor_card':
                    vue.supervisor_card.show_border = true;
                    vue.supervisor_card.description = supervisor_card_description + addText;
                    //clear text
                    vue.activity_card.description = activity_card_description;
                    vue.segmentation_card.description = segmentation_card_description;
                    vue.activity_card.show_border = false;
                    vue.segmentation_card.show_border = false;
                break;
                default:
                    vue.activity_card.show_border = false;
                    vue.segmentation_card.show_border = false;
                    vue.supervisor_card.show_border = false;
                    vue.next_step = ''; 
                break;
            }
        },
        async loadSelects() {

        },
        clickCard(){
            let vue = this;
            console.log('checklist',vue.checklist);
            vue.$emit('onConfirm',{
                checklist: vue.checklist,
                next_step : vue.next_step
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
