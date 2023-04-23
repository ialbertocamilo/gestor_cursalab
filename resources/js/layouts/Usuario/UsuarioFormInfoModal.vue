<template>
    <DefaultDialog
        width="30vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <span class="text-start">
                        <!-- {{ options.resource }} -->

                        <div class="bx_header">
                            <div class="img"><img src="/img/modal_alert.png"></div>
                            <div class="cont" v-text="options.subTitle"></div>
                        </div>
                        <hr />

                        <ul>
                            <li>
                                Este cambio podría afectar su segmentación, permitiéndole ver nuevos cursos o dejar de ver otros.
                            </li>
                        </ul>

                        <v-row justify="space-around" align="start" align-content="center">
                            <v-col cols="12" class="d-flex justify-content-between pb-0"
                                @click="showCriteria = !showCriteria"
                                style="cursor: pointer">
                                <strong class="cg">Ver Criterios</strong>
                                <v-icon v-text="showCriteria ? 'mdi-chevron-up' : 'mdi-chevron-down'"/>
                            </v-col>
                            <v-col cols="12" class="py-0 separated">
                                <DefaultDivider/>
                            </v-col>
                        </v-row>

                        <v-row justify="space-around" align="start" align-content="center">
                            <v-col cols="12" class="d-flex pt-2">
                                <v-expand-transition>
                                    <div v-show="showCriteria">
                                        <!-- usuario -->
                                        <div v-show="(options.resource.changes_data).length">
                                            <!-- <b>Información del usuario</b> -->
                                            <ul class="mb-0">
                                                <li v-for="item_data in options.resource.changes_data">
                                                    <span v-text="item_data.label"></span>
                                                </li>
                                            </ul>
                                        </div>

                                        <!-- criterios -->
                                        <div v-show="(options.resource.changes_criterios).length">
                                            <!-- <b>Criterios del usuario</b> -->
                                            <ul class="mb-0">
                                                <li v-for="item_criterio in options.resource.changes_criterios">
                                                    <span v-text="item_criterio.name"></span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </v-expand-transition>
                            </v-col>
                        </v-row>
                    </span>
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
        }
    },
    data() {
        return {
            resource: null,
            showCriteria: false,
        }
    },
    methods: {
        onCancel() {
            let vue = this;
            vue.showCriteria = false;
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {
            let vue = this
            vue.showCriteria = false;
            vue.$emit('onConfirm', true);
        },
        loadData(resource) {
        },
        loadSelects() {

        }
    }
}
</script>
<style>
    .bx_header .cont {
        color: #2A3649;
        font-size: 20px;
        font-family: "Nunito", sans-serif;
        font-weight: 700;
        margin-left: 29px;
        text-align: left;
        line-height: 25px;
    }
</style>