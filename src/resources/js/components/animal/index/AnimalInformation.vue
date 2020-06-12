<template>
    <div id="animal-info-content" class="column vld-parent">
        <model-deleted-message :model="animal" type="animal" :user="user"></model-deleted-message>

        <h1 class="column is-size-3 is-offset-1 has-text-centered">
            <b-skeleton :active="isLoading" height="48px"></b-skeleton>
            <span v-if="!isLoading">{{ animalName }}</span>
        </h1>

        <div id="animal-info-sections" class="column is-flex is-offset-1">
            <section class="animal-section">
                <h2 class="is-size-4" id="basic">{{ this.$t('animal.index.basic') }}</h2>
                <div class="columns">
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.nickname') }}: {{ nickname }}</p>
                        <p v-if="!isLoading">{{ this.$t('animal.index.sex') }}: {{ sex }}</p>
                        <p v-if="!isLoading">{{ this.$t('animal.index.birthdate') }}: {{ birthdate }}</p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.litter') }}:
                            <router-link :to="litterUrl">{{ litterLabel }}</router-link>
                        </p>
                        <p v-if="!isLoading">{{ this.$t('animal.index.mother') }}:
                            <router-link :to="motherUrl">{{ nameOfMother }}</router-link>
                        </p>
                        <p v-if="!isLoading">{{ this.$t('animal.index.father') }}:
                            <router-link :to="fatherUrl">{{ nameOfFather }}</router-link>
                        </p>
                    </div>
                </div>
            </section>
            <section class="animal-section">
                <h2 class="is-size-4" id="details">{{ this.$t('animal.index.details') }}</h2>
                <h3 class="is-size-5 subsection" id="people">{{ this.$t('animal.index.people') }}</h3>
                <div class="is-divider"></div>
                <div class="columns">
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.owner') }}: {{ ownerName }}</p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.breeder') }}: {{ breederName }}</p>
                    </div>
                </div>
            </section>
            <section class="animal-section">
                <h3 class="is-size-5 subsection" id="external-features">{{ this.$t('animal.index.external_features') }}</h3>
                <div class="is-divider"></div>
                <div class="columns">
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.eyes_color') }}: {{ eyesColor }}</p>
                        <p v-if="!isLoading">{{ this.$t('animal.index.ear_type') }}: {{ earType }}</p>
                        <p v-if="!isLoading">{{ this.$t('animal.index.markings') }}: {{ markings }}</p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.fur_color') }}: {{ furColor }}</p>
                        <p v-if="!isLoading">{{ this.$t('animal.index.fur_type') }}: {{ furType }}</p>
                    </div>
                </div>
            </section>
            <section class="animal-section">
                <h3 class="is-size-5 subsection" id="breeding-info">{{ this.$t('animal.index.breeding_info') }}</h3>
                <div class="is-divider"></div>
                <div class="columns">
                    <div class="column">
                        <b-skeleton :active="isLoading" width="50%" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.breeding_available') }} {{ breedingAvailable }}</p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" width="60%" />
                        <p v-if="!isLoading">{{ this.$t('animal.index.breeding_limitation') }}: {{ breedingLimitation }}</p>
                    </div>
                </div>
            </section>
            <section class="animal-section">
                <h3 class="is-size-5 subsection" id="death-info">{{ this.$t('animal.index.death_info') }}</h3>
                <div class="is-divider"></div>
                <b-skeleton :active="isLoading" width="50%" />
                <b-skeleton :active="isLoading" width="50%" />
                <p v-if="!isLoading">{{ this.$t('animal.index.death_date') }}: {{ deathDate }}</p>
                <p v-if="!isLoading">{{ this.$t('animal.index.death_reason') }}: {{ deathReason }}</p>
            </section>
            <section class="animal-section" v-if="canSeeOwnerSection">
                <h2 class="is-size-4" id="owner-info">{{ this.$t('animal.index.owner_info') }}</h2>
                <b-skeleton :active="isLoading" width="50%" />
                <b-skeleton :active="isLoading" width="50%" />
                <b-skeleton :active="isLoading" width="50%" />
                <p v-if="!isLoading">{{ this.$t('animal.index.name') }}: {{ ownerDocName }}</p>
                <p v-if="!isLoading">{{ this.$t('animal.index.contact') }}: {{ ownerContact }}</p>
                <p v-if="!isLoading">{{ this.$t('animal.index.member_card_number') }}: {{ ownerMemberCardNumber }}</p>
            </section>
            <section class="animal-section">
                <animal-registrations :animal-id="animalId" :animal="animal" :user="user" :key="$route.params.animal"></animal-registrations>
            </section>
            <section class="animal-section">
                <model-genealogy :model-id="animalId" model-name="animals" :key="$route.params.animal"></model-genealogy>
            </section>
            <section class="animal-section">
                <animal-notes :animal-id="animalId" :animal="animal" :user="user" :key="$route.params.animal"></animal-notes>
            </section>
            <section class="animal-section">
                <model-history :id="animalId" model="animals" :key="$route.params.animal"></model-history>
            </section>
        </div>
    </div>
