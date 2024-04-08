<template>
    <section class="section-list ">
         <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Diplomas: {{ diploma_id ? 'Editar plantilla' : 'Crear plantilla' }}
                <v-btn
                    icon
                    color="primary"
                    class="ml-2"
                    @click="openFormModal(modalDiplomaFormInfoOptions, null, 'status', 'Instrucciones')">
                    <v-icon>mdi-information</v-icon>
                </v-btn>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 my-4 py-5">
           <!--  <div  class="ml-4">
                <p class="d-flex align-items-center pt-4">
                     diploma_id ? 'Edición' : 'Creación'  de plantilla

                </p>
            </div> -->
            <div class="mx-8 mb-0 d-flex justify-content-center">
                <v-row class="mt-0 container-box">

                    <div id="overlay-div" class="overlay-div ma-auto mt-5" v-show="overlay">
                        <v-file-input
                            full-width
                            prepend-icon=""
                            class="m-0 p-0"
                            ref="overlayFile"
                            v-model="bg_image"
                            accept="image/*"
                            height="405px"
                            @change="set_plantilla()"
                        />
                        <div class="overlay-icon">
                            <div class="d-flex flex-column text-center">
                                <span class="fas fa-images"
                                      style="font-size: 1.5rem"></span>
                                <span style="font-size: 1rem; margin-top: 1rem;">Agregar imagen</span>
                            </div>

                        </div>
                    </div>

                    <v-col cols="12" md="12" sm="12" class="d-flex align-items-center justify-center">
                        <!-- === CANVAS-MENU === -->
                        <div class="c_menu text-center elevation-4 mx-2 my-1 px-2 py-1" id="c_menu"
                             style="display: none; flex-direction: row; align-items: center; border-radius: .3rem; grid-gap: .5rem;">
                            <v-btn-toggle
                                dense
                                multiple
                                v-model="toggle_multiple"
                                class="position-relative d-flex" style="grid-gap: .5rem;">

                                <div class="css-tooltip css-tooltip--top"
                                     data-tooltip="Negrita">
                                    <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="edit_object('bold')" text>
                                        <v-icon>mdi-format-bold</v-icon>
                                    </v-btn>
                                </div>
                                <div class="css-tooltip css-tooltip--top" data-tooltip="Cursiva">
                                    <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="edit_object('italic')" text>
                                        <v-icon>mdi-format-italic</v-icon>
                                    </v-btn>
                                </div>
                                <div class="css-tooltip css-tooltip--top" data-tooltip="Centrado">
                                    <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="edit_object('centrado')" text>
                                        <v-icon>mdi-format-align-center</v-icon>
                                    </v-btn>
                                </div>
                            </v-btn-toggle>
                            <div class="css-tooltip css-tooltip--top" data-tooltip="Tamaño">
                                <input @input="edit_object('size')" elevation="2" v-model="size_t" class="p-2"
                                        style="max-width: 61px;" type="number">
                            </div>
                            <div class="css-tooltip css-tooltip--top" data-tooltip="Formato">
                                <v-select elevation="2" solo style="max-height: 0vh;max-width:200px;padding-bottom:43px"
                                        @change="edit_object('formato')"
                                        return-object v-model="select_m"
                                        v-show="select_s" :items="options" item-text="text" item-value="id"
                                        hide-details filled dense></v-select>
                            </div>
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
                            <div class="css-tooltip css-tooltip--top" data-tooltip="Eliminar">
                                <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="delete_object()" text>
                                    <v-icon>mdi-delete</v-icon>
                                </v-btn>
                            </div>
                        </div>
                        <div class="c_menu text-center elevation-4 mx-2 my-1" id="c_menu_image"
                                style=" display:none; flex-direction:row; align-items: center; border-radius: .3rem; grid-gap: .5rem;">
                            <v-btn-toggle
                                v-model="toggle_multiple_image"
                                dense
                                multiple
                            >
                                <div class="css-tooltip css-tooltip--top" data-tooltip="Bloquear">
                                    <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="edit_object('bloquear')" text>
                                        <v-icon>mdi-lock</v-icon>
                                    </v-btn>
                                </div>
                            </v-btn-toggle>

                            <div class="css-tooltip css-tooltip--top" data-tooltip="Eliminar">
                                <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="delete_object()" text>
                                    <v-icon>mdi-delete</v-icon>
                                </v-btn>
                            </div>
                        </div>
                        <!-- === CANVAS-MENU === -->

                        <!-- === CANVAS === -->
                        <div v-show="!overlay" class="">
                            <canvas ref="canvasElement" id="canvas" class="canvas-style"></canvas>
                        </div>
                        <!-- === CANVAS === -->
                    </v-col>
                </v-row>

            </div>

            <div class="d-flex justify-center position-relative menu-save">
                <panelEditor
                    :d_btn="d_btn"
                    :btn_process="btn_process"
                    :btn_course="btn_course"
                    @emit_add_text="add_text"
                    @emit_add_itext="add_itext"
                    @emit_add_image="add_image"
                    @emit_prev="prev"
                    @emit_delete="delete_object"
                />
                <!-- <div class="menu-cancel-btn">
                    <v-btn
                        class="default-modal-action-button mx-1 btn_back"
                        text
                        elevation="0"
                        :ripple="false"
                        color="primary"
                        @click="leavePage"
                    >
                        Cancelar
                    </v-btn>
                </div> -->


                <div class="menu-save-btn">
                    <DefaultModalButton
                        :disabled="bg_image ? false : true"
                        :icon="false"
                        label="Guardar"
                        class="default-modal-action-button"
                        @click="openFormModalSave"
                    />
                </div>
            </div>
        </v-card>

        <!-- === INSTRUCCIONES MODAL === -->
        <DiplomaFormInfoModal
            width="30vh"
            :ref="modalDiplomaFormInfoOptions.ref"
            :options="modalDiplomaFormInfoOptions"
            @onConfirm="closeFormModal(modalDiplomaFormInfoOptions)"
        />

        <!-- === PREVIEW MODAL -->
        <DiplomaPreviewModal
            width="50vh"
            :ref="modalDiplomaPreviewOptions.ref"
            :options="modalDiplomaPreviewOptions"
            @onConfirm="closeFormModal(modalDiplomaPreviewOptions)"
        />

        <!-- === GUARDAR MODAL === -->
        <DiplomaFormSave
            width="50vh"
            :ref="modalDiplomaFormSaveOptions.ref"
            :options="modalDiplomaFormSaveOptions"
            @onCancel="closeFormModal(modalDiplomaFormSaveOptions)"
            @onConfirm="save_plantilla"
        />

        <!-- === ALERT MODAL === -->
        <DiplomaAlertModal
            width="30vh"
            :ref="modalDiplomaAlertOptions.ref"
            :options="modalDiplomaAlertOptions"
            @onCancel="closeFormModal(modalDiplomaAlertOptions)"
            @onConfirm="delete_object_confirm"
        />

    </section>
