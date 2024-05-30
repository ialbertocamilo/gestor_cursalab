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

        <v-col cols="12" md="12" lg="12">
            <span class="mb-3">Selecciona criterios:</span>
            <!-- Hide modules -->
            <DefaultAutocompleteOrder
                v-if="hideModule"
                return-object
                dense
                label="Criterios"
                v-model="segment.criteria_selected"
                :items="new_criteria"
                multiple
                item-text="name"
                :hide-codes="selectedCriteriaIncludesModule() ? ['module'] : []"
                :ready-only-codes="selectedCriteriaIncludesModule() ? ['module'] : []"
                item-id="id"
                :count-show-values="4"
                :showSelectAll="false"
                :loading-state="true"
                :clearable-state="true"
            />

            <!-- Show modules -->
            <DefaultAutocompleteOrder
                v-if="!hideModule"
                return-object
                dense
                label="Criterios"
                v-model="segment.criteria_selected"
                :items="new_criteria"
                multiple
                item-text="name"
                :ready-only-codes="selectedCriteriaIncludesModule() ? ['module'] : []"
                item-id="id"
                :count-show-values="4"
                :showSelectAll="false"
                :loading-state="true"
                :clearable-state="true"
            />
        </v-col>

        <v-divider class="mx-3"/>

        <v-col
               cols="12" md="12" lg="12">
            <span class="mb-2">Selecciona valores:</span>

            <segment-values
                v-for="(criterion, index) in segment.criteria_selected"
                :key="index"
                :criterion="criterion"
                @addDateRange="addDateRange($event)"
            />

            <v-divider class="" v-if="!showBtnDeleteSegment"/>

            <v-col
                cols="12"
                md="12"
                lg="12"
                class="d-flex justify-content-center"
                v-if="!showBtnDeleteSegment"
            >
                <v-btn
                    class="mr-1"
                    color="#796aee"
                    outlined
                    icon
                    @click="dialog_eliminar = true"
                >
                    <v-icon>mdi-trash-can</v-icon>
                </v-btn>
            </v-col>
        </v-col>
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
        "options",
        'hideModule',
        'showBtnDeleteSegment',
        "model_type","model_id"
    ],
    data() {
        return {
            new_criteria: [],
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

        vue.loadData();
    },
    methods: {
        addDateRange(data) {
            let vue = this;
            // console.log("addDateRange", data)
            let criterion = vue.segment.criteria_selected.find(criterion => criterion.code === data.criterion_code);

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
            console.log(resource);
            let base = `${vue.options.base_endpoint}`;
            let url = `${base}/create`;
            url = url + "?model_type=" + vue.model_type +
                "&model_id=" + vue.model_id;
            await vue.$http.get(url).then(({data}) => {
                let _data = data.data;

                // vue.segments = _data.segments
                vue.new_criteria = _data.criteria;

                // console.log(vue.new_criteria)
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
            let result = this.segment.criteria_selected.find(i => i.code === 'module')
            return !!result
        }
    }
};
</script>
