<template>
    <div class="cont text-center d-flex align-center justify-space-around" style="grid-gap: 0.6rem;">
        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Nombre del curso"
             v-if="(btn_course === 'true')">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_add_text('Nombre del Curso', false, 'courses', 1)"
                    text>
                <v-icon>mdi-notebook</v-icon>
            </v-btn>
        </div>
        
        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Nombre del proceso"
             v-if="(btn_process === 'true')">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_add_text('Nombre del Proceso', false, 'processes', 1)"
                    text>
                <v-icon>mdi-notebook</v-icon>
            </v-btn>
        </div>
        
        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Nombre del usuario">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_add_text('Nombre del usuario', false, 'users', 1)"
                    text>
                <v-icon>mdi-account</v-icon>
            </v-btn>
        </div>
        
        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Fecha">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_add_text('14/03/2023', false, 'fecha', '%d/%m/%Y')"
                    text>
                <v-icon>mdi-calendar</v-icon>
            </v-btn>
        </div>

        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Nota del curso">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_add_text('15', false, 'course-average-grade', 1, 76)"
                   text>
                <v-icon>mdi-numeric-9-plus-circle-outline</v-icon>
            </v-btn>
        </div>

        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Texto">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_add_itext()"
                    text>
                <v-icon>mdi-format-text</v-icon>
            </v-btn>
        </div>
        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Fuente de Texto">
            <DefaultAutocomplete
                :items="fonts"
                label="Fuente de texto"
                v-model="font_id"
                item-text="name"
                item-value="id"
                dense
                @input="changeFont"
                :disabled="d_btn"
                :openUp="true"
            />
        </div>

        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Imagen">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="click_input('input_image')"
                    text>
                <v-icon>mdi-image</v-icon>
            </v-btn>
            <input @change="emitir_add_image()" class="input_img" type="file" id="input_image">
        </div>
        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="QR de verificación">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_add_qr_image()"
                    text>
                <v-icon>mdi-qrcode</v-icon>
            </v-btn>
        </div>
        <!-- <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Previsualización">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_prev()" text>
                <v-icon>mdi-eye</v-icon>
            </v-btn>
        </div>
        -->
        <div class="css-tooltip css-tooltip--bottom"
             data-tooltip="Eliminar la plantilla">
            <v-btn class="btn-panel-editor" elevation="2" :disabled="d_btn" @click="emitir_delete()" text>
                <v-icon>mdi-delete</v-icon>
            </v-btn>
        </div>
    </div>
</template>
<script>

export default {
    props: {
        d_btn: {
            type: Boolean,
            default: false
        },
        btn_process: {
            type: String,
            default: 'false'
        },
        btn_course: {
            type: String,
            default: 'true'
        },
        fonts:{
            type: Array,
            default:[]
        }
    },
    data(){
        return {
            font_id:null
        }
    },
    methods: {
        emitir_add_text(text,hasControls,tipo,id_formato,font_size){
            this.$emit("emit_add_text", text,hasControls,tipo,id_formato,font_size);
        },
        emitir_add_itext(){
            this.$emit("emit_add_itext");
        },
        emitir_add_image(){
            this.$emit("emit_add_image");
        },
        emitir_add_qr_image(){
            this.$emit("emitir_add_qr_image");
        },
        emitir_prev(){
            this.$emit("emit_prev");
        },
        changeFont(font_id){
            console.log('font_id,',font_id);
            this.$emit("emit_change_font",font_id);
        },
        emitir_delete(){
            this.$emit("emit_delete",'bg');
        },
        click_input(id){
            document.getElementById(id).click();
        },
    },
}
</script>
<style>
    .btn-panel-editor{
        color:#707070 !important;
    }

    .btn-panel-editor:hover{
        color:#5457E7 !important;
    }
</style>
