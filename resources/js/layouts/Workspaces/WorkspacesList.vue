<template>
    <div class="wrapper">
        <div class="row justify-content-center pt-3 pb-3">
            <div class="col-6">
                <img src="img/we-connect-logo.png"
                     class="logo"
                     alt="">
            </div>
            <div class="col-2 d-flex align-items-center">
                <div class="mr-3">
                    <v-icon class="stats-icon">mdi-account</v-icon>

                    Juan Perez
                </div>


                <v-icon class="stats-icon">mdi-logout</v-icon>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-6">

                <h2>
                    Bienvenido(a) a WeConect 2.0
                </h2>
                <h4>
                    Ingresa a un workspace para administrar  su contenido
                </h4>
            </div>
            <div class="col-2">
                Configuraciones
                <v-icon class="stats-icon">mdi-cog</v-icon>
            </div>
        </div>
        <div class="row justify-content-center mt-3 pt-3 pb-3">
            <div class="col-8">
                <h4>
                    <b>
                        Workspaces
                    </b>
                </h4>
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-2 mr-3">
                <div class="row">
                    <div class="col-12 pt-3 pb-3 bg-white">
                        <img src="https://cdn.cosmicjs.com/6bc9f0f0-974b-11eb-bf6a-1ffd0f06b0f7-vuemastery.svg"
                             class="logo"
                             alt="">
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-sitemap</v-icon>
                        <br>
                        3<br> modulos
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-account-group</v-icon>
                        <br>
                        1350<br> usuarios
                    </div>
                    <div class="col-12 pt-3 pb-3 bg-stats">
                        <button class="btn btn-primary w-100">Ingresar</button>
                    </div>
                </div>
            </div>
            <div class="col-2 mr-3">
                <div class="row">
                    <div class="col-12 pt-3 pb-3 bg-white">
                        <img src="https://cdn.cosmicjs.com/6bc9f0f0-974b-11eb-bf6a-1ffd0f06b0f7-vuemastery.svg"
                             class="logo"
                             alt="">
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-sitemap</v-icon>
                        <br>
                        3<br> modulos
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-account-group</v-icon>
                        <br>
                        1350<br> usuarios
                    </div>
                    <div class="col-12 pt-3 pb-3 bg-stats">
                        <button class="btn btn-primary w-100">Ingresar</button>
                    </div>
                </div>
            </div>
            <div class="col-2 mr-3">
                <div class="row">
                    <div class="col-12 pt-3 pb-3 bg-white">
                        <img src="https://cdn.cosmicjs.com/6bc9f0f0-974b-11eb-bf6a-1ffd0f06b0f7-vuemastery.svg"
                             class="logo"
                             alt="">
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-sitemap</v-icon>
                        <br>
                        3<br> modulos
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-account-group</v-icon>
                        <br>
                        1350<br> usuarios
                    </div>
                    <div class="col-12 pt-3 pb-3 bg-stats">
                        <button class="btn btn-primary w-100">Ingresar</button>
                    </div>
                </div>
            </div>
            <div class="col-2 mr-3">
                <div class="row">
                    <div class="col-12 pt-3 pb-3 bg-white">
                        <img src="https://cdn.cosmicjs.com/6bc9f0f0-974b-11eb-bf6a-1ffd0f06b0f7-vuemastery.svg"
                             class="logo"
                             alt="">
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-sitemap</v-icon>
                        <br>
                        3<br> modulos
                    </div>
                    <div class="col-6 text-center bg-stats pt-1">
                        <v-icon class="stats-icon">mdi-account-group</v-icon>
                        <br>
                        1350<br> usuarios
                    </div>
                    <div class="col-12 pt-3 pb-3 bg-stats">
                        <button class="btn btn-primary w-100">Ingresar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    data() {

    }
    ,
    mounted() {

    }
    ,
    methods: {

    }
}
</script>

<style scoped>

.wrapper {
    position: fixed;
    top: 0;
    bottom: 0;
    right: 0;
    left: 0;
    background: white;
    z-index: 999;
}

.logo {
    max-height: 100px;
    max-width: 200px;
}

.bg-stats {
    background-color: #e5e6f4;
}

.stats-icon {
    color: #5d5fef;
}
</style>
