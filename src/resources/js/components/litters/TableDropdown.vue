<template>
    <b-dropdown aria-role="list" position="is-bottom-left">
        <button class="button" slot="trigger">
            <b-icon icon="ellipsis-h"></b-icon>
        </button>
        <b-dropdown-item aria-role="listitem" @click="onEdit" :disabled="!canModifyLitter">
            <b-icon icon="edit"></b-icon>
            <span>{{$t('litters.index.edit')}}</span>
        </b-dropdown-item>
        <b-dropdown-item aria-role="listitem" @click="onDelete" :disabled="!canModifyLitter">
            <b-icon icon="trash"></b-icon>
            <span>{{$t('litters.index.delete')}}</span>
        </b-dropdown-item>
    </b-dropdown>
</template>

<script>
    import HasRole from "../../mixins/HasRole";
    import HasPermission from "../../mixins/HasPermission";

    export default {
        name: "LittersTableDropdown",
        props: {
            litter: Object,
            user: Object,
        },
        mixins: [HasRole, HasPermission],
        computed: {
            canModifyLitter() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.litter?.latest_approval_request?.state === 'Approved') {
                    return this.hasPermission('edit approved litters');
                }

                if (this.litter?.creator_id === this.user?.id || this.litter?.owner_id === this.user?.id) {
                    return true;
                }

                return this.hasPermission('edit foreign litters');
            }
        },
        methods: {
            onEdit() {
                this.$router.push({ name: 'litter.edit', params: { litter: this.litter.id }});
            },
            onDelete() {
                this.$buefy.dialog.confirm({
                    title: this.$t('litters.index.deleting_litter'),
                    message: this.$t('litters.index.delete_litter_message'),
                    confirmText: this.$t('litters.index.delete_litter'),
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: async () => {
                        this.deleteLitter();
                    }
                });
            },
            async deleteLitter() {
                const url = `/api/litters/${this.litter.id}`;

                try {
                    await axios.delete(url);
                    this.$buefy.toast.open({ message: this.$t('litters.index.litter_deleted'), type: 'is-success' });

                    // Emit event, so we can reload the parent table
                    this.$emit('reloadTable');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litters.index.litter_delete_fail'), type: 'is-danger' });
                    throw e;
                }
            },
        }
    }
</script>
