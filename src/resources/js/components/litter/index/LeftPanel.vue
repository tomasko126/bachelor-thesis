<template>
    <aside id="left-panel" class="column is-narrow dashboard is-full-height is-one-fifth is-hidden-mobile">
        <div class="dashboard-panel is-medium has-thick-padding">
            <div class="dashboard-panel-content">
                <aside class="menu has-text-white">
                    <p class="menu-label">
                        {{ this.$t('litter.index.general') }}
                    </p>
                    <scrollactive>
                        <ul class="menu-list">
                            <li>
                                <a class="scrollactive-item" href="#basic">{{ this.$t('litter.index.basic') }}</a>
                                <ul>
                                    <li><a class="scrollactive-item" href="#parents">{{ this.$t('litter.index.parents') }}</a></li>
                                </ul>
                            </li>
                            <li>
                                <a class="scrollactive-item" href="#details">{{ this.$t('litter.index.details') }}</a>
                                <ul>
                                    <li><a class="scrollactive-item" href="#babies">{{ this.$t('litter.index.babies') }}</a></li>
                                    <li><a class="scrollactive-item" href="#breeder">{{ this.$t('litter.index.breeder') }}</a></li>
                                    <li v-if="canSeeLitterRequests"><a class="scrollactive-item" href="#registration">{{ this.$t('litter.index.registration') }}</a></li>
                                </ul>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#animals">{{ this.$t('litter.index.animals') }}</a>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#genealogy">{{ this.$t('litter.index.genealogy') }}</a>
                            </li>
                            <li v-if="!isForm && canSeeLitterRequests">
                                <a class="scrollactive-item" href="#requests">{{ this.$t('litter.index.approval_requests') }}</a>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#notes">{{ this.$t('litter.index.notes') }}</a>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#history">{{ this.$t('litter.index.history') }}</a>
                            </li>
                        </ul>
                    </scrollactive>
                </aside>
            </div>
        </div>
    </aside>
</template>

<script>
    import HasPermission from "../../../mixins/HasPermission";
    import HasRole from "../../../mixins/HasRole";

    export default {
        name: "LitterLeftPanel",
        props: {
            isForm: Boolean,
            litter: Object,
            user: Object,
        },
        mixins: [HasPermission, HasRole],
        computed: {
            canSeeLitterRequests() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (!this.user || !this.litter || this.litter?.type === 'VP') {
                    return false;
                }

                if (this.user?.id === this.litter?.creator_id || this.user?.id === this.litter?.owner_id) {
                    return true;
                }

                return this.hasPermission('see litter requests');
            },
        }
    }
</script>

<style lang="scss" scoped>
    #left-panel {
        margin-top: 40px;

        .dashboard-panel {
            top: 15px;
            position: sticky;
        }
    }
</style>
