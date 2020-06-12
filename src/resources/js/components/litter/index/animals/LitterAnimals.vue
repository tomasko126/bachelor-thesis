<template>
    <div id="animals">
        <div id="animals-header">
            <h2 class="is-size-4">{{ this.$t('litter.animals.animals') }}</h2>
        </div>

        <b-table
            :data="animals.data"
            :loading="animals.isLoading"
            id="animals-table"

            :aria-next-label="this.$t('common.next_page')"
            :aria-previous-label="this.$t('common.previous_page')"
            :aria-page-label="this.$t('common.page')"
            :aria-current-label="this.$t('common.current_page')"

            :default-sort-direction="defaultSortOrder"
            :default-sort="[sortField, sortOrder]"
            :detailed="true"
            :show-detail-icon="true"
        >

            <template slot-scope="props">
                <b-table-column field="name" :label="$t('litter.animals.name')" sortable>
                    <router-link :to="{ name: 'animal', params: { animal: props.row.id } }">
                        {{ props.row.name }}
                    </router-link>
                </b-table-column>

                <b-table-column field="nickname" :label="$t('litter.animals.nickname')" sortable>
                    {{ textFormat(props.row.nickname) }}
                </b-table-column>

                <b-table-column field="birthdate" :label="$t('litter.animals.birthdate')" sortable>
                    {{ textFormat(props.row.birthdate) }}
                </b-table-column>

                <b-table-column field="sex" :label="$t('litter.animals.sex')" sortable>
                    <span>
                        <b-icon :icon="props.row.sex === 'Male' ? 'mars' : 'venus'"></b-icon>
                        {{ $t(`litter.animals.${props.row.sex.toLowerCase()}`) }}
                    </span>
                </b-table-column>
            </template>

            <template slot="detail" slot-scope="props">
                <h3 class="is-size-5">{{ $t('litter.animals.details') }}</h3>
                <div class="columns">
                    <div class="column">
                        <p>{{ $t('litter.animals.ear_type') }}: {{ textFormat(props.row.ear_type) }}</p>
                        <p>{{ $t('litter.animals.eyes_color') }}: {{ textFormat(props.row.eyes_color) }}</p>
                        <p>{{ $t('litter.animals.fur_type') }}: {{ textFormat(props.row.fur_type) }}</p>
                    </div>
                    <div class="column">
                        <p>{{ $t('litter.animals.fur_color') }}: {{ textFormat(props.row.fur_color) }}</p>
                        <p>{{ $t('litter.animals.markings') }}: {{ textFormat(props.row.markings) }}</p>
                    </div>
                </div>
                <div class="columns">
                    <div class="column" v-if="canSeeOwnerSection">
                        <h3 class="is-size-5">{{ $t('litter.animals.owner') }}</h3>
                        <p>{{ $t('litter.animals.name') }}: {{ textFormat(props.row.owner_name) }}</p>
                        <p>{{ $t('litter.animals.contact') }}: {{ textFormat(props.row.owner_contact) }}</p>
                        <p>{{ $t('litter.animals.member_card_number') }}: {{ textFormat(props.row.owner_member_card_number) }}</p>
                    </div>
                </div>
            </template>

            <template v-if="!animals.isLoading" slot="empty">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <b-icon
                                icon="frown"
                                size="is-large">
                            </b-icon>
                        </p>
                        <p>{{ this.$t('common.no_results') }}</p>
                    </div>
                </section>
            </template>
        </b-table>
    </div>
</template>

<script>
    import HasRole from "../../../../mixins/HasRole";
    import HasPermission from "../../../../mixins/HasPermission";

    export default {
        name: "LitterAnimals",
        props: {
            litter: Object,
            litterId: Number,
            user: Object,
        },
        mixins: [HasRole, HasPermission],
        data() {
            return {
                animals: {
                    data: [],
                    isLoading: false,
                },
                sortField: 'id',
                sortOrder: 'desc',
                defaultSortOrder: 'desc',
            }
        },
        computed: {
            canSeeOwnerSection() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.litter?.creator_id === this.user?.id || this.litter?.owner?.user_id === this.user?.id) {
                    return true;
                }

                return this.hasPermission('edit foreign litters');
            }
        },
        methods: {
            async loadAnimals() {
                this.animals.isLoading = true;

                try {
                    const response = await axios.get(`/api/litters/${this.litterId}/animals`);
                    this.animals.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litter.animals.load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.animals.isLoading = false;
                }
            },

            textFormat(text) {
                return text ?? '-';
            },
        },
        async created() {
            await this.loadAnimals();
        }
    }
</script>

<style lang="scss" scoped>
    .is-divider {
        margin: 1rem 0;
    }
</style>
