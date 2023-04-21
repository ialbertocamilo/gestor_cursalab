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
                        <hr />

                        <!-- usuario -->
                        <div v-show="(options.resource.changes_data).length">
                            <b>Información del usuario</b>
                            <ul>
                                <li v-for="item_data in options.resource.changes_data">
                                    <span v-text="item_data.label"></span>
                                </li>
                            </ul>
                        </div>

                        <!-- criterios -->
                        <div v-show="(options.resource.changes_criterios).length">
                            <b>Criterios del usuario</b>
                            <ul>
                                <li v-for="item_criterio in options.resource.changes_criterios">
                                    <span v-text="item_criterio.name"></span>
                                </li>
                            </ul>
                        </div>

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
            resource: null
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {
            let vue = this
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