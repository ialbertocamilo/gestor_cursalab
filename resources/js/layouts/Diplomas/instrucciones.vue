<template>
  <div class="text-center">
    <v-dialog
      v-model="dialog"
      width="500"
    >
      <template v-slot:activator="{ on, attrs }">
        <v-btn
          icon
          color="primary"
          v-bind="attrs"
          v-on="on"
        >
            <v-icon>
              mdi-alert-circle
            </v-icon>
        </v-btn>
      </template>

      <v-card>
        <v-card-title class="text-h5 primary lighten-2">
          Instrucciones
        </v-card-title>
        <v-card-text class="mt-4">
            <ul>
                <li>Personaliza tu diploma ubicando el nombre del curso y de los usuarios, la fecha de emisión y el texto descriptivo.</li>
                <li>El detalle del diploma se mostraría dentro de la imagen que definas como plantilla.</li>
                <li>Puedes usar la herramienta de "Previsualizar" y realizar ediciones a tu progreso.</li>
                <li>Haz clic en Guardar para guardar tu progreso.</li>
            </ul>
        </v-card-text>
        <v-divider></v-divider>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn
            color="primary"
            text
            @click="dialog = false"
          >
            Entendido
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

     <UsuarioFormInfoModal
      width="30vh"
      :ref="modalDiplomaFormInfoOptions.ref"
      :options="modalDiplomaFormInfoOptions"
      @onCancel="closeFormModal(modalDiplomaFormInfoOptions)"
      @onConfirm="confirmByModalInfo()"
            />
  </div>
</template>
<script>
  export default {
    data () {
      return {
        dialog: false,
        
      }
    },
  }
</script>