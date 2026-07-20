<x-filament-panels::page>
    <div class="space-y-6 overflow-hidden flex flex-col h-[calc(100vh-12rem)] relative" id="jadwal-dashboard">
        
        
        <style>
        /* Custom scrollbar */
        .custom-scroll::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }
        .dark .glass-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(51, 65, 85, 0.8);
        }
    </style>
        
        <!-- Welcome Alert & Quick Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 shrink-0">
            <!-- Sync Card -->
            <div class="lg:col-span-2 p-5 bg-gradient-to-r from-primary-900 to-primary-700 dark:from-slate-900 dark:to-slate-800 text-white rounded-2xl flex flex-col justify-between shadow-sm relative overflow-hidden">
                <div class="absolute right-0 bottom-0 top-0 w-1/3 opacity-10 flex items-center justify-center pointer-events-none">
                    <svg class="w-24 h-24 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                </div>
                <div class="relative z-10">
                    <h3 class="font-bold text-lg text-white">Jadwal & Milestone Terpadu</h3>
                    <p class="text-xs text-primary-100 dark:text-slate-300 mt-1">Disinkronkan otomatis dari Google Sheets untuk visualisasi timeline latsar, survei, dan kegiatan.</p>
                </div>
                <div class="mt-4 flex gap-2.5 items-center relative z-10">
                    <button wire:click="sync" wire:loading.attr="disabled" class="flex items-center gap-2 px-4 py-2 bg-white text-primary-700 hover:bg-primary-50 rounded-lg text-xs font-bold shadow-sm transition">
                        <svg class="w-4 h-4 text-primary-700 dark:text-slate-200" wire:loading.class="animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" /></svg>
                        <span wire:loading.remove>Sinkronkan Google Sheets</span>
                        <span wire:loading>Menyinkronkan...</span>
                    </button>
                    <span class="text-[10px] text-primary-200 dark:text-slate-400">Tersinkronisasi otomatis</span>
                </div>
            </div>

            <!-- Stats: Selesai -->
            <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-xs flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-success-50 dark:bg-success-950 text-success-600 dark:text-success-400 flex items-center justify-center text-lg shrink-0">
                    <svg class="w-5 h-5 text-success-600 dark:text-success-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Milestone Selesai</span>
                    <h4 class="text-xl font-bold text-slate-850 dark:text-slate-100 mt-0.5" id="stats-completed-val">0</h4>
                </div>
            </div>

            <!-- Stats: Belum & Overdue -->
            <div class="p-5 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl shadow-xs flex items-center gap-4">
                <div class="w-10 h-10 rounded-xl bg-warning-50 dark:bg-warning-950 text-warning-600 dark:text-warning-400 flex items-center justify-center text-lg shrink-0">
                    <svg class="w-5 h-5 text-warning-600 dark:text-warning-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div class="flex-grow">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Belum Mulai / Overdue</span>
                    <div class="flex items-baseline gap-2 mt-0.5">
                        <h4 class="text-xl font-bold text-slate-850 dark:text-slate-100" id="stats-pending-val">0</h4>
                        <span class="text-[11px] text-danger-500 font-bold" id="stats-overdue-val">(0 Overdue)</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Control Bar -->
        <div class="p-4 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800 rounded-2xl flex flex-col md:flex-row md:items-center justify-between gap-4 shrink-0 shadow-xs">
            <!-- Left: View Switcher -->
            <div class="flex items-center bg-slate-100 dark:bg-slate-800/80 p-1 rounded-xl self-start">
                <button onclick="switchView('calendar')" id="btn-view-calendar" class="view-btn flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition duration-150 bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-xs">
                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" /></svg>
                    <span>Calendar</span>
                </button>
                <button onclick="switchView('timeline')" id="btn-view-timeline" class="view-btn flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition duration-150 text-slate-650 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">
                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 0 0 6 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0 1 18 16.5h-2.25m-7.5 0h7.5m-7.5 0-1 3m8.5-3 1 3m0 0h.5m-.5 0h-9.5m0 0h-.5m3-11.25h1.5m1.5 0h1.5m-3 3h1.5m1.5 0h1.5m-3 3h1.5m1.5 0h1.5" /></svg>
                    <span>Timeline</span>
                </button>
                <button onclick="switchView('kanban')" id="btn-view-kanban" class="view-btn flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition duration-150 text-slate-655 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white">
                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 4.5v15m6-15v15m-12-3h18M3 7.5h18" /></svg>
                    <span>Kanban</span>
                </button>
            </div>

            <!-- Right: Search & Dropdowns -->
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="relative flex-grow md:flex-grow-0 md:w-56">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 pointer-events-none">
                        <svg class="w-3.5 h-3.5 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" /></svg>
                    </span>
                    <input type="text" id="search-input" oninput="applyFilters()" placeholder="Cari kegiatan..." class="w-full pl-8 pr-3 py-1.5 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-lg text-xs focus:outline-none focus:border-primary-500 focus:bg-white dark:focus:bg-slate-900 transition">
                </div>

                <!-- PIC Select -->
                <select id="filter-pic" onchange="applyFilters()" class="text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-primary-500">
                    <option value="">Semua PIC</option>
                    <option value="ketua">Ketua</option>
                    <option value="anggota">Anggota</option>
                </select>

                <!-- Status Select -->
                <select id="filter-status" onchange="applyFilters()" class="text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg px-2.5 py-1.5 focus:outline-none focus:border-primary-500">
                    <option value="">Semua Status</option>
                    <option value="selesai">Selesai</option>
                    <option value="belum">Belum Mulai</option>
                    <option value="overdue">Overdue</option>
                </select>

                <!-- Clear filters button -->
                <button onclick="clearFilters()" class="text-xs text-rose-500 hover:text-rose-700 font-bold px-2 py-1 transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-rose-500 inline mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg> Reset
                </button>
            </div>
        </div>

        <!-- Dynamic Category Chips -->
        <div class="flex flex-wrap items-center gap-2 shrink-0 px-1">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Kategori:</span>
            <div id="filter-categories-container" class="flex flex-wrap gap-1.5">
                <!-- Dynamically generated category chips -->
            </div>
        </div>

        <!-- Visualizations Canvas -->
        <div class="flex-grow overflow-hidden bg-slate-50/50 dark:bg-slate-950/20 border border-slate-200/50 dark:border-slate-800 rounded-2xl relative">
            
            <!-- View 1: Calendar -->
            <div id="view-calendar" class="h-full flex flex-col p-4 overflow-y-auto">
                <div class="flex items-center justify-between mb-3 shrink-0">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-slate-200" id="calendar-month-year">Juli 2026</h3>
                    <div class="flex items-center gap-1.5">
                        <button onclick="prevMonth()" class="w-7 h-7 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-750 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition">
                            <svg class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
                        </button>
                        <button onclick="setToday()" class="px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-750 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-lg text-[10px] font-bold transition">
                            Today
                        </button>
                        <button onclick="nextMonth()" class="w-7 h-7 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-750 hover:bg-slate-50 dark:hover:bg-slate-800 flex items-center justify-center transition">
                            <svg class="w-3.5 h-3.5 text-slate-700 dark:text-slate-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
                        </button>
                    </div>
                </div>

                <div class="flex-grow bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden shadow-xs flex flex-col min-h-[480px]">
                    <div class="grid grid-cols-7 border-b border-slate-250 dark:border-slate-800 bg-slate-50/60 dark:bg-slate-900/50 text-center py-2 text-[10px] font-bold text-slate-500">
                        <div>Minggu</div>
                        <div>Senin</div>
                        <div>Selasa</div>
                        <div>Rabu</div>
                        <div>Kamis</div>
                        <div>Jumat</div>
                        <div>Sabtu</div>
                    </div>
                    <div class="grid grid-cols-7 flex-grow" id="calendar-grid" style="grid-template-rows: repeat(6, 1fr);">
                        <!-- Days generated dynamically -->
                    </div>
                </div>
            </div>

            <!-- View 2: Timeline -->
            <div id="view-timeline" class="p-4 space-y-4 hidden overflow-y-auto h-full custom-scroll">
                <div id="timeline-lanes-container" class="space-y-3.5">
                    <!-- Lanes generated dynamically -->
                </div>
            </div>

            <!-- View 3: Kanban -->
            <div id="view-kanban" class="p-4 h-full flex gap-4 overflow-x-auto select-none hidden custom-scroll">
                <!-- Column: Belum Mulai -->
                <div class="flex-1 min-w-[280px] max-w-[360px] flex flex-col bg-slate-100/50 dark:bg-slate-900/30 rounded-xl border border-slate-200/50 dark:border-slate-800 h-full overflow-hidden">
                    <div class="px-3 py-2.5 flex items-center justify-between border-b border-slate-200 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-warning-400"></span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Belum Mulai</span>
                        </div>
                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-bold text-slate-500" id="kanban-belum-count">0</span>
                    </div>
                    <div class="flex-grow overflow-y-auto p-3 space-y-2.5 custom-scroll" id="kanban-belum"></div>
                </div>

                <!-- Column: Sedang Berjalan -->
                <div class="flex-1 min-w-[280px] max-w-[360px] flex flex-col bg-slate-100/50 dark:bg-slate-900/30 rounded-xl border border-slate-200/50 dark:border-slate-800 h-full overflow-hidden">
                    <div class="px-3 py-2.5 flex items-center justify-between border-b border-slate-200 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Sedang Berjalan</span>
                        </div>
                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-bold text-slate-500" id="kanban-progress-count">0</span>
                    </div>
                    <div class="flex-grow overflow-y-auto p-3 space-y-2.5 custom-scroll" id="kanban-progress"></div>
                </div>

                <!-- Column: Selesai -->
                <div class="flex-1 min-w-[280px] max-w-[360px] flex flex-col bg-slate-100/50 dark:bg-slate-900/30 rounded-xl border border-slate-200/50 dark:border-slate-800 h-full overflow-hidden">
                    <div class="px-3 py-2.5 flex items-center justify-between border-b border-slate-200 dark:border-slate-800 shrink-0 bg-white dark:bg-slate-900">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-success-400"></span>
                            <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">Selesai</span>
                        </div>
                        <span class="px-2 py-0.5 bg-slate-100 dark:bg-slate-800 rounded-full text-[10px] font-bold text-slate-500" id="kanban-selesai-count">0</span>
                    </div>
                    <div class="flex-grow overflow-y-auto p-3 space-y-2.5 custom-scroll" id="kanban-selesai"></div>
                </div>
            </div>

        </div>

        <!-- ================= PANEL KANAN: CONTEXT DRAWER ================= -->
        <div id="context-drawer" class="absolute inset-y-0 right-0 w-full sm:w-80 bg-white dark:bg-slate-900 border-l border-slate-200 dark:border-slate-800 shadow-2xl z-30 translate-x-full transition-transform duration-300 ease-out flex flex-col">
            <!-- Drawer Header -->
            <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50/50 dark:bg-slate-900/50">
                <h3 class="font-bold text-slate-900 dark:text-white flex items-center gap-2 text-xs">
                    <svg class="w-4 h-4 text-primary-500 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 1 1 1.085 1.085l-.041.02H11.25V12.75Zm.75-3.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg> Detail Kegiatan
                </h3>
                <button onclick="closeDrawer()" class="w-7 h-7 rounded-full hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 flex items-center justify-center transition">
                    <svg class="w-4 h-4 text-slate-400 hover:text-slate-650 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <!-- Drawer Content -->
            <div class="flex-grow overflow-y-auto p-5 space-y-5 custom-scroll" id="drawer-content"></div>
        </div>

        <!-- Drawer Backdrop Overlay -->
        <div id="drawer-backdrop" onclick="closeDrawer()" class="absolute inset-0 bg-slate-950/10 dark:bg-slate-950/30 backdrop-blur-xs z-20 hidden transition-opacity duration-350 opacity-0"></div>

    <!-- JS Logic -->
    <script>
        // Initial state loaded from Livewire controller variables
        let rawMilestones = @json($milestones);
        let rawMetrics = @json($metrics);
        let filteredMilestones = [];

        let activeView = 'calendar';
        let currentYear = 2026;
        let currentMonth = 6; // July
        let selectedFilters = {
            categories: new Set(),
            pic: '',
            status: '',
            search: ''
        };

        // Listen for livewire:init and livewire dispatch refresh events
        window.addEventListener('sheets-synced', (e) => {
            rawMilestones = e.detail.milestones;
            rawMetrics = e.detail.metrics;
            applyFilters();
            renderOverview();
        });

        // Initialize view rendering
        document.addEventListener('DOMContentLoaded', () => {
            applyFilters();
            renderOverview();
            renderCategoryFilters();
        });

        // Update statistics counters and categories list
        function renderOverview() {
            let completed = 0;
            let pending = 0;
            let overdue = 0;

            rawMilestones.forEach(m => {
                let st = (m.status || '').toLowerCase();
                if (st === 'selesai') completed++;
                else if (st === 'belum' || st === 'belum mulai') pending++;
                else if (st === 'overdue') overdue++;
            });

            document.getElementById('stats-completed-val').innerText = completed;
            document.getElementById('stats-pending-val').innerText = pending + overdue;
            document.getElementById('stats-overdue-val').innerText = `(${overdue} Overdue)`;
        }

        // Render category filters
        function renderCategoryFilters() {
            let categorySet = new Set();
            rawMilestones.forEach(m => {
                if (m.kategori) categorySet.add(m.kategori);
            });

            let filterContainer = document.getElementById('filter-categories-container');
            filterContainer.innerHTML = '';

            categorySet.forEach(cat => {
                let chip = document.createElement('button');
                chip.id = `chip-cat-${cat}`;
                chip.className = 'px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-[10px] font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition';
                chip.innerText = cat.charAt(0).toUpperCase() + cat.slice(1);
                chip.onclick = () => toggleCategoryFilter(cat);
                filterContainer.appendChild(chip);
            });
        }

        function toggleCategoryFilter(cat) {
            let chip = document.getElementById(`chip-cat-${cat}`);
            if (selectedFilters.categories.has(cat)) {
                selectedFilters.categories.delete(cat);
                chip.className = 'px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-[10px] font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition';
            } else {
                selectedFilters.categories.add(cat);
                chip.className = 'px-2.5 py-1 bg-primary-50 dark:bg-slate-800 border border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-400 rounded-lg text-[10px] font-bold transition';
            }
            applyFilters();
        }

        function clearFilters() {
            selectedFilters.categories.clear();
            selectedFilters.pic = '';
            selectedFilters.status = '';
            selectedFilters.search = '';

            document.getElementById('filter-pic').value = '';
            document.getElementById('filter-status').value = '';
            document.getElementById('search-input').value = '';

            document.querySelectorAll('[id^="chip-cat-"]').forEach(chip => {
                chip.className = 'px-2.5 py-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-[10px] font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 transition';
            });

            applyFilters();
        }

        function switchView(view) {
            activeView = view;
            
            document.getElementById('view-calendar').classList.add('hidden');
            document.getElementById('view-timeline').classList.add('hidden');
            document.getElementById('view-kanban').classList.add('hidden');
            
            document.getElementById(`view-${view}`).classList.remove('hidden');

            document.querySelectorAll('.view-btn').forEach(btn => {
                if (btn.id === `btn-view-${view}`) {
                    btn.className = 'view-btn flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition duration-150 bg-white dark:bg-slate-700 text-slate-800 dark:text-white shadow-xs';
                } else {
                    btn.className = 'view-btn flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition duration-150 text-slate-650 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white';
                }
            });

            renderActiveView();
        }

        function applyFilters() {
            selectedFilters.pic = document.getElementById('filter-pic').value;
            selectedFilters.status = document.getElementById('filter-status').value;
            selectedFilters.search = document.getElementById('search-input').value.toLowerCase();

            filteredMilestones = rawMilestones.filter(m => {
                if (selectedFilters.categories.size > 0 && !selectedFilters.categories.has(m.kategori)) {
                    return false;
                }
                if (selectedFilters.pic && (m.pic || '').toLowerCase() !== selectedFilters.pic.toLowerCase()) {
                    return false;
                }
                if (selectedFilters.status) {
                    let st = (m.status || '').toLowerCase();
                    if (selectedFilters.status === 'belum' && st !== 'belum' && st !== 'belum mulai') return false;
                    if (selectedFilters.status === 'selesai' && st !== 'selesai') return false;
                    if (selectedFilters.status === 'overdue' && st !== 'overdue') return false;
                }
                if (selectedFilters.search) {
                    let searchString = `${m.kegiatan} ${m.activity_id} ${m.kategori} ${m.pic}`.toLowerCase();
                    if (!searchString.includes(selectedFilters.search)) return false;
                }
                return true;
            });

            renderActiveView();
        }

        function renderActiveView() {
            if (activeView === 'calendar') {
                renderCalendar();
            } else if (activeView === 'timeline') {
                renderTimeline();
            } else if (activeView === 'kanban') {
                renderKanban();
            }
        }

        // ================= CALENDAR VIEW =================
        const monthNames = [
            "Januari", "Februari", "Maret", "April", "Mei", "Juni",
            "Juli", "Agustus", "September", "Oktober", "November", "Desember"
        ];

        function renderCalendar() {
            document.getElementById('calendar-month-year').innerText = `${monthNames[currentMonth]} ${currentYear}`;
            
            let grid = document.getElementById('calendar-grid');
            grid.innerHTML = '';

            let firstDayIndex = new Date(currentYear, currentMonth, 1).getDay();
            let totalDays = new Date(currentYear, currentMonth + 1, 0).getDate();
            let prevTotalDays = new Date(currentYear, currentMonth, 0).getDate();

            // Prev month trailing
            for (let i = firstDayIndex; i > 0; i--) {
                let cell = createCalendarCell(prevTotalDays - i + 1, false, true);
                grid.appendChild(cell);
            }

            // Current Month
            for (let day = 1; totalDays >= day; day++) {
                let cell = createCalendarCell(day, true, false);
                grid.appendChild(cell);
            }

            // Next month trailing
            let totalCells = firstDayIndex + totalDays;
            let remainingCells = 42 - totalCells;
            for (let day = 1; remainingCells >= day; day++) {
                let cell = createCalendarCell(day, false, false);
                grid.appendChild(cell);
            }
        }

        function createCalendarCell(dayNum, isCurrentMonth, isPrevMonth) {
            let cell = document.createElement('div');
            cell.className = 'border-r border-b border-slate-200 dark:border-slate-800 p-1.5 min-h-[85px] flex flex-col justify-between transition hover:bg-slate-50/50 dark:hover:bg-slate-800/30 cursor-pointer overflow-hidden';
            
            let targetYear = currentYear;
            let targetMonth = currentMonth;

            if (isPrevMonth) {
                targetMonth = currentMonth - 1;
                if (0 > targetMonth) { targetMonth = 11; targetYear--; }
            } else if (!isCurrentMonth) {
                targetMonth = currentMonth + 1;
                if (targetMonth > 11) { targetMonth = 0; targetYear++; }
            }

            let cellDate = new Date(targetYear, targetMonth, dayNum);
            let isWeekend = cellDate.getDay() === 0 || cellDate.getDay() === 6;
            if (!isCurrentMonth) {
                cell.className += ' bg-slate-50/30 dark:bg-slate-900/10 text-slate-400 dark:text-slate-600';
            } else if (isWeekend) {
                cell.className += ' bg-danger-50 dark:bg-danger-950';
            }

            let dateBadge = document.createElement('div');
            dateBadge.className = 'w-5 h-5 flex items-center justify-center text-[10px] font-bold rounded-full';

            let dateStr = `${targetYear}-${String(targetMonth + 1).padStart(2, '0')}-${String(dayNum).padStart(2, '0')}`;
            dateBadge.innerText = dayNum;
            
            let today = new Date();
            let isToday = today.getFullYear() === targetYear && today.getMonth() === targetMonth && today.getDate() === dayNum;
            if (isToday) {
                dateBadge.className += ' bg-primary-600 text-white shadow-xs';
            }

            let topContainer = document.createElement('div');
            topContainer.className = 'flex justify-between items-start';
            topContainer.appendChild(dateBadge);
            cell.appendChild(topContainer);

            let eventsOnDate = filteredMilestones.filter(m => {
                return m.tanggal && m.tanggal.substring(0, 10) === dateStr;
            });

            let eventsListContainer = document.createElement('div');
            eventsListContainer.className = 'space-y-0.5 mt-1 flex-1 flex flex-col justify-end';

            const statusColors = {
                'selesai': 'bg-success-500',
                'belum': 'bg-amber-400',
                'belum mulai': 'bg-amber-400',
                'overdue': 'bg-danger-500'
            };

            if (eventsOnDate.length > 0) {
                let maxVisible = 2;
                eventsOnDate.slice(0, maxVisible).forEach(evt => {
                    let st = (evt.status || '').toLowerCase();
                    let color = statusColors[st] || 'bg-slate-400';
                    
                    let evtEl = document.createElement('div');
                    evtEl.className = 'text-[9px] py-0.5 px-1 rounded bg-slate-100 dark:bg-slate-800 hover:bg-slate-200/80 dark:hover:bg-slate-700/80 text-slate-700 dark:text-slate-300 transition truncate flex items-center gap-1';
                    evtEl.onclick = (e) => {
                        e.stopPropagation();
                        openDrawer(evt);
                    };
                    evtEl.innerHTML = `
                        \x3cspan class="w-1 h-1 rounded-full shrink-0 ${color}">\x3c/span>
                        \x3cspan class="truncate font-medium">${evt.kegiatan}\x3c/span>
                    `;
                    eventsListContainer.appendChild(evtEl);
                });

                if (eventsOnDate.length > maxVisible) {
                    let moreBadge = document.createElement('div');
                    moreBadge.className = 'text-[8px] font-bold text-primary-600 dark:text-primary-400 bg-primary-50 dark:bg-slate-800 rounded text-center py-0.5';
                    moreBadge.innerText = `+${eventsOnDate.length - maxVisible} lainnya`;
                    moreBadge.onclick = (e) => {
                        e.stopPropagation();
                        openDayListDialog(dateStr, eventsOnDate);
                    };
                    eventsListContainer.appendChild(moreBadge);
                }
            }

            cell.appendChild(eventsListContainer);
            cell.onclick = () => {
                if (eventsOnDate.length > 0) openDayListDialog(dateStr, eventsOnDate);
            };

            return cell;
        }

        function prevMonth() {
            currentMonth--;
            if (0 > currentMonth) { currentMonth = 11; currentYear--; }
            renderCalendar();
        }

        function nextMonth() {
            currentMonth++;
            if (currentMonth > 11) { currentMonth = 0; currentYear++; }
            renderCalendar();
        }

        function setToday() {
            let today = new Date();
            currentYear = today.getFullYear();
            currentMonth = today.getMonth();
            renderCalendar();
        }

        // ================= TIMELINE VIEW =================
        function renderTimeline() {
            let container = document.getElementById('timeline-lanes-container');
            container.innerHTML = '';

            let groups = {};
            filteredMilestones.forEach(m => {
                let actId = m.activity_id || 'Lainnya';
                if (!groups[actId]) groups[actId] = [];
                groups[actId].push(m);
            });

            if (Object.keys(groups).length === 0) {
                container.innerHTML = `
                    \x3cdiv class="text-center py-10 bg-white dark:bg-slate-900 rounded-xl border border-slate-200/60 dark:border-slate-800 text-slate-400 text-xs">
                        \x3csvg class="w-8 h-8 mb-2 opacity-55 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.008 1.24l.885 1.77a2.25 2.25 0 0 0 2.007 1.24h1.98a2.25 2.25 0 0 0 2.007-1.24l.885-1.77a2.25 2.25 0 0 1 2.007-1.24h3.86m-18 0h18M2.25 13.5a2.25 2.25 0 0 1 2.25-2.25h15a2.25 2.25 0 0 1 2.25 2.25m-19.5 0v5.25a2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-18.75V13.5M2.25 13.5V6a2.25 2.25 0 0 1 2.25-2.25h3.75a2.25 2.25 0 0 1 2.25 1.5L12 7.5h7.5a2.25 2.25 0 0 1 2.25 2.25v3.75" />\x3c/svg>
                        \x3cp>Tidak ada data timeline.\x3c/p>
                    \x3c/div>
                `;
                return;
            }

            const laneColors = [
                'from-primary-50 to-white dark:from-gray-900 dark:to-gray-900 border-gray-200 dark:border-gray-800 text-gray-800 dark:text-gray-200',
                'from-success-50 to-white dark:from-gray-900 dark:to-gray-900 border-gray-200 dark:border-gray-800 text-gray-800 dark:text-gray-200',
                'from-info-50 to-white dark:from-gray-900 dark:to-gray-900 border-gray-200 dark:border-gray-800 text-gray-800 dark:text-gray-200',
            ];

            let colorIdx = 0;
            Object.keys(groups).forEach(actId => {
                let items = groups[actId];
                let colorClass = laneColors[colorIdx % laneColors.length];
                colorIdx++;

                let actName = actId;
                if (items[0] && items[0].attributes_json && items[0].attributes_json.activity_name) {
                    actName = items[0].attributes_json.activity_name;
                }

                let lane = document.createElement('div');
                lane.className = `p-4 bg-gradient-to-r rounded-xl border shadow-xs ${colorClass}`;
                
                let laneTitle = document.createElement('div');
                laneTitle.className = 'flex items-center justify-between mb-3 pb-1.5 border-b border-black/5 dark:border-white/5';
                laneTitle.innerHTML = `
                    \x3cdiv class="flex items-center gap-2">
                        \x3csvg class="w-3.5 h-3.5 text-primary-500 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />\x3c/svg>
                        \x3cspan class="font-bold text-xs uppercase tracking-wide">${actName}\x3c/span>
                    \x3c/div>
                    \x3cspan class="text-[10px] font-bold px-2 py-0.5 bg-black/5 dark:bg-white/5 rounded-full">${items.length} milestones\x3c/span>
                `;
                lane.appendChild(laneTitle);

                let timelineFlow = document.createElement('div');
                timelineFlow.className = 'flex gap-3 overflow-x-auto py-1 pr-2 custom-scroll';

                items.sort((a,b) => new Date(a.tanggal) - new Date(b.tanggal));

                const borderColors = {
                    'selesai': 'border-l-success-500 bg-success-50 text-success-800 dark:text-success-300',
                    'belum': 'border-l-warning-400 bg-warning-50 text-warning-800 dark:text-warning-300',
                    'belum mulai': 'border-l-warning-400 bg-warning-50 text-warning-800 dark:text-warning-300',
                    'overdue': 'border-l-danger-500 bg-danger-50 text-danger-800 dark:text-danger-300'
                };

                items.forEach(evt => {
                    let st = (evt.status || '').toLowerCase();
                    let borderClass = borderColors[st] || 'border-l-slate-300 bg-white dark:bg-slate-800';
                    
                    let card = document.createElement('div');
                    card.className = `min-w-[210px] max-w-[250px] p-3 border-l-4 rounded-lg bg-white dark:bg-slate-900/60 shadow-xs hover:shadow-md transition cursor-pointer shrink-0 ${borderClass}`;
                    card.onclick = () => openDrawer(evt);
                    
                    card.innerHTML = `
                        \x3cdiv class="flex items-center justify-between mb-1.5">
                            \x3cspan class="text-[9px] font-bold text-slate-400">\x3csvg class="w-3 h-3 text-slate-400 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.25m-18 0a2.25 2.25 0 0 0 2.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25M21 18.75m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />\x3c/svg> ${formatReadableDate(evt.tanggal)}\x3c/span>
                            \x3cspan class="text-[8px] font-extrabold px-1 rounded uppercase tracking-wider bg-black/5 dark:bg-white/5">${evt.status}\x3c/span>
                        \x3c/div>
                        \x3ch5 class="text-[11px] font-bold text-slate-800 dark:text-slate-200 line-clamp-2 leading-relaxed mb-2.5">${evt.kegiatan}\x3c/h5>
                        \x3cdiv class="flex items-center justify-between text-[8px] text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-1.5">
                            \x3cspan>PIC: \x3cstrong class="capitalize">${evt.pic || '-'}\x3c/strong>\x3c/span>
                            \x3cspan class="capitalize bg-slate-100 dark:bg-slate-800 px-1 rounded font-bold">${evt.kategori}\x3c/span>
                        \x3c/div>
                    `;
                    timelineFlow.appendChild(card);
                });

                lane.appendChild(timelineFlow);
                container.appendChild(lane);
            });
        }

        // ================= KANBAN VIEW =================
        function renderKanban() {
            let colBelum = document.getElementById('kanban-belum');
            let colProgress = document.getElementById('kanban-progress');
            let colSelesai = document.getElementById('kanban-selesai');

            colBelum.innerHTML = '';
            colProgress.innerHTML = '';
            colSelesai.innerHTML = '';

            let counts = { belum: 0, progress: 0, selesai: 0 };

            filteredMilestones.forEach(m => {
                let st = (m.status || '').toLowerCase();
                let card = createKanbanCard(m);

                if (st === 'selesai') {
                    colSelesai.appendChild(card);
                    counts.selesai++;
                } else if (st === 'belum' || st === 'belum mulai') {
                    colBelum.appendChild(card);
                    counts.belum++;
                } else if (st === 'overdue') {
                    colProgress.appendChild(card);
                    counts.progress++;
                } else {
                    colBelum.appendChild(card);
                    counts.belum++;
                }
            });

            document.getElementById('kanban-belum-count').innerText = counts.belum;
            document.getElementById('kanban-progress-count').innerText = counts.progress;
            document.getElementById('kanban-selesai-count').innerText = counts.selesai;

            if (counts.belum === 0) colBelum.innerHTML = createKanbanEmptyState();
            if (counts.progress === 0) colProgress.innerHTML = createKanbanEmptyState();
            if (counts.selesai === 0) colSelesai.innerHTML = createKanbanEmptyState();
        }

        function createKanbanCard(evt) {
            let card = document.createElement('div');
            let st = (evt.status || '').toLowerCase();
            
            let colorBorder = 'border-l-warning-400';
            if (st === 'selesai') colorBorder = 'border-l-success-500';
            else if (st === 'overdue') colorBorder = 'border-l-danger-500 animate-pulse';

            card.className = `p-3 bg-white dark:bg-slate-900 border-l-4 border-slate-200 dark:border-slate-800 rounded-lg shadow-xs hover:shadow-md transition cursor-pointer select-none ${colorBorder}`;
            card.onclick = () => openDrawer(evt);

            let actName = evt.activity_id || 'Lainnya';
            if (evt.attributes_json && evt.attributes_json.activity_name) {
                actName = evt.attributes_json.activity_name;
            }

            card.innerHTML = `
                \x3cdiv class="flex items-center justify-between gap-1.5 mb-2">
                    \x3cspan class="text-[8px] font-bold text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-slate-800 px-1.5 py-0.5 rounded uppercase tracking-wider truncate max-w-[100px]">${actName}\x3c/span>
                    \x3cspan class="text-[8px] text-slate-400 whitespace-nowrap">\x3csvg class="w-3 h-3 text-slate-400 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />\x3c/svg> ${formatReadableDate(evt.tanggal)}\x3c/span>
                \x3c/div>
                \x3ch5 class="text-[11px] font-bold text-slate-800 dark:text-slate-200 line-clamp-2 leading-relaxed mb-2.5">${evt.kegiatan}\x3c/h5>
                \x3cdiv class="flex items-center justify-between text-[8px] text-slate-400 border-t border-slate-100 dark:border-slate-800 pt-2 shrink-0">
                    \x3cspan>PIC: \x3cstrong class="capitalize">${evt.pic || '-'}\x3c/strong>\x3c/span>
                    \x3cspan class="capitalize px-1 py-0.5 bg-slate-100 dark:bg-slate-800 rounded font-bold">${evt.kategori}\x3c/span>
                \x3c/div>
            `;
            return card;
        }

        function createKanbanEmptyState() {
            return `
                \x3cdiv class="text-center py-6 text-slate-400 text-[10px]">
                    \x3csvg class="w-8 h-8 mb-1.5 opacity-45 text-slate-400 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0a2.251 2.251 0 0 1 13.5 2.25H15c1.03 0 1.9.693 2.166 1.638m-7.377 12.481 3 3 6-6M9 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />\x3c/svg>
                    \x3cp>Kosong\x3c/p>
                \x3c/div>
            `;
        }

        // ================= CONTEXT DRAWER =================
        function openDrawer(evt) {
            let drawer = document.getElementById('context-drawer');
            let backdrop = document.getElementById('drawer-backdrop');
            let content = document.getElementById('drawer-content');
            
            content.innerHTML = '';

            let actName = evt.activity_id || 'Lainnya';
            if (evt.attributes_json && evt.attributes_json.activity_name) {
                actName = evt.attributes_json.activity_name;
            }

            const statusBadges = {
                'selesai': '\x3cspan class="px-2.5 py-0.5 bg-success-100 dark:bg-success-950 text-success-800 dark:text-success-400 rounded text-[9px] font-extrabold uppercase tracking-wider">Selesai\x3c/span>',
                'belum': '\x3cspan class="px-2.5 py-0.5 bg-warning-100 dark:bg-warning-950 text-warning-800 dark:text-warning-400 rounded text-[9px] font-extrabold uppercase tracking-wider">Belum Mulai\x3c/span>',
                'belum mulai': '\x3cspan class="px-2.5 py-0.5 bg-warning-100 dark:bg-warning-950 text-warning-800 dark:text-warning-400 rounded text-[9px] font-extrabold uppercase tracking-wider">Belum Mulai\x3c/span>',
                'overdue': '\x3cspan class="px-2.5 py-0.5 bg-danger-100 dark:bg-danger-950 text-danger-800 dark:text-danger-400 rounded text-[9px] font-extrabold uppercase tracking-wider animate-pulse">Overdue\x3c/span>'
            };

            let attributesHtml = '';
            if (evt.attributes_json && Object.keys(evt.attributes_json).length > 0) {
                attributesHtml = `
                    \x3cdiv class="space-y-2.5 pt-3 border-t border-slate-100 dark:border-slate-800">
                        \x3ch4 class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Metadata Kustom\x3c/h4>
                        \x3cdiv class="bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-850 rounded-xl p-3.5 space-y-2">
                `;

                Object.keys(evt.attributes_json).forEach(key => {
                    if (key === 'activity_name') return;
                    let val = evt.attributes_json[key];
                    let cleanKey = key.replace(/_/g, ' ');
                    
                    if (key === 'path' && val) {
                        attributesHtml += `
                            \x3cdiv>
                                \x3cspan class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">${cleanKey}\x3c/span>
                                \x3cdiv class="text-[10px] mt-0.5">\x3ca href="https://github.com/ihkaru/sipedas/blob/main/${val}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline break-all">\x3csvg class="w-3.5 h-3.5 mr-1 inline" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">\x3cpath d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg> ${val}\x3c/a>\x3c/div>
                            \x3c/div>
                        `;
                    } else if (typeof val === 'string' && (val.startsWith('http://') || val.startsWith('https://'))) {
                        attributesHtml += `
                            \x3cdiv>
                                \x3cspan class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">${cleanKey}\x3c/span>
                                \x3cdiv class="text-[10px] mt-0.5">\x3ca href="${val}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline break-all">${val}\x3c/a>\x3c/div>
                            \x3c/div>
                        `;
                    } else {
                        attributesHtml += `
                            \x3cdiv>
                                \x3cspan class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">${cleanKey}\x3c/span>
                                \x3cdiv class="text-[10px] font-bold text-slate-700 dark:text-slate-300 mt-0.5">${val}\x3c/div>
                            \x3c/div>
                        `;
                    }
                });

                attributesHtml += `
                        \x3c/div>
                    \x3c/div>
                `;
            }

            content.innerHTML = `
                \x3cdiv class="space-y-3">
                    \x3cspan class="inline-flex items-center gap-1 px-2 py-0.5 bg-primary-50 dark:bg-slate-800 border border-primary-200 dark:border-primary-850 text-primary-700 dark:text-primary-400 rounded text-[9px] font-bold uppercase tracking-wider">
                        \x3csvg class="w-3 h-3 text-primary-600 dark:text-primary-400 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.008 1.24l.885 1.77a2.25 2.25 0 0 0 2.007 1.24h1.98a2.25 2.25 0 0 0 2.007-1.24l.885-1.77a2.25 2.25 0 0 1 2.007-1.24h3.86m-18 0h18M2.25 13.5a2.25 2.25 0 0 1 2.25-2.25h15a2.25 2.25 0 0 1 2.25 2.25m-19.5 0v5.25a2.25 2.25 0 0 0 5.25 21h13.5a2.25 2.25 0 0 0 2.25-18.75V13.5M2.25 13.5V6a2.25 2.25 0 0 1 2.25-2.25h3.75a2.25 2.25 0 0 1 2.25 1.5L12 7.5h7.5a2.25 2.25 0 0 1 2.25 2.25v3.75" />\x3c/svg> ${evt.kategori}
                    \x3c/span>
                    \x3ch2 class="text-sm font-bold text-slate-900 dark:text-white leading-normal">${evt.kegiatan}\x3c/h2>
                \x3c/div>

                \x3cdiv class="h-[1px] bg-slate-100 dark:bg-slate-800 my-3">\x3c/div>

                \x3cdiv class="space-y-3 text-[11px]">
                    \x3cdiv class="flex justify-between items-center">
                        \x3cspan class="text-slate-400 font-medium">Status\x3c/span>
                        ${statusBadges[evt.status.toLowerCase()] || evt.status}
                    \x3c/div>
                    \x3cdiv class="flex justify-between items-center">
                        \x3cspan class="text-slate-400 font-medium">Tanggal\x3c/span>
                        \x3cspan class="font-bold text-slate-700 dark:text-slate-300">\x3csvg class="w-3 h-3 text-slate-400 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.25m-18 0a2.25 2.25 0 0 0 2.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25M21 18.75m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />\x3c/svg> ${formatReadableDate(evt.tanggal)}\x3c/span>
                    \x3c/div>
                    \x3cdiv class="flex justify-between items-center">
                        \x3cspan class="text-slate-400 font-medium">PIC\x3c/span>
                        \x3cspan class="font-bold text-slate-700 dark:text-slate-300 bg-slate-100 dark:bg-slate-800 px-1.5 rounded uppercase">${evt.pic || '-'}\x3c/span>
                    \x3c/div>
                    \x3cdiv class="flex justify-between items-center">
                        \x3cspan class="text-slate-400 font-medium">Activity Group\x3c/span>
                        \x3cspan class="font-bold text-slate-700 dark:text-slate-300 capitalize">${actName}\x3c/span>
                    \x3c/div>
                \x3c/div>

                ${attributesHtml}
            `;

            drawer.classList.remove('translate-x-full');
            backdrop.classList.remove('hidden');
            setTimeout(() => { backdrop.classList.remove('opacity-0'); }, 10);
        }

        function closeDrawer() {
            let drawer = document.getElementById('context-drawer');
            let backdrop = document.getElementById('drawer-backdrop');

            drawer.classList.add('translate-x-full');
            backdrop.classList.add('opacity-0');
            setTimeout(() => { backdrop.classList.add('hidden'); }, 300);
        }

        // ================= DAY DIALOG LIST =================
        function openDayListDialog(dateStr, events) {
            let drawer = document.getElementById('context-drawer');
            let backdrop = document.getElementById('drawer-backdrop');
            let content = document.getElementById('drawer-content');
            
            content.innerHTML = '';

            let listHtml = `
                \x3cdiv class="space-y-3">
                    \x3cspan class="inline-flex items-center gap-1 px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded text-[9px] font-bold uppercase tracking-wider">
                        \x3csvg class="w-3 h-3 text-slate-400 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">\x3cpath stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v11.25m-18 0a2.25 2.25 0 0 0 2.25 21h13.5a2.25 2.25 0 0 0 2.25-2.25M21 18.75m-18 0v-7.5a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25v7.5" />\x3c/svg> Tanggal Jadwal
                    \x3c/span>
                    \x3ch2 class="text-sm font-bold text-slate-900 dark:text-white leading-normal">${formatReadableDate(dateStr)}\x3c/h2>
                    \x3cp class="text-[10px] text-slate-500">Ditemukan ${events.length} kegiatan:\x3c/p>
                \x3c/div>

                \x3cdiv class="h-[1px] bg-slate-100 dark:bg-slate-800 my-3">\x3c/div>

                \x3cdiv class="space-y-3">
            `;

            const borderColors = {
                'selesai': 'border-l-success-500',
                'belum': 'border-l-warning-400',
                'belum mulai': 'border-l-warning-400',
                'overdue': 'border-l-danger-500'
            };

            events.forEach((evt, idx) => {
                let st = (evt.status || '').toLowerCase();
                let borderColor = borderColors[st] || 'border-l-slate-400';
                
                listHtml += `
                    \x3cdiv class="p-3 bg-slate-50 hover:bg-slate-100/80 dark:bg-slate-900 dark:hover:bg-slate-800/80 border-l-4 rounded-lg transition cursor-pointer ${borderColor}" onclick="openDrawer(${JSON.stringify(evt).replace(/"/g, '&quot;')})">
                        \x3cdiv class="flex items-center justify-between mb-1">
                            \x3cspan class="text-[8px] font-bold text-slate-400 uppercase">${evt.kategori}\x3c/span>
                            \x3cspan class="text-[8px] font-bold px-1.5 rounded bg-white/90 dark:bg-slate-900 uppercase text-slate-600 dark:text-slate-300 shadow-2xs">${evt.status}\x3c/span>
                        \x3c/div>
                        \x3ch4 class="text-[11px] font-bold text-slate-800 dark:text-slate-200 leading-snug">${evt.kegiatan}\x3c/h4>
                    \x3c/div>
                `;
            });

            listHtml += `
                \x3c/div>
            `;

            content.innerHTML = listHtml;

            drawer.classList.remove('translate-x-full');
            backdrop.classList.remove('hidden');
            setTimeout(() => { backdrop.classList.remove('opacity-0'); }, 10);
        }

        // Utility to format readable date
        function formatReadableDate(dateString) {
            if (!dateString) return '-';
            
            // Split YYYY-MM-DD untuk menghindari timezone shifting
            let parts = dateString.substring(0, 10).split('-');
            if (parts.length === 3) {
                let y = parseInt(parts[0], 10);
                let m = parseInt(parts[1], 10) - 1;
                let d = parseInt(parts[2], 10);
                if (!isNaN(y) && !isNaN(m) && !isNaN(d) && m >= 0 && m < 12) {
                    return `${d} ${monthNames[m]} ${y}`;
                }
            }
            
            // Fallback jika format string berbeda
            let d = new Date(dateString);
            if (isNaN(d.getTime())) return dateString;
            return `${d.getDate()} ${monthNames[d.getMonth()]} ${d.getFullYear()}`;
        }
    </script>
    </div>
</x-filament-panels::page>
