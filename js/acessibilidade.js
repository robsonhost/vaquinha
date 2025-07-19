// Melhorias de Acessibilidade e Feedback Visual
class Acessibilidade {
    constructor() {
        this.init();
    }

    init() {
        this.setupKeyboardNavigation();
        this.setupFocusManagement();
        this.setupScreenReaderSupport();
        this.setupLoadingStates();
        this.setupToastNotifications();
        this.setupFormValidation();
        this.setupSkipLinks();
    }

    // Navegação por teclado
    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Skip to main content
            if (e.key === 'Tab' && e.shiftKey === false) {
                const skipLink = document.querySelector('.skip-link');
                if (skipLink) {
                    skipLink.style.display = 'block';
                }
            }

            // ESC para fechar modais/dropdowns
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.modal.show');
                openModals.forEach(modal => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();
                });

                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(dropdown => {
                    const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown);
                    if (dropdownInstance) dropdownInstance.hide();
                });
            }
        });
    }

    // Gerenciamento de foco
    setupFocusManagement() {
        // Manter foco visível
        document.addEventListener('focusin', (e) => {
            e.target.style.outline = '2px solid var(--cor-primaria)';
            e.target.style.outlineOffset = '2px';
        });

        document.addEventListener('focusout', (e) => {
            e.target.style.outline = '';
            e.target.style.outlineOffset = '';
        });

        // Trap focus em modais
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    const focusableElements = modal.querySelectorAll(
                        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
                    );
                    const firstElement = focusableElements[0];
                    const lastElement = focusableElements[focusableElements.length - 1];

                    if (e.shiftKey) {
                        if (document.activeElement === firstElement) {
                            e.preventDefault();
                            lastElement.focus();
                        }
                    } else {
                        if (document.activeElement === lastElement) {
                            e.preventDefault();
                            firstElement.focus();
                        }
                    }
                }
            }
        });
    }

    // Suporte para leitores de tela
    setupScreenReaderSupport() {
        // Adicionar aria-labels dinâmicos
        const buttons = document.querySelectorAll('button:not([aria-label])');
        buttons.forEach(button => {
            if (button.textContent.trim()) {
                button.setAttribute('aria-label', button.textContent.trim());
            }
        });

        // Anúncios para leitores de tela
        this.announceToScreenReader = (message) => {
            const announcement = document.createElement('div');
            announcement.setAttribute('aria-live', 'polite');
            announcement.setAttribute('aria-atomic', 'true');
            announcement.className = 'sr-only';
            announcement.textContent = message;
            document.body.appendChild(announcement);
            
            setTimeout(() => {
                document.body.removeChild(announcement);
            }, 1000);
        };
    }

    // Estados de carregamento
    setupLoadingStates() {
        // Adicionar loading states aos botões
        document.addEventListener('click', (e) => {
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                const button = e.target.tagName === 'BUTTON' ? e.target : e.target.closest('button');
                if (button.type === 'submit' || button.classList.contains('btn-loading')) {
                    this.setButtonLoading(button, true);
                }
            }
        });

        // Loading state para formulários
        document.addEventListener('submit', (e) => {
            const form = e.target;
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton) {
                this.setButtonLoading(submitButton, true);
            }
        });
    }

    setButtonLoading(button, loading) {
        if (loading) {
            button.disabled = true;
            button.dataset.originalText = button.innerHTML;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Carregando...';
        } else {
            button.disabled = false;
            if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
                delete button.dataset.originalText;
            }
        }
    }

    // Notificações toast
    setupToastNotifications() {
        this.showToast = (message, type = 'info', duration = 5000) => {
            const toastContainer = document.getElementById('toast-container') || this.createToastContainer();
            
            const toast = document.createElement('div');
            toast.className = `toast align-items-center text-white bg-${type} border-0`;
            toast.setAttribute('role', 'alert');
            toast.setAttribute('aria-live', 'assertive');
            toast.setAttribute('aria-atomic', 'true');
            
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                </div>
            `;
            
            toastContainer.appendChild(toast);
            
            const bsToast = new bootstrap.Toast(toast, {
                autohide: true,
                delay: duration
            });
            
            bsToast.show();
            
            // Anunciar para leitores de tela
            this.announceToScreenReader(message);
            
            // Remover do DOM após esconder
            toast.addEventListener('hidden.bs.toast', () => {
                toastContainer.removeChild(toast);
            });
        };

        this.createToastContainer = () => {
            const container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
            return container;
        };
    }

    // Validação de formulários
    setupFormValidation() {
        document.addEventListener('input', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                this.validateField(e.target);
            }
        });

        document.addEventListener('blur', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') {
                this.validateField(e.target);
            }
        });
    }

    validateField(field) {
        const value = field.value.trim();
        const fieldType = field.type;
        const isRequired = field.hasAttribute('required');
        
        // Remover classes de erro anteriores
        field.classList.remove('is-invalid');
        field.classList.remove('is-valid');
        
        // Remover mensagens de erro anteriores
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
        
        let isValid = true;
        let errorMessage = '';
        
        // Validação de campo obrigatório
        if (isRequired && !value) {
            isValid = false;
            errorMessage = 'Este campo é obrigatório.';
        }
        
        // Validação de email
        if (fieldType === 'email' && value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Digite um email válido.';
            }
        }
        
        // Validação de senha
        if (fieldType === 'password' && value) {
            if (value.length < 6) {
                isValid = false;
                errorMessage = 'A senha deve ter pelo menos 6 caracteres.';
            }
        }
        
        // Aplicar resultado da validação
        if (isValid && value) {
            field.classList.add('is-valid');
        } else if (!isValid) {
            field.classList.add('is-invalid');
            this.showFieldError(field, errorMessage);
        }
    }

    showFieldError(field, message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    // Skip links para acessibilidade
    setupSkipLinks() {
        const skipLink = document.createElement('a');
        skipLink.href = '#main-content';
        skipLink.className = 'skip-link';
        skipLink.textContent = 'Pular para o conteúdo principal';
        skipLink.style.cssText = `
            position: absolute;
            top: -40px;
            left: 6px;
            background: var(--cor-primaria);
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 10000;
        `;
        
        document.body.insertBefore(skipLink, document.body.firstChild);
        
        // Mostrar skip link quando receber foco
        skipLink.addEventListener('focus', () => {
            skipLink.style.top = '6px';
        });
        
        skipLink.addEventListener('blur', () => {
            skipLink.style.top = '-40px';
        });
    }
}

// Inicializar quando o DOM estiver pronto
document.addEventListener('DOMContentLoaded', () => {
    window.acessibilidade = new Acessibilidade();
});

// Funções globais para uso em outros scripts
window.showToast = (message, type, duration) => {
    if (window.acessibilidade) {
        window.acessibilidade.showToast(message, type, duration);
    }
};

window.setButtonLoading = (button, loading) => {
    if (window.acessibilidade) {
        window.acessibilidade.setButtonLoading(button, loading);
    }
};

window.announceToScreenReader = (message) => {
    if (window.acessibilidade) {
        window.acessibilidade.announceToScreenReader(message);
    }
}; 