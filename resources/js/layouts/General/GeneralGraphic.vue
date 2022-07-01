<template>
    <div>
        <DefaultOverlay
            absolute
            v-model="graphic_data.overlay"/>
        <apexcharts
            height="450"
            :ref="graphic_data.ref"
            :options="graphic_data.chartOptions"
            :series="graphic_data.series"/>
        <div class="d-flex justify-content-end px-3 align-items-center"
             v-if="graphic_data.last_update">
            <v-icon small class="mx-1" @click="refreshCache" v-text="'mdi-cached'"/>
            <p class="mb-0 pt-1" style="font-size: .75rem !important;">
                Última actualización {{ graphic_data.last_update.text }}
            </p>
        </div>
    </div>
</template>
<script>
export default {
    props: {
        graphic_data: {
            type: Object,
            required: true
        }
    },
    methods: {
        refreshCache() {
            let vue = this
            vue.$emit('refreshCache')
        }
    }
}
</script>
