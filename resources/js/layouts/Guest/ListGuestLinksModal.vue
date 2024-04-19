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
                        <v-col cols="12">
                            <DefaultToggle v-model="url.activate_by_default" type="only-label" label="¿Desea que los usuarios se activen automáticamente al ingresar/ o realizar el registro?" />
                        </v-col>
                        <v-col cols="12"  :style="`display: grid;grid-template-columns: ${url.type_form == 'custom_criteria' ? 'auto 1fr 1fr 1fr 2fr;' : 'auto auto auto 1fr;' }`">
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
                            <div class="flex flex-column mx-2">
                                <span>Modulos</span>
                                <DefaultSelect
                                    :attach="false"
                                    v-model="url.subworkspace_id"
                                    :items="modules"
                                    item-text="name"
                                    item-value="id"
                                    :clearable="true"
                                    dense
                                />
                            </div>
                            <div class="flex flex-column mx-2">
                                <span>Tipo de formulario</span>
                                <DefaultSelect
                                    :attach="false"
                                    v-model="url.type_form"
                                    :items="types_form"
                                    item-text="label"
                                    item-value="value"
                                    dense
                                />
                            </div>
                            <div class="flex flex-column mx-2" v-if="url.type_form == 'custom_criteria'">
                                <span>Criterios</span>
                                <DefaultAutocomplete
                                    :attach="false"
                                    v-model="url.criteria_list"
                                    :items="criteria"
                                    item-text="criterion_title"
                                    item-value="criterion_id"
                                    dense
                                    multiple
                                    :showSelectAll="false"
                                />
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <div class="flex align-self-end">
                                <DefaultButton
                                    style=" width: 100%;max-width: 100% !important;"
                                    label="Añadir"
                                    :disabled=" 
                                        !url.code 
                                        || (url.type_of_time && (url.number_time<1 || url.number_time>99)) 
                                        || (url.type_form == 'custom_criteria' && url.criteria_list.length ==0)"
                                    @click="add_url()"
                                />
                            </div>
                        </v-col>
                        <v-col cols="12">
                            <DefaultSimpleTable>
                                <template slot="content">
                                    <thead class="text-center">
                                        <tr>
                                            <th style="background: #57BFE3;border:none !important;border-radius: 5px 0px 0px 0px;">URL</th>
                                            <th style="background: #57BFE3;border:none !important;border-radius: 0px 0px 0px 0px;">Módulo</th>
                                            <th style="background: #57BFE3;border:none !important;border-radius: 0px 0px 0px 0px;">Activación <br> automática</th>
                                            <th style="background: #57BFE3;border:none !important;">Expiración</th>
                                            <th style="background: #57BFE3;border:none !important;border-radius: 0px 5px 0px 0px;">Opciones</th>
                                        </tr>
                                    </thead>
                                    <tr
                                        v-for="(item, index) in urls_generated"
                                        :key="index"
                                    >
                                        <td style="border:none !important" :id="`td-url-${index}`">{{ app_url+item.url }}</td>
                                        <td style="border:none !important" :id="`td-url-${index}`">{{ item.subworkspace ? item.subworkspace.name : 'No configurado' }}</td>
                                        <td style="border:none !important" :id="`td-url-${index}`">{{ item.activate_by_default ? 'Sí' : 'No' }}</td>
                                        <td style="border:none !important">{{ (item.expiration_date) ? item.expiration_date : 'sin expiración' }}</td >
                                        <td style="border:none !important">
                                            <div class="d-flex justify-center flex-row my-2">
                                                <div class="text-center mx-1" style="cursor: pointer;" @click="copy_content(`td-url-${index}`,app_url+item.url)">
                                                    <v-icon style="color:#5458EA" >mdi-content-copy</v-icon>
                                                    <br> <span class="table-default-icon-title">Copiar</span>
                                                </div>
                                                <div class="text-center mx-1" style="cursor: pointer;" @click="downloadQr(app_url+item.url)">
                                                    <v-icon style="color:#5458EA" >mdi-qrcode</v-icon>
                                                    <br> <span class="table-default-icon-title">Genrar Qr</span>
                                                </div>
                                                <div class="text-center mx-1" style="cursor: pointer;" @click="openFormModal(modalDeleteOptions,item,'delete','Eliminar un <b>Enlace</b>')">
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
import QRCode from "qrcode";
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
            modules:[],
            criteria:[],
            url:{
                type_of_time:'months',
                type_form: 'default',
                number_time:1,
                code:null,
                activate_by_default:false,
                subworkspace_id:null,
                criteria_list :[]
            },
            types_of_time:[
                {label:'Día(s)',value:'day'},
                {label:'Semana(s)',value:'week'},
                {label:'Mes(es)',value:'months'},
                {label:'Sin expiración',value:null}
            ],
            types_form:[
                {label:'Datos personales y criterios obligatorios.',value:'default'},
                {label:'Datos personales y criterios personalizados.',value:'custom_criteria'},
                {label:'Solo datos personales (sin criterios).',value:'without_criteria'},
            ],
            urls_generated:[],
            modalDeleteOptions: {
                ref: 'RegisterUrlDeleteModal',
                open: false,
                resource:'',
                base_endpoint: '/invitados/register-url',
                contentText: '¿Desea eliminar este registro?',
                endpoint: '/register-url/destroy',
                resource_id:null
            },
            modalQrOptions:{
                ref:'ModalQROptions',
                title:'QR',
                open: false,
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
        resetSelects() {
            let vue = this;
            
            // Selects independientes

        },
        resetValidation() {
            let vue = this;
            
        },
        async loadData() {
            let vue = this
            let base = `${vue.options.base_endpoint}`
            let url = base;
            await vue.$http.get(url).then(({data}) => {
                this.urls_generated = data.data.urls_generated;
                vue.hideLoader();
            })
            return 0;
        },
        async loadSelects() {
            let vue = this
            await vue.$http.get('invitados/init-data').then(({data}) => {
                vue.app_url = data.data.app_url;
                vue.url.code = data.data.generic_url;
                vue.modules = data.data.modules;
                vue.criteria = data.data.criteria;
            })
        },
        copy_content(id,text){
            // const elem = document.createElement('textarea');
            // elem.value = text;
            // document.body.appendChild(elem);
            // elem.select();
            // document.execCommand('copy');
            // document.body.removeChild(elem);
            // console.log(elem.value,text);
            // this.showAlert('Elemento copiado.', 'success')

            // navigator.clipboard.writeText(this.generator.password)
            navigator.clipboard
              .writeText(text)
              .then(() => {
                this.toggle();
              })
              .catch(() => {
                this.showAlert('Elemento copiado.', 'success')
                // console.log("copyPassword: something went wrong");
              });
        },
        async add_url(){
            this.showLoader();
            await axios.post('/invitados/add-url',this.url).then(({data})=>{
                this.showAlert(data.data.msg,data.data.type_msg)
                this.loadData();
                this.hideLoader();
            }).catch(()=>{
                this.hideLoader();
                this.showAlert(data.data.msg || 'No se pudo crear la URL.', 'success')
            })
        },
        downloadQr(url){
            const opts = {
                errorCorrectionLevel: 'H',
                type: 'image/png',
                quality: 1,
                margin: 1,
                width : '250',
                color: {
                    dark:"#000000",
                    light:"#ffffff"
                }
            }
            let vue =this;
            QRCode.toDataURL(url, opts, function (err, qrCodeUrl) {
                if (err) throw err
                vue.descargarImagenDesdeBase64(qrCodeUrl,'qr-code');
            })
        },
        descargarImagenDesdeBase64(base64String, nombreArchivo) {
            // Separa la cadena Base64 para obtener el contenido de la imagen
            const partes = base64String.split(';base64,');
            const formato = partes[0].split(':')[1];
            const contenidoBase64 = partes[1];
            // Convierte el contenido Base64 en un ArrayBuffer
            const byteCharacters = atob(contenidoBase64);
            const byteArrays = new Uint8Array(byteCharacters.length);

            for (let i = 0; i < byteCharacters.length; i++) {
                byteArrays[i] = byteCharacters.charCodeAt(i);
            }

            const blob = new Blob([byteArrays], { type: formato });

            // Crea un enlace temporal para la descarga
            const enlace = document.createElement('a');
            enlace.href = URL.createObjectURL(blob);
            enlace.download = nombreArchivo || 'imagen.png';

            // Haz clic en el enlace para iniciar la descarga
            enlace.click();

            // Libera la URL del objeto Blob
            URL.revokeObjectURL(enlace.href);
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
.table-scroll{
    border: none !important;
}
</style>
