<template>
    <DefaultDialog
        width="25vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <span>

                        <div class="bx_header">
                            <div class="img"><img src="/img/modal_alert.png"></div>
                            <div class="cont" v-text="options.subTitle"></div>
                        </div>
                        <p class="mb-0 mt-4">
                            {{ options.contentText }}
                        </p>
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
        }
    },
    methods: {
        onCancel() {
            let vue = this;
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