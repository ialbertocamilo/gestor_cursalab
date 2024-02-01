<template>
    <!-- <v-card flat tile elevation="0" style="border-radius: 4px !important;">
        <v-card-title class="default-section-title d-flex align-items-center align-center" >
            <p class="default-section-title-p" v-text="title"/>
            <div v-if="comingSoon" class="coming-soon">
                <small>*Próximamente</small>
            </div>
            <DefaultInfoTooltip
                v-if="tooltip"
                class="ml-1"
                :text="tooltip"
                :right="right"
                :bottom="!right"
                color="white"
                small
            />
        </v-card-title>
        <v-card-text class="default-section-content py-3 px-5">
            <slot name="content"/>
        </v-card-text>
    </v-card> -->

    <div>   

    <v-row justify="space-around" align="start" align-content="center">
        <v-col cols="12" class="d-flex justify-content-between py-1"
            @click="expand.status = !expand.status"
            style="cursor: pointer">
            <span class="text-bold text-primary">{{ title }}</span>
            <v-icon class="text-primary">{{ expand.status ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
        </v-col>
        <v-col cols="12" class="py-0 separated">
            <DefaultDivider class="--divider_light primary"/>
        </v-col>
    </v-row>

    <v-row justify="space-around" align="start" align-content="center"   :class="[simple ? '' : 'px-3 pt-2', 'section-expanded']"  v-show="expand.status">
        <v-col cols="12" 
            :class="[simple ? '' : 'content-background', 'd-flex justify-content-center py-1']"
        >
            <v-expand-transition>
                <div style="width:100%">
                    <slot name="content"/>
                </div>
            </v-expand-transition>
        </v-col>
    </v-row>

    </div>

</template>
<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Título de la sección'
        },
        tooltip: String,
        right: {
            type: Boolean,
            default: true
        },
        comingSoon: false,
        expand: Object,
        simple:{
            type: Boolean,
            default: false
        }
    }
}
</script>
<style type="text/css">
    .section-expanded .content-background{
        /*background-color: #f5f5f575;*/
/*        background-color: #c8d6e11a;*/
        background-color: #f5f5f52e;
        border-radius: 8px;
        border: 1px solid #e5e5e5;
    }
</style>
