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
            
            <!-- Quick action buttons for all users -->
            <div class="d-grid gap-2 gap-md-3 mt-4">
                <!-- Primary Button Row -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="/smarthealth_nepal/frontend/views/token/book.php" class="btn btn-light btn-lg px-4 flex-grow-1" style="min-width: 200px;">
                        <i class="fas fa-plus-circle me-2"></i><?php echo $lang['book_token'] ?? 'Book Token'; ?>
                    </a>
                    <a href="/smarthealth_nepal/frontend/views/tracking/index.php" class="btn btn-outline-light btn-lg px-4 flex-grow-1" style="min-width: 200px;" title="<?php echo $lang['track_token_title'] ?? 'Check Your Queue Status'; ?>">
                        <i class="fas fa-ticket-alt me-2"></i><?php echo $lang['track_token'] ?? 'Track Token'; ?>
                    </a>
                </div>

                <!-- Secondary Button Row -->
                <div class="d-flex flex-wrap gap-2">
                    <a href="/smarthealth_nepal/frontend/views/offline/assisted_booking.php" class="btn btn-outline-light px-4 flex-grow-1" style="min-width: 200px;" title="<?php echo $lang['assisted_booking_title'] ?? 'Assisted Booking Mode'; ?>">
                        <i class="fas fa-hands-helping me-2"></i><?php echo $lang['assisted_booking'] ?? 'Assisted Booking'; ?>
                    </a>
                    <a href="/smarthealth_nepal/frontend/views/offline/sms_booking.php" class="btn btn-outline-light px-4 flex-grow-1" style="min-width: 200px;" title="<?php echo $lang['sms_booking_title'] ?? 'Book via SMS'; ?>">
                        <i class="fas fa-sms me-2"></i><?php echo $lang['sms_booking'] ?? 'SMS Booking'; ?>
                    </a>
                </div>
            </div>

            <div class="mt-3">
                <small class="text-white-50">
                    <i class="fas fa-info-circle me-1"></i><?php echo $lang['quick_actions'] ?? 'Quick access to all your healthcare needs'; ?>
                </small>
            </div>
        </div>
        <div class="col-md-6 text-center">
            <div style="position: relative; padding: 30px;">
                <!-- Animated hospital icon with background -->
                <div style="
                    display: inline-block;
                    width: 180px;
                    height: 180px;
                    background: rgba(255, 255, 255, 0.1);
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    animation: pulse-animation 2s infinite;
                    border: 3px solid rgba(255, 255, 255, 0.2);
                ">
                    <i class="fas fa-hospital" style="font-size: 5rem; opacity: 0.9;"></i>
                </div>
                
                <!-- Quick stats below icon (optional) -->
                <div class="mt-4 text-center" style="background: rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 10px; backdrop-filter: blur(10px);">
                    <div class="row g-2">
                        <div class="col-4">
                            <small style="opacity: 0.8;"><i class="fas fa-hospital me-1"></i>100+ Hospitals</small>
                        </div>
                        <div class="col-4">
                            <small style="opacity: 0.8;"><i class="fas fa-users me-1"></i>50K+ Users</small>
                        </div>
                        <div class="col-4">
                            <small style="opacity: 0.8;"><i class="fas fa-clock me-1"></i>24/7 Support</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CSS Animations -->
    <style>
        @keyframes pulse-animation {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.3);
            }
            70% {
                box-shadow: 0 0 0 20px rgba(255, 255, 255, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
            }
        }

        .hero-section .btn {
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .hero-section .btn-light {
            background: #ffffff;
            color: #1565c0;
            border: none;
        }

        .hero-section .btn-light:hover {
            background: #f5f5f5;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            color: #0d3a99;
        }

        .hero-section .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: #ffffff;
            background: transparent;
        }

        .hero-section .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: #ffffff;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        @media (max-width: 768px) {
            .hero-section .btn {
                font-size: 0.95rem;
                padding: 0.6rem 1.2rem !important;
            }

            .hero-section .btn-lg {
                padding: 0.6rem 1.2rem;
                font-size: 0.95rem;
            }

            .hero-section .d-flex {
                flex-direction: column;
            }

            .hero-section .flex-grow-1 {
                width: 100% !important;
                min-width: unset !important;
            }
        }
    </style>
