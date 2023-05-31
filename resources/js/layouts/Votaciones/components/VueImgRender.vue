<template>
	<v-avatar color="primary" :class="`my-1 ${vot_postulates.avatar_img}`">
    <v-icon v-if="!currentImage" dark>
      mdi-account-circle
    </v-icon>
		<img v-else :src="img" :alt="img">
	</v-avatar>
</template>

<script>
	
	export default {
		name:'vue-img-render',
		props:{
			img:{ type: String },
		},
		computed:{
			currentImage() {
				const vm = this;
				return (typeof vm.img === 'string');
			}
		}		
	}

</script>

<style module="vot_postulates">
	.avatar_img {
		height: 40px !important;
  	min-width: 40px !important;
  	width: 40px !important;
	}
</style>