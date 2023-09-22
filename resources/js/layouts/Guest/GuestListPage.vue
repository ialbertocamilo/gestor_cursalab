<template>
    
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Invitados
                <v-spacer/>
                <DefaultModalButton
                    :label="'Registrar por enlace'"
                    :showIcon="false"
                    @click="openFormModal(modalOptions,null, null, 'Registrar por enlace')"/>
            </v-card-title>
        </v-card>
        <v-card flat class="elevation-0 mb-4">
                <v-row class="justify-content-start">
                    <v-col cols="4">
                        <DefaultInput
                            clearable dense
                            v-model="filters.q"
                            label="Buscar por nombre o email"
                            append-icon="mdi-magnify"
                            @onEnter="refreshDefaultTable(dataTable, filters, 1)"
                            @clickAppendIcon="refreshDefaultTable(dataTable, filters, 1)"
                        />
                    </v-col>
                    <v-col cols="4">
                        <v-form v-model="isFormValid" v-on:submit.prevent>
                            <DefaultInput
                                clearable dense
                                v-model="email_user_invitation"
                                label="Invitar por email"
                                append-icon="mdi-send"
                                :rules="rules.input_email"
                                @onEnter="send_invitation()"
                                @clickAppendIcon="send_invitation()"
                            />
                        </v-form>
                    </v-col>
                    <!-- <v-col cols="4" class="text-end">
                        <DefaultButton
                            label="Invitar"
                            @click="send_invitation()"
                            :disabled="!isFormValid"
                        />
                    </v-col> -->
                </v-row>
                <v-row v-if="limitation_admin.limitation">
                    <v-col cols="12" class="my-4 d-flex align-items-center">
                        <p class="span-users m-0 p-0" v-text="`Usuarios activos (${limitation_admin.count_users}/${limitation_admin.limitation})`"></p>
                        <div class="ml-4">
                            <DefaultModalButton
                                :label="'Mejorar plan'"
                                :showIcon="false"
                                @click="openFormModal(modalIncreaseConstraintOptions,null, null, 'Mejorar plan')"/>
                        </div>
                    </v-col>
                </v-row>
                <v-row class="select-all-wrapper">
                    <v-col cols="3">
                        <input type="checkbox"
                               v-model="allSelected">
                        <span>Seleccionar todos</span>
                    </v-col>
                    <v-col cols="5">
                        <strong>
                            Seleccionados: {{ selectedItems.length }}/{{ totalCount }}
                        </strong>
                    </v-col>
                    <v-col cols="4">
                        <div class="row">
                            <div class="col-3 d-flex justify-content-end">
                                <v-switch
                                    v-model="activateSelected"
                                    :disabled="selectedItems.length === 0"
                                    @click.native.stop
                                ></v-switch>
                            </div>
                            <div class="col-9">
                                <span>Activar seleccionados</span>
                            </div>
                        </div>
                    </v-col>

                </v-row>
                <v-row>
                    <v-col cols="12" class="guests-table-wrapper">
                        <DefaultTable
                            :ref="dataTable.ref"
                            :data-table="dataTable"
                            :show-select="true"
                            :filters="filters"
                            @onSelectRow="selectionChange($event)"
                            @statusChange="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                        />
                    </v-col>
                </v-row>
        </v-card>
        <ListGuestLinksModal
            width="50vw"
            :ref="modalOptions.ref"
            :options="modalOptions"
            @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalOptions)"
        />
        <UsuarioStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters),load_data()"
            @onCancel="statusChange"
        />
            <!-- <IncreaseConstraintModal
                width="50vw"
                :ref="modalIncreaseConstraintOptions.ref"
                :options="modalIncreaseConstraintOptions"
                :limitation_admin="limitation_admin"
                @onConfirm="closeFormModal(modalIncreaseConstraintOptions, null, null)"
                @onCancel="closeFormModal(modalIncreaseConstraintOptions)"
            /> -->
        <DefaultAlertDialog
            :ref="multipleModalOptions.ref"
            :options="multipleModalOptions"
            @onCancel="onCancelMultipleActivations"
            @onConfirm="onConfirmMultipleActivations"
            width="30vw"
        >
            <template v-slot:content>
                <div v-html="multipleModalOptions.contentText"></div>
            </template>
        </DefaultAlertDialog>

    </section>
</template>


