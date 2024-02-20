<template>
    <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
        <template v-slot:content>
            <v-row class="modal_gestor_colab">
                <v-col cols="12" md="12" lg="12" class="pb-0">
                    <span class="text_default lbl_tit" v-text="show_section_criteria ? 'Selecciona los criterios sobres los que seleccionaras a los participantes de este curso.' : 'Lista de valores asignados.'"></span>

                    <!-- <span class="text_default lbl_tit fw-bold"><i class="fas fa-exclamation-triangle" style="color: #FF9800;"></i> Una vez confirmados, no se podrán retirar del beneficio.</span> -->
                </v-col>
                <v-col cols="12" v-if="!show_section_criteria">
                    <DefaultButton 
                        :min_content="false"
                        label="Deseo filtrar por criterios"
                        :outlined="true"
                        @click="showSectionCriteria()"
                    />
                </v-col>
                <v-col v-if="!show_section_criteria" cols="12" class="pb-0 pt-0">
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
                                <div class="bx_select_all" :class="{ disabled: filter_result.length == 0 && arrayCriteriaSelected.length == 0 }">
                                    <label class="text_default" for="checkbox">
                                        Todos
                                        <div class="round">
                                            <input type="checkbox" id="checkbox" @change="selectAll" v-model="select_all" :disabled="filter_result.length == 0 && arrayCriteriaSelected.length == 0" />
                                            <span class="checkmark" :class="{ selected: filter_result.length == 0 && arrayCriteriaSelected.length > 0 }"></span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </v-col>
                    </v-row>
                    <v-row justify="space-around">
                        <v-col cols="12" >
                            <v-row>
                                <v-col cols="6">
                                    <div class="d-flex justify-content-between mx-2">
                                        <div class="lbl_lists"><span>Colaboradores segmentados</span></div>
                                        <div class="lbl_lists"><span>Agregar</span></div>
                                    </div>
                                    <div class="box_resultados">
                                        <div class="bx_message" v-if="filter_result == null">
                                            <span class="text_default">Colaboradores segmentados</span>
                                        </div>
                                        <ul v-else>
                                            <li v-for="user in filter_result" :key="user.id" class="d-flex align-center justify-content-between">
                                                <span class="text_default">{{ user.document }} - {{ user.fullname }}</span>
                                                <v-btn small icon :ripple="false" @click="addUser(user)">
                                                    <v-icon class="icon_size" small color="#434D56"
                                                            style="font-size: 1.25rem !important;">
                                                        mdi-plus-circle
                                                    </v-icon>
                                                </v-btn>
                                            </li>
                                        </ul>
                                    </div>
                                </v-col>
                                <v-col cols="6">
                                    <div class="d-flex justify-content-between mx-2">
                                        <div class="lbl_lists"><span>Colaboradores registrados</span></div>
                                        <div class="d-flex justify-content-between">
                                            <div class="lbl_lists"><span>Retirar</span></div>
                                        </div>
                                    </div>
                                    <div class="box_seleccionados">
                                        <div class="bx_message" v-if="segment_by_document.segmentation_by_document == null">
                                            <span class="text_default">Colaboradores registrados</span>
                                        </div>
                                        <ul v-else>
                                            <li v-for="user in segment_by_document.segmentation_by_document" :key="user.id" class="d-flex align-center justify-content-between">
                                                <span class="text_default" style="max-width: calc(100% - 80px);">{{ user.document }} - {{ user.fullname }}</span>
                                                <div style="width: 70px;text-align: end;">
                                                    <v-btn small icon :ripple="false" @click="deleteUser(user)" class="p-0">
                                                        <v-icon class="icon_size" small :color="user.status != null && user.status.code == 'approved' ? '#A9B2B9' : '#434D56'"
                                                                style="font-size: 1.25rem !important;">
                                                            mdi-minus-circle
                                                        </v-icon>
                                                    </v-btn>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </v-col>
                            </v-row>
                        </v-col>
                    </v-row>
                </v-col>
                <v-col v-else cols="12"  class="d-flex justify-content-center pt-0 pb-0">
                    <UsuarioCriteriaSection
                        ref="CriteriaSection"
                        :options="options"
                        :user="resource"
                        :criterion_list="criterion_list"
                    />
                </v-col>
            </v-row>
        </template>
    </DefaultDialog>
</template>

<script>
import UsuarioCriteriaSection from "../Usuario/UsuarioCriteriaSection";

