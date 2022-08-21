<template>
    <div class="w-100">
        <v-row justify="start">
            <v-col cols="4" v-for="criterion in criterion_list" :key="criterion.id">
                <div v-if="TypeOf(user.criterion_list[criterion.code]) !== 'undefined'">
                    <DefaultAutocomplete
                        :required="!!criterion.required"
                        :multiple="!!criterion.multiple"
                        placeholder="Elige una opción"
                        :label="criterion.name"
                        :items="criterion.values"
                        item-text="value_text"
                        v-model="user.criterion_list[criterion.code]"
                    />
                </div>
            </v-col>
        </v-row>
    </div>
</template>


<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        user: {
            type: Object,
            required: true
        },
        criterion_list: {
            type: Array | Object,
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
