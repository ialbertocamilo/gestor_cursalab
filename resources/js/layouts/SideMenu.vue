<template>
    <div class="">
        <v-card flat tile >
            <v-card-title @click="workspacesListIsVisible = !workspacesListIsVisible"
                class="title-logo-wrapper bg-white d-flex justify-content-center rounded-0">
                <img v-if="userSession.session && logoIsLoaded" :src="userSession.session.workspace.logo"
                    :alt="userSession.session.workspace.name" @error="logoIsLoaded = false" width="140" />
                <!-- Text to be shown when logo fails to load -->
    
                <div v-if="!logoIsLoaded" style="width: 75%;">
                    {{ userSession.session.workspace.name }}
                </div>
    
                <v-icon v-if="workspaces.length > 1" class="caret">
                    {{
                        workspacesListIsVisible
                        ? "mdi-chevron-up"
                        : "mdi-chevron-down"
                    }}
                </v-icon>
    
                <!-- <v-app-bar-nav-icon color="white"></v-app-bar-nav-icon> -->
            </v-card-title>
            <div :class="{ visible: workspacesListIsVisible }" v-if="workspaces.length > 1" class="workspaces-list-wrapper">
                <v-list shaped class="bg-white workspaces-list">
                    <v-list-item-group color="primary" active-class="bg-white" class="bg-white">
                        <v-list-item v-for="(workspace, i) in workspaces" @click="setActiveWorkspace(workspace.id)" :key="i">
                            <v-list-item-content>
                                <v-list-item-title>{{ workspace.name }}</v-list-item-title>
                            </v-list-item-content>
                        </v-list-item>
                    </v-list-item-group>
                </v-list>
            </div>
            <v-list class="mx-auto" dark nav rounded expand tile>
                <div v-for="(grupo, i) in grupos" :key="i">
                    <v-list-item 
                        v-if="grupo.items.length == 0"
                        @mouseleave="cancelTimer"
                        @mouseover="startTimer(grupo,$event)"
                        dark 
                        sense 
                        style="cursor: pointer;"
                    >
                        <v-list-item-icon>
                            <v-icon small class="item_icon">{{ grupo.icon }}</v-icon>
                        </v-list-item-icon>
                        <v-badge small class="_badge" overlap>
                            <template v-slot:badge>
                                <div v-if="grupo.show_upgrade" class="ml-1 tag_beta_upgrade d-flex align-items-center"><img
                                        src="/img/premiun.svg"> Upgrade</div>
                                <div v-if="grupo.is_beta" class="ml-1 tag_beta_upgrade d-flex align-items-center">Beta</div>
                            </template>
                            <v-list-item-title class="grupo_title">{{ grupo.title }}</v-list-item-title>
                        </v-badge>
    
                    </v-list-item>
                    <v-list-group v-else dense :value="false" color="white" 
                        v-model="grupo.active" 
                        @mouseover="startTimer(grupo,$event)"
                        @mouseleave="cancelTimer">
                        <template v-slot:activator >
                            <v-list-item-icon v-if="!grupo.show_upgrade && !grupo.is_beta">
                                <v-icon small>{{ grupo.icon }}</v-icon>
                            </v-list-item-icon>
                            <v-list-item-title class="grupo_title" v-if="grupo.show_upgrade || grupo.is_beta" >
                                <v-list-item-icon>
                                    <v-icon small>{{ grupo.icon }}</v-icon>
                                </v-list-item-icon>
                                <v-badge small class="_badge" overlap>
                                    <template v-slot:badge>
                                        <div v-if="grupo.show_upgrade && !item.is_beta" class="ml-1 tag_beta_upgrade d-flex align-items-center">
                                            <img src="/img/premiun.svg"> Upgrade</div>
                                        <div v-if="grupo.is_beta" class="ml-1 tag_beta_upgrade d-flex align-items-center">Beta
                                        </div>
                                    </template>
                                    {{ grupo.title }}
                                </v-badge>
                            </v-list-item-title>
                            <v-list-item-title class="grupo_title" v-else>
                                {{ grupo.title }}
                            </v-list-item-title>
                        </template>
                        <div v-for="(item, i) in grupo.items" :key="i" class="list_submenu">
                            <v-list-item dark dense :href="`${item.path}`" @click="()=>{}" v-model="item.selected">
                                <v-list-item-icon>
                                    <v-icon small class="item_icon">{{ item.icon }}</v-icon>
                                </v-list-item-icon>
                                <v-badge small class="_badge" overlap> 
                                    <template v-slot:badge>
                                        <div  @click="openFormModal(ModalUpgradeOptions)" v-if="item.show_upgrade && !item.is_beta" class="tag_beta_upgrade d-flex align-items-center"><img
                                                src="/img/premiun.svg"> Upgrade</div>
                                        <div v-if="item.is_beta" class="tag_beta_upgrade d-flex align-items-center">Beta</div>
                                    </template>
                                    <v-list-item-title class="item_title">{{ item.title }}</v-list-item-title>
                                </v-badge>
                            </v-list-item>
                        </div>
                    </v-list-group>
                </div>
            </v-list>
        </v-card>
        <transition name="fade">
            <v-card
                v-if="cardHover.showCard"
                :width="cardHover.width"
                flat tile
                class="card-popup"
                :style="{ top: cardHover.cardTop,left: cardHover.cardLeft }"
                @mouseenter="cancelHideTimer"
                @mouseleave="startHideTimer"
            >
                <v-card-title class="d-flex justify-content-center py-2">
                    <div class="font-weight-bold" style="font-style: 'Nunito', sans-serif;color:#5457E7" v-text="cardHover.title"></div>
                </v-card-title>
                <v-card-text class="" style="font-style: 'Nunito', sans-serif"  v-html="cardHover.description">
                </v-card-text>
                <v-card-actions class="text-center d-flex justify-content-center" v-if="cardHover.show_upgrade">
                    <v-btn 
                        color="primary"  
                        @click="openFormModal(ModalUpgradeOptions)"
                        style="font-style: 'Nunito', sans-serif;background:#5457E7"
                    >
                        <img src="/img/premiun.svg"> Accede a más funciones
                    </v-btn>
                </v-card-actions>
            </v-card>
        </transition>
        <ModalUpgrade
            :options="ModalUpgradeOptions"
            width="55vw"
            :model_id="null"
            :ref="ModalUpgradeOptions.ref"
            @onCancel="closeSimpleModal(ModalUpgradeOptions)"
            @onConfirm="closeFormModal(ModalUpgradeOptions),openFormModal(modalGeneralStorageEmailSendOptions, null, 'status', 'Solicitud enviada')"
        />
        <GeneralStorageEmailSendModal
            :ref="modalGeneralStorageEmailSendOptions.ref"
            :options="modalGeneralStorageEmailSendOptions"
            width="35vw"
            @onCancel="closeFormModal(modalGeneralStorageEmailSendOptions)"
            @onConfirm="closeFormModal(modalGeneralStorageEmailSendOptions)"
        />
        
    </div>
