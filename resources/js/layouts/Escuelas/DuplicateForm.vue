<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"
        :showCardActions="false"
    >
        <!-- <template v-slot:title-icon>
            <DefaultInfoTooltip class="ml-3" right color="white">

                <template v-slot:content>
                    <b class="color-default-primary mt-2">Indicaciones:</b>
                    <ul style="text-align: left; color: black;">
                        <li class="mt-2">
                            Deberás asignar la segmentación manualmente.
                        </li>
                        <li class="mt-2">
                            Deberás asignar los requisitos manualmente de ser necesario (cursos y temas).
                        </li>
                        <li class="mt-2">
                            Solo se copiarán los contenidos nuevos en el lugar de destino.
                        </li>
                    </ul>
                </template>
                
            </DefaultInfoTooltip>
        </template> -->

        <template v-slot:content>

            <DefaultErrors :errors="errors"/>

            <v-form ref="schoolDuplicateForm" class="pa-0">
                
              <!--   <v-row justify="space-around">
                    <template v-if="!selection.length">
                          No nodes selected.
                        </template>
                        <template v-else>
                          <div
                            v-for="node in selection"
                            :key="'node-' + node"
                          >
                            {{ node }}
                          </div>
                        </template>
                </v-row> -->

                <v-stepper non-linear class="--stepper_box elevation-0" v-model="stepper_box">
                    <v-stepper-header class="--stepper_dots elevation-0">
                        <v-stepper-step step="1" :complete="stepper_box > 1">
                            Selecciona el contenido
                            <small>Paso 1</small>
                        </v-stepper-step>
                        <v-divider></v-divider>
                        <v-stepper-step step="2" :complete="stepper_box > 2">
                             Selecciona el destino
                            <small>Paso 2</small>
                        </v-stepper-step>
                    </v-stepper-header>

                    <hr class="mx-5 my-1">

                    <v-stepper-items>
                        <v-stepper-content step="1" class="p-0">

                            <v-row justify="space-around">
                                <v-col cols="12" class="px-8">

                                    <v-container
                                        id="scroll-target"
                                        class="overflow-y-auto grey lighten-4"
                                        style="max-height: 500px"
                                    >
                                        <v-treeview
                                          v-model="selection"
                                          :items="items"
                                          :selection-type="selectionType"
                                          selectable
                                          selected-color="primary"
                                        >
                                            <template v-slot:prepend="{ item, open }">
                                              <v-icon>
                                                {{ item.icon }}
                                              </v-icon>
                                            </template>
                                        </v-treeview>
                                          <!-- open-all -->

                                    </v-container>
                                </v-col>
                            </v-row>
                                
                        </v-stepper-content>
                        <v-stepper-content step="2" class="p-0">
                           <v-row justify="space-around">
                                <v-col cols="12" class="px-8">

                                    <!-- <div class="mx-5 mb-3">
                                        <v-text-field
                                            v-model="search"
                                            label="Buscar destino..."
                                            hide-details
                                            filled
                                            dense
                                        ></v-text-field>
                                    </div> -->
                                    <div class="mb-3">
                                        <DefaultInput
                                            clearable dense
                                            v-model="search"
                                            label="Buscar destino..."
                                            append-icon="mdi-magnify"
                                        />
                                    </div>

                                    <v-container
                                        id="scroll-target"
                                        class="overflow-y-auto grey lighten-4"
                                        style="max-height: 500px"
                                    >
                                        <v-treeview
                                          v-model="selection_schools"
                                          :items="items_destination"
                                          selection-type="leaf"
                                          selectable
                                          selected-color="primary"
                                          :search="search"
                                          :filter="filter"
                                        >
                                            <template v-slot:prepend="{ item, open }">
                                              <v-icon>
                                                {{ item.icon }}
                                              </v-icon>
                                            </template>
                                        </v-treeview>
                                          <!-- open-all -->

                                    </v-container>
                                </v-col>
                            </v-row>
                        </v-stepper-content>
                    </v-stepper-items>
                    
                </v-stepper>

            </v-form>

        </template>
        <template v-slot:card-actions>
            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <ButtonsModal
                    @cancel="prevStep"
                    @confirm="nextStep"
                    :cancelLabel="cancelLabel"
                    :confirmLabel="confirmLabel"
                    :disabled_next="disabled_btn_next"
                />
            </v-card-actions>
        </template>
    </DefaultDialog>
