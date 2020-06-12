<template>
    <b-input ref="input" class="textarea-input" :maxlength="maxLength" type="textarea" @input="onInput" :value="value" :disabled="disabled"></b-input>
</template>

<script>
    export default {
        name: "TextArea",
        props: {
            disabled: Boolean,
            maxLength: Number,
            value: String,
        },
        methods: {
            onInput() {
                // Set height of the textarea when input changes
                this.setHeight();

                this.$emit('input', this.$refs.input.$refs.textarea.value);
            },
            setHeight() {
                const textarea = this.$refs.input.$refs.textarea;

                textarea.style.height = 'auto';
                textarea.style.height = Math.max(textarea.scrollHeight, 60) + 'px';
            },
        },
        mounted() {
            // Sets textarea's height (can't be achieved by css :()
            this.setHeight();
        }
    }
</script>

<style lang="scss">
    .textarea-input {
        .textarea {
            background-color: white;
            resize: none;
            border-radius: 5px;
            border: 1px solid #00000029;
            overflow: hidden;
            min-height: 30px !important;

            &[disabled] {
                background-color: unset;
                border: unset;
                color: black;
                cursor: initial;
            }
        }
    }
</style>
