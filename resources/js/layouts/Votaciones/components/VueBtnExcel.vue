<template>
	<v-btn :color="color" text small
				 :href="uri" target="_blank" >
		<slot></slot>
	</v-btn>
</template>

<script>

	export default {
		name:'vue-btn-excel',
		props:{ 
			uri:{ type:String, require: true },
			color:{ type:String, default: 'primary' },
		}
	}
</script>