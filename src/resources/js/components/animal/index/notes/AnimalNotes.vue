<template>
    <div id="notes" class="vld-parent">
        <loading :active.sync="isLoading" :is-full-page="false"></loading>

        <div id="notes-header">
            <h2 class="is-size-4">{{ this.$t('animal.index.notes') }}</h2>
            <b-tooltip :label="canAddNewNote ? this.$t('animal.index.add_note') : this.$t('common.no_permission')" :type="canAddNewNote ? 'is-primary' : 'is-danger'">
                <b-button type="is-success" icon-right="plus-circle" :disabled="!canAddNewNote" @click="renderNewNote" />
            </b-tooltip>
        </div>

        <note v-for="note of notes" :key="note.id" :data="note" :user="user" @noteCreated="getNotes" @noteUpdated="getNotes" @noteDeleted="getNotes"></note>

        <template>
            <section class="section" v-if="!notes || !notes.length">
                <div class="content has-text-grey has-text-centered">
                    <p>
                        <b-icon
                            icon="frown"
                            size="is-large">
                        </b-icon>
                    </p>
                    <p>{{ this.$t('common.no_results') }}</p>
                </div>
            </section>
        </template>
    </div>
</template>

<script>
    import Note from '../../../common/Note';
    import Loading from 'vue-loading-overlay';
    import HasRole from "../../../../mixins/HasRole";
    import HasPermission from "../../../../mixins/HasPermission";

    export default {
        name: "AnimalNotes",
        components: {
            Note, Loading
        },
        mixins: [HasRole, HasPermission],
        props: {
            animalId: Number,
            animal: Object,
            user: Object,
        },
        data() {
            return {
                counter: 0,
                isLoading: true,
                notes: null,
            }
        },
        computed: {
            canAddNewNote() {
                if (this.hasRole('admin')) {
                    return true;
                }

                if (this.animal?.creator_id === this.user?.id || this.animal?.owner?.user_id === this.user?.id) {
                    return true;
                }

                return this.hasPermission('add note to foreign animals');
            }
        },
        methods: {
            async getNotes() {
                this.isLoading = true;

                const url = `/api/animals/${this.animalId}/notes`;

                try {
                    const response = await axios.get(url);
                    this.notes = response.data;
                    this.counter = this.notes.length;
                } catch (e) {
                    this.$buefy.toast.open({ message: this.$t('notes.notes_load_fail'), type: 'is-danger' });
                    throw e;
                } finally {
                    this.isLoading = false;
                }
            },
            renderNewNote() {
                ++this.counter;

                const note = {
                    id: this.counter,
                    animal_id: this.animalId,
                    litter_id: null,
                    category: 'general',
                    public: true,
                    note: '',
                    temp: true,
                };

                this.notes.unshift(note);
            },

            onNoteDelete(noteId) {
                for (let i = 0; i<this.notes.length; i++) {
                    if (this.notes[i].id === noteId) {
                        this.notes.splice(i, 1);
                        break;
                    }
                }
            }
        },
        async created() {
            await this.getNotes();
        }
    }
</script>

<style lang="scss" scoped>
    #notes {
        min-height: 200px;

        #notes-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
        }
    }
</style>
