<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="relative z-50 bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links (Grouped with Dropdowns) -->
                <div class="hidden space-x-6 sm:-my-px sm:ms-8 sm:flex sm:items-stretch">
                    <!-- 1. Kendali Kamar (Direct Link) -->
                    @can('dashboard.view')
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate class="h-full">
                        {{ __('Kendali Kamar') }}
                    </x-nav-link>
                    @endcan

                    <!-- 2. Reservasi Kamar (Direct Link) -->
                    @can('reservations.view')
                    <x-nav-link :href="route('reservations.index')" :active="request()->routeIs('reservations.*')" wire:navigate class="h-full">
                        {{ __('Reservasi Kamar') }}
                    </x-nav-link>
                    @endcan

                    <!-- 3. Meeting & Dining (Dropdown) -->
                    @canany(['meeting.view', 'dining.view'])
                    <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" type="button" class="inline-flex items-center h-full px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out cursor-pointer {{ request()->routeIs(['meeting-rooms.*', 'dining.*']) ? 'border-emerald-600 text-gray-900 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <span>Meeting & Dining</span>
                            <svg class="ms-1 fill-current h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 top-full z-[100] mt-1.5 w-52 rounded-xl shadow-xl bg-white border border-gray-100 py-1.5 transform origin-top"
                             @click="open = false" style="display: none;">
                            @can('meeting.view')
                            <x-dropdown-link :href="route('meeting-rooms.index')" wire:navigate class="{{ request()->routeIs('meeting-rooms.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Ruang Meeting') }}
                            </x-dropdown-link>
                            @endcan
                            @can('dining.view')
                            <x-dropdown-link :href="route('dining.index')" wire:navigate class="{{ request()->routeIs('dining.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Fine Dining') }}
                            </x-dropdown-link>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- 4. Inventory (Dropdown) -->
                    @canany(['inventory.view', 'expenses.view', 'assets.view'])
                    <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" type="button" class="inline-flex items-center h-full px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out cursor-pointer {{ request()->routeIs(['inventory.*', 'expenses.*', 'assets.*']) ? 'border-emerald-600 text-gray-900 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <span>Inventory</span>
                            <svg class="ms-1 fill-current h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 top-full z-[100] mt-1.5 w-52 rounded-xl shadow-xl bg-white border border-gray-100 py-1.5 transform origin-top"
                             @click="open = false" style="display: none;">
                            @can('inventory.view')
                            <x-dropdown-link :href="route('inventory.index')" wire:navigate class="{{ request()->routeIs('inventory.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Inventaris') }}
                            </x-dropdown-link>
                            @endcan
                            @can('expenses.view')
                            <x-dropdown-link :href="route('expenses.index')" wire:navigate class="{{ request()->routeIs('expenses.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Pengeluaran') }}
                            </x-dropdown-link>
                            @endcan
                            @can('assets.view')
                            <x-dropdown-link :href="route('assets.index')" wire:navigate class="{{ request()->routeIs('assets.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Aset Tetap') }}
                            </x-dropdown-link>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- 5. Report (Dropdown) -->
                    @canany(['calendar.view', 'night_audit.view', 'reports.view'])
                    <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" type="button" class="inline-flex items-center h-full px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out cursor-pointer {{ request()->routeIs(['calendar.*', 'night-audit.*', 'reports.*']) ? 'border-emerald-600 text-gray-900 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <span>Report</span>
                            <svg class="ms-1 fill-current h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 top-full z-[100] mt-1.5 w-52 rounded-xl shadow-xl bg-white border border-gray-100 py-1.5 transform origin-top"
                             @click="open = false" style="display: none;">
                            @can('calendar.view')
                            <x-dropdown-link :href="route('calendar.index')" wire:navigate class="{{ request()->routeIs('calendar.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Kalender') }}
                            </x-dropdown-link>
                            @endcan
                            @can('night_audit.view')
                            <x-dropdown-link :href="route('night-audit.index')" wire:navigate class="{{ request()->routeIs('night-audit.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Night Audit') }}
                            </x-dropdown-link>
                            @endcan
                            @can('reports.view')
                            <x-dropdown-link :href="route('reports.index')" wire:navigate class="{{ request()->routeIs('reports.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Laporan') }}
                            </x-dropdown-link>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- 6. Setting (Dropdown) -->
                    @canany(['users.view', 'roles.manage', 'settings.view', 'rooms_master.view', 'cashier.view', 'web_admin.view'])
                    <div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" type="button" class="inline-flex items-center h-full px-1 pt-1 border-b-2 text-sm font-medium leading-5 transition duration-150 ease-in-out cursor-pointer {{ request()->routeIs(['settings.*', 'rooms.*', 'cashier.*', 'users.*', 'roles.*']) ? 'border-emerald-600 text-gray-900 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            <span>Setting</span>
                            <svg class="ms-1 fill-current h-4 w-4 transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute left-0 top-full z-[100] mt-1.5 w-52 rounded-xl shadow-xl bg-white border border-gray-100 py-1.5 transform origin-top"
                             @click="open = false" style="display: none;">
                            @can('users.view')
                            <x-dropdown-link :href="route('users.index')" wire:navigate class="{{ request()->routeIs('users.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Manajemen User') }}
                            </x-dropdown-link>
                            @endcan
                            @can('roles.manage')
                            <x-dropdown-link :href="route('roles.index')" wire:navigate class="{{ request()->routeIs('roles.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Role & Hak Akses') }}
                            </x-dropdown-link>
                            @endcan
                            @can('settings.view')
                            <x-dropdown-link :href="route('settings.index')" wire:navigate class="{{ request()->routeIs('settings.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Pengaturan Apps') }}
                            </x-dropdown-link>
                            @endcan
                            @can('rooms_master.view')
                            <x-dropdown-link :href="route('rooms.index')" wire:navigate class="{{ request()->routeIs('rooms.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Master Kamar') }}
                            </x-dropdown-link>
                            @endcan
                            @can('cashier.view')
                            <x-dropdown-link :href="route('cashier.index')" wire:navigate class="{{ request()->routeIs('cashier.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Shift') }}
                            </x-dropdown-link>
                            @endcan
                            @can('web_admin.view')
                            <x-dropdown-link :href="route('web-admin.index')" wire:navigate class="{{ request()->routeIs('web-admin.*') ? 'bg-emerald-50 text-emerald-800 font-semibold' : '' }}">
                                {{ __('Administrasi Web') }}
                            </x-dropdown-link>
                            @endcan
                        </div>
                    </div>
                    @endcanany

                    <!-- 7. Administrasi Web (Direct Link) -->
                    @can('web_admin.view')
                    <x-nav-link :href="route('web-admin.index')" :active="request()->routeIs('web-admin.*')" wire:navigate class="h-full">
                        {{ __('Administrasi Web') }}
                    </x-nav-link>
                    @endcan
                </div>
            </div>

            <!-- Profile & User Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150 cursor-pointer">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @can('users.view')
                            <x-dropdown-link :href="route('users.index')" wire:navigate>
                                {{ __('Manajemen User') }}
                            </x-dropdown-link>
                        @endcan
                        @can('roles.manage')
                            <x-dropdown-link :href="route('roles.index')" wire:navigate>
                                {{ __('Role & Hak Akses') }}
                            </x-dropdown-link>
                        @endcan
                        @can('settings.view')
                            <x-dropdown-link :href="route('settings.index')" wire:navigate>
                                {{ __('Pengaturan Apps') }}
                            </x-dropdown-link>
                        @endcan

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start cursor-pointer">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger (Mobile) -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out cursor-pointer">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu (Mobile) -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-white border-t border-gray-100">
        <div class="pt-2 pb-3 space-y-1">
            <!-- Standalone Links -->
            @can('dashboard.view')
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Kendali Kamar') }}
            </x-responsive-nav-link>
            @endcan

            @can('reservations.view')
            <x-responsive-nav-link :href="route('reservations.index')" :active="request()->routeIs('reservations.*')" wire:navigate>
                {{ __('Reservasi Kamar') }}
            </x-responsive-nav-link>
            @endcan

            <!-- GROUP: MEETING & DINING -->
            @canany(['meeting.view', 'dining.view'])
            <div class="pt-3 pb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50/50">
                Meeting & Dining
            </div>
            @can('meeting.view')
            <x-responsive-nav-link :href="route('meeting-rooms.index')" :active="request()->routeIs('meeting-rooms.*')" wire:navigate class="ps-6">
                {{ __('Ruang Meeting') }}
            </x-responsive-nav-link>
            @endcan
            @can('dining.view')
            <x-responsive-nav-link :href="route('dining.index')" :active="request()->routeIs('dining.*')" wire:navigate class="ps-6">
                {{ __('Fine Dining') }}
            </x-responsive-nav-link>
            @endcan
            @endcanany

            <!-- GROUP: INVENTORY -->
            @canany(['inventory.view', 'expenses.view', 'assets.view'])
            <div class="pt-3 pb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50/50">
                Inventory
            </div>
            @can('inventory.view')
            <x-responsive-nav-link :href="route('inventory.index')" :active="request()->routeIs('inventory.*')" wire:navigate class="ps-6">
                {{ __('Inventaris') }}
            </x-responsive-nav-link>
            @endcan
            @can('expenses.view')
            <x-responsive-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.*')" wire:navigate class="ps-6">
                {{ __('Pengeluaran') }}
            </x-responsive-nav-link>
            @endcan
            @can('assets.view')
            <x-responsive-nav-link :href="route('assets.index')" :active="request()->routeIs('assets.*')" wire:navigate class="ps-6">
                {{ __('Aset Tetap') }}
            </x-responsive-nav-link>
            @endcan
            @endcanany

            <!-- GROUP: REPORT -->
            @canany(['calendar.view', 'night_audit.view', 'reports.view'])
            <div class="pt-3 pb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50/50">
                Report
            </div>
            @can('calendar.view')
            <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')" wire:navigate class="ps-6">
                {{ __('Kalender') }}
            </x-responsive-nav-link>
            @endcan
            @can('night_audit.view')
            <x-responsive-nav-link :href="route('night-audit.index')" :active="request()->routeIs('night-audit.*')" wire:navigate class="ps-6">
                {{ __('Night Audit') }}
            </x-responsive-nav-link>
            @endcan
            @can('reports.view')
            <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')" wire:navigate class="ps-6">
                {{ __('Laporan') }}
            </x-responsive-nav-link>
            @endcan
            @endcanany

            <!-- GROUP: SETTING -->
            @canany(['users.view', 'roles.manage', 'settings.view', 'rooms_master.view', 'cashier.view', 'web_admin.view'])
            <div class="pt-3 pb-1 px-4 text-[10px] font-bold uppercase tracking-wider text-emerald-800 bg-emerald-50/50">
                Setting
            </div>
            @can('users.view')
            <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')" wire:navigate class="ps-6">
                {{ __('Manajemen User') }}
            </x-responsive-nav-link>
            @endcan
            @can('roles.manage')
            <x-responsive-nav-link :href="route('roles.index')" :active="request()->routeIs('roles.*')" wire:navigate class="ps-6">
                {{ __('Role & Hak Akses') }}
            </x-responsive-nav-link>
            @endcan
            @can('settings.view')
            <x-responsive-nav-link :href="route('settings.index')" :active="request()->routeIs('settings.*')" wire:navigate class="ps-6">
                {{ __('Pengaturan Apps') }}
            </x-responsive-nav-link>
            @endcan
            @can('rooms_master.view')
            <x-responsive-nav-link :href="route('rooms.index')" :active="request()->routeIs('rooms.*')" wire:navigate class="ps-6">
                {{ __('Master Kamar') }}
            </x-responsive-nav-link>
            @endcan
            @can('cashier.view')
            <x-responsive-nav-link :href="route('cashier.index')" :active="request()->routeIs('cashier.*')" wire:navigate class="ps-6">
                {{ __('Shift') }}
            </x-responsive-nav-link>
            @endcan
            @can('web_admin.view')
            <x-responsive-nav-link :href="route('web-admin.index')" :active="request()->routeIs('web-admin.*')" wire:navigate class="ps-6">
                {{ __('Administrasi Web') }}
            </x-responsive-nav-link>
            @endcan
            @endcanany
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
</nav>
