<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        no-padding-card-text
    >
        <template v-slot:content>
            <v-form ref="ModalActivitySelectEncuestas">

                <v-row justify="space-around" class="mt-6" no-gutters>
                    <v-col cols="12">
                        <DefaultAutocomplete
                            dense
                            label="Requisito"
                            v-model="resource.encuesta_id"
                            :items="selects.encuestas"
                            :rules="rules.encuesta"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <img src="/svg/encuesta.svg" width="350" class="my-7">
                </v-row>
            </v-form>
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
        width: String
    },
    data() {
        return {
            resource: {
                id: null,
                encuesta_id: null,
            },
            selects: {
                encuestas: []
            },
            rules: {
                encuesta: this.getRules(['required']),
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            const validateForm = vue.validateForm('ModalActivitySelectEncuestas')
            if (validateForm) {
                const data = {
                    encuesta_id: vue.resource.encuesta_id
                }
                const url = `${vue.options.base_endpoint}/${vue.resource.id}/encuesta`
                vue.$http.post(url, data)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
            } else {
                this.hideLoader()
            }
        },
        resetValidation() {

        },
        loadData(resource) {
            let vue = this
            let url = `${vue.options.base_endpoint}/${resource.id}/encuesta`
            vue.$http.get(url).then(({data}) => {
                vue.resource.id = data.data.course.id
                vue.resource.encuesta_id = data.data.course.encuesta_id
                vue.selects.encuestas = data.data.encuestas
            });
        },
        loadSelects() {

        }
    }
}
</script>
