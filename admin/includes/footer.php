        </main>
    </div>
    
    <!-- Overlay for mobile sidebar -->
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden" @click="sidebarOpen = false"></div>
    
    <!-- Success/Error Messages -->
    <div id="messageContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>
    
    <!-- Scripts -->
    <script>
        // Success/Error message display
        function showMessage(message, type = 'success') {
            const container = document.getElementById('messageContainer');
            const messageDiv = document.createElement('div');
            
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            const icon = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
            
            messageDiv.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center transform translate-x-full transition-transform duration-300`;
            messageDiv.innerHTML = `
                <i class="${icon} mr-3"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            `;
            
            container.appendChild(messageDiv);
            
            // Animate in
            setTimeout(() => {
                messageDiv.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                messageDiv.classList.add('translate-x-full');
                setTimeout(() => messageDiv.remove(), 300);
            }, 5000);
        }
        
        // Confirm delete actions
        function confirmDelete(message = 'Bu öğeyi silmek istediğinizden emin misiniz?') {
            return confirm(message);
        }
        
        // Image preview functionality
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                    document.getElementById(previewId).style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        
        // Auto-generate slug from title
        function generateSlug(title) {
            return title
                .toLowerCase()
                .replace(/ğ/g, 'g')
                .replace(/ü/g, 'u')
                .replace(/ş/g, 's')
                .replace(/ı/g, 'i')
                .replace(/ö/g, 'o')
                .replace(/ç/g, 'c')
                .replace(/[^a-z0-9\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .trim('-');
        }
        
        // Form validation
        function validateForm(formId) {
            const form = document.getElementById(formId);
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('border-red-500');
                    isValid = false;
                } else {
                    field.classList.remove('border-red-500');
                }
            });
            
            return isValid;
        }
        
        // AJAX form submission
        function submitForm(formId, successCallback) {
            const form = document.getElementById(formId);
            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    if (successCallback) successCallback(data);
                } else {
                    showMessage(data.message, 'error');
                }
            })
            .catch(error => {
                showMessage('Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
            });
        }
        
        // Check for URL parameters and show messages
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            showMessage(urlParams.get('success'), 'success');
        }
        if (urlParams.has('error')) {
            showMessage(urlParams.get('error'), 'error');
        }
        
        // Auto-hide mobile sidebar when clicking on content
        document.addEventListener('click', function(e) {
            if (window.innerWidth < 768) {
                const sidebar = document.querySelector('.sidebar');
                const sidebarButton = document.querySelector('[\\@click="sidebarOpen = !sidebarOpen"]');
                
                if (!sidebar.contains(e.target) && !sidebarButton.contains(e.target)) {
                    Alpine.store('sidebarOpen', false);
                }
            }
        });
    </script>
</body>
</html>
