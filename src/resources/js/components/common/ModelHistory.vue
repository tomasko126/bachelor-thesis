<template>
    <div id="history">
        <div id="history-header">
            <h2 class="is-size-4">{{ this.$t('common.history.history') }}</h2>
        </div>

        <b-table :data="history.data"
                 :loading="history.isLoading"
                 id="history-table"
                 :aria-next-label="this.$t('common.next_page')"
                 :aria-previous-label="this.$t('common.previous_page')"
                 :aria-page-label="this.$t('common.page')"
                 :aria-current-label="this.$t('common.current_page')"
                 :default-sort-direction="defaultSortOrder"
                 :default-sort="[sortField, sortOrder]"
                 :paginated="true"
                 :per-page="10"
                 :detailed="true"
                 :show-detail-icon="true"
        >

            <template slot-scope="props">
                <b-table-column field="date" :label="$t('common.history.event_date')">
                    {{ dateFormat(props.row.created_at) }}
                </b-table-column>

                <b-table-column field="name" :label="$t('common.history.name')">
                    {{ props.row.fired_by }}
                </b-table-column>

                <b-table-column field="type" :label="$t('common.history.event_type')">
                    {{ getEventName(props.row.event) }}
                </b-table-column>
            </template>

            <template slot="detail" slot-scope="props">
                <div v-if="props.row.event === 'updated'" class="columns">
                    <div class="column">
                        <h3 class="has-text-weight-semibold">{{ $t('common.history.old_values') }}</h3>
                        <p v-for="value of getChangedFields(props.row.old_values)">{{ value }}</p>
                    </div>
                    <div class="column">
                        <h3 class="has-text-weight-semibold">{{ $t('common.history.new_values') }}</h3>
                        <p v-for="value of getChangedFields(props.row.new_values)">{{ value }}</p>
                    </div>
                </div>
                <div v-else>
                    <p class="has-text-centered">{{ $t('common.history.no_additional_info') }}</p>
                </div>
            </template>

            <template v-if="!history.isLoading" slot="empty">
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
    import {formatDateTime} from "../../functions/shared";

    export default {
        name: "History",
        props: {
            id: Number,
            model: String,
        },
        data() {
            return {
                history: {
                    data: [],
                    isLoading: false,
                },
                defaultSortOrder: 'desc',
                sortField: 'created',
                sortOrder: 'desc',
            }
        },
        methods: {
            async loadHistory() {
                this.history.isLoading = true;

                try {
                    const response = await axios.get(`/api/${this.model}/${this.id}/history`);
                    this.history.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('common.history.history_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.history.isLoading = false;
                }
            },
            dateFormat(date) {
                return formatDateTime(date);
            },
            getEventName(event) {
                const model = this.model.slice(0, -1);

                switch (event) {
                    case 'created': return this.$t(`${model}.index.created_${model}`);
                    case 'updated': return this.$t(`${model}.index.updated_${model}`);
                    case 'deleted': return this.$t(`${model}.index.deleted_${model}`);
                    case 'restored': return this.$t(`${model}.index.restored_${model}`);
                    default: return '';
                }
            },
            getLocalizedField(key) {
                // Handle IDs differently
                if (key.endsWith('_id')) {
                    return this.$t(`common.history.${key}`)
                }

                switch (this.model) {
                    case 'animals': return this.$t(`animal.index.${key}`);
                    case 'litters': return this.$t(`litter.index.${key}`);
                    default: return '';
                }
            },
            getChangedFields(data) {
                let fields = [];
                for (const [key, value] of Object.entries(data)) {
                    let localizedField = this.getLocalizedField(key);
                    let formattedValue = value;

                    if (!value) {
                        formattedValue = '-';
                    }

                    fields.push(`${localizedField} => ${formattedValue}`);
                }

                return fields;
            },
        },
        async created() {
            await this.loadHistory();
        }
    }
</script>

<style lang="scss" scoped>
    #history {
        margin-top: 10px;
    }
</style>
