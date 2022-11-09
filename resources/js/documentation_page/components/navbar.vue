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
        <v-card-title class="v-card__title bg-default-primary d-flex justify-center rounded-0">
            <img src="/img/logo_cursalab_white.png" alt="Cursalab" width="130">
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
                        <v-list-item-title class="grupo_title" v-text="item.title"></v-list-item-title>
                    </template>
                    <div v-for="(item,index) in item.subItems" :key="index" class="my-1" style="margin-left: 1rem;">
                        <v-list-item  dark dense :to="item.link" >
                            <v-list-item-icon >
                                <v-icon class="item_icon">{{item.icon}}</v-icon>
                            </v-list-item-icon>
                                <v-list-item-title class="item_title text-wrap" v-text="item.title"></v-list-item-title>
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
export default {
    data () {
      return {
          list_items:[
              {
                  icon:'mdi-security',
                  title:'Authorization',
                  link:'/documentation-api/secret-key',
                  isGroup :true,
                  subItems:[
                        {
                            icon:'mdi-key',
                            title:'Clave secreta',
                            link:'/documentation-api/secret-key',
                        },
                        {
                            icon:'mdi-fingerprint',
                            title:'Token',
                            link:'/documentation-api/token',
                        },
                  ]
              },
              {
                  icon:'mdi-cloud-sync',
                  title:"Lista de API's",
                  link:'/documentation-api/list-apis',
                  isGroup :true,
                  subItems:[
                        {
                            icon:'mdi-format-list-bulleted',
                            title:'Criterios',
                            link:'/documentation-api/criterions',
                        },
                        {
                            icon:'mdi-playlist-plus',
                            title:'Valores por criterio',
                            link:'/documentation-api/criterion-values',
                        },
                        {
                            icon:'mdi-hexagon',
                            title:'Workspaces',
                            link:'/documentation-api/workspace',
                        },
                        {
                            icon:'mdi-account-convert',
                            title:'Alta de usuarios',
                            link:'/documentation-api/update-create-users',
                        },
                        {
                            icon:'mdi-account-check',
                            title:'Activar usuarios',
                            link:'/documentation-api/activate-users',
                        },
                        {
                            icon:'mdi-account-off',
                            title:'Inactivar usuarios',
                            link:'/documentation-api/inactivate-users',
                        },
                        {
                            icon:'mdi-chart-areaspline',
                            title:'Avance de usuarios',
                            link:'/documentation-api/progress',
                        },
                        {
                            icon:'mdi-account',
                            title:'Listado de usuarios',
                            link:'/documentation-api/users',
                        },
                        {
                            icon:'mdi-book',
                            title:'Listado de cursos',
                            link:'/documentation-api/courses',
                        },
                        {
                            icon:'mdi-book-open-variant',
                            title:'Progreso de un Curso',
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
</style>