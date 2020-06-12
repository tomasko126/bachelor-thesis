// Check, whether user has given permission against the user loaded in PermissionsMixin
export default {
    methods: {
        hasPermission(permission) {
            return this.user?.allPermissions?.includes(permission);
        }
    }
}
