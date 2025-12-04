<?php include_once("../includes/admin-header.php"); ?>

<div class="page-header">
    <h2><i class="fa fa-dashboard"></i> Dashboard Overview</h2>
    <div>
        <button class="btn btn-primary" onclick="location.reload();">
            <i class="fa fa-refresh"></i> Refresh
        </button>
    </div>
</div>

<!-- Stat Cards -->
<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card blue">
            <h3>Total Members</h3>
            <div class="number" id="total-members">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
            <div class="change" id="member-growth"></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card green">
            <h3>Active Subscriptions</h3>
            <div class="number" id="active-subs">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
            <div class="change">Current active plans</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card orange">
            <h3>Monthly Revenue</h3>
            <div class="number" id="monthly-revenue">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
            <div class="change" id="revenue-growth"></div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="stat-card purple">
            <h3>Unread Messages</h3>
            <div class="number" id="unread-messages">
                <i class="fa fa-spinner fa-spin"></i>
            </div>
            <div class="change">Across all members</div>
        </div>
    </div>
</div>

<!-- Secondary Stats -->
<div class="row dashboard-row">
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <h4 style="margin-top: 0; color: #64748b; font-size: 0.9rem; text-transform: uppercase;">Pending Approvals</h4>
            <h2 id="pending-approvals" style="color: var(--danger-color); margin: 10px 0;">-</h2>
            <a href="members.php?filter=pending" class="btn btn-default btn-xs">View All</a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <h4 style="margin-top: 0; color: #64748b; font-size: 0.9rem; text-transform: uppercase;">Pending Interests</h4>
            <h2 id="pending-interests" style="color: var(--warning-color); margin: 10px 0;">-</h2>
            <a href="interest-logs.php?status=pending" class="btn btn-default btn-xs">View All</a>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <h4 style="margin-top: 0; color: #64748b; font-size: 0.9rem; text-transform: uppercase;">Active Members</h4>
            <h2 id="active-members" style="color: var(--success-color); margin: 10px 0;">-</h2>
            <small class="text-muted">Last 30 days</small>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card">
            <h4 style="margin-top: 0; color: #64748b; font-size: 0.9rem; text-transform: uppercase;">New This Week</h4>
            <h2 id="new-week" style="color: var(--primary-color); margin: 10px 0;">-</h2>
            <small class="text-muted">New registrations</small>
        </div>
    </div>
</div>

