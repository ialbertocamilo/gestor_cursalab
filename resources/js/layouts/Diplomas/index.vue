<template>
    <v-container  class="grey lighten-5 mb-6 d-flex justify-content-center">
        <panelEditor :d_btn="d_btn" @emit_add_text="add_text" @emit_add_itext="add_itext" @emit_add_image="add_image" @emit_prev="prev" @emit_delete="delete_object" />
        <v-row class="mt-15" style="height: 67vh;">
            <div id="overlay-div" class="overlay-div" v-show="overlay"> 
                <div class=" col-md-4 d-flex justify-content-center flex-column">
                    <p style="color: white; font-size: 20px;align-self: center;">Elige una plantilla</p>
                    <v-file-input
                        v-model="bg_image"
                        accept="image/*"
                        prepend-icon="mdi-camera"
                        @change="set_plantilla()"
                    ></v-file-input>
                    <!-- <dialogPlantillas @obtener_data="get_data_plantilla" /> -->
                </div>
            </div>
            <v-col cols="12" md="12" sm="12">
                <!-- CONTEXT MENU -->
                <div class="c_menu elevation-4 mx-2 my-1" id="c_menu" style="display:none;flex-direction:row;align-items: center;">
                    <v-btn-toggle
                        v-model="toggle_multiple"
                        dense
                        multiple
                    >
                        <v-tooltip right>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn :disabled="d_btn" @click="edit_object('bold')" v-bind="attrs" v-on="on" text><v-icon>mdi-format-bold</v-icon></v-btn>  
                            </template>
                            <span>Negrita</span>
                        </v-tooltip>
                        <v-tooltip right>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn :disabled="d_btn" @click="edit_object('italic')" v-bind="attrs" v-on="on" text><v-icon>mdi-format-italic</v-icon></v-btn>  
                            </template>
                            <span>Cursiva</span>
                        </v-tooltip>
                        <v-tooltip right>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn :disabled="d_btn" @click="edit_object('centrado')" v-bind="attrs" v-on="on" text><v-icon>mdi-format-align-center</v-icon></v-btn>  
                            </template>
                            <span>Centrado</span>
                        </v-tooltip>
                   </v-btn-toggle>
                   <v-tooltip right>
                        <template v-slot:activator="{ on, attrs }">
                            <input @input="edit_object('size')" v-model="size_t" class="p-2"  v-bind="attrs" v-on="on" style="max-width: 61px;" type="number">
                        </template>
                        <span>Tamaño</span>
                    </v-tooltip>
                    <v-tooltip right>
                        <template v-slot:activator="{ on, attrs }">
                            <v-select solo style="max-height: 0vh;max-width:200px;padding-bottom:43px" @change="edit_object('formato')" return-object v-model="select_m" v-show="select_s" :items="options" item-text="text" item-value="id" v-bind="attrs" v-on="on" hide-details filled dense></v-select>
                        </template>
                        <span>Formato</span>
                    </v-tooltip>
                    <v-text-field style="max-width:170px" class="pb-1 ml-2" v-model="color" hide-details solo filled dense>
                        <template v-slot:append>
                            <v-menu v-model="menu" top nudge-bottom="105" nudge-left="16" :close-on-content-click="false">
                                <template v-slot:activator="{ on }">
                                    <div :style="swatchStyle" v-on="on" />
                                </template>
                                <v-card>
                                    <v-card-text class="pa-0">
                                        <v-color-picker v-model="color" flat />
                                    </v-card-text>
                                </v-card>
                            </v-menu>
                        </template>
				    </v-text-field>
                     <v-tooltip right>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn :disabled="d_btn" @click="delete_object()" v-bind="attrs" v-on="on" text><v-icon>mdi-delete</v-icon></v-btn>  
                        </template>
                        <span>Eliminar</span>
                    </v-tooltip>
                </div>
                <!-- <div class="c_menu  elevation-4 mx-2 my-1 flex-row" style="right:100px">
                    <v-tooltip right>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn fab small @click="zoom('plus')" v-bind="attrs" v-on="on" text><v-icon>mdi-magnify-plus-outline</v-icon></v-btn>  
                        </template>
                        <span>Aumentar</span>
                    </v-tooltip>
                    <v-tooltip right>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn fab small @click="zoom('minus')" v-bind="attrs" v-on="on" text><v-icon>mdi-magnify-minus-outline</v-icon></v-btn>  
                        </template>
                        <span>Disminuir</span>
                    </v-tooltip>
                </div> -->
                <div class="c_menu elevation-4 mx-2 my-1" id="c_menu_image" style="display:none;flex-direction:row;align-items: center;">
                    <v-btn-toggle
                        v-model="toggle_multiple_image"
                        dense
                        multiple
                    >
                        <v-tooltip right>
                            <template v-slot:activator="{ on, attrs }">
                                <v-btn :disabled="d_btn" @click="edit_object('bloquear')" v-bind="attrs" v-on="on" text><v-icon>mdi-lock</v-icon></v-btn>  
                            </template>
                            <span>Bloquear</span>
                        </v-tooltip>
                   </v-btn-toggle>
                    <v-tooltip right>
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn :disabled="d_btn" @click="delete_object()" v-bind="attrs" v-on="on" text><v-icon>mdi-delete</v-icon></v-btn>  
                        </template>
                        <span>Eliminar</span>
                    </v-tooltip>
                </div>
                <!-- CANVAS -->
                <div v-show="!overlay" class="col-12">
                    <canvas ref="canvasElement" id="canvas" style="border:1px solid black;" width="1300" height="600"></canvas>
                </div>
            </v-col>
            <v-col class="d-flex justify-content-end">
                <instrucciones/>
            </v-col>
        </v-row>
        <!-- DIALOG DE PREVISUALIZACIÓN -->
        <v-dialog
            transition="dialog-bottom-transition"
            max-width="900"
            v-model="d_preview"
            style="z-index:501"
            >
            <template v-slot:default="dialog">
                <v-card>
                    <v-toolbar
                    color="primary"
                    dark
                    >Previsualización</v-toolbar>
                    <v-card-text class="d-flex justify-content-center mt-5">
                        <img :src="preview" style="max-width:720px">
                    </v-card-text>
                    <v-card-actions class="justify-end">
                    <v-btn
                        text
                        @click="dialog.value = false"
                    >Cerrar</v-btn>
                    <v-btn
                        depressed
                        @click="dialog_save = true"
                        color="primary"
                    >Guardar</v-btn>
                    </v-card-actions>
                </v-card>
            </template>
        </v-dialog>
        <!-- DIALOG SAVE -->
        <v-dialog
            v-model="dialog_save"
            persistent
            max-width="290"
        >
            <v-card>
                <v-card-subtitle class="headline">
                    Escriba el nombre de el diploma
                </v-card-subtitle>
                <v-card-text>
                    <v-form>
                        <v-text-field
                            :rules="nameRules"
                            label="Nombre"
                            v-model="name_plantilla"
                            prefix="plantilla-"
                        ></v-text-field>
                    </v-form>
                </v-card-text>
                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn
                        color="green darken-1"
                        text
                        @click="dialog_save = false"
                    >
                        Cancelar
                    </v-btn>
                    <v-btn
                        color="green darken-1"
                        text
                        @click="save_plantilla()"
                    >
                        Aceptar
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-container>
</template>
<script>
import { fabric } from 'fabric';
import instrucciones from './instrucciones.vue';
import dialogPlantillas from './dialog_plantillas.vue';
import panelEditor from './panel_editor.vue';
export default {
    components:{instrucciones,dialogPlantillas,panelEditor},
    data(){
        return {
            size:'16',
            canvas:null,
            absolute: true,
            overlay: true,
            d_btn:true,
            bg_image:null,
            image:null,
            click_object:false,
            obj_select:null,
            toggle_multiple:[],
            toggle_multiple_image:[],
            size_t:0,
            select_s:false,
            select_m:1,
            options:[],
            d_preview:false,
            preview:null,
            color: '#1976D2FF',
		    mask: '!#XXXXXXXX',
            menu: false,
            dialog_save:false,
            name_plantilla:'',
            with_z:1,
            nameRules: [
                v => !!v || 'El nombre es requerido',
            ],
            edit_plantilla:{},
            imgWidth:0,
            imgHeight:0,
        }
    },
    created(){
        window.addEventListener('keydown', this.keyDown)
    },
    computed:{
        swatchStyle() {
            const { color, menu } = this;
            if(this.obj_select){
                this.edit_object('color');
            }
            return {
                backgroundColor: color,
                cursor: 'pointer',
                height: '30px',
                width: '30px',
                borderRadius: menu ? '50%' : '4px',
                transition: 'border-radius 200ms ease-in-out'
            }
        }
    },
    mounted(){
        let vue=this;
        this.canvas =new fabric.Canvas('canvas');
        //CONTEXT MENU
        this.canvas.on('mouse:up', function (e) {
            //check if user clicked an object
            if(e.target && e.target.contextMenuImage){
                let c_menu_image = document.getElementById('c_menu_image');
                c_menu_image.style.display='flex';
                vue.context_menu_image(e.target);
            }else{
                document.getElementById('c_menu_image').style.display='none';
            }
            let c_menu = document.getElementById('c_menu');
            if (e.target && e.target.contextMenu) {
                c_menu.style.display='flex';
                vue.size_t = e.target.fontSize;
                vue.color = e.target.fill;
                vue.context_menu(e.target);
            }else{
                c_menu.style.display='none';
            }
        });
        vue.canvas.on('mouse:wheel', function(opt) {
            return false;
            // var delta = opt.e.deltaY;
            // var zoom = vue.canvas.getZoom();
            // zoom *= 0.999 ** delta;
            // if (zoom > 20) zoom = 20;
            // if (zoom < 0.01) zoom = 0.01;
            // vue.canvas.setZoom(zoom);
            // opt.e.preventDefault();
            // opt.e.stopPropagation();
            // var vpt = this.viewportTransform;
            // if (vpt[4] >= 0) {
            //     this.viewportTransform[4] = 0;
            // } else if (vpt[4] < vue.canvas.getWidth() - vue.imgWidth * zoom) {
            //     this.viewportTransform[4] = vue.canvas.getWidth() - vue.imgWidth * zoom;
            // }
            // if (vpt[5] >= 0) {
            //     this.viewportTransform[5] = 0;
            // } else if (vpt[5] < vue.canvas.getHeight() - vue.imgHeight * zoom) {
            //     this.viewportTransform[5] = vue.canvas.getHeight() - vue.imgHeight * zoom;
            // }
        });
        vue.canvas.on('object:moving', vue.preventDragOffCanvas);
    },
    methods:{
        preventDragOffCanvas({target}){
            let activeObject = this.canvas.getActiveObjects();
            if(activeObject.length==1 && activeObject[0].image){
                return false;
            }
            let modified = false;
            let left = target.left;
            let top = target.top;
            let zoom = this.canvas.getZoom();
            let width = this.canvas.width/zoom;
            let height = this.canvas.height/zoom;
            let rightBound = target.width;
            let bottomBound = target.height;

            if(top < 0){
                top = 0;
                modified = true;
            }
            // don't move off bottom
            if(top + bottomBound > height){
                top = height - bottomBound ;
                modified = true;
            }
            // don't move off left
            if(left < 0){
                left = 0;
                modified = true;
            }
            // don't move off right
            if(left + rightBound > width){
                left = width  - rightBound;
                modified = true;
            }
            if(modified){
                target.set("left", left);
                target.set("top", top);   
            }
        },
        context_menu(target){
            let vue = this;
            this.toggle_multiple=[];
            this.select_s=false;
            let activeObject = vue.canvas.getActiveObjects();
            if(activeObject.length==1){
                this.click_object=true;
                this.obj_select = target;
                if(target.fontWeight=='bold'){
                    this.toggle_multiple.push(0);
                }
                if(target.fontStyle=='italic'){
                    this.toggle_multiple.push(1);
                }
                if(target.centrado){
                    this.toggle_multiple.push(2);
                }
                this.select_m = target.id_formato; 
            }
            //POR TIPO
            switch (target.id) {
                case 'usuarios':
                    this.select_s=true;
                    console.log(this.select_s);
                    this.options=[
                        {id:1, text: 'nombre',formato:'Renato'},
                        {id:2, text: '1 nombre - 1 apellido',formato:'Renato Perez'},
                        {id:3, text: '1nombre - 2 apellidos',formato:'Renato Perez Melgar'},
                        {id:4, text: 'nombre completo',formato:'Renato Mauricio Perez Melgar'},
                    ]
                break;
                case 'fecha':
                    this.select_s=true;
                    this.options=[
                        {id:'%d/%m/%Y', text: '14/03/2021',formato:'14/03/2021'},
                        {id:'%d/%m/%y', text: '14/03/21',formato:'14/03/21'},
                        {id:'%d/%-m/%y', text: '14/3/21',formato:'14/3/21'},
                        {id:'%Y-%m-%d', text: '2021-03-14',formato:'2021-03-14'},
                        {id:'%A, %d de %B de %Y'   , text: 'miércoles, 14 de Diciembre de 2021',formato:'miércoles, 14 de Diciembre de 2021'},
                        {id:'%A %d de %B de %Y', text: 'miércoles 14 de Diciembre de 2021',formato:'miércoles 14 de Diciembre de 2021'},
                        {id:'%d de %B de %Y', text: '14 de Diciembre de 2021',formato:'14 de Diciembre de 2021'},
                    ]
                break;
            }
        },
        context_menu_image(target){
            let vue = this;
            this.toggle_multiple_image=[];
            this.select_s=false;
            let activeObject = vue.canvas.getActiveObjects();
            if(activeObject.length==1){
                this.click_object=true;
                this.obj_select = target;
                this.select_m = target.id_formato; 
            }
            if(target.lockMovementX){
                this.toggle_multiple_image.push(0);
            }
        },
        edit_object(tipo){
            let vue = this;
            switch (tipo) {
                case 'bold':
                    if(vue.obj_select.fontWeight=='normal'){
                        vue.obj_select.set({ fontWeight: 'bold' });
                        vue.toggle_multiple = 0;
                    }else{
                        vue.obj_select.set({ fontWeight: 'normal' });
                    }
                break;
                case 'italic':
                    if(vue.obj_select.fontStyle=='normal'){
                        vue.obj_select.set({ fontStyle: 'italic' });
                        vue.toggle_multiple = 1;
                    }else{
                        vue.obj_select.set({ fontStyle: 'normal' });
                    }
                break;
                case 'size':
                    vue.obj_select.set({ fontSize: vue.size_t });
                break
                case 'color':
                    vue.obj_select.set({ fill: vue.color });
                break
                case 'formato':
                    vue.obj_select.set({ text: vue.select_m.formato,id_formato:vue.select_m.id});
                break
                case 'centrado':
                    if(vue.obj_select.centrado==false){
                            let bg=vue.canvas.backgroundImage;
                        let left = bg.left+(bg.width/2)-(this.obj_select.width/2); 
                        vue.obj_select.set({ left: left,centrado:true,lockMovementX:true});
                        vue.obj_select.setCoords();
                        vue.toggle_multiple=2;
                    }else{
                        vue.obj_select.set({centrado:false,lockMovementX:false});
                    }
                break
                case 'bloquear':
                     if(vue.obj_select.lockMovementX==false){
                        vue.toggle_multiple_image = 0;
                        vue.obj_select.set({ lockMovementX: true,lockMovementY:true});
                    }else{
                        vue.obj_select.set({ lockMovementX: false,lockMovementY:false});
                    }
                break
            }
            this.canvas.renderAll() 
        },
        add_image(){
            let img = document.getElementById('input_image').files[0];
            let vue = this;
            let file = img;
            let reader = new FileReader();
            reader.onload = function (f) {
                let data = f.target.result;                    
                fabric.Image.fromURL(data, function (img) {
                    let oImg = img.set({image:true,left: 200, top: 200, angle: 0,contextMenuImage:true,static:true,lockRotation:true});
                    vue.canvas.add(oImg).renderAll();
                    vue.canvas.setActiveObject(oImg);
                    vue.canvas.toDataURL({format: 'jpg', quality: 1});
                });
            };
            reader.readAsDataURL(file);
        },
        add_text(text,hasControls,tipo,id_formato,font_size = null,left =300,top = 300,fill='#000000FF',centrado=false){
            this.close_context_menu();
            font_size = (!font_size) ? parseInt(30/this.canvas.getZoom()) : font_size;
            let text2 = new fabric.Text("Text", {
                id:tipo,
                fontSize: font_size,
                fontFamily:'calisto-mt',
                fontStyle:'normal',
                selectable: true,
                left: 300,
                top: 300,
                text: text,
                fill: '#000000FF',
                hasControls:hasControls,
                hasRotatingPoint:false,
                static:false,
                centrado:false,
                contextMenu:true,
                id_formato:id_formato,
                padding:0,
                charSpacing :0,
                textAlign:'center'
            });
            text2.strokeWidth= 0;
            this.canvas.add(text2);
            this.canvas.setActiveObject(text2);
        },
        add_itext(){
            this.close_context_menu();
            let font_size = parseInt(30/this.canvas.getZoom());
            let t = new fabric.IText('Escribe aquí..', { 
                fontFamily: 'calisto-mt',
                fontSize: font_size,
                left: 300, 
                top: 300,
                fill: '#000000FF',
                static:true,
                hasControls:false,
                contextMenu:true,
            });
            this.canvas.add(t);
            this.canvas.setActiveObject(t);
        },
        add_line(){
                this.canvas.add(new fabric.Line([0, 0, 180, 0], {
                    left: 300,
                    top: 300,
                    stroke: 'black',
                    static:true,
                    contextMenu:false,
                }));
        },
        set_plantilla(){
            let vue = this;
            let file = vue.bg_image;
            let reader = new FileReader();
            let clientWidth = document.getElementById('overlay-div').clientWidth;
            let clientHeight = document.getElementById('overlay-div').clientHeight;
            reader.onload = function (f) {
                let data = f.target.result;    
                fabric.Image.fromURL(data, function (img) {
                    let oImg = img.set({
                        id:'bg',
                        left: 0, 
                        top: 0, 
                        angle: 0,
                        static:true,
                        hasControls:false,
                        contextMenuImage:true,
                        lockMovementX:true,
                        lockMovementY:true,
                    });
                    //Adecuar tamaño a la plantilla
                    vue.canvas.setZoom( clientHeight/oImg.height);
                    vue.canvas.setHeight(clientHeight)
                    vue.canvas.setWidth(clientWidth)
                    let zoom = vue.canvas.getZoom();
                    vue.canvas.backgroundColor = '#2d3e50';
                    //Centrar
                    let left = (vue.canvas.width/zoom-oImg.width)/2; 
                    oImg.set({ left,centrado:true});
                    oImg.setCoords();
                    //insertar imagen
                    vue.imgWidth = oImg.width;
                    vue.imgHeight = oImg.height;
                    vue.canvas.setBackgroundImage(oImg).renderAll();
                    // vue.canvas.setActiveObject(oImg);
                    vue.canvas.toDataURL({format: 'jpg', quality: 1});
                });
            };
            reader.readAsDataURL(file);
            vue.d_btn=false;
            vue.overlay=false;
        },
        deleteObjects(){
            let vue=this;
            let activeObject = vue.canvas.getObjects();
            if (activeObject) {
                if (confirm('Se eliminará toda la plantilla y Todos sus elementos \n ¿Estas seguro?')) {
                    let objectsInGroup = activeObject;
                    vue.canvas.discardActiveObject();
                    objectsInGroup.forEach(function(object) {
                        vue.canvas.remove(object);
                    });
                    vue.d_btn=true;
                    vue.overlay=true;
                    vue.bg_image=null;
                    vue.canvas.clear();
                    // const image = new fabric.Image('');
                    // vue.canvas.setBackgroundImage(image, vue.canvas.renderAll.bind(vue.canvas));
                    this.close_context_menu();
                }
            }
        },
        close_context_menu(){
            document.getElementById('c_menu').style.display='none';
            document.getElementById('c_menu_image').style.display='none';
            this.select_s=false;
        },

        keyDown(){
            switch (event.keyCode) {
                case 46:
                    // ELIMINAR CON SUPR
                    // this.deleteObjects();
                    break;
                case 90:
                    //RETROCEDES CON CTRL+Z
                    let evtobj = window.event? event : e
                    if (evtobj.keyCode == 90 && evtobj.ctrlKey){ 
                        let canvas_objects = this.canvas._objects;
                        if(canvas_objects.length !== 0){
                            let last = canvas_objects[canvas_objects.length -1]; 
                            if(last.id=='bg'){
                                this.d_btn=true;
                                this.overlay=true;
                                this.bg_image=null;
                            }
                            this.canvas.remove(last);
                            this.canvas.renderAll();
                        }
                        this.close_context_menu();
                    };
                break
            }
        },
        delete_object(tipo='normal'){
            if(tipo == 'bg'){
                this.deleteObjects();
            }else{
                if (confirm('¿Estas seguro?')) {
                     this.canvas.remove(this.obj_select);
                     this.close_context_menu();
                }       
            }
        },
        async prev(){
            let data = {
                'info': this.canvas.toJSON(['id','static','x','y','width','height','centrado','id_formato','zoomX']),
                'zoom':this.canvas.getZoom(),
            };
            this.preview=null;
            this.name_plantilla=null;
            $("#pageloader").fadeIn();
            await axios.post('/diplomas/get_preview_data',data).then((res)=>{
                this.preview=res.data.preview,
                this.d_preview=true;
                $("#pageloader").fadeOut();
            }).catch(e=>{
                $("#pageloader").fadeOut();
            })
        },
        save_plantilla(){
            let data = {
                'info': this.canvas.toJSON(['id','static','x','y','width','height','centrado','id_formato','zoomX']),
                'nombre_plantilla':this.name_plantilla,
            };
            $("#pageloader").fadeIn();
            axios.post('/diplomas/save',data).then((res)=>{
                this.d_preview=false;
                this.dialog_save=false;
                let vue = this;
                vue.queryStatus("diploma", "crear_diploma");
                $("#pageloader").fadeOut();
                this.$notification.success('El diploma fue guardada correctamente.', {
                    timer: 25,
                    showLeftIcn: false,
                    showCloseIcn: true,
                });
            })
        },
        zoom(tipo){
            let vue = this;
            let zoom = vue.canvas.getZoom();
            zoom = (tipo=='plus') ? zoom + 0.1 : zoom - 0.1 ; 
            vue.canvas.setZoom(zoom);
        },
        get_data_plantilla(diploma){
            let vue = this;
            Object.assign(this.edit_plantilla,diploma);
            axios.post('/diplomas/get_data_diploma',diploma).then(res=>{
                let {d_objects,s_obj,info_bg} = res.data.diploma;
                let json_d_objects = JSON.parse(d_objects);
                let json_info_bg = JSON.parse(info_bg);
                
                fetch(res.data.plantilla)
                .then(res => res.blob())
                .then(blob => {
                    vue.bg_image  = new File([blob], "prueba",{ type: "image/png" })
                    this.set_plantilla();
                })
                json_d_objects.forEach(obj => {
                    switch (obj.type) {
                        case 'text':
                            this.add_text(obj.text,obj.hasControls,obj.tipo,obj.font_size,obj.left,obj.top,obj.fill,obj.centrado);
                        break;
                    }
                });
                vue.d_btn=false;
                vue.overlay=false;
                vue.dialog_plantillas = false;
            })
            
        },
        dataURLtoFile(dataurl, filename) {
            var arr = dataurl.split(','), mime = arr[0].match(/:(.*?);/)[1],
                bstr = atob(arr[1]), n = bstr.length, u8arr = new Uint8Array(n);
            while(n--){
                u8arr[n] = bstr.charCodeAt(n);
            }
            return new File([u8arr], filename, {type:mime});
        }

    }
}
</script>
<style>
.cont{
  border-radius: 5px;
  background: #fff;
  display: inline-block;
  position: fixed;
  z-index:500;
}
.c_menu{
  background: #fff;
  display: flex;
  flex-direction: column;
  position: fixed;
  z-index:500;
}
.input_img {
display: none;
}
.overlay-div{
display: flex;
justify-content: center;
align-items: center;
width: 100%;
height: 66vh;
background-color: rgba(0,0,0,0.5);
}
</style>