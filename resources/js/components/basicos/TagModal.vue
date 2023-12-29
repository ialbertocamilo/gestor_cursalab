<template>
    <div>
        <DefaultDialog :customTitle="true" :showCardActions="false" :noPaddingCardText="true" :options="options"
            width="350px" @onCancel="closeModal" @onConfirm="confirmModal" vCardClass=" p-0 overflow-hidden ">
            <template v-slot:card-title>
                <v-card-title class="py-0">
                    Gestión de Tags
                </v-card-title>
                <div>
                    <v-btn 
                        color="white"
                        fab
                        small
                        dark
                        @click="closeModal"
                    >
                        <v-icon color="black" small> mdi-close </v-icon>
                    </v-btn>
                </div>
            </template>
            <template v-slot:content>
                <div>
                        modal
                </div>
            </template>
            <template v-slot:card-actions>
                <div style="width: 100%">
                    <div style="background: #2E36CE;width: 100%; height: 45px; display: flex; justify-content: space-around;align-items: center;">
                        <div class="circle-content mdi mdi-bullhorn mdi-24px"></div>
                        <div class="circle-content mdi mdi-book-open-page-variant mdi-24px"></div>
                        <div class="circle-content mdi mdi-video mdi-24px"></div>
                        <div class="circle-content mdi mdi-chart-line mdi-24px"></div>
                    </div>
                    <div style="width: 100%;display: flex;justify-content: center;align-items: center;padding: 5px;">
                        <div style="width: 150px;height: 4px;background: #434D56;"></div>
                    </div>
                </div>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        async confirmModal() {
            vue.$emit('onConfirm')

        },
        resetSelects() {
            let vue = this
        },
        async loadData() {
            let vue = this
        },
        async loadSelects() {
            let vue = this;
        },
    }
}
</script>