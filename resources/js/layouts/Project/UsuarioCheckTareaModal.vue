<template>
    <DefaultDialog
        :options="options"
        width="30vw"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        :showCardActions="options.showCardActions"
        :showTitle="options.showTitle"
    >
        <template v-slot:content>
             <v-form ref="CheckTareaForm">
                <v-row>
                    <v-col cols="12">
                        <span v-if="description" class="pl-1 pb-1" style="color: gray;" v-text="description"></span>
                        <DefaultSelect
                            dense
                            label="Calificar"
                            v-model="usuario_tarea.status_id"
                            itemText="name"
                            itemValue="id"
                            :items="selects.status"
                            :rules="rules.estado"
                            @onChange="setDescription"
                        />
                    </v-col>
                    <v-col cols="12">
                        <DefaultTextArea 
                            label="Mensaje al usuario (opcional)"
                            v-model="usuario_tarea.msg_to_user"
                            counter
                        />
                    </v-col>
                </v-row>
            </v-form>
        </template>
    </DefaultDialog>
</template>
<script>
import DefaultTextArea from '../../components/globals/DefaultTextArea.vue';
export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            selects: {
                status: []
            },
            usuario_tarea: {
                status_id: null,
                msg_to_user:'',
            },
            usuario_tarea_id:'',
            description:'',
            rules: {
                estado:this.getRules(["required"]),
                msg_to_user: this.getRules(['only_max:255']),
            }
        };
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.resetSelects();
            vue.$emit("onCancel");
        },
        async confirmModal() {
            let vue = this;
            const validateForm = vue.validateForm("CheckTareaForm");
            console.log(vue.usuario_tarea);
            if (validateForm) {
                axios.post(`/tareas/${vue.usuario_tarea_id}/update-usuario-tarea`,vue.usuario_tarea).then((e)=>{
                    vue.queryStatus("tareas", "calificar_tarea");
                    vue.showAlert('Tarea actualizada correctamente.','success');
                    vue.$emit("onConfirm");
                }).catch((error)=>{
                    vue.show_http_errors(error);
                })
            }
        },
        resetSelects() {
            let vue = this;
            vue.usuario_tarea.status_id = null;
            vue.usuario_tarea.msg_to_user='';
            vue.usuario_tarea_id = null;
            vue.description='';
        },
        resetValidation() {
            let vue = this;
        },
        async loadData({usuario_tarea_id,msg_to_user,status_tarea_id}) {
            let vue = this;
            vue.resetSelects();
            (status_tarea_id) && (vue.usuario_tarea.status_id = status_tarea_id);
            vue.usuario_tarea_id = usuario_tarea_id;
            vue.usuario_tarea.msg_to_user = msg_to_user; 
        },
        loadSelects() {
            let vue = this;
            axios.get('/projects/users/status-list/select').then(({data})=>{
                vue.selects.status = data.data;
                (vue.usuario_tarea.status_id) && vue.setDescription(vue.usuario_tarea.status_id);
            }) 
        },
        setDescription(val){
            let vue = this;
            const status = vue.selects.status.find(status=>status.id == val); 
            (status) && (vue.description = status.description); 
        }
    },
    components: { DefaultTextArea }
}
</script>
<style>
    .notificationCenter {
        z-index:1000 !important;
    }
</style>