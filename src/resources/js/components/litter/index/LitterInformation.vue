<template>
    <div id="litter-info-content" class="column vld-parent">
        <model-deleted-message :model="litter" type="litter" :user="user"></model-deleted-message>

        <h1 class="column is-offset-1 is-size-3 has-text-centered">
            <b-skeleton :active="isLoading" height="48px"></b-skeleton>
            <span v-if="!isLoading">{{ litterHeader }}</span>
        </h1>

        <div id="litter-info-sections" class="column is-flex is-offset-1">
            <section class="litter-section">
                <h2 class="is-size-4" id="basic">{{ this.$t('litter.index.basic') }}</h2>
                <div class="is-divider"></div>
                <div class="columns">
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('litter.index.type') }}: {{ type }}</p>
                        <p v-if="!isLoading">{{ this.$t('litter.index.label') }}: {{ label }}</p>
                        <p v-if="!isLoading">{{ this.$t('litter.index.birthdate') }}: {{ birthdate }}</p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('litter.index.station') }}: {{ station }}</p>
                        <p v-if="!isLoading">{{ this.$t('litter.index.owner') }}: {{ owner }}</p>
                    </div>
                </div>
            </section>
            <section class="litter-section">
                <h3 class="is-size-5 subsection" id="parents">{{ this.$t('litter.index.parents') }}</h3>
                <div class="columns">
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('litter.index.mother') }}:
                            <router-link :to="motherUrl">{{ nameOfMother }}</router-link>
                        </p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('litter.index.father') }}:
                            <router-link :to="fatherUrl">{{ nameOfFather }}</router-link>
                        </p>
                    </div>
                </div>
            </section>
            <section class="litter-section">
                <h2 class="is-size-4" id="details">{{ this.$t('litter.index.details') }}</h2>
                <div class="is-divider"></div>
                <b-skeleton :active="isLoading" width="50%"/>
                <b-skeleton :active="isLoading" width="50%"/>
                <b-skeleton :active="isLoading"/>
                <p v-if="!isLoading">{{ this.$t('litter.index.line') }}: {{ line }}</p>
                <p v-if="!isLoading">{{ this.$t('litter.index.genetic_information') }}: {{ geneticInformation }}</p>
                <p v-if="!isLoading">{{ this.$t('litter.index.varieties') }}: {{ varieties }}</p>
            </section>
            <section class="litter-section">
                <h3 class="is-size-5 subsection" id="babies">{{ this.$t('litter.index.babies') }}</h3>
                <div class="columns">
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('litter.index.babies_born') }}: {{ babiesBorn }}</p>
                        <p v-if="!isLoading">{{ this.$t('litter.index.babies_reared') }}: {{ babiesReared }}</p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('litter.index.boys_reared') }}: {{ boysReared }}</p>
                        <p v-if="!isLoading">{{ this.$t('litter.index.girls_reared') }}: {{ girlsReared }}</p>
                    </div>
                    <div class="column">
                        <b-skeleton :active="isLoading" />
                        <b-skeleton :active="isLoading" />
                        <p v-if="!isLoading">{{ this.$t('litter.index.babies_for_breeding') }}: {{ babiesForBreeding }}</p>
                        <p v-if="!isLoading">{{ this.$t('litter.index.babies_for_petting') }}: {{ babiesForPetting }}</p>
                    </div>
                </div>
            </section>
            <section class="litter-section">
                <h3 class="is-size-5 subsection" id="breeder">{{ this.$t('litter.index.breeder') }}</h3>
                <b-skeleton :active="isLoading" width="50%"/>
                <b-skeleton :active="isLoading" width="50%"/>
                <p v-if="!isLoading">{{ this.$t('litter.index.breeder_name') }}: {{ breederName }}</p>
                <p v-if="!isLoading">{{ this.$t('litter.index.breeder_contact') }}: {{ breederContact }}</p>
            </section>
            <section v-if="showRegistrationsSection && !isLoading" class="litter-section">
                <h3 class="is-size-5 subsection" id="registration">{{ this.$t('litter.index.registration') }}</h3>
                <b-skeleton :active="isLoading" width="50%"/>
                <b-skeleton :active="isLoading" width="50%"/>
                <b-skeleton :active="isLoading" width="50%" />
                <p v-if="!isLoading">{{ this.$t('litter.index.registrator_name') }}: {{ registratorName }}</p>
                <p v-if="!isLoading">{{ this.$t('litter.index.registration_date') }}: {{ registrationDate }}</p>
                <p v-if="!isLoading">{{ this.$t('litter.index.registration_number') }}: {{ registrationNumber }}</p>
            </section>
            <section class="litter-section">
                <litter-animals :litter-id="litterId" :litter="litter" :user="user"></litter-animals>
            </section>
            <section class="litter-section">
                <model-genealogy :model-id="litterId" model-name="litters"></model-genealogy>
            </section>
            <section class="litter-section">
                <litter-approval-requests :litter-id="litterId" :litter="litter" :user="user"></litter-approval-requests>
            </section>
            <section class="litter-section">
                <litter-notes :litter-id="litterId" :litter="litter" :user="user"></litter-notes>
            </section>
            <section class="litter-section">
                <model-history :id="litterId" model="litters"></model-history>
            </section>
        </div>
    </div>
