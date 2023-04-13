<template>
    <v-card flat tile :collapse="!collapseOnScroll">
        <v-card-title
            @click="workspacesListIsVisible = !workspacesListIsVisible"
            class="title-logo-wrapper bg-white d-flex justify-content-center rounded-0"
        >
            <img
                v-if="userSession.session && logoIsLoaded"
                :src="userSession.session.workspace.logo"
                :alt="userSession.session.workspace.name"
                @error="logoIsLoaded = false"
                width="140"
            />
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
        <div
            :class="{ visible: workspacesListIsVisible }"
            v-if="workspaces.length > 1"
            class="workspaces-list-wrapper"
        >
            <v-list shaped class="bg-white workspaces-list">
                <v-list-item-group
                    color="primary"
                    active-class="bg-white"
                    class="bg-white"
                >
                    <v-list-item
                        v-for="(workspace, i) in workspaces"
                        @click="setActiveWorkspace(workspace.id)"
                        :key="i"
                    >
                        <v-list-item-content>
                            <v-list-item-title
                                v-text="workspace.name"
                            ></v-list-item-title>
                        </v-list-item-content>
                    </v-list-item>
                </v-list-item-group>
            </v-list>
        </div>
        <v-list class="mx-auto" dark nav rounded expand tile>
            <v-list-group
                :value="false"
                v-for="(grupo, i) in gruposFiltrado"
                :key="i"
                color="white"
                v-model="grupo.active"
            >
                <template v-slot:activator>
                    <v-list-item-icon>
                        <v-icon small v-text="grupo.icon"></v-icon>
                    </v-list-item-icon>
                    <v-list-item-title
                        v-text="grupo.title"
                        class="grupo_title"
                    ></v-list-item-title>
                </template>
                <div
                    v-for="(item, i) in grupo.items"
                    :key="i"
                    class="list_submenu"
                    >

                    <v-list-item
                        dark
                        dense
                        :href="`${item.path}`"
                        @click()
                        v-model="item.selected"
                    >
                        <v-list-item-icon>
                            <v-icon
                                small
                                v-text="item.icon"
                                class="item_icon"
                            ></v-icon>
                        </v-list-item-icon>
                        <v-list-item-title
                            v-text="item.title"
                            class="item_title"
                        ></v-list-item-title>
                    </v-list-item>



                </div>
            </v-list-group>
        </v-list>
    </v-card>
</template>

<script>

const SUB_ITEM_GLOSARY =  { title:"Glosario",
                            icon:"fas fa-book",
                            path:"/glosario",
                            subpaths:["glosario"],
                            selected:false,
                            permission:"glosario",
                            role:[ "super-user", "admin", "content-manager", "trainer" ]
                          };

const SUB_ITEM_VADEMECUM = {
                            title:"Protocolos y Documentos",
                            icon:"fas fa-file-invoice",
                            path:"/vademecum",
                            subpaths:["vademecum"],
                            selected:false,
                            permission:"vademecum",
                            role:["super-user","admin","content-manager","trainer-TEST"]
                        };

