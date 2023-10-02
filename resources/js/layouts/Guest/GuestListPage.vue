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
        <DefaultFilter v-model="open_advanced_filter"
            @filter="advanced_filter(dataTable, filters, 1)"
        >
            <template v-slot:content>
                <v-row justify="center">
                </v-row>
            </template>
        </DefaultFilter>
        <v-card flat class="elevation-0 mb-4">
            <v-card-text>
                <v-row justify="start">
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
                        <v-form v-on:submit.prevent>
                            <DefaultInput
                                clearable dense
                                v-model="email_user_invitation"
                                label="Invitar por email"
                                append-icon="mdi-send"
                                @onEnter="send_invitation()"
                                @clickAppendIcon="send_invitation()"
                            />
                        </v-form>
                    </v-col>
                    <v-col cols="4" class="d-flex justify-end">
                        <DefaultButton
                            text
                            label="Aplicar filtros"
                            icon="mdi-filter"
                            @click="open_advanced_filter = !open_advanced_filter"
                            class="btn_filter"
                        />
                    </v-col>
                </v-row>
                <v-row v-if="limit_workspace.limit_allowed_users">
                    <v-col cols="12" class="mr-8 d-flex align-items-center justify-end">
                        <p class="m-0 mr-4" v-text="`Usuarios activos (${limit_workspace.active_users_count}/${limit_workspace.limit_allowed_users})`"></p>
                        <div class="ml-4" v-if="limit_workspace.active_users_count>=limit_workspace.limit_allowed_users">
                            <DefaultModalButton
                                :label="'Mejorar plan'"
                                icon_name=""
                                @click="openFormModal(modalAlertStorageOptions, null, null, 'Alerta de almacenamiento')"
                            />
                        </div>
                    </v-col>
                </v-row>
            </v-card-text>
            <DefaultTable
                :ref="dataTable.ref"
                :data-table="dataTable"
                :show-select="true"
                :filters="filters"
                @onSelectRow="selectionChange($event)"
                @statusChange="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
            />
        </v-card>
        <!-- <v-card flat class="elevation-0 mb-4">
                <v-row class="justify-content-start">
                    <v-col cols="4">
                        
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
                       
                    </v-col>
                </v-row>
        </v-card> -->
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

        <!-- MODAL ALMACENAMIENTO -->
        <GeneralStorageModal
            :ref="modalGeneralStorageOptions.ref"
            :options="modalGeneralStorageOptions"
            width="45vw"
            @onCancel="closeFormModal(modalGeneralStorageOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageOptions), 
                        openFormModal(modalGeneralStorageEmailSendOptions, null, 'status', 'Solicitud enviada')"
        />
        <!-- MODAL ALMACENAMIENTO -->

        <!-- MODAL EMAIL ENVIADO -->
        <GeneralStorageEmailSendModal
            :ref="modalGeneralStorageEmailSendOptions.ref"
            :options="modalGeneralStorageEmailSendOptions"
            width="35vw"
            @onCancel="closeFormModal(modalGeneralStorageEmailSendOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageEmailSendOptions)"
        />
        <!-- MODAL EMAIL ENVIADO -->

        <!-- === MODAL ALERT STORAGE === -->
        <DefaultStorageAlertModal
            :ref="modalAlertStorageOptions.ref"
            :options="modalAlertStorageOptions"
            width="25vw"
            @onCancel="closeFormModal(modalAlertStorageOptions)"
            @onConfirm="openFormModal(modalGeneralStorageOptions, null, 'status', 'Aumentar mi plan'), 
                        closeFormModal(modalAlertStorageOptions)"
        />
        <!-- === MODAL ALERT STORAGE === -->
    </section>
</template>


<script>
    import ListGuestLinksModal from "./ListGuestLinksModal";
    import UsuarioStatusModal from "./UsuarioStatusModal";
    import DefaultCheckbox from "../../components/globals/DefaultCheckBox.vue";
    import DefaultAlertDialog from "../../components/globals/DefaultAlertDialog";

    // components to request increase limits storage
    import DefaultStorageAlertModal from '../Default/DefaultStorageAlertModal.vue';
    import GeneralStorageModal from '../General/GeneralStorageModal.vue';
    import GeneralStorageEmailSendModal from '../General/GeneralStorageEmailSendModal.vue';
    
    export default {
        components: {
            ListGuestLinksModal,
            UsuarioStatusModal, DefaultCheckbox, DefaultAlertDialog,
            DefaultStorageAlertModal,
            GeneralStorageModal,
            GeneralStorageEmailSendModal
        },
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
                        {text: "Estado invitación", value: "state_name", align: 'center', sortable: false},
                        {text: "Estado", value: "state", align: 'center', sortable: false},
                        {text: "Fecha y hora", value: "date_invitation", align: 'center'},
                        {text: "Fecha y hora", value: "date_invitation", align: 'center'},
                        {text: "Opciones", value: "actions", align: 'center', sortable: false},
                    ],
                    actions: [
                        {
                            text: "Editar",
                            icon: "mdi mdi-pencil",
                            type: "action",
                            method_name: "edit"
                        },
                        {
                            text: "Actualizar estado",
                            icon: 'fa fa-circle',
                            type: 'action',
                            method_name: 'status'
                        },
                        {
                            text: "Eliminar",
                            icon: 'far fa-trash-alt',
                            type: 'action',
                            method_name: 'delete'
                        },
                    ],
                    more_actions: [
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
                email_user_invitation:'',
                limit_workspace:{
                    limit_allowed_users:null,
                    active_users_count:0,
                },
                modalAlertStorageOptions: {
                    ref: 'AlertStorageModal',
                    open: false,
                    showCloseIcon: true,
                    base_endpoint: '/general',
                    confirmLabel:'Solicitar',
                    persistent: true,
                },
                modalGeneralStorageOptions: {
                    ref: 'GeneralStorageModal',
                    open: false,
                    showCloseIcon: true,
                    base_endpoint: '/general',
                    confirmLabel:'Enviar',
                    persistent: true
                },
                modalGeneralStorageEmailSendOptions: {
                    ref: 'GeneralStorageEmailSendModal',
                    open: false,
                    showCloseIcon: true,
                    hideCancelBtn: true,
                    confirmLabel:'Entendido',
                    persistent: false
                },
                open_advanced_filter:false
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
                let vue = this;
                await axios.get('/invitados/limits-workspace').then(({data})=>{
                    vue.limit_workspace.limit_allowed_users = data.data.limit_allowed_users;
                    vue.limit_workspace.active_users_count = data.data.active_users_count;
                })

                try {
                    let response = await axios.get('/invitados/search?page=1&paginate=10')
                    vue.totalCount = response.data.data.total
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
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
                if(!vue.email_user_invitation || !regex.test(vue.email_user_invitation)){
                    this.showAlert('El formato de correo electrónico es inválido', 'warning')
                    return 0;
                }
                vue.showLoader();
                await axios.post('/invitados/send-invitation',{
                    email:vue.email_user_invitation,
                }).then(({data})=>{
                    vue.queryStatus("invitados", "enviar_correo");
                    this.showAlert(data.data.msg, 'success')
                    vue.refreshDefaultTable(vue.dataTable, vue.filters)
                    vue.email_user_invitation='';
                    vue.hideLoader();

                }).catch((e)=>{
                    if(e.response.data.message){
                        this.showAlert(e.response.data.message, 'warning')
                    }else{
                        this.showAlert('No se pudo enviar la invitación.', 'warning')
                    }
                    vue.hideLoader();
                })
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
