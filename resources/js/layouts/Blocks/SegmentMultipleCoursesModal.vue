<template>
    <DefaultDialog
        width="30vw"
        :options="options"
        @onCancel="onCancel"
        @onConfirm="onConfirm"
    >
        <template v-slot:content>

            <v-row>
               <v-col cols="12">
                   Selecciona los cursos a los cuales queires copiar la
                   segmentación previamente realizada
               </v-col>
            </v-row>
            <v-row>
                <v-col cols="6">
                    input
                </v-col>
                <v-col cols="6">
                    Seleccionados: 0/{{ limit }}
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <div>
                        <div class="row">
                            <v-col cols="6">
                                <v-icon>
                                    mdi-book
                                </v-icon>
                                Nombre del curso
                            </v-col>
                            <v-col cols="3">
                                ID: 23
                            </v-col>
                            <v-col cols="3">
                                Sin segmentación
                                <v-icon>
                                    mdi-account-group
                                </v-icon>
                                <v-icon>
                                    mdi-check-circle
                                </v-icon>
                                <v-icon>
                                    mdi-alert
                                </v-icon>
                            </v-col>
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
            limit: 10
        }
    },
    methods: {
        onCancel() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {

        },
        onConfirm() {
            let vue = this
            vue.$emit('onConfirm', true);
        },
        loadData(resource) {
        },
        loadSelects() {

        }
    }
}
</script>
