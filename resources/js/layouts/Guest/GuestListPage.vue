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
                :filters="filters"
                @delete="openCustomDialog($event,'delete_one_user')"
                @openModalDeleteUsers="openCustomDialog($event,'delete_massive_user')"
                @openModalActiveUsers="openCustomDialog($event,'active_massive_user')"
                @openModalInactiveUsers="openCustomDialog($event,'inactive_massive_user')"
                @onSelectRow="selectionChange($event)"
                @status="openFormModal(modalStatusOptions, $event, 'status', 'Actualizar estado')"
                @edit="openFormModal(modalFormUserOptions, $event, 'edit')"
            />
        </v-card>
        <ListGuestLinksModal
            width="50vw"
            :ref="modalOptions.ref"
            :options="modalOptions"
            @onConfirm="closeFormModal(modalOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalOptions)"
        />
        <!-- <UsuarioStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters),load_data()"
            @onCancel="statusChange"
        /> -->

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
        <DialogConfirm
            v-model="customConfirmationDialog.open"
            width="408px"
            :options="customConfirmationDialog"
            @onConfirm="customConfirmationDialog.open = false,doCustomAction()"
            @onCancel="customConfirmationDialog.open = false,resetModal"
        />
        <!-- Modal to edit user -->
        <UsuarioFormModal
            width="60vw"
            :ref="modalFormUserOptions.ref"
            :options="modalFormUserOptions"
            @onConfirm="refreshDefaultTable(dataTable, filters)"
            @onCancel="closeFormModal(modalFormUserOptions)"
        />
        <DefaultStatusModal
            :options="modalStatusOptions"
            :ref="modalStatusOptions.ref"
            @onConfirm="closeFormModal(modalStatusOptions, dataTable, filters)"
            @onCancel="closeFormModal(modalStatusOptions)"
        />
    </section>
</template>


