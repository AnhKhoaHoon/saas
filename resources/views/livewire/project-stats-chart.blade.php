<div>
    <canvas id="usageChart" height="100"></canvas>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:initialized', () => {
            const ctx = document.getElementById('usageChart');
            
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @json($labels),
                    datasets: [
                        {
                            label: 'Total Requests',
                            data: @json($data),
                            borderColor: '#d6c6ae',
                            backgroundColor: 'rgba(214, 198, 174, 0.1)',
                            borderWidth: 2,
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Errors (4xx, 5xx)',
                            data: @json($errors),
                            borderColor: '#ff4a4a',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
                            borderDash: [5, 5],
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#333'
                            },
                            ticks: {
                                color: '#888'
                            }
                        },
                        x: {
                            grid: {
                                color: '#333'
                            },
                            ticks: {
                                color: '#888'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#d6c6ae'
                            }
                        }
                    }
                }
            });
        });
    </script>
</div>
