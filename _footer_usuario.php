            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- Common User Panel Scripts -->
<script>
// Função para mostrar toast notifications
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toast-container') || (() => {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
        return container;
    })();
    
    const toastId = 'toast-' + Date.now();
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'info'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHtml);
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, { delay: 5000 });
    toast.show();
    
    // Remove toast element after it's hidden
    toastElement.addEventListener('hidden.bs.toast', () => {
        toastElement.remove();
    });
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            if (alert) {
                const bsAlert = new bootstrap.Alert(alert);
                if (bsAlert) {
                    bsAlert.close();
                }
            }
        });
    }, 5000);
});

// Confirm delete actions
function confirmDelete(message = 'Tem certeza que deseja excluir?') {
    return confirm(message);
}

// Format currency inputs
function formatCurrency(input) {
    let value = input.value.replace(/\D/g, '');
    value = (parseFloat(value) / 100).toFixed(2);
    input.value = value.replace('.', ',');
}

// Character counter for textareas
function setupCharacterCounters() {
    document.querySelectorAll('[data-char-limit]').forEach(function(element) {
        const limit = parseInt(element.getAttribute('data-char-limit'));
        const counterId = element.id + '-counter';
        
        // Create counter element if it doesn't exist
        if (!document.getElementById(counterId)) {
            const counter = document.createElement('small');
            counter.id = counterId;
            counter.className = 'text-muted';
            element.parentNode.appendChild(counter);
        }
        
        const counter = document.getElementById(counterId);
        
        function updateCounter() {
            const remaining = limit - element.value.length;
            counter.textContent = `${element.value.length}/${limit} caracteres`;
            
            if (remaining < 0) {
                counter.className = 'text-danger';
                element.value = element.value.substring(0, limit);
            } else if (remaining < 50) {
                counter.className = 'text-warning';
            } else {
                counter.className = 'text-muted';
            }
        }
        
        element.addEventListener('input', updateCounter);
        updateCounter(); // Initial update
    });
}

// Initialize character counters on page load
document.addEventListener('DOMContentLoaded', setupCharacterCounters);

// File upload preview
function setupFilePreview() {
    document.querySelectorAll('input[type="file"][data-preview]').forEach(function(input) {
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewId = input.getAttribute('data-preview');
            const preview = document.getElementById(previewId);
            
            if (file && preview) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.innerHTML = `<img src="${e.target.result}" class="img-fluid rounded" style="max-height: 200px;">`;
                    };
                    reader.readAsDataURL(file);
                } else {
                    preview.innerHTML = `<div class="alert alert-info"><i class="fas fa-file me-2"></i>${file.name}</div>`;
                }
            }
        });
    });
}

// Initialize file preview on page load
document.addEventListener('DOMContentLoaded', setupFilePreview);

// Auto-refresh notifications badge
function refreshNotificationsBadge() {
    fetch('api/notificacoes_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            if (data.count > 0) {
                if (badge) {
                    badge.textContent = data.count;
                } else {
                    const notifLink = document.querySelector('a[href="notificacoes.php"]');
                    if (notifLink) {
                        notifLink.innerHTML += `<span class="notification-badge">${data.count}</span>`;
                    }
                }
            } else if (badge) {
                badge.remove();
            }
        })
        .catch(error => console.log('Error refreshing notifications:', error));
}

// Refresh notifications every 30 seconds
setInterval(refreshNotificationsBadge, 30000);
</script>

</body>
</html>