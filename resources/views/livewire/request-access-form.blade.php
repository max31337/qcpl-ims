<div>
    @if($submitted)
        <div class="text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-3">Request Submitted Successfully!</h3>
            <p class="text-gray-600 mb-6">Thank you for your interest in joining QCPL-IMS. An administrator will review your request and contact you via email with further instructions.</p>
            <button wire:click="$set('submitted', false)" class="text-blue-600 hover:text-blue-700 font-medium">
                Submit Another Request
            </button>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- First Name -->
                <div>
                    <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                        First Name *
                    </label>
                    <input wire:model="first_name" type="text" id="first_name" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                        placeholder="Enter your first name">
                    @error('first_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Last Name -->
                <div>
                    <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Last Name *
                    </label>
                    <input wire:model="last_name" type="text" id="last_name" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                        placeholder="Enter your last name">
                    @error('last_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Government Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                    Government Email Address *
                </label>
                <input wire:model="email" type="email" id="email" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                    placeholder="your.name@gov.ph or your.name@qc.gov.ph">
                <p class="mt-1 text-xs text-gray-500">Only government email addresses are accepted</p>
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Department -->
            <div>
                <label for="department" class="block text-sm font-medium text-gray-700 mb-2">
                    Department/Office
                </label>
                <input wire:model="department" type="text" id="department" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                    placeholder="e.g., Library Services, IT Department, etc.">
                @error('department')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Reason -->
            <div>
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    Reason for Access Request *
                </label>
                <textarea wire:model="reason" id="reason" rows="4" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors" 
                    placeholder="Please explain why you need access to the QCPL Inventory Management System. Include your role, responsibilities, and how you plan to use the system."></textarea>
                <p class="mt-1 text-xs text-gray-500">Minimum 20 characters</p>
                @error('reason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-700">
                        <p><strong>Please Note:</strong></p>
                        <ul class="mt-2 list-disc list-inside space-y-1">
                            <li>Only government employees with official email addresses can request access</li>
                            <li>Your request will be reviewed by system administrators</li>
                            <li>If approved, you will receive an invitation email with registration instructions</li>
                            <li>Processing may take 1-3 business days</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <button type="submit" 
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-6 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Submit Access Request
                </button>
            </div>
        </form>
    @endif
</div>
