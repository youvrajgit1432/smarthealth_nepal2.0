<?php
/**
 * OTP Debug Viewer - See OTP codes for testing
 */

require_once __DIR__ . '/../backend/config/database.php';

$title = 'OTP Debug Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="mb-4"><i class="fas fa-key"></i> OTP Debug Panel</h2>
            
            <div class="alert alert-warning">
                <strong>⚠️ For Development Only!</strong> 
                This page shows OTP codes for testing. Remove in production!
            </div>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Phone Number</th>
                            <th>OTP Code</th>
                            <th>MPIN</th>
                            <th>Status</th>
                            <th>Expires At</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $db->query("SELECT * FROM otp_sessions ORDER BY created_at DESC LIMIT 20");
                        
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $isExpired = strtotime($row['expires_at']) < time();
                                $rowClass = $isExpired ? 'table-danger' : '';
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['phone_number']); ?></strong>
                                    </td>
                                    <td>
                                        <code style="font-size: 18px; background-color: #f8f9fa; padding: 5px 10px;">
                                            <?php echo htmlspecialchars($row['otp_code']); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <code style="font-size: 18px; background-color: #f8f9fa; padding: 5px 10px;">
                                            <?php echo htmlspecialchars($row['mpin']); ?>
                                        </code>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $row['status'] === 'Verified' ? 'success' : 'primary'; ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['expires_at']); ?></td>
                                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No OTP sessions yet</td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="alert alert-info mt-4">
                <h5>How to Use for Testing:</h5>
                <ol>
                    <li>Go to <strong>/smarthealth_nepal/frontend/views/token/book.php</strong></li>
                    <li>Enter a phone number</li>
                    <li>Look at this page to see the generated OTP and MPIN</li>
                    <li>Enter the OTP code in the form</li>
                    <li>Complete the booking</li>
                </ol>
            </div>
            
            <div class="alert alert-danger mt-4">
                <h5>To Fix SMS Delivery:</h5>
                <ol>
                    <li>Log into <strong><a href="https://service.sparrowsms.com/" target="_blank">Sparrow SMS Dashboard</a></strong></li>
                    <li>Go to <strong>Settings → IP Whitelist</strong></li>
                    <li>Add IP: <code>110.44.118.114</code></li>
                    <li>Save and reload the page</li>
                </ol>
            </div>
            
            <a href="/smarthealth_nepal/" class="btn btn-secondary mt-4">
                <i class="fas fa-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>