</template>

<script>
import ModalUpgrade from './ModalUpgrade';
import GeneralStorageEmailSendModal from './General/GeneralStorageEmailSendModal.vue';

const img_rocket = '<img width="20px" class="mx-1" src="/img/rocket.svg">';
const SUB_ITEM_GLOSARY = {
    title: "Glosario",
    icon: "fas fa-book",
    path: "/glosario",
    subpaths: ["glosario"],
    selected: false,
    permission: "glosario",
    role: ["super-user", "admin", "content-manager", "trainer"]
};

// const SUB_ITEM_VADEMECUM = {
//     title: "Protocolos y Documentos",
//     icon: "fas fa-file-invoice",
//     path: "/protocolos-y-documentos",
//     subpaths: ["protocolos-y-documentos"],
//     selected: false,
//     permission: "vademecum",
//     role: ["super-user", "admin", "content-manager", "trainer-TEST"]
// };

export default {
    components:{ModalUpgrade,GeneralStorageEmailSendModal},
    data: () => ({
        logoIsLoaded: true,
        workspacesListIsVisible: false,
        workspaces: [],
        userSession: {},
        collapseOnScroll: true,
        grupos:[],
        cardHover:{
            showCard: false,
            timer: null,
            title: 'Beneficios',
            description: ' Listado de beneficios',
            show_upgrade:null,
            // cardTop:0,
            // cardLeft:0,
            width:'22vw'
        },
        ModalUpgradeOptions:{
            ref: 'ModalUpgradeModal',
            open: false,
            base_endpoint: '/upgrade',
            confirmLabel: 'Solicítalo hoy',
            resource: 'Upgrade',
            width:'70vw',
            title_modal: `${img_rocket}Accede a más soluciones${img_rocket}`,
            action: null
        },
        modalGeneralStorageEmailSendOptions: {
            ref: 'GeneralStorageEmailSendModal',
            open: false,
            showCloseIcon: true,
            hideCancelBtn: true,
            confirmLabel:'Entendido',
            persistent: false
        },
    }),
    props: {
        roles: {
            type: Array,
            required: true
        },
        show_meeting_section: {
            type: String,
            required: true
        },
        functionality: {
            type: Array,
            required: true
        }

    },
    // computed: {
    //     // gruposFiltrado: function() {
    //     //     let vue = this;
    //     //     let new_grupos = [];
    //     //     let new_grupos_sections = [];
    //     //     let location = window.location.pathname.split("/");
    //     //     this.grupos.forEach(grupo => {
    //     //         let new_items = [];
    //     //         grupo.items.forEach(i => {
    //     //             vue.roles.forEach(item => {
    //     //                 if (i.role.includes(item)) {
    //     //                     new_items.push(i);
    //     //                     if (this.verify_path(location, i.subpaths)) {
    //     //                         grupo.active = true;
    //     //                         i.selected = true;
    //     //                     }
    //     //                 }
    //     //             });
    //     //         });
    //     //         if (new_items.length > 0) {
    //     //             grupo.items = new_items;
    //     //             new_grupos.push(grupo);
    //     //         }
    //     //     });
    //     //     new_grupos.forEach(sec => {
    //     //         if (!sec.hasOwnProperty('functionality')) {
    //     //             new_grupos_sections.push(sec)
    //     //         }
    //     //         else {
    //     //             vue.functionality.forEach(f => {
    //     //                 if (sec.functionality.includes(f)) {
    //     //                     new_grupos_sections.push(sec)
    //     //                 }
    //     //             });
    //     //         }
    //     //     });
    //     //     // console.log(new_grupos);
    //     //     return new_grupos_sections;
    //     // }
    // },
    mounted() {
        this.loadData();
        this.loadSession();
    },
    methods: {
        verify_path(location, subpaths) {
            return subpaths.find(e => {
                let lt =
                    e.split("/").length > 1 && location.length > 1
                        ? location[1] + "/" + location[2]
                        : location[1];
                // console.log(lt,e);
                return lt.includes(e);
            });
        },
        /**
         * Load data from server
         */
        loadData() {

            let vue = this;

            // Load workspaces

            let url = `/workspaces/search`;
            this.$http.get(url).then(({ data }) => {
                vue.workspaces = data.data.workspaces.data;
            });
        },

        /**
         * Add new Item by workspace id
         * */
        availableItemGroup(in_title, item) {
            let vue = this;
            const index = vue.grupos.findIndex(({ title }) => title === in_title);
            if(index){
                vue.grupos[index].items.push(item);
            }
        },
        /**
         * Load session data from server
         */
        loadSession() {
            let vue = this;

            // Load session data

            let url = `/usuarios/session`;
            this.$http.get(url).then(({ data }) => {
                vue.userSession = data;
                vue.grupos = data.user.menus;
                //=== only for "Farmacias Peruanas"
                const { session: { workspace } } = data;
                if (workspace.id === 25) {
                      vue.availableItemGroup('GESTIONA TU CONTENIDO', SUB_ITEM_GLOSARY);
                }
                vue.setActiveSubmenu();
                // vue.availableItemGroup('GESTIONA TU CONTENIDO', SUB_ITEM_VADEMECUM);
                // === only for "Farmacias Peruanas"

            });
        },
        setActiveSubmenu() {
            let vue = this;
            const location = window.location.pathname.split("/");
            vue.grupos.forEach(grupo => {
                grupo.items.forEach(i => {
                    if (this.verify_path(location, i.subpaths)) {
                        grupo.active = true;
                        i.selected = true;
                    }
                });
            });
        },
        /**
         * Update workspace in User's session
         *
         * @param workspaceId
         */
        setActiveWorkspace(workspaceId) {
            let vue = this;

            // Submit request to update workspace in session

            let formData = vue.getMultipartFormData("PUT");
            let url = `/usuarios/session/workspace/${workspaceId}`;
            this.$http.post(url, formData).then(() => {
                vue.workspacesListIsVisible = false;
                vue.loadSession();

                // Redirect to welcome page

                window.location.href = "/home";
            });
        },
        startTimer({title,description,show_upgrade}, event) {
            let vue = this;
            if (vue.cardHover.timer) {
                clearTimeout(vue.cardHover.timer);
                vue.cardHover.timer = null;
            }
            const viewportWidth = window.innerWidth;
            const leftPostion =((parseInt(vue.cardHover.width.replace("vw", "")) * viewportWidth) / 100 ) +225 //225px es la medida del nav-container (custom.css);
            const topPosition = event.currentTarget.getBoundingClientRect().top;
            vue.cardHover.cardTop = `${topPosition}px`;
            vue.cardHover.cardLeft = `${leftPostion}px`;
            vue.cardHover.timer = setTimeout(() => {
                vue.cardHover.title = title;
                vue.cardHover.description = description;
                vue.cardHover.show_upgrade = show_upgrade;
                vue.cardHover.showCard = true;
                vue.startHideTimer();
                // setTimeout(() => {
                // this.cardHover.showCard = false;
                // }, 4000); // Ocultar la tarjeta después de 4 segundos
            }, 1000);
        },
        cancelTimer() {
            if (this.cardHover.timer) {
                clearTimeout(this.cardHover.timer);
                this.cardHover.timer = null;
            }
            this.startHideTimer();
        },
        startHideTimer() {
            this.cardHover.timer = setTimeout(() => {
                this.cardHover.showCard = false;
            }, 2500); 
        },
        cancelHideTimer() {
            if (this.cardHover.timer) {
                clearTimeout(this.cardHover.timer);
                this.cardHover.timer = null;
            }
        }
    }
};
</script>

