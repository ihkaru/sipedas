import ChartDataLabels from 'chartjs-plugin-datalabels';

window.filamentChartJsPlugins ??= [];
window.filamentChartJsPlugins.push(ChartDataLabels);

// Safeguard: Ensure Livewire endpoints never use plain HTTP when site is served over HTTPS
if (typeof window !== 'undefined') {
    const ensureLivewireHttps = () => {
        if (window.location.protocol === 'https:' && window.livewireScriptConfig) {
            if (window.livewireScriptConfig.uri && window.livewireScriptConfig.uri.startsWith('http:')) {
                window.livewireScriptConfig.uri = window.livewireScriptConfig.uri.replace(/^http:/, 'https:');
            }
            if (window.livewireScriptConfig.moduleUrl && window.livewireScriptConfig.moduleUrl.startsWith('http:')) {
                window.livewireScriptConfig.moduleUrl = window.livewireScriptConfig.moduleUrl.replace(/^http:/, 'https:');
            }
        }
    };
    ensureLivewireHttps();
    document.addEventListener('livewire:init', ensureLivewireHttps);
    document.addEventListener('livewire:navigated', ensureLivewireHttps);
}
