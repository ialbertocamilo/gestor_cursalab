<template>
    <v-dialog
        v-model="dialog"
        :max-width="(v_change_estado === 'solucionado')? 800 : 260"
        @click:outside="closeDialog"
    >
        <v-card>
            <v-card-title class="text-h5">
                <v-row>
                    <v-col cols="12" :md="(v_change_estado === 'solucionado') ? '4' : '12' ">
                        <v-select
                            :items="change_estados"
                            label="Estado"
                            hide-details
                            single-line
                            v-model="v_change_estado"
                            @change="$emit('update:v_change_estado', v_change_estado)"
                        ></v-select>
                    </v-col>
                </v-row>
            </v-card-title>
            <v-card-text>
                <v-form ref="form_mensaje">
                    <v-row>
                            <v-col cols="12" md="6">
                                <v-textarea
                                    v-if="v_change_estado === 'solucionado'"
                                    :rules="[v => !!v || 'Es necesario el mensaje de soporte']"
                                    name="input-7-1"
                                    label="Información Soporte"
                                    v-model="send_data.info_soporte"
                                ></v-textarea>
                            </v-col>
                            <v-col cols="12" md="6">
                                <v-textarea
                                    :rules="[v => !!v || 'Es necesario el mensaje']"
                                    v-if="v_change_estado === 'solucionado'"
                                    name="input-7-1"
                                    label="Mensaje al usuario"
                                    v-model="send_data.msg_to_user"
                                ></v-textarea>
                            </v-col>
                    </v-row>
                </v-form>
            </v-card-text>    
            <v-card-actions>
            <v-spacer></v-spacer>
            <v-btn
                text
                @click="closeDialog()"
            >
                Cancelar
            </v-btn>
            <v-btn
                color="primary"
                @click="change_estado()"
            >
                Enviar
            </v-btn>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>

export default ({
    props: ['v_change_estado','dialog','id_change'],
    data(){
        return {
            change_estados:['pendiente','revisando','solucionado'],
            send_data:{
                info_soporte:'',
                msg_to_user:'',
                id_change:0,
            },
        }
    },
    mounted() {
        console.log(this.change_estado);
    },
    methods: {
        // open_dialog(item){
        //     this.dialog=true;
        //     this.v_change_estado = item.estado,
        //     this.send_data.id_change = item.id,
        //     this.send_data.info_soporte = '';
        //     this.send_data.msg_to_user = '';
        // },
        change_estado(){
            let data = {
                'id':this.id_change,
                'estado':this.v_change_estado,
                'info_soporte':this.send_data.info_soporte,
                'msg_to_user':this.send_data.msg_to_user,
            };
            if(data.estado=='solucionado'){
                let validate = this.$refs.form_mensaje.validate()
                if(!validate) {return false}
           
           }
           console.log(data);
            axios.post('/usuario_ayuda/changeEstado',data).then(res =>{
                this.$emit('close-dialog',true);
            })
        },
        closeDialog(){
            this.$emit('close-dialog');
        }
    }
})
</script>
