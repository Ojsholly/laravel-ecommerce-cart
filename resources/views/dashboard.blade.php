<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl">
        <div>
            <h1 class="text-2xl font-bold text-zinc-900 dark:text-white">Welcome back, {{ auth()->user()->name }}!</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400 mt-1">Start shopping or manage your orders</p>
        </div>

        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <!-- Browse Products Card -->
            <a href="{{ route('products.index') }}" wire:navigate class="group relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-1">Browse Products</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Explore our collection of 25 products</p>
                    </div>
                    <svg class="w-5 h-5 text-zinc-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- View Cart Card -->
            <a href="{{ route('cart.index') }}" wire:navigate class="group relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-1">Shopping Cart</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">View and manage your cart items</p>
                    </div>
                    <svg class="w-5 h-5 text-zinc-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>

            <!-- Wishlist Card -->
            <a href="{{ route('wishlist.index') }}" wire:navigate class="group relative overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6 hover:border-zinc-300 dark:hover:border-zinc-600 transition-all">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 rounded-lg bg-pink-100 dark:bg-pink-900/30">
                                <svg class="w-6 h-6 text-pink-600 dark:text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-1">Wishlist</h3>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400">Save items for later</p>
                    </div>
                    <svg class="w-5 h-5 text-zinc-400 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </div>
            </a>
        </div>

        <!-- Recent Activity Section -->
        <div class="relative flex-1 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6">
            <h2 class="text-xl font-semibold text-zinc-900 dark:text-white mb-4">Quick Actions</h2>
            <div class="grid gap-3 md:grid-cols-2">
                <a href="{{ route('products.index') }}" wire:navigate class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                    <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-zinc-900 dark:text-white">Search Products</div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400">Find what you need</div>
                    </div>
                </a>
                <a href="{{ route('cart.index') }}" wire:navigate class="flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                    <div class="p-2 rounded-lg bg-green-100 dark:bg-green-900/30">
                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="font-medium text-zinc-900 dark:text-white">View Orders</div>
                        <div class="text-sm text-zinc-600 dark:text-zinc-400">Track your purchases</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
