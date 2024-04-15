<template>
    <v-btn  icon :ripple="false"
        v-click-outside="{ handler: onClickOutside }">
        <p v-if="localText"  @click="showEmojiPicker = !showEmojiPicker" class="m-0 p-0 pt-2" style="font-size: 18px;">{{localText}}</p>
        <v-icon  v-else class="emoji-icon-button" @click="showEmojiPicker = !showEmojiPicker" >mdi mdi-emoticon</v-icon>
        <Picker v-show="showEmojiPicker" :data="emojiIndex" set="google" @select="showEmoji" :show-preview="false"  :show-skin-tones="false" style="position: absolute; z-index: 100; top: 20px; left: 10px;"  :useButton="false" :native="false" />
    </v-btn>
</template>
<script>
    import data from "emoji-mart-vue-fast/data/all.json";
    import "emoji-mart-vue-fast/css/emoji-mart.css";
    import { Picker, EmojiIndex } from "emoji-mart-vue-fast";
    let emojiIndex = new EmojiIndex(data);
    export default {
        components:{Picker},
        props: {
            value: {
                required: true
            },
        },
        data(){
            return{
                showEmojiPicker:false,
                emojiIndex: emojiIndex,
                localText:''
            }
        },
        created() {
            if (this.value) {
                this.localText = this.value // set initial value
            }
        },
        watch: {
            value(val) {
                this.localText = val // watch change from parent component
            }
        },
        methods:{
            onClickOutside () {
                this.showEmojiPicker = false;
            },
            openEmojiPicker(){
                this.showEmojiPicker = true;
            },
            showEmoji(emoji) {
                let vue = this;
                this.localText = emoji.native;
                this.showEmojiPicker = false;
                vue.$emit('input', emoji.native || null)
            },
        }
    }
</script>