</div>

 
<!-- key features  -->
<section class="features py-5 mt-5" style="background: linear-gradient(135deg, #e3f2fd 0%, #e0f7fa 100%); position: relative;">
    <div class="container" style="position: relative; z-index: 1;">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold mb-2" style="color: #1565c0; text-shadow: 1px 1px 2px rgba(0,0,0,0.06);"><?php echo $lang['features_title'] ?? 'Why Choose SmartHealth Nepal?'; ?></h2>
            <div class="w-25 mx-auto" style="height:4px; background: linear-gradient(90deg, #1565c0, #0d7c5c, #009688); border-radius:2px;"></div>
            <p class="mt-3 lead" style="color: #424242;"><?php echo $lang['features_subtitle'] ?? 'Key capabilities to improve patient flow and care'; ?></p>
        </div>

        <div class="row g-4">
            <!-- Feature 1 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #1565c0; overflow: hidden;">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle mx-auto mb-3" style="width:84px;height:84px;display:flex;align-items:center;justify-content:center;background:rgba(21,101,192,0.08);">
                            <i class="fas fa-stethoscope" style="font-size:2rem;color:#1565c0;"></i>
                        </div>
                        <h5 class="card-title" style="color:#1565c0;font-weight:700;"><?php echo $lang['feature_1_title'] ?? 'Smart Triage'; ?></h5>
                        <p class="card-text" style="color:#37474f;"><?php echo $lang['feature_1_desc'] ?? 'Quick health assessment to prioritise care.'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Feature 2 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #009688; overflow: hidden;">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle mx-auto mb-3" style="width:84px;height:84px;display:flex;align-items:center;justify-content:center;background:rgba(0,150,136,0.08);">
                            <i class="fas fa-list-check" style="font-size:2rem;color:#009688;"></i>
                        </div>
                        <h5 class="card-title" style="color:#1565c0;font-weight:700;"><?php echo $lang['feature_2_title'] ?? 'Live Queue'; ?></h5>
                        <p class="card-text" style="color:#37474f;"><?php echo $lang['feature_2_desc'] ?? 'Track your position and wait times in real-time.'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #e53935; overflow: hidden;">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle mx-auto mb-3" style="width:84px;height:84px;display:flex;align-items:center;justify-content:center;background:rgba(229,57,53,0.08);">
                            <i class="fas fa-heartbeat" style="font-size:2rem;color:#e53935;"></i>
                        </div>
                        <h5 class="card-title" style="color:#1565c0;font-weight:700;"><?php echo $lang['feature_3_title'] ?? 'Chronic Care'; ?></h5>
                        <p class="card-text" style="color:#37474f;"><?php echo $lang['feature_3_desc'] ?? 'Follow-up reminders and longitudinal tracking.'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #4caf50; overflow: hidden;">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle mx-auto mb-3" style="width:84px;height:84px;display:flex;align-items:center;justify-content:center;background:rgba(76,175,80,0.08);">
                            <i class="fas fa-baby" style="font-size:2rem;color:#4caf50;"></i>
                        </div>
                        <h5 class="card-title" style="color:#1565c0;font-weight:700;"><?php echo $lang['feature_4_title'] ?? 'Maternal Health'; ?></h5>
                        <p class="card-text" style="color:#37474f;"><?php echo $lang['feature_4_desc'] ?? 'Pregnancy tracking and tailored care pathways.'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Feature 5 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #29b6f6; overflow: hidden;">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle mx-auto mb-3" style="width:84px;height:84px;display:flex;align-items:center;justify-content:center;background:rgba(41,182,246,0.08);">
                            <i class="fas fa-wifi" style="font-size:2rem;color:#29b6f6;"></i>
                        </div>
                        <h5 class="card-title" style="color:#1565c0;font-weight:700;"><?php echo $lang['feature_5_title'] ?? 'Offline Support'; ?></h5>
                        <p class="card-text" style="color:#37474f;"><?php echo $lang['feature_5_desc'] ?? 'Staff-assisted booking for low-connectivity users.'; ?></p>
                    </div>
                </div>
            </div>

            <!-- Feature 6 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #ffb300; overflow: hidden;">
                    <div class="card-body text-center p-4">
                        <div class="rounded-circle mx-auto mb-3" style="width:84px;height:84px;display:flex;align-items:center;justify-content:center;background:rgba(255,179,0,0.08);">
                            <i class="fas fa-sms" style="font-size:2rem;color:#ffb300;"></i>
                        </div>
                        <h5 class="card-title" style="color:#1565c0;font-weight:700;"><?php echo $lang['feature_6_title'] ?? 'SMS Updates'; ?></h5>
                        <p class="card-text" style="color:#37474f;"><?php echo $lang['feature_6_desc'] ?? 'SMS notifications for users without internet access.'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #features .card {
            transition: all 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        #features .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 18px 36px rgba(0,0,0,0.12) !important;
        }

        #features .card .rounded-circle i {
            transition: transform 0.25s ease;
        }

        #features .card:hover .rounded-circle i {
            transform: scale(1.12) rotate(6deg);
        }
    </style>
