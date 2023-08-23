<template>
    <DefaultDialog
        width="45vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
        :showCardActions="false"
    >
        <template v-slot:content>

            <v-row>
                <v-col cols="12">
                    <v-row>
                        <v-col cols="8" class="py-0 d-flex flex-column align-items-start justify-content-center">
                            <p class="mb-2"> 
                                <span class="font-weight-bold">Apellidos: </span> 
                                <span v-text="`${summoned.user.lastname} ${summoned.user.surname}`"></span>
                            </p>
                            <p class="mb-2"> 
                                <span class="font-weight-bold">Nombres: </span> 
                                <span v-text="`${summoned.user.name}`"></span>
                            </p>

                            <p class="mb-2"> 
                                <span class="font-weight-bold">Nro Documento: </span> 
                                <span v-text="summoned.document"></span>
                            </p>
                        </v-col>
                        
                        <v-col cols="4" class="py-0 d-flex justify-content-end">
                            <v-avatar 
                                class="profile border" 
                                color="primary" 
                                size="100" 
                                tile
                                >
                                <v-icon dark>
                                    mdi-account-circle
                                </v-icon>
                            </v-avatar>
                        </v-col>

                        <v-col v-show="availableContentData" cols="12" class="py-0">
                            <hr>
                            <p class="mb-2"> 
                                <span class="font-weight-bold">Pregunta: </span> 
                                <span v-text="campaign.question"></span>
                            </p>
                            <p class="mb-2"> 
                                <span class="font-weight-bold">Respuesta: </span> 
                                <span v-text="summoned.answer"></span>
                            </p>
                        </v-col>

                        <v-col cols="12" class="pb-0">
                            <div class="border-solid-primary rounded-md d-flex justify-space-between py-2 px-3 mb-4">
                                <div class="align-self-center">
                                    Estatus del proceso
                                </div>
                                <div class="d-flex">
                                    <span class="mr-5">
                                        Total: <span v-text="summoned.total"></span>
                                    </span>

                                    <div class="mr-4" v-show="availableSupportSustent">
                                        <div> 
                                            <span class="mr-6"> 
                                                <span class="text-success fas fa-check"></span>
                                                <span v-text="currCountSustentsIn.c_accepted"></span>
                                            </span>
                                            <span> 
                                                <span class="text-danger fas fa-times"></span> 
                                                <span v-text="currCountSustentsIn.c_rejected"></span>
                                            </span>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <!-- TABLA SUSTENTOS -->
                            <div>
                                <vue-table 
                                    class="m-0" 
                                    label-search="" 
                                    :uri="`/votaciones/${CampaignProvide.campaign.id}/postulacion/postulate/${summoned_id}/sustents`"
                                    :cols="tableColumns" 
                                    :reload="tableRefresh"  
                                    :searchable="false"
                                    >

                                    <template v-slot:sustent="{ item:{ sustent } }">
                                        <span v-text="sustent"></span> 
                                    </template>
                                    
                                    <template v-slot:actions="{ item:{ id, state} }">
                                        <div v-if="arrayValidators.length">
                                            <CheckBoxSustent 
                                                :item="{ id, state: checkValidator(id) ? setValidator(id) : state }" 
                                                @data="manageValidateCheck"
                                            />
                                        </div>
                                        <div v-else>
                                            <CheckBoxSustent 
                                                :item="{ id, state }" 
                                                @data="manageValidateCheck"
                                            />
                                            
                                        </div>
                                    </template>
                                </vue-table>
                            </div>
                            <!-- TABLA SUSTENTOS -->

                        </v-col>

                        <v-col cols="12" class="py-0">
                            <v-alert 
                                v-model="alert" 
                                text 
                                type="error" 
                                dismissible
                                >
                                Por favor verifique sustentos correctamente.
                            </v-alert>
                        </v-col>

                        <v-col cols="6" class="py-0" style="margin-top: -0.7rem;">
                            <div v-show="availableVotationData">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <DefaultToggle 
                                            no-label
                                            v-model="currCandidate"
                                            class="m-0"
                                        />
                                    </div>
                                    <span class="mt-3">Convertir en candidato</span>
                                </div>
                            </div>
                        </v-col>

                        <v-col cols="6" class="py-0">
                            <div v-if="availableNotificationData" class="d-flex justify-content-end w-100 px-1">
                                <BoxEmailNotification 
                                    :url="`/votaciones/${CampaignProvide.campaign.id}/postulacion/postulate/${summoned_id}`"
                                    :disabled="disabledEmail" 
                                    :index="CampaignProvide.campaign.id" 
                                    :subindex="summoned_id" 
                                />
                            </div>
                        </v-col>

                        <v-col cols="12" class="pb-0">
                            <hr>
                            <v-row>
                                <v-col cols="6" class="pb-0">
                                    <v-btn 
                                        v-if="summoned.state_sustent" 
                                        color="error" 
                                        :loading="subloader" 
                                        :disabled="subloader" 
                                        class="mr-2" 
                                        @click="cleanCheckValidate">
                                        Invalidar 
                                    </v-btn>
                                </v-col>

                                <v-col cols="6" class="d-flex justify-content-end pb-0">
                                    <v-btn 
                                        color="gray" 
                                        class="mr-5" 
                                        @click="closeModalPost(false)"
                                        > Cancelar 
                                    </v-btn>

                                    <v-btn 
                                        color="primary" 
                                        class="mr-1 px-9"
                                        :loading="loader" 
                                        :disabled="loader" 
                                        @click="sendCheckValidate"> 
                                        <span v-text="summoned.state_sustent ? 'Guardar' : 'Validar'"></span> 
                                    </v-btn>
                                </v-col>
                            </v-row>
                        </v-col>

                    </v-row>
                </v-col>
            </v-row>

        </template>
    </DefaultDialog>
