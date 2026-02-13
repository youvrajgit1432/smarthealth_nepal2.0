<?php
/**
 * Hospital Departments Management
 */

session_start();

// Check authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: /smarthealth_nepal/admin/hospital/login.php');
    exit;
}

// Set page variables
$pageTitle = 'Departments Management';
$activePage = 'departments';

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

<div class="section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Hospital Departments</h2>
        <button class="btn btn-primary" onclick="openAddDepartmentModal()">
            + Add Department
        </button>
    </div>

    <!-- Departments Grid -->
    <div class="departments-grid">
        <div class="department-card">
            <h4>Cardiology</h4>
            <p><strong>Max Capacity:</strong> 20</p>
            <p><strong>Avg Service Time:</strong> 30 mins</p>
            <p><strong>Current Load:</strong> 12 patients</p>
            <p><strong>Status:</strong> <span class="status-badge active">Active</span></p>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                <button class="btn btn-small btn-primary" onclick="editDepartment(1)">Edit</button>
                <button class="btn btn-small btn-danger" onclick="deleteDepartment(1)">Delete</button>
            </div>
        </div>

        <div class="department-card">
            <h4>Orthopedics</h4>
            <p><strong>Max Capacity:</strong> 15</p>
            <p><strong>Avg Service Time:</strong> 45 mins</p>
            <p><strong>Current Load:</strong> 8 patients</p>
            <p><strong>Status:</strong> <span class="status-badge active">Active</span></p>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                <button class="btn btn-small btn-primary" onclick="editDepartment(2)">Edit</button>
                <button class="btn btn-small btn-danger" onclick="deleteDepartment(2)">Delete</button>
            </div>
        </div>

        <div class="department-card">
            <h4>Pediatrics</h4>
            <p><strong>Max Capacity:</strong> 25</p>
            <p><strong>Avg Service Time:</strong> 20 mins</p>
            <p><strong>Current Load:</strong> 18 patients</p>
            <p><strong>Status:</strong> <span class="status-badge active">Active</span></p>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                <button class="btn btn-small btn-primary" onclick="editDepartment(3)">Edit</button>
                <button class="btn btn-small btn-danger" onclick="deleteDepartment(3)">Delete</button>
            </div>
        </div>

        <div class="department-card">
            <h4>Neurology</h4>
            <p><strong>Max Capacity:</strong> 18</p>
            <p><strong>Avg Service Time:</strong> 35 mins</p>
            <p><strong>Current Load:</strong> 10 patients</p>
            <p><strong>Status:</strong> <span class="status-badge inactive">Inactive</span></p>
            <div style="margin-top: 12px; display: flex; gap: 8px;">
                <button class="btn btn-small btn-primary" onclick="editDepartment(4)">Edit</button>
                <button class="btn btn-small btn-danger" onclick="deleteDepartment(4)">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit Department -->
<div id="departmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
    <div style="background: white; padding: 30px; border-radius: 8px; width: 90%; max-width: 500px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <h3 id="modalTitle">Add Department</h3>
        
        <div class="form-group">
            <label>Department Name (English)</label>
            <input type="text" placeholder="e.g., Cardiology" id="deptNameEn">
        </div>

        <div class="form-group">
            <label>Department Name (Nepali)</label>
            <input type="text" placeholder="e.g., हृदय रोग" id="deptNameNe">
        </div>

        <div class="form-group">
            <label>Description (English)</label>
            <textarea placeholder="Department description" id="deptDescEn"></textarea>
        </div>

        <div class="form-group">
            <label>Description (Nepali)</label>
            <textarea placeholder="विभाग विवरण" id="deptDescNe"></textarea>
        </div>

        <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
            <div class="form-group">
                <label>Max Capacity</label>
                <input type="number" placeholder="20" id="deptCapacity" min="1">
            </div>

            <div class="form-group">
                <label>Avg Service Time (mins)</label>
                <input type="number" placeholder="30" id="deptServiceTime" min="1">
            </div>
        </div>

        <div class="form-group">
            <label>Status</label>
            <select id="deptStatus">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button class="btn btn-primary" onclick="saveDepartment()">Save Department</button>
            <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
        </div>
    </div>
</div>

<script>
function openAddDepartmentModal() {
    document.getElementById('modalTitle').innerText = 'Add Department';
    document.getElementById('departmentModal').style.display = 'flex';
    // Clear form
    document.getElementById('deptNameEn').value = '';
    document.getElementById('deptNameNe').value = '';
    document.getElementById('deptDescEn').value = '';
    document.getElementById('deptDescNe').value = '';
    document.getElementById('deptCapacity').value = '';
    document.getElementById('deptServiceTime').value = '';
    document.getElementById('deptStatus').value = '1';
}

function editDepartment(id) {
    document.getElementById('modalTitle').innerText = 'Edit Department';
    document.getElementById('departmentModal').style.display = 'flex';
    // In real implementation, fetch department data and populate form
    alert('Edit department ' + id);
}

function saveDepartment() {
    const name = document.getElementById('deptNameEn').value;
    if (!name.trim()) {
        alert('Please enter department name');
        return;
    }
    alert('Department saved successfully');
    closeModal();
}

function deleteDepartment(id) {
    if (confirm('Are you sure you want to delete this department?')) {
        alert('Department deleted successfully');
    }
}

function closeModal() {
    document.getElementById('departmentModal').style.display = 'none';
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modal = document.getElementById('departmentModal');
    if (event.target === modal) {
        closeModal();
    }
});
</script>

<style>
.departments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
}

.department-card {
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
}

.department-card:hover {
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.department-card h4 {
    color: #2c3e50;
    margin-bottom: 12px;
    font-weight: 600;
    font-size: 15px;
}

.department-card p {
    font-size: 13px;
    color: #8fa8ba;
    margin: 8px 0;
}
</style>

<?php
// Include footer
include '../layouts/footer.php';
?>
