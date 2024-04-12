<template>
    <DefaultDialog
        width="50vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>
            <v-form ref="FontForm">
                <p>Subelos para tamaño de letra mediano.</p>
                <v-col cols="12" class="pb-0">
                    <DefaultInput label="Nombre de la fuente"  v-model="resource.name"
                        :rules="rules.required" show-required dense />
                </v-col>
                <v-row v-for="(font,index) in resource.fonts" :key="index">
                    <v-col cols="8">
                        <v-file-input
                            required
                            :rules="rules.required"
                            chips
                            accept=".ttf"
                            :label="font.label"
                            v-model="font.file"
                            prepend-icon="mdi-format-font"
                            @change="previewFont($event,font.type)"
                        ></v-file-input>
                    </v-col>
                    <p :id="font.type" class="d-flex align-items-center pl-2"></p>
                </v-row>
            </v-form>
        </template>
        
    </DefaultDialog>
</template>

<script>
export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            resource: {
                name:'',
                fonts:[
                    {label:'Fuente Normal',type:'font-normal',file:null},
                    {label:'Fuente Negrita',type:'font-bold',file:null},
                    {label:'Fuente Italica',type:'font-italic',file:null},
                    {label:'Fuente Negrita Italica',type:'font-bold-italic',file:null},
                ]
            },
            rules:{
                required:this.getRules(['required'])
            },
            
        }
    },
    methods: {
        resetValidation() {
            let vue = this;
            vue.resource.name = '';
            vue.resource.fonts[0].file = null;
            vue.resource.fonts[1].file = null;
            vue.resource.fonts[2].file = null;
            vue.resource.fonts[3].file = null;
        },
        loadData(resource) {},
        loadSelects() {},
        onCancel() {
           let vue = this;
            vue.$emit('onCancel');
        },
        async onConfirm() {
            let vue = this;
            vue.showLoader();
            const formData = new FormData();
            formData.append("_method", 'POST');
            formData.append("name" , vue.resource.name);
            for (let font of vue.resource.fonts) {
                console.log(font.type,font.file);
                formData.append(font.type,font.file);
            }
            await axios.post('/diplomas/save-font', formData).then((res)=>{
                vue.showAlert('Se creó la fuente correctamente.');
                vue.hideLoader();
                vue.resetValidation();
                vue.$emit('onConfirm');
            }).catch(()=>{
                vue.showAlert('No se pudo crear la fuente.','error');
                vue.hideLoader();
            });
        },
        previewFont(event,type_font) {
            const fontFile = event;
            if(fontFile){
                const fontUrl = URL.createObjectURL(fontFile);
                const fontPreview = document.getElementById(type_font);
                fontPreview.style.fontFamily = `previewFont${Date.now()}`;
                fontPreview.style.fontStyle = 'normal';
                fontPreview.style.fontWeight = 'normal';
                fontPreview.style.fontSize = '24px';
                fontPreview.innerHTML = 'Preview Text';
    
                const newStyle = document.createElement('style');
                newStyle.appendChild(document.createTextNode(`
                    @font-face {
                        font-family: 'previewFont${Date.now()}';
                        src: url('${fontUrl}') format('truetype');
                    }
                `));
                document.head.appendChild(newStyle);
            }
        }
    }
}
</script>
