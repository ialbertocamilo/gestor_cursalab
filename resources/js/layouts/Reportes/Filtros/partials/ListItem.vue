<template>
	<v-list-item >
		<v-list-item-content>
			<v-list-item-title>{{ titulo }}</v-list-item-title>
			<v-list-item-subtitle>{{ subtitulo }}</v-list-item-subtitle>
		</v-list-item-content>
	</v-list-item>
</template>

<script>
export default {
	props: {
		titulo: null,
		subtitulo: null,
		// line: null
	}
};
</script>

<style>
</style>