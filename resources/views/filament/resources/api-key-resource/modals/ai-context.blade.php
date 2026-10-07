<div
    x-data="{
        copied: false,
        text: @js($context),
        copyToClipboard() {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(this.text).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 3000);
                });
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = this.text;
                textArea.style.position = 'fixed';
                textArea.style.left = '-999999px';
                textArea.style.top = '-999999px';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    this.copied = true;
                    setTimeout(() => this.copied = false, 3000);
                } catch (err) {
                    console.error('Failed to copy', err);
                }
                textArea.remove();
            }
        },
        downloadFile() {
            const blob = new Blob([this.text], { type: 'text/markdown;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'SIPEDAS-AI-CONTEXT-PROMPT.md';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    }"
    class="space-y-4"
>
    <!-- Header Banner & Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-3.5 bg-gray-50 dark:bg-gray-800/80 rounded-xl border border-gray-200 dark:border-gray-700 gap-3">
        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center justify-center p-2 rounded-lg bg-teal-500/10 text-teal-600 dark:text-teal-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </span>
            <div>
                <div class="text-xs font-bold text-gray-800 dark:text-gray-200">
                    Kredensial Aktif: <span class="font-mono text-teal-600 dark:text-teal-400 font-bold">{{ $record->name }}</span>
                </div>
                <div class="text-[11px] text-gray-500 dark:text-gray-400">
                    Token: <code class="font-mono">{{ substr($record->key, 0, 8) }}...{{ substr($record->key, -4) }}</code> &bull; Base URL: <code class="font-mono">{{ url('/api/v1') }}</code>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <!-- Download Button -->
            <button
                type="button"
                @click="downloadFile()"
                class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-white dark:bg-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition shadow-sm"
                title="Unduh sebagai file markdown"
            >
                <svg class="w-3.5 h-3.5 mr-1.5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Unduh .md
            </button>

            <!-- 1-Click Copy Button -->
            <button
                type="button"
                @click="copyToClipboard()"
                class="inline-flex items-center px-4 py-1.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 active:bg-teal-800 dark:bg-teal-500 dark:hover:bg-teal-600 rounded-lg shadow-sm transition focus:outline-none focus:ring-2 focus:ring-teal-500"
            >
                <span x-show="!copied" class="flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" />
                    </svg>
                    Salin Seluruh Konteks (1-Klik)
                </span>
                <span x-show="copied" class="flex items-center text-teal-100 font-bold" style="display: none;">
                    <svg class="w-4 h-4 mr-1.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Tersalin ke Clipboard!
                </span>
            </button>
        </div>
    </div>

    <!-- Token-Efficient Architecture Badges & Tips -->
    <div class="p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl space-y-2">
        <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
            <span class="px-2 py-0.5 rounded-full bg-teal-100 dark:bg-teal-900/60 text-teal-800 dark:text-teal-200 font-bold">
                ⚡ Agent-Native Architecture (Oct 2026)
            </span>
            <span class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 font-semibold">
                ✓ Server-Side Filtering (Bulan, Jenis, Sisa SBML)
            </span>
            <span class="px-2 py-0.5 rounded-full bg-purple-100 dark:purple-900/60 text-purple-800 dark:text-purple-200 font-semibold">
                ✓ Mode ?compact=1 (Hemat 80% Token)
            </span>
            <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/60 text-emerald-800 dark:text-emerald-200 font-semibold">
                ✓ Idempotent Rollback / Undo
            </span>
        </div>
        <div class="text-xs text-amber-800 dark:text-amber-200">
            <span class="font-semibold">💡 Tips Integrasi:</span>
            Salin teks di bawah ini lalu tempelkan (*paste*) langsung pada jendela chat Coding Agent (Claude, Cursor, Windsurf, ChatGPT, atau agent script). Prompt ini telah dipersenjatai panduan efisiensi token, query filter presisi, dan kredensial API Key aktif di atas.
        </div>
    </div>

    <!-- Textarea Monospace Editor -->
    <div class="relative">
        <textarea
            x-ref="contextArea"
            readonly
            rows="23"
            class="w-full p-4 font-mono text-[11px] leading-relaxed bg-gray-900 text-gray-100 dark:bg-gray-950 rounded-xl border border-gray-700/80 focus:ring-2 focus:ring-teal-500 focus:outline-none select-all shadow-inner"
            style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; white-space: pre; tab-size: 2;"
        >{{ $context }}</textarea>
    </div>
</div>
