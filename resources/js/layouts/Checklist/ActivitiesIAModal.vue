<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                   <v-row>
                        <v-col cols="12" >
                            <div class="d-flex justify-space-between mx-10" v-for="(file, index) in files" :key="index">
                                <li>{{ file.title }} ({{ file.filesize }} MB)</li>
                                <v-icon @click="deleteResource(index)">mdi mdi-delete</v-icon>
                            </div>
                        </v-col>
                       <v-col cols="12" class="d-flex justify-content-center">
                            <input
                                type="file"
                                ref="fileInput"
                                style="display: none;"
                                @change="handleFileChange"
                                multiple
                            />
                            <DefaultModalButton
                                outlined
                                :label="'Subir archivo multimedia para transcripción'"
                                icon_name="mdi-folder-upload"
                                @click="openFileDialog"
                            />
                        </v-col>
                   </v-row>
            </template>
        </DefaultDialog>
        <DefaultDialog :options="modalConfirmOptions" width="46vw" @onCancel="closeSimpleModal(modalConfirmOptions)" @onConfirm="generateActivities()">
            <template v-slot:content>
                   <v-row>
                        <v-col cols="12">
                            <DefaultModalSectionExpand title="1. ¿Cómo funciona?"
                                    :expand="sections.showHowWorks" :simple="true">
                                    <template slot="content">
                                        <ul>
                                            <li>Crea la actividades para tu checklist con Inteligencia Artificial.</li>
                                            <li>Puedes elegir la cantidad de actividades que deseas que contenga tu checklist.</li>
                                            <li>Al dar clic en el botón Generar evaluación se crearán las actividades según la configuración indicada.</li>
                                            <li>Podrás editar, eliminar y seleccionar las actividades de tu cheklist luego de generarse.</li>
                                        </ul>
                                    </template>
                            </DefaultModalSectionExpand>
                        </v-col>
                        <v-col cols="12">
                            <DefaultModalSectionExpand title="2. Indica la cantidad de actividades"
                                    :expand="sections.configuration" :simple="true">
                                    <template slot="content">
                                        <v-form ref="formActivityIA" class="d-flex align-items-center">
                                            <span>
                                                Selecciona la cantidad de actividades a generar en tu checklist 
                                            </span>
                                            <span class="mr-4">Total</span>
                                            <DefaultInput
                                                clearable 
                                                dense
                                                numbersOnly
                                                max="2"
                                                v-model="number_activities"
                                            />
                                        </v-form>
                                    </template>
                            </DefaultModalSectionExpand>
                        </v-col>
                   </v-row>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            resourceDefault: {
            },
            resource: {
            },
            selects: {
                courses: [],
            },
            rules: {
            },
            files:[
                
            ],
            sections:{
                showHowWorks:{ status: true },
                configuration:{ status: true }
            },
            number_activities:10,
            modalConfirmOptions:{
                ref: 'ActvitiesConfirmIAFormModal',
                open: false,
                persistent: true,
                base_endpoint: "/entrenamiento/checklist/v2",
                confirmLabel: "Generar actividades con AI",
                cancelLabel:'Resetear',
                resource: "checklist",
                title:'Selecciona los cursos para conseguir información'
            }
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
        }
        ,
        confirmModal(){
            let vue = this;
            vue.openSimpleModal(vue.modalConfirmOptions);
        },
        async generateActivities() {
            let vue = this;
            vue.showLoader();
            let formData = new FormData();
            vue.files.map((file) => {
                formData.append("files[]", file);
                formData.append("number_activities", vue.number_activities);
            })
            await vue.$http.post('/jarvis/generate-checklist',formData).then(({data})=>{
                vue.hideLoader();
                vue.$emit('activities',data.data);
            })
            // vue.$emit('onConfirm')
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this

        },
        async loadSelects() {
            let vue = this;
        },
        openFileDialog() {
            this.$refs.fileInput.click();
        },
        handleFileChange(event) {
            let vue = this;
            let file = event.target.files[0];
            file.filesize = vue.roundTwoDecimal(file.size / 1024 ** 2);
            if (file.filesize > 2) {
                vue.showAlert(`El limite máximo por archivo es de 2 MB`, 'warning');
                return true;
            }
            file.type_resource = 'file';
            file.title = file.name;
            vue.files.push(file);
        },
        deleteResource(index) {
            this.files.splice(index, 1);
        }
        // getFileSize(bytes) {
        //     const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        //     if (bytes === 0) return '0 Byte';
        //     const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
        //     return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
        // }
    }
}
</script>
