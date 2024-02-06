<template>
    <div>
        <DefaultDialog :customTitle="true" :options="options"
            width="550px" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:card-title>
                <div class="mt-2">
                    <v-card-title class="py-0 d-flex justify-center">
                        Gestión de Etiquetas
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
                                placeholder="Escribe una nueva etiqueta"
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
                                label="Descripción de la etiqueta(opcional)"
                                placeholder="Descripción"
                                v-model="resource.description"
                                :rows="4"
                                counter
                                :rules="rules.description"
                            />
                        </v-col>
                        <v-col cols="12">
                            <p class="label-type-tag">Selecciona el tipo de etiqueta a crear</p>
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
                                label="Gestionar etiquetas realizadas previamente"
                                item-text="label"
                                item-value="value"
                                @onChange="getData"
                                dense
                            />
                            <div style="height: 250px;overflow: scroll;scrollbar-width: thin;">
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
                                            <v-btn icon @click="openFormModal(modalDeleteOptions,tag,null,'Eliminar etiqueta')">
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
            @onConfirm="deleteTag"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />
    </div>
</template>

<script>
import DefaultDeleteModal from "../../layouts/Default/DefaultDeleteModal";
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
                type:"competency",
            },
            rules:{
                name: this.getRules(['required', 'max:40']),
            },
            types:[
                {value:'competency',label:'Competencias'},
                {value:'hability',label:'Habilidades'},
            ],
            filter:{
                type:'competency'
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
            if(!vue.resource.type){
                vue.showAlert('Es necesario elegir un tipo de etiqueta.','warning');
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
                        vue.showAlert('Etiqueta creada correctamente')
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
            let vue = this;
            vue.getData();
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
                    }).catch((error) => {
                        if (error && error.errors) {
                            const errors = error.errors ? error.errors : error;
                            vue.show_http_errors(errors);
                        }
                    })
        },
        async deleteTag(tag){
            console.log(tag,'deleted');
            let vue = this;
            vue.tags = [];
            vue.closeFormModal(vue.modalDeleteOptions);
            vue.getData();
            vue.$emit('onDelete',tag)
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