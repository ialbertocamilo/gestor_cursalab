<template>
    <DefaultDialog :options="options"
                   :width="width"
                   no-padding-card-text
                   @onCancel="onClose"
                   @onConfirm="onConfirm">

        <template v-slot:content>
            <v-row justify="space-around">
                <v-col cols="4" class="d-flex justify-content-end" v-if="customFilter.length === 0">
                    <DefaultSelect
                        v-model="filter.type"
                        :items="types"
                        label="Tipo"
                        item-text="label"
                        item-value="value"
                        dense
                    />
                </v-col>
                <v-col cols="8" class="d-flex justify-content-end">
                    <DefaultInput
                        dense
                        v-model="filter.txt"
                        label="Buscar por nombre"
                        clearable
                        append-icon="mdi-text-search"
                        @onEnter="getData"
                    />
                </v-col>
            </v-row>
            <v-row justify="space-around">
                <v-col cols="12" class="d-flex justify-content-end py-0">
                    <div class="lista_media" style="width: 100%">
                        <div class="row">
                            <div class="col-12 d-flex justify-center" v-if="loading">
                                <h1>Cargando ....</h1>
                            </div>
                            <div class="col-12 d-flex justify-center" v-if="!loading && multimedias.length === 0">
                                <h1>No se encontraron resultados</h1>
                            </div>
                            <div class="col-sm-6 col-md-3 col-lg-2 mt-2"
                                 v-else
                                 v-for="item in multimedias"
                                 :key="item.id"
                                 @click="selectItem(item)"
                            >
                                <div class="med_box">
                                    <div class="img_box" :class="{'item-box-selected': item.selected}">
                                    <span class="tag"
                                          :style="{background: infoMedia(item).color}">{{ infoMedia(item).tipo }}</span>
                                        <v-img :src="infoMedia(item).preview" contain aspect-ratio="2"
                                               :lazy-src="`/img/loader.gif`"
                                        />
                                        <!--                                    <img :src="infoMedia(item).preview" class="img-fluid" >-->
                                    </div>
                                    <span>{{ item.title }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </v-col>
                <v-col cols="10" class="d-flex justify-content-center">
                    <v-pagination
                        :total-visible="7"
                        @input="cambiar_pagina"
                        v-model="paginate.page"
                        :length="paginate.total_paginas"
                    />
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>
</template>


<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        customFilter: {
            type: Array,
            default: []
        },
        width: String
    },
    data() {
        return {
            multimedias: [],
            loading: true,
            types: [
                {label: 'Todos', value: null},
                {label: 'Imagen', value: 'image'},
                {label: 'Video', value: 'video'},
                {label: 'Audio', value: 'audio'},
                {label: 'Excel', value: 'excel'},
                {label: 'PDF', value: 'pdf'},
                {label: 'Scorm', value: 'scorm'},
            ],
            paginate: {page: 1, total_paginas: 1},
            selected: null,
            filter: {
                txt: '',
                type: null
            },
        }
    },
    mounted() {
        let vue = this
        // vue.getData()
    },
    methods: {
        cambiar_pagina(page) {
            let vue = this;
            vue.getData(page);
        },
        getData() {
            let vue = this;
            vue.loading = true;
            let url = `/media/search?paginate=12&page=${vue.paginate.page}`
            url = vue.prepareUrl(url)

            axios.get(url)
                .then(({data}) => {
                    // console.log(data)
                    vue.multimedias = data.medias.data
                    vue.multimedias = vue.multimedias.map(item => {
                        let newItem = Object.assign({}, item, {
                            selected: false,
                        });
                        return newItem;
                    });
                    vue.paginate.total_paginas = data.medias.last_page;
                    vue.loading = false;

                })
                .catch((err) => {
                    console.log(err)
                    vue.loading = false;

                })
        },
        prepareUrl(url) {
            let vue = this
            if (vue.filter.txt) url += `&q=${vue.filter.txt}`
            if (vue.customFilter.length > 0) {
                vue.customFilter.forEach(el => url += `&tipo[]=${el}`)
            } else if (vue.filter.type) url += `&tipo[]=${vue.filter.type}`
            return url
        },
        selectItem(item) {
            let vue = this;
            vue.multimedias.forEach((el) => {
                el.selected = el.id === item.id
            })
            vue.selected = item
        },
        onClose() {
            let vue = this
            vue.$emit('onClose')
            vue.resetValues()
        },
        onConfirm() {
            let vue = this
            vue.$emit('onConfirm', vue.selected)
            vue.resetValues()
        },
        resetValues() {
            let vue = this
            vue.multimedias.forEach((el) => el.selected = false)
            vue.filter.txt = ''
            vue.filter.type = []
            vue.selected = null
        }
    }
}
</script>

<style lang="scss" scoped>

.item-box-selected {
    border: 2px solid;
}

.v-input__append-inner {
    margin: 0 !important;
    padding: 0 !important;
}
</style>
