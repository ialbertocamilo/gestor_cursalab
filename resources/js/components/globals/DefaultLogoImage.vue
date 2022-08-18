<template>
    <v-img
        :max-height="maxHeight"
        :max-width="maxWidth"
        :src="bucketBaseUrl + '/' + image"
    >
        <template v-slot:placeholder>
            <v-row
                class="fill-height ma-0"
                align="center"
                justify="center"
            >
                <v-progress-circular
                    indeterminate
                    color="grey lighten-5"
                ></v-progress-circular>
            </v-row>
        </template>
    </v-img>
</template>


<script>
export default {
    props: {
        image: {
            type: String,
            required: true
        },
        maxWidth: {type: String|Number, default: 50},
        maxHeight: {type: String|Number, default: 50},
    }
    ,
    data() {
        return {
            bucketBaseUrl : ''
        }
    }
    ,
    mounted() {

        // Initialize bucket URL

        this.bucketBaseUrl = this.getBucketBaseUrl();
    },
}
</script>
