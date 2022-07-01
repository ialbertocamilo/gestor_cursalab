<template>
    <p class="default-form-label d-flex align-items-center mb-2">
        {{ label }}<RequiredFieldSymbol v-if="showRequired"/>
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
        tooltip: String,
        showRequired: {
            type: Boolean,
            default: false,
        },
    }
}
</script>
