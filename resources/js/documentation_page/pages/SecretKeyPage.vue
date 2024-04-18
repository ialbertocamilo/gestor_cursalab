<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
            Clave Secreta (Secret Key)
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
               La clave secreta (secret key) es una cadena conformada por números y letras que será necesario para el uso de las API's. 
               Esta clave será entregada al inicio de la implementación y no tienen fecha de caducidad.
            </p>
            <alert 
                background='#F8F8FB'
                border='#C1C1FF'
                icon='mdi-information-outline'
                text="Esta clave secreta está asociada a un tipo específico de administrador (gestor) de la plataforma."
                class="mb-6"
            />
            <descriptionApi :options="api_description_options" />
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
            api_description_options:{
                title:'Obtener Clave Secreta',
                type:'POST',
                route:'/integrations/secret-key',
                parameters_type:[
                    {
                        title:'Parámetros (body)',
                        parameters:[
                            {
                                name:'email',
                                type:'String',
                                description:'Es el correo asociado a la cuenta administrador del gestor.'
                            },
                            {
                                name:'password',
                                type:'String',
                                description:'Es la contraseña asociada a la cuenta administrador del gestor.'
                            },
                        ]
                    }
                ],
                example_code:{
                    title:'Ejemplo',
                    tabs:[
                        'Request', 'Response (200)','Response (401)','Response (403)'
                    ],
                    content_tabs:[
                        {
                            type:'language-javascript',
                            code:
`
const base_url = '${base_url}';
const axios = require('axios');
const data = JSON.stringify({"email":"admin@company.com","password":"PASSWORD"});
const config = {
    method: 'post',
    url: base_url+'/integrations/secret-key',
    headers: { 
        'Content-Type': 'application/json'
    },
    data : data
};
axios(config).then(function (response) {
    console.log(JSON.stringify(response.data));
}).catch(function (error) {
    console.log(error);
});
`
                        },
                        {
                            type:'language-json',
                            code:
`
{
    "data": {
        "secretKey": "Clave Secreta necesaria para el uso de las API's"
    }
}
`
                        },{
                            type:'language-json',
                            code:
`
/*Credenciales incorrectas. Si el problema persiste, por favor, ponte en contacto con el equipo de Cursalab.*/
{
    "data": {
        "message": "Wrong credentials."
    }
}
`
                        },{
                            type:'language-json',
                            code:
`
/*El administrador no tiene una clave secreta asociada.
Por favor, contacta al equipo de Cursalab para verificar esta situación.*/
{
    "data": {
        "secretKey": "This Administrator does not have an associated secretKey."
    }
}
`
                        }
                    ]
                }
            },
        }
    }
}
</script>