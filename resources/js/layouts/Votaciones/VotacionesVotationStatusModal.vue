<template>
    <DefaultDialog
        width="50vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>
            <v-row>
                <v-col cols="6">
                    <DefaultInput
                        clearable dense
                        v-model="filters.q"
                        label="Buscar por valor de criterio ..."
                        @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                        append-icon="mdi-magnify"
                        @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                </v-col>
                <v-col cols="6"></v-col>
                <v-col cols="12">
                    <DefaultTable
                        :ref="dataTable.ref"
                        :data-table="dataTable"
                        :filters="filters"
                    />
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
    inject:['CampaignProvide'],
    data() {
        return {
            resource: null,
            dataTable: {
                endpoint:`/votaciones/${this.CampaignProvide.campaign.id}/votacion/search_status`,
                ref: 'VotacionesStatusTable',
                headers: [
                    { text: 'Criterio', value: 'criterio', sortable: false },
                    { text: 'Usuarios', value: 'users_criterio', sortable: false },
                    { text: 'Votos Esperados', align: 'center', value: 'users_waiting_votes', sortable: false },
                    { text: 'Votos Emitidos', align: 'center', value: 'users_emiting_votes', sortable: false },
                    { text: 'Validez', align: 'right', value: 'criterio_porcent', sortable: false }
                ],
            },
            filters: {
                q: '',
                active: null,
                criterio_value_id: null
            },
        }
    },
    methods: {
        resetValidation() {},
        loadData(resource) {},
        loadSelects() {},
        onCancel() {
            let vue = this;
            vue.$emit('onCancel')
        },
        onConfirm() {
            let vue = this
            vue.$emit('onConfirm', true);
        }
    }
}
</script>
