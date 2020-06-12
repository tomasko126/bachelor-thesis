<template>
    <section id="edit-roles-modal">
        <header class="modal-card-head">
            <p class="modal-card-title has-text-centered">{{ modalHeader }}</p>
        </header>

        <div id="edit-roles-content">
            <section id="edit-roles-section">
                <b-field :label="this.$t('users.index.roles_modal.roles')"></b-field>
                <div class="field" v-for="role of roles.data" :key="role.id">
                    <b-checkbox v-model="role.enabled"
                                :native-value="role.enabled"
                                :value="role.enabled"
                    >
                        {{ role.name }}
                    </b-checkbox>
                </div>
            </section>

            <section id="edit-roles-buttons">
                <b-button
                    native="submit"
                    id="submit-button"
                    type="is-link"
                    :loading="isSubmitting"
                    @click.prevent="saveUserRoles"
                >
                    {{ this.$t('users.index.roles_modal.save_roles') }}
                </b-button>
            </section>
        </div>
    </section>
</template>

<script>
    export default {
        name: "EditRolesModal",
        props: {
            user: Object,
        },
        data() {
            return {
                roles: {
                    data: [],
                    isLoading: false,
                },
                isSubmitting: false,
            }
        },
        computed: {
            modalHeader() {
                return `${this.$t('users.index.roles_modal.set_roles')} - ${this.user.name}`
            }
        },
        methods: {
            async getUserRoles() {
                this.roles.isLoading = true;

                try {
                    const response = await axios.get(`/api/users/${this.user.id}/roles`);
                    this.roles.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('users.index.roles_modal.roles_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.roles.isLoading = false;
                }
            },
            async saveUserRoles() {
                this.isSubmitting = true;

                try {
                    await axios.put(`/api/users/${this.user.id}/roles`, this.roles.data);
                    this.$buefy.toast.open({ message: this.$t('users.index.roles_modal.set_roles_ok'), type: 'is-success' });

                    // Emit message, so the table can be reloaded
                    this.$emit('submit');

                    // Close modal
                    this.$parent.close();
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('users.index.roles_modal.set_roles_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            }
        },
        async created() {
            await this.getUserRoles();
        }
    }
</script>

<style lang="scss" scoped>
    #edit-roles-modal {
        background: white;

        #edit-roles-content {
            padding: 20px 60px;

            #edit-roles-section {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }

            #submit-button {
                margin-top: 20px;
            }
        }
    }
</style>
