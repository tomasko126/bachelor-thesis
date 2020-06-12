<template>
    <section id="animal-registration-modal">
        <header class="modal-card-head">
            <p class="modal-card-title has-text-centered">{{ modalHeader }}</p>
        </header>

        <div id="animal-registration-content">
            <loading v-if="isEditMode" :active.sync="registration.isLoading" :is-full-page="false"></loading>

            <section id="animal-registration-section">
                <div class="field is-grouped">
                    <b-field :label="this.$t('registrations.registering_club')" custom-class="required"
                             :message="[
                                { [this.$t('registrations.registering_club_invalid')]: !$v.form.club.validOption && $v.form.club.$error },
                                { [this.$t('registrations.registering_club_required')]: !$v.form.club.required && $v.form.club.$error },
                             ]"
                             :type="{'is-danger': $v.form.club.$error}">
                        <b-select :expanded="true" :placeholder="this.$t('registrations.choose_club')" v-model.trim="$v.form.club.$model" :loading="clubs.isLoading" :disabled="clubs.isLoading">
                            <option
                                v-for="club of Object.keys(clubs.data)"
                                :value="club"
                                :key="club"
                                :disabled="clubs.data[club] || (!canRegisterUnderCZKP && club === 'CZKP')"
                            >
                                {{ club }}
                            </option>
                        </b-select>
                    </b-field>

                    <b-field v-if="showAdditionalInputs" :label="this.$t('registrations.registration_type')" custom-class="required"
                             :message="[
                                { [this.$t('registrations.registration_type_invalid')]: !$v.form.type.validOption && $v.form.type.$error },
                                { [this.$t('registrations.registration_type_required')]: !$v.form.type.required && $v.form.type.$error },
                             ]"
                             :type="{'is-danger': $v.form.type.$error}">
                        <b-select :expanded="true" :placeholder="this.$t('registrations.choose_registration_type')" v-model.trim="$v.form.type.$model" :loading="types.isLoading" :disabled="types.isLoading">
                            <option
                                v-for="type in types.data"
                                :value="type"
                                :key="type">
                                {{ type }}
                            </option>
                        </b-select>
                    </b-field>
                </div>

                <b-field :label="this.$t('registrations.registration_number')" custom-class="required"
                         :message="[
                                { [this.$t('registrations.registration_number_required')]: !$v.form.registration_number.required && $v.form.registration_number.$error },
                             ]"
                         :type="{'is-danger': $v.form.registration_number.$error}">
                    <b-input maxlength="255" v-model.trim="$v.form.registration_number.$model"></b-input>
                </b-field>

                <b-field v-if="showAdditionalInputs" :label="this.$t('registrations.registration_year')" custom-class="required"
                         :message="[
                                { [this.$t('registrations.registration_year_invalid')]: (!$v.form.year.numeric || !$v.form.year.minLength || !$v.form.year.maxLength) && $v.form.year.$error },
                                { [this.$t('registrations.registration_year_required')]: !$v.form.year.required && $v.form.year.$error },
                             ]"
                         :type="{'is-danger': $v.form.year.$error}">
                    <b-input maxlength="4" v-model.trim="$v.form.year.$model"></b-input>
                </b-field>

                <div class="field">
                    <b-checkbox v-model.trim="form.breeding_available">{{ this.$t('registrations.breeding_available') }}</b-checkbox>
                </div>

                <b-field :label="this.$t('registrations.breeding_limitation')">
                    <b-input maxlength="255" v-model.trim="$v.form.breeding_limitation.$model" :disabled="!form.breeding_available"></b-input>
                </b-field>

                <section id="animal-registration-buttons">
                    <b-button
                        native="submit"
                        id="submit-button"
                        type="is-link"
                        :loading="isSubmitting"
                        @click.prevent="submitRegistration"
                        :disabled="isFormInvalid"
                    >
                        {{ this.$t('animals.create.save') }}
                    </b-button>
                </section>
            </section>
        </div>
    </section>
</template>

