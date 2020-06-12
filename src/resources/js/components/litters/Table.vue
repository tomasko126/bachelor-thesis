<template>
    <div id="litters-table-container" class="container">
        <h1 id="heading" class="has-text-centered is-size-3">{{ this.$t('litters.index.litters') }}</h1>
        <div class="table-header">
            <table-filter :litter-approval-status="true"
                          :litter-label="true"
                          :litter-type="true"
                          :me-as-owner="true"
                          :owner="true"
                          :user="user"
                          @filter="onFilter($event)"></table-filter>
            <div class="add-button field">
                <b-tooltip :label="this.$t('litters.index.add_litter')">
                    <b-button tag="router-link"
                              :to="{ name: 'litters.create' }"
                              type="is-success"
                              icon-right="plus-circle"
                    >
                    </b-button>
                </b-tooltip>
            </div>
        </div>

        <b-table
            :data="data"
            :loading="loading"
            id="litters-table"

            paginated
            backend-pagination
            :total="total"
            :per-page="perPage"
            @page-change="onPageChange"

            :aria-next-label="this.$t('common.next_page')"
            :aria-previous-label="this.$t('common.previous_page')"
            :aria-page-label="this.$t('common.page')"
            :aria-current-label="this.$t('common.current_page')"

            backend-sorting
            :default-sort-direction="defaultSortOrder"
            :default-sort="[sortField, sortOrder]"
            @sort="onSort"
        >

            <template slot-scope="props">
                <b-table-column field="type" :label="$t('litters.index.type')" sortable>
                    {{ props.row.type }}
                </b-table-column>

                <b-table-column field="litter_approval_requests.registration_number" :label="$t('litters.index.registration_number')" sortable>
                    <router-link :to="{ name: 'litter', params: { litter: props.row.id }}">
                        {{ registrationNumberFormat(props.row.latest_approval_request) }}
                    </router-link>
                </b-table-column>

                <b-table-column field="label" :label="$t('litters.index.label')" sortable>
                    <router-link :to="{ name: 'litter', params: { litter: props.row.id }}">
                        {{ props.row.label }}
                    </router-link>
                </b-table-column>

                <b-table-column field="people.name" :label="$t('litters.index.owner')" sortable>
                    {{ usersNameFormat(props.row) }}
                </b-table-column>

                <b-table-column field="birthdate" :label="$t('litters.index.birthdate')" sortable>
                    {{ birthDate(props.row.birthdate) }}
                </b-table-column>

                <b-table-column field="litter_approval_requests.state" :label="$t('litters.index.approval_status')" sortable>
                    <span :class="
                            [
                                'tag',
                                'approval-status-tag',
                                {'is-danger': approvalStatusClass(props.row.state) === 'Rejected'},
                                {'is-info': approvalStatusClass(props.row.state) === 'Sent'},
                                {'is-success': approvalStatusClass(props.row.state) === 'Approved'}
                            ]">
                        {{ approvalStatusText(props.row.state) }}
                    </span>
                </b-table-column>

                <b-table-column>
                    <litters-table-dropdown :litter="props.row" :user="user" @reloadTable="loadData"></litters-table-dropdown>
                </b-table-column>
            </template>

            <template v-if="!loading" slot="empty">
                <section class="section">
                    <div class="content has-text-grey has-text-centered">
                        <p>
                            <b-icon
                                icon="frown"
                                size="is-large">
                            </b-icon>
                        </p>
                        <p>{{ $t('common.no_results') }}</p>
                    </div>
                </section>
            </template>
        </b-table>
    </div>
</template>

<script>
    import LittersTableDropdown from './TableDropdown';
    import TableFilter from "../common/TableFilter";

    export default {
        name: "AnimalsTable",
        components: {
            TableFilter, LittersTableDropdown
        },
        props: {
            user: Object,
        },
        data() {
            return {
                data: [],
                filter: [],
                total: 0,
                keyword: '',
                loading: false,
                sortField: 'id',
                sortOrder: 'desc',
                defaultSortOrder: 'desc',
                page: 1,
                perPage: 10,
            }
        },
        methods: {
            async loadData() {
                this.loading = true;

                try {
                    const url = `/api/litters/filter`;

                    const response = await axios.get(url, {
                        params: {
                            type: this.filter.type,
                            label: this.filter.label,
                            owner: this.filter.owner,
                            state: this.filter.state,
                            sort_field: this.sortField,
                            sort_order: this.sortOrder,
                            page: this.page,
                        }
                    });

                    this.data = [];
                    response.data.data.forEach((item) => {
                        this.data.push(item);
                    });

                    this.total = response.data.total;
                } catch (e) {
                    this.data = [];
                    this.total = 0;

                    this.$buefy.toast.open({ message: this.$t('litters.index.load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.loading = false;
                }
            },

            async onFilter(filters) {
                this.filter = filters;
                await this.loadData();
            },

            onPageChange(page) {
                this.page = page;
                this.loadData();
            },

            onSort(field, order) {
                this.sortField = field;
                this.sortOrder = order;
                this.loadData();
            },

            approvalStatusClass(state) {
                return state ? state : this.$t('litters.index.not_available');
            },

            approvalStatusText(state) {
                return state ? this.$t(`litters.index.${state.toLowerCase()}`) : this.$t('litters.index.not_available');
            },

            registrationNumberFormat(request) {
                return request?.registration_number ?? '-';
            },

            usersNameFormat(litter) {
                return litter?.owner?.name ?? '-';
            },

            birthDate(date) {
                return date ?? '-';
            },
        },
    }
</script>

<style lang="scss" scoped>
    #litters-table-container {
        display: flex;
        flex-direction: column;

        #heading {
            margin-bottom: 30px;
        }

        .table-header {
            display: inline-flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 10px;
        }

        #litters-table {
            width: 100%;

            .approval-status-tag {
                min-width: 5.5rem;
            }
        }
    }
</style>
