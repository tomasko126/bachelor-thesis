<template>
    <div id="animal-form" class="columns section">
        <left-panel :is-form="true"></left-panel>

        <div id="animal-form-content" class="column vld-parent">
            <loading v-if="isEditMode" :active.sync="animal.isLoading" :is-full-page="false"></loading>

            <h1 class="is-size-3 has-text-centered">{{ pageHeader }}</h1>

            <div id="animal-form-sections" class="is-flex">
                <section class="animal-section">
                    <h2 class="is-size-4" id="basic">{{ this.$t('animal.index.basic') }}</h2>

                    <b-field :label="this.$t('animals.create.name')" custom-class="required"
                             :message="[
                                { [this.$t('animals.create.name_required')]: !$v.form.name.required && $v.form.name.$error },
                             ]"
                             :type="{ 'is-danger': !$v.form.name.required && $v.form.name.$error }"
                    >
                        <b-input :loading="$v.form.name.$pending" maxlength="255" v-model.trim="$v.form.name.$model"></b-input>
                    </b-field>

                    <b-field :label="this.$t('animals.create.nickname')">
                        <b-input maxlength="255" v-model.trim="form.nickname"></b-input>
                    </b-field>

                    <div class="field is-grouped">
                        <b-field :label="this.$t('animals.create.sex')" custom-class="required"
                                 :message="[
                                    { [this.$t('animals.create.sex_required')]: !$v.form.sex.required && $v.form.sex.$error },
                                    { [this.$t('animals.create.sex_invalid')]: !$v.form.sex.validOption && $v.form.sex.$error },
                                 ]"
                                 :type="{'is-danger': $v.form.sex.$error}"
                        >
                            <b-select :placeholder="this.$t('animals.create.sex_select')" v-model.trim="$v.form.sex.$model">
                                <option value="Female">{{ this.$t('animals.create.female') }}</option>
                                <option value="Male">{{ this.$t('animals.create.male') }}</option>
                            </b-select>
                        </b-field>

                        <b-field :label="this.$t('animals.create.birthdate')">
                            <b-input icon="calendar-alt" :placeholder="this.$t('animals.create.type_birthdate')" maxlength="255" v-model.trim="form.birthdate"></b-input>
                        </b-field>
                    </div>
                </section>
                <section class="animal-section">
                    <h2 class="is-size-4" id="details">{{ this.$t('animal.index.details') }}</h2>
                    <h3 class="is-size-5 subsection" id="litter">{{ this.$t('animal.index.litter') }}</h3>
                    <div class="is-divider"></div>

                    <b-field :label="this.$t('animals.create.litter')"
                             :message="[
                                { [this.$t('animals.create.litter_not_exists')]: !$v.litters.input.validOption && $v.litters.input.$invalid }
                             ]"
                             :type="{'is-danger': $v.litters.input.$invalid}">
                        <b-autocomplete
                            :clearable="true"
                            :data="litters.data"
                            :placeholder="this.$t('animals.create.litter_placeholder')"
                            field="label"
                            :keep-first="true"
                            :loading="litters.isLoading"
                            :open-on-focus="true"
                            @select="option => this.updateLitterForm(option)"
                            @typing="debounceLitters"
                            v-model="litters.input"
                        >
                            <template slot="empty">{{ this.$t('common.no_results') }}</template>
                        </b-autocomplete>
                    </b-field>

                    <div class="field is-grouped">
                        <b-field :label="this.$t('animals.create.mother')"
                                 :message="[
                                    { [this.$t('animals.create.mother_not_exists')]: !$v.mothers.input.validOption && $v.mothers.input.$invalid }
                                 ]"
                                 :type="{'is-danger': $v.mothers.input.$invalid}"
                        >
                            <b-autocomplete
                                :clearable="!isLitterSelected"
                                :data="mothers.data"
                                :disabled="isLitterSelected"
                                :placeholder="this.$t('animals.create.mother_placeholder')"
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

                        <b-field :label="this.$t('animals.create.father')"
                                 :message="[
                                    { [this.$t('animals.create.father_not_exists')]: !$v.fathers.input.validOption && $v.fathers.input.$invalid }
                                 ]"
                                 :type="{'is-danger': $v.fathers.input.$invalid}"
                        >
                            <b-autocomplete
                                :clearable="!isLitterSelected"
                                :data="fathers.data"
                                :disabled="isLitterSelected"
                                :placeholder="this.$t('animals.create.father_placeholder')"
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
                    </div>
                </section>
                <section class="animal-section">
                    <h3 class="is-size-5 subsection" id="people">{{ this.$t('animal.index.people') }}</h3>
                    <div class="is-divider"></div>

                    <b-field :label="this.$t('animals.create.breeder')"
                             :message="[
                                { [this.$t('animals.create.breeder_not_exists')]: !$v.breeders.input.validOption && $v.breeders.input.$invalid }
                             ]"
                             :type="{'is-danger': $v.breeders.input.$invalid}">
                        <b-autocomplete
                            :clearable="true"
                            :data="breeders.data"
                            :placeholder="this.$t('animals.create.breeder_placeholder')"
                            field="name"
                            :keep-first="true"
                            :loading="breeders.isLoading"
                            :open-on-focus="true"
                            @select="option => breeders.selected = option"
                            @typing="debounceBreeders"
                            v-model="breeders.input"
                        >
                            <template slot="footer">
                                <a @click="openModal('breeder')">
                                    <span>{{ this.$t('common.add_new_breeder')}}</span>
                                </a>
                            </template>

                            <template slot="empty">{{ this.$t('common.no_results') }}</template>
                        </b-autocomplete>
                    </b-field>

                    <b-field v-if="canAddForeignOwner" :label="this.$t('animals.create.owner')"
                             :message="[
                                { [this.$t('animals.create.owner_not_exists')]: !$v.owners.input.validOption && $v.owners.input.$invalid }
                             ]"
                             :type="{'is-danger': $v.owners.input.$invalid}"
                    >
                        <b-autocomplete
                            :clearable="true"
                            :data="owners.data"
                            :placeholder="this.$t('animals.create.owner_placeholder')"
                            field="name"
                            :keep-first="true"
                            :loading="owners.isLoading"
                            :open-on-focus="true"
                            @select="option => owners.selected = option"
                            @typing="debounceOwners"
                            v-model="owners.input"
                        >
                            <template slot="footer">
                                <a @click="openModal('owner')">
                                    <span>{{ this.$t('common.add_new_owner')}}</span>
                                </a>
                            </template>
                            <template slot="empty">{{ this.$t('common.no_results') }}</template>
                        </b-autocomplete>
                    </b-field>
                </section>
                <section class="animal-section">
                    <h3 class="is-size-5 subsection" id="external-features">{{ this.$t('animal.index.external_features') }}</h3>
                    <div class="is-divider"></div>

                    <div class="field is-grouped">
                        <b-field :label="this.$t('animals.create.eyes_color')" custom-class="required"
                                 :message="[
                                { [this.$t('animals.create.eyes_color_required')]: !$v.form.eyes_color.required && $v.form.eyes_color.$error },
                                { [this.$t('animals.create.eyes_color_not_exists')]: !$v.form.eyes_color.validOption && $v.form.eyes_color.$error },
                             ]"
                                 :type="{'is-danger': $v.form.eyes_color.$error}"
                        >
                            <b-select :disabled="eyeColors.isLoading" :loading="eyeColors.isLoading" :placeholder="this.$t('animals.create.eyes_color_placeholder')" v-model.trim="$v.form.eyes_color.$model">
                                <option
                                    v-for="option of eyeColors.data"
                                    :value="option"
                                    :key="option">
                                    {{ option }}
                                </option>
                            </b-select>
                        </b-field>

                        <b-field :label="this.$t('animals.create.ear_type')" custom-class="required"
                                 :message="[
                                    { [this.$t('animals.create.ear_type_required')]: !$v.form.ear_type.required && $v.form.ear_type.$error },
                                    { [this.$t('animals.create.ear_type_not_exists')]: !$v.form.ear_type.validOption && $v.form.ear_type.$error }
                                 ]"
                                 :type="{'is-danger': $v.form.ear_type.$error}"
                        >
                            <b-select :disabled="earTypes.isLoading" :loading="earTypes.isLoading" :placeholder="this.$t('animals.create.ear_type_placeholder')" v-model.trim="$v.form.ear_type.$model">
                                <option
                                    v-for="option of earTypes.data"
                                    :value="option"
                                    :key="option">
                                    {{ option }}
                                </option>
                            </b-select>
                        </b-field>
                    </div>

                    <b-field :label="this.$t('animals.create.fur_color')" custom-class="required"
                             :message="[
                                 { [this.$t('animals.create.fur_color_required')]: !$v.form.fur_color.required && $v.form.fur_color.$error },
                             ]"
                             :type="{'is-danger': $v.form.fur_color.$error}"
                    >
                        <b-input maxlength="255" v-model.trim="$v.form.fur_color.$model"></b-input>
                    </b-field>

                    <b-field :label="this.$t('animals.create.fur_type')" custom-class="required"
                             :message="[
                                 { [this.$t('animals.create.fur_type_required')]: !$v.form.fur_type.required && $v.form.fur_type.$error },
                             ]"
                             :type="{'is-danger': $v.form.fur_type.$error}"
                    >
                        <b-input maxlength="255" v-model.trim="$v.form.fur_type.$model"></b-input>
                    </b-field>

                    <b-field :label="this.$t('animals.create.markings')" custom-class="required"
                             :message="[
                                 { [this.$t('animals.create.markings_required')]: !$v.form.markings.required && $v.form.markings.$error },
                             ]"
                             :type="{'is-danger': $v.form.markings.$error}"
                    >
                        <b-input maxlength="255" v-model.trim="$v.form.markings.$model"></b-input>
                    </b-field>
                </section>

                <section class="animal-section">
                    <h3 class="is-size-5 subsection" id="breeding-info">{{ this.$t('animal.index.breeding_info') }}</h3>
                    <div class="is-divider"></div>

                    <div class="field">
                        <b-checkbox v-model.trim="form.breeding_available">
                            {{ this.$t('animals.create.breeding_available') }}
                        </b-checkbox>
                    </div>

                    <b-field :label="this.$t('animals.create.breeding_limitation')">
                        <b-input maxlength="255" v-model.trim="form.breeding_limitation"></b-input>
                    </b-field>
                </section>

                <section class="animal-section">
                    <h3 class="is-size-5 subsection" id="death-info">{{ this.$t('animal.index.death_info') }}</h3>
                    <div class="is-divider"></div>

                    <b-field :label="this.$t('animals.create.death_date')">
                        <b-input icon="calendar-alt" :placeholder="this.$t('animals.create.type_death_date')" maxlength="255" v-model.trim="form.death_date"></b-input>
                    </b-field>

                    <b-field :label="this.$t('animals.create.death_reason')">
                        <b-input maxlength="255" v-model.trim="form.death_reason"></b-input>
                    </b-field>
                </section>

                <section class="animal-section">
                    <h2 class="is-size-4" id="owner-info">
                        {{ this.$t('animal.index.owner_info') }}
                        <b-tooltip type="is-primary" :label="this.$t('litters.create.breeder_info_shown_on_docs')">
                            <b-icon size="is-small" icon="info-circle"></b-icon>
                        </b-tooltip>
                    </h2>
                    <div class="is-divider"></div>

                    <b-field :label="this.$t('animals.create.name')">
                        <b-input maxlength="255" v-model.trim="form.owner_name"></b-input>
                    </b-field>

                    <b-field :label="this.$t('animals.create.contact')">
                        <b-input maxlength="255" v-model.trim="form.owner_contact"></b-input>
                    </b-field>

                    <b-field :label="this.$t('animals.create.member_card_number')">
                        <b-input maxlength="255" v-model.trim="form.owner_member_card_number"></b-input>
                    </b-field>
                </section>

                <section class="animal-buttons">
                    <b-button
                        native="submit"
                        type="is-link"
                        :loading="isSubmitting"
                        @click.prevent="submitAnimal"
                        :disabled="isFormInvalid"
                    >
                        {{ this.$t('animals.create.save') }}
                    </b-button>
                </section>

                <b-modal :active.sync="isModalActive"
                         trap-focus
                         aria-role="dialog"
                         aria-modal
                         :width="450"
                >
                    <new-person-modal :person-to-create="personToCreate" @createdPerson="updatePersonField($event)"></new-person-modal>
                </b-modal>
            </div>
        </div>
        <aside class="column is-one-fifth"></aside>
    </div>
