<div x-data="{ activeTab: 'requests' }">
    <!-- Header -->
    <div class="flex items-center justify-between space-y-2">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">User Management</h1>
            <p class="text-muted-foreground">Manage user invitations, requests, and accounts</p>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <x-ui.alert variant="success" icon="check">
            {{ session('success') }}
        </x-ui.alert>
    @endif

    @if (session()->has('error'))
        <x-ui.alert variant="destructive" icon="x">
            {{ session('error') }}
        </x-ui.alert>
    @endif

    <!-- Tabs -->
    <div class="w-full">
        <div class="inline-flex h-10 items-center justify-center rounded-md bg-muted p-1 text-muted-foreground">
            <button @click="activeTab = 'requests'"
                :class="activeTab === 'requests' ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/60'"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                Invitation Requests
                @if($pendingRequestsCount > 0)
                    <span class="ml-2 flex h-5 w-5 items-center justify-center rounded-full bg-destructive text-xs text-destructive-foreground">
                        {{ $pendingRequestsCount }}
                    </span>
                @endif
            </button>

            <button @click="activeTab = 'invitations'"
                :class="activeTab === 'invitations' ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/60'"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                Sent Invitations
                @if($pendingInvitationsCount > 0)
                    <span class="ml-2 flex h-5 w-5 items-center justify-center rounded-full bg-yellow-500 text-xs text-white">
                        {{ $pendingInvitationsCount }}
                    </span>
                @endif
            </button>

            <button @click="activeTab = 'users'"
                :class="activeTab === 'users' ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/60'"
                class="inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                User Management
            </button>
        </div>
    </div>

    <!-- Invitation Requests Tab -->
    <div x-show="activeTab === 'requests'">
        <div class="rounded-lg border bg-card text-card-foreground shadow-sm">
            <div class="flex flex-col space-y-1.5 p-6">
                <h3 class="text-2xl font-semibold leading-none tracking-tight">Access Requests</h3>
                <p class="text-sm text-muted-foreground">Review requests from the landing page form</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requester</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submitted</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($invitationRequests as $request)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->full_name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $request->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">{{ $request->department ?: 'Not specified' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($request->status === 'approved') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($request->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    @if($request->status === 'pending')
                                        <button wire:click="openRequestModal({{ $request->id }}, 'approve')"
                                            class="text-green-600 hover:text-green-900">Approve</button>
                                        <button wire:click="openRequestModal({{ $request->id }}, 'reject')"
                                            class="text-red-600 hover:text-red-900">Reject</button>
                                    @else
                                        <span class="text-gray-400">Reviewed by {{ $request->reviewer?->name ?? 'Unknown' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No invitation requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $invitationRequests->links('pagination::custom-light') }}
            </div>
        </div>
    </div>

    <!-- Sent Invitations Tab -->
    <div x-show="activeTab === 'invitations'" class="space-y-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Sent Invitations</h3>
                        <p class="mt-1 text-sm text-gray-600">Manage invitations sent to users</p>
                    </div>
                    <button wire:click="$set('showInviteModal', true)"
                        class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                        <x-ui.icon name="user-plus" class="mr-2 h-4 w-4" />
                        Send Invitation
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Info</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expires</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($invitations as $invitation)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $invitation->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($invitation->status === 'pending') bg-yellow-100 text-yellow-800
                                        @elseif($invitation->status === 'registered') bg-blue-100 text-blue-800
                                        @elseif($invitation->status === 'approved') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst($invitation->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($invitation->user)
                                        <div class="text-sm text-gray-900">{{ $invitation->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $invitation->user->username }} ({{ $invitation->user->role }})</div>
                                    @else
                                        <span class="text-sm text-gray-400">Not registered</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $invitation->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($invitation->expires_at < now())
                                        <span class="text-red-600">Expired</span>
                                    @else
                                        {{ $invitation->expires_at->format('M d, Y H:i') }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    @if($invitation->status === 'pending')
                                        <button wire:click="resendInvitation({{ $invitation->id }})"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800">
                                            <x-ui.icon name="refresh-ccw" /> Resend
                                        </button>
                                        <button wire:click="deleteInvitation({{ $invitation->id }})"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800"
                                            onclick="return confirm('Are you sure?')">
                                            <x-ui.icon name="trash-2" /> Delete
                                        </button>
                                    @elseif($invitation->status === 'registered' && $invitation->user)
                                        <button wire:click="openApprovalModal({{ $invitation->id }}, 'approve')"
                                            class="inline-flex items-center gap-1 text-green-600 hover:text-green-800">
                                            <x-ui.icon name="user-check" /> Approve
                                        </button>
                                        <button wire:click="openApprovalModal({{ $invitation->id }}, 'reject')"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800">
                                            <x-ui.icon name="user-x" /> Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No invitations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $invitations->links('pagination::custom-light') }}
            </div>
        </div>
    </div>

    <!-- User Management Tab -->
    <div x-show="activeTab === 'users'" class="space-y-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">User Accounts</h3>
                    <p class="mt-1 text-sm text-gray-600">Manage registered user accounts and permissions</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->username }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-500">
                                        @if($user->branch)
                                            {{ $user->branch->name }}
                                            @if($user->division)
                                                / {{ $user->division->name }}
                                                @if($user->section)
                                                    / {{ $user->section->name }}
                                                @endif
                                            @endif
                                        @else
                                            Not assigned
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="space-y-1">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($user->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                        @if($user->approval_status)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                                @if($user->approval_status === 'approved') bg-green-100 text-green-800
                                                @elseif($user->approval_status === 'pending') bg-yellow-100 text-yellow-800
                                                @else bg-red-100 text-red-800 @endif">
                                                {{ ucfirst($user->approval_status) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                    @if(!$user->is_active)
                                        <button wire:click="openUserModal({{ $user->id }}, 'activate')"
                                            class="inline-flex items-center gap-1 text-green-600 hover:text-green-800">
                                            <x-ui.icon name="shield-check" /> Activate
                                        </button>
                                    @else
                                        <button wire:click="openUserModal({{ $user->id }}, 'deactivate')"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800">
                                            <x-ui.icon name="shield-x" /> Deactivate
                                        </button>
                                    @endif

                                    @if($user->approval_status === 'pending')
                                        <button wire:click="openUserModal({{ $user->id }}, 'approve')"
                                            class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800">
                                            <x-ui.icon name="user-check" /> Approve
                                        </button>
                                        <button wire:click="openUserModal({{ $user->id }}, 'reject')"
                                            class="inline-flex items-center gap-1 text-red-600 hover:text-red-800">
                                            <x-ui.icon name="user-x" /> Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="bg-white px-4 py-3 border-t border-gray-200">
                {{ $users->links('pagination::custom-light') }}
            </div>
        </div>
    </div>

    <!-- Request Approval Modal -->
    @if($showRequestModal && $selectedRequest)
        <div class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-md rounded-lg bg-background border-border">
                <div class="mt-3">
                    <h3 class="text-lg font-semibold text-foreground mb-4">
                        {{ $requestAction === 'approve' ? 'Approve' : 'Reject' }} Access Request
                    </h3>

                    <div class="mb-4 p-3 bg-muted rounded-lg">
                        <div class="text-sm">
                            <div><span class="font-medium">Name:</span> {{ $selectedRequest->full_name }}</div>
                            <div><span class="font-medium">Email:</span> {{ $selectedRequest->email }}</div>
                            <div><span class="font-medium">Department:</span> {{ $selectedRequest->department ?: 'Not specified' }}</div>
                            <div><span class="font-medium">Reason:</span> {{ Str::limit($selectedRequest->reason, 100) }}</div>
                        </div>
                    </div>

                    <form wire:submit="{{ $requestAction === 'approve' ? 'approveRequest' : 'rejectRequest' }}({{ $selectedRequest->id }})">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-foreground mb-2">
                                {{ $requestAction === 'approve' ? 'Approval' : 'Rejection' }} Notes (Optional)
                            </label>
                            <x-ui.textarea wire:model="requestNotes" rows="3" placeholder="Add notes for the user..." />
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="resetRequestModal"
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-3">
                                Cancel
                            </button>
                            <button type="submit"
                                class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3 {{ $requestAction === 'approve' ? 'bg-green-600 text-white hover:bg-green-700' : 'bg-red-600 text-white hover:bg-red-700' }}">
                                {{ $requestAction === 'approve' ? 'Approve & Send Invitation' : 'Reject Request' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Send Invitation Modal -->
    @if($showInviteModal)
        <div class="fixed inset-0 bg-black/50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-md rounded-lg bg-background border-border">
                <div class="mt-3">
                    <h3 class="text-lg font-semibold text-foreground mb-4">Send User Invitation</h3>

                    <form wire:submit="sendInvitation">
                        <div class="mb-4">
                            <x-ui.label>Email Address</x-ui.label>
                            <x-ui.input wire:model="email" type="email" required />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Message (Optional)</label>
                            <textarea wire:model="message" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Add a personal message to the invitation..."></textarea>
                            @error('message') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="$set('showInviteModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                Send Invitation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Approval Modal -->
    @if($showApprovalModal && $selectedInvitation)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $approvalAction === 'approve' ? 'Approve' : 'Reject' }} User Registration
                    </h3>

                    @if($selectedInvitation->user)
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="text-sm">
                                <div><strong>Name:</strong> {{ $selectedInvitation->user->name }}</div>
                                <div><strong>Username:</strong> {{ $selectedInvitation->user->username }}</div>
                                <div><strong>Employee ID:</strong> {{ $selectedInvitation->user->employee_id }}</div>
                                <div><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $selectedInvitation->user->role)) }}</div>
                            </div>
                        </div>
                    @endif

                    <form wire:submit="processApproval">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                {{ $approvalAction === 'approve' ? 'Approval' : 'Rejection' }} Message (Optional)
                            </label>
                            <textarea wire:model="approvalMessage" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Add a message for the user..."></textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" wire:click="$set('showApprovalModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white rounded-md
                                    {{ $approvalAction === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                                {{ $approvalAction === 'approve' ? 'Approve User' : 'Reject Registration' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- User Action Modal -->
    @if($showUserModal && $selectedUser)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ ucfirst($userAction) }} User Account
                    </h3>

                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <div class="text-sm">
                            <div><strong>Name:</strong> {{ $selectedUser->name }}</div>
                            <div><strong>Email:</strong> {{ $selectedUser->email }}</div>
                            <div><strong>Username:</strong> {{ $selectedUser->username }}</div>
                            <div><strong>Role:</strong> {{ ucfirst(str_replace('_', ' ', $selectedUser->role)) }}</div>
                            <div><strong>Current Status:</strong>
                                <span class="ml-1 px-2 py-1 text-xs rounded-full
                                    @if($selectedUser->is_active) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                    {{ $selectedUser->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if(in_array($userAction, ['reject']))
                        <form wire:submit="processUserAction">
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Reason (Optional)
                                </label>
                                <textarea wire:model="userNotes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Provide a reason for this action..."></textarea>
                            </div>

                            <div class="flex justify-end space-x-3">
                                <button type="button" wire:click="$set('showUserModal', false)"
                                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                    Cancel
                                </button>
                                <button type="submit"
                                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700">
                                    {{ ucfirst($userAction) }} User
                                </button>
                            </div>
                        </form>
                    @else
                        <div class="flex justify-end space-x-3">
                            <button wire:click="$set('showUserModal', false)"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                                Cancel
                            </button>
                            <button wire:click="processUserAction"
                                class="px-4 py-2 text-sm font-medium text-white rounded-md
                                    @if(in_array($userAction, ['activate', 'approve'])) bg-green-600 hover:bg-green-700
                                    @else bg-red-600 hover:bg-red-700 @endif">
                                {{ ucfirst($userAction) }} User
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>