<template>
    <aside id="right-panel" class="column is-2">
        <div id="action-buttons" class="is-flex">
            <router-link :to='editLitterUrl'>
                <b-tooltip :label="this.$t('common.no_permission')" type="is-danger" :active="!canModifyLitter">
                    <b-button type="is-warning" icon-left="edit" :loading="isLoading" :disabled="isButtonDisabled">
                        {{ this.$t('litter.index.edit') }}
                    </b-button>
                </b-tooltip>
            </router-link>
            <b-tooltip v-if="!isDeleted" :label="this.$t('common.no_permission')" type="is-danger" :active="!canModifyLitter">
                <b-button type="is-danger" icon-left="trash" :loading="isLoading" @click="onDelete" :disabled="isButtonDisabled">
                    {{ this.$t('litter.index.delete') }}
                </b-button>
            </b-tooltip>
            <b-tooltip v-else :label="this.$t('common.no_permission')" type="is-danger" :active="!canModifyLitter">
                <b-button type="is-link" icon-left="trash-restore-alt" :loading="isLoading" :disabled="isButtonDisabled" @click="restoreLitter">
                    {{ this.$t('animal.index.restore') }}
                </b-button>
            </b-tooltip>
        </div>
    </aside>
</template>

<script>
    import HasRole from "../../../mixins/HasRole";
    import HasPermission from "../../../mixins/HasPermission";

    export default {
        name: "LitterRightPanel",
        props: {
            isLoading: Boolean,
            litterId: Number,
            litter: Object,
            user: Object,
        },
        mixins: [HasRole, HasPermission],
        computed: {
            editLitterUrl() {
                if (this.canModifyLitter) {
                    return { name: 'litter.edit', params: { litter: this.litterId } };
                }

                return { name: 'litter', params: { litter: this.litterId } };
            },
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
            },
            isDeleted() {
                return this.litter?.deleted_at;
            },
            isButtonDisabled() {
                return !this.canModifyLitter || this.isLoading;
            }
        },
        methods: {
            onDelete() {
                this.$buefy.dialog.confirm({
                    title: this.$t('litter.index.deleting_litter'),
                    message: this.$t('litter.index.delete_litter_message'),
                    confirmText: this.$t('litter.index.delete_litter'),
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: async () => {
                        this.deleteLitter();
                    }
                });
            },
            async deleteLitter() {
                const url = `/api/litters/${this.litterId}`;

                try {
                    await axios.delete(url);
                    this.$buefy.toast.open({ message: this.$t('litter.index.litter_deleted'), type: 'is-success' });
                    setTimeout(() => {
                        this.$router.push({ name: 'litters'})
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litter.index.litter_delete_fail'), type: 'is-danger' });
                }
            },
            async restoreLitter() {
                const url = `/api/litters/${this.litterId}/restore`;

                try {
                    await axios.put(url);
                    this.$buefy.toast.open({ message: this.$t('litter.index.litter_restored'), type: 'is-success' });
                    setTimeout(() => {
                        this.$router.go();
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litter.index.litter_restore_fail'), type: 'is-danger' });
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    #right-panel {
        margin-top: 40px;

        #action-buttons {
            justify-content: space-between;
        }
    }

    @media screen and (max-width: 768px) {
        #right-panel {
            margin-top: 0;
        }
    }
</style>
