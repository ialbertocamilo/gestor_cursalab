<template>
	<v-dialog persistent v-model="dialog" width="500">
    <v-card>
      <v-card-title class="d-flex justify-content-between primary text-white">
      	<span>¡Ups! Campaña incompleta.</span>
      	<v-btn icon @click="$emit('close', false)">
    		  <v-icon class="text-white"> mdi-close </v-icon>
      	</v-btn>
      </v-card-title>
      <v-card-text class="pt-5">
      	<p class="mb-0">
					Debes completar todos los campos de la campaña para activarla.
				</p>
      </v-card-text>
      <v-card-actions class="d-flex justify-content-center pb-4">
        <v-btn color="primary" @click="$emit('close', false)"> Entendido </v-btn>
      </v-card-actions>

    </v-card>

  </v-dialog>
</template>

<script>

	export default {
		name: 'ModalVotAlert',
		props: {
			open:{ type:Boolean, require:true, },
			index:{ type:Number, require:true }
		},
		data() {
			return {
				dialog: false,
				loader: false,
			}
		},
		watch:{
			open:{
				handler(){
					this.dialog = !this.dialog;
				}
			}
		}
	}
</script>