</template>

<script>

import VueTable from './components/VueTable.vue';
import CheckBoxSustent from './components/CheckBoxSustent.vue';
import BoxEmailNotification from './components/BoxEmailNotification.vue';

const SUSTENT_HEADERS = [
    {text: "Sustento", value: "sustent", align: 'left', sortable: false },
    {text: "Aprobar - Rechazar", value: "actions", align: 'right', sortable: false},
];

const NORMAL_HEADERS = [
    {text: "Sustento", value: "sustent", align: 'left', sortable: false },
];

export default {
    props: {
        options: {
            type: Object,
            required: true
        }
    },
    inject:['CampaignProvide'],
    components:{ VueTable, CheckBoxSustent, BoxEmailNotification },
    data() {
        return {
            summoned_id: 0,
            resource: null,
            tableColumns: {
                headers: (this.CampaignProvide.campaign.state_postulate_support) ? SUSTENT_HEADERS : NORMAL_HEADERS,
                columns: ['fullname', 'numdoc', 'sustent', 'actions']
            },
            tableRefresh: false,

            alert: false,
            dialog: false,
            loader: false,
            subloader: false,

            // campos para validaciones
            arrayValidators: [],
            currCountSustents: { c_accepted: 0, c_rejected: 0 },
            currCountSustentsIn: { c_accepted: 0, c_rejected: 0 },
            checkEditSustent: false,
            currCandidate: false,

            // campos para vlidar checks
            canbeeditable: true, // si se modifica algun check de la tabla debe ser 'false'
            countnull: 0 // contador de nulos para checks
        }
    },
    computed: {
        summoned() {
            const vue = this;
            return vue.options.resource.summoned;
        },
        campaign() {
            const vue = this;
            return vue.options.resource.campaign;
        },
        availableContentData() {
            const vue = this;
            const { stage_content } = vue.campaign;
            return (stage_content === null) ? false : true;
        },
        availableVotationData() {
            const vue = this;
            const { stage_votation } = vue.campaign;
            return (stage_votation === null) ? false : true;
        },
        availableNotificationData() {
            const vue = this;
            const { subject, body } = vue.campaign;
            return (subject === null && body === null) ? false : true;
        },
        availableSupportSustent() {
            const vue = this;
            const { state_postulate_support } = vue.campaign;
            return state_postulate_support;
        },
        disabledEmail() {
            const vue = this;
            const { total, state_sustent } = vue.summoned;

            if(state_sustent) return false;

            if(!vue.availableSupportSustent) return false;
            return (vue.arrayValidators.length !== total);
        }
    },
    watch:{
        options:{
            handler(val) {
                const vue = this;
                const { summoned, campaign } = val.resource;

                if(summoned.id) {
                    vue.summoned_id = summoned.id; // summoned_id -> sustentos
                    vue.currCandidate = summoned.candidate_state;
                    vue.tableRefresh = !vue.tableRefresh; // actualiza la tabla

                    vue.data = { ...summoned };

                    vue.getCheckSustents(); // los checks aceptados - rechazados
                }
            },
            deep: true
        }
    },
    methods: {
        getCheckSustents() {
            const vue = this;

            vue.$http.get(`${vue.options.base_endpoint}/postulate/${vue.summoned_id}/sustents/checks`)
               .then( (res) => {
                    const { data } = res.data;

                    vue.currCountSustents.c_accepted = data.accepteds;
                    vue.currCountSustents.c_rejected = data.rejecteds;

                    // para layout modal
                    vue.currCountSustentsIn.c_accepted = data.accepteds;
                    vue.currCountSustentsIn.c_rejected = data.rejecteds;
               });
        },
        cleanCheckValidate() {
            const vm = this;
            vm.subloader = true;

            vm.$http.put(`${vm.options.base_endpoint}/postulate/${vm.summoned_id}/sustents/reset`,{ id: vm.summoned_id })
                            .then((res) => {
                                // console.log('send', res);
                                vm.closeModalPost(true);
                            });

        },
        sendCheckValidate() {
            const vm = this;
            const { total, state_sustent } = vm.data;
            const base_url = `${vm.options.base_endpoint}/postulate/${vm.summoned_id}/sustents`;

            let data = { id: vm.summoned_id }

            // sustentos bloqueados
            if(!vm.availableSupportSustent) {
                vm.loader = true;
                // id_summoned -- vm.currIndex;
                // candidate_state -- vm.currCandidate;

                if(!vm.availableVotationData) {
                  vm.$http.put(`${base_url}/update_sub`, data)
                                .then((res) => {
                                        // console.log(res);
                                        vm.closeModalPost(true)
                                    });

                } else {
                    data = { ...data, candidate_state: vm.currCandidate };

                  vm.$http.put(`${base_url}/update_sub`, data)
                                .then((res) => {
                                        // console.log(res);
                                        vm.closeModalPost(true)
                                    });
                }
            } else {
                data = { ...data, currSustents: vm.arrayValidators, candidate_state: vm.currCandidate }; 
                
                const countChecks = vm.arrayValidators.length;//conteo de sustentos (validados - rechazados); 
                
                if(state_sustent) {
                    // si el usuario ya esta validado validar si modifica algun check

                    if(!vm.canbeeditable) {
                        if(vm.countnull > 0) return vm.alert = true;
                    } 

                    vm.loader = true;
                    vm.$http.put(`${base_url}/update`, data)
                                    .then((res) => {
                                        vm.closeModalPost(true);
                                    });

                }else{
                    // si el usuario no esta validado pero se ingresaron nuevos sustentos

                    const { c_accepted, c_rejected } = vm.currCountSustents;
                    const stateChecks = (!c_accepted && !c_rejected); //verificar el nro checks del usuario

                    if(stateChecks) {
                        // quiere decir que no se han enviado checks 'accepted' o 'rejected'
                        if(countChecks !== total) return vm.alert = true; // validar con el total
                        
                    } else {
                        // quiere decir que tiene checks pero no todos  
                        const { c_accepted: in_c_accepted, c_rejected: in_c_rejected } = vm.currCountSustentsIn;
                        const countChecksReal = (in_c_accepted + in_c_rejected); 

                        if(countChecksReal !== total) return vm.alert = true;// validar con el total
                    }

                    vm.loader = true;
                    vm.$http.put(`${base_url}/update`, data)
                            .then((res) => {
                            vm.closeModalPost(true);
                    });

                }
                if(vm.currCandidate){
                    vm.queryStatus("reconocimiento", "confirma_postulacion");
                }
            }
        },
        manageValidateCheck({ payload, state }) { 
            const vm = this;
            const { accepted, rejected } = payload;

            vm.canbeeditable = false; // inidicador de que ha ingresado a esta funcion

            if(vm.arrayValidators.length){

                const { id:index } = payload;
                const currIndex = vm.arrayValidators.findIndex(({id}) => id === index);

                if(currIndex >= 0) {
                
                    // extraemos el check previo para contador
                    const { accepted: idx_accepted, 
                                    rejected: idx_rejected } = vm.arrayValidators[currIndex];
                    if(idx_accepted) vm.currCountSustentsIn.c_accepted--;
                    if(idx_rejected) vm.currCountSustentsIn.c_rejected--;

                    // console.log('splice', { payload, state});
                    //elimina el check payload
                    vm.arrayValidators.splice(currIndex, 1, {...payload});

                    // valida checks nulos
                    if(!accepted && !rejected) vm.countnull++;
                    else if(vm.countnull == 0) vm.countnull = 0; 
                    else vm.countnull--;

                }else{
                    vm.arrayValidators.push({...payload});
                    // console.log('push', { payload, state})
                    // check para contador
                    if(accepted) vm.currCountSustentsIn.c_accepted++;
                    if(rejected) vm.currCountSustentsIn.c_rejected++;

                    // valida checks nulos
                    if(!accepted && !rejected) {
                        // extraer previo estado y decrementar
                        const { accepted: st_accepted, rejected: st_rejected } = state;
                        
                        if(st_accepted) vm.currCountSustentsIn.c_accepted--;
                        if(st_rejected) vm.currCountSustentsIn.c_rejected--;

                        vm.countnull++;
                    }else if(vm.countnull == 0) vm.countnull = 0; 
                    else vm.countnull--;
                }

            } else {
                // console.log('state, payload', { state, payload });

                // valida checks nulos
                if(!accepted && !rejected)  {
                    // extraer previo estado y decrementar
                    const { accepted: st_accepted, rejected: st_rejected } = state;

                    if(st_accepted) vm.currCountSustentsIn.c_accepted--;
                    if(st_rejected) vm.currCountSustentsIn.c_rejected--;

                    return vm.countnull++;
                }

                vm.arrayValidators.push({...payload});

                // check para contador
                if(accepted) vm.currCountSustentsIn.c_accepted++;
                if(rejected) vm.currCountSustentsIn.c_rejected++;

                if(vm.countnull == 0) vm.countnull = 0; 
                else vm.countnull--; 
            }

        },
        checkValidator(index) {
            const vm = this;

            const currIndex = vm.arrayValidators.findIndex(({id}) => id === index);
            return (currIndex >= 0);
        },
        setValidator(index) {
            const vm = this;

            const currIndex = vm.arrayValidators.findIndex(({id}) => id === index);
            const { accepted, rejected } = vm.arrayValidators[currIndex];

            if(accepted === rejected) {
                vm.arrayValidators.splice(currIndex, 1);

                if(vm.data.state_sustent) vm.checkEditSustent = true;
                return null;
            }
            return accepted;
        },
        closeModalPost(flag){
            const vm = this;
            vm.alert = false;
            vm.loader = false;
            vm.subloader = false;
            vm.checkEditSustent = false;
            vm.currCandidate = false;

            // limpiar campos para checks
            vm.arrayValidators = [];
            vm.countnull = 0;
            vm.canbeeditable = true;

            // contador validadores
            vm.currCountSustents.c_accepted = 0;
            vm.currCountSustents.c_rejected = 0;

            vm.currCountSustentsIn.c_accepted = 0;
            vm.currCountSustentsIn.c_rejected = 0;

            if(flag) {
                vm.$emit('onConfirm', true);
            }else {
                vm.$emit('onCancel', flag);
            }
        }
        ,
        onCancel() {
            let vue = this;
            vue.$emit('onCancel')
        },
        resetValidation() {},
        onConfirm() {
            let vue = this
            console.log('confirm');
        },
        loadData(resource) {
        },
        loadSelects() {}
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