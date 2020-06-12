<template>
    <section class="section">
        <users-table></users-table>
    </section>
</template>

<script>
    import UsersTable from "./Table";
    import {hasRightTo} from "../../functions/shared";

    export default {
        name: "UsersIndexPage",
        components: {
            UsersTable,
        },
        async beforeRouteEnter (to, from, next) {
            // Check, if we have permission to view user's page
            const routerData = { to, from, next };

            const apiData = { ability: 'viewAny', model: 'User'};

            await hasRightTo(routerData, apiData);
        },
    }
</script>
