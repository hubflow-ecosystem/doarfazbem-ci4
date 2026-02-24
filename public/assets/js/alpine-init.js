/**
 * Alpine.js Initialization
 * DoarFazBem - Plataforma de Crowdfunding Social
 */

import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';

// Register Alpine plugins
Alpine.plugin(persist);
Alpine.plugin(focus);
Alpine.plugin(collapse);

// Global Alpine Store - Estado Global da Aplicação
Alpine.store('app', {
    // User state
    user: Alpine.$persist({
        id: null,
        name: null,
        email: null,
        role: null,
        isLoggedIn: false
    }).as('user'),

    // UI state
    ui: {
        sidebarOpen: false,
        mobileMenuOpen: false,
        modalOpen: false,
        notificationsOpen: false
    },

    // Notifications
    notifications: [],

    // Methods
    toggleSidebar() {
        this.ui.sidebarOpen = !this.ui.sidebarOpen;
    },

    toggleMobileMenu() {
        this.ui.mobileMenuOpen = !this.ui.mobileMenuOpen;
    },

    openModal() {
        this.ui.modalOpen = true;
        document.body.style.overflow = 'hidden';
    },

    closeModal() {
        this.ui.modalOpen = false;
        document.body.style.overflow = '';
    },

    addNotification(message, type = 'info') {
        const id = Date.now();
        this.notifications.push({ id, message, type });

        // Auto-remove after 5 seconds
        setTimeout(() => {
            this.removeNotification(id);
        }, 5000);
    },

    removeNotification(id) {
        this.notifications = this.notifications.filter(n => n.id !== id);
    },

    setUser(userData) {
        this.user = {
            id: userData.id,
            name: userData.name,
            email: userData.email,
            role: userData.role,
            isLoggedIn: true
        };
    },

    logout() {
        this.user = {
            id: null,
            name: null,
            email: null,
            role: null,
            isLoggedIn: false
        };
    }
});

// Global Alpine Data - Componentes Reutilizáveis
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

console.log('Alpine.js initialized successfully!');
