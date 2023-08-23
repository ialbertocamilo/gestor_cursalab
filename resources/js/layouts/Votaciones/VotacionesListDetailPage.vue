<template>
    <section class="section-list ">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Detalle de campaña 
                <v-spacer/>
                <DefaultButton 
                    :label="'Listar campañas'"
                    @click="openCRUDPage(`/votaciones`)"
                />
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-4">
            <v-card-title class="text-white bg-primary-sub">
                Campaña: {{ campaign.title }} 
            </v-card-title>

            <v-tabs grow class="mb-2">
                <v-tab 
                    @click="current_component = 'VotacionesPostulationListPage' ">
                    Postulaciones
                </v-tab>
                <v-tab v-if="show_stage_votation" 
                    @click="current_component = 'VotacionesVotationListPage'">
                    Votaciones
                </v-tab>
            </v-tabs>

            <transition name="fade-transition" mode="out-in">
                <component :is="current_component"></component>
            </transition>
        </v-card>
    </section>
</template>

<script>

import VotacionesVotationListPage from "./VotacionesVotationListPage.vue";
import VotacionesPostulationListPage from "./VotacionesPostulationListPage.vue";

export default {
    props:['modulo_id', 'campaign_id'],
    components: { VotacionesPostulationListPage, VotacionesVotationListPage },
    data() {
        return {
            current_component: null,
            show_stage_votation: false,
            campaign: {}
        }
    },
    provide() {
        const CampaignProvide = {};

        Object.defineProperty(CampaignProvide, "campaign", {
            enumerable: true,
            get: () => this.campaign,
        });

        return { CampaignProvide };
    },
    mounted() {
        const vue = this;
        vue.loadData();
    },
    methods:{
        loadData() {
            const vue = this;

            vue.showLoader();

            vue.$http.get(`/votaciones/get_campaign/${vue.campaign_id}`)
               .then((res) => {
                const { data } = res.data;

                vue.show_stage_votation = !(data.stage_votation === null);
                vue.campaign = { ...data };
                vue.current_component = 'VotacionesPostulationListPage';
                
                vue.hideLoader();
            });
        }
    }
}
</script>
