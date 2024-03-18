<template>
    <v-card flat tile elevation="0" style="border-radius: 4px !important;">

        <v-subheader class="mt-4 px-0" v-if="titleDefault">
            <strong>{{ title }}</strong>
            <DefaultInfoTooltip
                v-if="tooltipInfoText"
                class="ml-2"
                bottom
                :text="tooltipInfoText" />
        </v-subheader>

        <!-- <DefaultInfoTooltip
            v-if="tooltip"
            class="ml-1"
            :text="tooltip"
            right
            color="white"
        /> -->
        <slot name="title"/>

        <v-divider class="mt-0"/>

        <slot name="content"/>
        
    </v-card>
</template>
<script>
export default {
    props: {
        title: {
            type: String,
            default: 'Título de la sección'
        },
        tooltip: String,
        titleDefault: {
            type: Boolean,
            default: true,
        },
        tooltipInfoText:{
            type:String,
            default:''
        }
    }
}
</script>
