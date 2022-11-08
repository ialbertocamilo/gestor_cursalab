<template>
    <DefaultDialog :options="options"
                   :width="width"
                   @onCancel="closeModal"
                   @onConfirm="confirmModal"
                   no-padding-card-text
    >
        <template v-slot:content>

            <v-row justify="space-around" class="mt-8 mx-1">
                <v-col cols="12">

                    <template >

                        <v-alert
                          border="top"
                          colored-border
                          elevation="2"
                          color="primary"
                          class="pb-0"
                        >
                            <small><strong>IMPORTANTE</strong></small>

                            <ul class="mt-3">
                                <li><small>Se reinicia solamente el tema o curso seleccionado.</small></li>
                                <li><small>Solo se listan los temas desaprobados o cursos con temas desaprobados.</small></li>
                                <li><small>Los temas y cursos están ordenados desde el más actual al más antiguo.</small></li>
                            </ul>
                        </v-alert>

                        <v-tabs vertical class="mt-5">
                          <v-tab class="justify-content-start">
                            <v-icon left>mdi mdi-history</v-icon> Reinicio por tema
                          </v-tab>
<!-- 
                          <v-tab class="justify-content-start">
                            <v-icon left>mdi mdi-history</v-icon> Reinicio por curso
                          </v-tab>

                          <v-tab class="justify-content-start">
                            <v-icon left> mdi mdi-history</v-icon> Reinicio total
                          </v-tab>
 -->
                          <v-tab-item >
                            <v-card flat class="ml-5">
                              <v-card-text class="pr-0 pt-1">
                                <v-alert
                                  class="py-2"
                                  elevation="2"
                                  color="primary"
                                  border="right"
                                    colored-border
                                >
                                    <small><strong>Para temas desaprobados</strong></small>
                                    <br>
                                    <small>Se reiniciará el tema seleccionado.</small>

                                </v-alert>
                                   <v-form ref="temasForm">

                                        <v-row justify="space-around">
                                            <v-col cols="12" class="d-flex justify-content-center mx-0 px-0">
                                                <DefaultAutocomplete clearable
                                                                     itemText="name"
                                                                     :items="selects.temas"
                                                                     v-model="resource.p"
                                                                     return-object
                                                                     label="Temas"
                                                                     :rules="rules.tema"
                                                />
                                            </v-col>
                                        </v-row>

                                        <v-row justify="space-around">
                                            <v-col cols="12" class="d-flex justify-content-center mx-0 px-0">

                                                <v-btn
                                                    class="default-modal-action-button mx-1"
                                                    elevation="0"
                                                    :ripple="false"
                                                    color="primary"
                                                    @click="reset('reset_x_tema', 'temasForm')"
                                                >
                                                    Reiniciar tema
                                                </v-btn>

                                            </v-col>
                                        </v-row>

                                    </v-form>
                              </v-card-text>
                            </v-card>
                          </v-tab-item>

                          <v-tab-item >
                            <v-card flat class="ml-5">
                              <v-card-text class="pr-0 pt-1">
                                <v-alert
                                  border="right"
                                  class="py-2"
                                  colored-border
                                  elevation="2"
                                  color="primary"
                                >
                                    <small><strong>Para cursos con temas desaprobados</strong></small>
                                    <br>
                                    <small>Si se reinicia por curso, se reiniciarán todos sus temas desaprobados.</small>
                                </v-alert>
                                <v-form ref="cursosForm">

                                        <v-row justify="space-around">
                                            <v-col cols="12" class="d-flex justify-content-center mx-0 px-0">
                                                <DefaultAutocomplete clearable
                                                                     :items="selects.cursos"
                                                                     v-model="resource.c"
                                                                     return-object
                                                                     label="Cursos"
                                                                     :rules="rules.curso"
                                                />
                                            </v-col>
                                        </v-row>

                                        <v-row justify="space-around">
                                            <v-col cols="12" class="d-flex justify-content-center mx-0 px-0">

                                                <v-btn
                                                    class="default-modal-action-button mx-1"
                                                    elevation="0"
                                                    :ripple="false"
                                                    color="primary"
                                                    @click="reset('reset_x_curso', 'cursosForm')"
                                                >
                                                    Reiniciar curso
                                                </v-btn>

                                            </v-col>
                                        </v-row>

                                    </v-form>
                              </v-card-text>
                            </v-card>
                          </v-tab-item>

                          <v-tab-item >
                            <v-card flat class="ml-5">
                              <v-card-text class="pr-0 pt-1">
                                <v-alert
                                  border="right"
                                  class="py-2"
                                  colored-border
                                  elevation="2"
                                  color="primary"
                                >
                                    <small><strong>Eliminar todo el avance</strong></small>
                                    <br>
                                    <small>Se eliminará todo el avance de cursos y temas.</small>
                                </v-alert>
                                    <v-form ref="totalForm">

                                        <v-row justify="space-around">
                                            <v-col cols="12" class="d-flex justify-content-center mx-0 px-0">

                                                <v-btn
                                                    class="default-modal-action-button mx-1"
                                                    elevation="0"
                                                    :ripple="false"
                                                    color="primary"
                                                    @click="reset('reset_total', 'totalForm')"
                                                >
                                                    Reiniciar todo
                                                </v-btn>

                                            </v-col>
                                        </v-row>

                                    </v-form>

                              </v-card-text>
                            </v-card>
                          </v-tab-item>

                        </v-tabs>


                    </template>
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>

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
            resource: {
                id: '',
                c: '', // curso
                p: '', // posteo/tema
            },
            selects:{
                temas: [],
                cursos: [],
            },
            rules: {
                curso: this.getRules(['required']),
                tema: this.getRules(['required']),
            }
        }
    },
    methods: {
        closeModal() {
            let vue = this;
            vue.$emit('onCancel')
        },
        reset(action, form) {

            console.log(action)

            let vue = this

            this.showLoader()

            const validateForm = vue.validateForm(form)

            let base = `${vue.options.base_endpoint}`
            let url =`${base}/${vue.resource.id}/${action}`;
            let fields = []

            if (validateForm ) {

                if (action === 'reset_x_tema')
                    fields = ['p']

                if (action === 'reset_x_curso')
                    fields = ['c']

                let formData = vue.getMultipartFormData('POST', vue.resource, fields);

                vue.$http.post(url, formData)
                // vue.$http.post(url, {'modulos_carreras' : vue.resource.modulos_carreras, '_method' : method})
                    .then(({data}) => {
                        vue.showAlert(data.data.msg)

                        if (action == 'reset_total') {
                            this.hideLoader()
                            vue.closeModal()
                        } else {
                            this.loadData(vue.resource)
                            this.hideLoader()
                        }
                        
                        vue.$emit('onReinicioTotal')
                    })
            }else{
                this.hideLoader()
            }
        },
        confirmModal() {},
        resetValidation() {
            let vue = this
            vue.resetFormValidation('cursosForm')
            vue.resetFormValidation('totalForm')
            vue.resetFormValidation('temasForm')
        }
        ,
        loadData(resource) {

            let vue = this
            let url = `${vue.options.base_endpoint}/${resource.id}/reset`

            vue.$http.get(url).then(({data}) => {
                vue.selects.temas = data.data.topics
                vue.selects.cursos = data.data.courses
                vue.resource = data.data.user
                vue.resetValidation()
            });
        },
        loadSelects() {

        }
    }
}
</script>

<style>
    .simple-table .v-data-table__wrapper{
        min-height: 150px; max-height: 450px;
        overflow-x: auto;
        overflow-y: auto;
    }
</style>
