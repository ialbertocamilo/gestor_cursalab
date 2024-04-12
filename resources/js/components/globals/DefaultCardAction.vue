<template>
    <v-hover v-slot="{ hover }">
        <v-card :elevation="hover ? 16 : 2" 
            :class="[
                'mx-auto', 'max-height-card', 'py-8', 
                { 'd-flex justify-content-center align-items-center': 
                horizontal, 'on-hover': hover ,'current-card':card_properties.show_border}
            ]
            "
            @click="doAction(card_properties.action)"
        >
            <div :class="['d-flex','justify-content-center',{'px-6':horizontal}]">
                <div :class="['d-flex','justify-content-center']"
                    :style="`
                        width: 90px;
                        height: 90px;
                        border-radius: 50%;
                        background-color: ${card_properties.color}; 
                        ${card_properties.icon_color == 'black' ? 'border:1px solid black' : ''}
                        ${horizontal ? 'ml-4' : ''}  
                    `
                    "
                >
                    <v-icon 
                        v-if="card_properties.icon" 
                        :color="card_properties.icon_color ? card_properties.icon_color : 'white'" 
                        large
                    >
                        {{ card_properties.icon }}
                    </v-icon>
                    <img v-else :src="card_properties.image" style="height: 40px;width: 40px;margin: auto;">
                </div>
            </div>
            <div>
                <v-card-title class="d-flex justify-content-center mb-3" style="font-weight: bold;">
                    {{ card_properties.name}}
                </v-card-title>
                <v-card-subtitle style="color:#2A3649" v-html="card_properties.description"></v-card-subtitle>
            </div>
        </v-card>
    </v-hover>
</template>
<script>
export default {
    props: {
        card_properties:{
            type:Object,
            required:true,
        },
        horizontal:{
            type:Boolean,
            default:false
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
.current-card{
    border: 2px solid #5458EA;
}
</style>