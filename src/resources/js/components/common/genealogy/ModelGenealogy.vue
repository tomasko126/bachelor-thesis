<template>
    <section id="genealogy" class="vld-parent">
        <loading :active.sync="animals.isLoading" :is-full-page="false"></loading>

        <header id="genealogy-header">
            <h2 class="is-size-4">{{ this.$t('common.genealogy.genealogy') }}</h2>
        </header>

        <div v-if="!animals.isLoading" id="genealogy-content" class="columns is-mobile is-gapless parents">
            <genealogy :animal="animals.data" :counter="counter"></genealogy>
        </div>
    </section>
</template>

<script>
    import Genealogy from "./Genealogy";
    import Loading from 'vue-loading-overlay';

    export default {
        name: "ModelGenealogy",
        components: {
            Genealogy,
            Loading,
        },
        props: {
            modelId: Number,
            modelName: String,
        },
        data() {
            return {
                animals: {
                    data: {},
                    isLoading: false,
                },
                counter: 0,
            }
        },
        methods: {
            async loadModelGenealogy() {
                this.animals.isLoading = true;

                try {
                    const response = await axios.get(`/api/${this.modelName}/${this.modelId}/genealogy`);
                    this.animals.data = response.data;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('common.genealogy.genealogy_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.animals.isLoading = false;
                }
            }
        },
        async created() {
            await this.loadModelGenealogy();
        }
    }
</script>

<style lang="scss" scoped>
    #genealogy {
        #genealogy-content {
            margin-top: 10px;
            align-items: center;

            div {
                height: 100%;
            }
        }
    }
</style>
