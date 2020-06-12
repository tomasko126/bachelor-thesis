<template>
    <div id="users-table-container" class="container">
        <h1 id="heading" class="has-text-centered is-size-3">{{ this.$t('users.index.users') }}</h1>

        <b-table :data="users.data"
                 :loading="users.isLoading"
                 id="users-table"
        >
            <template slot-scope="props">
                <b-table-column field="name" :label="$t('users.index.name')" sortable>
                    {{ props.row.name }}
                </b-table-column>

                <b-table-column field="email" :label="$t('users.index.email')" sortable>
                    {{ props.row.email }}
                </b-table-column>

                <b-table-column field="roles" :label="$t('users.index.roles')" sortable>
                    {{ rolesFormat(props.row.roles) }}
                </b-table-column>

                <b-table-column :centered="true">
                    <users-table-buttons :user="props.row" @reloadTable="loadUsers"></users-table-buttons>
                </b-table-column>
            </template>
        </b-table>
    </div>
</template>

<script>
    import UsersTableButtons from "./TableButtons";

    export default {
        name: "UsersTable",
        components: {
            UsersTableButtons,
        },
        data() {
            return {
                users: {
                    data: [],
                    isLoading: false,
                }
            }
        },
        methods: {
            async loadUsers() {
                this.users.isLoading = true;

                try {
                    const response = await axios.get('/api/users');
                    this.users.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('users.index.load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.users.isLoading = false;
                }
            },
            getRoles(roles) {
                if (!roles || !roles.length) {
                    return [];
                }

                let allRoles = [];

                for (const role of roles) {
                    allRoles.push(role.name);
                }

                return allRoles;
            },
            rolesFormat(roles) {
                if (!roles || !roles.length) {
                    return '-';
                }

                return this.getRoles(roles).join(', ');
            }
        },
        async created() {
            await this.loadUsers();
        }
    }
</script>
