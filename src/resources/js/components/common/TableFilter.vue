<template>
    <section id="table-filter">
        <b-field grouped group-multiline>
            <b-field v-if="litterType" :label="this.$t('filter.type')">
                <b-select :placeholder="this.$t('filter.type_placeholder')" v-model="type">
                    <option value="">{{ this.$t('filter.all') }}</option>
                    <option value="PP">{{ this.$t('filter.pp') }}</option>
                    <option value="VP">{{ this.$t('filter.vp') }}</option>
                    <option value="NV">{{ this.$t('filter.nv') }}</option>
                </b-select>
            </b-field>

            <b-field v-if="litterLabel" :label="this.$t('filter.label')">
                <b-autocomplete
                    :clearable="true"
                    :data="labels.data"
                    :placeholder="this.$t('filter.label_placeholder')"
                    field="label"
                    :keep-first="true"
                    :open-on-focus="true"
                    :loading="labels.isLoading"
                    @typing="debounceLabels"
                    v-model="labels.input"
                >
                    <template slot="empty">{{ this.$t('common.no_results') }}</template>
                </b-autocomplete>
            </b-field>

            <b-field v-if="animalName" :label="this.$t('filter.name')">
                <b-autocomplete
                    :clearable="true"
                    :data="names.data"
                    :placeholder="this.$t('filter.name_placeholder')"
                    field="name"
                    :keep-first="true"
                    :open-on-focus="true"
                    :loading="names.isLoading"
                    @typing="debounceAnimalNames"
                    v-model="names.input"
                >
                    <template slot="empty">{{ this.$t('common.no_results') }}</template>
                </b-autocomplete>
            </b-field>

            <b-field v-if="animalNickname" :label="this.$t('filter.nickname')">
                <b-autocomplete
                    :clearable="true"
                    :data="nicknames.data"
                    :placeholder="this.$t('filter.nickname_placeholder')"
                    field="nickname"
                    :keep-first="true"
                    :open-on-focus="true"
                    :loading="nicknames.isLoading"
                    @typing="debounceAnimalNickNames"
                    v-model="nicknames.input"
                >
                    <template slot="empty">{{ this.$t('common.no_results') }}</template>
                </b-autocomplete>
            </b-field>

            <b-field v-if="breeder" :label="this.$t('filter.breeder')">
                <b-autocomplete
                    :clearable="true"
                    :data="breeders.data"
                    :placeholder="this.$t('filter.breeder_placeholder')"
                    field="name"
                    :keep-first="true"
                    :open-on-focus="true"
                    :loading="breeders.isLoading"
                    @typing="debounceBreeders"
                    v-model="breeders.input"
                >
                    <template slot="empty">{{ this.$t('common.no_results') }}</template>
                </b-autocomplete>
            </b-field>

            <b-field v-if="owner" :label="this.$t('filter.owner')">
                <b-autocomplete
                    :clearable="true"
                    :data="owners.data"
                    :placeholder="this.$t('filter.owner_placeholder')"
                    field="name"
                    :keep-first="true"
                    :open-on-focus="true"
                    :loading="owners.isLoading"
                    @typing="debounceOwners"
                    v-model="owners.input"
                >
                    <template slot="empty">{{ this.$t('common.no_results') }}</template>
                </b-autocomplete>
            </b-field>

            <b-field v-if="litterApprovalStatus" :label="this.$t('filter.approval_status')">
                <b-select :placeholder="this.$t('filter.approval_status_placeholder')" v-model="state">
                    <option value="">{{ this.$t('filter.all') }}</option>
                    <option value="Approved">{{ this.$t('filter.approved') }}</option>
                    <option value="Sent" :disabled="!canSeeNotApprovedLitters">{{ this.$t('filter.sent') }}</option>
                    <option value="Rejected" :disabled="!canSeeNotApprovedLitters">{{ this.$t('filter.rejected') }}</option>
                </b-select>
            </b-field>

            <b-field v-if="meAsOwner" id="me-as-owner-button">
                <b-button type="is-info" @click="setMeAsOwner">
                    {{ setMeAsOwnerMessage }}</b-button>
            </b-field>

            <b-field id="filter-button">
                <b-button type="is-success" @click="onFilter">{{ this.$t('filter.filter') }}</b-button>
            </b-field>

            <div class="field" style="display:none;"></div>
        </b-field>
    </section>
</template>

