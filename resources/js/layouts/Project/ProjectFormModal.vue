<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="projectForm">
                <p>Los proyectos serán asignados a todos los usuarios segmentados en el curso.</p>
                <v-row justify="space-around">
                    <v-col cols="12">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.modules"
                            v-model="resource.module_id"
                            label="Módulo"
                            item-value='id'
                            item-text='name'
                            :rules="rules.required"
                            @onChange="getSchoolsByModule()"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="12">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.schools"
                            v-model="resource.school_id"
                            label="Escuela"
                            item-value='id'
                            item-text='name'
                            :rules="rules.required"
                            @onChange="getCoursesBySchool()"
                            :disabled="!resource.module_id"
                        />
                    </v-col>
                </v-row>
                <v-row justify="space-around">
                    <v-col cols="12">
                        <DefaultAutocomplete
                            clearable
                            :items="selects.courses"
                            v-model="resource.course_id"
                            label="Curso"
                            item-value='id'
                            item-text='name'
                            :rules="rules.required"
                            :disabled="!resource.school_id"
                        />
                    </v-col>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <DefaultTextArea
                            label="Indicaciones"
                            placeholder="Indicaciones (Opcional)"
                            v-model="resource.description"
                        />
                    </v-col>
                </v-row>
                <div class="d-flex justify-space-between align-center my-4">
                    <p class="p-0 m-0 text-bold">Lista de Recursos</p>
                    <div>
                        <v-btn class="mt-1 ml-1" color="primary" elevation="0"
                            :disabled="resources.length>=constraints.max_quantity_upload_files"
                            @click="openSelectPreviewMultimediaModal"
                        >
                            Multimedia
                        </v-btn>
                        <v-btn class="mt-1 ml-1" color="primary" elevation="0"
                            :disabled="resources.length>=constraints.max_quantity_upload_files"
                            @click="openFileInput"
                        >
                            Escritorio
                        </v-btn>
                        <input
                            ref="uploader_input_file"
                            class="d-none"
                            type="file"
                            @change="subirArchivo"
                        >
                    </div>
                </div>
                <span>Añade una lista de recursos para que los usuarios puedan descargar. Peso máximo {{constraints.max_size_upload_files}} MB</span>
                <v-row class="mt-4">
                    <ul style="width: 100%;">
                        <div class="d-flex justify-space-between" v-for="(resource,index) in resources" :key="index">
                            <li  v-text="getTitle(resource)"></li> 
                             <v-icon @click="deleteResource(index)">mdi mdi-close</v-icon>
                        </div>
                    </ul>
                </v-row>
            </v-form>
            <SelectMultimedia
                :ref="modalPreviewMultimedia.ref"
                :options="modalPreviewMultimedia"
                :custom-filter="[]"
                width="85vw"
                @onClose="closeSelectPreviewMultimediaModal"
                @onConfirm="onSelectMediaPreview"
            />
        </template>
    </DefaultDialog>
</template>

<script>
import SelectMultimedia from '../../components/forms/SelectMultimedia.vue'


