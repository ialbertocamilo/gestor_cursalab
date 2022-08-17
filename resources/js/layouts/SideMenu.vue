<template>
    <v-card flat tile :collapse="!collapseOnScroll">
        <v-card-title class="bg-default-primary d-flex justify-content-center rounded-0">
            <img :src="`/img/ucfp_logo_blanco.png`" alt="Farmacias peruanas" width="140">
            <!-- <v-app-bar-nav-icon color="white"></v-app-bar-nav-icon> -->
        </v-card-title>
        <v-list class="mx-auto"
        dark
        nav
        rounded
        expand
        tile>
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
                    <v-list-item-title v-text="grupo.title" class="grupo_title"></v-list-item-title>
                </template>
                    <div v-for="(item, i) in grupo.items" :key="i" class="list_submenu">
                        <v-list-item
                        dark
                        dense
                        :href="`${item.path}`"
                        v-model="item.selected"
                        >
                            <v-list-item-icon >
                                <v-icon small v-text="item.icon" class="item_icon"></v-icon>
                            </v-list-item-icon>
                            <v-list-item-title v-text="item.title" class="item_title"></v-list-item-title>
                        </v-list-item>
                    </div>
            </v-list-group>
        </v-list>
    </v-card>
</template>

<script>

export default {
    data: () => ({
        collapseOnScroll: true,
        grupos: [
            {
                title:"RESUMEN",
                icon:"fas fa-dice-d6",
                active:false,
                items:[
                    {
                        title:"Dashboard",
                        icon:"fas fa-tachometer-alt",
                        path:"/home",
                        subpaths:["home"],
                        selected:false,
                        permission:"home"
                    },
                    {
                        title:"Learning Analytics",
                        icon:"fas fa-chart-line",
                        path:"/dashboard_pbi",
                        subpaths:["dashboard_pbi"],
                        selected:false,
                        permission:"learning_analytics"
                    },
                    {
                        title:"Resultados de encuestas",
                        icon:"fas fa-poll",
                        path:"/resumen_encuesta/index",
                        subpaths:["resumen_encuesta"],
                        selected:false,
                        permission:"resumen_encuesta"
                    }
                ]
            },
            {
                title:"AULAS VIRTUALES",
                icon:"fas fa-chalkboard",
                active:false,
                items:[
                    {
                        title:"Aulas Virtuales",
                        icon:"fas fa-chalkboard-teacher",
                        path:"/aulas-virtuales",
                        subpaths:["aulas-virtuales"],
                        selected:false,
                        permission:"meetings"
                    },
                    {
                        title:"Cuentas Zoom",
                        icon:"fas fa-chalkboard-teacher",
                        path:"/aulas-virtuales/cuentas",
                        subpaths:["cuentas-zoom"],
                        selected:false,
                        permission:"accounts",
                        //Fix -2
                        // permission:"accounts.list"
                    },
                ]
            },
            {
                title:"USUARIOS",
                icon:"fas fa-users-cog",
                active:false,
                items:[
                    {
                        title:"Usuarios",
                        icon:"fas fa-users",
                        path:"/usuarios",
                        subpaths:['usuarios'],
                        selected:false,
                        permission:"usuarios"
                    },
                    {
                        title:"Código de matrícula",
                        icon:"fas fa-grip-horizontal",
                        path:"/grupos/index",
                        subpaths:["grupos"],
                        selected:false,
                        permission:"grupos"
                    },
                    {
                        title:"Cargos",
                        icon:"fas fa-user-tie",
                        path:"/cargos",
                        subpaths:["cargos"],
                        selected:false,
                        permission:"cargos"
                    },
                    {
                        title:"Sedes",
                        icon:"fas fa-building",
                        path:"/boticas",
                        subpaths:["boticas"],
                        selected:false,
                        permission:"sedes"
                    },
                    {
                        title:"Criterios",
                        icon:"fas fa-clipboard-list",
                        path:"/criterios",
                        subpaths:["criterios", "valores"],
                        selected:false,
                        permission:"criterios"
                    },
                    {
                        title:"Supervisores",
                        icon:"fas fa-sitemap",
                        // path:"/reportes-supervisores/index",
                        path:"/supervisores",
                        subpaths:["reportes-supervisores"],
                        selected:false,
                        permission:"supervisores"
                    },
                ]
            },
            {
                title:"GESTIONA TUS CURSOS",
                icon:"fas fa-cog",
                active:false,
                items:[
                    {
                        title:"Módulos",
                        icon:"fas fa-th-large",
                        path:"/modulos",
                        subpaths:['modulos', 'abconfigs', 'categorias', 'cursos'],
                        selected:false,
                        permission:"modulos"
                    },
                    {
                        title:"Segmentación",
                        icon:"fas fa-map-signs",
                        path:"/curriculas_grupos",
                        subpaths:["curriculas_grupos"],
                        selected:false,
                        permission:"segmentacion"
                    },
                    {
                        title:"Carreras",
                        icon:"fas fa-th-large",
                        subpaths:["carreras"],
                        selected:false,
                        path:"/carreras/index",
                        permission:"carreras"
                    },
                ]
            },

            {
                title:"GESTIONA TU CONTENIDO",
                icon:"fas fa-pen-square",
                active:false,
                items:[
                    {
                        title:"Anuncios",
                        icon:"far fa-newspaper",
                        path:"/anuncios",
                        subpaths:["anuncios"],
                        selected:false,
                        permission:"anuncios"
                    },
                    {
                        title:"Encuestas",
                        icon:"fas fa-pencil-alt",
                        path:"/encuestas",
                        subpaths:["encuestas"],
                        selected:false,
                        permission:"encuestas"
                    },
                    {
                        title:"Multimedia",
                        icon:"fas fa-photo-video",
                        path:"/multimedia",
                        subpaths:["multimedia"],
                        selected:false,
                        permission:"multimedia"
                    },
                    {
                        title:"Glosario",
                        icon:"fas fa-book",
                        path:"/glosario",
                        subpaths:["glosario"],
                        selected:false,
                        permission:"glosario"
                    },
                    {
                        title:"Vademécum",
                        icon:"fas fa-file-invoice",
                        path:"/vademecum",
                        subpaths:["vademecum"],
                        selected:false,
                        permission:"vademecum"
                    },
                    {
                        title:"Videoteca",
                        icon:"fas fa-caret-square-right",
                        path:"/videoteca/list",
                        subpaths:["videoteca"],
                        selected:false,
                        permission:"videoteca"
                    },
                    {
                        title:"Tags",
                        icon:"fas fa-tags",
                        path:"/tags",
                        subpaths:["tags"],
                        selected:false,
                        permission:"tags"
                    },
                ]
            },
            {
                title:"ENTRENADORES Y CHECKLIST",
                icon:"fas fa-business-time",
                active:false,
                items:[
                    {
                        title:"Entrenadores",
                        icon:"fas fa-user-graduate",
                        path:"/entrenamiento/entrenadores",
                        subpaths:["entrenamiento/entrenador"],
                        selected:false,
                        permission:"entrenadores"
                    },
                    {
                        title:"Checklists",
                        icon:"fas fa-tasks",
                        path:"/entrenamiento/checklists",
                        subpaths:["entrenamiento/checklist"],
                        selected:false,
                        permission:"checklist"
                    },
                ]
            },
            {
                title:"EXPORTAR",
                icon:"fas fa-download",
                active:false,
                items:[
                    {
                        title:"Reportes",
                        icon:"fas fa-download",
                        path:"/exportar/node",
                        subpaths:["exportar/node"],
                        selected:false,
                        permission:"reportes"
                    },
                    // {
                    //     title:"Reportes (NEW)",
                    //     icon:"fas fa-download",
                    //     path:"/reportes/general",
                    //     subpaths:["reportes"],
                    //     selected:false,
                        // permission:"exportar.index"
                    // },

                    {
                        title:"Conferencias",
                        icon:"fas fa-download",
                        path:"/exportar/conferencias",
                        subpaths:["exportar/conferencias"],
                        selected:false,
                        permission:"conferencias"
                    },
                    // {
                    //     title:"Otros",
                    //     icon:"fas fa-download",
                    //     path:"/reportes/usuarios",
                    //     subpaths:["reportes/usuarios"],
                    //     selected:false,
                        // permission:"exportar.index"
                    // },
                ]
            },
            {
                title:"ATENCIÓN AL CLIENTE",
                icon:"fas fa-headset",
                active:false,
                items:[
                    {
                        title:"Notificaciones push",
                        icon:"fas fa-envelope-open-text",
                        path:"/notificaciones_push",
                        subpaths:["notificaciones_push"],
                        selected:false,
                        permission:"notificaciones"
                    },
                    {
                        title:"Preguntas frecuentes",
                        icon:"far fa-question-circle",
                        path:"/preguntas-frecuentes",
                         subpaths:["preguntas-frecuentes"],
                        selected:false,
                        permission:"faq"
                    },
                    {
                        title:"Ayuda",
                        icon:"fas fa-hands-helping",
                        path:"/ayudas",
                        subpaths:["ayudas"],
                        selected:false,
                        permission:"ayuda"
                    },
                    {
                        title:"Soporte",
                        icon:"fas fa-headset",
                        path:"/soporte",
                        subpaths:["soporte"],
                        selected:false,
                        permission:"soporte"
                    },
                ]
            },
            {
                title:"HERRAMIENTAS",
                icon:"fas fa-tools",
                active:false,
                items:[
                    {
                        title:"Reinicio de usuarios",
                        icon:"fas fa-redo-alt",
                        path:"/masivo/usuarios/index_reinicios",
                        subpaths:["masivo/usuarios"],
                        selected:false,
                        permission:"reinicio_usuarios"
                    },
                    {
                        title:"Procesos masivos",
                        icon:"fas fa-share-square",
                        path:"/masivo/index",
                        subpaths:["masivo/index"],
                        selected:false,
                        permission:"proceso_masivo"
                    },
                    {
                        title:"Migrar Avance",
                        icon:"fas fa-exchange-alt",
                        path:"/migrar_avance",
                        subpaths:["migrar_avance"],
                        selected:false,
                        permission:"migrar_avance"
                    },
                    {
                        title:"Compatibles",
                        icon:"fas fa-equals",
                        path:"/compatibles",
                        subpaths:["compatibles"],
                        selected:false,
                        permission:"compatibles"
                    }
                ]
            },
            {
                title:"AUDITORÍA",
                icon:"fas fa-check-double",
                active:false,
                items:[
                    {
                        title:"Incidencias",
                        icon:"fas fa-exclamation-triangle",
                        path:"/incidencias",
                        subpaths:["incidencias"],
                        selected:false,
                        permission:"incidencias"
                    },
                    {
                        title:"Errores",
                        icon:"fas fa-bug",
                        path:"/errores",
                        subpaths:["errores"],
                        selected:false,
                        permission:"errores"
                    },
                    {
                        title:"Auditoría",
                        icon:"fas fa-receipt",
                        path:"/auditoria",
                        subpaths:["auditoria"],
                        selected:false,
                        permission:"auditoria"
                    }
                ]
            },
            {
                title:"ROLES Y PERMISOS",
                icon:"fas fa-users-cog",
                active:false,
                items:[
                    {
                        title:"Administradores",
                        icon:"fas fa-users-cog",
                        path:"/users",
                        subpaths:["users"],
                        selected:false,
                        permission:"users"
                    },
                    {
                        title:"Roles",
                        icon:"fas fa-user-tie",
                        path:"/roles",
                        subpaths:["roles"],
                        selected:false,
                        permission:"roles"
                    },
                    // {
                    //     title:"Permisos",
                    //     icon:"fas fa-shield-alt",
                    //     path:"/permisos",
                    //     subpaths:["permisos"],
                    //     selected:false,
                    //     // permission:"permisos.index"
                    // }
                ]
            }
        ]
    }),
    props: {
        options: {
            type: Array,
            required: true
        },
    },
    computed: {
        gruposFiltrado: function () {
            let vue = this
            let new_grupos = []
            let location = window.location.pathname.split('/');
            this.grupos.forEach((grupo) => {
                let new_items = []
                grupo.items.forEach((i) => {
                    vue.options.forEach((item)=>{
                        if(item == 'all'){
                            new_items.push(i)
                            if(this.verify_path(location,i.subpaths)){
                                grupo.active = true;
                                i.selected=true;
                            }
                        }else{
                            if(i.permission == item){
                                new_items.push(i)
                                if(this.verify_path(location,i.subpaths)){
                                    grupo.active = true;
                                    i.selected=true;
                                }
                            }
                        }
                    })
                })
                if(new_items.length > 0){
                    grupo.items = new_items;
                    new_grupos.push(grupo)
                }
            });
            return new_grupos;
        }
    },
    methods:{
        verify_path(location,subpaths){
            return subpaths.find((e)=>{
                let lt  = ((e.split('/')).length > 1 && location.length >1 ) ? (location[1]+'/'+location[2]) : location[1] ;
                // console.log(lt,e);
                return lt.includes(e)
            })
        }
    }
}
</script>

<style scoped>
.v-list{
    background: #5458ea;
}
.v-list-group--active .grupo_title, .v-list-group--active .v-list-item__icon{
    color:#FFF;
}
.list_submenu{
    margin-left: 10px;
}
.list_submenu .v-list-item{
    margin-top: 3px;
    margin-bottom: 3px;
}
.v-list-item:hover{
    background: rgba(255, 255, 255, 0.2);
}
.v-application--is-ltr .v-list-item__icon:first-child {
    margin-right: 8px;
}
.v-list-item{
    text-decoration: none;
}
/* Custom class */
.grupo_title{
    font-size: 0.73em!important;
    font-weight: bold!important;
    white-space: normal;
}
.item_icon{
    color: rgba(255, 255, 255, 0.85);
}
.item_title{
    color: rgba(255, 255, 255, 0.85);
    font-size: .93em;
}
</style>