<script>
    import debounce from 'lodash/debounce';
    import HasPermission from "../../mixins/HasPermission";
    import HasRole from "../../mixins/HasRole";

    export default {
        name: "TableFilter",
        props: {
            litterApprovalStatus: Boolean,
            litterType: Boolean,
            litterLabel: Boolean,
            animalName: Boolean,
            animalNickname: Boolean,
            breeder: Boolean,
            owner: Boolean,
            meAsOwner: Boolean,
            user: Object,
        },
        data() {
            return {
                type: '',
                state: '',
                names: {
                    data: [],
                    isLoading: false,
                    input: null,
                },
                nicknames: {
                    data: [],
                    isLoading: false,
                    input: null,
                },
                breeders: {
                    data: [],
                    isLoading: false,
                    input: null,
                },
                owners: {
                    data: [],
                    isLoading: false,
                    input: null,
                },
                labels: {
                    data: [],
                    isLoading: false,
                    input: null,
                },
            }
        },
        mixins: [HasPermission, HasRole],
        computed: {
            canSeeNotApprovedLitters() {
                if (this.hasRole('admin')) {
                    return true;
                }

                return this.hasPermission('see not approved litters');
            },
            setMeAsOwnerMessage() {
                return this.$route.name === 'animals' ? this.$t('filter.show_my_animals') : this.$t('filter.show_my_litters');
            }
        },
        methods: {
            debounceLabels: debounce(function (input) {
                if (!input) {
                    this.labels.data = [];
                    return;
                }

                this.loadLabels();
            }, 500),
            debounceBreeders: debounce(function (input) {
                if (!input) {
                    this.breeders.data = [];
                    return;
                }

                this.loadBreeders();
            }, 500),
            debounceOwners: debounce(function (input) {
                if (!input) {
                    this.owners.data = [];
                    return;
                }

                this.loadOwners();
            }, 500),
            debounceAnimalNames: debounce(function (input) {
                if (!input) {
                    this.names.data = [];
                    return;
                }

                this.loadAnimalNames();
            }, 500),
            debounceAnimalNickNames: debounce(function (input) {
                if (!input) {
                    this.names.data = [];
                    return;
                }

                this.loadAnimalNickNames();
            }, 500),
            async loadLabels() {
                this.labels.isLoading = true;
                this.labels.data = [];

                try {
                    const response = await axios.get('/api/litters/search', {
                        params: {
                            keyword: this.labels.input,
                            sort_field: 'label',
                            sort_order: 'asc',
                        }
                    });

                    this.labels.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('filter.litters_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.labels.isLoading = false;
                }
            },
            async loadBreeders() {
                this.breeders.isLoading = true;
                this.breeders.data = [];

                try {
                    const response = await axios.get('/api/people/search', {
                        params: {
                            keyword: this.breeders.input,
                            sort_field: 'name',
                            sort_order: 'asc',
                        },
                    });

                    this.breeders.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('filter.people_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.breeders.isLoading = false;
                }
            },
            async loadOwners() {
                this.owners.isLoading = true;
                this.owners.data = [];

                try {
                    const response = await axios.get('/api/people/search', {
                        params: {
                            keyword: this.owners.input,
                            sort_field: 'name',
                            sort_order: 'asc',
                        },
                    });

                    this.owners.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('filter.people_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.owners.isLoading = false;
                }
            },
            async loadAnimalNames() {
                this.names.isLoading = true;
                this.names.data = [];

                try {
                    const response = await axios.get('/api/animals/search', {
                        params: {
                            keyword: this.names.input,
                            sort_field: 'name',
                            sort_order: 'asc',
                            column: 'name',
                        },
                    });

                    this.names.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('filter.animals_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.names.isLoading = false;
                }
            },
            async loadAnimalNickNames() {
                this.nicknames.isLoading = true;
                this.nicknames.data = [];

                try {
                    const response = await axios.get('/api/animals/search', {
                        params: {
                            keyword: this.nicknames.input,
                            sort_field: 'name',
                            sort_order: 'asc',
                            column: 'nickname',
                        },
                    });

                    this.nicknames.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('filter.animals_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.nicknames.isLoading = false;
                }
            },
            onFilter() {
                const filters = {};

                if (this.litterApprovalStatus) {
                    filters.state = this.state;
                }

                if (this.litterLabel) {
                    filters.label = this.labels.input;
                }

                if (this.litterType) {
                    filters.type = this.type;
                }

                if (this.breeders) {
                    filters.breeder = this.breeders.input;
                }

                if (this.owners) {
                    filters.owner = this.owners.input;
                }

                if (this.animalName) {
                    filters.name = this.names.input;
                }

                if (this.animalNickname) {
                    filters.nickname = this.nicknames.input;
                }

                const queryString = '?' + this.createQueryString(filters);
                this.$router.push(queryString).catch(() => {});

                this.$emit('filter', filters);
            },
            createQueryString(filters) {
                return Object
                    .keys(filters)
                    .filter(k => filters[k])
                    .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(filters[k]))
                    .join('&');
            },
            getOptionsFromUrl() {
                const {query} = this.$route;

                this.type = query?.type ?? '';
                this.labels.input = query?.label ?? '';
                this.breeders.input = query?.breeder ?? '';
                this.owners.input = query?.owner ?? '';
                this.state = query?.state ?? '';
                this.names.input = query?.name ?? '';
                this.nicknames.input = query?.nickname ?? '';

                this.onFilter();
            },
            setMeAsOwner() {
                this.owners.input = this.user.name;

                this.onFilter();
            },
        },
        watch: {
            $route() {
                this.getOptionsFromUrl();
            }
        },
        created() {
            this.getOptionsFromUrl();
        }
    }
</script>

<style lang="scss" scoped>
    #table-filter {
        display: flex;
        flex-direction: column;
        width: 100%;

        #me-as-owner-button, #filter-button {
            align-self: flex-end;
        }
    }
</style>
