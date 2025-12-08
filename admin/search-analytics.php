<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: /login.php");
    exit();
}

require_once("../includes/dbconn.php");

$user_id = intval($_SESSION['id']);
$check_admin = mysqli_query($conn, "SELECT userlevel FROM users WHERE id = $user_id AND userlevel = 1");
if (mysqli_num_rows($check_admin) == 0) {
    header("Location: /index.php");
    exit();
}

include("../includes/admin-header.php");
?>

<div class="admin-content">
    <h1>Search Analytics</h1>
    <p class="text-muted">Monitor member search behavior and popular filters</p>
    
    <!-- Statistics Cards -->
    <div class="row" style="margin-bottom: 30px;">
        <div class="col-md-3">
            <div class="stat-card blue">
                <h3>Total Searches</h3>
                <div class="number" id="total-searches">-</div>
                <div class="change">Last 30 days</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card green">
                <h3>Saved Searches</h3>
                <div class="number" id="saved-searches">-</div>
                <div class="change">All members</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card orange">
                <h3>Avg Results</h3>
                <div class="number" id="avg-results">-</div>
                <div class="change">Per search</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card purple">
                <h3>Active Searchers</h3>
                <div class="number" id="active-searchers">-</div>
                <div class="change">Last 7 days</div>
            </div>
        </div>
    </div>
    
    <!-- Popular Filters -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Popular Search Filters</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Filter</th>
                                <th>Value</th>
                                <th>Count</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody id="popular-filters-tbody">
                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Recent Searches</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Filters Used</th>
                                <th>Results</th>
                                <th>Time</th>
                            </tr>
                        </thead>
                        <tbody id="recent-searches-tbody">
                            <tr><td colspan="4" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Search History Chart -->
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Search Activity (Last 7 Days)</h3>
                </div>
                <div class="card-body">
                    <canvas id="searchChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
$(document).ready(function() {
    loadAnalytics();
    loadPopularFilters();
    loadRecentSearches();
    loadSearchChart();
});

function loadAnalytics() {
    $.get('/admin/api/search-analytics.php', function(response) {
        if (response.success) {
            const stats = response.data;
            $('#total-searches').text(stats.total_searches.toLocaleString());
            $('#saved-searches').text(stats.saved_searches.toLocaleString());
            $('#avg-results').text(stats.avg_results.toFixed(1));
            $('#active-searchers').text(stats.active_searchers.toLocaleString());
        }
    });
}

function loadPopularFilters() {
    $.get('/admin/api/search-analytics.php?type=filters', function(response) {
        if (response.success) {
            const tbody = $('#popular-filters-tbody');
            tbody.empty();
            
            if (response.data.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center">No data yet</td></tr>');
                return;
            }
            
            const total = response.data.reduce((sum, item) => sum + item.count, 0);
            
            response.data.forEach(filter => {
                const percentage = ((filter.count / total) * 100).toFixed(1);
                tbody.append(`
                    <tr>
                        <td><strong>${filter.filter_name}</strong></td>
                        <td>${filter.filter_value}</td>
                        <td><span class="badge">${filter.count}</span></td>
                        <td>${percentage}%</td>
                    </tr>
                `);
            });
        }
    });
}

function loadRecentSearches() {
    $.get('/admin/api/search-analytics.php?type=recent', function(response) {
        if (response.success) {
            const tbody = $('#recent-searches-tbody');
            tbody.empty();
            
            if (response.data.length === 0) {
                tbody.append('<tr><td colspan="4" class="text-center">No searches yet</td></tr>');
                return;
            }
            
            response.data.forEach(search => {
                const filterCount = Object.keys(search.filters).length;
                const timeAgo = moment(search.searched_at).fromNow();
                
                tbody.append(`
                    <tr>
                        <td>${search.username}</td>
                        <td><span class="label label-info">${filterCount} filters</span></td>
                        <td><span class="badge">${search.results_count}</span></td>
                        <td>${timeAgo}</td>
                    </tr>
                `);
            });
        }
    });
}

function loadSearchChart() {
    $.get('/admin/api/search-analytics.php?type=chart', function(response) {
        if (response.success) {
            const ctx = document.getElementById('searchChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: response.data.labels,
                    datasets: [{
                        label: 'Searches',
                        data: response.data.counts,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        }
    });
}
</script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

<?php include("../includes/admin-footer.php"); ?>
