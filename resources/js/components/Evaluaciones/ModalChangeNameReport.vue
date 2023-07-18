<template>
    <v-dialog
            v-model="filenameDialog"
            width="600"
            persistent
        >
        <v-card class="p-4">
            <v-text-field
                v-model="data.new_name"
                label="Nombre de archivo del reporte"
                required
            ></v-text-field>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                <DefaultModalActionButton
                    :cancel-label="'Cancelar'"
                    :confirm-label="'Descargar'"
                    @cancel="closeModal"
                    @confirm="saveReport"/>
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>
<script>
    export default{
        props:{
            filenameDialog:{
                default:''
            },
            data:{
                default:{}
            }
        },
        methods:{
            saveReport(){
                this.$emit('saveReport',this.data);
            },
            closeModal(){
                this.$emit('closeModal');
            }
        }
    }
</script>