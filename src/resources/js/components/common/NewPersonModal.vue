<template>
    <section id="new-person-modal">
        <header class="modal-card-head">
            <p class="modal-card-title has-text-centered">{{ this.$t('common.person_modal.create_person') }}</p>
        </header>

        <section id="new-person-content">
            <b-field :label="this.$t('common.person_modal.name')" custom-class="required">
                <b-input maxlength="255" v-model.trim="$v.form.name.$model"></b-input>
            </b-field>

            <b-field :label="this.$t('common.person_modal.station')"
                     :message="[
                                { [this.$t('common.person_modal.station_not_exists')]: !$v.stations.input.validOption && $v.stations.input.$invalid }
                             ]"
                     :type="{'is-danger': $v.stations.input.$invalid}"
            >
                <b-autocomplete
                    :clearable="true"
                    :data="stations.data"
                    :placeholder="this.$t('common.person_modal.station_placeholder')"
                    field="name"
                    :keep-first="true"
                    :loading="stations.isLoading"
                    :open-on-focus="true"
                    @select="option => stations.selected = option"
                    @typing="debounceStations"
                    v-model="stations.input"
                >
                    <template slot="footer">
                        <a @click="newStationPrompt">
                            <span>{{ this.$t('common.person_modal.add_new_station')}}</span>
                        </a>
                    </template>

                    <template slot="empty">
                        {{ this.$t('common.no_results') }}
                    </template>
                </b-autocomplete>
            </b-field>

            <b-field :label="this.$t('common.person_modal.email')"
                     :message="[
                                { [this.$t('common.person_modal.email_invalid')]: $v.form.email.$invalid }
                             ]"
                     :type="{'is-danger': $v.form.email.$invalid}"
            >
                <b-input maxlength="255" v-model.trim="$v.form.email.$model"></b-input>
            </b-field>

            <b-field :label="this.$t('common.person_modal.telephone_number')">
                <b-input maxlength="255" v-model.trim="form.telephone_number"></b-input>
            </b-field>

            <section id="person-modal-buttons">
                <b-button
                    native="submit"
                    id="submit-button"
                    type="is-link"
                    :loading="isSubmitting"
                    @click.prevent="submitNewPerson"
                    :disabled="isFormInvalid"
                >
                    {{ this.$t('animals.create.save') }}
                </b-button>
            </section>
        </section>
    </section>
</template>

<script>
    import debounce from "lodash/debounce";
    import {validationMixin} from 'vuelidate';
    import {email, required} from 'vuelidate/lib/validators';

    export default {
        name: "NewpersonModal",
        mixins: [validationMixin],
        props: {
            personToCreate: String,
        },
        data() {
            return {
                form: {
                    user_id: null,
                    station_id: null,
                    name: null,
                    email: null,
                    telephone_number: null,
                },
                stations: {
                    data: [],
                    isLoading: false,
                    selected: {},
                    input: null,
                },
                isSubmitting: false,
            }
        },
        computed: {
            isFormInvalid() {
                return this.$v.$invalid;
            }
        },
        methods: {
            debounceStations: debounce(function (input) {
                if (!input) {
                    this.stations.data = [];
                    return;
                }

                this.loadStations();
            }, 500),
            async loadStations() {
                this.stations.isLoading = true;
                this.stations.data = [];

                try {
                    const response = await axios.get('/api/stations/search', {
                        params: {
                            keyword: this.stations.input,
                            sort_field: 'name',
                            sort_order: 'asc',
                        },
                    });

                    this.stations.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('common.person_modal.stations_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.stations.isLoading = false;
                }
            },
            fillIds() {
                this.form.station_id = this.stations.selected?.id ?? null;
            },
            async submitNewPerson() {
                // Fill out id from selected station to the form
                this.fillIds();

                this.isSubmitting = true;

                try {
                    const response = await axios.post('/api/people', this.form);

                    // Emit message with the stored person to the parent element,
                    // so we can update the selected person in some field
                    const storedPerson = response.data;
                    this.$emit('createdPerson', { personType: this.personToCreate, person: storedPerson });

                    this.$buefy.toast.open({ message: this.$t('common.person_modal.person_created'), type: 'is-success' });
                } catch(e) {
                    this.$buefy.toast.open({ message: this.$t('common.person_modal.person_created_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    // Close modal
                    this.$parent.close();
                    this.isSubmitting = false;
                }
            },
            newStationPrompt() {
                this.$buefy.dialog.prompt({
                    message: this.$t('common.person_modal.type_new_station_name'),
                    inputAttrs: {
                        maxlength: 255
                    },
                    trapFocus: true,
                    onConfirm: async (stationName) => await this.createNewStation(stationName)
                });
            },
            async createNewStation(stationName) {
                try {
                    const response = await axios.post('/api/stations', { name: stationName });

                    // Update selected and input fields,
                    // so the created station gets filled into the field
                    this.stations.selected = response.data;
                    this.stations.input = response.data.name;

                    this.$buefy.toast.open({ message: this.$t('common.person_modal.station_created'), type: 'is-success' });
                } catch(e) {
                    this.$buefy.toast.open({ message: this.$t('common.person_modal.station_created_fail'), type: 'is-danger' });
                    throw e;
                }
            }
        },
        validations: {
            form: {
                name: {
                    required,
                },
                email: {
                    email,
                }
            },
            stations: {
                input: {
                    validOption(stationName) {
                        if (!stationName) {
                            return true;
                        }

                        if (!this.stations.selected || this.stations.selected.name !== stationName) {
                            return false;
                        }

                        return true;
                    }
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    #new-person-modal {
        background: white;

        #new-person-content {
            padding: 20px 60px;

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
