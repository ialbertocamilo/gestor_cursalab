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
                            v-model="project_user.status_id"
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
                            v-model="project_user.msg_to_user"
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
            project_user: {
                status_id: null,
                msg_to_user:'',
            },
            project_user_id:'',
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
            console.log(vue.project_user);
            if (validateForm) {
                axios.put(`/projects/${vue.project_user_id}/update-user-project`,vue.project_user).then((e)=>{
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
            vue.project_user.status_id = null;
            vue.project_user.msg_to_user='';
            vue.project_user_id = null;
            vue.description='';
        },
        resetValidation() {
            let vue = this;
        },
        async loadData({project_user_id,msg_to_user,status_project_id}) {
            let vue = this;
            vue.resetSelects();
            (status_project_id) && (vue.project_user.status_id = status_project_id);
            vue.project_user_id = project_user_id;
            vue.project_user.msg_to_user = msg_to_user; 
        },
        loadSelects() {
            let vue = this;
            axios.get('/projects/users/status-list/select').then(({data})=>{
                vue.selects.status = data.data;
                (vue.project_user.status_id) && vue.setDescription(vue.project_user.status_id);
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