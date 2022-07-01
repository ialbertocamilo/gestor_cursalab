<template>
    <v-row >
        <v-col cols="12" md="5" height="200px">
                <v-card-title>Puntos a tomar en cuenta: </v-card-title>
                <v-card-text>
                    <ul>
                        <li>
                            Indicaciones: <br> 
                            <ul style="list-style: none;" class="mt-2">
                                <li class="ml-2 my-0">
                                    Las Escuelas deben estar creadas previamente.
                                </li>
                                <li class="ml-2 my-0">
                                    El nombre de módulo, escuela y curso deben ser iguales a los existentes en la plataforma.
                                </li>
                                <li class="ml-2 my-0">
                                    Los campos en verde son obligatorio y los de amarillo son opcionales.
                                </li>
                                <li class="ml-2 my-0">
                                    El estado solo debe tener un valor "Activo" o "Inactivo".
                                </li>
                            </ul>
                        <li>
                            <v-card-actions @click="show = !show">
                                <span>Nombres de hojas:</span>
                                <v-btn
                                    icon
                                >
                                    <v-icon>{{ show ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                                </v-btn>
                            </v-card-actions>
                            <v-expand-transition>
                                <div v-show="show">
                                    <v-card-text>
                                        <span class="ml-2 my-0"><b>Cursos:</b> Carga de Cursos</span><br> 
                                        <span class="ml-2 my-0"><b>Temas:</b> Carga de Temas</span><br> 
                                        <!-- <span class="ml-2 my-0"><b>Evaluaciones:</b> Carga Evaluaciones</span>  -->
                                    </v-card-text>
                                </div>
                            </v-expand-transition>
                        </li>
                        <li>
                            <v-card-actions @click="show2 = !show2">
                                <span>Columnas del excel:</span>
                                <v-btn
                                    icon
                                >
                                    <v-icon>{{ show2 ? 'mdi-chevron-up' : 'mdi-chevron-down' }}</v-icon>
                                </v-btn>
                            </v-card-actions>
                            <v-expand-transition>
                                <div v-show="show2">
                                    <v-card-text>
                                        <span class="ml-2 my-0"><b>Cursos:</b> Módulo, Nombre de la Escuela, Nombre del Curso, Estado, Imagen</span><br> 
                                        <span class="ml-2 my-1"><b>Temas:</b> Módulo, Nombre de la Escuela, Nombre del Curso, Nombre del Tema, Requisito del Tema, Contenido, Estado</span><br> 
                                        <!-- <span class="ml-2 my-0"><b>Evaluaciones:</b> Carga Evaluaciones</span>  -->
                                        <!-- <span class="ml-2 my-1"><b>Evaluaciones:</b> Módulo, Nombre del Curso, Tema, Pregunta, Alternativa 1, Alternativa 2, Alternativa 3, Alternativa 4, Alternativa 5, Alternativa 6, Alternativa 7, Respuesta Correcta, Estado</span>  -->
                                    </v-card-text>
                                </div>
                            </v-expand-transition>
                        </li>
                        <li>Los errores encontrados en la subida masiva las podrás descargar desde el botón "Descargar errores" 👇</li>
                    </ul>
                </v-card-text>
                <v-card-actions class="d-flex justify-center">
                    <modalErrores :q_error="q_error" tipo="cursos"></modalErrores>
                    <!-- <v-btn depressed color="error" :disabled="!(q_error>0)" @click="descargar_reporte">Descargar errores</v-btn> -->
                </v-card-actions>
        </v-col>
        <v-col cols="12" md="7" class="d-flex flex-column justify-content-center">
            <v-row justify="center">
                <v-overlay
                    class="custom-overlay"
                    color="#796aee"
                    opacity="0.75"
                    :value="loading_guardar"
                    style="z-index:37"
                >
                    <div
                        style="display: flex; flex-direction: column; align-items: center"
                        class="text-center justify-center overlay-curricula"
                    >
                        <v-progress-circular indeterminate size="64"></v-progress-circular>
                            <p class="text-h6" v-if="loading_guardar">
                                Este proceso puede tomar más de un minuto, espere por favor.
                            </p>
                            <p class="text-h6" v-if="loading_guardar">No actualice la página.</p>
                        </div>
                    </v-overlay>
                </v-row>
            <v-row>
                <v-col
                    cols="12"
                    sm="12"
                    md="12"
                    class="d-flex align-items-center justify-content-center ml-8 mt-0"
                >
                    <v-checkbox
                        class="mr-8"
                        v-for="tipo in tipos"
                        :key="tipo.id"
                        color="red"
                        :label="tipo.nombre"
                        :value="tipo.nombre"
                        v-model="s_tipo"
                        persistent-hint
                    ></v-checkbox>
                </v-col>
            </v-row>
            <v-row class="d-flex justify-content-center my-2">
                <vuedropzone @emitir-archivo="cambio_archivo" @emitir-alerta="enviar_alerta" />
            </v-row>
            <v-row class="d-flex justify-content-center">
                <v-card-actions>
                    <v-btn color="success" @click="enviar_archivo()">Subir</v-btn>
                </v-card-actions>
            </v-row>
        </v-col>
    </v-row>
</template>
<script>
    import vuedropzone  from "./../dropzone.vue";
    import modalErrores from "./../ModalErrores"
    export default {
        props:['q_error'],
        components:{vuedropzone,modalErrores},
        data () {
            return {
                s_check:[''],
                tipos:[
                    {id:1,'nombre':'cursos'},
                    {id:2,'nombre':'temas'},
                    // {id:3,'nombre':'evaluaciones'},
                ],
                s_tipo:[],
                archivo:null,
                loading_guardar:false,
                show: false,
                show2: false,
            }
        },
        methods:{
            cambio_archivo(res){
                this.archivo = res;
            },
            enviar_archivo(){
                let validar_data = this.validar_data();
                if(validar_data){
                    this.loading_guardar = true;
                    let data = new FormData();
                    data.append("tipos", this.s_tipo);
                    data.append("file_cursos", this.archivo);
                    axios.post('/masivo/subir_cursos',data).then((res)=>{
                        this.loading_guardar = false;
                        let info = res.data.info;
                        if(res.data.error){
                            this.$emit("update_q_error",{
                                    tipo:'cursos',
                                    q_error: res.data.q_error
                                }
                            );
                        }
                        this.enviar_alerta(info);
                    }).catch(err=>{
                        this.loading_guardar = false;
                    });
                }
            },
            enviar_alerta(info){
                this.$emit("emitir-alert",info );
            },
            validar_data(){
                if(!this.s_tipo.length){
                    this.enviar_alerta('Seleccione un tipo de subida');
                    return false;
                }
                if(!this.archivo){
                    this.enviar_alerta('No ha seleccionado ningún archivo');
                    return false;
                }
                return true ;
            },
            descargar_reporte(){
        	    window.open('/masivo/reporte_errores/curs_tema_eva').attr("href");
		    }
        }
    }
</script>
<style>
.v-input__slot{
    display: flex;
    align-items: initial !important;
}
</style>