</template>

<script>
    import LitterAnimals from "./animals/LitterAnimals";
    import ModelHistory from "../../common/ModelHistory";
    import LitterNotes from "./notes/LitterNotes";
    import LitterApprovalRequests from "./approval_requests/LitterApprovalRequests";
    import ModelDeletedMessage from "../../common/ModelDeletedMessage";
    import ModelGenealogy from "../../common/genealogy/ModelGenealogy";

    export default {
        name: "LitterInformation",
        props: {
            isLoading: Boolean,
            litterId: Number,
            litter: Object,
            user: Object,
        },
        components: {
            ModelGenealogy,
            LitterApprovalRequests,
            LitterNotes,
            LitterAnimals,
            ModelHistory,
            ModelDeletedMessage
        },
        computed: {
            litterHeader() {
                return `${this.label} - ${this.owner}`;
            },
            type() {
                return this.litter?.type ?? '-';
            },
            label() {
                return this.litter?.label ?? '-';
            },
            station() {
                return this.litter?.owner?.station?.name ?? '-';
            },
            owner() {
                return this.litter?.owner?.name ?? '-';
            },
            birthdate() {
                return this.litter?.birthdate ?? '-';
            },
            nameOfMother() {
                return this.litter?.mother?.name ?? '-';
            },
            nameOfFather() {
                return this.litter?.father?.name ?? '-';
            },
            line() {
                return this.litter?.line ?? '-';
            },
            geneticInformation() {
                return this.litter?.genetic_information ?? '-';
            },
            varieties() {
                return this.litter?.varieties ?? '-';
            },
            registratorName() {
                return this.litter?.latest_approval_request?.registrator?.name ?? '-';
            },
            registrationDate() {
                return this.litter?.latest_approval_request?.registration_date ?? '-';
            },
            registrationNumber() {
                return this.litter?.latest_approval_request?.registration_number ?? '-';
            },
            breederName() {
                return this.litter?.breeder_name ?? '-';
            },
            breederContact() {
                return this.litter?.breeder_contact ?? '-';
            },
            babiesBorn() {
                return this.litter?.babies_born ?? '-';
            },
            babiesReared() {
                return this.litter?.babies_reared ?? '-';
            },
            boysReared() {
                return this.litter?.reared_boys ?? '-';
            },
            girlsReared() {
                return this.litter?.reared_girls ?? '-';
            },
            babiesForBreeding() {
                return this.litter?.for_breeding ?? '-';
            },
            babiesForPetting() {
                return this.litter?.for_petting ?? '-';
            },
            motherUrl() {
                if (this.litter?.mother?.id) {
                    return { name: 'animal', params: { animal: this.litter.mother.id } };
                }

                return { name: 'litter', params: { litter: this.litterId } };
            },
            fatherUrl() {
                if (this.litter?.father?.id) {
                    return { name: 'animal', params: { animal: this.litter.father.id } };
                }

                return { name: 'litter', params: { litter: this.litterId } };
            },
            showRegistrationsSection() {
                if (this.litter?.type === 'VP') {
                    return false;
                }

                return true;
            }
        }
    }
</script>

<style lang="scss" scoped>
    #litter-info-content {
        section {
            margin: 20px 0;
        }

        #litter-info-sections {
            flex-direction: column;

            .litter-section {
                margin-bottom: 15px;

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
