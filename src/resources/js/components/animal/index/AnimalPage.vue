<template>
    <div id="animal-page" class="columns section">
        <left-panel :animal="animal" :user="user"></left-panel>
        <animal-information :animal-id="animalId" :animal="animal" :is-loading="isLoading" :user="user"></animal-information>
        <right-panel :animal-id="animalId" :is-loading="isLoading" :animal="animal" :user="user" :key="animalId"></right-panel>
    </div>
</template>

<script>
    import LeftPanel from "./LeftPanel";
    import AnimalInformation from "./AnimalInformation";
    import RightPanel from "./RightPanel";

    import permissionsMixin from "../../../mixins/PermissionsMixin";

    export default {
        name: "AnimalPage",
        components: {RightPanel, LeftPanel, AnimalInformation},
        mixins: [permissionsMixin],
        data() {
            return {
                animal: null,
                isLoading: false,
            }
        },
        computed: {
            animalId() {
                return Number(this.$route.params.animal);
            }
        },
        methods: {
            async loadData() {
                this.isLoading = true;
                const url = `/api/animals/${this.animalId}`;

                try {
                    const request = await axios.get(url);
                    this.animal = request.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animal.index.page_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isLoading = false;
                }
            }
        },
        watch: {
            async $route(to, from) {
                if (from.name === to.name) {
                    await this.loadData();
                }
            }
        },
        async created() {
            await this.loadData();
        }
    }
</script>
