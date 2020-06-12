<template>
    <div v-if="animal && counter < counterLimit" class="column is-flex">
        <article v-if="counter !== 0" class="animal" :style="{ 'background-color': color, 'height': height + 'px' }">
            <header class="animal-header">
                <h3 v-if="animalId">
                    <router-link :to="{ name: 'animal', params: { animal: animalId } }">
                        {{ name }}
                    </router-link>
                </h3>
                <h3 v-else>
                    {{ name }}
                </h3>
            </header>
            <div class="animal-content is-size-7">
                <p>{{ this.$t('animal.index.birthdate') }}: {{ birthdate }}</p>
                <p>{{ this.$t('animal.index.eyes_color') }}: {{ eyesColor }}</p>
                <p>{{ this.$t('animal.index.ear_type') }}: {{ earType }}</p>
                <p>{{ this.$t('animal.index.fur_color') }}: {{ furColor }}</p>
                <p>{{ this.$t('animal.index.fur_type') }}: {{ furType }}</p>
                <p>{{ this.$t('animal.index.markings') }}: {{ markings }}</p>
            </div>
        </article>
        <div v-if="animal.father || animal.mother" class="columns is-mobile is-gapless parents" :style="{ 'height': height + 'px'}">
            <genealogy :animal="animal.father" :counter="counter + 1"></genealogy>
            <genealogy :animal="animal.mother" :counter="counter + 1"></genealogy>
        </div>
    </div>
</template>

<script>
    export default {
        name: "Genealogy",
        props: {
            animal: Object,
            counter: Number,
        },
        data() {
            return {
                counterLimit: 4,
                minBaseHeight: 170,
            }
        },
        computed: {
            height() {
                // We want children to have half of the parent size minus additional spacing
                return (this.minBaseHeight * 5) / (2 ** this.counter) - 4;
            },
            birthdate() {
                return this.animal?.animal?.birthdate ?? '-';
            },
            eyesColor() {
                return this.animal?.animal?.eyes_color ?? '-';
            },
            earType() {
                return this.animal?.animal?.ear_type ?? '-';
            },
            furType() {
                return this.animal?.animal?.fur_type ?? '-';
            },
            furColor() {
                return this.animal?.animal?.fur_color ?? '-';
            },
            markings() {
                return this.animal?.animal?.markings ?? '-';
            },
            name() {
                return this.animal?.animal?.name ?? this.$t('animal.index.unknown');
            },
            animalId() {
                return this.animal?.animal?.id ?? null;
            },
            color() {
                const sex = this.animal?.animal?.sex;

                switch (sex) {
                    case 'Male': return '#0000ff17';
                    case 'Female': return '#ff00003b';
                    default: return '#8080803b';
                }
            },
        },
    }
</script>

<style lang="scss" scoped>
    .animal {
        width: 160px;
        margin: 2px;
        box-shadow: 0 0 3px 1px rgba(10, 10, 10, 0.1), 0 0 0 1px rgba(10, 10, 10, 0.1);
        color: #4a4a4a;
        padding: 4px 8px;
        overflow: auto;
        border-radius: 2px;

        .animal-header {
            text-align: center;
        }

        .animal-content {
            margin-top: 5px;
            height: 100%;
        }
    }

    .parents {
        flex-direction: column;
    }

    @media screen and (max-width: 768px) {
        .animal {
            width: 138px;
        }
    }
</style>
