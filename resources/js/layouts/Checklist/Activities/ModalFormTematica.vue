<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <v-form ref="tagForm" @submit.prevent="">
                    <v-row>
                        <v-col cols="12">
                            <DefaultInput
                                label="Nueva Area"
                                v-model="resource.name"
                                placeholder="Escribe una temática"
                                show-required
                                dense
                                counter
                            />
                        </v-col>
                    </v-row>
                </v-form>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import DefaultDeleteModal from "../../Default/DefaultDeleteModal";
import DefaultCheckbox from "../../../components/globals/DefaultCheckBox";

export default {
    components:{DefaultDeleteModal,DefaultCheckbox},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            resource :{
                name:''
            },
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
            // vue.$refs.areaForm.resetValidation()
        }
        ,
        async confirmModal() {
            let vue = this;
            vue.showLoader()
            const checklist_id = window.location.pathname.split('/')[4];
            await vue.$http.post(`${vue.options.base_endpoint}${checklist_id}/tematica/edit`,{
                tematica:vue.resource
            }).then(()=>{
                vue.hideLoader()
                vue.$emit('onConfirm')
            })
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this;
           vue.resource = {...resource};
        },
        async loadSelects() {
           
        },
    }
}
</script>
