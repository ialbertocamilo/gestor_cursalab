<template>
    <div>
        <DefaultDialog :options="options" :width="width" @onCancel="closeModal" @onConfirm="confirmModal">
            <template v-slot:content>
                <div>
                    <iframe allowfullscreen="" frameborder="0" height="350px" width="100%" 
                        :src="currentMedia.url">
                    </iframe>
                </div>
                <v-list>
                    <v-list-group
                        v-for="topic in topics"
                        :key="topic.id"
                        no-action
                    >
                        <template v-slot:activator>
                            <v-list-item-content>
                                <v-list-item-title>{{ topic.name }}</v-list-item-title>
                            </v-list-item-content>
                        </template>

                        <v-list-item
                            v-for="media in topic.medias"
                            :key="media.id"
                        >
                        <v-list-item-content @click="changeMedia(media)">
                            <v-list-item-title>{{ media.name }}</v-list-item-title>
                        </v-list-item-content>
                        </v-list-item>
                    </v-list-group>
                </v-list>
            </template>
        </DefaultDialog>
    </div>
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
            topics:[],
            currentMedia:''
        };
    },
    methods: {
        closeModal() {
            let vue = this
            vue.$emit('onCancel')
        },
        resetValidation() {
            let vue = this
        },
        async confirmModal() {

            vue.$emit('onConfirm')

        },
        resetSelects() {
            let vue = this
        },
        async loadData(course_id) {
            let vue = this
            console.log(course_id);
            let url = `/cursos/${course_id}/medias`
            vue.showLoader();
            await vue.$http.get(url).then(({ data }) => {
                        vue.topics = data.data.topics;
                        console.log(vue.topics);
                        vue.hideLoader();
                    })
        },
        async loadSelects() {
            let vue = this;
        },
        changeMedia(media) {
            let vue = this;
            vue.currentMedia = media; 
            console.log('Cambiar media:', media);
        }
    }
}
</script>
