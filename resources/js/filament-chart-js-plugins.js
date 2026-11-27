/**
 * Self-contained Chart.js `datalabels` plugin for Filament v5 ChartWidget.
 *
 * Filament v5 pre-bundles Chart.js v4.5.1 inside an isolated Alpine component module,
 * so external `chartjs-plugin-datalabels` bundles a duplicate Chart.js instance whose
 * `el instanceof BarElement` check always fails and falls back to broken coordinates.
 * Reading element geometry (`x`, `y`, `base`, `horizontal`) directly from Filament's
 * live chart instance avoids any `instanceof` mismatch and removes 70+ KB of duplicate JS.
 */
const datalabelsPlugin = {
    id: 'datalabels',
    afterDatasetsDraw(chart, _args, pluginOptions) {
        if (! pluginOptions || pluginOptions.display === false) {
            return;
        }

        const { ctx, chartArea } = chart;
        if (! ctx || ! chartArea) {
            return;
        }

        const defaultFontFamily = chart.options?.font?.family || 'sans-serif';
        const defaultColor = chart.options?.color || '#334155';

        chart.data.datasets.forEach((dataset, datasetIndex) => {
            if (! chart.isDatasetVisible(datasetIndex) || dataset.datalabels === false) {
                return;
            }

            const meta = chart.getDatasetMeta(datasetIndex);
            if (! meta || ! Array.isArray(meta.data)) {
                return;
            }

            const dsOpts =
                dataset.datalabels && typeof dataset.datalabels === 'object'
                    ? { ...pluginOptions, ...dataset.datalabels }
                    : pluginOptions;

            meta.data.forEach((element, dataIndex) => {
                if (! element || element.skip || ! chart.getDataVisibility(dataIndex)) {
                    return;
                }

                const rawValue = dataset.data?.[dataIndex];
                const context = {
                    active: false,
                    chart,
                    dataIndex,
                    dataset,
                    datasetIndex,
                };

                const resolveOpt = (opt, fallback) =>
                    typeof opt === 'function' ? opt(context) : (opt ?? fallback);

                if (! resolveOpt(dsOpts.display, true)) {
                    return;
                }

                const formatted =
                    typeof dsOpts.formatter === 'function'
                        ? dsOpts.formatter(rawValue, context)
                        : rawValue;

                if (formatted === null || formatted === undefined || formatted === '') {
                    return;
                }

                const text = String(formatted);
                const fontCfg = dsOpts.font && typeof dsOpts.font === 'object' ? dsOpts.font : {};
                const fontStyle = resolveOpt(fontCfg.style, 'normal');
                const fontWeight = resolveOpt(fontCfg.weight, '600');
                const fontSize = Number(resolveOpt(fontCfg.size, 11)) || 11;
                const fontFamily = resolveOpt(fontCfg.family, defaultFontFamily);
                const color = resolveOpt(dsOpts.color, defaultColor);
                const anchor = resolveOpt(dsOpts.anchor, 'end');
                const align = resolveOpt(dsOpts.align, 'top');
                const offset = Number(resolveOpt(dsOpts.offset, 4)) || 0;
                const clamp = Boolean(resolveOpt(dsOpts.clamp, false));

                const props =
                    typeof element.getProps === 'function'
                        ? element.getProps(['x', 'y', 'base'], false)
                        : element;

                const elX = props?.x ?? element.x;
                const elY = props?.y ?? element.y;
                const elBase = props?.base ?? element.base;
                const isHorizontal = Boolean(element.horizontal);

                if (! Number.isFinite(elX) || ! Number.isFinite(elY)) {
                    return;
                }

                let drawX = elX;
                let drawY = elY;
                let textAlign = 'center';
                let textBaseline = 'middle';

                if (typeof elBase === 'number' && Number.isFinite(elBase)) {
                    if (isHorizontal) {
                        const startX = elBase;
                        const endX = elX;
                        const minX = Math.min(startX, endX);
                        const maxX = Math.max(startX, endX);
                        const anchorX =
                            anchor === 'start'
                                ? startX
                                : anchor === 'center'
                                  ? (minX + maxX) / 2
                                  : endX;

                        drawY = elY;
                        textBaseline = 'middle';

                        if (align === 'right' || align === 'end') {
                            drawX = anchorX + offset;
                            textAlign = 'left';
                        } else if (align === 'left' || align === 'start') {
                            drawX = anchorX - offset;
                            textAlign = 'right';
                        } else {
                            drawX = anchorX;
                            textAlign = 'center';
                        }
                    } else {
                        const startY = elBase;
                        const endY = elY;
                        const minY = Math.min(startY, endY);
                        const maxY = Math.max(startY, endY);
                        const anchorY =
                            anchor === 'start'
                                ? startY
                                : anchor === 'center'
                                  ? (minY + maxY) / 2
                                  : endY;

                        drawX = elX;
                        textAlign = 'center';

                        if (align === 'top' || align === 'end') {
                            drawY = anchorY - offset;
                            textBaseline = 'bottom';
                        } else if (align === 'bottom' || align === 'start') {
                            drawY = anchorY + offset;
                            textBaseline = 'top';
                        } else {
                            drawY = anchorY;
                            textBaseline = 'middle';
                        }

                        if (clamp && textBaseline === 'bottom') {
                            drawY = Math.max(fontSize + 2, drawY);
                        }
                    }
                } else if (typeof element.tooltipPosition === 'function') {
                    const pos = element.tooltipPosition(false);
                    drawX = pos.x;
                    drawY = pos.y - offset;
                    textAlign = 'center';
                    textBaseline = 'bottom';
                }

                ctx.save();
                ctx.font = `${fontStyle} ${fontWeight} ${fontSize}px ${fontFamily}`;
                ctx.fillStyle = color;
                ctx.textAlign = textAlign;
                ctx.textBaseline = textBaseline;
                ctx.fillText(text, drawX, drawY);
                ctx.restore();
            });
        });
    },
};

window.filamentChartJsPlugins ??= [];

if (! window.filamentChartJsPlugins.some((plugin) => plugin?.id === datalabelsPlugin.id)) {
    window.filamentChartJsPlugins.push(datalabelsPlugin);
}

// Safeguard: Ensure Livewire endpoints never use plain HTTP when site is served over HTTPS
if (typeof window !== 'undefined') {
    const ensureLivewireHttps = () => {
        if (window.location?.protocol === 'https:' && window.livewireScriptConfig) {
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
