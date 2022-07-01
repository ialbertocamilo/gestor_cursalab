<template>
  <div class="container-fluid">
    <b-card no-body>
      <Index :Encuestas="encuestas"/>
    </b-card>
  </div>
</template>
<script>
import Index from "../components/Encuestas/Index";
export default {
  components: {
    Index,
  },
  props: {
    encuestas: {
      // type: String,
      default: () => window.data.encuestas,
    },
  },
};
</script>

<style></style>
