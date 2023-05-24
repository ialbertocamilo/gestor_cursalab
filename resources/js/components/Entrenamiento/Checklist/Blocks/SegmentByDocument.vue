<template>
    <v-row justify="space-around">
        <v-col cols="12" style="height: 400px;">
            <v-row>
                <v-col cols="5" class="d-flex flex-row bx_search_by_document">
                    <DefaultInput
                        clearable dense
                        v-model="search"
                        placeholder="Búsqueda"
                        append-icon="mdi-magnify"
                        :loading="autocomplete_loading"
                        class="col-12"
                    />
                </v-col>
                <v-col cols="7" class="d-flex justify-content-between align-items-center">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="d-flex justify-content-center align-items-center mx-2 est_link">
                            <v-file-input
                                show-size
                                label="Suba el archivo"
                                v-model="file"
                                color="#796aee"
                                hide-details="auto"
                                dense
                                outlined
                                hide-input
                                @change="uploadExcel"
                                class="upload-file-segment flex-initial hide_icon"
                                ref="input_file_upload"
                                id="input_file_upload"
                                accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel"
                            >
                                <template v-slot:append-outer>
                                </template>
                            </v-file-input>
                            <div @click="$refs.input_file_upload.$refs.input.click()" style="cursor: pointer;">
                                <img src="/img/checklist/upload.svg" class="mrb_3">
                                <span class="text_default">Subir archivo</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center align-items-center mx-2 est_link">
                            <a class="justify-end" href="/templates/Plantilla-Segmentacion.xlsx">
                                <img src="/img/checklist/download.svg" class="mrb_3">
                                <span class="text_default">Plantilla</span>
                            </a>
                        </div>
                    </div>
                    <div class="d-flex">
                        <div class="d-flex justify-content-center align-items-center mx-2">
                            <span class="text_default">{{ arrayCriteriaSelected.length || 0 }} seleccionados</span>
                        </div>
                        <div class="bx_select_all">
                            <label class="text_default" for="checkbox">
                                Todos
                                <div class="round">
                                    <input type="checkbox" id="checkbox" @change="selectAll" v-model="select_all" />
                                    <span class="checkmark"></span>
                                </div>
                            </label>
                        </div>
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <div class="box_resultados">
                        <div class="bx_message" v-if="filter_result.length == 0">
                            <span class="text_default">Resultados de búsqueda</span>
                        </div>
                        <ul v-else>
                            <li v-for="user in filter_result" :key="user.id" class="d-flex align-center justify-content-between">
                                <span class="text_default">{{ user.document }} - {{ user.fullname }}</span>
                                <v-btn small icon :ripple="false" @click="addUser(user)">
                                    <v-icon class="icon_size" small color="black"
                                            style="font-size: 1.25rem !important;">
                                        mdi-plus-circle
                                    </v-icon>
                                </v-btn>
                            </li>
                        </ul>
                    </div>
                </v-col>
            </v-row>
            <v-row>
                <!-- <v-col cols="7" v-show="true">
                    <DefaultInput
                        clearable dense
                        v-model="usersearch"
                        placeholder="Buscar por nombre o documento"
                    />
                </v-col> -->
                <v-col cols="12">
                    <div class="box_seleccionados">
                        <div class="bx_message" v-if="arrayCriteriaSelected.length == 0">
                            <span class="text_default">Seleccionados</span>
                        </div>
                        <ul v-else>
                            <li v-for="user in arrayCriteriaSelected" :key="user.id" class="d-flex align-center justify-content-between">
                                <span class="text_default">{{ user.document }} - {{ user.fullname }}</span>
                                <v-btn small icon :ripple="false" @click="deleteUser(user)">
                                    <v-icon class="icon_size" small color="black"
                                            style="font-size: 1.25rem !important;">
                                        mdi-minus-circle
                                    </v-icon>
                                </v-btn>
                            </li>
                        </ul>
                    </div>
                </v-col>

            </v-row>
        </v-col>
            <DialogConfirm
                v-model="deleteConfirmationDialog.open"
                :options="deleteConfirmationDialog"
                width="270px"
                title="Cambiar de estado del curso"
                overlay_opacity="0"
                subtitle="¡Estás a punto cambiar la configuración de un curso!"
                @onConfirm="deleteConfirmationDialog.open = false"
                @onCancel="deleteConfirmationDialog.open = false"
            />
    </v-row>
</template>

