<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
           Actualización de usuarios
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Este proceso permite actualizar usuarios usando como identificador el número de documento.
            </p>
            <descriptionApi
                v-if="initialized"
                :options="api_description_options" :set_responses="true" />
        </v-card-text>
    </v-card>
</template>
<script>
import descriptionApi from '../components/description_api.vue';
import axios from "axios";
let base_url = window.location.origin;
const is_inretail =  base_url.includes('inretail');
const criterions = is_inretail ?
`
                    "module":text,
                    "email_type":text,
                    "national_identifier_number_manager":text,
                    "nombre_de_jefe":text,
                    "posicion_jefe":text,`
:
`
                    "module":text,
                    "gender":text,
                    "area":text`
export default {
    components: {descriptionApi},
    data() {
        return{
            initialized: false,
            api_description_options:{
                title:'Actualizar usuarios',
                type:'POST',
                route:'/integrations/update-create-users',
                parameters_type:[
                    {
                        title:'Parámetros (body)',
                        parameters:[
                            {
                                name:'usuarios',
                                type:'Array de usuarios(objeto)',
                                description:`
                                    Listado de usuarios a actualizar. Solo se deben enviar los datos a actualizar; no es necesario enviar todos los datos.<br>
                                    <ul>
                                        Estáticos:"active","document", "fullname","phone_number", etc.<br>
                                        Dinámicos: "criterions"<br>
                                        Campos obligatorios: "document"<br>
                                        Ejemplo:<br>
<pre class='language-js line-numbers'><code>
    {
        "workspace_id":"Identificador del workspace",
        "users": [
            {
                "document": text,
                "criterions": {
CRITERION_LIST
                }
            }
        ]
    }
</code></pre>
                                    </ul>                                `
                            }
                        ]
                    },
                    {
                        title:'Parámetros (header)',
                        parameters:[
                            {
                                name:'secretKey',
                                type:'String',
                                description:'Clave secreta asociada a la cuenta del administrador.'
                            },
                            {
                                name:'Authorization',
                                type:'String',
                                description:`
                                    Token asociado a la cuenta del administrador concatenado con el tipo de token.<br>Ejemplo:<br>
<pre class='language-js line-numbers'>
    <code>
        token:'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
    </code>
</pre>`
                            }
                        ]
                    }
                ],
                example_code:{
                    title:'Ejemplo',
                    tabs:[
                        'Request', 'Response (200)'
                    ],
                    content_tabs:[
{
type:'language-js',
code:
`
const base_url = '${base_url}';
let axios = require('axios');
let data = JSON.stringify({
    "users":
        [
            {
                "document": text,
                "criterions": {
CRITERION_LIST
                }
            }
        ]
    }
);
var config = {
    method: 'post',
    url: base_url+'/integrations/update-create-users',
    headers: {
        'secretKey': 'f*hdj[!GbdZQ4{#zKlot',
        'Authorization': 'Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ...',
        'Content-Type': 'application/json'
    },
    data : data
};

axios(config).then(function (response) {
    console.log(JSON.stringify(response.data));
}).catch(function (error) {
    console.log(error);
});`}
,{
type:'language-js',
code:
`
{
    "data": {
        "amount_updated_users": "Cantidad de usuarios actualizados.",
        "amount_errors":"Cantidad de errores encontrados."
        "processed_data": "Cantidad de data recibida.",
        "updated_users": "Documento y identificador del workspace de los usuarios actualizados."
        "errors": "Listado de errores encontrados en la api."
    }
}`
}],
                }
            },
        }
    },
    mounted() {
        this.loadData()
    },
    methods: {
        async loadData() {

            let url = '../criterios/workspace'
            try {

                let response = await axios({
                    method: 'get',
                    url: url
                })

                // Generate workspace criterions list

                let criterionsList = [];
                response.data.forEach(c => {
                    criterionsList.push(`                    "${c.code}": text (Optional)`);
                })

                // Replace criteria in documentation code

                this.api_description_options.example_code.content_tabs[0].code = this.api_description_options.example_code.content_tabs[0].code.replace('CRITERION_LIST', criterionsList.join(',\n'))

                this.api_description_options.parameters_type[0].parameters[0].description = this.api_description_options.parameters_type[0].parameters[0].description.replace('CRITERION_LIST', criterionsList.join(',\n'))

                this.initialized = true


            } catch (e) {
                console.log(e)
            }
        }
    }
}
</script>
