<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                   <v-row>
                        <v-col cols="12">
                            <span>Brinda información para poder realizar las evaluaciones </span>
                        </v-col>
                        <v-col cols="12" class="d-flex align-items-center">
                            <DefaultInput 
                                dense
                                clearable
                                v-model="course_name"
                                label="Nombre de curso"
                                :loading="searching_course"
                                @input="handleInput"
                            />
                            <v-spacer></v-spacer>
                            <span class="mr-2 text-bold">{{ courses_selected.length }} /{{ limits.quantity }} seleccionados</span>
                        </v-col>
                        
                        <v-col cols="12" >
                            <DefaultSimpleSection title="Resultados de la búsqueda" marginy="my-1" marginx="mx-0" style="min-height: 100px;">
                                <template slot="content">
                                    <div v-for="(course,index) in courses_searched" :key="index" class="d-flex align-items-center mx-4">
                                        <span>{{ course.name }}</span>
                                        <v-spacer></v-spacer>
                                        <span class="mx-4">{{ course.medias_count }} archivo(s) multimedia transcritos</span>
                                        <v-btn :disabled="courses_selected.length >= limits.quantity " icon>
                                            <v-icon class="icon_size pb-2" small color="black"
                                                
                                                    style="font-size: 1.25rem !important;" @click="addCourse(index,course)">
                                                mdi-plus-circle
                                            </v-icon>
                                        </v-btn>
                                    </div>
                                </template>
                            </DefaultSimpleSection>
                        </v-col>
                        <v-col cols="12" >
                            <DefaultSimpleSection title="Cursos y archivos seleccionados" marginy="my-1" marginx="mx-0" style="min-height: 100px;">
                                <template slot="content">
                                    <div v-for="(course,index) in courses_selected" :key="index"  class="d-flex align-items-center mx-4">
                                        <span :style="{ color: course.type_resource !== 'course' ? '#5458EA' : '' }">
                                            {{ course.name }}
                                            <i v-if="course.type_resource !== 'course'" class="mdi mdi-folder-upload"></i>
                                        </span>
                                        <v-spacer></v-spacer>
                                        <span class="mx-4">{{  
                                            course.type_resource == 'course' 
                                                ? course.medias_count +' archivo(s) multimedia transcritos'
                                                : 'Transcribiendo archivo...' }}
                                        </span>
                                        <v-icon class="icon_size pb-2" small color="black"
                                                style="font-size: 1.25rem !important;" @click="removeCourse(index,course)">
                                            mdi-delete
                                        </v-icon>
                                    </div>
                                </template>
                            </DefaultSimpleSection>
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
                                :disabled="courses_selected.length >= limits.quantity "
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
            },
            course_name:'',
            searching_course:false,
            courses_searched : [],
            courses_selected:[],
            limits:{
                quantity:4
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
            vue.courses_selected.filter( cs => cs.type_resource == 'file').map((file) => {
                formData.append("files[]", file);
            })
            vue.courses_selected.filter( cs => cs.type_resource == 'course').map((course) => {
                formData.append("course_ids[]", course.id);
            })
            formData.append("number_activities", vue.number_activities);
            await vue.$http.post('/jarvis/generate-checklist',formData).then(({data})=>{
                vue.hideLoader();
                vue.courses_selected = [];
                vue.courses_searched = [];
                vue.modalConfirmOptions.open=false;
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
            vue.courses_selected.push(file);
        },
        handleInput: _.debounce(function() {
            let vue = this;
            if(vue.course_name){
                vue.searchCourses();
            }
        }, 500),
        async searchCourses(){
            let vue = this;
            vue.searching_course = true;
            await vue.$http.get('/jarvis/search-courses-transcribed?name='+ vue.course_name).then(({data})=>{
                vue.courses_searched = data.data;
            })
            vue.searching_course = false;
        },
        addCourse(index,course){
            let vue = this;
            if(vue.courses_selected.findIndex(cs => cs.id == course.id) > -1){
                vue.showAlert('Este curso ya ha sido seleccionado','warning');
                return;
            }
            vue.courses_searched.splice(index, 1);
            course.type_resource='course';
            vue.courses_selected.push(course);
        },
        removeCourse(index,course){
            let vue = this;
            vue.courses_selected.splice(index, 1);
            if( course.type_resource == 'course'){
                vue.courses_searched.push(course);
            }
        }
    }
}
</script>
