<template>
    <p class="default-section-label d-flex align-items-center mb-2">
        {{ label }}
        <DefaultInfoTooltip
            v-if="tooltip"
            :text="tooltip"
            bottom
            small
            class="ml-1"
        />
    </p>
</template>
<script>
export default {
    props: {
        label: {
            type: String,
            required: true
        },
        tooltip: String
    }
}
</script>
