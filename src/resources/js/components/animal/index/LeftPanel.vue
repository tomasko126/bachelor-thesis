<template>
    <aside id="left-panel" class="column is-narrow dashboard is-full-height is-one-fifth is-hidden-mobile">
        <div class="dashboard-panel is-medium has-thick-padding">
            <div class="dashboard-panel-content">
                <aside class="menu has-text-white">
                    <p class="menu-label">
                        {{ this.$t('animal.index.general') }}
                    </p>
                    <scrollactive>
                        <ul class="menu-list">
                            <li>
                                <a class="scrollactive-item" href="#basic">{{ this.$t('animal.index.basic') }}</a>
                            </li>
                            <li>
                                <a class="scrollactive-item" href="#details">{{ this.$t('animal.index.details') }}</a>
                                <ul>
                                    <li><a class="scrollactive-item" href="#people">{{ this.$t('animal.index.people') }}</a></li>
                                    <li><a class="scrollactive-item" href="#external-features">{{ this.$t('animal.index.external_features') }}</a></li>
                                    <li><a class="scrollactive-item" href="#breeding-info">{{ this.$t('animal.index.breeding_info') }}</a></li>
                                    <li><a class="scrollactive-item" href="#death-info">{{ this.$t('animal.index.death_info') }}</a></li>
                                </ul>
                            </li>
                            <li v-if="!(!isForm && !canSeeOwnerSection)">
                                <a class="scrollactive-item" href="#owner-info">{{ this.$t('animal.index.owner_info') }}</a>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#registrations">{{ this.$t('animal.index.registrations') }}</a>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#genealogy">{{ this.$t('animal.index.genealogy') }}</a>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#notes">{{ this.$t('animal.index.notes') }}</a>
                            </li>
                            <li v-if="!isForm">
                                <a class="scrollactive-item" href="#history">{{ this.$t('animal.index.history') }}</a>
                            </li>
                        </ul>
                    </scrollactive>
                </aside>
            </div>
        </div>
    </aside>
</template>

<script>
    import HasRole from "../../../mixins/HasRole";
    import HasPermission from "../../../mixins/HasPermission";

    export default {
        name: "LeftPanel",
        props: {
            animal: Object,
            isForm: Boolean,
            user: Object,
        },
        mixins: [HasRole, HasPermission],
        computed: {
            canSeeOwnerSection() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.animal?.creator_id === this.user?.id || this.animal?.owner?.user_id === this.user?.id) {
                    return true;
                }

                return this.hasPermission('edit foreign animals');
            }
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
