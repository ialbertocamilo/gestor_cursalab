<template>
    <DefaultDialog :options="options" :width="options.width" @onCancel="closeModal" @onConfirm="confirmModal">
        <template v-slot:content>
            <v-row justify="center">
                <div class="text-center">
                    <span class="text-primary-sub my-1" style="font-size: 1.1rem;">¡Estás a punto de dar el siguiente paso para el aprendizaje de tu equipo! </span> <br>
                    <span class="my-1">Nuestro acceso total te brinda ilimitadas funcionalidades y servicios. </span><br>
                    <span class="text-primary-sub my-1">Arma tu plataforma a tu medida</span>
                </div>
            </v-row>
            <v-row>
                <v-col cols="12">
                    Selecciona las mejoras de tu plan.
                </v-col>
                <v-col cols="6">
                    <v-card elevation="4">
                        <v-card-text class="d-flex align-items-center">
                            <v-icon>mdi-database</v-icon>
                            <DefaultSelect
                                class="mx-2"
                                clearable
                                v-model="resource.limit_allowed_storage" 
                                :items="storageItems"
                                item-text="name"
                                item-id="value"
                                item-value="value"
                                :prefix="resource.limit_allowed_storage ? '+' : '' "
                                label="¿Cuánto almacenamiento deseas aumentar?"
                            />
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col cols="6">
                    <v-card elevation="4">
                        <v-card-text class="d-flex align-items-center">
                            <v-icon>mdi-account-multiple</v-icon>
                            <DefaultInput
                                class="mx-2"
                                clearable
                                v-model="resource.limit_allowed_users"
                                label="¿Cuántos usuarios deseas aumentar?"
                                type="number"
                                :prefix="resource.limit_allowed_users ? '+' : '' "
                                min="0"
                            />
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col>
                    <div class="container-switch p-1">
                        <div class="px-3 py-2" :class="total_access ? 'switch-inactive' : 'switch-active' " @click="setFunctionalities(false)">
                            <img width="20px" class="mx-1" :class="total_access ? 'image-inactive' : 'image-active' " src="/img/ic_round-dashboard-customize.svg">
                            Módulos adicionales
                        </div>
                        <div class="px-3 py-2" :class="total_access ? 'switch-active' : 'switch-inactive' " @click="setFunctionalities(true)">
                            <img width="20px" :class="total_access ? 'image-active' : 'image-inactive' " class="mx-1" src="/img/rocket.svg">
                            Acceso total
                        </div>
                        <!-- <div class="text">{{ isActive ? 'Inacti' : inactiveText }}</div> -->
                    </div>
                </v-col>
                <v-col cols="12">
                    <v-card elevation="4">
                        <DefaultSelect
                            :showSelectAll="false"
                            class="p-4"
                            multiple
                            value
                            item-text="name"
                            item-id="id"
                            item-value="name"
                            clearable
                            v-model="resource.functionalities" 
                            return-object
                            :items="functionalities"
                            countShowValues="4"
                            label="Funcionalidades y servicios adicionales"
                        />
                    </v-card>
                </v-col>
                <v-col cols="12">
                    <DefaultTextArea
                        clearable
                        v-model="resource.description"
                        label="Detalle (Opcional)"
                        rows="5"
                    />
                </v-col>
            </v-row>
            <!-- <v-row>
                <div v-for="functionality in functionalities " :key="functionality.id">
                    <v-checkbox class="mx-2" v-model="functionality.selected" :label="functionality.name"></v-checkbox>
                </div>
            </v-row> -->
        </template>
    </DefaultDialog>
</template>
<script>

export default {
    props: {
        options: {
            type: Object,
            required: true,
        }
    },
    data() {
        return {
            storageItems: [
				{ name: '+ 8 Gb', value: 8 },
				{ name: '+ 16 Gb', value: 16 },
				{ name: '+ 32 Gb', value: 32 },
				{ name: '+ 64 Gb', value: 64 }
			],
            resource: {
				limit_allowed_storage: null,
				limit_allowed_users: null,
				description: null,
                functionalities:[]
			},
            functionalities:[
                {id:1,name:'Beneficios',selected:false},
                {id:2,name:'Cursos Presenciales',selected:false},
                {id:3,name:'Cursos Virtuales',selected:false},
                {id:1,name:'Beneficios',selected:false},
                {id:2,name:'Evaluaciones con Inteligencia Artificial',selected:false },
                {id:3,name:'Sesiones live', selected:false},
                {id:4,name:'Reconocimiento',selected:false},
                {id:5,name:'Creación de cursos a tu medida con expertos',selected:false},
                {id:6,name:'Trasforma tus ppts/pdf a video',selected:false},
                {id:7,name:'Assistant manager',selected:false},
            ],
            total_access:false
        }
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        resetSelects() {
            let vue = this
        },
        async confirmModal() {
            let vue = this
            vue.showLoader();
            vue.$http.put('general/workspace-plan', { ...vue.resource })
            .then((res) => {
                vue.hideLoader();
                vue.$emit("onConfirm");
                // vue.showAlert(data.data.msg);
            }).catch(error => {
                if (error && error.errors) vue.errors = error.errors;
            });
            // vue.$emit('onConfirm')
        },
        async loadData() {
            let vue = this
            vue.resource.limit_allowed_storage =  null;
            vue.resource.limit_allowed_users =  null;
            vue.resource.description =  null;
            vue.resource.functionalities = [];
        },
        loadSelects() {
            let vue = this
        },
        setFunctionalities(value){
            let vue = this;
            vue.total_access = value;
            vue.resource.functionalities = vue.total_access ? vue.functionalities : [];
        },
    }
}
</script>
<style>
.container-switch{
    display: flex;
    border-radius: 20px;
    border: 1px solid #5457E7;
    background: #FFF;
    box-shadow: 0px 2px 8px 0px rgba(0, 0, 0, 0.15);
    width: max-content;
}
.switch-active{
    color: white;
    border-radius: 20px;
    border: 1px solid #5457E7;
    background: #5457E7;
    box-shadow: 0px 2px 8px 0px rgba(0, 0, 0, 0.15);
    cursor: pointer;
    transition: all 300ms ease-in-out;
}
.switch-inactive{
    color: #A9B2B9;
    text-align: center;
    font-family: Nunito;
    font-size: 14px;
    font-style: normal;
    font-weight: 500;
    line-height: 18px; 
    cursor: pointer;
    transition: all 300ms ease-in-out;
}
.image-inactive{
    filter: invert(42%) sepia(98%) saturate(0%) hue-rotate(349deg) brightness(111%) contrast(100%);
    /* transition: all 2s ease-in-out; */
}
.image-active{
    filter: hue-rotate(360deg);
}
</style>