<style scoped>
.title-logo-wrapper {
    /* height: 130px; */
    box-shadow: 0px 4px 4px rgba(165, 166, 246, 0.25);
    cursor: pointer;

    line-height: 22px;
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
    hyphens: auto;
}

.title-logo-wrapper .caret {
    color: #5458ea;
    margin-left: 5px;
}

.workspaces-list-wrapper {
    background: white;
    box-shadow: inset 8px 8px 5px rgba(165, 166, 246, 0.15);
    opacity: 0;
    transition: all 1000ms;
    overflow: hidden;
}

.workspaces-list-wrapper .v-list {
    display: none;
}

.workspaces-list-wrapper.visible {
    padding-top: 10px;
    opacity: 1;
}

.workspaces-list-wrapper.visible .v-list {
    display: block;
}

.v-list {
    background: #5458ea;
}

.v-list-group--active .grupo_title,
.v-list-group--active .v-list-item__icon {
    color: #fff;
}

.list_submenu {
    margin-left: 20px;
}

.list_submenu .v-list-item__title.item_title {
    white-space: normal;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 400;
    letter-spacing: 0.1px;
    color: #E5E6FC;
    /* width: min-content; */
}

.list_submenu .v-list-item {
    margin-top: 3px;
    margin-bottom: 3px;
}

