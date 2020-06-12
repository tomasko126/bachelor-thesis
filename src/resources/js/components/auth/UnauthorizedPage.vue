<template>
    <main id="unauthorized-page" class="is-flex">
        <b-icon icon="exclamation-circle" size="is-large"></b-icon>
        <p class="is-size-4">{{ this.$t('common.no_permission_page') }}</p>
    </main>
</template>

<script>
    export default {
        name: "UnauthorizedPage"
    }
</script>

<style lang="scss" scoped>
    #unauthorized-page {
        height: 100%;
        width: 100%;
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }
</style>
