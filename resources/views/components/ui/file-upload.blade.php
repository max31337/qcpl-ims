@props([
    'label' => '',
    'accept' => 'image/*',
    'multiple' => false,
    'preview' => false,
    'currentImage' => null
])

<div x-data="fileUpload()" x-init="init()">
    @if($label)
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
        </label>
    @endif
    
    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-600 border-dashed rounded-md hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
        <div class="space-y-1 text-center">
            <!-- Live Preview -->
            <div x-show="previewUrl || currentImageUrl" class="mb-4">
                <img :src="previewUrl || currentImageUrl" 
                     alt="Preview" 
                     class="mx-auto h-32 w-32 object-cover rounded-lg border">
                <button @click="clearImage()" 
                        type="button"
                        class="mt-2 text-sm text-red-600 hover:text-red-800">
                    Remove Image
                </button>
            </div>
            
            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            
            <div class="flex text-sm text-gray-600 dark:text-gray-400">
                <label for="{{ $attributes['id'] ?? 'file-upload' }}" class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                    <span>Upload a file</span>
                    <input 
                        {{ $attributes->merge([
                            'id' => 'file-upload',
                            'type' => 'file',
                            'class' => 'sr-only',
                            'accept' => $accept
                        ]) }}
                        @if($multiple) multiple @endif
                        @change="handleFileSelect($event)"
                        x-ref="fileInput"
                    >
                </label>
                <p class="pl-1">or drag and drop</p>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                @if(str_contains($accept, 'image'))
                    PNG, JPG, GIF up to 2MB
                @else
                    {{ strtoupper(str_replace(['*/', 'image/'], '', $accept)) }} files
                @endif
            </p>
        </div>
    </div>
    
    @error($attributes['wire:model'] ?? '')
        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror

    <script>
        function fileUpload() {
            return {
                previewUrl: null,
                currentImageUrl: '{{ $currentImage }}',
                
                init() {
                    // Set current image if provided
                    this.currentImageUrl = '{{ $currentImage }}';
                },
                
                handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (file) {
                        // Create preview URL
                        this.previewUrl = URL.createObjectURL(file);
                        this.currentImageUrl = null; // Hide current image when new one is selected
                        
                        // Trigger Livewire file upload
                        this.$wire.upload('image', file, (uploadedFilename) => {
                            // Upload finished successfully
                        }, (error) => {
                            // Upload failed
                            console.error('Upload failed:', error);
                            this.clearImage();
                        });
                    }
                },
                
                clearImage() {
                    this.previewUrl = null;
                    this.currentImageUrl = null;
                    this.$refs.fileInput.value = '';
                    this.$wire.set('image', null);
                }
            }
        }
    </script>
</div>