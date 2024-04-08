<template>
    <v-btn  icon :ripple="false"
        v-click-outside="{ handler: onClickOutside }">
        <p v-if="emojiSelected"  @click="showEmojiPicker = !showEmojiPicker" class="m-0 p-0 pt-2" style="font-size: 18px;">{{emojiSelected}}</p>
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
        data(){
            return{
                showEmojiPicker:false,
                emojiIndex: emojiIndex,
                emojiSelected:''
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
                console.log(emoji);      
                this.emojiSelected = emoji.native;
                this.showEmojiPicker = false;
            },
        }
    }
</script>