export default {
    components: { SelectMultimedia },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            errors: [],
            resourceDefault: {
                id: null,
                module_id: null,
                school_id: null,
                course_id: null,
                description:'',
                name:'',
                count_file:0
            },
            resource: {
                id: null,
                module_id: null,
                school_id: null,
                course_id: null,
                description:'',
                name:'',
                count_file:0
            },
            selects: {
                modules: [],
                schools: [],
                courses: [],
            },
            rules: {
                required: this.getRules(['required']),
            },
            modalPreviewMultimedia: {
                ref: 'modalSelectPreviewMultimedia',
                open: false,
                title: 'Buscar multimedia',
                confirmLabel: 'Seleccionar',
                cancelLabel: 'Cerrar'
            },
            file:null,
            resources:[],
            edit_resource:false,
            constraints:{
                max_quantity_upload_files:6,
                max_size_upload_files:25,
            },
            fileSelected: null,
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
            vue.$refs.projectForm.resetValidation()
        }
        ,
        confirmModal() {

            let vue = this

            vue.errors = []

            this.showLoader()

            const validateForm = vue.validateForm('projectForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id
                        ? `${base}/${vue.resource.id}/update`
                        : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';
            if (validateForm) {
                const formData = vue.createFormData();
                vue.$http
                   .post(url, formData)
                   .then(({data}) => {

                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                   }).catch((error) => {
                       if (error && error.errors)
                            vue.errors = error.errors
                    })
            }

            this.hideLoader()
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {

            let vue = this

            vue.errors = []

            vue.$nextTick(() => {
                vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            })

            let base = `${vue.options.base_endpoint}`
            if(resource){
                let url =   `${base}/${resource.id}/edit`
                await vue.$http.get(url).then(({data}) => {
    
                    vue.selects.modules = data.data.modules
                    vue.selects.destinos = data.data.destinos
    
                    if (resource) {
                        vue.resource = Object.assign({}, data.data.announcement);
    
                        if (vue.resource.publish_date)
                            vue.resource.publish_date = vue.resource.publish_date.substring(0, 10)
    
                        if (vue.resource.end_date)
                            vue.resource.end_date = vue.resource.end_date.substring(0, 10)
                    }
                })
            }

            return 0;
        },
        loadSelects() {
            let vue = this;
            let url = `${vue.options.base_endpoint}/get-selects?type=module`
            vue.selects.schools = [];
            vue.selects.courses = [];
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.modules = data.data

                })
        },
        getSchoolsByModule(){
            let vue = this;
            let url = `${vue.options.base_endpoint}/get-selects?type=school&module_id=${vue.resource.module_id}`
            vue.selects.schools = [];
            vue.selects.courses = [];
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.schools = data.data
                })
        },
        getCoursesBySchool(){
            let vue = this;
            let url = `${vue.options.base_endpoint}/get-selects?type=course&school_id=${vue.resource.school_id}`
            vue.selects.courses = [];
            vue.$http.get(url)
                .then(({data}) => {
                    vue.selects.courses = data.data
                })
        },
        openFileInput(){
            this.$refs.uploader_input_file.click();
        },
        deleteResource(index){
           this.resources.splice(index, 1); 
        },
        createFormData(){
            let vue = this;
            let formData = new FormData();
            const resources_file = vue.resources.filter(r=>r.type_resource =='file');
            resources_file.map(rf =>{
                formData.append("files[]",rf);
            })
            const resources_media = vue.resources.filter(r=>r.type_resource =='media');
            if(resources_media.length>0){
                resources_media.forEach((rm,index)=>{
                    const keys = Object.keys(rm);
                    keys.forEach(k=>{
                        formData.append(`multimedias[${index}][${k}]`, resources_media[index][k]);
                    })
                })
            }
            const keys_tarea = Object.keys(vue.resource);
            keys_tarea.forEach(k => {
                formData.append(`tarea[${k}]`, vue.resource[k]);
            });
            return formData;
        },
        getTitle(resource){
            if(resource.type_resource=='file'){
                return `${resource.title} (${resource.filesize} MB)`;
            }
            return (resource.size) ?  `${resource.title} (${resource.size} MB)` : resource.title;
        },
        openSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = true
            if(vue.$refs[vue.modalPreviewMultimedia.ref]){
                vue.$refs[vue.modalPreviewMultimedia.ref].getData()
            }
        },
        onSelectMediaPreview(media) {
            let vue = this;
            console.log(media);
            if(!media){
                vue.showAlert('Seleccione un multimedia.','warning')
                return true;
            }
            if(this.resources.find(r=> r.id && r.id==media.id)){
                vue.showAlert('Este recurso ya ha sido seleccionado.','warning')
                return true;
            }
            if(media.formattedSize.includes('MB')){
                media.size = media.formattedSize.replace(' MB','');
            }
            if(media.size>vue.constraints.max_size_upload_files){
                vue.showAlert(`El limite máximo por archivo es de ${vue.constraints.max_size_upload_files} MB`,'warning')
                return true;
            }
            vue.closeSelectPreviewMultimediaModal();
            media.type_resource = 'media';
            this.resources.push(media); 
        },
        closeSelectPreviewMultimediaModal() {
            let vue = this
            vue.modalPreviewMultimedia.open = false
        },
        subirArchivo(e){
            const vue = this;
            let file = e.target.files[0];
            file.filesize = vue.roundTwoDecimal(file.size/1024**2);
            if(file.filesize>vue.constraints.max_size_upload_files){
                vue.showAlert(`El limite máximo por archivo es de ${vue.constraints.max_size_upload_files} MB`,'warning');
                return true;
            }
            file.type_resource = 'file';
            file.title = file.name;
            vue.resources.push(file);
        }
    }
}
</script>
