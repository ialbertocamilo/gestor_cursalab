

<template>
    <v-dialog
        class="default-dialog"
        v-model="value"
        :width="width"
        scrollable
        persistent
        @click:outside="closemodalGestorColaboradores"
        :class="{}"
        content-class="br-dialog"
    >
        <v-card class="modal_gestor_colab">
            <v-card-title class="default-dialog-title">
                Gestión de colaboradores - {{ benefit_name }}
                <v-spacer/>
                <v-btn icon :ripple="false" color="white"
                       @click="closemodalGestorColaboradores">
                    <v-icon>mdi-close</v-icon>
                </v-btn>
            </v-card-title>
            <v-card-text class="pt-0 pb-0">
                <v-row>
                    <v-col cols="12" md="12" lg="12" class="pb-0">
                        <span class="text_default lbl_tit">Otorga beneficio a los colaboradores o retíralos de manera extraordinaria, sin la restricción de cupos ni fecha de matrícula.</span>
                        <span class="text_default lbl_tit fw-bold"><i class="fas fa-exclamation-triangle" style="color: #FF9800;"></i> Una vez confirmados, no se podrán retirar del beneficio.</span>
                    </v-col>
                    <v-col cols="12" class="pb-0 pt-0">
                        <v-row justify="space-around">
                            <v-col cols="12" >
                                <v-row>
                                    <v-col cols="5" class="d-flex flex-row bx_search_by_document">
                                        <DefaultInput
                                            clearable dense
                                            v-model="search"
                                            placeholder="Nombre o doc. de identidad"
                                            append-icon="mdi-magnify"
                                            :loading="autocomplete_loading"
                                            class="col-12"
                                        />
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="6">
                                        <div class="d-flex justify-content-between mx-2">
                                            <div class="lbl_lists"><span>Colaboradores segmentados</span></div>
                                            <div class="lbl_lists"><span>Agregar</span></div>
                                        </div>
                                        <div class="box_resultados">
                                            <div class="bx_message" v-if="list_filter_segmentados == null">
                                                <span class="text_default">Colaboradores segmentados</span>
                                            </div>
                                            <ul v-else>
                                                <li v-for="user in list_filter_segmentados" :key="user.id" class="d-flex align-center justify-content-between">
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
                                            <div class="d-flex justify-content-end">
                                                <div class="lbl_lists mr-2"><span>Confirmar</span></div>
                                                <div class="lbl_lists"><span>Retirar</span></div>
                                            </div>
                                        </div>
                                        <div class="box_seleccionados">
                                            <div class="bx_message" v-if="list_filter_users == null">
                                                <span class="text_default">Colaboradores registrados</span>
                                            </div>
                                            <ul v-else>
                                                <li v-for="user in list_filter_users" :key="user.id" class="d-flex align-center justify-content-between">
                                                    <span class="text_default" style="max-width: calc(100% - 80px);">{{ user.document }} - {{ user.fullname }}</span>
                                                    <div style="width: 70px;">
                                                        <v-btn small icon :ripple="false" class="p-0" @click="user.status != null && user.status.code == 'approved' ? null : changeStatusUser(user)">
                                                            <v-icon class="icon_size" small :color="user.status != null && user.status.code == 'approved' ? '#9bc99d' : '#4CAF50'"
                                                                    style="font-size: 1.25rem !important;"
                                                                    v-if="(user.status != null && user.status.code == 'approved') || (user.ev_user_status != null && user.ev_user_status == 'approved')">
                                                                    mdi-checkbox-marked-circle
                                                            </v-icon>
                                                            <v-icon class="icon_size" small color="#434D56"
                                                                    style="font-size: 1.25rem !important;"
                                                                    v-else>
                                                                mdi-checkbox-blank-circle-outline
                                                            </v-icon>
                                                        </v-btn>

                                                        <v-btn small icon :ripple="false" @click="user.status != null && user.status.code == 'approved' ? null : deleteUser(user)" class="p-0">
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
                </v-row>

            </v-card-text>

            <v-card-actions style="border-top: 1px solid rgba(0,0,0,.12)" class="actions_btn_modal">
                <DefaultModalActionButton
                    @cancel="closemodalGestorColaboradores"
                    @confirm="confirm"
                    cancelLabel="Cancelar"
                    confirmLabel="Guardar"
                    />
            </v-card-actions>
        </v-card>
    </v-dialog>
