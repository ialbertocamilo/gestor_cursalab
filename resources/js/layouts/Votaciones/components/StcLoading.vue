<template>
	<!-- la altura de tu contenedor -->
	<v-container :style="heightContainer">
    <v-row class="fill-height" align-content="center" justify="center">

        <v-col class="text-subtitle-1 text-center" cols="12" v-text="text"></v-col>

        <v-col cols="6">
          <v-progress-linear color="primary" indeterminate rounded :height="lineHeight"></v-progress-linear>
        </v-col>
      </v-row>
  </v-container>
</template>

<script>
	export default {
		name: 'StcLoading', 
		props: {
			height: { type:Number, default: 400 },
			text: { type:String, default: 'Cargando sección' },
			lineHeight: { type:Number, default: 4 }
		},
		computed:{
			heightContainer() {
				return { height: `${this.height}px`}
			}
		}
	}
</script>