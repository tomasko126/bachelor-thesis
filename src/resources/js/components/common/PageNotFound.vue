<template>
    <main id="unauthorized-page" class="is-flex">
        <b-icon icon="frown" size="is-large"></b-icon>
        <p class="is-size-4">{{ this.$t('common.page_not_found') }}</p>
    </main>
</template>

<script>
    export default {
        name: "PageNotFound"
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