</template>


<script>

export default {
    components: {
    },
    props: {
        data: [Object, Array],
        segmentados: [Object, Array],
        seleccionados: [Object, Array],
        value: Boolean,
        width: String,
        benefit_id: Number,
        benefit_name: String,
    },
    data() {
        return {
            isLoading: false,
            resourceDefault: {
                id: null,
                name: null,
                type_checklist: null
            },
            resource: {},
            search: null,
            autocomplete_loading: false,
            file: null,
            debounce: null,
            filter_result: [],
            usersearch: null,
            filtrados: [],
        };
    },
    computed: {
        list_filter_segmentados() {
            const vue = this;

            if (vue.search === null) {
                return vue.$props.segmentados;
            }
            return vue.$props.segmentados.filter((user) => {
                if(user.fullname != '' && user.fullname != null && user.document != '' && user.document != null)
                return (
                    user.fullname.toLowerCase().includes(vue.search.toLowerCase()) ||
                    user.document.toLowerCase().includes(vue.search.toLowerCase())
                );
            });
        },
        list_filter_users() {
            const vue = this;

            if (vue.search === null) {
                return vue.$props.seleccionados;
            }
            return vue.$props.seleccionados.filter((user) => {
                if(user.fullname != '' && user.fullname != null && user.document != '' && user.document != null)
                return (
                    user.fullname.toLowerCase().includes(vue.search.toLowerCase()) ||
                    user.document.toLowerCase().includes(vue.search.toLowerCase())
                );
            });
        }
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
    },
    async mounted() {
        // this.addActividad()
    },
    methods: {
        validateRequired(input) {
            return input != undefined && input != null && input != "";
        },
        closemodalGestorColaboradores() {
            let vue = this;
            vue.resetValidation()
            vue.$emit("closemodalGestorColaboradores");
        },
        resetValidation() {
            let vue = this;
        },
        confirm() {
            let vue = this;
            console.log(vue.$props.benefit_id);
            console.log(vue.$props.seleccionados);
            vue.$emit("confirmModalGestorColaboradores",vue.$props.benefit_id, vue.$props.seleccionados);
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
        // selectAll() {
        //     let vue = this
        //     if(vue.filter_result.length > 0) {
        //         vue.filter_result.forEach((element, index) => {
        //             this.addUserAll(element)
        //         });
        //         if(vue.list_filter_users.length >= vue.filter_result.length)
        //             vue.filter_result = []
        //     }
        //     else {
        //         if(vue.list_filter_users.length > 0) {
        //             vue.list_filter_users.forEach((element, index) => {
        //                 vue.filter_result.push(element)
        //             });
        //             this.deleteUserAll()
        //         }
        //     }
        // },
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
        addUserAll(user) {
            let vue = this;
            vue.$emit("addUserAll", user);
        },
        deleteUserAll() {
            let vue = this;
            vue.$emit("deleteUserAll");
        },
        addUser(user) {
            let vue = this;

            const already_added = vue.$props.seleccionados.filter(el => el.document == user.document).length > 0;

            if (!already_added) {
                user.ev_type_register = 'extraordinario'
                vue.$props.seleccionados.push(user)
            }
            const index = vue.$props.segmentados.findIndex(el => el.document == user.document);

            if (index !== -1) {
                vue.$props.segmentados.splice(index, 1);
            }
        },
        deleteUser(user) {
            let vue = this;

            const already_added = vue.$props.segmentados.filter(el => el.document == user.document).length > 0;

            if (!already_added) {
                vue.$props.segmentados.push(user)
            }

            const index = vue.$props.seleccionados.findIndex(el => el.document == user.document);

            if (index !== -1) {
                vue.$props.seleccionados.splice(index, 1);
            }
        },
        changeStatusUser(user) {
            let vue = this;
            console.log(user);
            if(user.ev_user_status == 'approved') {
                user.ev_user_status = null
            }
            else {
                user.ev_user_status = 'approved'
            }
            vue.seleccionados = [...vue.seleccionados]
            console.log(vue.seleccionados);
        },
        resetFields() {
            let vue = this;
            vue.search = null;
            vue.usersearch = null;
            vue.filter_result = [];
        }
    }
};
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
</style>
