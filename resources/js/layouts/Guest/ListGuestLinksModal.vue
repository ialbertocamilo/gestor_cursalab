<template>
    <div>
        <DefaultDialog
            :options="options"
            :width="width"
            @onCancel="closeModal"
            @onConfirm="confirmModal"
            :showCardActions="options.showCardActions"
        >
            <template v-slot:content>
                    <v-row>
                        <v-col cols="12" style="display: grid;grid-template-columns:auto 1fr;">
                            <span class="d-flex align-items-center" v-text="app_url"></span>
                            <input ref="url_share" type="text" v-model="url.code" class="p-2 mr-4 rounded" style="background: #F8F8FB;">
                        </v-col>
                        <v-col cols="12" style="display: grid; grid-template-columns: auto auto 1fr;">
                            <div class="flex flex-column">
                                <span>Tiempo</span>
                                <div>
                                    <input min="1" max="99" :disabled="!url.type_of_time" ref="time" type="number" v-model="url.number_time" class="p-2 mr-4 rounded text-center" style="background: #F8F8FB;width: 80px;">
                                </div>
                            </div>
                            <div class="flex flex-column">
                                <span>Condición</span>
                                <DefaultSelect
                                    :attach="false"
                                    v-model="url.type_of_time"
                                    @onChange="changeTypeOfTime"
                                    :items="types_of_time"
                                    item-text="label"
                                    item-value="value"
                                    :clearable="false"
                                    dense
                                />
                            </div>
                            <div class="flex align-self-end">
                                <DefaultButton
                                    style=" width: 100%;max-width: 100% !important;"
                                    label="Añadir"
                                    :disabled="!url.code ||(url.type_of_time && (url.number_time<1 || url.number_time>99))"
                                    @click="add_url()"
                                />
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <DefaultSimpleTable>
                                <template slot="content">
                                    <thead>
                                        <tr>
                                            <th>URL</th>
                                            <th>Expiración</th>
                                            <th>Opciones</th>
                                        </tr>
                                    </thead>
                                    <tr
                                        v-for="(item, index) in urls_generated"
                                        :key="index"
                                    >
                                        <td :id="`td-url-${index}`">{{ app_url+item.url }}</td>
                                        <td>{{ (item.expiration_date) ? item.expiration_date : 'sin expiración' }}</td>
                                        <td>
                                            <div class="d-flex justify-center flex-row my-2">
                                                <div class="text-center mx-1" style="cursor: pointer;" @click="copy_content(`td-url-${index}`,app_url+item.url)">
                                                    <v-icon style="color:#5458EA" >mdi-content-copy</v-icon>
                                                    <br> <span class="table-default-icon-title">Copiar</span>
                                                </div>
                                                <div class="text-center mx-1" style="cursor: pointer;" @click="modalDeleteOptions.resource_id=item.id;modalDeleteOptions.open=true">
                                                    <v-icon style="color:#5458EA" >mdi-trash-can</v-icon>
                                                    <br> <span class="table-default-icon-title">Eliminar</span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </DefaultSimpleTable>
                        </v-col>
                    </v-row>
            </template>
        </DefaultDialog>
        <DefaultDeleteModal
            :options="modalDeleteOptions"
            :ref="modalDeleteOptions.ref"
            @onConfirm="closeFormModal(modalDeleteOptions);loadData()"
            @onCancel="closeFormModal(modalDeleteOptions)"
        />
    </div>
</template>

<script>

import DefaultDeleteModal from "../Default/DefaultDeleteModal";
export default {
    components:{DefaultDeleteModal},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            generic_url:'',
            app_url:'',
            url:{
                time_type:'week',
                number_time:1,
                code:null
            },
            types_of_time:[
                {label:'Día(s)',value:'day'},
                {label:'Semana(s)',value:'week'},
                {label:'Mes(es)',value:'months'},
                {label:'Sin expiración',value:null}
            ],
            urls_generated:[],
            modalDeleteOptions: {
                ref: 'RegisterUrlDeleteModal',
                open: false,
                resource:'',
                base_endpoint: '/register_url',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '/register_url/destroy',
                resource_id:null
            },
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
        resetSelects() {
            let vue = this
            // Selects independientes

        },
         resetValidation() {
            let vue = this
        },
        async loadData() {
            let vue = this
            let base = `${vue.options.base_endpoint}`
            let url = base;
            await vue.$http.get(url).then(({data}) => {
                this.app_url = data.data.data.app_url;
                this.url.code = data.data.data.generic_url;
                this.urls_generated = data.data.data.urls_generated;
                vue.hideLoader();
            })
            return 0;
        },
        loadSelects() {
            let vue = this
        },
        copy_content(id,text){
            const elem = document.createElement('textarea');
            elem.value = text;
            document.body.appendChild(elem);
            elem.select();
            document.execCommand('copy');
            document.body.removeChild(elem);
            this.showAlert('Elemento copiado.', 'success')
        },
        async add_url(){
            this.showLoader();
            await axios.post('/invitados/add-url',this.url).then(({data})=>{
                this.showAlert(data.data.msg, 'success')
                this.loadData();
            })
            this.hideLoader();
        },
        async delete_url(){
            this.showLoader();
            await axios.delete(`/invitados/delete-url/${this.modalDeleteOptions.url_id}`,).then(({data})=>{
                this.loadData();
            })
            this.hideLoader();
        },
        changeTypeOfTime(value){
            if(this.url.number_time && value){
                return 0;
            }
            (value) ? (this.url.number_time=1) : this.url.number_time=null;
        }
    }
}
</script>
<style>
.copy-text{
    font-style: normal;
    font-weight: 400;
    font-size: 16px;
    line-height: 23px;
    display: flex;
    align-items: center;
    text-decoration-line: underline;
    color: #5458EA;
}
.notificationCenter{
    z-index: 99999999999;
}
</style>