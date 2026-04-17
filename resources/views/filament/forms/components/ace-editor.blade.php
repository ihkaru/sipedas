<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("entangle('{$getStatePath()}')") }},
            editor: null,
            init() {
                if (typeof ace === 'undefined') {
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.32.7/ace.min.js';
                    document.head.appendChild(script);

                    script.onload = () => this.initEditor();
                } else {
                    this.initEditor();
                }
            },
            initEditor() {
                this.editor = ace.edit(this.$refs.aceEditor);
                this.editor.setTheme('ace/theme/{{ $getTheme() }}');
                this.editor.session.setMode('ace/mode/{{ $getLanguage() }}');
                this.editor.setShowPrintMargin(false);
                this.editor.setOptions({
                    fontSize: '14px',
                    enableBasicAutocompletion: true,
                    enableLiveAutocompletion: true,
                    useWorker: false, // Disable syntax worker for CDN stability
                });

                // Sync Ace -> Livewire
                this.editor.setValue(this.state || '', -1);
                this.editor.on('change', () => {
                    this.state = this.editor.getValue();
                });

                // Sync Livewire -> Ace
                this.$watch('state', (value) => {
                    if (value !== this.editor.getValue()) {
                        this.editor.setValue(value || '', -1);
                    }
                });
            }
        }"
        wire:ignore
    >
        <div
            x-ref="aceEditor"
            class="w-full border rounded-lg shadow-sm"
            style="min-height: {{ $getHeight() }}; font-family: 'JetBrains Mono', 'Fira Code', monospace;"
        ></div>
    </div>
</x-dynamic-component>
