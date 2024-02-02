<template>
    <v-card elevation="0" class="mx-10">
        <v-card-title class="font-weight-bold">
           Alta de usuarios (crear o actualizar)
        </v-card-title>
        <v-card-text class="ml-2">
            <p>
                Este proceso permite crear o actualizar usuarios usando como identificador el número de DNI.
            </p>
            <descriptionApi :options="api_description_options" :set_responses="true" />
        </v-card-text>
    </v-card>
</template>
<script>
import descriptionApi from '../components/description_api.vue';
let base_url = window.location.origin;
const is_inretail =  base_url.includes('inretail');
const criterions = is_inretail ?
`
                    "module":text,
                    "user_action_id":text,
                    "document_type_id":text,
                    "business_unit_id":text,
                    "business_unit_name":text,
                    "gender":text,
                    "position_name":text,
                    "position_code":text,
                    "date_start":text,
                    "termination_date":date,
                    "seniority_date":date,
                    "birthday_date":date,
                    "phone_type_id":text,
                    "aplica_a_bono":text,
                    "tipo_de_bono":text,
                    "grupo_ocupacional":text,
                    "location_code":text,
                    "location_name":text,
                    "department_name":text,
                    "department_code":text,
                    "modalidad_de_trabajo":text,
                    "department_name_nivel_1":text,
                    "department_name_nivel_2":text,
                    "department_name_nivel_3":text,
                    "department_name_nivel_4":text,
                    "department_name_nivel_5":text,
                    "department_name_nivel_6":text,
                    "email_type":text,
                    "national_identifier_number_manager":text,
                    "nombre_de_jefe":text,
                    "posicion_jefe":text,
                    "clasificacion_de_evd":text,
                    "gor_gerente_de_área":text,
                    "botica":text,
                    "grupo":text,
                    "zonal":text,
                    "correo_zonal":text,
                    "tipo_de_publico":text,
                    "division":text,
                    "area":text,
                    "region":text,
                    "region_de_tienda":text,
                    "correo_jefe":text,
                    "grupos_de_supervision_supply":text,
                    "gerente_de_area_o_mall":text,
`
: 
`
                    "module":text,
                    "gender":text,
                    "area":text,
                    "position_name":text,
                    "department_name":text,
                    "date_start":text,
`
export default {
    components: {descriptionApi},
    data() {
        return{
            api_description_options:{
                title:'Crear o actualizar usuarios',
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
                                    Listado de usuarios a actualizar/crear. Cada usuario contiene atributos estáticos y dinámicos<br>
                                    <ul>
                                        Estáticos:"active","document", "fullname","phone_number", etc.<br>
                                        Dinámicos: "criterions"<br>
                                        Ejemplo:<br>
<pre class='language-js line-numbers'><code>
    {
        "workspace_id":"Identificador del workspace",
        "users": [
            {
                "active": boolean,
                "document": text,
                "person_number": text,
                "fullname": text,
                "name": text,
                "lastname": text,
                "surname": text,
                "username": text,
                "phone_number": number,
                "email": text,
                "criterions": {
                    ${criterions}
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
            "active": boolean,
            "document": text,
            "person_number": text,
            "fullname": text,
            "name": text,
            "lastname": text,
            "surname": text,
            "username": text,
            "phone_number": number,
            "email": text,
            "criterions": {
                ${criterions}
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
        "insert_updated_users": "Cantidad de usuarios insertados/actualizados.",
        "amount_errors": "Cantidad de errores encontrados.",
        "processed_data": "Cantidad de data recibida.",
        "errors": "Listado de errores encontrados en la api." /[
            {
                "error": "Listado de errores encontrados en los atributos del usuario",
                "err_data_original": "Data original del usuario enviada por el api"
            }
        ]
    }
}`
}],
                }
            },
        }
    }
}
</script>