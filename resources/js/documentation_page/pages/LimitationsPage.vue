<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Limitaciones para cantidad de solicitudes
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Para garantizar un rendimiento óptimo y justo para todos los usuarios, hemos establecido límites en la cantidad de solicitudes que se pueden realizar a nuestro API. Estas restricciones están diseñadas para prevenir el abuso del sistema y garantizar que todos los usuarios tengan un acceso equitativo a nuestros servicios. Si necesitas realizar más solicitudes, considera optimizar tus consultas o ponte en contacto con nuestro equipo de soporte para discutir opciones adicionales.
            </p>
            <alert
                background='#F8F8FB'
                border='#C1C1FF'
                icon='mdi-information-outline'
                text=" Cada cuenta de desarrollador tiene un límite máximo de 20 solicitudes por minuto y 50 solicitudes por día."
                class="mb-6"
            />

        </v-card-text>
    </v-card>
</template>
<script>
import alert from '../components/alert.vue';
import descriptionApi from '../components/description_api.vue';
let base_url = window.location.origin;
export default {
    components: {alert,descriptionApi},
    data(){
        return{

        }
    }
}
</script>