<script>
import DialogConfirm from "../../../basicos/DialogConfirm";
export default {
    components: {DialogConfirm},
    props: {
        segment: {
            required: true
        },
        currentClean:{
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
            usersearch: null,
            modalAlert: {
                ref: 'modalAlert',
                title: 'Alerta',
                contentText: 'Este checklist debe de tener por lo menos una (1) actividad "Califica a: Alumno"',
                open: false,
                endpoint: '',
                confirmLabel:"Entendido",
                hideCancelBtn:true,
            },
            deleteConfirmationDialog: {
                open: false,
                title_modal: 'Cargar usuarios',
                type_modal: 'upload',
                hide_btns: true,
                hide_header: true,
                content_modal: {
                    upload: {
                        status: 'success'
                    }
                },
            },
            select_all: false
        };
    },
    computed: {
        arrayCriteriaSelected() {
            const vue = this;
            if(vue.segment !== undefined && vue.segment !== null) {
                const UserSelecteds = vue.segment.segmentation_by_document;
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
            return [];
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
                const url = `/segments/search-users`;

                vue.$http.post(url, data)
                    .then(({data}) => {
                        vue.filter_result = data.data;
                        vue.autocomplete_loading = false;
                    })
                    .catch(err => {
                        vue.autocomplete_loading = false;
                    })

            }, 1600);
        },
    },
    methods: {
        selectAll() {
            let vue = this
            if(vue.filter_result.length > 0) {
                vue.filter_result.forEach(element => {
                    this.addUser(element)
                });
            }
            else {
                if(vue.arrayCriteriaSelected.length > 0) {
                    vue.arrayCriteriaSelected.forEach(element => {
                        this.deleteUser(element)
                    });
                }
            }
        },
        checkIfExistUser(currentdoc) {
            currentdoc = currentdoc.trim();

            const vue = this;
            if(vue.segment !== undefined && vue.segment !== null) {
                const UserDocuments = vue.segment.segmentation_by_document.map( ({document: doc}) => doc );
                return { docState : UserDocuments.includes(currentdoc),
                        docData: UserDocuments };
            }
            return [];
        },
        addUser(user) {
            let vue = this;

            vue.$emit("addUser", user);
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

            vue.showLoader();

            const url = `/segments/search-users`;
            vue.$http.post(url, formData)
                .then(({data}) => {
                    // vue.filter_result = data.data;
                    data.data.forEach((user) => {
                        const exist = vue.segment.segmentation_by_document.filter(el => el.document == user.document);

                        if (exist.length === 0){
                            vue.$emit("addUser", user);
                        }
                    });

                    vue.deleteConfirmationDialog.open = true
                    setTimeout(() => {
                        vue.deleteConfirmationDialog.open = false
                    }, 5500);

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
.flex-initial{
    flex: initial !important;
}
.est_link i.v-icon,
.est_link span.text_default {
    color: #5458ea !important;
}
button.v-icon.v-icon--link {
    font-size: 16px;
    color: #5458ea !important;
}
.est_link .v-input.upload-file-segment .v-input__prepend-outer {
    margin: 0;
}
.bx_search_by_document .v-input__append-inner button.v-btn {
    padding: 0 !important;
}
.bx_search_by_document .v-input__slot fieldset {
    border-radius: 8px;
}
.est_link a {
    text-decoration: none !important;
    display: flex;
    align-items: flex-end;
}
.mrb_3 {
    margin-bottom: 3px;
    margin-right: 3px;
}
.hide_icon .v-input__prepend-outer {
    display: none;
}
// check todos
.bx_select_all {
    display: flex;
    border-left: 1px solid #434D56;
    padding-left: 6px;
}
.bx_select_all label {
    display: flex;
    margin: 0;
}
.bx_select_all .round {
    position: relative;
    cursor: pointer;
    font-size: 22px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    margin-left: 5px;
}

.bx_select_all .round input {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  height: 0;
  width: 0;
}

.bx_select_all .round .checkmark {
    height: 14px;
    width: 14px;
    border-radius: 15px;
    border: 1px solid #434D56;
}

.bx_select_all .round:hover input ~ .checkmark {
  background-color: #5458ea;
}

.bx_select_all .round input:checked ~ .checkmark {
  background-color: #5458ea;
}

.bx_select_all .round .checkmark:after {
  content: "";
  position: absolute;
  display: none;
}

.bx_select_all .round input:checked ~ .checkmark:after {
  display: block;
}

.bx_select_all .round .checkmark:after {
    left: 50%;
    top: 50%;
    width: 7px;
    height: 7px;
    transform: translate(-50%,-50%);
    position: absolute;
    background: #fff;
    border-radius: 50%;
}
</style>
