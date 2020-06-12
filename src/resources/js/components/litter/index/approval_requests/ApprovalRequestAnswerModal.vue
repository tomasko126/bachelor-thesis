<template>
    <section id="approval-request-answer-modal">
        <header class="modal-card-head">
            <p class="modal-card-title has-text-centered">{{ this.$t('litter.requests.modal.answer_to_litter_approval_request') }}</p>
        </header>

        <section id="approval-request-answer-modal-content">
            <b-field :label="this.$t('litter.requests.modal.state')" custom-class="required"
                     :message="
                     [
                         { [this.$t('litter.requests.modal.state_required')]: !$v.form.state.required && $v.form.state.$error },
                     ]"
                     :type="{'is-danger': $v.form.state.$error}"
            >
                <b-select :placeholder="this.$t('litter.requests.modal.select_state')" v-model="$v.form.state.$model">
                    <option value="Approved">{{ this.$t('litter.requests.modal.approved') }}</option>
                    <option value="Rejected">{{ this.$t('litter.requests.modal.rejected') }}</option>
                </b-select>
            </b-field>

            <b-field v-if="isApproved" :label="this.$t('litter.requests.modal.registration_number')" custom-class="required"
                     :message="
                     [
                         { [this.$t('litter.requests.modal.registration_number_required')]: !$v.form.registration_number.required && $v.form.registration_number.$error },
                     ]"
                     :type="{'is-danger': $v.form.registration_number.$error}"
            >
                <b-input maxlength="255" v-model.trim="$v.form.registration_number.$model"></b-input>
            </b-field>

            <b-field v-if="isApproved" :label="this.$t('litter.requests.modal.registration_date')" custom-class="required"
                     :message="
                     [
                         { [this.$t('litter.requests.modal.registration_date_required')]: !$v.form.registration_date.required && $v.form.registration_date.$error },
                     ]"
                     :type="{'is-danger': $v.form.registration_date.$error}"
            >
                <b-input maxlength="10" v-model.trim="$v.form.registration_date.$model"></b-input>
            </b-field>

            <b-field>
                <template slot="label">
                    {{ this.$t('litter.requests.modal.note_to_request') }}
                    <b-tooltip type="is-primary" :label="this.$t('litter.requests.modal.note_to_request_hint')">
                        <b-icon size="is-small" icon="info-circle"></b-icon>
                    </b-tooltip>
                </template>
                <text-area :max-length="500" v-model="form.registrator_note"></text-area>
            </b-field>

            <b-field>
                <template slot="label">
                    {{ this.$t('litter.requests.modal.note_to_litter') }}
                    <b-tooltip type="is-primary" :label="this.$t('litter.requests.modal.note_to_litter_hint')">
                        <b-icon size="is-small" icon="info-circle"></b-icon>
                    </b-tooltip>
                </template>
                <text-area :max-length="255" v-model="form.litter_registrator_note"></text-area>
            </b-field>

            <section id="approval-request-answer-modal-buttons">
                <b-button
                    native="submit"
                    id="submit-button"
                    type="is-link"
                    :loading="isSubmitting"
                    @click.prevent="submitAnswerToRequest"
                    :disabled="isFormInvalid"
                >
                    {{ this.$t('litter.requests.modal.save') }}
                </b-button>
            </section>
        </section>
    </section>
</template>

<script>
    import TextArea from "../../../common/TextArea";

    import {validationMixin} from 'vuelidate';
    import {required, requiredIf} from 'vuelidate/lib/validators';

    export default {
        name: "ApprovalRequestAnswerModal",
        props: {
            litterId: Number,
            requestId: Number,
        },
        components: {
            TextArea,
        },
        mixins: [validationMixin],
        data() {
            return {
                form: {
                    id: this.requestId,
                    litter_id: this.litterId,
                    state: null,
                    registration_number: null,
                    registration_date: null,
                    registrator_note: null,
                    litter_registrator_note: null,
                },
                isSubmitting: false,
            }
        },
        computed: {
            isApproved() {
                return this.form.state === 'Approved';
            },
            isFormInvalid() {
                return this.$v.$invalid;
            },
        },
        methods: {
            async submitAnswerToRequest() {
                this.isSubmitting = true;

                try {
                    await axios.put(`/api/litterapprovalrequests/${this.requestId}`, this.form);
                    this.$buefy.toast.open({ message: this.$t('litter.requests.modal.answer_sent'), type: 'is-success' });

                    this.$router.go();
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litter.requests.modal.answer_sent_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            }
        },
        watch: {
            requestId(newValue) {
                this.form.id = newValue;
            }
        },
        validations: {
            form: {
                state: {
                    required,
                },
                registration_number: {
                    required: requiredIf(function () {
                        return this.isApproved;
                    })
                },
                registration_date: {
                    required: requiredIf(function () {
                        return this.isApproved;
                    })
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    #approval-request-answer-modal {
        background: white;

        #approval-request-answer-modal-content {
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
