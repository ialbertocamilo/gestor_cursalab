<template>
    <div class="w-100">
        <v-row justify="start" v-if="only_req">
            <v-col cols="4" v-for="criterion in criterion_list" :key="criterion.id"
                        v-show="criterion.required">
                <div v-if="TypeOf(user.criterion_list[criterion.code]) !== 'undefined'">

                    <DefaultAutocomplete
                        :rules="criterion.required ? rules.required : []"
                        :multiple="!!criterion.multiple"
                        placeholder="Elige una opción"
                        :label="criterion.name+'*'"
                        :items="criterion.values"
                        item-text="value_text"
                        clearable
                        v-model="user.criterion_list[criterion.code]"
                    />

                </div>
            </v-col>
        </v-row>
        <v-row justify="start" v-else>
            <v-col cols="4" v-for="(criterion, index) in criterion_list" :key="criterion.id"
                        v-show="!criterion.required">
                <div v-if="TypeOf(user.criterion_list[criterion.code]) !== 'undefined'">
                    <div v-if="criterion.field_type != null && criterion.field_type.code == 'date'">
                        <DefaultInputDate
                            clearable
                            :referenceComponent="'modalDateFilter'+criterion.id"
                            :options="modalDate[index]"
                            v-model="user.criterion_list[criterion.code]"
                            :label="criterion.name"
                        />
                    </div>
                    <div v-else>
                        <DefaultAutocomplete
                            :rules="criterion.required ? rules.required : []"
                            :multiple="!!criterion.multiple"
                            placeholder="Elige una opción"
                            :label="criterion.name"
                            :items="criterion.values"
                            item-text="value_text"
                            clearable
                            v-model="user.criterion_list[criterion.code]"
                        />
                    </div>
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
        },
        only_req: {
            type: Boolean
        }
    },
    data() {
        return {
            rules: {
                required: this.getRules(['required'])
            },
            modalDate: [
                { open: false,}
            ],
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
    },
    created() {
        let vue = this
        for (let i = 0; i < 150; i++) {
            vue.modalDate.push({open:false});
        }
    }
}
</script>
