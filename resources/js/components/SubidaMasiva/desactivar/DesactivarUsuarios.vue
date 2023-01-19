<template>
    <v-row >
        <v-col cols="12" md="5" height="200px">
                <v-card-title>Descripción: </v-card-title>
				<v-card-text class="py-0">
					Se cambia el estado del usuario a 0(inactivo).
				</v-card-text>
                <v-card-title>Puntos a tomar en cuenta: </v-card-title>
                <v-card-text>
                    <ul>
                        <li class="mt-2">
                            <b>La cantidad máxima de filas por excel es de 2500.</b>
                        </li>
                        <li class="mt-2">
                            <b>Columnas del excel:</b>DNI - Fecha de cese (opcional).
                        </li>
                        <li class="mt-2">
                            <b>La fecha de cese debe tener el formato (yyyy/mm/dd) o (dd/mm/yyyy).</b>
                        </li>
                    </ul>
                </v-card-text>
        </v-col>
        <v-col cols="12" md="7" class="d-flex flex-column justify-content-center">
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
    const percentLoader = document.getElementById('percentLoader');
    export default {
        props:['q_error'],
        components:{vuedropzone},
        data () {
            return {
                archivo:null,
            }
        },
        methods:{
            cambio_archivo(res){
                this.archivo = res;
            },
            enviar_archivo(){
                let vue = this;
                let validar_data = this.validar_data();
                if(validar_data){
                    this.showLoader();
                    let data = new FormData();
                    data.append("file", this.archivo);
                    data.append("number_socket", this.number_socket || null);
                    percentLoader.innerHTML = ``;
                    axios.post('/masivos/inactive-users',data).then((res)=>{
                        const data = res.data.data;
                        if(data.errores.length > 0){
                            let headers = data.headers;
                            let values =Object.keys(data.errores[0]);
                            this.descargarExcelFromArray(
                                headers,
                                values,
                                data.errores,
                                "No procesados_" + Math.floor(Math.random() * 1000),
                                "Se han encontrado observaciones. ¿Desea descargar lista de observaciones?"
                            );
                        }
                        const message = ` <ul>
                            <li>${data.message}</li>
                            <li>Cantidad de usuarios inactivados: ${data.datos_procesados || 0}</li>
                            <li>Cantidad de usuarios con observaciones: ${data.errores.length || 0}</li>
                        </ul>`
                        vue.queryStatus("subida_masiva", "activar_proceso");
                        this.hideLoader();
                        this.enviar_alerta(message);
                    }).catch(err=>{
                        this.hideLoader();
                        this.enviar_alerta(err.response.data.message);
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
