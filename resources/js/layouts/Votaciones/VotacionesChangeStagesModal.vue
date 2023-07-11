<template>
    <DefaultDialog
        width="45vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <span class="text-center">
                        <p class="mb-6 mt-0">
                            {{ options.contentText }}
                        </p>
                    </span>
                    <div class="d-flex justify-content-around gap">
                        <div class="flex-1" v-show="options.resource.stages.stage_content != null">
                            <div class="d-flex flex-column align-items-center">
                                <v-card class="elevation-1" style="height:11rem;">
                                    <v-card-title class="justify-content-center">Contenido</v-card-title>
                                    <v-card-text>
                                        Podrán ver contenido referente a la campaña y contestar una pregunta.
                                    </v-card-text>
                                </v-card>

                                <DefaultToggle 
                                    class="mt-0 ml-3" 
                                    v-model="options.resource.stages.stage_content" 
                                    no-label
                                />
                            </div>
                        </div>
                        <div class="flex-1" v-show="options.resource.stages.stage_postulate != null">
                            <div class="d-flex flex-column align-items-center">
                                <v-card class="elevation-1" style="height:11rem;">
                                    <v-card-title class="justify-content-center">Postulaciones</v-card-title>
                                    <v-card-text>
                                        Postular a varios usuarios del mismo criterio y brindar un sustento por cada uno que podrá ser revisado y filtrado desde el gestor.
                                    </v-card-text>
                                </v-card>

                                <DefaultToggle 
                                    class="mt-0 ml-3" 
                                    v-model="options.resource.stages.stage_postulate" 
                                    no-label
                                />
                            </div>
                        </div>
                        <div class="flex-1" v-show="options.resource.stages.stage_votation != null"  >
                            <div class="d-flex flex-column align-items-center">

                                <v-card class="elevation-1" style="height:11rem;">
                                    <v-card-title class="justify-content-center">Votaciones</v-card-title>
                                    <v-card-text>
                                       Realizar una votación final, los resultados solo se verán en el gestor.
                                    </v-card-text>
                                </v-card>
                                <DefaultToggle 
                                    class="mt-0 ml-3" 
                                    v-model="options.resource.stages.stage_votation" 
                                    no-label
                                />
                            </div>
                        </div>
                    </div>
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
        }
    },
    data() {
        return {
            resource: null,
        }
    },
    methods: {
        onCancel() {
            let vue = this;
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {
            let vue = this
            let { base_endpoint, resource } = vue.options;

            vue.showLoader();
            
            vue.$http.put(`${base_endpoint}/${resource.campaign_id}`, resource.stages)
            .then((res) => {
                if(res.data.errors.length) {
                    vue.showAlert('Hubo un problema al actualizar etapas.', 'error');
                } else {
                    vue.showAlert('Etapas actualizadas correctamente.');
                }

                vue.hideLoader();
                vue.$emit('onConfirm', true);
            });

        },
        loadData(resource) {
        },
        loadSelects() {

        }
    }
}
</script>
<style>
    .bx_header .cont {
        color: #2A3649;
        font-size: 20px;
        font-family: "Nunito", sans-serif;
        font-weight: 700;
        margin-left: 29px;
        text-align: left;
        line-height: 25px;
    }
</style>