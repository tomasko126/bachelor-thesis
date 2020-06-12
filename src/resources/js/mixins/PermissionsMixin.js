// Permission mixin used to load user's object with roles and permissions
export default {
    data() {
        return {
            user: {},
        }
    },
    methods: {
        async getRolesAndPermissions() {
            const url = `/api/users/getUserWithRolesAndPermissions`;

            try {
                const response = await axios.get(url);
                this.user = response.data;
            } catch (e) {
                throw e;
            }
        },
    },
    async created() {
        await this.getRolesAndPermissions();
    }
};
