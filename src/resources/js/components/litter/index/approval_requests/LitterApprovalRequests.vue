<template>
    <div v-if="canSeeLitterRequests" id="requests" class="vld-parent">
        <header id="requests-header">
            <h2 class="is-size-4">{{ this.$t('litter.requests.approval_requests') }}</h2>
            <b-tooltip :label="addNewRequestLabel" :type="canAddNewRequest ? 'is-primary' : 'is-danger'" :multilined="!canAddNewRequest && !isCompleteDataAvailable">
                <b-button type="is-success" icon-right="plus-circle" :disabled="!canAddNewRequest" @click="openRequestModal" />
            </b-tooltip>
        </header>

        <b-table
            :data="requests.data"
            :loading="requests.isLoading"
            id="requests-table"

            :aria-next-label="this.$t('common.next_page')"
            :aria-previous-label="this.$t('common.previous_page')"
            :aria-page-label="this.$t('common.page')"
            :aria-current-label="this.$t('common.current_page')"
        >
            <template slot-scope="props">
                <b-table-column field="created_at" :label="$t('litter.requests.created')">
                    {{ dateTimeFormat(props.row.created_at) }}
                </b-table-column>

                <b-table-column field="updated_at" :label="$t('litter.requests.updated')">
                    {{ dateTimeFormat(props.row.updated_at) }}
                </b-table-column>

                <b-table-column field="state" :label="$t('litter.requests.state')">
                    <span :class="
                            [
                                'tag',
                                'approval-status-tag',
                                {'is-danger': props.row.state === 'Rejected'},
                                {'is-info': props.row.state === 'Sent'},
                                {'is-success': props.row.state === 'Approved'}
                            ]">
                        {{ approvalStatusText(props.row.state) }}
                    </span>
                </b-table-column>

                <b-table-column field="creator_note" :label="$t('litter.requests.your_note')">
                    {{ props.row.creator_note }}
                </b-table-column>

                <b-table-column field="registrator_note" :label="$t('litter.requests.registrator_note')">
                    {{ props.row.registrator_note }}
                </b-table-column>

                <b-table-column>
                    <b-button v-if="props.row.state === 'Sent' && canReplyToRequest"
                              size="is-small"
                              icon-left="reply"
                              type="is-primary"
                              @click="openAnswerModal(props.row.id)"
                    >
                        {{ $t('litter.requests.reply') }}
                    </b-button>
                </b-table-column>
            </template>

            <template v-if="!requests.isLoading" slot="empty">
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

        <b-modal :active.sync="isAnswerModalActive"
                 trap-focus
                 aria-role="dialog"
                 aria-modal
                 :width="650"
        >
            <approval-request-answer-modal :litter-id="litterId" :request-id="requestId"></approval-request-answer-modal>
        </b-modal>

        <b-modal :active.sync="isRequestModalActive"
                 trap-focus
                 aria-role="dialog"
                 aria-modal
                 :width="650"
        >
            <new-approval-request-modal :litter-id="litterId" @submit="loadRequests"></new-approval-request-modal>
        </b-modal>
    </div>
</template>

<script>
    import {formatDateTime} from "../../../../functions/shared";
    import ApprovalRequestAnswerModal from "./ApprovalRequestAnswerModal";
    import HasRole from "../../../../mixins/HasRole";
    import HasPermission from "../../../../mixins/HasPermission";
    import NewApprovalRequestModal from "./NewApprovalRequestModal";

    export default {
        name: "LitterApprovalRequests",
        components: {NewApprovalRequestModal, ApprovalRequestAnswerModal},
        props: {
            litterId: Number,
            litter: Object,
            user: Object,
        },
        mixins: [HasRole, HasPermission],
        data() {
            return {
                requests: {
                    data: [],
                    isLoading: false,
                },
                isAnswerModalActive: false,
                isRequestModalActive: false,
                requestId: null,
            }
        },
        computed: {
            canAddNewRequest() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.user?.id === this.litter?.creator_id || this.user?.id === this.litter?.owner_id) {
                    // If we don't have all available data, do not let user to add new request
                    if (!this.isCompleteDataAvailable) {
                        return false;
                    }

                    // If no request has been submitted yet, we can add a new one
                    if (!this.requests.data.length) {
                        return true;
                    }

                    // If last request was rejected, we still can create a new one
                    if (this.requests.data[0].state === 'Rejected') {
                        return true;
                    }

                    return false;
                }

                return false;
            },
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
            canReplyToRequest() {
                if (this.hasRole('admin')) {
                    return true;
                }

                return this.hasPermission('answer to litter requests');
            },
            isCompleteDataAvailable() {
                if (!this.litter?.father_id || !this.litter?.mother_id || !this.litter?.birthdate ||
                    !this.litter?.babies_born || !this.litter?.babies_reared || !this.litter?.reared_boys || !this.litter?.reared_girls) {
                    return false;
                }
                return true;
            },
            addNewRequestLabel() {
                if (!this.isCompleteDataAvailable) {
                    return this.$t('litter.requests.missing_data_for_request');
                }

                return !this.canAddNewRequest ? this.$t('common.no_permission') : this.$t('litter.requests.add_request');
            }
        },
        methods: {
            approvalStatusText(state) {
                return this.$t(`litter.requests.${state.toLowerCase()}`);
            },
            dateTimeFormat(date) {
                return formatDateTime(date);
            },
            async loadRequests() {
                // Do not load approval requests, when we do not have rights to see them anyway
                if (!this.canSeeLitterRequests || this.requests.isLoading) {
                    return;
                }

                this.requests.isLoading = true;

                try {
                    const response = await axios.get(`/api/litters/${this.litterId}/requests`);
                    this.requests.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litter.requests.requests_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.requests.isLoading = false;
                }
            },
            openAnswerModal(id) {
                this.requestId = id;
                this.isAnswerModalActive = true;
            },
            openRequestModal() {
                this.isRequestModalActive = true;
            }
        },
        watch: {
            litter() {
                this.loadRequests();
            },
            user() {
                this.loadRequests();
            },
        },
        async created() {
            await this.loadRequests();
        }
    }
</script>

<style lang="scss" scoped>
    #requests {
        min-height: 200px;

        #requests-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }

        #requests-table {
            margin-top: 10px;
        }
    }
</style>
