<template>
    <div>
        <DefaultDialog :customTitle="true" :options="options"
            width="550px" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:card-title>
                <div class="mt-2">
                    <v-card-title class="py-0 d-flex justify-center">
                        Gestión de Tags
                    </v-card-title>
                    <div style="position: absolute;right: 8px !important;top: 0 !important;">
                        <v-icon color="black"  @click="closeModal"> mdi-close </v-icon>
                    </div>
                </div>
            </template>
            <template v-slot:content>
                <v-form ref="tagForm">
                    <v-row>
                        <v-col cols="12">
                            <DefaultInput
                                label="Nuevo Tag"
                                placeholder="Escribe un nuevo Tag"
                                v-model="resource.name"
                                :rules="rules.name"
                                show-required
                                dense
                                counter
                            />
                        </v-col>
                        <v-col cols="12">
                            <DefaultTextArea
                                dense
                                label="Descripción del tag(opcional)"
                                placeholder="Descripción"
                                v-model="resource.description"
                                :rows="4"
                                counter
                                :rules="rules.description"
                            />
                        </v-col>
                        <v-col cols="12">
                            <p class="label-type-tag">Selecciona el tipo de tag a crear</p>
                            <v-radio-group
                                v-model="resource.type"
                                row
                            >
                                <v-radio
                                    value="competency"
                                >
                                    <template v-slot:label>
                                        <div class="pt-1">Competencia</div>
                                    </template>
                                </v-radio>
                                <v-radio
                                    value="hability"
                                >
                                    <template v-slot:label>
                                        <div class="pt-1">Habilidad</div>
                                    </template>
                                </v-radio>
                            </v-radio-group>
                        </v-col>
                        <v-col cols="12">
                            <DefaultSelect
                                v-model="filter.type"
                                :items="types"
                                label="Tipo"
                                item-text="label"
                                item-value="value"
                                @onChange="getData"
                                dense
                            />
                            <div style="min-height: 50px;">
                                <v-list
                                    two-line
                                >   
                                    <v-list-item
                                        v-for="tag in tags"
                                        :key="tag.id"
                                    >
                                        <v-list-item-content>
                                            <v-list-item-title>{{ tag.name }}</v-list-item-title>
                                            <v-tooltip bottom>
                                                <template v-slot:activator="{ on, attrs }">
                                                    <v-list-item-subtitle
                                                        v-bind="attrs"
                                                        v-on="on"
                                                    >{{ tag.description }}</v-list-item-subtitle>
                                                </template>
                                                <span>{{tag.description}}</span>
                                            </v-tooltip>
                                        </v-list-item-content>

                                        <v-list-item-action>
                                            <v-btn icon @click="openFormModal(modalDeleteOptions,tag,null,'Eliminar tag')">
                                                <v-icon color="grey lighten-1">mdi-delete</v-icon>
                                            </v-btn>
                                        </v-list-item-action>
                                    </v-list-item>
                                </v-list>
                            </div>
                        </v-col>
                    </v-row>
                </v-form>
            </template>
        </DefaultDialog>
        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions)"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />
    </div>
</template>

<script>
import DefaultDeleteModal from "../../layouts/Default/DefaultDeleteModal.vue";
export default {
    components: {
        DefaultDeleteModal,
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            resource:{
                name:'',
                description:'',
                type:'competency',
            },
            rules:{
                name: this.getRules(['required', 'max:40']),
            },
            types:[
                {value:'competency',label:'Competencias'},
                {value:'hability',label:'Habilidades'},
            ],
            filter:{
                type:''
            },
            tags:[
            ],
            modalDeleteOptions: {
                ref: 'TagDeleteModal',
                open: false,
                base_endpoint: '/tags',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '',
            },
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this;
            vue.resource.name = '';
            vue.resource.description = '';
            vue.resource.type = '';
        },
        async confirmModal() {
            let vue = this;
            if(vue.resource.description.length >120){
                vue.showAlert('La descripción debe tener máximo 120 carácteres.','warning');
                return;
            }
            const validateForm = vue.validateForm('tagForm')
            let url = '/tags/store';
            if (validateForm) {
                const formData = vue.resource
                await vue.$http
                    .post(url, formData)
                    .then(({ data }) => {
                        vue.resetValidation()
                        const tag = data.data.tag;
                        console.log(tag,data);
                        vue.showAlert('Tag creado correctamente')
                        vue.$emit('onConfirm',tag)
                    }).catch((error) => {
                        if (error && error.errors) {
                            const errors = error.errors ? error.errors : error;
                            vue.show_http_errors(errors);
                        }
                    })
            }
        },
        resetSelects() {
            let vue = this
        },
        async loadData() {
            let vue = this
        },
        async loadSelects() {
            let vue = this;
        },
        async getData(){
            let vue = this;
            const url = '/tags/search-by-type';
            vue.tags = [];
            await vue.$http
                    .post(url, vue.filter)
                    .then(({ data }) => {
                        vue.tags = data.data.tags;
                        console.log(tag,data);
                    }).catch((error) => {
                        if (error && error.errors) {
                            const errors = error.errors ? error.errors : error;
                            vue.show_http_errors(errors);
                        }
                    })
        },
        async deleteTag(tag){
            const url = `/tags/${tag.id}/delete`;
            vue.tags = [];
            await vue.$http
                    .delete(url)
                    .then(({ data }) => {
                        vue.showAlert('Tag eliminado correctamente')
                    }).catch((error) => {
                        if (error && error.errors) {
                            const errors = error.errors ? error.errors : error;
                            vue.show_http_errors(errors);
                        }
                    })
        }
    }
}
</script>
<style scoped>
.label-type-tag{
    color: #666;
    font-family: Nunito;
    font-size: 16px;
    font-style: normal;
    font-weight: 400;
    line-height: 20px; 
    letter-spacing: 0.1px; 
}
</style>