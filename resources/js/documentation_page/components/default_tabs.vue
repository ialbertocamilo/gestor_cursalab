<template>
    <div>
        <v-tabs
            v-model="tab"
            background-color="transparent"
            color="basil"
            left
        >
            <v-tab
                v-for="item in tabs"
                :key="item"
            >
                {{ item }}
            </v-tab>
        </v-tabs>
        <v-tabs-items v-model="tab">
            <v-tab-item
                v-for="(item,index) in content_tabs"
                :key="index"
                eager
            >
                <v-card flat style="height: 800px;overflow-y: auto;">
                    <v-card-text >
                        <pre class="line-numbers"><code :class="content_tabs[index].type" v-text="content_tabs[index].code"></code></pre>
                    </v-card-text>
                </v-card>
            </v-tab-item>
        </v-tabs-items>
    </div>
</template>
<script>
import Prism from "prismjs/prism";

export default{
    props: {
        tabs:Array,
        content_tabs:Array
    },
    data () {
        return {
            tab: 0,
        }
    },
    mounted () {
        Prism.highlightAll();
    },
}
</script>
<style scoped>
.v-tab{
    font-size:0.6rem !important;
}
.v-tabs-bar{
    height:auto !important;
}
.theme--light.v-application code{
    background-color:#f5f2f0 !important;
}

.line-numbers .line-numbers-rows{
    left:-16em;
}

</style>