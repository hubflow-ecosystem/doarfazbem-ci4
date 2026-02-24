/**
 * Alpine.js Components
 * Componentes reutilizáveis para DoarFazBem
 */

// ==============================================
// COMPONENTE: Donation Form (Formulário de Doação)
// ==============================================
window.donationForm = function(campaignId, campaignType = 'medical', campaignSlug = '', initialRewardId = null, initialMinAmount = 10) {
    return {
        // State
        amount: 50,
        minAmount: initialMinAmount || ((campaignSlug === 'mantenha-a-plataforma-ativa') ? 5 : 10),
        paymentMethod: 'pix',
        donorPaysGatewayFee: true, // Marcado por padrão
        donateToPlatform: true, // Marcado por padrão (mas pode ser desmarcado)
        selectedRewardId: initialRewardId, // Recompensa selecionada
        isRecurring: false, // Doação recorrente

        // Computed - Nome do método de pagamento
        get paymentMethodName() {
            if (this.paymentMethod === 'pix') return 'PIX';
            if (this.paymentMethod === 'boleto') return 'Boleto';
            if (this.paymentMethod === 'credit_card') return 'Cartão';
            return '';
        },

        // Computed - Gateway Fee (taxa do gateway)
        get gatewayFee() {
            if (!this.donorPaysGatewayFee) return 0;

            if (this.paymentMethod === 'pix') return 0.95;
            if (this.paymentMethod === 'boleto') return 0.99;
            // Cartão: R$ 0,49 + 1,99%
            return 0.49 + (this.amount * 0.0199);
        },

        // Computed - Subtotal (sem arredondamento)
        get subtotal() {
            let total = this.amount;

            if (this.donorPaysGatewayFee) {
                total += this.gatewayFee;
            }

            return total;
        },

        // Computed - Arredondamento
        get roundingExtra() {
            return Math.ceil(this.subtotal) - this.subtotal;
        },

        // Computed - Total Amount (arredondado para cima)
        get totalAmount() {
            return Math.ceil(this.subtotal);
        },

        // Computed - Valor efetivamente pago pelo doador
        get amountPaidByDonor() {
            return this.totalAmount;
        },

        // Computed - Valor que vai para o criador
        get amountToCreator() {
            let toCreator = this.amount;

            // Se doador NÃO paga gateway, desconta do valor da doação
            if (!this.donorPaysGatewayFee) {
                toCreator -= this.gatewayFee;
            }

            return Math.max(0, toCreator);
        },

        // Helper - Format Money
        formatMoney(value) {
            return 'R$ ' + value.toFixed(2).replace('.', ',');
        },

        // Métodos
        selectAmount(value) {
            this.amount = value;
        },

        // Selecionar recompensa
        selectReward(rewardId, minAmount) {
            this.selectedRewardId = rewardId;
            this.minAmount = minAmount;
            // Se o valor atual for menor que o mínimo da recompensa, atualiza
            if (this.amount < minAmount) {
                this.amount = minAmount;
            }
        }
    };
};

// ==============================================
// COMPONENTE: Campaign Filter (Filtro de Campanhas)
// ==============================================
window.campaignFilter = function(initialCampaigns = []) {
    return {
        campaigns: initialCampaigns,
        filteredCampaigns: initialCampaigns,
        category: 'all',
        search: '',
        sortBy: 'recent',

        get campaignCount() {
            return this.filteredCampaigns.length;
        },

        // Helper para formatar valores em BRL
        formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value || 0);
        },

        filterCampaigns() {
            let filtered = this.campaigns;

            // Filter by category
            if (this.category !== 'all') {
                filtered = filtered.filter(c => c.category === this.category);
            }

            // Filter by search
            if (this.search) {
                const searchLower = this.search.toLowerCase();
                filtered = filtered.filter(c =>
                    c.title.toLowerCase().includes(searchLower) ||
                    c.description.toLowerCase().includes(searchLower)
                );
            }

            // Sort
            if (this.sortBy === 'recent') {
                filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            } else if (this.sortBy === 'progress') {
                filtered.sort((a, b) => b.percentage - a.percentage);
            } else if (this.sortBy === 'goal') {
                filtered.sort((a, b) => b.goal_amount - a.goal_amount);
            }

            this.filteredCampaigns = filtered;
        },

        init() {
            this.$watch('category', () => this.filterCampaigns());
            this.$watch('search', () => this.filterCampaigns());
            this.$watch('sortBy', () => this.filterCampaigns());
        }
    };
};

// Outros componentes permanecem iguais...
window.progressBar = function(current, goal) {
    return {
        current: current,
        goal: goal,
        get percentage() {
            if (this.goal === 0) return 0;
            const pct = (this.current / this.goal) * 100;
            return Math.min(Math.round(pct), 100);
        },
        get progressColor() {
            if (this.percentage >= 100) return 'bg-green-500';
            if (this.percentage >= 75) return 'bg-primary-500';
            if (this.percentage >= 50) return 'bg-blue-500';
            if (this.percentage >= 25) return 'bg-yellow-500';
            return 'bg-orange-500';
        },
        formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {style: 'currency', currency: 'BRL'}).format(value);
        }
    };
};

window.modal = function() {
    return {open: false, show() {this.open = true; document.body.style.overflow = 'hidden';}, hide() {this.open = false; document.body.style.overflow = '';}, toggle() {this.open ? this.hide() : this.show();}};
};

window.dropdown = function() {
    return {open: false, toggle() {this.open = !this.open;}, close() {this.open = false;}};
};

window.tabs = function(defaultTab = 0) {
    return {activeTab: defaultTab, isActive(tab) {return this.activeTab === tab;}, setActive(tab) {this.activeTab = tab;}};
};

window.toast = function(message, type = 'info', duration = 5000) {
    return {
        visible: false, message: message, type: type,
        show() {this.visible = true; if (duration > 0) {setTimeout(() => {this.hide();}, duration);}},
        hide() {this.visible = false;},
        get bgColor() {const colors = {success: 'bg-green-500', error: 'bg-red-500', warning: 'bg-yellow-500', info: 'bg-blue-500'}; return colors[this.type] || colors.info;},
        get icon() {const icons = {success: 'fa-check-circle', error: 'fa-exclamation-circle', warning: 'fa-exclamation-triangle', info: 'fa-info-circle'}; return icons[this.type] || icons.info;}
    };
};

console.log('Alpine.js components loaded successfully!');
