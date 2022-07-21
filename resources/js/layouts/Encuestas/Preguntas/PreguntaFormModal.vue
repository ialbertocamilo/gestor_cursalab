<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
    >
        <template v-slot:content>
            <v-form ref="encuestaPreguntaForm" class="mb-15">

                <v-row justify="space-around">
                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultInput clearable
                                      v-model="resource.titulo"
                                      label="Título"
                                      :rules="rules.titulo"
                        />
                    </v-col>
                </v-row>

                <v-row justify="space-around">

                    <v-col cols="12" class="d-flex justify-content-center">
                        <DefaultSelect clearable
                                       :items="selects.tipos"
                                       v-model="resource.type_id"
                                       label="Tipo"
                                       return-object
                                       :rules="rules.tipo"
                        />
                    </v-col>

                </v-row>

                <v-row justify="space-around"
                       v-show="['simple', 'multiple'].includes(resource.type_id)">

                    <v-col cols="12" class="d-flex justify-content-center align-center">
                        <div class="label">
                            Opciones: {{ resource.type_id }}
                        </div>
                        <v-btn
                            text icon
                            color="primary"
                            @click="addOption"
                        >
                            <v-icon v-text="'mdi mdi-plus-circle'"/>
                        </v-btn>
                    </v-col>
                    <v-col cols="12"
                           class="d-flex justify-content-center py-0"
                           v-for="(opcion, index) in resource.opciones"
                           :key="index">

                        <v-row justify="space-around"  >
                            <v-col cols="10" class="justify-content-center">

                                <DefaultInput clearable
                                      v-model="opcion.titulo"
                                      :label="'Opción '+ (index+1)"
                                      :rules="rules.opcion"
                                />
                            </v-col>
                            <v-col cols="2" class="d-flex  justify-content-center align-center" >
                                <v-btn
                                    text icon
                                    color="primary"
                                    @click="removeOption(index)"
                                >
                                    <v-icon v-text="'far fa-trash-alt'"/>
                                </v-btn>
                            </v-col>
                        </v-row>
                    </v-col>

                </v-row>

                <v-row align="start" align-content="center">
                    <v-col cols="4" class="--d-flex --justify-content-start">
                        <DefaultToggle v-model="resource.active" />
                    </v-col>
                </v-row>

            </v-form>
        </template>

    </DefaultDialog>
</template>

<script>

const fields = ['titulo', 'type_id', 'active'];
const array_fields = ['opciones'];

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
                id: null,
                titulo: '',
                active: true,
                opciones: [],
                type_id: null
            },
            resource: { },
            selects: {
                tipos: [],
            },

            rules: {
                // modulo: this.getRules(['required']),
                titulo: this.getRules(['required', 'max:150',]),
                tipo: this.getRules(['required']),
                opcion: this.getRules(['required']),
            },

        }
    },
    methods: {
        addOption()
        {
            let vue = this
            // console.log(vue.resource.opciones)
            // let position = Object.keys(vue.resource.opciones).length;
            // console.log(position)
            vue.resource.opciones.push({'titulo':''});
            // vue.resource.opciones[position + 1] = "";
            // console.log(vue.resource.opciones)
        },
        removeOption(index)
        {
            let vue = this
            vue.resource.opciones.splice(index, 1);
        },
        closeModal() {
            let vue = this
            // vue.options.open = false
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
            vue.$refs.encuestaPreguntaForm.resetValidation()
        },
        confirmModal() {
            let vue = this

            const validateForm = vue.validateForm('encuestaPreguntaForm')
            const edit = vue.options.action === 'edit'

            let base = `${vue.options.base_endpoint}`
            let url = vue.resource.id
                        ? `${base}/${vue.resource.id}/update`
                        : `${base}/store`;

            let method = edit ? 'PUT' : 'POST';

            // if (validateForm && validateSelectedModules) {
            if (validateForm ) {

                let formData = vue.getMultipartFormData(
                    method, vue.resource, fields, [], array_fields
                );

                vue.$http.post(url, formData)
                    .then(({data}) => {
                        vue.closeModal()
                        vue.showAlert(data.data.msg)
                        vue.$emit('onConfirm')
                        this.hideLoader()
                    })
            }

            this.hideLoader()
        },
        resetSelects() {
            let vue = this
            // Selects independientes
            vue.selects.modulos = []
            vue.selects.tipos = []
        },
        async loadData(resource) {
            let vue = this

            vue.$nextTick(() => {
                vue.resource = Object.assign(
                    {}, vue.resource, vue.resourceDefault
                )

                vue.resource.opciones = []
            })

            let base = `${vue.options.base_endpoint}`
            let url = resource ? `${base}/${resource.id}/edit` : `${base}/create`;

            await vue.$http.get(url).then(({data}) => {

                vue.selects.tipos = data.data.tipos

                if (resource) {
                    vue.resource = Object.assign({}, data.data.pollquestion)
                    //vue.resource.tipo_pregunta = Object.assign({}, data.data.pollquestion.tipo_pregunta)
                }
            })

            return 0;
        },
        loadSelects() {
        },

    }
}
</script>
