<template>
    <b-message :type="noteType" class="vld-parent note">
        <header v-if="!isNewNote && !isSubmitting" class="note-header">
            <p class="note-author">{{ noteAuthor }}:</p>
            <b-icon class="note-visibility" v-if="" :icon="isPublic ? 'globe' : 'lock'" size="is-small"></b-icon>
        </header>

        <text-area :max-length="500" :disabled="!isEditing || isSubmitting" v-model="form.note"></text-area>

        <div class="note-buttons">
            <div class="left-box">
                <div v-if="!isEditing" class="note-timestamps">
                    <p class="is-size-7">{{ this.$t('notes.created') }}: {{ createdAt }}</p>
                    <p v-if="createdAt !== updatedAt" class="is-size-7">{{ this.$t('notes.updated') }}: {{ updatedAt }}</p>
                </div>

                <b-tooltip v-if="isEditing" :label="this.$t('notes.note_type')">
                    <b-select :placeholder="this.$t('notes.select_note_type')" required v-model="form.category">
                        <option value="general">{{ this.$t('notes.general') }}</option>
                        <option value="warning">{{ this.$t('notes.warning') }}</option>
                        <option value="alert">{{ this.$t('notes.alert') }}</option>
                    </b-select>
                </b-tooltip>
            </div>

            <div class="right-box">
                <b-tooltip v-if="isEditing" :label="this.$t('notes.save')">
                    <b-button icon-left="save" :loading="isSaving" :disabled="isSubmitting || !this.canSave" @click="onSave" type="is-primary"></b-button>
                </b-tooltip>

                <b-tooltip v-if="canEdit && !isEditing" :label="this.$t('notes.edit')">
                    <b-button type="is-info" class="edit-button" :disabled="isSubmitting" @click="onEdit" icon-right="pen-square" />
                </b-tooltip>

                <b-tooltip v-if="isEditing" :label="this.$t('notes.note_visibility')">
                    <b-dropdown v-model="form.public" aria-role="list" :disabled="isSubmitting">
                        <button class="button is-primary" type="button" slot="trigger">
                            <template v-if="isPublic">
                                <b-icon icon="globe"></b-icon>
                                <span>{{ this.$t('notes.public') }}</span>
                            </template>
                            <template v-else>
                                <b-icon icon="lock"></b-icon>
                                <span>{{ this.$t('notes.private') }}</span>
                            </template>
                            <b-icon icon="caret-down"></b-icon>
                        </button>

                        <b-dropdown-item :value="true" aria-role="listitem">
                            <div class="media">
                                <b-icon class="media-left" icon="globe"></b-icon>
                                <div class="media-content">
                                    <h3>{{ this.$t('notes.public') }}</h3>
                                    <small>{{ this.$t('notes.everyone_can_see') }}</small>
                                </div>
                            </div>
                        </b-dropdown-item>

                        <b-dropdown-item :value="false" aria-role="listitem">
                            <div class="media">
                                <b-icon class="media-left" icon="lock"></b-icon>
                                <div class="media-content">
                                    <h3>{{ this.$t('notes.private') }}</h3>
                                    <small>{{ this.$t('notes.only_you_can_see') }}</small>
                                </div>
                            </div>
                        </b-dropdown-item>
                    </b-dropdown>
                </b-tooltip>

                <b-tooltip v-if="isEditing" :label="this.$t('notes.delete')">
                    <b-button type="is-danger" icon-left="trash" :disabled="isSubmitting" @click="onDelete"></b-button>
                </b-tooltip>
            </div>
        </div>
    </b-message>
</template>

