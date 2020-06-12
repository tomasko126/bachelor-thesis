<template>
    <div id="litter-page" class="columns section">
        <left-panel :litter="litter" :user="user" :key="litterId + 1"></left-panel>
        <litter-information :litter-id="litterId" :litter="litter" :is-loading="isLoading" :user="user"></litter-information>
        <right-panel :is-loading="isLoading" :litter-id="litterId" :litter="litter" :user="user" :key="litterId"></right-panel>
    </div>
</template>

<script>
    import LeftPanel from './LeftPanel';
    import RightPanel from "./RightPanel";
    import LitterInformation from "./LitterInformation";
    import PermissionsMixin from "../../../mixins/PermissionsMixin";
    import {hasRightTo} from "../../../functions/shared";

    export default {
        name: "LitterPage",
        components: {
            LeftPanel, LitterInformation, RightPanel,
        },
        mixins: [PermissionsMixin],
        data() {
            return {
                litter: null,
                isLoading: false,
            }
        },
        computed: {
            litterId() {
                return Number(this.$route.params.litter);
            }
        },
        methods: {
            async loadData() {
                this.isLoading = true;
                const url = `/api/litters/${this.litterId}`;

                try {
                    const request = await axios.get(url);
                    this.litter = request.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litter.index.page_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isLoading = false;
                }
            },
        },
        async beforeRouteEnter (to, from, next) {
            // Check, if we have permission to see the litter
            const routerData = { to, from, next };
            const litterId = to.params?.litter;

            const apiData = { ability: 'view', model: 'Litter', model_id: litterId, deleted: true };

            await hasRightTo(routerData, apiData);
        },
        async created() {
            await this.loadData();
        }
    }
</script>
