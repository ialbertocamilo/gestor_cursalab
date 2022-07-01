<template>
	<v-dialog :max-width="width" v-model="value" @click:outside="closeModal">
		<v-card>
			<v-card-title class="headline">
				Asignar Checklist a Curso
				<v-spacer></v-spacer>
				<v-btn icon :ripple="false" @click="closeModal"><v-icon>mdi-close</v-icon></v-btn>
			</v-card-title>
			<v-card-text style="height: 650px">
                <v-row>
                    <v-col cols="12" md="4" lg="4">
                        <v-text-field
                            dense
                            outlined
                            hide-details
                            label="Checklist"
                        >
                        </v-text-field>
                    </v-col>
                    <v-col cols="12" md="4" lg="4">
                        <v-text-field
                            dense
                            outlined
                            hide-details
                            label="Checklist"
                        >
                        </v-text-field>
                    </v-col>
                </v-row>
			</v-card-text>
		</v-card>
	</v-dialog>
</template>

<script>
  import XLSX from "xlsx";

  export default {
  	props: ["value", "width"],

  	data() {
  		return {

  		};
  	},
  	methods: {
        closeModal() {
            let vue = this;
            vue.actividades_expanded = [];
            vue.$emit("onClose");
        },
        confirm() {
            let vue = this;
            vue.$emit("onConfirm");
        },
        cancel() {
            let vue = this;
            vue.$emit("onCancel");
        },
    }
  };
</script>

<style>
  .txt-white-bold {
  	color: white !important;
  	font-weight: bold !important;
  }
  .v-input__icon {
  	padding-bottom: 12px;
  }
  .v-icon.v-icon.v-icon--link {
  	color: #1976d2;
  }
  .v-icon.v-icon {
  	font-size: 31px !important;
  }
  .v-input--selection-controls {
  	margin: unset !important;
  	padding-top: unset !important;
  }
</style>
