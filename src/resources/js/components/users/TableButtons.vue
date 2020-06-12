<template>
    <div>
        <div class="users-table-buttons">
            <b-button type="is-warning"
                      icon-left="edit"
                      @click="onEdit"
            >
                {{ this.$t('users.index.edit_roles') }}
            </b-button>
            <b-button type="is-danger"
                      icon-left="trash-alt"
                      @click="onDelete"
            >
            </b-button>
        </div>

        <b-modal :active.sync="isModalActive"
                 trap-focus
                 aria-role="dialog"
                 aria-modal
                 :width="450"
        >
            <edit-roles-modal :user="user" @submit="reloadTable"></edit-roles-modal>
        </b-modal>
    </div>
</template>

<script>
    import EditRolesModal from "./EditRolesModal";

    export default {
        name: "UsersTableButtons",
        props: {
            user: Object,
        },
        components: {
            EditRolesModal
        },
        data() {
            return {
                isModalActive: false,
            }
        },
        methods: {
            onEdit() {
                this.isModalActive = true;
            },
            onDelete() {
                this.$buefy.dialog.confirm({
                    title: this.$t('users.index.deleting_user'),
                    message: this.$t('users.index.delete_user_message'),
                    confirmText: this.$t('users.index.delete_user'),
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: async () => {
                        this.deleteUser();
                    }
                });
            },
            async deleteUser() {
                const url = `/api/users/${this.user.id}`;

                try {
                    await axios.delete(url);
                    this.$buefy.toast.open({ message: this.$t('users.index.user_deleted'), type: 'is-success' });

                    // Send message to the parent, so the table gets reloaded
                    this.$emit('reloadTable');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('users.index.user_delete_fail'), type: 'is-danger' });
                }
            },
            reloadTable() {
                this.$emit('reloadTable');
            }
        }
    }
</script>