.v-list-item:hover {
    background: rgba(255, 255, 255, 0.2);
}

.v-application--is-ltr .v-list-item__icon:first-child {
    margin-right: 8px;
}

.v-list-item {
    text-decoration: none;
}

/* Custom class */
.grupo_title {
    font-size: 0.73em !important;
    font-weight: bold !important;
    white-space: normal;
}
._badge {
    max-width: 200px;
    white-space: normal;
}
.item_icon {
    color: rgba(255, 255, 255, 0.85);
}

.item_title {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.93em;
}

.v-list-group .beta,
.list_submenu .beta {
    padding: 2px 10px 2px 10px;
    border-radius: 8px;
    background: #FFF;
    color: #5458EA;
    font-size: 13px;
    font-style: normal;
    font-weight: 400;
}

/* Ocultar el icono de flecha cuando el grupo está vacío */
.v-list-group__header--dense .v-list-group__header__append-icon {
    display: none;
}

.tag_beta_upgrade {
    padding: 2px 6px 2px 6px;
    border-radius: 8px;
    background: #FFF;
    color: #5458EA;
    font-size: 11px;
    font-style: normal;
    font-weight: 400;
    margin-left: 8px;
    margin-top: -8px;
}
/* POPUP */
.card-popup {
    position: absolute;
    left: 530px;
    /* top: 15%; */
    transform: translateX(-100%);
    transition: transform 0.3s ease-in-out;
    z-index: 1;
    border: 1px solid #5457E7; 
    box-shadow: 0px 4px 16px 8px rgba(0, 0, 0, 0.25) !important; 
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease-in-out;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
</style>
