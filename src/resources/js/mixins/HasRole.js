// Check, whether user has given role against the user loaded in PermissionsMixin
export default {
    methods: {
        hasRole(role) {
            return this.user?.allRoles?.includes(role);
        },
    }
}
