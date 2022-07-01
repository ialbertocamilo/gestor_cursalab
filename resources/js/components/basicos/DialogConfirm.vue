<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closeModal"
    >
        <v-card>
            <v-card-title class="default-dialog-title">
                {{ title }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closeModal">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
            <v-card-text class="py-8 text-center">
                <div v-html="subtitle"></div>
            </v-card-text>
            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                <DefaultModalActionButton
                    @cancel="closeModal"
                    @confirm="confirmModal"/>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>
export default {
    props: ["value", "width", "title", "subtitle", "txt_btn_confirm", "txt_btn_cancel"],
    data() {
        return {
            dialog: false,
        };
    },
    methods: {
        confirm() {
            let vue = this;
            vue.$emit("onConfirm");
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },

        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        }
    },
};
</script>

<style>
.txt-white-bold {
    color: white !important;
    font-weight: bold !important;
}
</style>
