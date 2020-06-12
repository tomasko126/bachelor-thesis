<template>
    <div id="litter-form" class="columns section">
        <left-panel :is-form="true"></left-panel>

        <main id="litter-form-content" class="column vld-parent">
            <loading v-if="isEditMode" :active.sync="litter.isLoading" :is-full-page="false"></loading>

            <h1 class="is-size-3 has-text-centered">{{ pageHeader }}</h1>

            <div id="litter-form-sections" class="is-flex">
                <section class="litter-section">
                    <h2 class="is-size-4" id="basic">{{ this.$t('litters.create.basic') }}</h2>

                    <b-field :label="this.$t('litters.create.type')" custom-class="required"
                             :message="[
                                { [this.$t('litters.create.type_required')]: !$v.form.type.required && $v.form.type.$error },
                             ]"
                             :type="{ 'is-danger': !$v.form.type.required && $v.form.type.$error }"
                    >
                        <b-select :placeholder="this.$t('litters.create.select_type')" v-model="$v.form.type.$model">
                            <option value="VP">VP</option>
                            <option value="PP">PP</option>
                            <option value="NV">NV</option>
                        </b-select>
                    </b-field>

                    <b-field :label="this.$t('litters.create.label')" custom-class="required"
                             :message="[
                                { [this.$t('litters.create.label_required')]: !$v.form.label.required && $v.form.label.$error },
                             ]"
                             :type="{ 'is-danger': $v.form.label.$error }"
                    >
                        <b-input maxlength="255" v-model.trim="$v.form.label.$model"></b-input>
                    </b-field>

                    <b-field v-if="canAddOwner" :label="this.$t('litters.create.owner')" custom-class="required"
                             :message="[
                                { [this.$t('litters.create.owner_required')]: !$v.owners.input.required && $v.owners.input.$error },
                                { [this.$t('litters.create.owner_invalid')]: !$v.owners.input.validOption && $v.owners.input.$error },
                             ]"
                             :type="{'is-danger': $v.owners.input.$error }"
                    >
                        <b-autocomplete
                            :clearable="true"
                            :data="owners.data"
                            :placeholder="this.$t('litters.create.owner_placeholder')"
                            field="name"
                            :keep-first="true"
                            :open-on-focus="true"
                            :loading="owners.isLoading"
                            @typing="debounceOwners"
                            @select="option => owners.selected = option"
                            v-model="$v.owners.input.$model"
                        >
                            <template slot="empty">{{ this.$t('common.no_results') }}</template>
                        </b-autocomplete>
                    </b-field>

                    <b-field :label="this.$t('litters.create.birthdate')">
                        <b-input icon="calendar-alt" :placeholder="this.$t('litters.create.type_birthdate')" maxlength="255" v-model.trim="form.birthdate"></b-input>
                    </b-field>
                </section>
                <section class="litter-section">
                    <h3 class="is-size-5" id="parents">{{ this.$t('litters.create.parents') }}</h3>
                    <div class="is-divider"></div>

                    <b-field :label="this.$t('litters.create.mother')"
                             :message="[
                                { [this.$t('litters.create.mother_invalid')]: !$v.mothers.input.validOption && $v.mothers.input.$invalid },
                             ]"
                             :type="{'is-danger': $v.mothers.input.$invalid }"
                    >
                        <b-autocomplete
                            :clearable="true"
                            :data="mothers.data"
                            :placeholder="this.$t('litters.create.mother_placeholder')"
                            field="name"
                            :keep-first="true"
                            :loading="mothers.isLoading"
                            :open-on-focus="true"
                            @select="option => mothers.selected = option"
                            @typing="debounceMothers"
                            v-model="mothers.input"
                        >
                            <template slot="empty">{{ this.$t('common.no_results') }}</template>
                        </b-autocomplete>
                    </b-field>

                    <b-field :label="this.$t('litters.create.father')"
                             :message="[
                                { [this.$t('litters.create.father_invalid')]: !$v.fathers.input.validOption && $v.fathers.input.$invalid },
                             ]"
                             :type="{'is-danger': $v.fathers.input.$invalid }"
                    >
                        <b-autocomplete
                            :clearable="true"
                            :data="fathers.data"
                            :placeholder="this.$t('litters.create.father_placeholder')"
                            field="name"
                            :keep-first="true"
                            :loading="fathers.isLoading"
                            :open-on-focus="true"
                            @select="option => fathers.selected = option"
                            @typing="debounceFathers"
                            v-model="fathers.input"
                        >
                            <template slot="empty">{{ this.$t('common.no_results') }}</template>
                        </b-autocomplete>
                    </b-field>
                </section>

                <section class="litter-section">
                    <h2 class="is-size-4" id="details">{{ this.$t('litters.create.details') }}</h2>
                    <div class="is-divider"></div>

                    <b-field :label="this.$t('litters.create.line')">
                        <b-input maxlength="255" v-model.trim="form.line"></b-input>
                    </b-field>

                    <b-field :label="this.$t('litters.create.genetic_information')">
                        <b-input maxlength="255" v-model.trim="form.genetic_information"></b-input>
                    </b-field>
                </section>

                <section class="litter-section">
                    <h3 class="is-size-5" id="babies">{{ this.$t('litters.create.babies') }}</h3>
                    <div class="is-divider"></div>

                    <b-field :label="this.$t('litters.create.babies_born')" custom-class="required"
                             :message="[
                                { [this.$t('litters.create.babies_born_required')]: !$v.form.babies_born.required && $v.form.babies_born.$error },
                             ]"
                             :type="{'is-danger': $v.form.babies_born.$error }"
                    >
                        <b-numberinput :expanded="true" min="0" controls-position="compact" v-model.trim="$v.form.babies_born.$model"></b-numberinput>
                    </b-field>

                    <b-field :label="this.$t('litters.create.babies_reared')" custom-class="required"
                             :message="[
                                { [this.$t('litters.create.babies_reared_required')]: !$v.form.babies_reared.required && $v.form.babies_reared.$error },
                             ]"
                             :type="{'is-danger': $v.form.babies_reared.$error }"
                    >
                        <b-numberinput :expanded="true" min="0" controls-position="compact" v-model.trim="$v.form.babies_reared.$model"></b-numberinput>
                    </b-field>

                    <b-field :label="this.$t('litters.create.reared_boys')" custom-class="required"
                             :message="[
                                { [this.$t('litters.create.reared_boys_required')]: !$v.form.reared_boys.required && $v.form.reared_boys.$error },
                             ]"
                             :type="{'is-danger': $v.form.reared_boys.$error }"
                    >
                        <b-numberinput :expanded="true" min="0" controls-position="compact" v-model="$v.form.reared_boys.$model"></b-numberinput>
                    </b-field>

                    <b-field :label="this.$t('litters.create.reared_girls')" custom-class="required"
                             :message="[
                                { [this.$t('litters.create.reared_girls_required')]: !$v.form.reared_girls.required && $v.form.reared_girls.$error },
                             ]"
                             :type="{'is-danger': $v.form.reared_girls.$error }"
                    >
                        <b-numberinput :expanded="true" min="0" controls-position="compact" v-model="$v.form.reared_girls.$model"></b-numberinput>
                    </b-field>

                    <b-field :label="this.$t('litters.create.babies_for_breeding')">
                        <b-numberinput :expanded="true" min="0" controls-position="compact" v-model="form.for_breeding"></b-numberinput>
                    </b-field>

                    <b-field :label="this.$t('litters.create.babies_for_petting')">
                        <b-numberinput :expanded="true" min="0" controls-position="compact" v-model="form.for_petting"></b-numberinput>
                    </b-field>
                </section>

                <section class="litter-section">
                    <h3 class="is-size-5" id="breeder">
                        {{ this.$t('litters.create.breeder') }}
                        <b-tooltip type="is-primary" :label="this.$t('litters.create.breeder_info_shown_on_docs')">
                            <b-icon size="is-small" icon="info-circle"></b-icon>
                        </b-tooltip>
                    </h3>
                    <div class="is-divider"></div>
                    <b-field :label="this.$t('litters.create.breeder_name')">
                        <b-input maxlength="255" v-model.trim="form.breeder_name"></b-input>
                    </b-field>

                    <b-field :label="this.$t('litters.create.breeder_contact')">
                        <b-input maxlength="255" v-model.trim="form.breeder_contact"></b-input>
                    </b-field>
                </section>

                <section class="litter-buttons">
                    <b-button
                        native="submit"
                        type="is-link"
                        :loading="isSubmitting"
                        @click.prevent="submitLitter"
                        :disabled="isFormInvalid"
                    >
                        {{ this.$t('animals.create.save') }}
                    </b-button>
                </section>
            </div>
        </main>

        <aside class="column is-one-fifth"></aside>
    </div>
