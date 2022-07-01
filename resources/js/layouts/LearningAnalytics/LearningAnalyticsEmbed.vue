<template>
    <section class="section-list">
        <v-card flat class="elevation-0 mb-4">
            <v-card-title>
                Learning Analytics
                <v-spacer/>
            </v-card-title>
        </v-card>

        <v-card flat class="elevation-0 mb-2">
            <iframe width="100%" height="600" style="width:100%!important;" :src="this.pbi_url" frameborder="0" allowFullScreen="true"></iframe>
        </v-card>
    </section>
</template>

<script>
export default {
  props: ['pbi_url'],
  mounted(){
      console.log('pbi',this.pbi_url)
  }
};
</script>
