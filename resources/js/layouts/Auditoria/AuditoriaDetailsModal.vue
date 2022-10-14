<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onConfirm="confirmModal"
        @onCancel="closeModal"
    >
        <template v-slot:content>

            <v-tabs
                fixed-tabs
                v-model="tabs"
            >
                <v-tabs-slider></v-tabs-slider>
                <v-tab
                    href="#tab-1"
                    :key="1"
                    class="primary--text"
                >
                    <v-icon>mdi-text-box-outline</v-icon>
                    <span class="ml-3">Registros</span>
                </v-tab>

                <v-tab
                    href="#tab-2"
                    :key="2"
                    class="primary--text"
                >
                    <v-icon>mdi-text-box-edit-outline</v-icon>
                    <span class="ml-3">Modificados</span>
                </v-tab>

                <v-tab
                    href="#tab-3"
                    :key="3"
                    class="primary--text"
                >
                    <v-icon>mdi-text-box-search-outline</v-icon>
                    <span class="ml-3">Detalle</span>
                </v-tab>
            </v-tabs>

            <v-tabs-items v-model="tabs">
                <v-tab-item :key="1" :value="'tab-1'">
                    <v-simple-table class="pt-4">
                        <template v-slot:default>
                            <tbody>
                                <tr>
                                    <td><strong>Usuario</strong></td>
                                    <td>{{ activeAudit.user }}</td>
                                </tr>
                                <tr>
                                    <td><strong>IP</strong></td>
                                    <td>{{ activeAudit.ip }}</td>
                                </tr>
                                <tr>
                                    <td><strong>URL</strong></td>
                                    <td>{{ activeAudit.url }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Acción</strong></td>
                                    <td>{{ activeAudit.event }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sección</strong></td>
                                    <td>{{ activeAudit.model }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Registro</strong></td>
                                    <td>{{ activeAudit.name }}</td>
                                </tr>
                                <tr>
                                    <td><strong># modificados</strong></td>
                                    <td>{{ activeAudit.modified_fields_count }}</td>
                                </tr>
                            </tbody>
                        </template>
                    </v-simple-table>


                </v-tab-item>
                <v-tab-item :key="2" :value="'tab-2'">
                    <v-simple-table class="pt-4">
                        <template v-slot:default>
                            <tbody>
                            <tr v-for="property of activeAudit.modified">
                                <td><strong>{{ property.label }}</strong></td>
                                <td>{{ property.value }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>
                </v-tab-item>
                <v-tab-item :key="3" :value="'tab-3'">

                    <v-simple-table class="pt-4">
                        <template v-slot:default>
                            <tbody>
                            <tr v-for="property of activeAudit.total">
                                <td><strong>{{ property.label }}</strong></td>
                                <td>{{ property.value }}</td>
                            </tr>
                            </tbody>
                        </template>
                    </v-simple-table>

                </v-tab-item>
            </v-tabs-items>


        </template>
    </DefaultDialog>
</template>

<script>

import DefaultRichText from "../../components/globals/DefaultRichText";
import moment from "moment";

const fields = [
    "nombre",
    "active",
    "destino",
    "link",
    "module_ids",
    "contenido",
    "publish_date",
    "end_date"
];
const file_fields = ["imagen", "archivo"];

export default {
    components: { DefaultRichText },
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            tabs: null,
            activeAudit: {}
        };
    },
    mounted() {

    },
    methods: {
        loadData(activeAudit) {
            this.activeAudit = activeAudit
        },
        resetValidation() {

        },
        loadSelects() {

        },
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        }
        ,
        confirmModal() {
            //console.log(this.options.activeAudit.id)
            this.$emit('onConfirm')
        }
    }
}
</script>