</template>

<script>
    import Loading from 'vue-loading-overlay';
    import LeftPanel from '../index/LeftPanel';

    import debounce from "lodash/debounce";
    import {validationMixin} from 'vuelidate';
    import {required, requiredIf} from 'vuelidate/lib/validators';
    import permissionsMixin from "../../../mixins/PermissionsMixin";
    import HasPermission from "../../../mixins/HasPermission";
    import HasRole from "../../../mixins/HasRole";
    import {hasRightTo} from "../../../functions/shared";

    export default {
        name: "LitterForm",
        components: {
            LeftPanel, Loading,
        },
        mixins: [validationMixin, permissionsMixin, HasPermission, HasRole],
        data() {
            return {
                form: {
                    type: null,
                    label: null,
                    owner_id: null,
                    birthdate: null,
                    mother_id: null,
                    father_id: null,
                    line: null,
                    genetic_information: null,
                    babies_born: null,
                    babies_reared: null,
                    reared_boys: null,
                    reared_girls: null,
                    for_breeding: null,
                    for_petting: null,
                    breeder_name: null,
                    breeder_contact: null,
                },
                litter: {
                    data: [],
                    isLoading: false,
                },
                fathers: {
                    data: [],
                    isLoading: false,
                    input: null,
                    selected: {},
                },
                mothers: {
                    data: [],
                    isLoading: false,
                    input: null,
                    selected: {},
                },
                owners: {
                    data: [],
                    isLoading: false,
                    input: null,
                    selected: {},
                },
                isSubmitting: false,
            }
        },
        computed: {
            canAddOwner() {
                if (this.hasRole('admin')) {
                    return true;
                }

                return this.hasPermission('add foreign owner to litter');
            },
            isEditMode() {
                return !isNaN(this.litterId);
            },
            isFormInvalid() {
                return this.$v.$invalid;
            },
            litterId() {
                return Number(this.$route.params.litter);
            },
            pageHeader() {
                return this.isEditMode ? this.$t('litters.create.edit_litter') : this.$t('litters.create.create_litter');
            }
        },
        methods: {
            debounceOwners: debounce(function (input) {
                if (!input) {
                    this.owners.data = [];
                    return;
                }

                this.loadOwners();
            }, 500),
            debounceFathers: debounce(function (input) {
                if (!input) {
                    this.fathers.data = [];
                    return;
                }

                this.loadFathers();
            }, 500),
            debounceMothers: debounce(function (input) {
                if (!input) {
                    this.mothers.data = [];
                    return;
                }

                this.loadMothers();
            }, 500),
            async loadLitter() {
                this.litter.isLoading = true;
                this.litter.data = [];

                try {
                    const response = await axios.get(`/api/litters/${this.litterId}`);
                    this.litter.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litters.create.litter_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.litter.isLoading = false;
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
                    this.$buefy.toast.open({ message: this.$t('litters.create.users_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.owners.isLoading = false;
                }
            },
            async loadFathers() {
                this.fathers.isLoading = true;
                this.fathers.data = [];

                try {
                    const response = await axios.get('/api/animals/search', {
                        params: {
                            keyword: this.fathers.input,
                            sort_field: 'name',
                            sort_order: 'asc',
                            column: 'all',
                            sex: 'Male',
                        },
                    });

                    this.fathers.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litters.create.fathers_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.fathers.isLoading = false;
                }
            },
            async loadMothers() {
                this.mothers.isLoading = true;
                this.mothers.data = [];

                try {
                    const response = await axios.get('/api/animals/search', {
                        params: {
                            keyword: this.mothers.input,
                            sort_field: 'name',
                            sort_order: 'asc',
                            column: 'all',
                            sex: 'Female'
                        },
                    });

                    this.mothers.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litters.create.mothers_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.mothers.isLoading = false;
                }
            },
            async createLitter() {
                this.isSubmitting = true;

                try {
                    await axios.post('/api/litters', this.form);
                    this.$buefy.toast.open({ message: this.$t('litters.create.litter_created'), type: 'is-success' });
                    setTimeout(() => {
                        this.$router.push({ name: 'litters' })
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litters.create.litter_created_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            },
            async updateLitter() {
                this.isSubmitting = true;

                try {
                    await axios.put(`/api/litters/${this.litterId}`, this.form);
                    this.$buefy.toast.open({ message: this.$t('litters.create.litter_updated'), type: 'is-success' });
                    setTimeout(() => {
                        this.$router.push({ name: 'litter', params: { litter: this.litterId } })
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litters.create.litter_updated_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            },
            async submitLitter() {
                // Fill ids to the form object
                this.fillIds();

                if (!this.isEditMode) {
                    this.createLitter();
                } else {
                    this.updateLitter();
                }
            },
            fillIds() {
                this.form.owner_id = this.owners.selected?.id ?? null;
                this.form.father_id = this.fathers.selected?.id ?? null;
                this.form.mother_id = this.mothers.selected?.id ?? null;
            },
            fillOutForm() {
                // Fill out form data with the given litter
                for (const key of Object.keys(this.form)) {
                    if (this.litter.data.hasOwnProperty(key)) {
                        this.form[key] = this.litter.data[key];
                    }
                }

                // Fill out form with names
                this.owners.selected = this.litter.data.owner ?? {};
                this.owners.input = this.litter.data.owner?.name ?? '';

                this.fathers.selected = this.litter.data.father ?? {};
                this.fathers.input = this.litter.data.father?.name ?? '';

                this.mothers.selected = this.litter.data.mother ?? {};
                this.mothers.input = this.litter.data.mother?.name ?? '';
            },
            async loadData() {
                if (this.isEditMode) {
                    await this.loadLitter();
                    this.fillOutForm();
                }
            }
        },
        async beforeRouteEnter (to, from, next) {
            // Anyone can create new litter
            if (to.name === 'litters.create') {
                next();
                return;
            }

            // Check, if we have permission to edit the litter
            const routerData = { to, from, next};
            const litterId = to.params?.litter;

            const apiData = { ability: 'update', model: 'Litter', model_id: litterId};

            await hasRightTo(routerData, apiData);
        },
        async created() {
            await this.loadData();
        },
        validations: {
            form: {
                type: {
                    required,
                },
                label: {
                    required,
                },
                babies_born: {
                    required,
                },
                babies_reared: {
                    required,
                },
                reared_boys: {
                    required,
                },
                reared_girls: {
                    required,
                }
            },
            owners: {
                input: {
                    required: requiredIf(function() {
                        return this.canAddOwner;
                    }),
                    validOption(name) {
                        if (!name) {
                            if (this.canAddOwner) {
                                return false;
                            }
                            return true;
                        }

                        if (!this.owners.selected || this.owners.selected.name !== name) {
                            return false;
                        }

                        return true;
                    }
                }
            },
            mothers: {
                input: {
                    validOption(name) {
                        if (!name) {
                            return true;
                        }

                        if (!this.mothers.selected || this.mothers.selected.name !== name) {
                            return false;
                        }

                        return true;
                    }
                }
            },
            fathers: {
                input: {
                    validOption(name) {
                        if (!name) {
                            return true;
                        }

                        if (!this.fathers.selected || this.fathers.selected.name !== name) {
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
    #litter-form {
        display: flex;

        #litter-form-content {
            min-width: 475px;
            margin: 0 auto;

            #litter-form-sections {
                flex-direction: column;
                max-width: 450px;
                margin: 0 auto;

                .litter-section {
                    margin-bottom: 40px;

                    h2, h3 {
                        margin-bottom: 10px;
                    }

                    h2 + h3 {
                        margin: 10px 0 10px 0;
                    }

                    .suboptions {
                        padding-left: 5px;
                    }

                    .is-grouped {
                        width: 100%;
                        justify-content: space-between !important;
                    }
                }
            }

            .litter-buttons {
                display: flex;
                justify-content: center;
            }

            .required:after {
                content: " *";
                color: red;
            }

            .is-divider {
                margin: 1rem 0 !important;
            }
        }
    }
</style>