</template>

<script>

import ButtonsModal from '../../components/Entrenamiento/Checklist/Blocks/ButtonsModal.vue';

const fields = [
    'selection_source', 
    'selection_destination', 
];

const file_fields = [];

export default {
    components: {
        ButtonsModal
    },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            search: null,
            caseSensitive: false,
            stepper_box: 1,
            is_superuser: false,
            errors: [],
            resourceDefault: {
                items: {},
                items_destination: {},
            },
            limit_allowed_users: null,
            resource: {
            },
            functionalities: [],
            rules: {
                // name: this.getRules(['required', 'max:255']),
                // logo: this.getRules(['required']),
            },
            selectionType: 'leaf',
            selection: [],
            selection_schools: [],
            items: [],
            items_destination: [],
            cancelLabel: 'Cancelar',
            confirmLabel: 'Continuar',
            disabled_btn_next: true,
        }
    },
    watch: {
        stepper_box: {
            handler(n, o) {
                let vue = this;

                vue.setButtonStatus()
            },
            deep: true
        },
        selection: {
            handler(n, o) {
                let vue = this;

                vue.setButtonStatus()
            },
            deep: true
        },
        selection_schools: {
            handler(n, o) {
                let vue = this;

                vue.setButtonStatus()
            },
            deep: true
        }
    },
    computed: {
        filter () {
            return this.caseSensitive
                    ? (item, search, textKey) => item[textKey].indexOf(search) > -1
                    : undefined
        },
    },
    mounted() {
        // this.loadData();
        let vue = this;

    },
    methods: {
        setButtonStatus() {
            let vue = this;
            
            if (vue.stepper_box == 1) {

                vue.disabled_btn_next = vue.selection.length > 0 ? false : true;
            }
            else if (vue.stepper_box == 2) {

                vue.disabled_btn_next = true;

                if (vue.selection.length > 0 && vue.selection_schools.length > 0) {

                    vue.disabled_btn_next = false;
                }
            }
        },
        nextStep(){
            let vue = this;

            if (vue.stepper_box == 1) {
               
                vue.stepper_box++
                vue.cancelLabel = "Atrás";
                vue.confirmLabel = "Copiar contenido";

            } else {

                if (vue.stepper_box == 2) {
                    vue.confirmModal();
                }
            }
        },
        prevStep(){
            let vue = this;

            if (vue.stepper_box == 1) {
                
                vue.closeModal();

            } else {

                if (vue.stepper_box == 2) {
                    vue.stepper_box--
                    vue.cancelLabel = "Cancelar";
                    vue.confirmLabel = "Continuar";
                }
            }
        },

        resetValidation() {
            let vue = this
        },
        resetForm() {
            let vue = this

            vue.stepper_box = 1
            vue.cancelLabel = "Cancelar";
            vue.confirmLabel = "Continuar";
        },
        closeModal() {
            let vue = this;
            vue.resetForm();
            vue.$emit('onCancel');
        },
        confirmModal() {

            let vue = this
            vue.errors = []
            this.showLoader()

            let base = `${vue.options.base_endpoint}`;
            let url = `${base}/${vue.resource.id}/copy-content`

            let method = 'POST';

            vue.resource.selection_source = vue.selection
            vue.resource.selection_destination = vue.selection_schools

            let formData = vue.getMultipartFormData(method, vue.resource, fields);

            // Submit data to be saved

            vue.$http
                .post(url, formData)
                .then(({data}) => {

                    vue.resetForm();
                    vue.closeModal();
                    vue.showAlert(data.data.msg);
                    vue.$emit('onConfirm');
                    this.hideLoader();

                }).catch((error) => {
                    this.hideLoader();
                    if (error && error.errors) {
                        vue.errors = error.errors
                    }
                })
        },

        async loadData(resource) {
            let vue = this

            vue.resource = resource

            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })
            vue.selection = []
            
            let base = `${vue.options.base_endpoint}`;
            let url = `${base}/copy/${resource.id}`

            await vue.$http.get(url)
                .then(({data}) => {
                    vue.items = data.data.items
                    vue.items_destination = data.data.items_destination

                    // if (resource) {
                    //     vue.resource = Object.assign({}, data.data.model)
                    // }
                })
            return 0;
        },

        loadSelects() {
        },
    }
}
</script>
