<template>
    <section class="section">
        <animals-table :user="user"></animals-table>
    </section>
</template>

<script>
    import AnimalsTable from "./Table";
    import PermissionsMixin from "../../mixins/PermissionsMixin";

    export default {
        name: "AnimalsIndexPage",
        components: {
            AnimalsTable
        },
        mixins: [PermissionsMixin],
    }
</script>
