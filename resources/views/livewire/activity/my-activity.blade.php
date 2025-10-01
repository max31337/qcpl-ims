<div>
    <x-ui-card title="My Activity">
        <div class="flex items-center gap-3 mb-4">
            <x-ui-input wire:model.debounce.300ms="search" placeholder="Search my activity..." />
            <x-ui-select wire:model="actionFilter">
                <option value="">All actions</option>
                <option value="created">Created</option>
                <option value="updated">Updated</option>
                <option value="deleted">Deleted</option>
                <option value="login">Login</option>
                <option value="logout">Logout</option>
            </x-ui-select>
        </div>

        <div class="overflow-hidden bg-white rounded shadow">
            <table class="w-full text-left">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-2">When</th>
                        <th class="p-2">Action</th>
                        <th class="p-2">Description</th>
                        <th class="p-2">Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $log)
                        <tr class="border-t">
                            <td class="p-2">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                            <td class="p-2">{{ ucfirst($log->action) }}</td>
                            <td class="p-2">{{ $log->friendly_description ?? $log->description }}</td>
                            <td class="p-2"><x-ui-button x-data x-on:click="$dispatch('open-modal', '{{ 'activity-'.$log->id }}')" size="sm">View</x-ui-button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </x-ui-card>

    @foreach($logs as $log)
        @php
            $changes = collect($log->getAllChanges());
            $displayChanges = $changes->filter(fn($c) => !($c['is_system_field'] ?? false))->values();
            $subject = $log->getRelatedModel();
            $canOpen = false;
            $openUrl = null;
            if ($log->model === 'Supply' && $log->model_id) {
                $canOpen = true; $openUrl = route('supplies.edit', ['id' => $log->model_id]);
            } elseif ($log->model === 'Asset' && $log->model_id) {
                $canOpen = true; $openUrl = route('assets.edit', ['assetId' => $log->model_id]);
            }
        @endphp
    <x-ui-modal name="{{ 'activity-'.$log->id }}" :show="false" maxWidth="3xl" :forceLight="true">
            <x-slot name="title">
                <div class="flex items-center gap-3">
                    <x-ui.icon name="{{ $log->action_icon }}" class="h-5 w-5 {{ $log->action_color }}" />
                    <div>
                        <div class="font-medium">{{ ucfirst($log->action) }}</div>
                        <div class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y g:i A') }}</div>
                    </div>
                </div>
            </x-slot>
            <div class="p-6 space-y-4 text-gray-800">
                <div class="flex items-start justify-between gap-4">
                    <div class="text-sm text-gray-800">{{ $log->friendly_description ?? $log->description }}</div>
                    <div class="flex items-center gap-2">
                        @if($canOpen && $openUrl)
                            <a href="{{ $openUrl }}" wire:navigate class="inline-flex items-center gap-2 rounded-md border px-3 py-1.5 text-sm hover:bg-accent">
                                <x-ui.icon name="external-link" class="h-4 w-4" /> Open
                            </a>
                        @endif
                        <button x-data x-on:click="$dispatch('close-modal','{{ 'activity-'.$log->id }}')" class="inline-flex items-center rounded-md border px-3 py-1.5 text-sm hover:bg-accent">Close</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-1">
                        <div class="rounded-md border p-3 text-sm bg-white">
                            <div class="flex items-center justify-between">
                                <div class="text-gray-500">Model</div>
                                <div class="font-medium">{{ $log->model ?? '—' }}</div>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="text-gray-500">Actor</div>
                                <div class="font-medium">{{ $log->user->name ?? ('User #'.$log->user_id) }}</div>
                            </div>
                            <div class="flex items-center justify-between mt-2">
                                <div class="text-gray-500">When</div>
                                <div class="font-medium">{{ $log->created_at->diffForHumans() }}</div>
                            </div>
                            @if(!empty($log->ip_address) || !empty($log->browser))
                                <div class="mt-3 text-xs text-gray-500">
                                    <div>IP: {{ $log->ip_address ?? '—' }}</div>
                                    <div>{{ $log->browser ?? '' }} {{ $log->browser_version ?? '' }} • {{ $log->platform ?? '' }} • {{ $log->device ?? '' }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        @if($displayChanges->isNotEmpty())
                            <div class="overflow-x-auto rounded-md border">
                                <table class="min-w-full text-sm">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th class="px-3 py-2 text-left">Field</th>
                                            <th class="px-3 py-2 text-left">Old</th>
                                            <th class="px-3 py-2 text-left">New</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($displayChanges as $c)
                                            <tr class="border-t">
                                                <td class="px-3 py-2 whitespace-nowrap">{{ $c['field_name'] }}</td>
                                                <td class="px-3 py-2"><span class="inline-block rounded px-2 py-0.5 bg-red-100 text-red-800">{{ $c['old_value'] }}</span></td>
                                                <td class="px-3 py-2"><span class="inline-block rounded px-2 py-0.5 bg-green-100 text-green-800">{{ $c['new_value'] }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="rounded-md border p-3 text-sm text-gray-500">No field-level changes recorded.</div>
                        @endif
                    </div>
                </div>
            </div>
        </x-ui-modal>
    @endforeach
</div>