<script>
    import HasPermission from "../../mixins/HasPermission";
    import HasRole from "../../mixins/HasRole";
    import {formatDateTime} from "../../functions/shared";
    import TextArea from "./TextArea";

    export default {
        name: "Note",
        components: {
            TextArea,
        },
        mixins: [HasPermission, HasRole],
        props: {
            data: Object,
            user: Object,
        },
        data() {
           return {
               form: {
                   id: null,
                   animal_id: null,
                   litter_id: null,
                   note: null,
                   category: null,
                   public: null,
               },
               isEditing: false,
               isSaving: false,
               isSubmitting: false,
               isNewNote: null,
           }
        },
        computed: {
            createdAt() {
                return formatDateTime(this.data?.created_at);
            },
            updatedAt() {
                return formatDateTime(this.data?.updated_at);
            },
            canEdit() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.data?.creator?.id !== this.user?.id) {
                    return false;
                }

                return true;
            },
            canSave() {
                return this.form?.note;
            },
            isPublic() {
                return this.form?.public;
            },
            noteAuthor() {
                return this.data?.creator?.name ?? '';
            },
            noteText() {
                return this.form?.note;
            },
            noteType() {
                switch (this.form?.category) {
                    case 'general': return 'is-success';
                    case 'warning': return 'is-warning';
                    case 'alert': return 'is-danger';
                    default: return 'info';
                }
            }
        },
        methods: {
            onEdit() {
                this.isEditing = true;
            },
            onDelete() {
                this.$buefy.dialog.confirm({
                    title: this.$t('notes.deleting_note'),
                    message: this.$t('notes.delete_note_message'),
                    confirmText: this.$t('notes.delete_note'),
                    type: 'is-danger',
                    hasIcon: true,
                    onConfirm: async () => {
                        this.deleteNote();
                    }
                });
            },
            async onSave() {
                this.isSaving = true;

                // Create note
                if (this.isNewNote) {
                    await this.createNote();
                } else {
                    // Update note
                    await this.updateNote();
                }

                //this.setHeight();
                this.isSaving = false;
            },
            async createNote() {
                this.isSubmitting = true;
                const url = '/api/notes';

                try {
                    await axios.post(url, this.form);
                    this.$buefy.toast.open({ message: this.$t('notes.note_created'), type: 'is-success' });

                    this.isEditing = false;
                    this.setNoteAsOld();

                    this.$emit('noteCreated');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('notes.note_create_fail'), type: 'is-danger' });
                } finally {
                    this.isSubmitting = false;
                }
            },
            async updateNote() {
                this.isSubmitting = true;
                const url = `/api/notes/${this.form.id}`;

                try {
                    await axios.put(url, this.form);
                    this.$buefy.toast.open({ message: this.$t('notes.note_updated'), type: 'is-success' });

                    this.isEditing = false;
                    this.setNoteAsOld();

                    this.$emit('noteUpdated');
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('notes.note_update_fail'), type: 'is-danger' });
                } finally {
                    this.isSubmitting = false;
                }
            },
            async deleteNote() {
                if (!this.isNewNote) {
                    this.isSubmitting = true;
                    const url = `/api/notes/${this.form.id}`;

                    try {
                        await axios.delete(url);
                        this.$buefy.toast.open({ message: this.$t('notes.note_deleted'), type: 'is-success' });
                        this.isEditing = false;
                        this.$emit('noteDeleted', this.form.id);
                    } catch (e) {
                        this.$buefy.toast.open({ message: this.$t('notes.note_delete_fail'), type: 'is-danger' });
                    } finally {
                        this.isSubmitting = false;
                    }
                } else {
                    this.$buefy.toast.open({ message: this.$t('notes.note_deleted'), type: 'is-success' });
                    this.$emit('noteDeleted', this.form.id);
                }
            },
            setNoteAsOld() {
                this.isNewNote = false;
            },
            fillOutForm() {
                // Fill out form data with the given note
                for (const key of Object.keys(this.form)) {
                    if (this.data.hasOwnProperty(key)) {
                        this.form[key] = this.data[key];
                    }
                }

                // Set the note status
                this.isNewNote = this.data.temp;
            }
        },
        created() {
            // Fill out form's data
            this.fillOutForm();
        },
        mounted() {
            // Set edit mode for new note
            if (this.isNewNote) {
                this.isEditing = true;
            }
        },
    }
</script>

<style lang="scss" scoped>
    .note {
        margin: 10px 0;

        .note-header {
            color: initial;
            display: flex;
            align-items: center;
            justify-content: space-between;

            .note-visibility {
                width: 40px;
            }
        }

        .note-buttons {
            display: flex;
            justify-content: space-between;
            min-width: 110px;
            margin-top: 10px;
            align-items: flex-end;
            line-height: normal;

            .right-box {
                display: inline-flex;

                * {
                    margin: 0 2px;
                }
            }

            .edit-button {
                margin-top: 2px;
            }
        }

        #notes .message-body .media-content {
            width: 100%;
        }
    }
</style>