<script>
    import ListGuestLinksModal from "./ListGuestLinksModal";
    import UsuarioStatusModal from "./UsuarioStatusModal";
    import DefaultCheckbox from "../../components/globals/DefaultCheckBox.vue";
    // components to request increase limits storage
    import DefaultStorageAlertModal from '../Default/DefaultStorageAlertModal.vue';
    import GeneralStorageModal from '../General/GeneralStorageModal.vue';
    import GeneralStorageEmailSendModal from '../General/GeneralStorageEmailSendModal.vue';

    import DialogConfirm from '../../components/basicos/DialogConfirm.vue'
    import UsuarioFormModal from "../Usuario/UsuarioFormModal.vue";
    import DefaultStatusModal from "../Default/DefaultStatusModal";

    export default {
        components: {
            ListGuestLinksModal,
            UsuarioStatusModal, 
            DefaultCheckbox,
            DefaultStorageAlertModal,
            GeneralStorageModal,
            GeneralStorageEmailSendModal,
            DialogConfirm,
            UsuarioFormModal,
            DefaultStatusModal
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
                        { text: "custom-select", value: "custom-select", align: 'left', model: 'Categoria', sortable: false },
                        {text: "Nombre", value: "user_name", align: 'center', sortable: false},
                        {text: "Email", value: "email", sortable: false},
                        {text: "Estado invitación", value: "state_name", align: 'center', sortable: false},
                        {text: "Estado", value: "status", align: 'center', sortable: false},
                        {text: "Fecha y hora", value: "date_invitation", align: 'center'},
                        {text: "Opciones", value: "actions", align: 'center', sortable: false},
                    ],
                    customSelectActions:[
                        {text:'Activar',method_name:'openModalActiveUsers'},
                        {text:'Inactivar',method_name:'openModalInactiveUsers'},
                        {text:'Eliminar',method_name:'openModalDeleteUsers'},
                    ],
                    actions: [
                        {
                            text: "Editar",
                            icon: "mdi mdi-pencil",
                            type: "action",
                            show_condition:'show_edit',
                            method_name: "edit"
                        },
                        {
                            text: "Actualizar estado",
                            icon: 'fa fa-circle',
                            type: 'action',
                            show_condition:'show_status',
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
                    base_endpoint: '/invitados/user',
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
                modalUsuarioResource: {
                    ref: 'UsuarioResourceModal',
                    open: false,
                    showCloseIcon: true,
                    showTitle: false,
                    width: '30vw',
                    showCardActions: false,
                    noPaddingCardText: true
                },
                customConfirmationDialog: {
                    open: false,
                    action:'',
                    type_modal:'',
                    title_modal: '',
                    content_modal :{
                        delete:{
                            title:'',
                            details:[]
                        },
                    },
                    data:[]
                },
                open_advanced_filter:false,
                modalFormUserOptions: {
                    ref: 'UsuarioFormModal',
                    open: false,
                    base_endpoint: '/invitados/user',
                    resource: 'Usuario',
                    confirmLabel: 'Confirmar',
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
            openCustomDialog(event,type){
                let vue = this;
                console.log(event,type);
                switch (type) {
                    case 'delete_one_user':
                        vue.customConfirmationDialog.type = 'delete_user';
                        vue.customConfirmationDialog.type_modal = 'delete';
                        vue.customConfirmationDialog.title_modal = 'Eliminar';
                        vue.customConfirmationDialog.content_modal.delete.title = event.user_id ? '¡Estás por eliminar un Usuario!' :  '¡Estás por eliminar una Invitación!';
                        vue.customConfirmationDialog.content_modal.delete.details = event.user_id ? [
                            'Perderá la información de los usuarios.',
                            'La información eliminada no podra recuperarse'
                        ] :  [
                            'El usuario no podrá registrarse por la invitación enviada.',
                            'La información eliminada no podra recuperarse'
                        ];
                        vue.customConfirmationDialog.data = event;
                    break;
                    case 'delete_massive_user':
                        vue.customConfirmationDialog.type = 'delete_user';
                        vue.customConfirmationDialog.type_modal = 'delete';
                        vue.customConfirmationDialog.title_modal = 'Eliminar';
                        vue.customConfirmationDialog.content_modal.delete.title = `¡Estás por eliminar ${event.length} usuarios!`;
                        vue.customConfirmationDialog.content_modal.delete.details =  [
                            'Perderá la información de todos los usuarios seleccionados.',
                            'La información eliminada no podra recuperarse'
                        ] 
                        vue.customConfirmationDialog.data = event;
                    break
                    case 'active_massive_user':
                        vue.customConfirmationDialog.type = 'active_massive_user';
                        vue.customConfirmationDialog.type_modal = 'delete';
                        vue.customConfirmationDialog.title_modal = 'Activar';
                        vue.customConfirmationDialog.content_modal.delete.title = `¡Estás por activar ${event.filter(u => !u.active && u.user_id).length} usuario(s)!`;
                        vue.customConfirmationDialog.content_modal.delete.details =  [
                            'Podrán ingresar a la plataforma.',
                        ] 
                        vue.customConfirmationDialog.data = event.filter(u => !u.active && u.user_id);
                    break
                    case 'inactive_massive_user':
                        vue.customConfirmationDialog.type = 'inactive_massive_user';
                        vue.customConfirmationDialog.type_modal = 'delete';
                        vue.customConfirmationDialog.title_modal = 'Inactivar';
                        vue.customConfirmationDialog.content_modal.delete.title = `¡Estás por inactivar ${event.filter(u => u.active && u.user_id).length} usuario(s)!`;
                        vue.customConfirmationDialog.content_modal.delete.details =  [
                            'No podrán ingresar a la plataforma.',
                        ] 
                        vue.customConfirmationDialog.data = event.filter(u => u.active && u.user_id);
                    break
                }
                vue.openSimpleModal(vue.customConfirmationDialog);
            },
            doCustomAction(){
                let vue = this;
                switch (vue.customConfirmationDialog.type) {
                    case 'delete_user':
                        vue.callApiToDeleteGuest(vue.customConfirmationDialog.data);
                        break;
                    case 'active_massive_user':
                        vue.callApiToActiveMassive(vue.customConfirmationDialog.data);
                        break
                    case 'inactive_massive_user':
                        vue.callApiToActiveMassive(vue.customConfirmationDialog.data);
                    break
                    default:
                        break;
                }
            },
            resetModal(){
                let vue = this;
                vue.customConfirmationDialog.type = '';
                vue.customConfirmationDialog.type_modal= '';
                vue.customConfirmationDialog.title_modal = '';
                vue.customConfirmationDialog.content_modal.delete.title = '';
                vue.customConfirmationDialog.content_modal.delete.details = [];
                vue.customConfirmationDialog.data=[]
            },
            async callApiToDeleteGuest(data){
                let vue =this;
                let guests = Array.isArray(data) ? data : [data] ;
                let guest_ids = guests.map((g)=> g.id);
                if(guest_ids.length >0 ){
                    vue.showLoader();
                    axios.post('/invitados/delete',{
                        guest_ids:guest_ids
                    }).then(({data})=>{
                        vue.showAlert(data.data.message, 'success', '')
                        vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                        vue.hideLoader();
                    }).catch((err)=>{
                        vue.showAlert('No se puedo eliminar a el/los invitado(s)', 'error', '')
                        vue.hideLoader();
                    })
                }
            },
            async callApiToActiveMassive(data){
                let vue =this;
                if(data.length>0){
                    vue.showLoader();
                    await axios.post('/invitados/users_activation',{
                            users_id:data.map(g => g.user_id)
                    }).then(({data})=>{
                        vue.showAlert(data.data.message, 'success', '')
                        vue.refreshDefaultTable(vue.dataTable, vue.filters, 1)
                        vue.hideLoader();
                    }).catch((err)=>{
                        vue.showAlert('No se puedo activar a los invitado(s)', 'error', '')
                        vue.hideLoader();
                    })
                }
            }
        }

    }
</script>
