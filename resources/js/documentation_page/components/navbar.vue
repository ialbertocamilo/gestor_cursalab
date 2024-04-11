<template>
  <div>
    <v-app-bar
        app
        color="white"
        elevate-on-scroll
        scroll-target="#scrolling-techniques-7"
    >
        <!-- <v-toolbar-title>Developer Cursalab</v-toolbar-title> -->
        <v-spacer/>
        <v-btn depressed @click="return_gestor()">Regresar al gestor</v-btn>
    </v-app-bar>
    <v-navigation-drawer
        permanent
        app
        class="bg-default-primary"
    >
        <v-card-title class="title-logo-wrapper bg-white flex-center rounded-0">
            <img src="/img/logo_cursalab_v2_black.png" alt="Cursalab" width="130">
        </v-card-title>
        <v-list
            dark
            nav
            rounded
            expand
            tile
        >
            <div v-for="(item,index) in list_items" :key="index">
                <v-list-group
                    color="white"
                    v-if="item.isGroup"
                >
                    <template v-slot:activator>
                        <v-list-item-icon >
                            <v-icon class="item_icon">{{item.icon}}</v-icon>
                        </v-list-item-icon>
                        <v-list-item-title class="grupo_title">{{ item.title }}</v-list-item-title>
                    </template>
                    <div v-for="(item,index) in item.subItems" :key="index" class="my-1" style="margin-left: 1rem;">
                        <v-list-item  dark dense :to="item.link" v-if="item.show">
                            <v-list-item-icon >
                                <v-icon class="item_icon">{{item.icon}}</v-icon>
                            </v-list-item-icon>
                                <v-list-item-title class="item_title text-wrap">{{ item.title }}</v-list-item-title>
                        </v-list-item>
                    </div>
                </v-list-group>
            </div>
        </v-list>
    </v-navigation-drawer>
    <v-main style="background-color:#f8f8fb">
        <v-container fluid >
            <router-view></router-view>
        </v-container>
    </v-main>
  </div>
</template>
<script>
const base_url = window.location.origin;
const is_inretail =  base_url.includes('inretail');
export default {
    data () {
      return {
          list_items:[
              {
                  icon:'mdi-alert-circle-outline',
                  title:'Limitaciones',
                  link:'/documentation-api/limitations',
                  isGroup :true,
                  subItems: [
                      {
                          icon:'mdi-alert-circle-outline',
                          title:'Solicitudes',
                          show: true,
                          link:'/documentation-api/limitations',
                      },
                  ]
              },
              {
                  icon:'mdi-security',
                  title:'Autorización',
                  link:'/documentation-api/secret-key',
                  isGroup :true,
                  subItems:[
                        {
                            icon:'mdi-key',
                            title:'Clave secreta',
                            show:true,
                            link:'/documentation-api/secret-key',
                        },
                        {
                            icon:'mdi-fingerprint',
                            title:'Token',
                            show:true,
                            link:'/documentation-api/token',
                        },
                  ]
              },
              {
                  icon:'mdi-cloud-sync',
                  title:"Recursos",
                  link:'/documentation-api/list-apis',
                  isGroup :true,
                  subItems:[
                        {
                            icon:'mdi-format-list-bulleted',
                            title:'Criterios',
                            show:true,
                            link:'/documentation-api/criterions',
                        },
                        {
                            icon:'mdi-playlist-plus',
                            title:'Valores por criterio',
                            show:true,
                            link:'/documentation-api/criterion-values',
                        },
                        {
                            icon:'mdi-hexagon',
                            title:'Workspaces',
                            show:true,
                            link:'/documentation-api/workspace',
                        },
                        // {
                        //     icon:'mdi-account-convert',
                        //     title:'Alta de usuarios',
                        //     show:true,
                        //     link:'/documentation-api/update-create-users',
                        // },
                        {
                            icon:'mdi-account-multiple-plus',
                            title:'Crear usuarios',
                            show:true,
                            link:'/documentation-api/create-users',
                        },
                        {
                            icon:'mdi-account-edit',
                            title:'Actualizar usuarios',
                            show:true,
                            link:'/documentation-api/update-users',
                        },
                        {
                            icon:'mdi-account-check',
                            title:'Activar usuarios',
                            show:true,
                            link:'/documentation-api/activate-users',
                        },
                        {
                            icon:'mdi-account-off',
                            title:'Inactivar usuarios',
                            show:true,
                            link:'/documentation-api/inactivate-users',
                        },
                        {
                            icon:'mdi-chart-areaspline',
                            title:'Avance de usuarios',
                            show: is_inretail,
                            link:'/documentation-api/progress',
                        },
                        {
                            icon:'mdi-account',
                            title:'Listado de usuarios',
                            show:true,
                            link:'/documentation-api/users',
                        },
                        {
                            icon:'mdi-book',
                            title:'Listado de cursos',
                            show:is_inretail,
                            link:'/documentation-api/courses',
                        },
                        {
                            icon:'mdi-book-open-variant',
                            title:'Progreso de un Curso',
                            show:is_inretail,
                            link:'/documentation-api/course-progress',
                        }
                  ]
              }
          ]
      }
    },
    methods: {
        return_gestor(){
            window.location.href="../"
        }
    }
}
</script>
<style scoped>
.v-icon{
    color:white !important;
}
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
    padding-left:1px !important;
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
    white-space: normal;
}
.item_icon{
    color: rgba(255, 255, 255, 0.85);
    font-size: 1.2em!important;
}
.item_title{
    color: rgba(255, 255, 255, 0.85);
    font-size: .93em;
}

.bg-default-primary{
    background:#5458ea
}
.flex-center{
    display: flex;
    justify-content: center;
    align-items: center;
}
.bg-white{
    background: white;
}
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

</style>