export default {
    data: () => ({
        logoIsLoaded: true,
        workspacesListIsVisible: false,
        workspaces: [],
        userSession: {},
        collapseOnScroll: true,
        grupos: [
            {
                title: "RESUMEN",
                icon: "fas fa-dice-d6",
                active: false,
                items: [
                    {
                        title: "Dashboard",
                        icon: "fas fa-tachometer-alt",
                        path: "/home",
                        subpaths: ["home"],
                        selected: false,
                        permission: "home",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer",
                            "reports",
                        ]
                    },
                    {
                        title: "Learning Analytics",
                        icon: "fas fa-chart-line",
                        path: "/dashboard_pbi",
                        subpaths: ["dashboard_pbi"],
                        selected: false,
                        permission: "learning_analytics",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer",
                            "reports"
                        ]
                    }
                ]
            },
            {
                title: "AULAS VIRTUALES",
                icon: "fas fa-chalkboard",
                active: false,
                items: [
                    {
                        title: "Aulas Virtuales",
                        icon: "fas fa-chalkboard-teacher",
                        path: "/aulas-virtuales",
                        subpaths: ["aulas-virtuales"],
                        selected: false,
                        permission: "meetings",
                        role: ["super-user", this.show_meeting_section]
                    },
                    {
                        title: "Cuentas Zoom",
                        icon: "fas fa-chalkboard-teacher",
                        path: "/aulas-virtuales/cuentas",
                        subpaths: ["cuentas-zoom"],
                        selected: false,
                        permission: "accounts",
                        role: ["super-user"]
                        //Fix -2
                        // permission:"accounts.list"
                    }
                ]
            },
            {
                title: "USUARIOS",
                icon: "fas fa-users-cog",
                active: false,
                items: [
                    {
                        title: "Módulos",
                        icon: "fas fa-th-large",
                        path: "/modulos",
                        subpaths: [
                            "modulos",
                            // "abconfigs",
                            // "categorias",
                            // "cursos"
                        ],
                        selected: false,
                        permission: "modulos",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    },
                    {
                        title: "Usuarios",
                        icon: "fas fa-users",
                        path: "/usuarios",
                        subpaths: ["usuarios"],
                        selected: false,
                        permission: "usuarios",
                        role: ["super-user", "admin"]
                    },
                    {
                        title: "Administradores",
                        icon: "fas fa-users-cog",
                        path: "/users",
                        subpaths: ["users"],
                        selected: false,
                        role: ["super-user", "config"]
                    },
                    {
                        title: "Criterios",
                        icon: "fas fa-clipboard-list",
                        path: "/criterios",
                        subpaths: ["criterios", "valores"],
                        selected: false,
                        permission: "criterios",
                        role: ["super-user", "config", "admin"]
                    },
                    {
                        title:"Supervisores",
                        icon:"fas fa-sitemap",
                        // path:"/reportes-supervisores/index",
                        path:"/supervisores",
                        subpaths:["reportes-supervisores"],
                        selected:false,
                        permission:"supervisores",
                        role:["super-user","admin"]
                    },
                ]
            },
            {
                title: "GESTIONA TUS CURSOS",
                icon: "fas fa-cog",
                active: false,
                items: [
                    {
                        title: "Módulos",
                        icon: "fas fa-th-large",
                        path: "/modulos",
                        subpaths: [
                            "modulos",
                            // "abconfigs",
                            // "categorias",
                            // "cursos"
                        ],
                        selected: false,
                        permission: "modulos",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    },
                    {
                        title: "Escuelas",
                        icon: "fas fa-th-large",
                        path: "/escuelas",
                        subpaths: ["escuelas"],
                        selected: false,
                        permission: "escuelas",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    },
                    {
                        title: "Cursos",
                        icon: "mdi mdi-notebook",
                        path: "/cursos",
                        subpaths: ["cursos"],
                        selected: false,
                        permission: "cursos",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                        ]
                    },
                    // {
                    //     title:"Cursos",
                    //     icon:"fas fa-map-signs",
                    //     path:"/cursos",
                    //     subpaths:["cursos"],
                    //     selected:false,
                    //     permission:"cursos",
                    //     role:["super-user","admin","content-manager","trainer"]
                    // },
                ]
            },

            {
                title: "GESTIONA TU CONTENIDO",
                icon: "fas fa-pen-square",
                active: false,
                items: [
                    {
                        title: "Anuncios",
                        icon: "far fa-newspaper",
                        path: "/anuncios",
                        subpaths: ["anuncios"],
                        selected: false,
                        permission: "anuncios",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    },
                    {
                        title: "Encuestas",
                        icon: "fas fa-pencil-alt",
                        path: "/encuestas",
                        subpaths: ["encuestas"],
                        selected: false,
                        permission: "encuestas",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    },
                    {
                        title: "Multimedia",
                        icon: "fas fa-photo-video",
                        path: "/multimedia",
                        subpaths: ["multimedia"],
                        selected: false,
                        permission: "multimedia",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    },

                    {
                         title:"Videoteca",
                         icon:"fas fa-caret-square-right",
                         path:"/videoteca/list",
                         subpaths:["videoteca"],
                         selected:false,
                         permission:"videoteca",
                         role:["super-user","admin","content-manager","trainer"]
                     },
                ]
            },
            {
                title: "ENTRENADORES Y CHECKLIST",
                icon: "fas fa-business-time",
                active: false,
                items: [
                    {
                        title: "Entrenadores",
                        icon: "fas fa-user-graduate",
                        path: "/entrenamiento/entrenadores",
                        subpaths: ["entrenamiento/entrenador"],
                        selected: false,
                        permission: "entrenadores",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    },
                    {
                        title: "Checklists",
                        icon: "fas fa-tasks",
                        path: "/entrenamiento/checklists",
                        subpaths: ["entrenamiento/checklist"],
                        selected: false,
                        permission: "checklist",
                        role: [
                            "super-user",
                            "admin",
                            "content-manager",
                            "trainer"
                        ]
                    }
                ]
            },
            {
                title: "REPORTES",
                icon: "fas fa-download",
                active: false,
                items: [
                    {
                        title: "General",
                        icon: "fas fa-download",
                        path: "/exportar/node",
                        subpaths: ["exportar/node"],
                        selected: false,
                        permission: "reportes",
                        role: [
                            "super-user",
                            "admin",
                            "trainer",
                            "reports",
                            'only-reports'
                        ]
                    },
                    // {
                    //     title: "Aulas Virtuales",
                    //     icon: "fas fa-download",
                    //     path: "/exportar/conferencias",
                    //     subpaths: ["exportar/conferencias"],
                    //     selected: false,
                    //     permission: "conferencias",
                    //     role: [
                    //         "super-user",
                    //         "admin-TEMPORAL_INACTIVO",
                    //         "trainer-TEMPORAL_INACTIVO",
                    //         "reports-TEMPORAL_INACTIVO"
                    //     ]
                    // },
                    {
                        title: "Encuestas",
                        icon: "fas fa-poll",
                        path: "/resumen_encuesta",
                        subpaths: ["resumen_encuesta"],
                        selected: false,
                        permission: "resumen_encuesta",
                        role: [
                            "super-user",
                            "admin",
                            "trainer",
                            "reports",
                            'only-reports'
                        ]
                    }
                ]
            },
            {
                title: "HERRAMIENTAS",
                icon: "fas fa-tools",
                active: false,
                items: [
                    {
                        title: "Notificaciones push",
                        icon: "fas fa-envelope-open-text",
                        path: "/notificaciones_push",
                        subpaths: ["notificaciones_push"],
                        selected: false,
                        permission: "notificaciones",
                        role: ["super-user", "admin"]
                    },
                    {
                        title: "Intentos masivos",
                        icon: "fas fa-redo-alt",
                        path: "/masivo/usuarios/index_reinicios",
                        subpaths: ["masivo/usuarios"],
                        selected: false,
                        permission: "reinicio_usuarios",
                        role: ["super-user", "admin"]
                    },
                    {
                        title: "Subida masivos",
                        icon: "fas fa-share-square",
                        path: "/masivos",
                        subpaths: ["masivos"],
                        selected: false,
                        permission: "proceso_masivo",
                        role: ["super-user", "admin"]
                    },
                    {
                        title: "Subida de notas",
                        icon: "fas fa-share-square",
                        path: "/importar-notas",
                        subpaths: ["importar-notas"],
                        selected: false,
                        permission: "proceso_masivo_notas",
                        role: ["super-user", "admin"]
                    },
                    {
                        title: "Documentación API",
                        icon: "fas fa-file",
                        path: "/documentation-api",
                        subpaths: ["documentation-api"],
                        selected: false,
                        permission: "documentation_api",
                        role: ["super-user", "admin"]
                    }
                ]
            },
            {
                title: "ATENCIÓN AL COLABORADOR",
                icon: "fas fa-headset",
                active: false,
                items: [
                    {
                        title: "Preguntas frecuentes",
                        icon: "far fa-question-circle",
                        path: "/preguntas-frecuentes",
                        subpaths: ["preguntas-frecuentes"],
                        selected: false,
                        role: ["super-user", "config"]
                    },
                    {
                        title: "Formulario de Ayuda",
                        icon: "far fa-clipboard",
                        path: "/soporte/formulario-ayuda",
                        subpaths: ["formulario-ayuda"],
                        selected: false,
                        role: ["super-user"]
                    },
                    // {
                    //     title:"Ayuda",
                    //     icon:"fas fa-hands-helping",
                    //     path:"/ayudas",
                    //     subpaths:["ayudas"],
                    //     selected:false,
                    //     permission:"ayuda.index"
                    // },
                    {
                        title:"Soporte",
                        icon:"fas fa-headset",
                        path:"/soporte",
                        subpaths:["soporte"],
                        selected:false,
                        role: ["super-user", "admin"]
                    },
                ]
            }
        ]
    }),
    props: {
        roles: {
            type: Array,
            required: true
        },
        show_meeting_section: {
            type: String,
            required: true
        }

    },
    computed: {
        gruposFiltrado: function() {
            let vue = this;
            let new_grupos = [];
            let location = window.location.pathname.split("/");
            this.grupos.forEach(grupo => {
                let new_items = [];
                grupo.items.forEach(i => {
                    vue.roles.forEach(item => {
                        if (i.role.includes(item)) {
                            new_items.push(i);
                            if (this.verify_path(location, i.subpaths)) {
                                grupo.active = true;
                                i.selected = true;
                            }
                        }
                    });
                });
                if (new_items.length > 0) {
                    grupo.items = new_items;
                    new_grupos.push(grupo);
                }
            });
            console.log(new_grupos);
            return new_grupos;
        }
    },
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
                vue.workspaces = data.data.data;
            });
        },

        /**
         * Add new Item by workspace id
         * */
        availableItemGroup(in_title, item) {
          let vue = this;
          const index = vue.grupos.findIndex(({title}) => title === in_title);
          vue.grupos[index].items.push(item);
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

                //=== only for "Farmacias Peruanas"
                const { session:{ workspace } } = data;
                if(workspace.id === 25) {
                  vue.availableItemGroup('GESTIONA TU CONTENIDO', SUB_ITEM_GLOSARY);
                }

                vue.availableItemGroup('GESTIONA TU CONTENIDO', SUB_ITEM_VADEMECUM);
                //=== only for "Farmacias Peruanas"

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

                window.location.href = "/welcome";
            });
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
    margin-left: 31px;
}
.list_submenu .v-list-item__title.item_title {
    white-space: normal;
    font-family: "Nunito", sans-serif;
    font-size: 13px;
    font-weight: 400;
    letter-spacing: 0.1px;
    color: #E5E6FC;
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
.item_icon {
    color: rgba(255, 255, 255, 0.85);
}
.item_title {
    color: rgba(255, 255, 255, 0.85);
    font-size: 0.93em;
}
</style>
