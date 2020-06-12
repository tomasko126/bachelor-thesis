<template>
    <b-dropdown aria-role="list" position="is-bottom-left">
        <button class="button" slot="trigger">
            <b-icon icon="ellipsis-h"></b-icon>
        </button>
        <b-dropdown-item aria-role="listitem" @click="onEdit" :disabled="!canEditOrDeleteRegistration" :focusable="false">
            <b-tooltip :label="this.$t('animal.index.no_permission')" type="is-danger" :active="!canEditOrDeleteRegistration">
                <b-icon icon="edit"></b-icon>
                <span>{{$t('registrations.edit')}}</span>
            </b-tooltip>
        </b-dropdown-item>
        <b-dropdown-item aria-role="listitem" @click="onDelete" :disabled="!canEditOrDeleteRegistration" :focusable="false">
            <b-tooltip :label="this.$t('animal.index.no_permission')" type="is-danger" :active="!canEditOrDeleteRegistration" position="is-bottom">
                <b-icon icon="trash"></b-icon>
                <span>{{$t('registrations.delete')}}</span>
            </b-tooltip>
        </b-dropdown-item>
    </b-dropdown>
</template>

<script>
    import HasPermission from "../../../../mixins/HasPermission";
    import HasRole from "../../../../mixins/HasRole";

    export default {
        name: "AnimalRegistrationsDropdown",
        props: {
            animal: Object,
            registrationId: Number,
            registrationClub: String,
            user: Object,
        },
        mixins: [HasPermission, HasRole],
        computed: {
            canEditOrDeleteRegistration() {
                if (this.hasRole('admin')) {
                    return true;
                }

                // If animal is registrated in CZKP club and user does not have permission to add/edit czkp registrations,
                // he can't edit/delete any CZKP registration
                if (this.registrationClub === 'CZKP' && !this.hasPermission('modify czkp registration')) {
                    return false;
                }

                // If owner of animal is different than the currently logged in user,
                // do not let this user edit or delete registration unless he has a permission to do it
                if (this.user?.id !== this.animal?.owner?.user_id && !this.hasPermission('modify registration to foreign animal')) {
                    return false;
                }

                return true;
            },
        },
        methods: {
            onEdit() {
                this.$emit('openModal', this.registrationId);
            },
            onDelete() {
                this.$buefy.dialog.confirm({
                    title: this.$t('registrations.deleting_registration'),
                    message: this.$t('registrations.delete_registration_message'),
                    confirmText: this.$t('registrations.delete_registration'),
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: async () => {
                        this.deleteRegistration();
                    }
                });
            },
            async deleteRegistration() {
                const url = `/api/animalregistrations/${this.registrationId}`;

                try {
                    await axios.delete(url);
                    this.$buefy.toast.open({ message: this.$t('registrations.deleted'), type: 'is-success' });

                    this.$emit('reloadTable');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('registrations.deleted_fail'), type: 'is-danger' });
                    throw e;
                }
            },
        }
    }
</script>
