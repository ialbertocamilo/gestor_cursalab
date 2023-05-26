<template>
    <DefaultDialog
        :options="options"
        :width="width"
        :persistent="true"
        :show-card-actions="false"
        @onCancel="closeModal"
    >
        <template v-slot:content>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :filters="filters"
                @showdetails="
                    openFormModal(
                        detailsModalOptions,
                        $event,
                        'status',
                        `Detalles de ${$event.name} por ${$event.user} el ${$event.created_at}`
                    )
                "
            />

            <AuditoriaDetailsModal
                width="50vw"
                :ref="detailsModalOptions.ref"
                :options="detailsModalOptions"
            />
        </template>
    </DefaultDialog>
</template>

<script>
import AuditoriaDetailsModal from "../../layouts/Auditoria/AuditoriaDetailsModal";

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: {
            type: String,
            required: false
        },
        model_id: Number,
        model_type: String
    },
    components: { AuditoriaDetailsModal },
    data() {
        return {
            dataTable: {
                ref: "AuditoriaTable",
                endpoint: `/auditoria/search`,
                headers: [
                    {
                        text: "Fecha",
                        value: "created_at",
                        align: "center",
                        sortable: true
                    },
                    {
                        text: "Usuario",
                        value: "user",
                        align: "center",
                        sortable: false
                    },
                    {
                        text: "Acción",
                        value: "event",
                        align: "center",
                        sortable: false
                    },
                    {
                        text: "# Modificados",
                        align: "center",
                        value: "modified_fields_count",
                        sortable: false
                    },

                    {
                        text: "Acciones",
                        value: "actions",
                        align: "center",
                        sortable: false
                    }
                ],
                actions: [
                    {
                        text: "Detalles",
                        icon: "mdi mdi-file-document-multiple",
                        type: "action",
                        method_name: "showdetails"
                    }
                ]
            },

            filters: {
                model_type: this.model_type,
                model_id: null
            },
            detailsModalOptions: {
                ref: "AuditoriaDetailsModal",
                open: false,
                resource: "Audit"
                // persistent: true
            }
        };
    },
    mounted() {},
    methods: {
        closeModal() {
            let vue = this;
            // console.log('emit Cancel modal')
            vue.$emit("onCancel");
        },

        loadData(resource) {
            this.filters.model_id = resource.id;
            this.refreshDefaultTable(this.dataTable, this.filters);
            console.log("loadData", this.filters);
        },
        resetValidation() {},
        loadSelects() {}
    }
};
</script>
