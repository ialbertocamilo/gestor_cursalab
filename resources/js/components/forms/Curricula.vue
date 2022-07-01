<template>
    <div class="">
         <!-- {{ curricula }} -->
        <table class="table table-hover ">
            <thead class="bg-dark">
            <tr>
                <th>Carrera</th>
                <th>Ciclo</th>
                <th>Grupo</th>
                <!-- <th>¿Habilitar?</th> -->
                <th>&nbsp;</th>
            </tr>
            </thead>
            <tbody>
                <tr v-for="bloque in bloqueFiltros" :key="bloque.id" >
                    <td>
                        <v-select
                            :name="'carrera' + bloque.pos"
                            class="sel_carreras"
                            attach
                            solo
                            chips
                            required  
                            :value="bloque.carrera_value"  
                            :items="Carreras"
                            @change="onChangeCarrera($event, bloque.pos)"
                        ></v-select>
                    </td>
                    <td>
                        <v-select
                            :name="'ciclo' + bloque.pos"
                            class="sel_ciclos"
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            required
                            :value="bloque.ciclo_value"
                            :items="bloque.ciclo_options"
                        ></v-select>
                    </td>
                    <td>
                        <v-select
                            class="sel_ciclos"
                            attach
                            solo
                            chips
                            clearable
                            multiple
                            required
                            item-text="nombre"
                            item-key="id"
                            :items="grupos"
                        ></v-select>
                    </td>
                    <td>
                        <i class="fas fa-times-circle fz-lg color-red mt-3 pointer" @click="deleteBloque(bloque.pos)"></i>
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        <button class="btn btn-md bg-green" @click="addBloque"><i class="fa fa-plus"></i> Agregar </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
<style>
.table tbody tr td{
    padding: 10px;
}
  .v-text-field.v-text-field--solo .v-input__control {
    min-height: auto;
    padding: 0;
}
.v-text-field.v-text-field--enclosed .v-text-field__details {
    margin-bottom: 0;
}
.v-text-field__details {
    min-height: auto;
}
.v-messages {
    min-height: auto;
}
</style>

<script>
export default {
    props: ["modulo", "curricula"],
    data() {
        return {
            grupos: [
                {nombre:'GRUPO 01', id:1 }, 
                {nombre:'GRUPO 02', id:2 }, 
            ],
            counter: 0,
            Carreras: [],
            bloqueFiltros: [{
                id:'b0',
                pos: 0,
                carrera_value: '',
                ciclo_options: [],
                ciclo_value: []
            }]
        };
    },
    methods: {
        cargarCarreras() {
            axios
                .post("/carreras", {
                    config_id: this.modulo
                })
                .then(res => {
                    console.log("Carreras ", res);
                    res.data.forEach(el => {
                        let NewJson = {};
                        NewJson.text = el.nombre;
                        NewJson.value = el.id;
                        this.Carreras.push(NewJson);
                    });
                })
                .catch(err => {
                    console.log(err);
                });
        },
        cargarCiclos(carrera_id, bloque_pos) {
            axios
                .post("/ciclos", {
                    carrera_id: carrera_id
                })
                .then(res => {
                    console.log("Ciclos ", res);
                    this.bloqueFiltros[bloque_pos].ciclo_options = [];
                    res.data.forEach(el => {
                        let NewJson = {};
                        NewJson.text = el.nombre;
                        NewJson.value = el.id;
                        this.bloqueFiltros[bloque_pos].ciclo_options.push(NewJson);
                    });
                })
                .catch(err => {
                    console.log(err);
                });
        },
        addBloque() {
            event.preventDefault();
            let NewIndice = this.bloqueFiltros.length; // El NewIndice sería igual a la cantidad de bloques (1 más que el Array bloqueFiltros empieza en 0) 

            this.bloqueFiltros.push({
                id:`b${NewIndice}`,
                pos: NewIndice,
                carrera_value: '',
                ciclo_options: [],
                ciclo_value: []
            });
            
        },
        onChangeCarrera: function(event, bloque_pos) {
            console.log("change carrera:", event);
            this.cargarCiclos(event, bloque_pos);
        },
        deleteBloque: function(bloque_pos) {
            this.bloqueFiltros.splice(bloque_pos, 1);
        }
    },
    created() {
        this.cargarCarreras();
        let Curri = JSON.parse(this.curricula);

        if (Curri.length > 0) {
            this.bloqueFiltros = [];
            Curri.forEach(el => {

                this.bloqueFiltros.push({
                    id:`b${this.counter}`,
                    pos: this.counter,
                    carrera_value: el.carrera_id,
                    ciclo_options: [],
                    ciclo_value: []
                });

                el.ciclos.forEach(element => {
                    this.bloqueFiltros[this.counter].ciclo_value.push(element);    
                });                
                
                this.cargarCiclos(el.carrera_id, this.counter);

                ++this.counter;
            });

        }
    }
};
</script>
