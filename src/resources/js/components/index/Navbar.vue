<template>
    <b-navbar type="is-link">
        <template slot="brand">
            <b-navbar-item tag="strong" class="pointer" @click="$router.push({ name: 'animals' })">
                {{ this.$t('index.name') }}
            </b-navbar-item>
        </template>

        <template slot="start">
            <b-navbar-item tag="router-link" :to="{ name: 'animals' }">
                {{ this.$t('index.animals') }}
            </b-navbar-item>
            <b-navbar-dropdown :label="this.$t('index.litters')">
                <b-navbar-item tag="router-link" :to="{ name: 'litters' }">
                    {{ this.$t('index.show_all_litters') }}
                </b-navbar-item>
                <b-navbar-item v-if="canSeeLitterRequests" tag="router-link" :to="{ name: 'litters', query: { state: 'Sent' } }">
                    {{ this.$t('index.show_pending_litters') }}
                </b-navbar-item>
            </b-navbar-dropdown>
            <b-navbar-item v-if="canSeeUsers" tag="router-link" :to="{ name: 'users' }">
                {{ this.$t('index.users') }}
            </b-navbar-item>
            <b-navbar-item class="is-hidden-desktop">
                <b-field>
                    <b-radio-button v-model="language"
                                    :expanded="true"
                                    native-value="ces"
                                    @input="setAppLanguage"
                    >
                        🇨🇿
                    </b-radio-button>
                    <b-radio-button v-model="language"
                                    :expanded="true"
                                    native-value="eng"
                                    @input="setAppLanguage"
                    >
                        🇬🇧
                    </b-radio-button>
                </b-field>
            </b-navbar-item>
            <b-navbar-item class="is-hidden-desktop" @click="logout">
                {{ this.$t('index.logout') }}
            </b-navbar-item>
        </template>

        <template slot="end">
            <b-dropdown
                position="is-bottom-left"
                append-to-body
                aria-role="menu"
                class="is-hidden-mobile"
            >
                <a
                    class="navbar-item"
                    slot="trigger"
                    role="button">
                    <b-icon icon="bars"></b-icon>
                </a>
                <b-dropdown-item aria-role="menuitem">
                    {{ this.$t('index.logged_as') }}: <b>{{ userName }}</b>
                </b-dropdown-item>
                <hr class="dropdown-divider" />
                <b-dropdown-item aria-role="menuitem">
                    <b-field>
                        <b-radio-button v-model="language"
                                        :expanded="true"
                                        native-value="ces"
                                        @input="setAppLanguage"
                        >
                            🇨🇿
                        </b-radio-button>
                        <b-radio-button v-model="language"
                                        :expanded="true"
                                        native-value="eng"
                                        @input="setAppLanguage"
                        >
                            🇬🇧
                        </b-radio-button>
                    </b-field>
                </b-dropdown-item>
                <hr class="dropdown-divider" />
                <b-dropdown-item aria-role="menuitem" @click="logout">
                    <b-icon icon="sign-out-alt"></b-icon>
                    {{ this.$t('index.logout') }}
                </b-dropdown-item>
            </b-dropdown>
        </template>
    </b-navbar>
</template>

<script>
    import HasRole from "../../mixins/HasRole";
    import HasPermission from "../../mixins/HasPermission";

    export default {
        name: "Navbar",
        props: {
            user: Object,
        },
        mixins: [HasRole, HasPermission],
        data() {
            return {
                language: null,
            }
        },
        computed: {
            canSeeLitterRequests() {
                if (this.hasRole('admin')) {
                    return true;
                }

                return this.hasPermission('see litter requests');
            },
            canSeeUsers() {
                return this.hasRole('admin');
            },
            userName() {
                return this.user?.name ?? '';
            }
        },
        methods: {
            loadAppLanguage() {
                if (this.$cookies.isKey('lang')) {
                    this.language = this.$cookies.get('lang');
                } else {
                    this.language = 'ces';
                }

                if (this.language !== this.$i18n.locale) {
                    this.setAppLanguage();
                }
            },
            setAppLanguage() {
                this.$cookies.set('lang', this.language, -1);

                this.$i18n.locale = this.language;
            },
            async logout() {
                try {
                    await axios.post('/logout');

                    this.$buefy.toast.open({
                        message: this.$t('index.loggingOutSuccess'),
                        type: 'is-success'
                    });

                    window.location.href = '/';
                } catch (e) {
                    this.$buefy.toast.open({
                        message: this.$t('index.loggingOutFail'),
                        type: 'is-danger'
                    });
                }
            }
        },
        mounted() {
            this.loadAppLanguage();
        }
    }
</script>

<style lang="scss" scoped>
    .pointer {
        cursor: pointer;
    }
</style>