</section>

<!-- About Us (Styled) -->
<section class="about py-5 mt-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); color: #333; position: relative;">
    <div class="container" style="position: relative; z-index: 1;">
        <div class="text-center mb-4">
            <h2 class="display-5 fw-bold mb-2" style="color: #1565c0; text-shadow: 1px 1px 2px rgba(0,0,0,0.06);">
                <?php echo $lang['about_us'] ?? 'About SmartHealth Nepal'; ?>
            </h2>
            <div class="w-25 mx-auto" style="height: 4px; background: linear-gradient(90deg, #1565c0, #0d7c5c, #009688); border-radius: 2px;"></div>
        </div>

        <div class="row align-items-center">
            <div class="col-md-8">
                <p class="lead mb-3" style="color: #424242; font-weight: 500;">
                    <?php echo $lang['about_short_desc'] ?? 'SmartHealth Nepal is a sustainable digital patient flow and chronic care tracking system designed for government hospitals in Nepal.'; ?>
                </p>
                <p class="text-muted mb-0">
                    <?php echo $lang['about_note'] ?? 'Built for the NIST Tech Carnival 2.0 Hackathon | Team CIVIX'; ?>
                </p>
            </div>

            <div class="col-md-4 text-center">
                <div style="display:inline-block; background: rgba(255,255,255,0.8); padding: 18px; border-radius: 12px; box-shadow: 0 6px 18px rgba(0,0,0,0.06);">
                    <i class="fas fa-hospital fa-3x mb-2" style="color: #1565c0;"></i>
                    <div style="font-weight: 600; color: #1565c0;"><?php echo $lang['hospitals'] ?? 'Hospitals'; ?></div>
                    <small class="text-muted"><?php echo $lang['about_quick_stat'] ?? 'Connected facilities across regions'; ?></small>
                </div>
            </div>
        </div>
    </div>
</section>
 

<!-- How It Works Section -->
<section id="how-it-works" class="py-5 mt-5 mb-5" style="background: linear-gradient(135deg, #e3f2fd 0%, #e0f2f1 100%); color: #333; position: relative;">
    <div class="container" style="position: relative; z-index: 1;">
        <div class="text-center mb-5">
            <h2 class="display-4 fw-bold mb-3" style="color: #1565c0; text-shadow: 1px 1px 2px rgba(0,0,0,0.08);"><?php echo $lang['how_it_works_title'] ?? 'How SmartHealth Nepal Works'; ?></h2>
            <div class="w-25 mx-auto" style="height: 4px; background: linear-gradient(90deg, #1565c0, #0d7c5c, #009688); border-radius: 2px;"></div>
        </div>
        
        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #3f51b5;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 3.5rem; color: #3f51b5; margin-bottom: 20px;"><i class="fas fa-user-plus"></i></div>
                        <h5 class="card-title" style="color: #1565c0; font-weight: 700; margin-bottom: 15px;"><?php echo $lang['step_1_title'] ?? 'Step 1'; ?></h5>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6;"><?php echo $lang['step_1_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Step 2 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #4caf50;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 3.5rem; color: #4caf50; margin-bottom: 20px;"><i class="fas fa-hospital"></i></div>
                        <h5 class="card-title" style="color: #1565c0; font-weight: 700; margin-bottom: 15px;"><?php echo $lang['step_2_title'] ?? 'Step 2'; ?></h5>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6;"><?php echo $lang['step_2_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Step 3 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #fbc02d;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 3.5rem; color: #f57f17; margin-bottom: 20px;"><i class="fas fa-stethoscope"></i></div>
                        <h5 class="card-title" style="color: #1565c0; font-weight: 700; margin-bottom: 15px;"><?php echo $lang['step_3_title'] ?? 'Step 3'; ?></h5>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6;"><?php echo $lang['step_3_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Step 4 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #2196f3;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 3.5rem; color: #2196f3; margin-bottom: 20px;"><i class="fas fa-ticket-alt"></i></div>
                        <h5 class="card-title" style="color: #1565c0; font-weight: 700; margin-bottom: 15px;"><?php echo $lang['step_4_title'] ?? 'Step 4'; ?></h5>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6;"><?php echo $lang['step_4_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Step 5 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #f44336;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 3.5rem; color: #f44336; margin-bottom: 20px;"><i class="fas fa-map-marker-alt"></i></div>
                        <h5 class="card-title" style="color: #1565c0; font-weight: 700; margin-bottom: 15px;"><?php echo $lang['step_5_title'] ?? 'Step 5'; ?></h5>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6;"><?php echo $lang['step_5_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
            
            <!-- Step 6 -->
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: #ffffff; border-top: 5px solid #009688;">
                    <div class="card-body p-4 text-center">
                        <div style="font-size: 3.5rem; color: #009688; margin-bottom: 20px;"><i class="fas fa-heart"></i></div>
                        <h5 class="card-title" style="color: #1565c0; font-weight: 700; margin-bottom: 15px;"><?php echo $lang['step_6_title'] ?? 'Step 6'; ?></h5>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6;"><?php echo $lang['step_6_desc'] ?? ''; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #how-it-works .card {
            transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        #how-it-works


