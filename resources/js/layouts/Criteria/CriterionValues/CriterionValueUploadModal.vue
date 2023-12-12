<template>
    <div>
        <!-- <v-btn @click="modal.subida_masiva= true" color="primary">
            Subida masiva
        </v-btn> -->
        <CriterionValueUploadStepper
            urlPlantilla="/templates/Plantilla-criterios.xlsx"
            :urlSubida="`/criterios/${criterion_id}/valores/upload`"
            v-model="options.open"
            @onClose="closeModal"
        />
    </div>
</template>
<script>

    import CriterionValueUploadStepper from "./CriterionValueUploadStepper";

    export default{
        components: {CriterionValueUploadStepper},
        props:{
            options: {
                type: Object,
                required: true
            },
            criterion_id:{
                type: Number|String,
                required:true
            }
        },
        name: "criterion-values-upload",
        data(){
            return {
                modal: {
                    crear_editar_checklist: false,
                    ver_items: false,
                    asignar: false,
                    subida_masiva: false
                },
            }
        },
        methods:{
            closeModal() {
                let vue = this
                vue.resetValidation()
                vue.$emit('onCancel')
            },
            resetValidation() {
                let vue = this
                vue.modelStepper=1;
                vue.archivo= null;
                // vue.$refs.dropzone.limpiarArchivo()
                // vue.errors = []
                // vue.$refs.anuncioForm.resetValidation()
            },
            // closeModalSubidaMasiva(){
            //     this.modal.subida_masiva=false;
            // },
            resetSelects() {
            },
            async loadData() {


            },
            loadSelects() {
                let vue = this
            },
        }
    }

</script>