</template>

<script>
    import {validationMixin} from 'vuelidate';
    import {maxLength, required} from 'vuelidate/lib/validators';
    import debounce from "lodash/debounce";

    import LeftPanel from "../index/LeftPanel";
    import Loading from 'vue-loading-overlay';
    import HasRole from "../../../mixins/HasRole";
    import HasPermission from "../../../mixins/HasPermission";
    import PermissionsMixin from "../../../mixins/PermissionsMixin";
    import NewPersonModal from "../../common/NewPersonModal";

    import {hasRightTo} from "../../../functions/shared";

    export default {
        name: "AnimalForm",
        mixins: [validationMixin, HasRole, HasPermission, PermissionsMixin],
        components: {
            LeftPanel, Loading, NewPersonModal
        },
        data() {
            return {
                form: {
                    breeder_id: null,
                    owner_id: null,
                    mother_id: null,
                    father_id: null,
                    litter_id: null,
                    name: null,
                    nickname: null,
                    sex: null,
                    birthdate: null,
                    eyes_color: null,
                    ear_type: null,
                    fur_color: null,
                    fur_type: null,
                    markings: null,
                    death_date: null,
                    death_reason: null,
                    owner_name: null,
                    owner_contact: null,
                    owner_member_card_number: null,
                    breeding_available: null,
                    breeding_limitation: null,
                },
                breeders: {
                    data: [],
                    isLoading: false,
                    selected: {},
                    input: null,
                },
                owners: {
                    data: [],
                    isLoading: false,
                    selected: {},
                    input: null,
                },
                litters: {
                    data: [],
                    isLoading: false,
                    selected: {},
                    input: null,
                },
                mothers: {
                    data: [],
                    isLoading: false,
                    selected: {},
                    input: null,
                },
                fathers: {
                    data: [],
                    isLoading: false,
                    selected: {},
                    input: null,
                },
                animal: {
                    data: [],
                    isLoading: true,
                },
                eyeColors: {
                    data: [],
                    isLoading: false,
                },
                earTypes: {
                    data: [],
                    isLoading: false,
                },
                isLoading: false,
                isSubmitting: false,
                isModalActive: false,
                personToCreate: null,
            }
        },
        computed: {
            animalId() {
                return Number(this.$route.params.animal);
            },
            canAddForeignOwner() {
                if (this.role === 'admin') {
                    return true;
                }

                return this.hasPermission('add foreign owner to animal');
            },
            isEditMode() {
                return !isNaN(this.animalId);
            },
            isFormInvalid() {
                return this.$v.$invalid;
            },
            isLitterSelected() {
                return Object.keys(this.litters.selected).length > 0;
            },
            pageHeader() {
                if (this.isEditMode) {
                    return this.$t('animals.create.edit_animal');
                } else {
                    return this.$t('animals.create.create_animal');
                }
            },
        },
        methods: {
            debounceLitters: debounce(function (input) {
                if (!input) {
                    this.litters.data = [];
                    return;
                }

                this.loadLitters();
            }, 500),
            debounceMothers: debounce(function (input) {
                if (!input) {
                    this.mothers.data = [];
                    return;
                }

                this.loadMothers();
            }, 500),
            debounceFathers: debounce(function (input) {
                if (!input) {
                    this.fathers.data = [];
                    return;
                }

                this.loadFathers();
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
            async loadLitters() {
                this.litters.isLoading = true;
                this.litters.data = [];

                try {
                    const response = await axios.get('/api/litters/search', {
                        params: {
                            keyword: this.litters.input,
                            sort_field: 'label',
                            sort_order: 'asc',
                        },
                    });

                    this.litters.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animals.create.litters_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.litters.isLoading = false;
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
                            sex: 'Female',
                        },
                    });

                    this.mothers.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animals.create.mothers_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.mothers.isLoading = false;
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
                    this.$buefy.toast.open({ message: this.$t('animals.create.fathers_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.fathers.isLoading = false;
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
                    this.$buefy.toast.open({ message: this.$t('animals.create.people_not_loaded'), type: 'is-danger' });
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
                    this.$buefy.toast.open({ message: this.$t('animals.create.people_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.owners.isLoading = false;
                }
            },
            async createAnimal() {
                this.isSubmitting = true;

                try {
                    await axios.post('/api/animals', this.form);
                    this.$buefy.toast.open({ message: this.$t('animals.create.animal_created'), type: 'is-success' });
                    setTimeout(() => {
                        this.$router.push({ name: 'animals' })
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animals.create.animal_created_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            },
            async updateAnimal() {
                this.isSubmitting = true;

                try {
                    await axios.put(`/api/animals/${this.animalId}`, this.form);
                    this.$buefy.toast.open({ message: this.$t('animals.create.animal_updated'), type: 'is-success' });
                    setTimeout(() => {
                        this.$router.push({ name: 'animal', params: { animal: this.animalId } } )
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animals.create.animal_updated_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            },
            async submitAnimal() {
                this.fillIds();

                if (!this.isEditMode) {
                    this.createAnimal();
                } else {
                    this.updateAnimal();
                }
            },
            async loadAnimal() {
                this.animal.isLoading = true;
                this.animal.data = [];

                try {
                    const response = await axios.get(`/api/animals/${this.animalId}`);
                    this.animal.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animals.create.animal_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.animal.isLoading = false;
                }
            },
            async loadEarTypes() {
                this.earTypes.isLoading = true;
                this.earTypes.data = [];

                try {
                    const response = await axios.get('/api/animals/earTypes');
                    this.earTypes.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animals.create.ear_types_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.earTypes.isLoading = false;
                }
            },
            async loadEyeColors() {
                this.eyeColors.isLoading = true;
                this.eyeColors.data = [];

                try {
                    const response = await axios.get('/api/animals/eyeColors');
                    this.eyeColors.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animals.create.eye_colors_not_loaded'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.eyeColors.isLoading = false;
                }
            },
            updateLitterForm(selected) {
                if (selected) {
                    this.litters.selected = selected;
                    this.litters.input = selected.label;

                    this.mothers.selected = selected.mother;
                    this.mothers.input = selected.mother.name;

                    this.fathers.selected = selected.father;
                    this.fathers.input = selected.father.name;
                } else {
                    this.litters.selected = {};
                    this.litters.input = '';

                    this.mothers.selected = {};
                    this.mothers.input = '';

                    this.fathers.selected = {};
                    this.fathers.input = '';
                }
            },
            updatePersonField(event) {
                const {personType, person} = event;

                if (personType === 'breeder') {
                    this.breeders.selected = person;
                    this.breeders.input = person.name;
                } else if (personType === 'owner') {
                    this.owners.selected = person;
                    this.owners.input = person.name;
                }
            },
            openModal(person) {
                this.personToCreate = person;
                this.isModalActive = true;
            },
            fillIds() {
                this.form.litter_id = this.litters.selected?.id ?? null;
                this.form.father_id = this.fathers.selected?.id ?? null;
                this.form.mother_id = this.mothers.selected?.id ?? null;
                this.form.breeder_id = this.breeders.selected?.id ?? null;
                this.form.owner_id = this.owners.selected?.id ?? null;
            },
            fillOutForm() {
                // Fill out form data with the given animal
                for (const key of Object.keys(this.form)) {
                    if (this.animal.data.hasOwnProperty(key)) {
                        this.form[key] = this.animal.data[key];
                    }
                }

                // Fill out form with names
                this.litters.selected = this.animal.data.litter ?? {};
                this.litters.input = this.animal.data.litter?.label ?? '';

                this.fathers.selected = this.animal.data.father ?? {};
                this.fathers.input = this.animal.data.father?.name ?? '';

                this.mothers.selected = this.animal.data.mother ?? {};
                this.mothers.input = this.animal.data.mother?.name ?? '';

                this.breeders.selected = this.animal.data.breeder ?? {};
                this.breeders.input = this.animal.data.breeder?.name ?? '';

                this.owners.selected = this.animal.data.owner ?? {};
                this.owners.input = this.animal.data.owner?.name ?? '';

                // Convert int to boolean
                this.form.breeding_available = Boolean(this.animal.data.breeding_available);
            },
            async loadData() {
                await Promise.all([this.loadEyeColors(), this.loadEarTypes()]);

                // If we are editing animal, load animal and fill out the form
                if (this.isEditMode) {
                    await this.loadAnimal();
                    await this.fillOutForm();
                }
            },
        },
        async beforeRouteEnter (to, from, next) {
            // Anyone can create new animal
            if (to.name === 'animals.create') {
                next();
                return;
            }

            // Check, if we have permission to edit the animal
            const routerData = { to, from, next};
            const animalId = to.params?.animal;

            const apiData = { ability: 'update', model: 'Animal', model_id: animalId};

            await hasRightTo(routerData, apiData);
        },
        validations: {
            form: {
                name: {
                    required,
                    maxLength: maxLength(255),
                },
                sex: {
                    required,
                    validOption(option) {
                        return ['Male', 'Female'].includes(option);
                    }
                },
                eyes_color: {
                    required,
                    validOption(option) {
                        return this.eyeColors.data.includes(option);
                    }
                },
                ear_type: {
                    required,
                    validOption(option) {
                        return this.earTypes.data.includes(option);
                    }
                },
                fur_color: {
                    required,
                },
                fur_type: {
                    required,
                },
                markings: {
                    required,
                }
            },
            breeders: {
                input: {
                    validOption(name) {
                        if (!name) {
                            return true;
                        }

                        if (!this.breeders.selected || this.breeders.selected.name !== name) {
                            return false;
                        }

                        return true;
                    }
                }
            },
            owners: {
                input: {
                    validOption(name) {
                        if (!name) {
                            return true;
                        }

                        if (!this.owners.selected || this.owners.selected.name !== name) {
                            return false;
                        }

                        return true;
                    }
                }
            },
            litters: {
                input: {
                    validOption(label) {
                        if (!label) {
                            return true;
                        }

                        if (!this.litters.selected || this.litters.selected.label !== label) {
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
        },
        async created() {
            this.loadData();
        },
    }
</script>

<style lang="scss" scoped>
    #animal-form {
        display: flex;

        #animal-form-content {
            min-width: 475px;
            margin: 0 auto;

            #animal-form-sections {
                flex-direction: column;
                max-width: 450px;
                margin: 0 auto;

                .animal-section {
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

            .animal-buttons {
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
