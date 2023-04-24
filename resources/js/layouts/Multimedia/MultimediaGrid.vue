<template>
    <section style="padding-top: 0 !important;">
        <v-row v-show="loading" justify="center">
            <v-col cols="12" style="padding-right: 2.2rem; padding-left: 2.2rem">
                <v-progress-linear
                    indeterminate
                    color="primary"
                />
            </v-col>
        </v-row>
        <v-row justify="space-around" class="mt-1">
            <v-col cols="12" class="d-flex justify-content-end py-0 px-8">
                <div class="lista_media" style="width: 100%">
                    <div class="row">
                        <div class="col-12 d-flex justify-center" v-if="!loading && data.length === 0">
                            <h4>No se encontraron resultados</h4>
                        </div>
                        <div class="col-sm-6 col-md-3 col-lg-2 mt-2"
                             v-else
                             v-for="item in data"
                             :key="item.id"
                             @click="detalles(item)"
                        >
                            <div class="med_box">
                                <!-- <div class="img_box" :class="{'item-box-selected': item.selected}">
                                    <v-img :src="item.image" contain aspect-ratio="2"
                                           :lazy-src="`/img/loader.gif`"
                                    />
                                </div>
                                <span class="med-box-title">{{ item.title }}</span><br>
                                <span class="med-box-tag">{{ infoMedia(item).tipo || '-' }}</span> -->

                                <div class="row">
                                    <div class="logo-wrapper col-12 pt-3 pb-3">

                                        <!-- Logo -->

                                        <img v-bind:src="workspace.logo"
                                             class="logo"
                                             alt="">

                                        <!-- Edit button -->

                                        <div @click="editWorkspace(workspace.id)"
                                             v-if="isAdminInWorkspace(workspace.id)"
                                             class="edit-button">
                                            <v-icon color="white" size="16px">
                                                mdi-square-edit-outline
                                            </v-icon>
                                        </div>
                                    </div>
                                    <div class="col-12 text-center bg-white">
                                        <span>{{ workspace.name }}</span>
                                    </div>
                                    <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                        <v-icon class="icon" size="30px">mdi-sitemap</v-icon>
                                        <div class="text-left ml-2">
                                            <span class="number">
                                                {{ workspace.modules_count }}
                                            </span><br>
                                            <span class="label">módulos</span>
                                        </div>
                                    </div>
                                    <div class="col-6 stats pt-3 d-flex justify-content-center align-items-center">
                                        <v-icon class="icon" size="30px">mdi-account-group</v-icon>
                                        <div class="text-left ml-2">
                                            <span class="number">
                                                {{ workspace.users_count }}
                                            </span><br>
                                            <span class="label">usuarios</span>
                                        </div>
                                    </div>
                                    <div class="col-12 pt-3 pb-3 button-wrapper d-flex justify-content-center">
                                        <button @click="setActiveWorkspace(workspace.id, true)"
                                                class="btn">
                                            Ingresar
                                        </button>
                                    </div>
                                </div>
                                
                                
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
    border-radius: 4px;
    padding: .3rem 2rem !important;
    background-color: $primary-default-color;
    font-size: .9rem !important;
    color: white;
}

.med-box-title {
    white-space: nowrap;
    width: -webkit-fill-available;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>
