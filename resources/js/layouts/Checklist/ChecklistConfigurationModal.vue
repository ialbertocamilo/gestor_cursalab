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
        modalities: [],
        width: String
    },
    data() {
        return {
           activity_card:{
                icon:'mdi-file-account',
                icon_color:'black',
                name:'Asignar actividades',
                description:'Procesos que desarrollaran los colaboradores dentro de su checklist',
                show_border:true
           },
           segmentation_card:{
                icon:'mdi-clipboard-file',
                icon_color:'black',
                name:'Segmentar',
                description:'Selecciona criterios que filtraran a los colaboradores que realizaran el checklist'
           },
           supervisor_card:{
                icon:'mdi-account-details',
                icon_color:'black',
                name:'Asignar supervisores',
                description:'Selecciona a los colaboradores que supervisaran este checklist'
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
        async confirmModal(modality_course) {
            let vue = this;
            vue.$emit('onConfirm', modality_course)
        },
        resetSelects() {
            let vue = this
        },
        async loadData(card_name) {
            let vue = this
            switch (card_name) {
                case 'activity_card':
                    vue.activity_card.show_border = true;
                    vue.activity_card.description = vue.activity_card.description + '<br> <span class="text-center">Siguiente proceso recomendado</span>';
                break;
                case 'segmentation_card':
                    
                break;
                case 'supervisor_card':
                    
                break;
            }
            // vue[card_name].show_border = true;
            // console.log(vue[card_name]);
            // vue[card_name].description = vue[card_name].description + '<br> <span class="text-center">Siguiente proceso recomendado</span>';
            // console.log(vue[card_name].description);
            return 0;
        },
        async loadSelects() {

        },
        clickCard(){
            let vue = this;
            vue.$emit('onConfirm',vue.card_properties);
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
