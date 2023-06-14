<template>
    <section class="py-0">
        <v-row v-show="loading" justify="center">
            <v-col cols="12" style="padding-right: 2.2rem; padding-left: 2.2rem">
                <v-progress-linear
                    indeterminate
                    color="primary"
                />
            </v-col>
        </v-row>
        <v-row justify="space-around" class="">
            <v-col cols="12" class="d-flex justify-content-end py-0 px-8">
                <div class="lista_media py-0" style="width: 100%">
                    <div class="row">
                        <div class="col-12 d-flex justify-center" v-if="!loading && data.length === 0">
                            <h5>No se encontraron resultados</h5>
                        </div>
                        <div class="col-sm-6 col-md-3 col-lg-2 mt-2"
                             v-else
                             v-for="item in data"
                             :key="item.id"
                             @click="detalles(item)"
                        >
                            <div class="med_box">
                                <div class="img_box" :class="{'item-box-selected': item.selected}">
                                    <v-img :src="item.image" contain aspect-ratio="2"
                                           :lazy-src="`/img/loader.gif`"
                                    />
                                </div>
                                <span class="med-box-title">{{ item.title }}</span><br>
                                <span class="med-box-tag">{{ infoMedia(item).tipo || '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </v-col>
        </v-row>
    </section>
</template>
<script>
export default {
    props: {
        data: {
            required: true
        },
        loading: {
            required: true
        },
        showSelect: {
            type: Boolean,
            default: false
        },
        filters: {
            type: Object,
        },
    },
    data() {
        return {}
    },
    mounted() {
        // this.getData()
    },
    methods: {
        detalles(rowData) {
            let vue = this
            vue.$emit('detalles', rowData)
        },
    },

}
</script>

<style lang="scss">
@import "resources/sass/variables";

.med-box-tag {
    // border-radius: 4px;
    // padding: .3rem 2rem !important;
    // background-color: $primary-default-color;
    // font-size: .9rem !important;
    // color: white;

    border-radius: 1em;
    padding: 0.3rem 1rem !important;
    background-color: #b0b1c7;
    color: white;
}

.med-box-title {
    white-space: nowrap;
    width: -webkit-fill-available;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
