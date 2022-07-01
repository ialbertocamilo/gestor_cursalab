<template>
    <v-dialog v-model="modalData.open" width="50%" scrollable @click:outside="close">
        <v-card>
            <v-card-title class="default-dialog-title">
                {{ modalData.title }} /&nbsp;<b>Entrenador:&nbsp;</b> {{ alumno.entrenador }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="close">
                    <v-icon v-text="'mdi-close'"/>
                </v-btn>
            </v-card-title>
        <!--
            <v-card-title class="pb-0">
                {{ modalData.title }}
                <v-spacer></v-spacer>
                <v-btn icon @click="close">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
                <v-card-subtitle class="pb-0">
                    <p class="title_filtro_alumno p-0"></p>
                </v-card-subtitle>

            </v-card-title>
            <hr> -->
            <v-card-text class="pb-0" style="max-height: 65%;">
                <v-simple-table>
                    <template v-slot:default>
                        <thead>
                        <tr>
                            <th class="text-left" style="width:70%">
                                Checklist
                            </th>
                            <th class="text-center">
                                Avance
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="alumno.checklists.length === 0">
                            <td colspan="4" class="text-center"> - El alumno no tiene checklists asignados -</td>
                        </tr>
                        <tr
                            v-else
                            v-for="item in alumno.checklists"
                            :key="item.id"
                        >
                            <td style="font-size: 1rem;">{{ item.titulo }}</td>
                            <td>
                                <v-progress-linear
                                    :value="item.avance"
                                <DefaultStaticProgressLinear
                                    :text="`${item.avance}%`"
                                    :color="getColor(item.avance)"
                                />
                            </td>
                        </tr>
                        </tbody>
                    </template>
                </v-simple-table>

                <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                    <v-row justify="center" class="mx-0">
                        <v-col cols="4" class="d-flex justify-content-around py-0">
                            <v-btn
                                class="default-modal-action-button  mx-1"
                                text
                                elevation="0"
                                :ripple="false"
                                color="primary"
                                @click="close"
                            >
                                Entendido
                            </v-btn>

                        </v-col>
                    </v-row>
                </v-card-actions>

            </v-card-text>
        </v-card>
    </v-dialog>
</template>

<script>

import DefaultStaticProgressLinear from "../../../components/globals/DefaultStaticProgressLinear";
// import DefaultStaticProgressLinear from "../../../DefaultStaticProgressLinear";

export default {
    components: {DefaultStaticProgressLinear},
    props: {
        modalData: {
            type: Object,
            required: true
        },
        alumno: {
            type: Object,
            required: true
        }
    },
    methods: {
        close() {
            this.$emit('onClose');
        },

        getColor(avance) {
            if (avance === 100) {
                return 'success'
            } else {
                return 'amber'
            }
        }
    }
}
</script>
<style>
.title_filtro_alumno {
    font-size: 1.1rem;
    font-weight: 500;
    letter-spacing: .0125em;
}
</style>