<script>
    import ListGuestLinksModal from "./ListGuestLinksModal";
    import UsuarioStatusModal from "./UsuarioStatusModal";
    import DefaultCheckbox from "../../components/globals/DefaultCheckBox.vue";
    import DefaultAlertDialog from "../../components/globals/DefaultAlertDialog";

    export default {
        components: {
            ListGuestLinksModal,
            UsuarioStatusModal, DefaultCheckbox, DefaultAlertDialog},
        data() {
            return {
                allSelected: false,
                selectedItems: [],
                totalCount: 0,
                activateSelected: false,
                dataTable: {
                    endpoint: '/invitados/search',
                    ref: 'GuestTable',
                    headers: [
                        {text: "Nombre", value: "user_name", align: 'center', sortable: false},
                        {text: "Email", value: "email", sortable: false},
                        {text: "Invitación", value: "state_name", align: 'center', sortable: false},
                        {text: "Activo", value: "state", align: 'center', sortable: false},
                        {text: "Fecha y hora", value: "date_invitation", align: 'center'},
                    ],
                    actions: [

                    ],
                    more_actions: [
                        {
                            text: "Actividad",
                            icon: 'fas fa-file',
                            type: 'action',
                            method_name: 'activity'
                        },
                    ]
                },
                filters: {
                    q: '',
                },
                modalOptions: {
                    ref: 'ListGuestLinksModal',
                    open: false,
                    base_endpoint: '/invitados/list-guest-url',
                    confirmLabel: 'Hecho',
                    showCardActions:false,
                    title:'Registrar por enlace',
                },
                modalIncreaseConstraintOptions:{
                    ref: 'IncreaseConstraint',
                    open: false,
                    base_endpoint: '',
                    confirmLabel: 'Hecho',
                    showCardActions:false,
                    title:'Mejorar plan',
                },
                modalStatusOptions: {
                    ref: 'UsuarioStatusModal',
                    open: false,
                    base_endpoint: '/usuarios/status',
                    contentText: '¿Desea cambiar de estado a este registro?',
                    endpoint: '',
                },
                multipleModalOptions: {
                    ref: 'DefaultAlertDialog',
                    open: false,
                    endpoint: '',
                    base_endpoint: ``,
                    contentText: '¿Desea cambiar de estado a los registros seleccionados?'
                },
                rules: {
                    input_email: this.getRules(['required','email']),
                },
                email_user_invitation:'',
                isFormValid:null,
                limitation_admin:{
                    limitation:null,
                    count_users:0,
                    name_admin:'',
                    count_user_additional:null
                }
            }
        },
        watch: {
            allSelected: function(newValue) {
                // Perform a click on "select all" checkbox
                // in datatable

                let datatableCheckboxSelector = '.v-data-table-header .v-simple-checkbox';
                document.querySelector(datatableCheckboxSelector).click()
            },
            activateSelected (newValue) {
                if (newValue) {
                    this.multipleModalOptions.open = true
                }
            }
        },
        mounted(){
            this.load_data();
        },
        methods: {
            async load_data(){
                await axios.get('/guest/limitation_admin').then(({data})=>{
                    this.limitation_admin.limitation = data.data.limitation;
                    this.limitation_admin.count_users = data.data.count_users;
                    this.limitation_admin.name_admin = data.data.name_admin;
                })

                try {
                    let response = await axios.get('/invitados/search?page=1&paginate=10')
                    this.totalCount = response.data.data.total
                } catch (ex) {
                    console.log(ex)
                }
            },
            activity() {
                console.log('activity')
            },
            confirmModal() {
                // TODO: Call store or update USER
            },
            async send_invitation(){
                let vue = this;
                if(!vue.isFormValid){
                    return 0;
                }
                vue.showLoader();
                await axios.post('/invitados/send_invitation',{
                    email:vue.email_user_invitation,
                }).then(({data})=>{
                    vue.queryStatus("invitados", "enviar_correo");
                    this.showAlert(data.data.msg, 'success')
                    vue.refreshDefaultTable(vue.dataTable, vue.filters)
                    vue.email_user_invitation='';
                })
                vue.hideLoader();
            },
            statusChange(value){
                const vue = this;
                let UFC = this.$refs.GuestTable.getData();
                vue.closeFormModal(vue.modalStatusOptions);
            },
            selectionChange($rows) {
                this.selectedItems = $rows

                // Update selection flag

                if (this.selectedItems.length < this.totalCount) {
                    this.allSelected = false
                }

                if (this.selectedItems.length === this.totalCount) {
                    this.allSelected = true
                }
            },
            onCancelMultipleActivations() {
                this.multipleModalOptions.open = false
                this.activateSelected = false
            },
            async onConfirmMultipleActivations() {

                let usersIds = this.selectedItems.map(i => i.user_id)

                try {
                    let response = await axios({
                        method: 'post',
                        url: '../guest/users_activation',
                        data: {
                            usersIds
                        }
                    })

                    if (response.data.success) {
                        this.$refs[this.dataTable.ref].getData();
                    }
                    this.multipleModalOptions.open = false

                } catch (ex) {
                    console.log(ex);
                }
            }
        }

    }
</script>
<style lang="scss">

    .select-all-wrapper {
        background: white;
        height: 45px;
        margin: 25px 0 15px;
        border-radius: 4px;

        span, strong {
            color: #796AEE
        }
    }

    .guests-table-wrapper {
        // checkboxes style
        .v-data-table__checkbox i {
            font-size: 18px !important;
        }

        // hide checkbox in header
        .v-data-table-header .v-simple-checkbox {
            opacity: 0;
        }
    }

    .span-users{
        font-style: normal;
        font-weight: 400;
        font-size: 18px;
        line-height: 22px;
        color: #333D5D;
    }
    .v-btn__content {
        text-transform: initial !important;
        color: white !important;
    }
    .v-data-table{
        margin: 0 !important;
    }
    .v-data-table__wrapper{
        background: #FFFFFF !important;
        box-shadow: 0px 4px 4px rgba(165, 166, 246, 0.25) !important;
        border-radius: 6px !important;
        padding: 19px 23px;
    }
    .v-data-table-header{
        background: #F9FAFB !important;
    }
    .v-data-table-header > tr > th{
        color: #333D5D;
        font-weight: 400;
    }
    .v-input__control{
        box-shadow: none !important;
    }
    tr:hover .v-input__slot{
        background: #eeeeee !important;
    }
    .v-input__control{
        box-shadow: 0px 4px 4px rgba(165, 166, 246, 0.25);
    }
    .v-input__slot{
        background: white !important;
    }
    .v-text-field .v-input__control, .v-text-field .v-input__slot, .v-text-field fieldset{
        border-radius:6px !important;
        border: none !important;
    }
    .v-text-field__slot > label{
        color: rgba(51, 61, 93, 0.5) !important;
    }
    .section{
        margin: 0 !important;
    }
</style>
