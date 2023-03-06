<template>
    <DefaultDialog
        :options="options"
        :width="width"
        @onCancel="closeModal"
        @onConfirm="confirmModal"

        :show-card-actions="false"
    >
        <template v-slot:content>

            <v-card
                class="mx-auto"
                flat
            >
                <p>
                    Use el control deslizante a continuación para extender su contraseña y aumentar la seguridad.
                    Longitud de la contraseña (8-32).
                </p>

                <v-card-text>
                  <v-row
                    class="mb-4---"
                    justify="space-between"
                  >
                    <v-col cols="2" class="text-left">
                        <v-btn 
                            depressed
                            fab
                            small
                            @click="generatePassword"
                        >

                            <v-icon>mdi-refresh</v-icon>
                        </v-btn>

                      <!-- <span
                        class="text-h5 font-weight-light"
                        v-text="generator.size"
                      ></span> -->

                      <!-- <span class="subheading font-weight-light mr-1"># carac.</span> -->
                      <!-- <v-fade-transition> -->
                        <!-- <v-avatar
                          v-if="generator.isCopying"
                          :color="color"
                          :style="{
                            animationDuration: animationDuration
                          }"
                          class="mb-1 v-avatar--metronome"
                          size="12"
                        >¡Copiado!</v-avatar> -->
                      <!-- </v-fade-transition> -->
                    </v-col>
                    <v-col cols="8" class="text-center" >
                        <span
                        class="text-h6 font-weight-light"
                        v-text="generator.password"
                        style="word-break: break-all;"
                      ></span>
                    </v-col>
                    <v-col cols="2" class="text-right">
                        <v-btn
                            :color="color"
                            dark
                            depressed
                            fab
                            @click="copyPassword"
                            small
                          >
                            <v-icon small>
                                {{ generator.isCopying ? 'mdi-check' : 'mdi-content-copy' }}
                            </v-icon>
                        </v-btn>
                    </v-col>

                  </v-row>

                  <v-slider
                    v-model="generator.size"
                    :color="color"
                    track-color="grey"
                    always-dirty
                    :min="generator.min"
                    :max="generator.max"
                     :hint="generator.size + ' caracteres'"
                     persistent-hint
                     class="slider-password"
                  >
                    <template v-slot:prepend>
                      <v-icon
                        :color="color"
                        @click="decrement"
                        :disabled="generator.size == generator.min"
                      >
                        mdi-minus
                      </v-icon>
                    </template>

                    <template v-slot:append>
                      <v-icon
                        :color="color"
                        @click="increment"
                        :disabled="generator.size == generator.max"
                      >
                        mdi-plus
                      </v-icon>
                    </template>
                  </v-slider>


                  <!-- <hr class="mt-3"> -->

                    <v-row class="mt-8">

                        <v-col
                            cols="3"
                            v-for="(element, idx) in generator.characters" :key="idx+'-characters'"
                        >

                            <v-checkbox
                                v-model="element.active"
                                :label="element.name"
                                color="blue darken-3"
                                hide-details
                                :disabled="element.disabled"
                                class="password-checkbox"
                            ></v-checkbox>

                        </v-col>
                          
                    </v-row>

                </v-card-text>
            </v-card>

       
        </template>
    </DefaultDialog>
</template>

<script>

export default {
    props: {
        options: {
            type: Object,
            required: true
        },
        width: String
    },
    data() {
        return {
            errors: [],
            generator: {
                min: 8,
                max: 32,
                size: 20,
                password: null,
                isCopying: false,

                characters: {
                    lowercase: {
                        name: 'Minúsculas',
                        active: true,
                        disabled: true,
                        values: 'abcdefghijklmnopqrstuvwxyz'
                    },
                    uppercase: {
                        name: 'Mayúsculas',
                        active: true,
                        disabled: true,
                        values: 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
                    },
                    numbers: {
                        name: 'Números',
                        active: true,
                        disabled: true,
                        values: '0123456789'
                    },
                    symbols: {
                        name: 'Símbolos',
                        active: true,
                        disabled: true,
                        values: '?!@#$%^&*()_-'
                    },
                }
            }
            
        }
    },
    computed: {
        color () {
            if (this.generator.size < 15) return 'blue darken-1'
            if (this.generator.size < 25) return 'blue darken-2'
            return 'blue darken-3'
        },

        animationDuration () {
            return `${60 / this.generator.size}s`
        },

        checkboxEvent() {

            let count = 0

            Object.entries(this.generator.characters).forEach(([key, element]) => {

                if (element.active == true) count++;
            });

            return count
        }
    },
    watch: {
        'generator.size'(newValue) {
          this.generatePassword()
        },
        'options.open'(newValue) {
            if (newValue) {
                this.generatePassword()
            } else {
                this.generator.password = null
            }
        },
        'checkboxEvent'(newValue) {
            this.generatePassword()
        }
    },
    mounted() {
        let vue = this

        // this.generatePassword()
    },
    methods: {

        generatePassword() {

            let chars = "";
            let password = "";

            Object.entries(this.generator.characters).forEach(([key, element]) => {

                if (element.active == true) {

                    chars += element.values;
                    password += this.getRandomCharacter(element.values);
                }
            });
            
            if (chars.length == 0) {

                return this.generator.password = 'MISSING DATA'
            }

            let passwordLength = this.generator.size - password.length;

            for (let i = 0; i < passwordLength; i++) {
               password += this.getRandomCharacter(chars);
            }

            password = this.shuffle(password)

            this.generator.password = password;
        },

        getRandomCharacter(chars) {
           let randomNumber = Math.floor(Math.random() * chars.length);

           return chars.substring(randomNumber, randomNumber + 1);
        },

        copyPassword() {

            if (this.generator.isCopying) return false;

            // navigator.clipboard.writeText(this.generator.password)

            navigator.clipboard
              .writeText(this.generator.password)
              .then(() => {
                this.toggle();
              })
              .catch(() => {
                console.log("copyPassword: something went wrong");
              });
        },

        decrement () {
            this.generator.size--
        },

        increment () {
            this.generator.size++
        },

        toggle () {
            this.generator.isCopying = !this.generator.isCopying

            setTimeout(() => { this.generator.isCopying = !this.generator.isCopying }, 750);
        },

        shuffle(string) {
            var arr = string.split('');

            arr.sort(function() {
                return 0.5 - Math.random();
            });  

            return arr.join('');
        },

        async loadData(resource) {
        
        },

        resetSelects() {
           
        },

        loadSelects() {

        },

        closeModal() {
            let vue = this;
            vue.$emit('onCancel');
        },
        
        resetValidation() {
            let vue = this
            vue.resetFormValidation('UsuarioForm');
            vue.errors = [];
        },

        async confirmModal() {
            
        },
    }
}
</script>