<!-- Offices Section -->
<section id="offices" class="py-5" style="background: linear-gradient(135deg, #f3e5f5 0%, #e8f5e9 100%); position: relative;">
    <div class="container" style="position: relative; z-index: 1;">
        <div class="text-center mb-5">
            <h2 class="display-4 fw-bold mb-3" style="color: #6a1b9a; text-shadow: 1px 1px 2px rgba(0,0,0,0.08);"><?php echo $lang['offices_title'] ?? 'Our Offices & Hospitals'; ?></h2>
            <div class="w-25 mx-auto" style="height: 4px; background: linear-gradient(90deg, #ff6b6b, #ffd93d, #6bcf7f); border-radius: 2px;"></div>
            <p class="mt-4 lead" style="color: #424242; font-weight: 400;"><?php echo $lang['offices_desc'] ?? 'Quality healthcare available at multiple locations near you'; ?></p>
        </div>
        
        <div class="row g-4">
            <!-- Kathmandu Office -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: linear-gradient(135deg, rgba(63, 81, 181, 0.12), rgba(103, 58, 183, 0.08)); border-left: 5px solid #3f51b5; border-top: 3px solid #3f51b5; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle p-3" style="background: rgba(63, 81, 181, 0.2); font-size: 1.5rem; color: #1a237e;">
                                <i class="fas fa-hospital"></i>
                            </div>
                            <h5 class="card-title" style="color: #1a237e; margin-bottom: 0; margin-left: 15px; font-weight: 700;"><?php echo $lang['kathmandu_office'] ?? 'Kathmandu Main Hospital'; ?></h5>
                        </div>
                        <div style="background: rgba(255,255,255,0.5); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-map-marker-alt me-2" style="color: #3f51b5;"></i><strong>Address:</strong> Kathmandu, Nepal
                            </p>
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-phone me-2" style="color: #3f51b5;"></i><strong>Phone:</strong> +977-1-XXXXXXX
                            </p>
                            <p class="card-text" style="color: #37474f;">
                                <i class="fas fa-clock me-2" style="color: #3f51b5;"></i><strong>Hours:</strong> 9 AM - 5 PM Daily
                            </p>
                        </div>
                        <a href="#" class="btn w-100" style="background: linear-gradient(90deg, #3f51b5, #1a237e); color: white; font-weight: 600; border: none;">
                            <i class="fas fa-arrow-right me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Bhaktapur Office -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.12), rgba(139, 195, 74, 0.08)); border-left: 5px solid #4caf50; border-top: 3px solid #4caf50; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle p-3" style="background: rgba(76, 175, 80, 0.2); font-size: 1.5rem; color: #1b5e20;">
                                <i class="fas fa-clinic-medical"></i>
                            </div>
                            <h5 class="card-title" style="color: #1b5e20; margin-bottom: 0; margin-left: 15px; font-weight: 700;"><?php echo $lang['bhaktapur_office'] ?? 'Bhaktapur Health Center'; ?></h5>
                        </div>
                        <div style="background: rgba(255,255,255,0.5); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-map-marker-alt me-2" style="color: #4caf50;"></i><strong>Address:</strong> Bhaktapur, Nepal
                            </p>
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-phone me-2" style="color: #4caf50;"></i><strong>Phone:</strong> +977-1-XXXXXXX
                            </p>
                            <p class="card-text" style="color: #37474f;">
                                <i class="fas fa-clock me-2" style="color: #4caf50;"></i><strong>Hours:</strong> 9 AM - 5 PM Daily
                            </p>
                        </div>
                        <a href="#" class="btn w-100" style="background: linear-gradient(90deg, #4caf50, #1b5e20); color: white; font-weight: 600; border: none;">
                            <i class="fas fa-arrow-right me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Lalitpur Office -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: linear-gradient(135deg, rgba(33, 150, 243, 0.12), rgba(66, 165, 245, 0.08)); border-left: 5px solid #2196f3; border-top: 3px solid #2196f3; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle p-3" style="background: rgba(33, 150, 243, 0.2); font-size: 1.5rem; color: #0d47a1;">
                                <i class="fas fa-stethoscope"></i>
                            </div>
                            <h5 class="card-title" style="color: #0d47a1; margin-bottom: 0; margin-left: 15px; font-weight: 700;"><?php echo $lang['lalitpur_office'] ?? 'Lalitpur Medical Clinic'; ?></h5>
                        </div>
                        <div style="background: rgba(255,255,255,0.5); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-map-marker-alt me-2" style="color: #2196f3;"></i><strong>Address:</strong> Lalitpur, Nepal
                            </p>
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-phone me-2" style="color: #2196f3;"></i><strong>Phone:</strong> +977-1-XXXXXXX
                            </p>
                            <p class="card-text" style="color: #37474f;">
                                <i class="fas fa-clock me-2" style="color: #2196f3;"></i><strong>Hours:</strong> 9 AM - 5 PM Daily
                            </p>
                        </div>
                        <a href="#" class="btn w-100" style="background: linear-gradient(90deg, #2196f3, #0d47a1); color: white; font-weight: 600; border: none;">
                            <i class="fas fa-arrow-right me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Pokhara Office -->
            <div class="col-md-6 mb-4">
                <div class="card h-100 shadow-sm border-0" style="background: linear-gradient(135deg, rgba(255, 152, 0, 0.12), rgba(255, 193, 7, 0.08)); border-left: 5px solid #ff9800; border-top: 3px solid #ff9800; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-4">
                            <div class="rounded-circle p-3" style="background: rgba(255, 152, 0, 0.2); font-size: 1.5rem; color: #e65100;">
                                <i class="fas fa-hospital-alt"></i>
                            </div>
                            <h5 class="card-title" style="color: #e65100; margin-bottom: 0; margin-left: 15px; font-weight: 700;"><?php echo $lang['pokhara_office'] ?? 'Pokhara Regional Hospital'; ?></h5>
                        </div>
                        <div style="background: rgba(255,255,255,0.5); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-map-marker-alt me-2" style="color: #ff9800;"></i><strong>Address:</strong> Pokhara, Nepal
                            </p>
                            <p class="card-text" style="color: #37474f; margin-bottom: 8px;">
                                <i class="fas fa-phone me-2" style="color: #ff9800;"></i><strong>Phone:</strong> +977-1-XXXXXXX
                            </p>
                            <p class="card-text" style="color: #37474f;">
                                <i class="fas fa-clock me-2" style="color: #ff9800;"></i><strong>Hours:</strong> 9 AM - 5 PM Daily
                            </p>
                        </div>
                        <a href="#" class="btn w-100" style="background: linear-gradient(90deg, #ff9800, #e65100); color: white; font-weight: 600; border: none;">
                            <i class="fas fa-arrow-right me-2"></i>Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #offices .card {
            transition: all 0.3s ease;
        }
        
        #offices .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15) !important;
        }

        #offices .btn:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
    </style>
