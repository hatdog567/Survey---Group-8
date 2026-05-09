document.addEventListener('DOMContentLoaded', () => {
    // Fetch data from API
    fetch('../server/actions/api_reports.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                if (data.error === 'Unauthorized') {
                    window.location.href = 'index.html';
                } else {
                    console.error("API Error:", data.error);
                }
                return;
            }

            // Update Quick Stats
            document.getElementById('valFamilies').innerText = data.stats.totalFamilies;
            document.getElementById('valMembers').innerText = data.stats.totalMembers;
            document.getElementById('valDonors').innerText = data.stats.totalDonors;

            const commonOptions = {
                responsive: true,
                plugins: { legend: { position: 'bottom' } }
            };

            // 1. Blood Type Chart
            const bLabels = data.charts.blood.labels;
            const bData = data.charts.blood.data;
            if (bLabels.length > 0) {
                new Chart(document.getElementById('bloodTypeChart').getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: bLabels,
                        datasets: [{
                            data: bData,
                            backgroundColor: ['#dc2626', '#ef4444', '#f87171', '#fca5a5', '#991b1b', '#b91c1c', '#7f1d1d', '#fee2e2'],
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: commonOptions
                });
            } else {
                document.getElementById('bloodTypeChart').style.display = 'none';
                document.getElementById('noBloodData').style.display = 'block';
            }

            // 2. Donor Chart
            const dLabels = data.charts.donors.labels;
            const dData = data.charts.donors.data;
            if (dLabels.length > 0) {
                new Chart(document.getElementById('donorCountChart').getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: dLabels,
                        datasets: [{
                            label: 'New Donors',
                            data: dData,
                            backgroundColor: '#089700',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            } else {
                document.getElementById('donorCountChart').style.display = 'none';
                document.getElementById('noDonorData').style.display = 'block';
            }

            // 3. Vendor Chart
            const vLabels = data.charts.vendors.labels;
            const vData = data.charts.vendors.data;
            if (vLabels.length > 0) {
                new Chart(document.getElementById('vendorCountChart').getContext('2d'), {
                    type: 'pie',
                    data: {
                        labels: vLabels,
                        datasets: [{
                            data: vData,
                            backgroundColor: ['#16a34a', '#facc15', '#dc2626', '#64748b'],
                            borderWidth: 1,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: commonOptions
                });
            } else {
                document.getElementById('vendorCountChart').style.display = 'none';
                document.getElementById('noVendorData').style.display = 'block';
            }
        })
        .catch(error => {
            console.error("Fetch error:", error);
        });
});
