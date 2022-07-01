<template>
  <div class="text-center">
    <v-btn
        color="red lighten-2"
        class="mt-2 mr-6"
        dark
        @click="getData()"
    >
        Administración de Ayuda
    </v-btn>
    <v-dialog
      v-model="dialog"
      width="600"
    >
      <v-card>
        <v-card-title class="text-h6 grey lighten-2">
          Lista de Ayuda (se muestra en la APP)
          <v-spacer></v-spacer>
          <v-btn outlined color="indigo" @click="add_item()">Agregar</v-btn>
        </v-card-title>
        <v-card-text>
            <v-data-table
                :headers="headers"
                :items="lista_ayuda"
                :items-per-page="5"
            >
                <template v-slot:[`item.orden`]="props">
                    <div class="d-flex flex-column align-items-center">
                        <v-btn text icon @click="move_item('down',props.item)">
                            <v-icon >mdi-chevron-up</v-icon>
                        </v-btn>
                        <span v-text="props.item.orden"></span>
                        <v-btn text icon @click="move_item('up',props.item)">
                            <v-icon>mdi-chevron-down</v-icon>
                        </v-btn>
                    </div>
                </template>
                <template v-slot:[`item.nombre`]="props">
                    <v-text-field v-model="props.item.nombre"></v-text-field>
                </template>
                <template v-slot:[`item.check_text_area`]="props">
                    <v-simple-checkbox
                        v-model="props.item.check_text_area"
                    ></v-simple-checkbox>
                </template>
                 <template v-slot:[`item.opcion`]="props">
                     <v-icon small @click="delete_item(props.item)">mdi-delete</v-icon>
                </template>
            </v-data-table>
        </v-card-text>
        <v-card-actions>
            <v-spacer>
            </v-spacer>
            <v-btn
                @click="dialog=false"
                depressed
              >
                Cancelar
            </v-btn>
            <v-btn color="primary" @click="saveData()">
                Guardar
            </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
<script>
export default {
     data() {
    return {
        search: '',
        headers: [
          { text:'Orden', value: 'orden',align:'center',sortable:false},
          { text:'Nombre', value: 'nombre',align:'center',sortable:false},
          { text:'¿Agregar detalle?', value: 'check_text_area',align:'center',sortable:false},
          { text:'Opciones', value: 'opcion',align:'center',sortable:false},
        ],
        lista_ayuda:[],
        delete_elements:[],
        dialog:false,
        custom_list:{
            'id':0,
            'orden':0,
            'nombre':'',
            'check_text_area':false
        },
    }},
    methods:{
        async getData() {
            await axios.get('/ayuda_app/getData').then(res =>{
                 this.lista_ayuda = res.data.lista_ayuda;
                 this.dialog = true;
            })
        },
        saveData(){
            let validation = this.lista_ayuda.every(item => item.nombre);
            if(!validation){alert('Hay nombres vacíos en la lista.');return false;}
            let data={
                'update_create':this.lista_ayuda,
                'delete':this.delete_elements,
            };
             axios.post('/ayuda_app/saveData',data).then(res =>{
                this.dialog = false;
            })
        },
        move_item(type,item){
            let findIndex = this.lista_ayuda.findIndex(e=>e.id == item.id);
            if((findIndex == 0 && type =='down') || (findIndex+1 ==  this.lista_ayuda.length && type=='up')){return false;}
            this.lista_ayuda[findIndex].orden = (type == 'up') ? item.orden +1 : item.orden -1 ;
            let index_modify =  (type == 'up') ? findIndex+1 : findIndex-1;
            this.lista_ayuda[index_modify].orden = (type == 'up') ? item.orden -1 : item.orden +1 ; 
            this.reorden_items();
        },
        add_item(){
            let max_orden =  Math.max(...this.lista_ayuda.map(o => o.orden));
            let orden = (max_orden ==-Infinity ) ? 1 : max_orden +1  ;
            this.lista_ayuda.push({
                id:'item_nuevo:'+Date.now(),
                orden:orden,
                nombre:'',
                check_text_area:false,
            })
        },
        delete_item(item){
            this.delete_elements.push(item.id);
            let index_item = this.lista_ayuda.indexOf(item);
            let orden = item.orden;
            this.lista_ayuda.forEach((e,index) => {
                if(index>index_item){
                    (this.lista_ayuda[index]) && (this.lista_ayuda[index].orden = orden);
                    orden++; 
                }
            });
            this.lista_ayuda.splice(index_item, 1);
            this.reorden_items();
        },
        reorden_items(){
            this.lista_ayuda.sort((a,b) => (a.orden > b.orden) ? 1 : ((b.orden > a.orden) ? -1 : 0))
        }
    }
}
</script>