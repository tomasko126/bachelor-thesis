<template>
    <aside id="right-panel" class="column is-2">
        <div id="export-animal-button">
            <b-tooltip :label="this.$t('common.no_permission')" type="is-danger" :active="!canDownloadExportedPDF">
                    <b-button type="is-link" icon-left="download" :loading="isLoading" :disabled="isDownloadButtonDisabled"
                              @click="exportAnimalToPDF"
                    >
                        {{ this.$t('animal.index.export_animal') }}
                    </b-button>
            </b-tooltip>
        </div>
        <div id="action-buttons" class="is-flex">
            <router-link :to='editAnimalUrl'>
                <b-tooltip :label="this.$t('common.no_permission')" type="is-danger" :active="!canModifyAnimal">
                    <b-button type="is-warning" icon-left="edit" :loading="isLoading" :disabled="isButtonDisabled">
                        {{ this.$t('animal.index.edit') }}
                    </b-button>
                </b-tooltip>
            </router-link>
            <b-tooltip v-if="!isDeleted" :label="this.$t('common.no_permission')" type="is-danger" :active="!canModifyAnimal">
                <b-button type="is-danger" icon-left="trash" :loading="isLoading" @click="onDelete" :disabled="isButtonDisabled">
                    {{ this.$t('animal.index.delete') }}
                </b-button>
            </b-tooltip>
            <b-tooltip v-else :label="this.$t('common.no_permission')" type="is-danger" :active="!canModifyAnimal">
                <b-button type="is-link" icon-left="trash-restore-alt" :loading="isLoading" :disabled="isButtonDisabled" @click="restoreAnimal">
                    {{ this.$t('animal.index.restore') }}
                </b-button>
            </b-tooltip>
        </div>
    </aside>
</template>

<script>
    import HasRole from "../../../mixins/HasRole";
    import HasPermission from "../../../mixins/HasPermission";

    export default {
        name: "RightPanel",
        mixins: [HasRole, HasPermission],
        props: {
            animalId: Number,
            animal: Object,
            isLoading: Boolean,
            user: Object,
        },
        computed: {
            canDownloadExportedPDF() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.animal?.creator_id === this.user?.id || this.animal?.owner?.user_id === this.user?.id) {
                    return true;
                }

                return this.hasPermission('download animal summary');
            },
            canModifyAnimal() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.isDeleted) {
                    // Animal has been deleted
                    return false;
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
            },
            canShowRestoreButton() {
                return this.hasRole('admin') && this.isDeleted;
            },
            editAnimalUrl() {
                if (this.canModifyAnimal) {
                    return { name: 'animal.edit', params: { animal: this.animalId } };
                }

                return { name: 'animal', params: { animal: this.animalId } };
            },
            isButtonDisabled() {
                return !this.canModifyAnimal || this.isLoading;
            },
            isDownloadButtonDisabled() {
                return !this.canDownloadExportedPDF || this.isLoading;
            },
            isDeleted() {
                return this.animal?.deleted_at;
            },
        },
        methods: {
            exportAnimalToPDF() {
                window.open(`/animals/${this.animalId}/export`, '_blank');
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
                    setTimeout(() => {
                        this.$router.push({ name: 'animals' })
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animal.index.animal_delete_fail'), type: 'is-danger' });
                }
            },
            async restoreAnimal() {
                const url = `/api/animals/${this.animalId}/restore`;

                try {
                    await axios.put(url);
                    this.$buefy.toast.open({ message: this.$t('animal.index.animal_restored'), type: 'is-success' });
                    setTimeout(() => {
                        this.$router.go();
                    }, 2000);
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('animal.index.animal_restore_fail'), type: 'is-danger' });
                }
            }
        }
    }
</script>

<style lang="scss" scoped>
    #right-panel {
        margin-top: 40px;

        #export-animal-button {
            margin-bottom: 20px;
        }

        #action-buttons {
            justify-content: space-between;
        }
    }

    @media screen and (max-width: 768px) {
        #right-panel {
            margin-top: 0;
        }
    }
</style>
