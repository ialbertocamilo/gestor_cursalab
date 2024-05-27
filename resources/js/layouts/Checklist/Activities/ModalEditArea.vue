<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <v-form ref="areaEditForm" @submit.prevent="">
                    <v-row>
                        <v-col cols="12">
                            <v-radio-group v-model="type_edition" column>
                                <v-radio
                                v-for="item in items"
                                :key="item.value"
                                :label="item.label"
                                :value="item.value"
                                ></v-radio>
                            </v-radio-group>
                        </v-col>
                        <v-col cols="12" v-if="type_edition">
                            <DefaultInput
                                v-model="resource.name"
                                :label="`${type_edition=='edit_area' ? 'Editar área' : 'Nueva área'}`"
                                :placeholder="`${type_edition=='edit_area' ? 'Nombre de área' : 'Escribe una nueva área'}`"
                                :rules="rules.required"
                                show-required
                                dense
                                counter
                            />
                        </v-col>
                        <v-col cols="12" v-if="type_edition == 'edit_area'" class="pt-0">
                            Recuerda que este cambio afectará a todos tus checklist que tengan asignada esta área
                        </v-col>
                    </v-row>
                </v-form>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import DefaultCheckbox from "../../../components/globals/DefaultCheckBox";

export default {
    components:{DefaultCheckbox},
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
            items: [
                { label: 'Editar área para todos los checklist', value: 'edit_area' },
                { label: 'Crear una nueva área', value: 'create_area' },
            ],
            type_edition: null,
            rules:{
                required: this.getRules(['required']),
            }
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
            vue.resource.name ='';
            vue.type_edition=null;
            vue.$refs.areaEditForm.resetValidation();
        }
        ,
        async confirmModal() {
            let vue = this;
            const validateForm = vue.validateForm('areaEditForm')
            if(validateForm){
                const checklist_id = window.location.pathname.split('/')[4];

                await axios.post(`${vue.options.base_endpoint}area/edit`,{
                    type_edition:vue.type_edition,
                    area: vue.resource,
                    checklist_id:checklist_id
                }).then(()=>{
                    
                })
                vue.$emit('onConfirm')
            }
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
