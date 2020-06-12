<template>
    <section id="new-approval-request-modal">
        <header class="modal-card-head">
            <p class="modal-card-title has-text-centered">{{ this.$t('litter.requests.modal.send_new_approval_request') }}</p>
        </header>

        <section id="new-approval-request-modal-content">

            <b-field :label="this.$t('litter.requests.modal.note_to_registrator')">
                <text-area :max-length="500" v-model="form.creator_note"></text-area>
            </b-field>

            <section id="new-approval-request-modal-buttons">
                <b-button
                    native="submit"
                    id="submit-button"
                    type="is-link"
                    :loading="isSubmitting"
                    @click.prevent="submitNewApprovalRequest"
                >
                    {{ this.$t('litter.requests.modal.save') }}
                </b-button>
            </section>
        </section>
    </section>
</template>

<script>
    import TextArea from "../../../common/TextArea";

    export default {
        name: "NewApprovalRequestModal",
        components: {TextArea},
        props: {
            litterId: Number,
        },
        data() {
            return {
                form: {
                    litter_id: this.litterId,
                    state: 'Sent',
                    creator_note: null,
                },
                isSubmitting: false,
            }
        },
        methods: {
            async submitNewApprovalRequest() {
                this.isSubmitting = true;

                try {
                    await axios.post('/api/litterapprovalrequests/', this.form);
                    this.$buefy.toast.open({ message: this.$t('litter.requests.modal.request_sent'), type: 'is-success' });

                    // Emit message, so we can reload litter requests table
                    this.$emit('submit');

                    this.$parent.close();
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('litter.requests.modal.request_sent_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isSubmitting = false;
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    #new-approval-request-modal {
        background: white;

        #new-approval-request-modal-content {
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

