/**
 * Tremor-Style Components for Alpine.js
 * Components visuais modernos inspirados no Tremor
 */

// ========================================
// 1. METRIC CARD (KPI Card)
// ========================================
window.metricCard = function(value, previousValue, title, icon = 'chart-line') {
    return {
        value: value,
        previousValue: previousValue,
        title: title,
        icon: icon,

        get changePercent() {
            if (!this.previousValue || this.previousValue === 0) return 0;
            return ((this.value - this.previousValue) / this.previousValue) * 100;
        },

        get isPositive() {
            return this.changePercent >= 0;
        },

        get trendIcon() {
            return this.isPositive ? 'fa-arrow-up' : 'fa-arrow-down';
        },

        get trendColor() {
            return this.isPositive ? 'text-green-600' : 'text-red-600';
        },

        formatNumber(num) {
            if (num >= 1000000) {
                return (num / 1000000).toFixed(1) + 'M';
            }
            if (num >= 1000) {
                return (num / 1000).toFixed(1) + 'K';
            }
            return num.toLocaleString('pt-BR');
        }
    };
};

// ========================================
// 2. AREA CHART (usando Chart.js)
// ========================================
window.areaChart = function(labels, data, label = 'Valor') {
    return {
        chartId: 'chart-' + Math.random().toString(36).substr(2, 9),
        chart: null,

        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById(this.chartId);
                if (!ctx) return;

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: data,
                            fill: true,
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 0,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: 'rgb(16, 185, 129)',
                            pointHoverBorderColor: '#fff',
                            pointHoverBorderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    title: function(context) {
                                        return context[0].label;
                                    },
                                    label: function(context) {
                                        return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    color: '#6B7280'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    color: '#6B7280',
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return 'R$ ' + (value / 1000) + 'k';
                                        }
                                        return 'R$ ' + value;
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false
                        }
                    }
                });
            });
        },

        destroy() {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    };
};

// ========================================
// 3. BAR CHART (Gráfico de Barras)
// ========================================
window.barChart = function(labels, data, label = 'Valor', color = 'rgb(16, 185, 129)') {
    return {
        chartId: 'chart-' + Math.random().toString(36).substr(2, 9),
        chart: null,

        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById(this.chartId);
                if (!ctx) return;

                this.chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: label,
                            data: data,
                            backgroundColor: color,
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'R$ ' + context.parsed.y.toLocaleString('pt-BR', {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        });
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    color: '#6B7280'
                                }
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)',
                                    drawBorder: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    color: '#6B7280',
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return 'R$ ' + (value / 1000) + 'k';
                                        }
                                        return 'R$ ' + value;
                                    }
                                }
                            }
                        }
                    }
                });
            });
        },

        destroy() {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    };
};

// ========================================
// 4. DONUT CHART (Gráfico de Pizza)
// ========================================
window.donutChart = function(labels, data, colors) {
    return {
        chartId: 'chart-' + Math.random().toString(36).substr(2, 9),
        chart: null,

        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById(this.chartId);
                if (!ctx) return;

                this.chart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: colors || [
                                'rgb(16, 185, 129)',
                                'rgb(59, 130, 246)',
                                'rgb(251, 146, 60)',
                                'rgb(139, 92, 246)',
                                'rgb(236, 72, 153)'
                            ],
                            borderWidth: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: {
                                        size: 12
                                    },
                                    color: '#374151',
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                cornerRadius: 8,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percent = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + percent + '% (R$ ' +
                                               context.parsed.toLocaleString('pt-BR') + ')';
                                    }
                                }
                            }
                        }
                    }
                });
            });
        },

        destroy() {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    };
};

// ========================================
// 5. DATA TABLE (Tabela com sorting e filtros)
// ========================================
window.dataTable = function(initialData, columns) {
    return {
        data: initialData,
        columns: columns,
        sortColumn: '',
        sortDirection: 'asc',
        search: '',
        currentPage: 1,
        perPage: 10,

        get filteredData() {
            let filtered = this.data;

            // Filtro de busca
            if (this.search) {
                const searchLower = this.search.toLowerCase();
                filtered = filtered.filter(row => {
                    return Object.values(row).some(val =>
                        String(val).toLowerCase().includes(searchLower)
                    );
                });
            }

            // Ordenação
            if (this.sortColumn) {
                filtered = [...filtered].sort((a, b) => {
                    const aVal = a[this.sortColumn];
                    const bVal = b[this.sortColumn];

                    if (typeof aVal === 'number' && typeof bVal === 'number') {
                        return this.sortDirection === 'asc' ? aVal - bVal : bVal - aVal;
                    }

                    const aStr = String(aVal).toLowerCase();
                    const bStr = String(bVal).toLowerCase();

                    if (this.sortDirection === 'asc') {
                        return aStr.localeCompare(bStr);
                    } else {
                        return bStr.localeCompare(aStr);
                    }
                });
            }

            return filtered;
        },

        get paginatedData() {
            const start = (this.currentPage - 1) * this.perPage;
            const end = start + this.perPage;
            return this.filteredData.slice(start, end);
        },

        get totalPages() {
            return Math.ceil(this.filteredData.length / this.perPage);
        },

        sort(column) {
            if (this.sortColumn === column) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = column;
                this.sortDirection = 'asc';
            }
        },

        getSortIcon(column) {
            if (this.sortColumn !== column) return 'fa-sort';
            return this.sortDirection === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
        }
    };
};

// ========================================
// 6. SPARK LINE (Mini gráfico inline)
// ========================================
window.sparkLine = function(data, color = 'rgb(16, 185, 129)') {
    return {
        chartId: 'spark-' + Math.random().toString(36).substr(2, 9),
        chart: null,

        init() {
            this.$nextTick(() => {
                const ctx = document.getElementById(this.chartId);
                if (!ctx) return;

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.map((_, i) => i),
                        datasets: [{
                            data: data,
                            borderColor: color,
                            borderWidth: 2,
                            tension: 0.4,
                            pointRadius: 0
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false }
                        },
                        scales: {
                            x: { display: false },
                            y: { display: false }
                        }
                    }
                });
            });
        },

        destroy() {
            if (this.chart) {
                this.chart.destroy();
            }
        }
    };
};

// ========================================
// 7. PROGRESS CIRCLE (Círculo de progresso)
// ========================================
window.progressCircle = function(percentage, size = 120, strokeWidth = 8) {
    return {
        percentage: percentage,
        size: size,
        strokeWidth: strokeWidth,

        get radius() {
            return (this.size - this.strokeWidth) / 2;
        },

        get circumference() {
            return 2 * Math.PI * this.radius;
        },

        get strokeDashoffset() {
            return this.circumference - (this.percentage / 100) * this.circumference;
        },

        get color() {
            if (this.percentage >= 75) return '#10b981'; // green
            if (this.percentage >= 50) return '#3b82f6'; // blue
            if (this.percentage >= 25) return '#f59e0b'; // amber
            return '#ef4444'; // red
        }
    };
};
