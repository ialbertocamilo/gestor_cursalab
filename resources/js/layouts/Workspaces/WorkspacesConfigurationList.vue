<template>
    <div class="wrapper">
        <div class="row justify-content-center pt-5">
            <div class="col-3">
                <h5>Usuarios</h5>
                <div class="card p-3">
                    <h5>
                        <v-icon>mdi-account-cog</v-icon>
                        Criterios

                        <div class="go-icon">
                            <v-icon>mdi-arrow-top-right-thin</v-icon>
                        </div>
                    </h5>
                    <p>
                        Configura datos generales del ambiente y de branding como el
                        logotipo, colores, etc.
                    </p>
                </div>

                <div class="card p-3 mt-3">
                    <h5>
                        <v-icon>mdi-account-group</v-icon>
                        Usuarios

                        <div class="go-icon">
                            <v-icon>mdi-arrow-top-right-thin</v-icon>
                        </div>
                    </h5>
                    <p>
                        Configura datos generales del ambiente y de branding como el
                        logotipo, colores, etc.
                    </p>
                </div>
            </div>
            <div class="col-3">
                <h5>Roles y permisos</h5>
                <div class="card p-3">
                    <h5>
                        <v-icon>mdi-notebook</v-icon>
                        Roles

                        <div class="go-icon">
                            <v-icon>mdi-arrow-top-right-thin</v-icon>
                        </div>
                    </h5>
                    <p>
                        Configura datos generales del ambiente y de branding como el
                        logotipo, colores, etc.
                    </p>
                </div>

                <div class="card p-3 mt-3">
                    <h5>
                        <v-icon>mdi-key-variant</v-icon>
                        Permisos

                        <div class="go-icon">
                            <v-icon>mdi-arrow-top-right-thin</v-icon>
                        </div>
                    </h5>
                    <p>
                        Configura datos generales del ambiente y de branding como el
                        logotipo, colores, etc.
                    </p>
                </div>

                <div class="card p-3 mt-3">
                    <h5>
                        <v-icon>mdi-account-tie</v-icon>
                        Administradores

                        <div class="go-icon">
                            <v-icon>mdi-arrow-top-right-thin</v-icon>
                        </div>
                    </h5>
                    <p>
                        Configura datos generales del ambiente y de branding como el
                        logotipo, colores, etc.
                    </p>
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

.card {
    background: white;
}

.go-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #dfdffc;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
}
</style>
