<template>
    <div v-if="shouldShowMessage" id="model-deleted" class="is-flex column is-offset-1 has-text-centered">
        <b-icon icon="exclamation-circle" size="is-large"></b-icon>
        <span class="is-size-4">{{ deleteMessage }}</span>
    </div>
</template>

<script>
    import HasRole from "../../mixins/HasRole";

    export default {
        name: "ModelDeletedMessage",
        props: {
            model: Object,
            type: String,
            user: Object,
        },
        mixins: [HasRole],
        computed: {
            deleteMessage() {
                if (this.type === 'animal') {
                    return this.$t('common.animal_is_deleted_warning');
                }

                return this.$t('common.litter_is_deleted_warning');
            },
            isDeleted() {
                return this.model?.deleted_at;
            },
            shouldShowMessage() {
                return this.isDeleted && !this.hasRole('admin');
            }
        }
    }
</script>

<style lang="scss" scoped>
    #model-deleted {
        align-items: center;
        justify-content: space-evenly;
    }
</style>