export default {
    components: { UsuarioCriteriaSection },
    props: {
        options: {
            type: Object,
            required: true
        },
        model_type:String,
        width: String
    },
    data() {
        return {
            isLoading: false,
            resourceDefault: {
                criterion_list:{}
            },
            resource: {
                criterion_list:{}
            },
            search: null,
            autocomplete_loading: false,
            file: null,
            debounce: null,
            filter_result: [],
            usersearch: null,
            filtrados: [],
            rules: {
                required: this.getRules(['required']),
            },
            segments:[],
            segment_by_document:[],
            arrayCriteriaSelected:[],
            select_all:false,
            modulesIds:[],
            modulesSchools:[],
            show_section_criteria : false,
            criterion_list:[]
        };
    },
    computed: {
        // list_filter_segmentados() {
        //     const vue = this;

        //     if (vue.search === null) {
        //         return vue.$props.segmentados;
        //     }
        //     return vue.$props.segmentados.filter((user) => {
        //         if(user.fullname != '' && user.fullname != null && user.document != '' && user.document != null)
        //         return (
        //             user.fullname.toLowerCase().includes(vue.search.toLowerCase()) ||
        //             user.document.toLowerCase().includes(vue.search.toLowerCase())
        //         );
        //     });
        // },
        // list_filter_users() {
        //     const vue = this;

        //     // if (vue.search === null) {
        //     // }
        //     return vue.segment_by_document.segmentation_by_document;
        //     return vue.segment_by_document.segmentation_by_document.filter((user) => {
        //         if(user.fullname != '' && user.fullname != null && user.document != '' && user.document != null)
        //         return (
        //             user.fullname.toLowerCase().includes(vue.search.toLowerCase()) ||
        //             user.document.toLowerCase().includes(vue.search.toLowerCase())
        //         );
        //     });
        // }
    },
    watch: {
        segmentados: {
            handler(n, o) {
                let vue = this;
                console.log(vue.$props.segmentados);
            },
            deep: true
        },
        seleccionados: {
            handler(n, o) {
                let vue = this;
                console.log(vue.$props.seleccionados);
            },
            deep: true
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
                        users.forEach((user) => {
                            const exist = vue.segment_by_document.segmentation_by_document.filter(el => el.document == user.document);

                            if (exist.length === 0){
                                vue.$emit("addUser", user);
                            }
                        });
                    })
                    .catch(err => {
                        vue.autocomplete_loading = false;
                    })

            }, 1600);
        },
    },
    methods: {
        closeModal() {
            let vue = this
            if(vue.show_section_criteria){
                vue.show_section_criteria = false;
                return;
            }
            vue.resetSelects()
            vue.resetValidation()
            vue.$emit('onCancel')
        }
        ,
        resetValidation() {
            let vue = this
        }
        ,
        async confirmModal() {
            let vue = this;
            if(vue.show_section_criteria){
                vue.showLoader();
                if(!vue.resource.criterion_list.module){
                    vue.showAlert(`Es necesario seleccionar algún valor para el módulo.`,'warning');
                }
                await axios.post('/users/list-users-by-criteria',{
                    criterion_list: vue.resource.criterion_list
                }).then(({data})=>{
                    vue.filter_result = data.data.users;
                    vue.hideLoader();
                    vue.showAlert(`Se han encontrado ${vue.filter_result.length} usuarios.`);
                    vue.show_section_criteria = false;
                }).catch(()=>{
                    vue.hideLoader();

                })
                vue.hideLoader();
                return;
            }
            let base = `${vue.options.base_endpoint}`;
            let url = `${base}/store`;
            let formData = JSON.stringify({
                    model_type: vue.model_type,
                    model_id: vue.resource.id,
                    code: 'segmentation-by-document',
                    segments: vue.segments,
                    segment_by_document: vue.segment_by_document
                });
                vue.$http.post(url, formData).then(({data}) => {
                    vue.$emit("onConfirm");
                    vue.closeModal();
                    vue.showAlert(data.data.msg);
                    vue.hideLoader();
                })
                .catch(error => {
                    if (error && error.errors) vue.errors = error.errors;
                    vue.hideLoader();
                });
            vue.$emit('onConfirm')
        }
        ,
        resetSelects() {
            let vue = this
        },
        async loadData(resource) {
            let vue = this;
            if(!resource){
                return;
            }
            vue.errors = [];
            // vue.$nextTick(() => {
            //     vue.resource = Object.assign({}, vue.resource, vue.resourceDefault)
            // })

            vue.resource = resource;

            let base = `${vue.options.base_endpoint}`;
            let url = resource
                ? `${base}/${resource.id}/edit`
                : `${base}/create`;

            url = url + "?model_type=" + vue.model_type +
                "&model_id=" + resource.id;

            await vue.$http.get(url).then(({data}) => {
                let _data = data.data;
                vue.criterion_list = _data.criteria;
                let resource= {
                    criterion_list:{}
                }
                console.log(resource,'resource');
                for (const criterion of _data.criteria) {
                    console.log(resource,'resource');
                    let criterion_default_value = criterion.multiple ? [] : null;
                    console.log(resource,'resource');
                    resource.criterion_list[criterion.code] = criterion_default_value;
                }
                // _data.criteria.forEach(criterion => {
                    
                //     // Object.assign(vue.resource.criterion_list, {[`${criterion.code}`]: criterion_default_value})
                // })
                vue.resource.criterion_list = resource.criterion_list;
                console.log(resource,vue.resource,'vue.resource');
                vue.segments = _data.segments.filter(segment => segment.type.code === 'direct-segmentation');
                vue.segment_by_document = _data.segments.find(segment => segment.type.code === 'segmentation-by-document');
                vue.hideLoader();
                console.log(vue.segment_by_document);
                // if (vue.segments.length === 0) this.addSegmentation();
                // if (vue.segment_by_document === undefined) {
                //     vue.segment_by_document = {
                //         criteria_selected: []
                //     };
                // }
                // vue.criteria = _data.criteria;
                // vue.courseModules = _data.courseModules;
                // vue.total = _data.users_count;
                // Replace modules with course's school modules
            });
            await this.loadModulesFromCourseSchools(resource.id);
            return 0;
        },
        async loadModulesFromCourseSchools(courseId) {
            if (!this.resource) return
            let url = `${this.options.base_endpoint}/modules/course/${courseId}`;
            try {
                const response = await this.$http.get(url);
                if (response.data.data) {
                    this.modulesIds = response.data.data.modulesIds
                    this.modulesSchools = response.data.data.modulesSchools
                }
            } catch (ex) {
                console.log(ex)
            }
        },
        async loadSelects() {
            let vue = this;
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
                    const users = (data.data.users) ? data.data.users :  data.data;

                    users.forEach((user) => {
                        const exist = vue.segment_by_document.segmentation_by_document.filter(el => el.document == user.document);

                        if (exist.length === 0){
                            vue.addUser(user);
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
        selectAll() {
            let vue = this
            if(vue.filter_result.length > 0) {
                vue.filter_result.forEach((element, index) => {
                    this.addUserAll(element)
                });
                if(vue.arrayCriteriaSelected.length >= vue.filter_result.length)
                    vue.filter_result = []
            }
            else {
                if(vue.arrayCriteriaSelected.length > 0) {
                    vue.arrayCriteriaSelected.forEach((element, index) => {
                        vue.filter_result.push(element)
                    });
                    this.deleteUserAll()
                }
            }
        },
        checkIfExistUser(currentdoc) {
            currentdoc = currentdoc.trim();

            const vue = this;
            if(vue.segment_by_document !== undefined && vue.segment_by_document !== null) {
                const UserDocuments = vue.segment_by_document.segmentation_by_document.map( ({document: doc}) => doc );
                return { docState : UserDocuments.includes(currentdoc),
                        docData: UserDocuments };
            }
            return [];
        },
        addUser(user) {
            let vue = this;

            const already_added = vue.segment_by_document.segmentation_by_document.filter(el => el.document == user.document).length > 0;
            if (!already_added) {
                vue.segment_by_document.criteria_selected.push(user);
                vue.segment_by_document.segmentation_by_document.push(user)
                vue.addOrRemoveFromFilterResult(user, 'remove');
            }
        },
        addOrRemoveFromFilterResult(user, action = 'add') {
            let vue = this;
            const index = vue.filter_result.findIndex(el => el.document == user.document);
            if (index !== -1) {
                if (action === 'remove'){
                    vue.filter_result.splice(index, 1);
                }
            }
        },
        deleteUser(user) {
            let vue = this;
            const index = vue.segment_by_document.criteria_selected.findIndex(el => el.document == user.document);
            if (index !== -1) {
                vue.segment_by_document.criteria_selected.splice(index, 1);
            }
            const index_segmentation_by_document = vue.segment_by_document.segmentation_by_document.findIndex(el => el.document == user.document);
            if (index_segmentation_by_document !== -1) {
                vue.segment_by_document.segmentation_by_document.splice(index, 1);
            }
            vue.filter_result.push(user)
        },
        showSectionCriteria(){
            let vue = this;
            vue.show_section_criteria = true;
        }
    }
}
</script>
<style lang="scss">
.modal_gestor_colab {
    .lbl_lists span {
        font-family: "Nunito", sans-serif;
        font-size: 10px;
        color: #A9B2B9;
    }
    .box_resultados, .box_seleccionados {
        height: 230px;
        border: 1px solid #d9d9d9;
        overflow-y: auto;
    }
    span.lbl_tit_center {
        font-family: "Nunito", sans-serif;
        color: #5458EA;
        font-weight: bold;
        font-size: 16px;
        position: relative;
        padding-bottom: 4px;
        &:after {
            content: '';
            position: absolute;
            border-bottom: 2px solid currentColor;
            width: 90%;
            bottom: 0px;
            left: 50%;
            transform: translateX(-50%);
        }
    }
}
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
    cursor: pointer;
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
.bx_select_all .round .checkmark.selected {
    border: 1px solid #5458ea;
    background-color: #5458ea;
}

.bx_select_all .round:hover input ~ .checkmark
{
  background-color: #5458ea;
}

.bx_select_all.disabled label {
    color: #bfbfbf;
}

.bx_select_all.disabled .round .checkmark {
    border: 1px solid #bfbfbf;
}

.bx_select_all.disabled .round:hover input ~ .checkmark
{
    background-color: #fff;
}

.bx_select_all .round .checkmark:after {
    content: "";
    display: none;
    left: 50%;
    top: 50%;
    width: 7px;
    height: 7px;
    transform: translate(-50%,-50%);
    position: absolute;
    background: #fff;
    border-radius: 50%;
}

.bx_select_all .round:hover .checkmark.selected:after,
.bx_select_all .round .checkmark.selected:after {
  display: block;
}
</style>