<script>
    import {maxLength, minLength, numeric, required, requiredIf} from 'vuelidate/lib/validators';
    import {validationMixin} from 'vuelidate';

    import Loading from 'vue-loading-overlay';
    import HasPermission from "../../../../mixins/HasPermission";
    import HasRole from "../../../../mixins/HasRole";

    export default {
        name: "AnimalRegistrationsModal",
        components: {
            Loading,
        },
        props: {
            animalId: Number,
            registrationId: Number,
            user: Object,
        },
        mixins: [validationMixin, HasPermission, HasRole],
        data() {
            return {
                form: {
                    animal_id: this.animalId,
                    club: null,
                    type: null,
                    registration_number: null,
                    year: null,
                    breeding_available: false,
                    breeding_limitation: null,
                },
                clubs: {
                    data: [],
                    isLoading: false,
                },
                registration: {
                    data: [],
                    isLoading: true,
                },
                types: {
                    data: [],
                    isLoading: false,
                },
                isSubmitting: false,
            }
        },
        computed: {
            canRegisterUnderCZKP() {
                if (this.hasRole('admin')) {
                    return true;
                }

                return this.hasPermission('modify czkp registration');
            },
            isEditMode() {
                return this.registrationId;
            },
            isFormInvalid() {
                return this.$v.form.$invalid;
            },
            modalHeader() {
                return this.isEditMode ? this.$t('registrations.edit_registration') : this.$t('registrations.add_registration');
            },
            showAdditionalInputs() {
                return this.$v.form.club.$model === 'CZKP';
            }
        },
        methods: {
            async getClubs() {
                this.clubs.isLoading = true;

                try {
                    const response = await axios.get(`/api/animals/${this.animalId}/registrations/availableClubs`);
                    this.clubs.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('registrations.clubs_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.clubs.isLoading = false;
                }
            },
            async getTypes() {
                this.types.isLoading = true;

                try {
                    const response = await axios.get(`/api/animalregistrations/types`);
                    this.types.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('registrations.types_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.types.isLoading = false;
                }
            },
            async loadRegistration() {
                this.registration.isLoading = true;

                try {
                    const response = await axios.get(`/api/animalregistrations/${this.registrationId}`);
                    this.registration.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('registrations.load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.registration.isLoading = false;
                }
            },
            fillOutForm() {
                if (this.isEditMode) {
                    this.form = this.registration.data;

                    // Convert int to boolean
                    this.form.breeding_available = Boolean(this.registration.data.breeding_available);
                } else {
                    // Club and type should be pre-selected
                    for (const club of Object.keys(this.clubs.data)) {
                        if (!this.canRegisterUnderCZKP && club === 'CZKP') {
                            continue;
                        }

                        if (!this.clubs.data[club]) {
                            this.$v.form.club.$model = club;
                            break;
                        }
                    }

                    this.$v.form.type.$model = this.types.data[0];
                }
            },
            async createRegistration() {
                this.isSubmitting = true;

                try {
                    await axios.post('/api/animalregistrations', this.form);
                    this.$buefy.toast.open({ message: this.$t('registrations.registration_created'), type: 'is-success' });
                    this.$parent.close();

                    this.$emit('registrationAdded');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('registrations.registration_create_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            },
            async updateRegistration() {
                this.isSubmitting = true;

                try {
                    await axios.put(`/api/animalregistrations/${this.form.id}`, this.form);
                    this.$buefy.toast.open({ message: this.$t('registrations.registration_updated'), type: 'is-success' });
                    this.$parent.close();

                    this.$emit('registrationUpdated');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('registrations.registration_update_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            },
            async submitRegistration() {
                // Clear out data we do not want to send
                if (this.$v.form.club.$model !== 'CZKP') {
                    this.$v.form.type.$model = null;
                    this.$v.form.year.$model = null;
                }

                if (this.isEditMode) {
                    this.updateRegistration();
                } else {
                    this.createRegistration();
                }
            }
        },
        watch: {
            'form.breeding_available'(newValue) {
                if (!newValue) {
                    this.$v.form.breeding_limitation.$model = null;
                }
            }
        },
        async created() {
            await this.getClubs();
            await this.getTypes();

            if (this.isEditMode) {
                await this.loadRegistration();
            }

            this.fillOutForm();
        },
        validations: {
            form: {
                club: {
                    required,
                    validOption(option) {
                        for (const club of Object.keys(this.clubs.data)) {
                            if (club === option) {
                                return true;
                            }
                        }

                        return false;
                    }
                },
                type: {
                    required: requiredIf(function(){
                        return this.showAdditionalInputs;
                    }),
                    validOption(option) {
                        if (!this.showAdditionalInputs) {
                            return true;
                        }

                        for (const type of this.types.data) {
                            if (type === option) {
                                return true;
                            }
                        }

                        return false;
                    }
                },
                registration_number: {
                    required,
                    maxLength: maxLength(255),
                },
                year: {
                    required: requiredIf(function(){
                        return this.showAdditionalInputs;
                    }),
                    minLength: minLength(4),
                    maxLength: maxLength(4),
                    numeric,
                },
                breeding_limitation: {
                    maxLength: maxLength(255),
                }
            }
        },
    }
</script>

<style lang="scss">
    #animal-registration-modal {
        background: white;

        #animal-registration-content {
            padding: 20px 60px;

            #animal-registration-section {
                display: flex;
                flex-direction: column;

                .is-grouped {
                    width: 100%;
                    justify-content: space-between !important;
                }
            }

            #submit-button {
                margin-top: 20px;
            }
        }
    }

    .required:after {
        content: " *";
        color: red;
        vertical-align: top;
    }
</style>
