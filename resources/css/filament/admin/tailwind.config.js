import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/filament/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    safelist: [
        // ── Gradient backgrounds (Sync card header & timeline lanes) ──
        'bg-gradient-to-r',
        'from-primary-900', 'from-primary-700', 'to-primary-700',
        'from-primary-50/50', 'from-emerald-50/30', 'from-indigo-50/30',

        // ── Status border-left colors (Kanban & Timeline cards) ──
        'border-l-4',
        'border-l-emerald-500', 'border-l-amber-400', 'border-l-rose-500', 'border-l-slate-300', 'border-l-slate-400',

        // ── Status background tints (cards) ──
        'bg-emerald-50/20', 'bg-amber-50/20', 'bg-rose-50/20',

        // ── Status text colors (light mode) ──
        'text-emerald-800', 'text-amber-800', 'text-rose-800',

        // ── Status text colors (dark mode) ──
        'dark:text-emerald-300', 'dark:text-amber-300', 'dark:text-rose-300',

        // ── Status badge backgrounds ──
        'bg-emerald-100', 'bg-amber-100', 'bg-rose-100',
        'dark:bg-emerald-950/30', 'dark:bg-amber-950/30', 'dark:bg-rose-950/30',

        // ── Status badge text ──
        'dark:text-emerald-400', 'dark:text-amber-400', 'dark:text-rose-400',

        // ── Icon container colors (stats cards) ──
        'bg-emerald-50', 'text-emerald-600',
        'bg-amber-50', 'text-amber-600',
        'dark:bg-emerald-950/30', 'dark:bg-amber-950/30',
        'dark:text-emerald-400', 'dark:text-amber-400',

        // ── Calendar weekend highlight ──
        'bg-rose-50/25', 'dark:bg-rose-950/10',

        // ── Calendar today badge ──
        'bg-primary-600', 'text-white',

        // ── Category chip / more-badge ──
        'bg-primary-50', 'dark:bg-primary-800/30',
        'text-primary-700', 'dark:text-primary-400',
        'border-primary-200', 'dark:border-primary-800',

        // ── Primary text variants ──
        'text-primary-100', 'text-primary-200', 'text-primary-600',
        'hover:bg-primary-50',

        // ── Drawer / backdrop ──
        'bg-slate-950/10', 'bg-slate-950/20', 'bg-slate-950/30',
        'backdrop-blur-xs',

        // ── Overdue pulse ──
        'animate-pulse',

        // ── Border opacity variants ──
        'border-slate-200/50', 'border-slate-200/60',

        // ── Calendar "more" badge text ──
        'text-primary-600', 'dark:text-primary-400',
    ],
}

