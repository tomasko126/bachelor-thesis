<template>
    <section class="section">
        <litters-table :user="user"></litters-table>
    </section>
</template>

<script>
    import LittersTable from "./Table";
    import PermissionsMixin from "../../mixins/PermissionsMixin";

    export default {
        name: "LittersIndexPage",
        components: {
            LittersTable
        },
        mixins: [PermissionsMixin],
    }
</script>
