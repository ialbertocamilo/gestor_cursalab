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
                        <ul>
                            <li v-for="user in filter_result"
                                class="d-flex justify-content-between align-items-center">

                                {{ user.document }} - {{ user.fullname }}

                                <v-btn icon primary small :ripple="false"
                                       @click="addUser(user)">
                                    <v-icon small v-text="'mdi-plus'"/>
                                </v-btn>

                            </li>
                        </ul>
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">

                    <div class="box-selected-segmentation-document">
                        <ul>
                            <li v-for="user in segment"
                                class="d-flex justify-content-between align-items-center">

                                {{ user.document }} - {{ user.fullname }}

                                <v-btn icon primary small :ripple="false"
                                       @click="deleteUser(user)">
                                    <v-icon small v-text="'mdi-minus'"/>
                                </v-btn>

                            </li>
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

            if (filter_text.length <= 2) return;

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
    methods: {
        addUser(user) {
            let vue = this;

            const data = {}

            vue.$emit("addUser", user);
        },
        deleteUser(user) {
            let vue = this;

            const data = {}

            vue.$emit("deleteUser", user);
        }
    }
}

</script>


<style lang="scss">
@import "resources/sass/variables";

.box-document-segmentation-results {
    padding: 10px 5px;
    min-height: 150px;
    height: 100px;
    border: 1.5px solid $primary-default-color;
    border-radius: 10px;
    overflow-x: hidden;
    overflow-y: scroll;

    li {
        list-style: none;
    }
}

.box-selected-segmentation-document {
    padding: 10px 5px;
    min-height: 150px;
    border: 1.5px solid $primary-default-color;
    border-radius: 10px;

    li {
        list-style: none;
    }
}

</style>
