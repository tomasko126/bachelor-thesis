<template>
    <b-dropdown aria-role="list" position="is-bottom-left">
        <button class="button" slot="trigger">
            <b-icon icon="ellipsis-h"></b-icon>
        </button>
        <b-dropdown-item aria-role="listitem" @click="onEdit" :disabled="!canModifyAnimal">
            <b-icon icon="edit"></b-icon>
            <span>{{$t('animals.index.edit')}}</span>
        </b-dropdown-item>
        <b-dropdown-item aria-role="listitem" @click="onDelete" :disabled="!canModifyAnimal">
            <b-icon icon="trash"></b-icon>
            <span>{{$t('animals.index.delete')}}</span>
        </b-dropdown-item>
    </b-dropdown>
</template>

<script>
    import HasPermission from "../../mixins/HasPermission";
    import HasRole from "../../mixins/HasRole";

    export default {
        name: "AnimalsTableDropdown",
        props: {
            animal: Object,
            animalId: Number,
            user: Object,
        },
        mixins: [HasPermission, HasRole],
        computed: {
            canModifyAnimal() {
                if (this.hasRole('admin')) {
                    return true;
                }

                let hasCZKPRegistration = false;

                if (this.animal?.registrations) {
                    for (const registration of this.animal?.registrations) {
                        if (registration.club === 'CZKP') {
                            hasCZKPRegistration = true;
                            break;
                        }
                    }
                }

                if (hasCZKPRegistration && !this.hasPermission('edit animals with czkp registration')) {
                    return false;
                }

                if (this.animal?.creator_id === this.user?.id || this.animal?.owner?.user_id === this.user?.id) {
                    return true;
                }

                return this.hasPermission('edit foreign animals');
            }
        },
        methods: {
            onEdit() {
                this.$router.push({ name: 'animal.edit', params: { animal: this.animalId } } );
            },
            onDelete() {
                this.$buefy.dialog.confirm({
                    title: this.$t('animal.index.deleting_animal'),
                    message: this.$t('animal.index.delete_animal_message'),
                    confirmText: this.$t('animal.index.delete_animal'),
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: async () => {
                        this.deleteAnimal();
                    }
                });
            },
            async deleteAnimal() {
                const url = `/api/animals/${this.animalId}`;

                try {
                    await axios.delete(url);
                    this.$buefy.toast.open({ message: this.$t('animal.index.animal_deleted'), type: 'is-success' });
                    this.$emit('reloadTable');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animal.index.animal_delete_fail'), type: 'is-danger' });
                }
            },
        }
    }
</script>
