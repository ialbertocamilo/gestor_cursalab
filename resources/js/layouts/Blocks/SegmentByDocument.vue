<template>
    <v-row justify="space-around">
        <v-col cols="10">
            <v-row>
                <v-col cols="8" class="d-flex justify-content-center flex-row">
                    <DefaultInput
                        clearable dense
                        v-model="search"
                        placeholder="Buscar por nombre, correo o documento"
                        append-icon="mdi-magnify"
                        :loading="autocomplete_loading"
                        class="col-11"
                    />
                    <v-file-input
                        show-size
                        label="Suba el archivo"
                        v-model="file"
                        color="#796aee"
                        hide-details="auto"
                        dense
                        outlined
                        hide-input
                        prepend-icon="mdi-file-upload"
                        @change="uploadExcel"
                        class="justify-end upload-file-segment"
                    >
                        <template v-slot:append-outer>
                        </template>
                    </v-file-input>
                </v-col>
                <v-col cols="4">
                    <a class="pt-2 justify-end"
                       href="/templates/Plantilla-Segmentacion.xlsx"
                       v-text="'Descargar plantilla'"
                    />
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <span>Usuarios disponibles:</span>
                </v-col>

                <v-col cols="12">

                    <div class="box-document-segmentation-results">
                        <ul>
                            <li v-for="(user,index) in filter_result" :key="index"
                                class="d-flex justify-content-between align-items-center">

                                {{ user.document }} - {{ user.fullname }}

                                <v-btn icon primary small :ripple="false"
                                       @click="addUser(user)">
                                    <v-icon small v-text="'mdi-plus'"/>
                                </v-btn>

                            </li>

                            <li class="text-center" v-show="!filter_result.length">
                                No se encontraron resultados.
                            </li>
                        </ul>
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <span>
                        Usuarios agregados:
                        <span v-text="segment.criteria_selected.length"></span>
                    </span>
                </v-col>

            </v-row>
            <v-row>
                <v-col cols="6" v-show="segment.criteria_selected.length">
                    <DefaultInput
                        clearable dense
                        v-model="usersearch"
                        placeholder="Buscar por nombre o documento"
                    />
                </v-col>
                <v-col cols="6" v-show="segment.criteria_selected.length">
                    <DefaultButton
                            icon="mdi-download"
                            :outlined="true"
                            label="Descargar"
                        />
                    <a class="pt-2 justify-end"
                        @click="deleteAllUsers()"
                       href="#"
                       v-text="'Eliminar a todos'"
                    />
                </v-col>
                <v-col cols="12">

                    <div class="box-selected-segmentation-document">
                        <ul>
                            <li v-for="user in arrayCriteriaSelected"
                                class="d-flex justify-content-between align-items-center">

                                {{ user.document }} - {{ user.fullname }}

                                <v-btn icon primary small :ripple="false"
                                       @click="deleteUser(user)">
                                    <v-icon small v-text="'mdi-minus'"/>
                                </v-btn>

                            </li>

                            <li class="text-center" v-show="!arrayCriteriaSelected.length">
                                No se encontraron resultados.
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
        },
        currentClean:{
            required: true
        },
        modulesIds: {
            default: [],
            type: Array
        }
    },
    data() {
        return {
            search: null,
            autocomplete_loading: false,
            file: null,
            debounce: null,
            filter_result: [],
            usersearch: null
        };
    },
    computed: {
        arrayCriteriaSelected() {
            const vue = this;
            const UserSelecteds = vue.segment.criteria_selected;
            let usersearch = vue.usersearch || '';
                usersearch = usersearch.trim();

            //check if empty
            if(!usersearch.length) return UserSelecteds;
                usersearch = usersearch.toLowerCase();

            return UserSelecteds.filter( ({ document: doc, fullname: full }) => {
                const searchDoc =  (doc.toLowerCase()).includes(usersearch);
                const searchFull =  (full.toLowerCase()).includes(usersearch);

                return (searchDoc || searchFull);
            });
        }
    },
    watch: {
        currentClean: {
            handler(tabs) {
                const vue = this;
                vue.resetFields();
            }
        },
        search(filter_text) {
            let vue = this;

            if (filter_text === null) return vue.filter_result = [];

            if (filter_text.length <= 2) return vue.filter_result = [];

            const { docState, docData } = vue.checkIfExistUser(filter_text);
            if (docState) return vue.filter_result = [];

            vue.autocomplete_loading = true;

            clearTimeout(this.debounce);

            this.debounce = setTimeout(() => {
                let data = { filter_text: filter_text,
                             omit_documents: docData };

                // Add modules ids array to request if it has items

                if (vue.modulesIds.length) {
                    data.modulesIds = vue.modulesIds
                }

                const url = `/segments/search-users`;
                vue.$http.post(url, data)
                    .then(({data}) => {
                        const users = (data.data.users) ? data.data.users :  data.data;
                        vue.filter_result = users;
                        vue.autocomplete_loading = false;
                    })
                    .catch(err => {
                        vue.autocomplete_loading = false;
                    })

            }, 1600);
        },
    },
    methods: {
        checkIfExistUser(currentdoc) {
            currentdoc = currentdoc.trim();

            const vue = this;
            const UserDocuments = vue.segment.criteria_selected.map( ({document: doc}) => doc );
            return { docState : UserDocuments.includes(currentdoc),
                     docData: UserDocuments };
        },
        addUser(user) {
            let vue = this;

            vue.$emit("addUser", user);
        },
        deleteAllUsers(){
            let vue = this;
            vue.$emit("deleteUserAll");
        },
        deleteUser(user) {
            let vue = this;

            vue.$emit("deleteUser", user);
        },
        addOrRemoveFromFilterResult(user, action = 'add') {
            let vue = this;

            const index = vue.filter_result.findIndex(el => el.document == user.document);

            if (index !== -1) {

                // if (action === 'add')
                //     vue.filter_result.push(user);

                if (action === 'remove')
                    vue.filter_result.splice(index, 1);
            }
        },
        uploadExcel() {
            let vue = this;

            let formData = new FormData();
            formData.append("file", vue.file);

            // Add modules ids array to request if it has items

            if (vue.modulesIds.length) {
                formData.append("modulesIds", vue.modulesIds);
            }

            vue.showLoader();

            const url = `/segments/search-users`;
            vue.$http.post(url, formData)
                .then(({data}) => {
                    // vue.filter_result = data.data;
                    const users = (data.data.users) ? data.data.users :  data.data;
                    if(data.data.users_not_found && data.data.users_not_found.length>0){
                        let headers = ["Documento"];
                        let values =["document"];
                        console.log('not_found',data.data.users_not_found);
                        vue.descargarExcelFromArray(
                            headers,
                            values,
                            data.data.users_not_found,
                            "Documentos_no_encontrados_" + Math.floor(Math.random() * 1000),
                            "Se han encontrado observaciones. Descargar lista de observaciones"
                        );
                    }
                    console.log('users',users.length);
                    users.forEach((user) => {
                        const exist = vue.segment.criteria_selected.filter(el => el.document == user.document);

                        if (exist.length === 0){
                            vue.$emit("addUser", user);
                        }
                    });

                    vue.file = null;

                    vue.hideLoader();
                })
                .catch(error => {
                    vue.hideLoader();
                })
        },
        resetFields() {
            let vue = this;
            vue.search = null;
            vue.usersearch = null;
            vue.filter_result = [];
        }
    }
}

</script>


<style lang="scss">
@import "resources/sass/variables";

.upload-file-segment .v-input__prepend-outer {
    margin-top: 0px !important;
}

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
    height: 100px;
    border: 1.5px solid $primary-default-color;
    border-radius: 10px;
    overflow-x: hidden;
    overflow-y: scroll;

    li {
        list-style: none;
    }
}

</style>
