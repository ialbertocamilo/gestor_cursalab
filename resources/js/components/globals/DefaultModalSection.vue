<template>
    <v-card flat tile elevation="0" style="border-radius: 4px !important;">
        <v-card-title class="default-section-title d-flex align-items-center align-center" >
            <p class="default-section-title-p" v-text="title"/>
            <div v-if="comingSoon" class="coming-soon">
                <small>*Próximamente</small>
            </div>
            <DefaultInfoTooltip
                v-if="tooltip"
                class="ml-1"
                :text="tooltip"
                right
                color="white"
            />
        </v-card-title>
        <v-card-text class="default-section-content py-3 px-5">
            <slot name="content"/>
        </v-card-text>
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
        comingSoon: false,
    }
}
</script>
