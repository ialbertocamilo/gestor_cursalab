<template>
    <div class="w-100">
        <div class="d-flex justify-content-center mb-2" v-if="title">
            <label class="default-rich-text-title">{{ title }}</label>
        </div>

        <fieldset class="editor">
            <legend v-if="label">{{ label }}
                <RequiredFieldSymbol v-if="showRequired"/>
            </legend>
            <editor
                api-key="6i5h0y3ol5ztpk0hvjegnzrbq0hytc360b405888q1tu0r85"
                v-model="localText"
                :init="{
                    content_style: 'img { vertical-align: middle; }; p {font-family: Roboto-Regular }',
                    height: height,
                    menubar: false,
                    language: 'es',
                    force_br_newlines: true,
                    force_p_newlines: false,
                    forced_root_block: '',
                    plugins: ['lists anchor', 'code', 'paste','link','image', 'emoticons'],
                    toolbar:
                        'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist | code | link | image | emoticons',
                    images_file_types: 'jpg,svg,webp,gif'
                }"
                @input="updateValue"
            />
        </fieldset>
        <div v-if="showValidateRequired"
             class="v-messages__message mt-2"
             v-text="'Campo requerido'"
             style="color: #FF5252; font-size: 12px"
        />
        <div v-if="showAlertLength" 
             class="v-messages__message mt-2"
             v-text="`El campo debe tener menos de ${maxLength} caracteres`"
             style="color: #FF5252; font-size: 12px"
        />
    </div>
</template>

<script>
import Editor from "@tinymce/tinymce-vue";

export default {
    components: {Editor,},
    props: {
        value: {
            required: true
        },
        placeholder: {
            type: String
        },
        label: {
            type: String
        },
        title: {
            type: String
        },
        height: {
            type: Number,
            default: 170
        },
        showRequired: {
            type: Boolean,
            default: false,
        },
        showValidateRequired: {
            type: Boolean,
            default: false,
        },
        maxLength: { 
            type: Number, 
            default: 200 
        }
    },
    data() {
        return {
            localText: null,
            showAlertLength: false
        }
    },
    created() {
        if (this.value) {
            this.localText = this.value // set initial value
        }
        
        document.addEventListener('focusin', (e) => {
          if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
            e.stopImmediatePropagation();
          }
        });
    },
    watch: {
        value(val) {
            // console.log("cambio desde el parent :: ", val)
            if (!val)
                this.$emit('setVaidateRequired', true)

            this.localText = val // watch change from parent component
        }

    },
    methods: {
        emitLengthState(st) {
            let vue = this

            vue.showAlertLength = st;
            vue.$emit('stateLength', st);
        },
        updateValue(value) {
            let vue = this
            
            if (value !== ""){
                vue.$emit('setVaidateRequired', false)
            }

            // check length 
            if(value) {
                if(value.length > vue.maxLength) {
                    vue.emitLengthState(true)
                } else {
                    vue.emitLengthState(false)
                    vue.$emit('input', value || null)
                }
            }else {
                vue.emitLengthState(false);
                vue.$emit('input', value || null)
            }


        },
        onClear() {
            let vue = this
            vue.localText = ''
            vue.updateValue()
        },
    }
}
</script>
