<template>
    <div class="px-4" style="width: 100%;">

        <v-row v-for="(criteria, index) in segment.direct_segmentation" :key="index">
            <v-col cols="12" md="12" lg="12">
                <div class="bx_seg">
                    <div v-if="segment.direct_segmentation[index] == null" style="width: 100%;">
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
                    </div>
                    <div v-else style="width: 100%;">
                        <segment-values
                            :criterion="segment.direct_segmentation[index]"
                            @addDateRange="addDateRange($event, index)"
                        />
                    </div>
                    <div class="bx_delete_segment">
                        <v-btn
                            class="mr-1"
                            color="#796aee"
                            icon
                            @click="deleteCriterio(index)"
                            :disabled="segment.direct_segmentation[index] == null"
                        >
                            <v-img src="/img/checklist/trash_enabled.svg" v-if="segment.direct_segmentation[index] != null"/>
                            <v-img src="/img/checklist/trash_disabled.svg" v-else/>
                        </v-btn>
                    </div>
                </div>
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
                    @click="addCriterio($event)"
                    :disabled="disabled_btn"
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
            dialog_guardar: false,
            disabled_btn: true
        };
    },
    mounted() {
        let vue = this;
        vue.segment.loading = true;
        setTimeout(() => {
            vue.segment.loading = false;
        }, 1200);
        // this.addCriterio();

        vue.loadData();
    },
    watch: {
        segment: {
            handler(n, o) {
                let vue = this;

                let direct_segmentation = this.segment.direct_segmentation;

                this.segment.criteria_selected = direct_segmentation
                vue.disabled_btn = false;

                if (direct_segmentation != null) {
                    direct_segmentation.forEach(element => {
                        if (element == null || element.values_selected == undefined || element.values_selected == null) {
                            vue.disabled_btn = true;
                        }
                    });
                }
                vue.disabledBtnModal();
            },
            deep: true
        }
    },
    methods: {
        disabledBtnModal() {
            let vue = this;
            vue.$emit("disabledBtnModal");
        },
        addCriterio() {
            let direct_segmentation = this.segment.direct_segmentation;
            direct_segmentation.push(null);
            this.disabled_btn = true;
        },
        addDateRange(data, index) {
            let vue = this;
            let criterion = vue.segment.direct_segmentation[index];

            if (criterion){
                const hasValuesSelected = criterion.hasOwnProperty('values_selected');
                if(!hasValuesSelected)
                    criterion = Object.assign(criterion, {values_selected: []});
                // criterion.values_selected = data.date_range_selected;
                // console.log(`CRITERION`, criterion);
                criterion.values_selected.push(data.new_date_range);
            }
        },
        async loadData(resource) {
            let vue = this;

            vue.showLoader();
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

                 vue.hideLoader()

                // console.log(vue.new_criteria)
                // console.log(vue.courseModules)
            });

            return 0;
        },
        deleteCriterio(index) {
            let vue = this;
            if (index !== -1) {
                vue.segment.direct_segmentation.splice(index, 1);
            }
        },
        selectedCriteriaIncludesModule() {
            let result = this.segment.direct_segmentation.code === 'module'
            return !!result
        }
    }
};
</script>
<style lang="scss">
.bx_seg {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border: 1px solid #D9D9D9;
    border-radius: 8px;
}
.bx_seg .v-input .v-input__slot {
    padding-right: 0 !important;
}
.bx_seg .v-input .v-input__slot fieldset {
    border: none;
}
.bx_seg .v-input .v-input__slot label.v-label {
    background-color: #fff;
    padding: 0 8px;
}
.bx_seg .v-select__slot .v-input__append-inner,
.bx_steps .bx_seg .v-text-field--enclosed.v-input--dense:not(.v-text-field--solo).v-text-field--outlined .v-input__append-inner {
    margin-top: 6px !important;
}
.bx_seg .v-btn:not(.v-btn--text):not(.v-btn--outlined):hover:before,
.bx_seg .v-btn:not(.v-btn--text):not(.v-btn--outlined):focus:before {
    opacity: 0 !important;
}
</style>
