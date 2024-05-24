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
                                placeholder="Escribe una nueva área"
                                show-required
                                dense
                                counter
                                append-icon="mdi-send"
                                @onEnter="addAreaToList"
                                @clickAppendIcon="addAreaToList"
                            />
                        </v-col>
                        
                        <v-col cols="12" class="py-0">
                            <DefaultInput
                                v-model="searchQuery"
                                label="Busca un área"
                                placeholder="Escribe un área en el listado"
                                show-required
                                dense
                                append-icon="mdi-magnify"
                            />
                        </v-col>
                        <v-col cols="12">
                            <DefaultSimpleSection title="Selecciona un listado de áreas" marginy="my-1 px-2 py-4" marginx="mx-0">
                                <template slot="content">
                                    <div style="height: 250px;overflow: scroll;scrollbar-width: thin;">
                                        <v-list>   
                                            <v-list-item
                                                v-for="area in filteredAreas"
                                                :key="area.id"
                                            >
                                                <v-list-item-content>
                                                    <v-list-item-title>
                                                        <DefaultCheckbox
                                                            v-model="area.selected"
                                                            :labelTrue="area.name"
                                                            :labelFalse="area.name"
                                                            class="mt-4"
                                                            hideDetails
                                                        />
                                                    </v-list-item-title>
                                                </v-list-item-content>
                                                <v-list-item-action>
                                                    <v-btn icon @click="openFormModal(modalDeleteOptions,area,null,'Eliminar área')">
                                                        <v-icon color="grey lighten-1">mdi-delete</v-icon>
                                                    </v-btn>
                                                </v-list-item-action>
                                            </v-list-item>
                                        </v-list>
                                    </div>
                                </template>
                            </DefaultSimpleSection>
                        </v-col>
                    </v-row>
                    
                </v-form>
            </template>
        </DefaultDialog>
        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="deleteModal"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />
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
            areas:[],
            area_id:null,
            rules:{
                name: this.getRules(['required', 'max:40']),
            },
            resource :{
                name:''
            },
            area_selected:[],
            modalDeleteOptions: {
                ref: 'AreaDeleteModal',
                open: false,
                base_endpoint: '/areas',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
            searchQuery:'', 
        };
    },
    computed: {
        filteredAreas() {
            return this.areas.filter(area => {
                return area.name.toLowerCase().includes(this.searchQuery.toLowerCase());
            });
        },
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
            const checklist_id = window.location.pathname.split('/')[4];
            const areas = vue.areas.filter( (a) => a.selected == true);
            vue.showLoader();
            await vue.$http.post(vue.options.base_endpoint+'save-area',{
                'areas': areas,
                'checklist_id' : checklist_id
            }).then(({data})=>{
            })
            vue.hideLoader()
            vue.$emit('onConfirm')
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this;
            vue.area_id = resource;
            
        },
        async loadSelects() {
           let vue = this;
           vue.$http.get(vue.options.base_endpoint+'areas').then(({data})=>{
                vue.areas = data.data.areas;
                vue.areas = vue.areas.map((a)=>{
                    a.selected = false
                    return a;
                });
           })
        },
        addAreaToList(){
            let vue = this;
            const _id = 'insert-'+vue.areas.length+ vue.generateRandomString(4);
            vue.areas.push({
                id:_id,
                selected: true,
                name: vue.resource.name,
            })
            vue.resource.name = '';
        },
        deleteModal(){
            
        }
    }
}
</script>
