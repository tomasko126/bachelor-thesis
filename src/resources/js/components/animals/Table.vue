<template>
    <div id="animals-table-container" class="container">
        <h1 id="heading" class="has-text-centered is-size-3">{{ this.$t('animals.index.animals') }}</h1>
        <div class="table-header">
            <table-filter :animal-name="true"
                          :animal-nickname="true"
                          :breeder="true"
                          :owner="true"
                          :me-as-owner="true"
                          :user="user"
                          @filter="onFilter($event)"></table-filter>
            <div class="add-button field">
                <b-tooltip :label="this.$t('animals.index.add_animal')">
                    <router-link :to="{ name: 'animals.create' }">
                        <b-button icon-right="plus-circle" type="is-success" />
                    </router-link>
                </b-tooltip>
            </div>
        </div>

        <b-table
            :data="data"
            :loading="loading"
            id="animals-table"
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
            @sort="onSort">

            <template slot-scope="props">
                <b-table-column field="name" :label="$t('animals.index.name')" sortable>
                    <router-link :to="{ name: 'animal', params: { animal: props.row.id } }">{{ props.row.name }}</router-link>
                </b-table-column>

                <b-table-column field="breeder.name" :label="$t('animals.index.breeder')" sortable>
                    {{ peopleFormat(props.row.breeder) }}
                </b-table-column>

                <b-table-column field="owner.name" :label="$t('animals.index.owner')" sortable>
                    {{ peopleFormat(props.row.owner) }}
                </b-table-column>

                <b-table-column field="birthdate" :label="$t('animals.index.birthdate')" sortable>
                    {{ birthDate(props.row.birthdate) }}
                </b-table-column>

                <b-table-column field="sex" :label="$t('animals.index.sex')" sortable>
                    <span>
                        <b-icon :icon="props.row.sex === 'Male' ? 'mars' : 'venus'"></b-icon>
                        {{ $t(`animals.index.${props.row.sex.toLowerCase()}`) }}
                    </span>
                </b-table-column>

                <b-table-column field="registrated" :label="$t('animals.index.registered')">
                    <span :class="
                            [
                                'tag',
                                {'is-danger': props.row.registrations.length === 0},
                                {'is-success': props.row.registrations.length > 0}
                            ]">
                        {{ props.row.registrations.length > 0 ? $t('animals.index.yes') : $t('animals.index.no') }}
                    </span>
                </b-table-column>

                <b-table-column>
                    <animals-table-dropdown :animal-id="props.row.id" :animal="props.row" :user="user" @reloadTable="loadData"></animals-table-dropdown>
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
    import AnimalsTableDropdown from "./TableDropdown";
    import TableFilter from "../common/TableFilter";

    export default {
        name: "AnimalsTable",
        components: {
            TableFilter,
            AnimalsTableDropdown,
        },
        props: {
            user: Object,
        },
        data() {
            return {
                data: [],
                filter: [],
                total: 0,
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
                    const url = `/api/animals/filter`;

                    const response = await axios.get(url, {
                        params: {
                            name: this.filter.name,
                            nickname: this.filter.nickname,
                            breeder: this.filter.breeder,
                            owner: this.filter.owner,
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

                    this.$buefy.toast.open({ message: this.$t('animals.index.load_fail'), type: 'is-danger' });
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

            peopleFormat(owner) {
                return owner?.name ?? '-';
            },

            birthDate(date) {
                return date ?? '-';
            }
        },
    }
</script>

<style lang="scss" scoped>
    #animals-table-container {
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

        #animals-table {
            width: 100%;
        }
    }
</style>
