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
                
            ]
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
        async confirmModal() {
            let vue = this;
            let formData = new FormData();
            vue.files.map((file) => {
                formData.append("files[]", file);
            })
            await vue.$http.post('/jarvis/generate-checklist',formData).then(({data})=>{
                console.log(data);
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