</section>

<!-- About Section -->
<!-- About Section -->
<section id="about" class="py-5 mt-5" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); color: #333; position: relative;">
    <div class="container" style="position: relative; z-index: 1;">
        <div class="text-center mb-5">
            <h2 class="display-4 fw-bold mb-3" style="color: #1565c0; text-shadow: 1px 1px 2px rgba(0,0,0,0.08);"><?php echo $lang['about_section_title'] ?? 'About SmartHealth Nepal'; ?></h2>
            <div class="w-25 mx-auto" style="height: 4px; background: linear-gradient(90deg, #ff6b6b, #4ecdc4, #45b7d1); border-radius: 2px;"></div>
        </div>
        
        <!-- Main Description with Icon -->
        <div class="row align-items-center mb-5">
            <div class="col-lg-7">
                <div class="about-content" style="line-height: 1.8;">
                    <div class="mb-4">
                        <p class="lead" style="font-size: 1.1rem; color: #1565c0; font-weight: 500;">
                            <?php echo $lang['about_section_desc_1'] ?? 'SmartHealth Nepal is revolutionizing healthcare delivery in Nepal by providing an innovative, transparent, and patient-centric digital platform.'; ?>
                        </p>
                    </div>
                    <div class="mb-4">
                        <p style="font-size: 1rem; color: #424242; line-height: 1.6; font-weight: 400;">
                            <?php echo $lang['about_section_desc_2'] ?? 'Our system reduces hospital overcrowding, improves emergency response times, and provides continuous chronic disease monitoring with SMS reminders for patients without reliable internet access.'; ?>
                        </p>
                    </div>
                    <div class="d-flex gap-3 mt-4">
                        <div class="text-center" style="flex: 1; background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05)); padding: 20px; border-radius: 12px; border: 2px solid #81c784;">
                            <div class="display-5 mb-2" style="color: #2e7d32;"><i class="fas fa-hospital"></i></div>
                            <p class="small mb-0" style="color: #1b5e20; font-weight: 600;"><?php echo $lang['hospitals'] ?? 'Hospitals'; ?></p>
                            <p style="color: #558b2f; font-size: 0.9rem; font-weight: 400;">Connected Healthcare</p>
                        </div>
                        <div class="text-center" style="flex: 1; background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 152, 0, 0.05)); padding: 20px; border-radius: 12px; border: 2px solid #ffb74d;">
                            <div class="display-5 mb-2" style="color: #e65100;"><i class="fas fa-users"></i></div>
                            <p class="small mb-0" style="color: #bf360c; font-weight: 600;"><?php echo $lang['patients'] ?? 'Patients'; ?></p>
                            <p style="color: #ff6f00; font-size: 0.9rem; font-weight: 400;">Empowered Care</p>
                        </div>
                        <div class="text-center" style="flex: 1; background: linear-gradient(135deg, rgba(33, 150, 243, 0.1), rgba(33, 150, 243, 0.05)); padding: 20px; border-radius: 12px; border: 2px solid #64b5f6;">
                            <div class="display-5 mb-2" style="color: #0d47a1;"><i class="fas fa-globe"></i></div>
                            <p class="small mb-0" style="color: #1565c0; font-weight: 600;"><?php echo $lang['coverage'] ?? 'Coverage'; ?></p>
                            <p style="color: #1976d2; font-size: 0.9rem; font-weight: 400;">Nationwide</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 text-center">
                <div style="position: relative; padding: 40px;">
                    <i class="fas fa-heartbeat" style="font-size: 8rem; color: #ef5350; opacity: 0.15; position: relative; z-index: 1;"></i>
                    <div class="mt-4" style="background: linear-gradient(135deg, rgba(244, 67, 54, 0.08), rgba(233, 30, 99, 0.08)); padding: 20px; border-radius: 15px; border: 2px solid #ef5350;">
                        <p style="color: #c62828; font-size: 0.95rem; font-weight: 500;">Compassionate Healthcare</p>
                        <p style="color: #d32f2f; font-size: 1.3rem; font-weight: bold;">For Every Patient</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mission & Vision Cards -->
        <div class="row g-4 mt-2">
            <!-- Mission Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.12), rgba(139, 195, 74, 0.08)); border-left: 5px solid #4caf50;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle p-3" style="background: rgba(76, 175, 80, 0.2); font-size: 1.5rem; color: #2e7d32;">
                                <i class="fas fa-bullseye"></i>
                            </div>
                            <h5 class="card-title" style="color: #1b5e20; margin-bottom: 0; margin-left: 15px; font-weight: 600;"><?php echo $lang['about_mission'] ?? 'Our Mission'; ?></h5>
                        </div>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6; margin-top: 15px;">
                            <?php echo $lang['about_mission_desc'] ?? 'To revolutionize healthcare delivery by creating a transparent, efficient, and patient-focused digital ecosystem that empowers both healthcare providers and patients.'; ?>
                        </p>
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #c8e6c9;">
                            <small style="color: #2e7d32; font-weight: 500;"><i class="fas fa-check-circle me-2" style="color: #4caf50;"></i>Evidence-based care</small><br>
                            <small style="color: #2e7d32; margin-top: 8px; display: inline-block; font-weight: 500;"><i class="fas fa-check-circle me-2" style="color: #4caf50;"></i>Patient empowerment</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Vision Card -->
            <div class="col-md-6">
                <div class="card h-100 shadow-sm border-0" style="background: linear-gradient(135deg, rgba(33, 150, 243, 0.12), rgba(66, 165, 245, 0.08)); border-left: 5px solid #2196f3;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="rounded-circle p-3" style="background: rgba(33, 150, 243, 0.2); font-size: 1.5rem; color: #0d47a1;">
                                <i class="fas fa-eye"></i>
                            </div>
                            <h5 class="card-title" style="color: #0d47a1; margin-bottom: 0; margin-left: 15px; font-weight: 600;"><?php echo $lang['about_vision'] ?? 'Our Vision'; ?></h5>
                        </div>
                        <p class="card-text" style="color: #37474f; font-weight: 400; line-height: 1.6; margin-top: 15px;">
                            <?php echo $lang['about_vision_desc'] ?? 'A future where every Nepali has access to efficient, transparent, and compassionate healthcare through innovative digital solutions that bridge the gap between urban and rural areas.'; ?>
                        </p>
                        <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #bbdefb;">
                            <small style="color: #0d47a1; font-weight: 500;"><i class="fas fa-check-circle me-2" style="color: #2196f3;"></i>Universal access</small><br>
                            <small style="color: #0d47a1; margin-top: 8px; display: inline-block; font-weight: 500;"><i class="fas fa-check-circle me-2" style="color: #2196f3;"></i>Digital inclusion</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Core Values -->
        <div class="row g-3 mt-4">
            <div class="col-6 col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, rgba(76, 175, 80, 0.1), rgba(76, 175, 80, 0.05)); border-radius: 12px; transition: all 0.3s; border: 2px solid #81c784;">
                    <i class="fas fa-shield-alt fa-2x mb-2" style="color: #2e7d32;"></i>
                    <p style="color: #1b5e20; font-size: 0.95rem; font-weight: 600; margin-bottom: 0;"><?php echo $lang['value_safety'] ?? 'Safety'; ?></p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, rgba(33, 150, 243, 0.1), rgba(33, 150, 243, 0.05)); border-radius: 12px; transition: all 0.3s; border: 2px solid #64b5f6;">
                    <i class="fas fa-lock fa-2x mb-2" style="color: #0d47a1;"></i>
                    <p style="color: #0d47a1; font-size: 0.95rem; font-weight: 600; margin-bottom: 0;"><?php echo $lang['value_privacy'] ?? 'Privacy'; ?></p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, rgba(255, 152, 0, 0.1), rgba(255, 152, 0, 0.05)); border-radius: 12px; transition: all 0.3s; border: 2px solid #ffb74d;">
                    <i class="fas fa-handshake fa-2x mb-2" style="color: #e65100;"></i>
                    <p style="color: #bf360c; font-size: 0.95rem; font-weight: 600; margin-bottom: 0;"><?php echo $lang['value_trust'] ?? 'Trust'; ?></p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="text-center p-3" style="background: linear-gradient(135deg, rgba(233, 30, 99, 0.1), rgba(233, 30, 99, 0.05)); border-radius: 12px; transition: all 0.3s; border: 2px solid #f48fb1;">
                    <i class="fas fa-lightbulb fa-2x mb-2" style="color: #880e4f;"></i>
                    <p style="color: #c2185b; font-size: 0.95rem; font-weight: 600; margin-bottom: 0;"><?php echo $lang['value_innovation'] ?? 'Innovation'; ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .card {
        transition: transform 0.3s, box-shadow 0.3s;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1) !important;
    }
