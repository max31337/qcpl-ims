

<header class="flex h-14 items-center gap-4 border-b border-border bg-background px-4 lg:h-[60px] lg:px-6">
    <div class="flex-1"></div>
    
    <div class="flex items-center gap-4 md:ml-auto md:gap-2 lg:gap-4">
        @auth
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium text-foreground">{{ auth()->user()->name }}</span>
                
                @if (Route::has('logout'))
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                            <x-ui.icon name="log-out" class="mr-2 h-4 w-4" />
                            Sign out
                        </button>
                    </form>
                @endif
            </div>
        @endauth
    </div>
</header>
 
