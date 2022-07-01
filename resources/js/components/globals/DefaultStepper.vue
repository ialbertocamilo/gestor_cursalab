<template>
    <section class="p-0">
        <v-stepper v-model="originalModelStepper" class="elevation-0">
                    <!-- :complete="originalModelStepper < n" -->
            <v-stepper-header class="elevation-0">
                <template v-for="(label,index) in stepsLabel">
                <v-stepper-step
                    :key="`${index+1}-step`"
                    :complete="originalModelStepper > index+1"
                    :step="index+1"
                >
                    {{label}}
                </v-stepper-step>
                <v-divider
                    v-if="index+1 !== stepsLabel.length"
                    :key="index+1"
                ></v-divider>
                </template>
            </v-stepper-header>
            <v-stepper-items>
                <v-stepper-content
                    v-for="(label,index) in stepsLabel"
                    :key="`${index+1}-content`"
                    :step="index+1"
                    class="pb-0"
                >
                  <slot :name="`content-step-${index+1}`" />
                </v-stepper-content>
            </v-stepper-items>
        </v-stepper>
    </section>
</template>
<script>
export default {
    props: {
        stepsLabel:{
            type: Array,
            required: true
        },
        modelStepper:{
            type: Number,
            required: true,
        }
    },
    data () {
      return {
        originalModelStepper: this.modelStepper,
      }
    },
    watch: {
      steps (val) {
        if (this.originalModelStepper > val) {
          this.originalModelStepper = val
        }
      },
      modelStepper (newValue) {
        this.originalModelStepper = newValue // Update when the prop changes
      }
    },
    // methods: {
    //   // nextStep (n) {
    //   //   if (n === this.stepsLabel) {
    //   //     this.originalModelStepper = 1
    //   //   } else {
    //   //     this.originalModelStepper = n + 1
    //   //   }
    //   // },
    //   closeModal() {
    //     let vue = this
    //     vue.$emit('onCancel')
    //   },
    // },
}
</script>
