

<header class="flex h-14 items-center gap-4 border-b border-border bg-background px-4 lg:h-[60px] lg:px-6">
    <div class="flex-1"></div>
    
    <div class="flex items-center gap-4 md:ml-auto md:gap-2 lg:gap-4">
        @auth
            <!-- User Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-foreground bg-background border border-input rounded-md hover:bg-accent hover:text-accent-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 transition-colors">
                    <div class="flex items-center justify-center w-8 h-8 bg-primary/10 text-primary rounded-full text-xs font-semibold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    <div class="flex flex-col items-start">
                        <span class="text-sm font-medium">{{ auth()->user()->name }}</span>
                        <span class="text-xs text-muted-foreground">{{ ucfirst(auth()->user()->role ?? 'User') }}</span>
                    </div>
                    <x-ui.icon name="chevron-down" class="h-4 w-4 transition-transform" x-bind:class="{ 'rotate-180': open }" />
                </button>

                <!-- Dropdown Menu -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 bg-background border border-border rounded-md shadow-lg z-50">
                    <div class="px-4 py-3 border-b border-border">
                        <div class="text-sm font-medium text-foreground">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-muted-foreground">{{ auth()->user()->email }}</div>
                        <div class="text-xs text-muted-foreground mt-1">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                {{ ucfirst(auth()->user()->role ?? 'User') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="py-1">
                        <a href="{{ route('profile') }}" 
                           class="flex items-center w-full px-4 py-2 text-sm text-foreground hover:bg-accent hover:text-accent-foreground transition-colors"
                           wire:navigate>
                            <x-ui.icon name="user" class="mr-3 h-4 w-4" />
                            Profile
                        </a>
                        
                        @if (Route::has('logout'))
                            <div class="border-t border-border my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="flex items-center w-full px-4 py-2 text-sm text-foreground hover:bg-accent hover:text-accent-foreground transition-colors">
                                    <x-ui.icon name="log-out" class="mr-3 h-4 w-4" />
                                    Sign out
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @endauth
    </div>
</header>
 