<!-- Charts and Tables Row -->
<div class="row dashboard-row">
    <!-- Plan Distribution -->
    <div class="col-md-6">
        <div class="card">
            <h4 style="margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                <i class="fa fa-pie-chart"></i> Plan Distribution
            </h4>
            <div id="plan-distribution" class="admin-table">
                <p style="text-align: center; padding: 40px; color: #95a5a6;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i><br>
                    Loading...
                </p>
            </div>
        </div>
    </div>
    
    <!-- Recent Registrations -->
    <div class="col-md-6">
        <div class="card">
            <h4 style="margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                <i class="fa fa-users"></i> Recent Registrations
            </h4>
            <div class="admin-table" id="recent-registrations">
                <p style="text-align: center; padding: 40px; color: #95a5a6;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i><br>
                    Loading...
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Recent Payments -->
<div class="row dashboard-row">
    <div class="col-md-12">
        <div class="card">
            <h4 style="margin-top: 0; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                <i class="fa fa-money"></i> Recent Payments
            </h4>
            <div class="admin-table" id="recent-payments">
                <p style="text-align: center; padding: 40px; color: #95a5a6;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i><br>
                    Loading...
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Load dashboard metrics
function loadMetrics() {
    fetch('api/metrics.php')
        .then(response => response.json())
        .then(data => {
            // Update stat cards
            document.getElementById('total-members').textContent = data.total_members || 0;
            document.getElementById('active-subs').textContent = data.active_subscriptions || 0;
            document.getElementById('monthly-revenue').textContent = '$' + (data.monthly_revenue || '0.00');
            document.getElementById('unread-messages').textContent = data.unread_messages || 0;
            
            // Update secondary stats
            document.getElementById('pending-approvals').textContent = data.pending_approvals || 0;
            document.getElementById('pending-interests').textContent = data.pending_interests || 0;
            document.getElementById('active-members').textContent = data.active_members || 0;
            document.getElementById('new-week').textContent = data.new_members_week || 0;
            
            // Update growth indicators
            const memberGrowth = data.member_growth_percent || 0;
            const memberGrowthEl = document.getElementById('member-growth');
            memberGrowthEl.className = 'change ' + (memberGrowth >= 0 ? 'up' : 'down');
            memberGrowthEl.innerHTML = '<i class="fa fa-arrow-' + (memberGrowth >= 0 ? 'up' : 'down') + '"></i> ' + 
                                       Math.abs(memberGrowth) + '% from last month';
            
            const revenueGrowth = data.revenue_growth_percent || 0;
            const revenueGrowthEl = document.getElementById('revenue-growth');
            revenueGrowthEl.className = 'change ' + (revenueGrowth >= 0 ? 'up' : 'down');
            revenueGrowthEl.innerHTML = '<i class="fa fa-arrow-' + (revenueGrowth >= 0 ? 'up' : 'down') + '"></i> ' + 
                                        Math.abs(revenueGrowth) + '% from last month';
            
            // Plan Distribution
            if (data.plan_distribution && data.plan_distribution.length > 0) {
                let html = '<table style="width: 100%;"><tbody>';
                data.plan_distribution.forEach(plan => {
                    html += '<tr>';
                    html += '<td style="padding: 10px;">' + plan.name + '</td>';
                    html += '<td style="padding: 10px; text-align: right;"><strong>' + plan.count + '</strong></td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                document.getElementById('plan-distribution').innerHTML = html;
            } else {
                document.getElementById('plan-distribution').innerHTML = '<p style="padding: 20px; text-align: center; color: #95a5a6;">No plan data available</p>';
            }
            
            // Recent Registrations
            if (data.recent_registrations && data.recent_registrations.length > 0) {
                let html = '<table><tbody>';
                data.recent_registrations.forEach(reg => {
                    html += '<tr>';
                    html += '<td><strong>' + reg.name + '</strong><br><small class="text-muted">' + reg.email + '</small></td>';
                    html += '<td style="text-align: right;"><small class="text-muted">' + reg.date + '</small></td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                document.getElementById('recent-registrations').innerHTML = html;
            } else {
                document.getElementById('recent-registrations').innerHTML = '<p style="padding: 20px; text-align: center; color: #95a5a6;">No recent registrations</p>';
            }
            
            // Recent Payments
            if (data.recent_payments && data.recent_payments.length > 0) {
                let html = '<table><thead><tr>';
                html += '<th>ID</th><th>User</th><th>Amount</th><th>Status</th><th>Date</th>';
                html += '</tr></thead><tbody>';
                data.recent_payments.forEach(payment => {
                    const statusClass = payment.status === 'completed' ? 'success' : 
                                       payment.status === 'pending' ? 'warning' : 'danger';
                    html += '<tr>';
                    html += '<td>#' + payment.id + '</td>';
                    html += '<td>' + payment.username + '<br><small class="text-muted">' + payment.email + '</small></td>';
                    html += '<td><strong>' + payment.currency + ' ' + payment.amount + '</strong></td>';
                    html += '<td><span class="badge badge-' + statusClass + '">' + payment.status + '</span></td>';
                    html += '<td><small class="text-muted">' + payment.created_at + '</small></td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
                document.getElementById('recent-payments').innerHTML = html;
            } else {
                document.getElementById('recent-payments').innerHTML = '<p style="padding: 20px; text-align: center; color: #95a5a6;">No recent payments</p>';
            }
        })
        .catch(error => {
            console.error('Error loading metrics:', error);
        });
}

// Load metrics on page load
loadMetrics();

// Auto-refresh every 60 seconds
setInterval(loadMetrics, 60000);
</script>

<?php include_once("../includes/admin-footer.php"); ?>
