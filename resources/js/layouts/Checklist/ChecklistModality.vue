<template>
    <div>
        <DefaultDialog :options="options" :width="width" :showCardActions="false" @onCancel="closeModal"
            @onConfirm="confirmModal">
            <template v-slot:content>
                <v-row>
                    <v-col cols="12" class="pb-1">
                        <span>¿Cómo deseas <b>calificar tu checklist?</b></span>
                        <br>
                        <p class="p-0 m-0">Escoge una opción</p>
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="4" v-for="(modality,index) in modalities" :key="index">
                        <DefaultCardAction 
                            @clickCard="confirmModal"
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
        async confirmModal(modality_checklist) {
            let vue = this;
            vue.$emit('onConfirm', modality_checklist)
        },
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
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

.tag-premium {
    display: inline-flex;
    padding: 2px 12px;
    justify-content: center;
    align-items: center;
    gap: 3px;
    flex-shrink: 0;
    border-radius: 23px;
    border: 1px solid hsl(43, 100%, 50%);
    color: #5458EA;
    font-family: Nunito;
    font-size: 12px;
    font-style: normal;
    font-weight: 400;
    line-height: 20px;
    /* 200% */
    letter-spacing: 0.1px;
}
</style>