</template>
<script>
import { fabric } from 'fabric';
import panelEditor from './panel_editor.vue';
import DiplomaFormInfoModal from './DiplomaFormInfoModal.vue';
import DiplomaPreviewModal from './DiplomaPreviewModal.vue';
import DiplomaAlertModal from './DiplomaAlertModal.vue';
import DiplomaFormSave from './DiplomaFormSave.vue';

export default {
    components:{
        DiplomaPreviewModal, DiplomaFormInfoModal, DiplomaAlertModal,
        DiplomaFormSave, panelEditor
    },
    props:['modulo_id', 'diploma_id', 'model_id', 'model_type', 'redirect', 'btn_process', 'btn_course'],
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

            base_endpoint:'/diplomas',
            modalDiplomaFormInfoOptions:{
                ref: 'DiplomaFormInfoModal',
                open: false,
                confirmLabel: 'Entendido',
                subTitle:'',
                hideCancelBtn: true,
            },
            modalDiplomaPreviewOptions:{
                ref: 'DiplomaPreviewModal',
                open: false,
                confirmLabel: 'Entendido',
                subTitle:'',
                hideCancelBtn: true,
                resource: {
                    preview: null
                }
            },
            modalDiplomaFormSaveOptions:{
                ref: 'DiplomaFormSaveModal',
                open: false,
                confirmLabel: 'Guardar',
                subTitle: '',
                resource: {
                    preview: null,
                }
            },
            modalDiplomaAlertOptions: {
                ref: 'DiplomaAlertModal',
                open: false,
                confirmLabel: 'Aceptar',
                persistent: true,
                resource:{
                    tipo: null
                }
            }
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
    async mounted(){
        let vue = this;
        this.canvas = new fabric.Canvas('canvas');

        if(vue.diploma_id) await vue.loadData(); // para cargar data al actualizar

        // === añadir puntero a input ===
        const overlayFile = vue.$refs.overlayFile.$el.querySelector('.v-file-input__text');
        const overlayPrependIcon = vue.$refs.overlayFile.$el.querySelector('.v-input__append-inner');
        overlayFile.classList.add('pointer');
        overlayPrependIcon.classList.add('d-none');
        // === añadir puntero a input ===

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
        leavePage() {
            const vue = this
            window.location.href = vue.redirect ? vue.redirect : vue.base_endpoint;
        },
        async openFormModalSave(){
            const vue = this;
            await vue.prev(true);

            if(vue.diploma_id) {
                Object.assign(vue.$refs.DiplomaFormSaveModal.resource, { diploma: vue.edit_plantilla.title});
            }

            const titleLabel = vue.diploma_id ? 'Editar diploma': 'Guardar diploma';
            vue.openFormModal(vue.modalDiplomaFormSaveOptions, null, 'create', titleLabel);
        },
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
            // console.log('add_image', file);
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
        add_image_param(data) {
            const { img, left, top, scaleX, scaleY } = data; // === imagen ===

            let vue = this;
            let file = img;

            let reader = new FileReader();
            reader.onload = function (f) {
                let data = f.target.result;
                fabric.Image.fromURL(data, function (img) {
                    let oImg = img.set({
                        image: true,
                        // === props data ===
                        left,
                        top,
                        scaleX,
                        scaleY,
                        // === props data ===

                        angle: 0,
                        contextMenuImage:true,
                        static:true,
                        lockRotation:true
                    });

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
        add_text_param(data){
            const vue = this;
            const { id: tipo, fill, text, id_formato,
                    fontSize, fontStyle, fontWeight, centrado,
                    top, left, height, width } = data;

            vue.close_context_menu();

            let text2 = new fabric.Text("Text", {
                // === props data ===
                id: tipo,
                fill,
                text,
                id_formato,

                fontSize,
                fontStyle,
                fontWeight,
                centrado,
                top,
                left,
                // === props data ===

                fontFamily:'calisto-mt',
                selectable: true,
                textAlign:'center',
                hasControls: false,
                hasRotatingPoint: false,
                static: false,
                contextMenu: true,
                padding:0,
                charSpacing :0,
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
        add_itext_param(data){
            const vue = this;
            console.log('add_itext_param', data);
            const { fontSize, textAlign,
                    fontStyle, fontWeight,
                    left, top, fill, text } = data;
            vue.close_context_menu();

            let font_size = parseInt(30/this.canvas.getZoom());
            let t = new fabric.IText(text, {
                fontFamily: 'calisto-mt',
                // === props data ===
                fontSize,
                textAlign,
                fontStyle,
                fontWeight,
                left,
                top,
                fill,
                // === props data ===

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

            // console.log('set_plantilla', { clientWidth, clientHeight, file });

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
                    vue.canvas.backgroundColor = '#efefef';
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
        set_plantilla_param(data) {
            let vue = this;
            const { left, top } = data;
            let file = vue.bg_image;

            let reader = new FileReader();
            let clientWidth = document.getElementById('overlay-div').clientWidth;
            let clientHeight = document.getElementById('overlay-div').clientHeight;

            // console.log('set_plantilla_param', { clientWidth, clientHeight, file });

            reader.onload = function (f) {
                let data = f.target.result;
                fabric.Image.fromURL(data, function (img) {
                    let oImg = img.set({
                        id:'bg',

                        // === props data ===
                        left,
                        top,
                        // === props data ===

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
                    vue.canvas.backgroundColor = '#efefef';
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
                // if (confirm(' \n ¿Estas seguro?')) {
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
                // }
            }
        },
        close_context_menu(){
            document.getElementById('input_image').value = null;
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
        delete_object(tipo = 'normal'){
            const vue = this;
            vue.modalDiplomaAlertOptions.resource = { tipo };
            vue.openFormModal(vue.modalDiplomaAlertOptions, null, 'status', 'Eliminar elemento');
        },
        delete_object_confirm(evt) {
            const vue = this;

            if(evt.tipo === 'normal') {
                vue.canvas.remove(this.obj_select);
                vue.close_context_menu();
            }

            if(evt.tipo === 'bg') vue.deleteObjects();

            vue.closeFormModal(vue.modalDiplomaAlertOptions);
        },

        async prev(flag = false){
            let data = {
                'info': this.canvas.toJSON(['id','static','x','y','width','height','centrado','id_formato','zoomX']),
                'zoom':this.canvas.getZoom(),
            };
            this.name_plantilla = null;

            this.showLoader();
            await axios.post('/diplomas/get_preview_data',data).then((res) => {

                this.preview = res.data.preview;
                this.modalDiplomaPreviewOptions.resource = res.data;
                this.modalDiplomaFormSaveOptions.resource = res.data;

                if(!flag) {
                    this.openFormModal(this.modalDiplomaPreviewOptions, null, 'status', 'Previzualizar diploma');
                }

                this.hideLoader();
            }).catch(e=>{
                this.hideLoader();
            });
        },
        save_plantilla(){
            const vue = this;

            const title_plantilla = vue.$refs.DiplomaFormSaveModal.resource.diploma;
            // if(!title_plantilla) return;
            // if(title_plantilla.length <= 10) return;

            let data = {
                'info': this.canvas.toJSON(['id','static','x','y','width','height','centrado','id_formato','zoomX']),
                // 'nombre_plantilla': this.name_plantilla,
                'nombre_plantilla': title_plantilla,
                'model_id': vue.model_id,
                'model_type': vue.model_type
            };

            vue.showLoader();

            if(vue.diploma_id) {
                data = { ...data, edit_plantilla: vue.edit_plantilla }
                // === actualizar ===
                axios.put('/diplomas/update/'+vue.diploma_id, data).then((res) => {

                    vue.d_preview = false;
                    vue.dialog_save = false;

                    vue.hideLoader();

                    if(res.data.error) {
                        vue.showAlert('Hubo un problema al actualizar el diploma.', 'error');
                    } else {
                        vue.showAlert('El diploma fue actualizado correctamente.');
                        setTimeout(() => vue.leavePage(), 1500);
                    }
                });

            } else {
                // === guardar ===
                axios.post('/diplomas/save', data).then((res)=>{
                    this.d_preview = false;
                    this.dialog_save = false;
                    let vue = this;
                    vue.queryStatus("diploma", "crear_diploma");
                    vue.hideLoader();

                    if(res.data.error) {
                        vue.showAlert('Hubo un problema al guardar el diploma.', 'error');
                    } else {
                        vue.showAlert('El diploma fue guardado correctamente.');
                        setTimeout(() => vue.leavePage(), 1500);
                    }
                });
            }
        },
        zoom(tipo){
            let vue = this;
            let zoom = vue.canvas.getZoom();
            zoom = (tipo=='plus') ? zoom + 0.1 : zoom - 0.1 ;
            vue.canvas.setZoom(zoom);
        },
        async base64ToFile(base64) {
            const imagePromise = await fetch(base64);
            const imageBlob = await imagePromise.blob();

            return new File([imageBlob], 'image', { type: 'image/png' });
        },
        async loadData() {
            const vue = this;
            vue.showLoader();

            const response = await axios.get('/diplomas/search/'+vue.diploma_id)
            const { plantilla, diploma } = response.data;
            const { entries, assign } = Object;
            assign(this.edit_plantilla, diploma); // asignar diploma
            const { s_objects_images, s_object_bg, s_objects_text } = diploma;

            // === renderizar fondo plantilla ===
            const bgFile = await vue.base64ToFile(s_object_bg.src);
            vue.bg_image = bgFile;
            vue.set_plantilla_param({ ...s_object_bg });

            // === renderizar imagenes ===
            for(const [, image] of entries(s_objects_images)) {
                const imageFile = await vue.base64ToFile(image.src);
                vue.add_image_param({ img: imageFile, ...image });
            }

            // === renderizar textos "editables" ===
            for(const [, text] of entries(s_objects_text)) vue.add_itext_param(text);

            // === renderizar textos "no - editables" ===
            const d_objects = JSON.parse(diploma.d_objects);
            for(const text of d_objects) vue.add_text_param(text);

            // === renderizar plantilla completa ===
            const plantillaFile = await vue.base64ToFile(plantilla);

            vue.hideLoader();

            vue.d_btn = false;
            vue.dialog_plantillas = false;
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

.img-preview {
    width: 100%;
    height: 25rem;
    object-fit: contain;
    background-color: #f8f8fb;
}

.cont{
    border-radius: 5px;
    background: #fff;
    z-index: 10;
    display: inline-block;
}

.c_menu{
    background: #fff;
    position: absolute;
    display: flex;
    bottom: 0;
    flex-direction: row;
    right: 50%;
    transform: translate(50%, -50%);
    z-index:10;
}

.input_img {
    display: none;
}

.overlay-div{
    position: relative;
    display: flex;
    width: 850px;
/*    height: auto;*/
    background-color: #ffffff;
    border-radius: .5rem;
}

.overlay-div:hover{
    background-color: #ffffff;
}

.overlay-div:hover .overlay-icon{
    color: #5458ea !important;
}

.overlay-div .v-input__slot::before,
.overlay-div .v-input__slot::after {
    transition: none !important;
    width: 0 !important;
}

.overlay-icon {
    position: absolute;
    pointer-events: none;
    color: #A9A9A9;
    width: auto;
    top: 36%;
    left: 40%;
    background-color: #ffffff;
    padding: 1rem 1.5rem;
    border-radius: .5rem;
}

.menu-save{
    position: relative;
    margin-top: 1.5rem;
}

.menu-save-btn {
    position: absolute;
    right: 1rem;
}

.menu-cancel-btn{
    position: absolute;
    right: 10rem;
}

.container-box  {
    height: 450px;
    background-color: #D4D4D4;
    border-radius: 0.5rem;
}

.canvas-style {
    margin: auto !important;
    border-radius: 0.5rem;
    height: auto !important;
}

canvas {
    padding-left: 0;
    padding-right: 0;
    margin-left: auto;
    margin-right: auto;
    display: block;
}

</style>
