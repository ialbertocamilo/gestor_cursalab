<template>
    <v-hover v-slot="{ hover }">
        <v-card :elevation="hover ? 16 : 2" :class="{ 'on-hover': hover }" class="mx-auto max-height-card py-8"
            @click="doAction(card_properties.action)">
            <!-- <div v-if="card_properties.image">
                <div class="mr-4 d-flex justify-content-end align-items-center">
                    <img :src="card_properties.image">
                </div>
            </div> -->
            <div class="d-flex justify-content-center">
                <div class="d-flex justify-content-center"
                    :style="`width: 90px;height: 90px;border-radius: 50%;background-color: ${card_properties.color}`">
                    <v-icon v-if="card_properties.icon" color="white" large>{{ card_properties.icon }}</v-icon>
                    <img v-else :src="card_properties.image" style="height: 40px;width: 40px;margin: auto;">
                </div>
            </div>
            <v-card-title class="d-flex justify-content-center mb-3">
                {{ card_properties.name}}
            </v-card-title>
            <v-card-subtitle style="color:#2A3649" v-html="card_properties.description"></v-card-subtitle>
        </v-card>
    </v-hover>
</template>
<script>
export default {
    props: {
        card_properties:{
            type:Object,
            required:true,
        }
    },
    methods:{
        doAction(action){
            let vue = this;
            vue.$emit('clickCard',vue.card_properties);
        }
    }
}
</script>
<style scoped>
.max-height-card {
    height: 100%;
    cursor: pointer;
    background: white;
    color: black;
}
</style>