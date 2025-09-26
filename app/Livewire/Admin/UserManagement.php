<?php

namespace App\Livewire\Admin;

use App\Models\UserInvitation;
use App\Models\InvitationRequest;
use App\Models\User;
use App\Mail\InvitationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class UserManagement extends Component
{
    use WithPagination;

    // Common properties
    public $activeTab = 'requests';

    // Computed property values
    public $pendingRequestsCount = 0;
    public $pendingInvitationsCount = 0;

    // Invitation Request properties
    public $selectedRequest = null;
    public $showRequestModal = false;
    public $requestAction = '';
    public $requestNotes = '';

    // Sent Invitations properties
    public $showInviteModal = false;
    public $email = '';
    public $message = '';

    public $selectedInvitation = null;
    public $showApprovalModal = false;
    public $approvalAction = '';
    public $approvalMessage = '';

    // User Management properties
    public $selectedUser = null;
    public $showUserModal = false;
    public $userAction = '';
    public $userNotes = '';

    protected $rules = [
        'email' => 'required|email|unique:users,email|unique:user_invitations,email',
        'message' => 'nullable|string|max:500'
    ];

    public function mount()
    {
        $this->activeTab = 'requests';
        $this->loadCounts();
    }

    public function loadCounts()
    {
        $this->pendingRequestsCount = InvitationRequest::where('status', 'pending')->count();
        $this->pendingInvitationsCount = UserInvitation::where('status', 'registered')->count();
    }

    // Invitation Requests Methods
    public function approveRequest($requestId)
    {
        $request = InvitationRequest::findOrFail($requestId);

        try {
            $request->approve(auth()->user(), $this->requestNotes);

            // Create invitation for approved request
            $invitation = UserInvitation::createInvitation($request->email, auth()->user()->name);

            // Send invitation email
            Mail::to($request->email)->send(new InvitationMail($invitation));

            session()->flash('success', 'Request approved and invitation sent to ' . $request->email);
            $this->resetRequestModal();
            $this->loadCounts();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to approve request: ' . $e->getMessage());
        }
    }

    public function rejectRequest($requestId)
    {
        $request = InvitationRequest::findOrFail($requestId);

        try {
            $request->reject(auth()->user(), $this->requestNotes);
            session()->flash('success', 'Request rejected successfully.');
            $this->resetRequestModal();
            $this->loadCounts();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to reject request: ' . $e->getMessage());
        }
    }

    public function openRequestModal($requestId, $action)
    {
        $this->selectedRequest = InvitationRequest::findOrFail($requestId);
        $this->requestAction = $action;
        $this->showRequestModal = true;
        $this->requestNotes = '';
    }

    public function resetRequestModal()
    {
        $this->selectedRequest = null;
        $this->showRequestModal = false;
        $this->requestAction = '';
        $this->requestNotes = '';
    }

    // Sent Invitations Methods
    public function sendInvitation()
    {
        $this->validate();

        try {
            $invitation = UserInvitation::createInvitation($this->email, auth()->user()->name);

            // Send invitation email
            Mail::to($this->email)->send(new InvitationMail($invitation));

            session()->flash('success', 'Invitation sent successfully to ' . $this->email);

            $this->reset(['email', 'message', 'showInviteModal']);
            $this->loadCounts();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send invitation: ' . $e->getMessage());
        }
    }

    public function openApprovalModal($invitationId, $action)
    {
        $this->selectedInvitation = UserInvitation::findOrFail($invitationId);
        $this->approvalAction = $action;
        $this->showApprovalModal = true;
        $this->approvalMessage = '';
    }

    public function processApproval()
    {
        if (!$this->selectedInvitation || !$this->selectedInvitation->user) {
            session()->flash('error', 'Invalid invitation or user not found.');
            return;
        }

        try {
            if ($this->approvalAction === 'approve') {
                $this->selectedInvitation->approve(auth()->user()->name);
                session()->flash('success', 'User approved successfully!');
            } else {
                $this->selectedInvitation->reject($this->approvalMessage ?: 'Registration rejected by admin');
                session()->flash('success', 'User registration rejected.');
            }

            $this->reset(['selectedInvitation', 'showApprovalModal', 'approvalAction', 'approvalMessage']);
            $this->loadCounts();
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to process approval: ' . $e->getMessage());
        }
    }

    public function resendInvitation($invitationId)
    {
        $invitation = UserInvitation::findOrFail($invitationId);

        if ($invitation->status !== 'pending') {
            session()->flash('error', 'Can only resend pending invitations.');
            return;
        }

        try {
            // Extend expiry
            $invitation->update(['expires_at' => now()->addDays(7)]);

            // Resend email
            Mail::to($invitation->email)->send(new InvitationMail($invitation));

            session()->flash('success', 'Invitation resent to ' . $invitation->email);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to resend invitation: ' . $e->getMessage());
        }
    }

    public function deleteInvitation($invitationId)
    {
        $invitation = UserInvitation::findOrFail($invitationId);
        $invitation->delete();

        session()->flash('success', 'Invitation deleted successfully.');
    }

    // User Management Methods
    public function openUserModal($userId, $action)
    {
        $this->selectedUser = User::findOrFail($userId);
        $this->userAction = $action;
        $this->showUserModal = true;
        $this->userNotes = '';
    }

    public function processUserAction()
    {
        if (!$this->selectedUser) {
            session()->flash('error', 'User not found.');
            return;
        }

        try {
            switch ($this->userAction) {
                case 'activate':
                    $this->selectedUser->update([
                        'is_active' => true,
                        'approval_status' => 'approved'
                    ]);
                    session()->flash('success', 'User activated successfully.');
                    break;

                case 'deactivate':
                    $this->selectedUser->update([
                        'is_active' => false,
                        'approval_status' => 'suspended'
                    ]);
                    session()->flash('success', 'User deactivated successfully.');
                    break;

                case 'approve':
                    $this->selectedUser->update([
                        'approval_status' => 'approved',
                        'approved_at' => now(),
                        'approved_by' => auth()->user()->name,
                        'is_active' => true
                    ]);
                    session()->flash('success', 'User approved successfully.');
                    break;

                case 'reject':
                    $this->selectedUser->update([
                        'approval_status' => 'rejected',
                        'rejection_reason' => $this->userNotes ?: 'Rejected by admin',
                        'is_active' => false
                    ]);
                    session()->flash('success', 'User rejected successfully.');
                    break;
            }

            $this->reset(['selectedUser', 'showUserModal', 'userAction', 'userNotes']);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to process user action: ' . $e->getMessage());
        }
    }

    // Computed Properties
    public function getInvitationRequestsProperty()
    {
        return InvitationRequest::with('reviewer')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'requests_page');
    }

    public function getInvitationsProperty()
    {
        return UserInvitation::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'invitations_page');
    }

    public function getUsersProperty()
    {
        return User::with(['branch', 'division', 'section'])
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'users_page');
    }

    public function render()
    {
        return view('livewire.admin.user-management')->with([
            'invitationRequests' => $this->invitationRequests,
            'invitations' => $this->invitations,
            'users' => $this->users,
            'pendingRequestsCount' => $this->pendingRequestsCount,
            'pendingInvitationsCount' => $this->pendingInvitationsCount,
        ]);
    }
}