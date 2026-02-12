<?php
/**
 * Frontend Public Entry Point
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set language if switching
if (isset($_GET['lang'])) {
    $_SESSION['language'] = $_GET['lang'];
}

// Load language
$langFile = __DIR__ . '/../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($langFile)) {
    require_once $langFile;
} else {
    require_once __DIR__ . '/../../backend/lang/en.php';
}

$pageTitle = $lang['welcome'] ?? 'SmartHealth Nepal';
$activePage = 'home';

require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="hero-section bg-primary text-white py-5 rounded">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h2><?php echo $lang['hero_title'] ?? 'Healthcare Made Simple'; ?></h2>
            <p><?php echo $lang['hero_subtitle'] ?? 'Efficient, transparent, and patient-focused'; ?></p>
            
            <?php if (!isset($_SESSION['user_id'])): ?>
            <a href="/smarthealth_nepal/frontend/views/auth/login.php" class="btn btn-light btn-lg">
                <i class="fas fa-sign-in-alt"></i> <?php echo $lang['login'] ?? 'Login'; ?>
            </a>
            <?php else: ?>
            <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-light btn-lg">
                <i class="fas fa-plus-circle"></i> <?php echo $lang['book_token'] ?? 'Book Token'; ?>
            </a>
            <?php endif; ?>
        </div>
        <div class="col-md-6 text-center">
            <i class="fas fa-hospital" style="font-size: 5rem; opacity: 0.8;"></i>
        </div>
    </div>
</div>

<section class="features py-5 mt-5">
    <h2 class="text-center mb-5"><?php echo $lang['features_title'] ?? 'Why Choose SmartHealth Nepal?'; ?></h2>
    
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-stethoscope fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_1_title'] ?? 'Smart Triage'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_1_desc'] ?? 'Quick health assessment'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-list-check fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_2_title'] ?? 'Live Queue'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_2_desc'] ?? 'Track your position'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-heartbeat fa-3x text-primary mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_3_title'] ?? 'Chronic Care'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_3_desc'] ?? 'Follow-up reminders'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-baby fa-3x text-success mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_4_title'] ?? 'Maternal Health'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_4_desc'] ?? 'Pregnancy tracking'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-wifi fa-3x text-info mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_5_title'] ?? 'Offline Support'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_5_desc'] ?? 'Staff-assisted booking'; ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-sms fa-3x text-warning mb-3"></i>
                    <h5 class="card-title"><?php echo $lang['feature_6_title'] ?? 'SMS Updates'; ?></h5>
                    <p class="card-text"><?php echo $lang['feature_6_desc'] ?? 'No internet needed'; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="about py-5 bg-light rounded p-4">
    <h2><?php echo $lang['about_us'] ?? 'About SmartHealth Nepal'; ?></h2>
    <p>
        SmartHealth Nepal is a sustainable digital patient flow and chronic care tracking system designed for 
        government hospitals in Nepal. It addresses overcrowding, improves emergency response, and provides 
        continuous chronic disease monitoring with SMS reminders for patients without reliable internet access.
    </p>
    <p class="text-muted">
        Built for the NIST Tech Carnival 2.0 Hackathon | Team CIVIX
    </p>
</section>

<!-- How It Works Section -->
<section id="how-it-works" class="py-5 mt-5">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo $lang['how_it_works_title'] ?? 'How SmartHealth Nepal Works'; ?></h2>
        
        <div class="row">
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-primary mb-3"><i class="fas fa-user-plus"></i></div>
                        <h5 class="card-title"><?php echo $lang['step_1_title'] ?? 'Step 1'; ?></h5>
                        <p class="card-text"><?php echo $lang['step_1_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-success mb-3"><i class="fas fa-hospital"></i></div>
                        <h5 class="card-title"><?php echo $lang['step_2_title'] ?? 'Step 2'; ?></h5>
                        <p class="card-text"><?php echo $lang['step_2_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-warning mb-3"><i class="fas fa-stethoscope"></i></div>
                        <h5 class="card-title"><?php echo $lang['step_3_title'] ?? 'Step 3'; ?></h5>
                        <p class="card-text"><?php echo $lang['step_3_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-info mb-3"><i class="fas fa-ticket-alt"></i></div>
                        <h5 class="card-title"><?php echo $lang['step_4_title'] ?? 'Step 4'; ?></h5>
                        <p class="card-text"><?php echo $lang['step_4_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-danger mb-3"><i class="fas fa-map-marker-alt"></i></div>
                        <h5 class="card-title"><?php echo $lang['step_5_title'] ?? 'Step 5'; ?></h5>
                        <p class="card-text"><?php echo $lang['step_5_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-4 text-success mb-3"><i class="fas fa-heart"></i></div>
                        <h5 class="card-title"><?php echo $lang['step_6_title'] ?? 'Step 6'; ?></h5>
                        <p class="card-text"><?php echo $lang['step_6_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Offices Section -->
<section id="offices" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo $lang['offices_title'] ?? 'Our Offices & Hospitals'; ?></h2>
        <p class="text-center mb-4 lead"><?php echo $lang['offices_desc'] ?? ''; ?></p>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-hospital text-primary"></i> <?php echo $lang['kathmandu_office'] ?? 'Kathmandu Main Hospital'; ?></h5>
                        <p class="card-text">
                            <strong>Address:</strong> Kathmandu, Nepal<br>
                            <strong>Phone:</strong> +977-1-XXXXXXX<br>
                            <strong>Hours:</strong> 9 AM - 5 PM Daily
                        </p>
                        <a href="#" class="btn btn-sm btn-primary">Learn More</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-clinic-medical text-success"></i> <?php echo $lang['bhaktapur_office'] ?? 'Bhaktapur Health Center'; ?></h5>
                        <p class="card-text">
                            <strong>Address:</strong> Bhaktapur, Nepal<br>
                            <strong>Phone:</strong> +977-1-XXXXXXX<br>
                            <strong>Hours:</strong> 9 AM - 5 PM Daily
                        </p>
                        <a href="#" class="btn btn-sm btn-success">Learn More</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-stethoscope text-info"></i> <?php echo $lang['lalitpur_office'] ?? 'Lalitpur Medical Clinic'; ?></h5>
                        <p class="card-text">
                            <strong>Address:</strong> Lalitpur, Nepal<br>
                            <strong>Phone:</strong> +977-1-XXXXXXX<br>
                            <strong>Hours:</strong> 9 AM - 5 PM Daily
                        </p>
                        <a href="#" class="btn btn-sm btn-info">Learn More</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-hospital-alt text-warning"></i> <?php echo $lang['pokhara_office'] ?? 'Pokhara Regional Hospital'; ?></h5>
                        <p class="card-text">
                            <strong>Address:</strong> Pokhara, Nepal<br>
                            <strong>Phone:</strong> +977-1-XXXXXXX<br>
                            <strong>Hours:</strong> 9 AM - 5 PM Daily
                        </p>
                        <a href="#" class="btn btn-sm btn-warning">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- About Section -->
<section id="about" class="py-5 mt-5">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo $lang['about_section_title'] ?? 'About SmartHealth Nepal'; ?></h2>
        
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <p><?php echo $lang['about_section_desc_1'] ?? ''; ?></p>
                        <p><?php echo $lang['about_section_desc_2'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center">
                <i class="fas fa-heartbeat" style="font-size: 5rem; color: #0056b3; opacity: 0.2;"></i>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-left border-primary">
                    <div class="card-body">
                        <h5 class="card-title text-primary"><i class="fas fa-bullseye"></i> <?php echo $lang['about_mission'] ?? 'Our Mission'; ?></h5>
                        <p class="card-text"><?php echo $lang['about_mission_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-left border-success">
                    <div class="card-body">
                        <h5 class="card-title text-success"><i class="fas fa-eye"></i> <?php echo $lang['about_vision'] ?? 'Our Vision'; ?></h5>
                        <p class="card-text"><?php echo $lang['about_vision_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Section -->
<section id="contact" class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5"><?php echo $lang['contact_section_title'] ?? 'Contact Us'; ?></h2>
        <p class="text-center mb-5 lead"><?php echo $lang['contact_desc'] ?? ''; ?></p>
        
        <div class="row">
            <!-- Contact Information -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><i class="fas fa-info-circle text-primary"></i> Contact Information</h5>
                        
                        <div class="mb-4">
                            <h6 class="text-muted"><?php echo $lang['contact_phone'] ?? 'Phone'; ?></h6>
                            <p><strong>+977-1-XXXXXXX</strong></p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-muted"><?php echo $lang['contact_email'] ?? 'Email'; ?></h6>
                            <p><strong>info@smarthealth.npl</strong></p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-muted"><?php echo $lang['contact_address'] ?? 'Address'; ?></h6>
                            <p><strong>Kathmandu, Nepal</strong></p>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="text-muted"><?php echo $lang['contact_hours'] ?? 'Office Hours'; ?></h6>
                            <p><strong><?php echo $lang['contact_hours_value'] ?? ''; ?></strong></p>
                        </div>
                        
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <h6><?php echo $lang['contact_support'] ?? 'Technical Support'; ?></h6>
                                <p class="mb-0"><?php echo $lang['contact_support_hours'] ?? ''; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <h5 class="card-title mb-4"><i class="fas fa-envelope text-primary"></i> Send Us a Message</h5>
                        
                        <form>
                            <div class="mb-3">
                                <label for="contactName" class="form-label"><?php echo $lang['contact_form_name'] ?? 'Your Name'; ?></label>
                                <input type="text" class="form-control" id="contactName" placeholder="Enter your name" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contactEmail" class="form-label"><?php echo $lang['contact_form_email'] ?? 'Your Email'; ?></label>
                                <input type="email" class="form-control" id="contactEmail" placeholder="Enter your email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="contactMessage" class="form-label"><?php echo $lang['contact_form_message'] ?? 'Your Message'; ?></label>
                                <textarea class="form-control" id="contactMessage" rows="4" placeholder="Enter your message" required></textarea>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane"></i> <?php echo $lang['contact_send'] ?? 'Send Message'; ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