</template>

<script>
    import AnimalNotes from "./notes/AnimalNotes";
    import AnimalRegistrations from "./registrations/AnimalRegistrations";
    import ModelHistory from "../../common/ModelHistory";
    import ModelGenealogy from "../../common/genealogy/ModelGenealogy";

    import ModelDeletedMessage from "../../common/ModelDeletedMessage";
    import HasRole from "../../../mixins/HasRole";
    import HasPermission from "../../../mixins/HasPermission";

    export default {
        name: "AnimalInformation",
        props: {
            animalId: Number,
            animal: Object,
            isLoading: Boolean,
            user: Object,
        },
        components: {
            ModelHistory,
            AnimalNotes,
            AnimalRegistrations,
            ModelDeletedMessage,
            ModelGenealogy,
        },
        mixins: [HasRole, HasPermission],
        computed: {
            animalName() {
                return this.animal?.name ?? '-';
            },
            notes() {
                return this.animal?.notes ?? [];
            },
            nickname() {
                return this.animal?.nickname ?? '-';
            },
            sex() {
                if (!this.animal?.sex) {
                    return '-';
                }

                return this.$t(`animal.index.${this.animal.sex.toLowerCase()}`);
            },
            birthdate() {
                return this.animal?.birthdate ?? '-';
            },
            nameOfMother() {
                return this.animal?.mother?.name ?? '-';
            },
            nameOfFather() {
                return this.animal?.father?.name ?? '-';
            },
            litterLabel() {
                return this.animal?.litter?.label ?? '-';
            },
            breederName() {
                return this.animal?.breeder?.name ?? '-';
            },
            ownerName() {
                return this.animal?.owner?.name ?? '-';
            },
            breedingAvailable() {
                if (this.animal?.hasOwnProperty('breeding_available')) {
                    return this.animal?.breeding_available ? this.$t('animal.index.yes') : this.$t('animal.index.no');
                }

                return '-';
            },
            breedingLimitation() {
                return this.animal?.breeding_limitation ?? '-';
            },
            ownerDocName() {
                return this.animal?.owner_name ?? '-';
            },
            ownerContact() {
                return this.animal?.owner_contact ?? '-';
            },
            ownerMemberCardNumber() {
                return this.animal?.owner_member_card_number ?? '-';
            },
            eyesColor() {
                return this.animal?.eyes_color ?? '-';
            },
            earType() {
                return this.animal?.ear_type ?? '-';
            },
            furColor() {
                return this.animal?.fur_color ?? '-';
            },
            furType() {
                return this.animal?.fur_type ?? '-';
            },
            markings() {
                return this.animal?.markings ?? '-';
            },
            deathDate() {
                return this.animal?.death_date ?? '-';
            },
            deathReason() {
                return this.animal?.death_reason ?? '-';
            },
            litterUrl() {
                if (this.animal?.litter?.id) {
                    return { name: 'litter', params: { litter: this.animal.litter.id } };
                }

                return { name: 'animal', params: { animal: this.animal.id }};
            },
            motherUrl() {
                if (this.animal?.mother_id) {
                    return { name: 'animal', params: { animal: this.animal.mother_id } };
                }

                return { name: 'animal', params: { animal: this.animal.id }};
            },
            fatherUrl() {
                if (this.animal?.father_id) {
                    return { name: 'animal', params: { animal: this.animal.father_id } };
                }

                return { name: 'animal', params: { animal: this.animal.id }};
            },
            canSeeOwnerSection() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.animal?.creator_id === this.user?.id || this.animal?.owner?.user_id === this.user?.id) {
                    return true;
                }

                return this.hasPermission('edit foreign animals');
            }
        }
    }
</script>

<style lang="scss" scoped>
    #animal-info-content {
        section {
            margin: 20px 0;
        }

        .subsection {
            margin: 10px 0;
        }

        #animal-info-sections {
            flex-direction: column;

            .animal-section {
                margin-bottom: 15px;

                h2, h3 {
                    margin-bottom: 10px;
                }

                h2 + h3 {
                    margin: 10px 0 10px 0;
                }
            }
        }

        .is-divider {
            margin: 1rem 0 !important;
        }
    }
</style>
