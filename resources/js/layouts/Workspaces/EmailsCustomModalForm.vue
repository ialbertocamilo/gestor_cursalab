<template>
    <div>
        <DefaultDialog 
            :options="options" 
            :width="width" 
            @onCancel="closeModal" 
            @onConfirm="confirmModal" 
            :showCardActions="false"
            :noPaddingCardText="false"
        >
            <template v-slot:content>
                <v-form ref="email_custom_modal">
                    <v-row justify="space-around">
                        <v-col cols="12">
                            <DefaultModalSectionExpand
                                v-for="(email,index) in custom_emails" :key="index"
                                :expand="email.expand"
                                :title="email.title"
                            >
                                <template slot="content">
                                    <div style="width: 100%;">
                                        <v-row justify="space-around">
                                            <v-col cols="8">
                                                <DefaultInput
                                                    dense
                                                    label="Título"
                                                    show-required
                                                    placeholder="Ingrese un título"
                                                    v-model="email.data_custom.title"
                                                    :rules="rules.name"
                                                    emojiable
                                                />
                                            </v-col>
                                            <v-col cols="4" class="d-flex align-items-center">
                                                <DefaultToggle 
                                                    v-model="email.data_custom.show_subworkspace_logo" 
                                                    active-label="Mostrar el logo del módulo"
                                                    inactive-label="Mostrar el logo del módulo"
                                                    dense
                                                />
                                            </v-col>
                                        </v-row>
                                        
                                        <DefaultRichText
                                            label="Contenido"
                                            v-model="email.data_custom.content"
                                            :height="300"
                                            :key="`key_description_rich_text-${index}`"
                                            :ref="`ref_description_rich_text-${index}`"
                                            :maxLength="1000"
                                        />
                                        <div class="d-flex justify-content-end mt-4">
                                            <DefaultButton
                                                label="Ver y guardar"
                                                :outlined="true"
                                                @click="saveCustomEmail(email)"
                                            />
                                        </div>
                                    </div>
                                </template>
                            </DefaultModalSectionExpand>
                        </v-col>
                    </v-row>
                </v-form>
            </template>
        </DefaultDialog>
    </div>
</template>

<script>
import DefaultRichText from "../../components/globals/DefaultRichText";

export default {
    components:{DefaultRichText},
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            custom_emails:[],
            workspace:null,
            rules: {
                name: this.getRules(['required', 'max:120']),
            },
        };
    },

    methods: {
        closeModal() {
            let vue = this
            vue.custom_emails = [];
            vue.workspace = null;
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
        }
        ,
        async confirmModal() {
            let vue = this
            
            // vue.$emit('onConfirm')
        },
        async saveCustomEmail(email){
            let vue = this;
            let url = `/workspaces/${vue.workspace.id}/save-custom-emails`;
            const redirect = `/workspaces/${vue.workspace.id}/show-email/welcome-email`
            vue.showLoader();
            await this.$http
                .post(url,{
                    custom_email:email
                })
                .then(({data}) => {
                    window.open(redirect, '_blank');
                    this.hideLoader();
                })
                .catch((error) => {
                    this.hideLoader();
                })
        },
        resetSelects() {
            let vue = this
        },
        async loadData(workspace) {
            let vue = this;
            vue.workspace = workspace;
            let url = `/workspaces/${workspace.id}/custom-emails`;
            vue.showLoader();
            await this.$http
                .get(url)
                .then(({data}) => {
                    vue.custom_emails = data.data;
                    this.hideLoader();
                })
                .catch((error) => {
                    this.hideLoader();
                })
        },
        async loadSelects() {
            let vue = this;
        },
    }
}
</script>
