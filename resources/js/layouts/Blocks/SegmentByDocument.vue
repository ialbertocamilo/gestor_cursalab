<template>
    <v-row>
        <v-col cols="12">
            <v-row>
                <v-col cols="10">
                    <DefaultInput
                        clearable dense
                        v-model="search"
                        placeholder="Buscar por nombre o documento"
                        append-icon="mdi-magnify"
                        :loading="autocomplete_loading"
                        class="col-11"
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
    props: {
        segment: {
            required: true
        }
    },
    data() {
        return {
            search: null,
            autocomplete_loading: false,
            file: null,
            debounce: null,
            filter_result: [],
        };
    },
    computed: {},
    watch: {
        search(filter_text) {
            let vue = this;

            if (filter_text === null) return;

            if (filter_text.length <= 3) return;

            vue.autocomplete_loading = true;

            clearTimeout(this.debounce);

            this.debounce = setTimeout(() => {
                let data = {filter_text: filter_text};
                const url = `/segments/search-users`;

                vue.$http.post(url, data)
                    .then(({data}) => {
                        vue.filter_result = data.data.users;
                        vue.autocomplete_loading = false;
                    })

            }, 1600);
        },
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
