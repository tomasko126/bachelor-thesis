<template>
    <div id="registrations">
        <div id="registrations-header">
            <h2 class="is-size-4">{{ this.$t('animal.index.registrations') }}</h2>
            <b-tooltip :label="canAddRegistration ? this.$t('animal.index.add_registration') : this.$t('animal.index.no_permission')" :type="canAddRegistration ? 'is-primary' : 'is-danger'">
                <b-button type="is-success" icon-right="plus-circle" @click="openModal()" :disabled="!canAddRegistration"/>
            </b-tooltip>
        </div>

        <b-table
            :data="registrations.data"
            :loading="registrations.isLoading"
            id="registrations-table"
            :aria-next-label="this.$t('common.next_page')"
            :aria-previous-label="this.$t('common.previous_page')"
            :aria-page-label="this.$t('common.page')"
            :aria-current-label="this.$t('common.current_page')"
            :default-sort-direction="defaultSortOrder"
            :default-sort="[sortField, sortOrder]"
        >

            <template slot-scope="props">
                <b-table-column field="club" :label="$t('registrations.club')" sortable>
                    {{ props.row.club }}
                </b-table-column>

                <b-table-column field="registration_number" :label="$t('registrations.registration_number')" sortable>
                    {{ registrationNumberFormat(props.row.club, props.row.type, props.row.registration_number, props.row.year) }}
                </b-table-column>

                <b-table-column field="created_at" :label="$t('registrations.registration_date')" sortable>
                    {{ registrationDateFormat(props.row.created_at) }}
                </b-table-column>

                <b-table-column field="breeding_available" :label="$t('registrations.breeding_available')">
                    <span :class="
                            [
                                'tag',
                                {'is-danger': props.row.breeding_available === 0},
                                {'is-warning': props.row.breeding_available === null},
                                {'is-success': props.row.breeding_available}
                            ]">
                        {{ breedingAvailableText(props.row.breeding_available) }}
                    </span>
                </b-table-column>

                <b-table-column field="breeding_limitation" :label="$t('registrations.breeding_limitation')">
                    {{ breedingLimitationFormat(props.row.breeding_limitation) }}
                </b-table-column>

                <b-table-column>
                    <animal-registrations-dropdown :animal="animal" :registration-id="props.row.id" :registration-club="props.row.club" :user="user" @openModal="openModal($event)" @reloadTable="loadRegistrations"></animal-registrations-dropdown>
                </b-table-column>
            </template>

            <template v-if="!registrations.isLoading" slot="empty">
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

        <b-modal :active.sync="isModalActive"
                 trap-focus
                 aria-role="dialog"
                 aria-modal
                 :width="450"
        >
            <animal-registrations-modal :animal-id="animalId" :registration-id="registrationIdForModal" :user="user" @registrationAdded="loadRegistrations" @registrationUpdated="loadRegistrations"></animal-registrations-modal>
        </b-modal>
    </div>
</template>

<script>
    import AnimalRegistrationsDropdown from "./AnimalRegistrationsDropdown";
    import AnimalRegistrationsModal from "./AnimalRegistrationsModal";

    import {formatDate} from "../../../../functions/shared";
    import HasPermission from "../../../../mixins/HasPermission";
    import HasRole from "../../../../mixins/HasRole";

    export default {
        name: "AnimalRegistrations",
        props: {
            animalId: Number,
            animal: Object,
            user: Object,
        },
        mixins: [HasPermission, HasRole],
        components: {
            AnimalRegistrationsDropdown, AnimalRegistrationsModal,
        },
        data() {
            return {
                registrations: {
                    data: [],
                    isLoading: false,
                },
                registrationIdForModal: null,
                defaultSortOrder: 'desc',
                isModalActive: false,
                sortField: 'id',
                sortOrder: 'desc',
            }
        },
        computed: {
            canAddRegistration() {
                if (this.hasRole('admin')) {
                    return true;
                }

                // If owner of animal is different than currently logged-in user,
                // do not let this user add new registration unless he has a permission to do it
                if (this.user?.id !== this.animal?.owner?.user_id && !this.hasPermission('modify registration to foreign animal')) {
                    return false;
                }

                return true;
            }
        },
        methods: {
            breedingAvailableText(available) {
                switch (available) {
                    case null: return this.$t('registrations.not_available');
                    case 0: return this.$t('registrations.no');
                    case 1: return this.$t('registrations.yes');
                    default: return '';
                }
            },
            breedingLimitationFormat(number) {
                return number ? number : '-';
            },
            registrationDateFormat(date) {
                return formatDate(date);
            },
            registrationNumberFormat(club, type, regNumber, year) {
                if (club === 'CZKP') {
                    return `${type} ${regNumber}-${year}`;
                }

                return `${regNumber}`;
            },
            async loadRegistrations() {
                this.registrations.isLoading = true;

                try {
                    const response = await axios.get(`/api/animals/${this.animalId}/registrations`);
                    this.registrations.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('registrations.load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.registrations.isLoading = false;
                }
            },
            openModal(registrationId) {
                this.registrationIdForModal = registrationId ?? null;
                this.isModalActive = true;
            }
        },
        async created() {
            await this.loadRegistrations();
        }
    }
</script>

<style lang="scss" scoped>
    #registrations {
        min-height: 200px;

        #registrations-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }

        #registrations-table {
            margin-top: 10px;
        }
    }
</style>
