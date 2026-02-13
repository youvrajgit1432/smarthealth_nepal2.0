<?php
/**
 * Hospital Reports
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Hospital Reports';
$activePage = 'reports';

// Set default session variables if missing
if (!isset($_SESSION['access_type'])) {
    $_SESSION['access_type'] = 'hospital';
}
if (!isset($_SESSION['hospital_id'])) {
    $_SESSION['hospital_id'] = null;
}
if (!isset($_SESSION['admin_name'])) {
    $_SESSION['admin_name'] = $_SESSION['admin_username'] ?? 'Admin';
}

// Include header
include '../layouts/header.php';
?>

<!-- Report Filters -->
<div class="section">
    <h3 style="margin-bottom: 20px;">Filter Reports</h3>
    <div class="form-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="form-group">
            <label>From Date</label>
            <input type="date">
        </div>
        <div class="form-group">
            <label>To Date</label>
            <input type="date">
        </div>
        <div class="form-group">
            <label>Department</label>
            <select>
                <option>All Departments</option>
                <option>Cardiology</option>
                <option>Orthopedics</option>
                <option>Pediatrics</option>
            </select>
        </div>
        <div class="form-group" style="display: flex; align-items: flex-end;">
            <button class="btn btn-primary">Generate Report</button>
        </div>
    </div>
</div>

<!-- Token Statistics Report -->
<div class="section">
    <h2>Token Statistics</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Total Tokens</th>
                <th>Completed</th>
                <th>Pending</th>
                <th>Missed</th>
                <th>Completion Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2026-02-13</td>
                <td>45</td>
                <td>38</td>
                <td>5</td>
                <td>2</td>
                <td><span class="status-badge success">84.4%</span></td>
            </tr>
            <tr>
                <td>2026-02-12</td>
                <td>52</td>
                <td>48</td>
                <td>3</td>
                <td>1</td>
                <td><span class="status-badge success">92.3%</span></td>
            </tr>
            <tr>
                <td>2026-02-11</td>
                <td>41</td>
                <td>35</td>
                <td>4</td>
                <td>2</td>
                <td><span class="status-badge success">85.4%</span></td>
            </tr>
            <tr>
                <td>2026-02-10</td>
                <td>58</td>
                <td>52</td>
                <td>4</td>
                <td>2</td>
                <td><span class="status-badge success">89.7%</span></td>
            </tr>
            <tr>
                <td>2026-02-09</td>
                <td>39</td>
                <td>33</td>
                <td>5</td>
                <td>1</td>
                <td><span class="status-badge success">84.6%</span></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Department Performance Report -->
<div class="section">
    <h2>Department Performance</h2>
    <table>
        <thead>
            <tr>
                <th>Department</th>
                <th>Tokens Issued</th>
                <th>Avg Wait Time</th>
                <th>Avg Service Time</th>
                <th>Patient Satisfaction</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Cardiology</strong></td>
                <td>156</td>
                <td>12 mins</td>
                <td>28 mins</td>
                <td>4.5/5 ⭐</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
            <tr>
                <td><strong>Orthopedics</strong></td>
                <td>128</td>
                <td>15 mins</td>
                <td>42 mins</td>
                <td>4.3/5 ⭐</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
            <tr>
                <td><strong>Pediatrics</strong></td>
                <td>94</td>
                <td>8 mins</td>
                <td>18 mins</td>
                <td>4.7/5 ⭐</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
            <tr>
                <td><strong>Neurology</strong></td>
                <td>67</td>
                <td>18 mins</td>
                <td>35 mins</td>
                <td>4.2/5 ⭐</td>
                <td><span class="status-badge inactive">Inactive</span></td>
            </tr>
            <tr>
                <td><strong>General Medicine</strong></td>
                <td>189</td>
                <td>10 mins</td>
                <td>25 mins</td>
                <td>4.6/5 ⭐</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Assisted Booking Report -->
<div class="section">
    <h2>Assisted Bookings Report</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Walk-in Patients</th>
                <th>Booked via SMS</th>
                <th>Booked via App</th>
                <th>Total</th>
                <th>Success Rate</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>2026-02-13</td>
                <td>12</td>
                <td>8</td>
                <td>15</td>
                <td>35</td>
                <td><span class="status-badge success">94.3%</span></td>
            </tr>
            <tr>
                <td>2026-02-12</td>
                <td>18</td>
                <td>11</td>
                <td>22</td>
                <td>51</td>
                <td><span class="status-badge success">96.1%</span></td>
            </tr>
            <tr>
                <td>2026-02-11</td>
                <td>14</td>
                <td>9</td>
                <td>18</td>
                <td>41</td>
                <td><span class="status-badge success">92.7%</span></td>
            </tr>
            <tr>
                <td>2026-02-10</td>
                <td>16</td>
                <td>12</td>
                <td>20</td>
                <td>48</td>
                <td><span class="status-badge success">95.8%</span></td>
            </tr>
            <tr>
                <td>2026-02-09</td>
                <td>13</td>
                <td>7</td>
                <td>17</td>
                <td>37</td>
                <td><span class="status-badge success">91.9%</span></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Staff Performance Report -->
<div class="section">
    <h2>Staff Performance</h2>
    <table>
        <thead>
            <tr>
                <th>Staff Name</th>
                <th>Role</th>
                <th>Patients Served</th>
                <th>Avg Rating</th>
                <th>Response Time</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Dr. Ramesh Patel</strong></td>
                <td>Doctor</td>
                <td>156</td>
                <td>4.8/5 ⭐</td>
                <td>2 mins</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
            <tr>
                <td><strong>Nurse Priya Sharma</strong></td>
                <td>Nurse</td>
                <td>234</td>
                <td>4.6/5 ⭐</td>
                <td>1 min</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
            <tr>
                <td><strong>Receptionist Anita K</strong></td>
                <td>Receptionist</td>
                <td>89</td>
                <td>4.4/5 ⭐</td>
                <td>3 mins</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
            <tr>
                <td><strong>Lab Tech Suman Singh</strong></td>
                <td>Lab Technician</td>
                <td>167</td>
                <td>4.7/5 ⭐</td>
                <td>5 mins</td>
                <td><span class="status-badge active">Active</span></td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Emergency Queue Report -->
<div class="section">
    <h2>Emergency Queue Statistics</h2>
    <div class="dashboard-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="stat-card emergency">
            <h3>Emergency Calls Today</h3>
            <div class="number">8</div>
            <div class="subtitle">3 resolved, 5 pending</div>
        </div>
        <div class="stat-card emergency">
            <h3>Avg Response Time</h3>
            <div class="number">4.2 min</div>
            <div class="subtitle">From call to action</div>
        </div>
        <div class="stat-card emergency">
            <h3>Critical Cases</h3>
            <div class="number">2</div>
            <div class="subtitle">Currently being treated</div>
        </div>
        <div class="stat-card emergency">
            <h3>Referrals Sent</h3>
            <div class="number">1</div>
            <div class="subtitle">To higher facility</div>
        </div>
    </div>
</div>

<!-- Export Options -->
<div class="section" style="text-align: center; margin-top: 30px;">
    <h3 style="margin-bottom: 20px;">Export Reports</h3>
    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <button class="btn btn-primary">📄 Export as PDF</button>
        <button class="btn btn-success">📊 Export as Excel</button>
        <button class="btn btn-info" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); color: white;">📧 Email Report</button>
        <button class="btn btn-secondary">🖨️ Print</button>
    </div>
</div>

<style>
.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.stat-card {
    text-align: center;
}

.stat-card .number {
    font-size: 32px;
}
</style>

<?php
// Include footer
include '../layouts/footer.php';
?>