</style>
<!-- Contact Section -->
<section id="contact" class="py-5" style="background: linear-gradient(135deg, #f3f6ff 0%, #eef7f5 100%); position: relative;">
    <div class="container" style="position: relative; z-index:1;">
        <div class="text-center mb-4">
            <h2 class="display-5 fw-bold mb-2" style="color: #1565c0; text-shadow: 1px 1px 2px rgba(0,0,0,0.06);"><?php echo $lang['contact_section_title'] ?? 'Contact Us'; ?></h2>
            <div class="w-25 mx-auto" style="height:4px; background: linear-gradient(90deg, #1565c0, #0d7c5c, #009688); border-radius:2px;"></div>
            <p class="mt-3 text-muted mb-0"><?php echo $lang['contact_desc'] ?? 'Questions or feedback? We are here to help.'; ?></p>
        </div>

        <div class="row g-4 align-items-stretch mt-4">
            <!-- Left: Contact cards -->
            <div class="col-lg-5">
                <div class="mb-4 p-3" style="background: rgba(255,255,255,0.9); border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.06);">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="font-size:1.6rem; color:#1565c0;"><i class="fas fa-phone-alt"></i></div>
                        <div>
                            <h6 class="mb-1" style="font-weight:700; color:#1565c0;"><?php echo $lang['contact_phone'] ?? 'Phone'; ?></h6>
                            <a href="tel:+977-1-XXXXXXX" style="color:#424242; text-decoration:none;">+977-1-XXXXXXX</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="font-size:1.6rem; color:#1565c0;"><i class="fas fa-envelope"></i></div>
                        <div>
                            <h6 class="mb-1" style="font-weight:700; color:#1565c0;"><?php echo $lang['contact_email'] ?? 'Email'; ?></h6>
                            <a href="mailto:info@smarthealth.npl" style="color:#424242; text-decoration:none;">info@smarthealth.npl</a>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div style="font-size:1.6rem; color:#1565c0;"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <h6 class="mb-1" style="font-weight:700; color:#1565c0;"><?php echo $lang['contact_address'] ?? 'Address'; ?></h6>
                            <div style="color:#424242;">Kathmandu, Nepal</div>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div style="font-size:1.6rem; color:#1565c0;"><i class="fas fa-clock"></i></div>
                        <div>
                            <h6 class="mb-1" style="font-weight:700; color:#1565c0;"><?php echo $lang['contact_hours'] ?? 'Office Hours'; ?></h6>
                            <div style="color:#424242;"><strong><?php echo $lang['contact_hours_value'] ?? '9 AM - 5 PM Daily'; ?></strong></div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-2">
                        <a href="tel:+977-1-XXXXXXX" class="btn btn-outline-primary flex-fill">
                            <i class="fas fa-phone me-2"></i><?php echo $lang['call_us'] ?? 'Call Us'; ?>
                        </a>
                        <a href="mailto:info@smarthealth.npl" class="btn btn-primary flex-fill">
                            <i class="fas fa-envelope me-2"></i><?php echo $lang['email_us'] ?? 'Email Us'; ?>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right: Contact form -->
            <div class="col-lg-7">
                <div style="background: rgba(255,255,255,0.95); padding:20px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.06);">
                    <form action="#" method="post" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contactName" class="form-label"><?php echo $lang['contact_form_name'] ?? 'Your Name'; ?></label>
                                <input type="text" class="form-control" id="contactName" name="name" placeholder="Enter your name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="contactEmail" class="form-label"><?php echo $lang['contact_form_email'] ?? 'Your Email'; ?></label>
                                <input type="email" class="form-control" id="contactEmail" name="email" placeholder="you@example.com" required>
                            </div>
                            <div class="col-12">
                                <label for="contactPhone" class="form-label"><?php echo $lang['contact_form_phone'] ?? 'Phone (optional)'; ?></label>
                                <input type="tel" class="form-control" id="contactPhone" name="phone" placeholder="+977-XXXXXXXXX">
                            </div>
                            <div class="col-12">
                                <label for="contactMessage" class="form-label"><?php echo $lang['contact_form_message'] ?? 'Your Message'; ?></label>
                                <textarea class="form-control" id="contactMessage" name="message" rows="5" placeholder="How can we help you?" required></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i><?php echo $lang['contact_send'] ?? 'Send Message'; ?>
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <?php echo $lang['contact_reset'] ?? 'Reset'; ?>
                                </button>
                                <div class="ms-auto text-muted align-self-center small"><?php echo $lang['contact_response_time'] ?? 'We usually respond within 24 hours.'; ?></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <style>
        #contact .btn-primary { background: linear-gradient(90deg,#1565c0,#0d7c5c); border: none; }
        #contact .btn-outline-primary { border-color: rgba(21,101,192,0.15); color:#1565c0; }
        #contact a.btn { box-shadow: 0 6px 18px rgba(0,0,0,0.06); }
        @media (max-width:767px) { #contact .text-center.mb-4 h2 { font-size:1.5rem; } }
    </style>
</section>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
