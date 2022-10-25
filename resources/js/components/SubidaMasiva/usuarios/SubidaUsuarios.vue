<template>
    <v-row >
        <v-col cols="12" md="5" height="200px">
                <v-card-title>Descripción: </v-card-title>
				<v-card-text class="py-0">
					<!-- Se crea o actualiza los datos de usuarios según el valor de la columna de acción. -->
                    Se crea o actualiza a los usuarios según los datos indicados en el excel.
				</v-card-text>
                <v-card-title>Puntos a tomar en cuenta: </v-card-title>
                <v-card-text>
                    <ul>
                        <li class="mt-2">
                            <b>Las columnas número de teléfono y email son opcionales</b>
                        </li>
                        <li class="mt-2">
                            <b>La columna estado toma los valores Active(usuario activo) , Inactive (usuarios inactivo)</b> 
                        </li>
                        <li class="mt-2">
                            <b>Los criterios tipo fecha deben tener el formato (Y/m/d) o (d/m/Y)</b>
                        </li>

                    </ul>
                </v-card-text>
                <!-- <v-card-actions class="d-flex justify-center">
					<modalErrores :q_error="q_error" tipo="usuarios"></modalErrores>
				</v-card-actions> -->
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
	import modalErrores from "./../ModalErrores.vue"
    export default {
        props:['q_error'],
        components:{vuedropzone,modalErrores},
        data () {
            return {
                archivo:null,
                loading_guardar:false,
            }
        },
        methods:{
            cambio_archivo(res){
                this.archivo = res;
            },
            async enviar_archivo(){
                let validar_data = this.validar_data();
                if(validar_data){
                    this.loading_guardar = true;
                    let data = new FormData();
                    data.append("file", this.archivo);
                    // console.log(data);
                    await axios.post('/masivos/create-update-users',data).then((res)=>{
                        this.loading_guardar = false;
                        const data = res.data.data;
                        if(data.errores.length > 0){
                            this.$emit("download-excel-observations",{
                                    errores:data.errores,
                                    headers:data.headers
                                }
                            );
                        }
                        const message = ` <ul>
                            <li>${data.message}</li>
                            <li>Cantidad de usuarios creados/actualizados: ${data.datos_procesados || 0}</li>
                            <li>Cantidad de usuarios con observaciones: ${data.errores.length || 0}</li>
                        </ul>`
                        this.enviar_alerta(message);
                    }).catch(err=>{
                        this.enviar_alerta(err.response.data.message);
                        this.loading_guardar = false;
                    });
                }
            },
            enviar_alerta(info){
                this.$emit("emitir-alert",info );
            },
            validar_data(){
                if(!this.archivo){
                    this.enviar_alerta('No ha seleccionado ningún archivo');
                    return false;
                }
                return true ;
            },
            descargar_reporte(){
        	    window.open('/masivo/reporte_errores/carreras').attr("href");
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