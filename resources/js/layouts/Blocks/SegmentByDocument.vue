<template>
    <v-row>
        <v-col cols="12">
            <v-row>
                <v-col cols="10">
                    <DefaultInput
                        v-model="filter"
                        clearable dense
                        label="Buscar por nombre"
                        append-icon="mdi-magnify"
                    />
                </v-col>
                <v-col cols="2">

                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">

                    <div class="box-document-segmentation-results">

                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">

                    <div class="box-selected-segmentation-document">
                        <ul>
                            <li v-for="user in segment.criteria_selected"></li>
                        </ul>
                    </div>
                </v-col>

            </v-row>
        </v-col>
    </v-row>
</template>

<script>
export default {
    props:{
        segment: {
            required: true
        }
    },
    data() {
        return {
            filter: null,
        };
    },
    methods: {}
}

</script>


<style lang="scss">
@import "resources/sass/variables";

.box-document-segmentation-results {
    min-height: 150px;
    border: 1.5px solid $primary-default-color;
    border-radius: 10px;
}

.box-selected-segmentation-document {
    min-height: 150px;
    border: 1.5px solid $primary-default-color;
    border-radius: 10px;
}

</style>
