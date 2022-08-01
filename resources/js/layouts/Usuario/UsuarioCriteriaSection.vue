<template>
    <!--    <v-card class="usuario-sΩection-matricula">-->

    <div class="usuario-section-matricula">
        <v-row justify="start">
            <v-col cols="4" v-for="criterion in criteria_list" :key="criterion.id">
                <DefaultAutocomplete
                    placeholder="Elige una opción"
                    :label="criterion.name"
                    :items="criterion.values"
                    item-text="value_text"
                    v-model="usuario.criteria_list[criterion.code]"
                />
            </v-col>
        </v-row>
    </div>
    <!--    </v-card>-->

</template>


<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        usuario: {
            type: Object,
            required: true
        },
        criteria_list: {
            type: Array,
            required: true
        }

    },
    data() {
        return {
            rules: {
                carrera: this.getRules(['required'])
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        confirmModal() {
            let vue = this
            vue.$emit('onConfirm')
        },
        loadData() {

        },
        loadSelects() {

        },
    }
}
</script>
<style>
.usuario-section-matricula {
    width: 100%;
}
</style>
