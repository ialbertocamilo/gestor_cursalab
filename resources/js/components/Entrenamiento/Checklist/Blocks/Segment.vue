<template>
    <div class="px-4">
        <v-dialog persistent max-width="400" v-model="dialog_eliminar">
            <v-card>
                <v-card-title class="default-dialog-title">
                    Eliminar segmento
                </v-card-title>
                <v-card-text class="py-5">
                    ¿Está seguro de eliminar este segmento de segmentación?
                    <br/>
                    Después de guardar, esta acción no podrá revertirse.
                </v-card-text>
                <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)">
                    <DefaultModalActionButton
                        @cancel="dialog_eliminar = false"
                        @confirm="borrarBloque(segment)"
                    />
                </v-card-actions>
            </v-card>
        </v-dialog>

        <v-row v-for="(criteria, index) in segment.direct_segmentation" :key="index">
            <v-col cols="12" md="12" lg="12" v-if="segment.direct_segmentation[index] == null">
                <DefaultAutocomplete
                    v-model="segment.direct_segmentation[index]"
                    :ready-only-codes="selectedCriteriaIncludesModule() ? ['module'] : []"
                    :items="new_criteria"
                    label="Selecciona criterios"
                    item-text="name"
                    item-value="id"
                    dense
                    returnObject
                />
            </v-col>

            <v-col cols="12" md="12" lg="12" v-else>
                <segment-values
                    :criterion="segment.direct_segmentation[index]"
                    @addDateRange="addDateRange($event)"
                />
            </v-col>
        </v-row>
        <v-row>
            <v-col
                cols="12"
                md="12"
                lg="12"
                class="d-flex justify-content-center"
            >
                <v-btn
                    class="mr-1"
                    color="#796aee"
                    icon
                    @click="prueba"
                >
                    <v-icon>mdi-plus-circle</v-icon>
                </v-btn>
            </v-col>
        </v-row>
    </div>
</template>

<script>
import SegmentValues from "./SegmentValues";

export default {
    components: {
        SegmentValues
    },
    props: [
        "segment",
        "segments",
        "criteria",
        "courseModules",
        'isCourseSegmentation',
        "options"
    ],
    data() {
        return {
            new_criteria: [],
            lista_criterios: [],
            absolute: true,
            loading_guardar: false,
            dialog_eliminar: false,
            dialog_guardar: false
        };
    },
    mounted() {
        let vue = this;
        vue.segment.loading = true;
        setTimeout(() => {
            vue.segment.loading = false;
        }, 1200);
        // this.prueba();

        vue.loadData();
    },
    methods: {
        prueba() {
            this.segment.direct_segmentation.push(null);
        },
        addDateRange(data) {
            let vue = this;
            let criterion = vue.segment.direct_segmentation;

            if (criterion){
                const hasValuesSelected = criterion.hasOwnProperty('values_selected');
                if(!hasValuesSelected)
                    criterion = Object.assign(criterion, {values_selected: []});
                // criterion.values_selected = data.date_range_selected;
                console.log(`CRITERION`, criterion);
                criterion.values_selected.push(data.new_date_range);
            }
        },
        async loadData(resource) {
            let vue = this;
            // vue.errors = []

            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })

            let base = `${vue.options.base_endpoint}`;
            let url = `${base}/create`;

            await vue.$http.get(url).then(({data}) => {
                let _data = data.data;

                // vue.segments = _data.segments
                vue.new_criteria = _data.criteria;

                console.log(vue.new_criteria)
                // console.log(vue.courseModules)
            });

            return 0;
        },
        borrarBloque(segment) {
            let vue = this;

            vue.$emit("borrar_segment", segment);
            vue.dialog_eliminar = false;
        },
        selectedCriteriaIncludesModule() {
            let result = this.segment.direct_segmentation.code === 'module'
            return !!result
        }
    }
};
</script>
