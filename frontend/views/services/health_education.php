<?php
require_once __DIR__ . '/../../../backend/init.php';
$lang_file = __DIR__ . '/../../../backend/lang/' . ($_SESSION['language'] ?? 'en') . '.php';
if (file_exists($lang_file)) require_once $lang_file;
$activePage = 'education';
require_once __DIR__ . '/../layouts/header.php';
?>
<div class="row justify-content-center mt-4">
    <div class="col-lg-10">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="fas fa-book"></i> Health Education & Tips</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-thermometer"></i> Fever Management</h5>
                                <ul>
                                    <li>Drink plenty of water to stay hydrated</li>
                                    <li>Rest adequately for recovery</li>
                                    <li>Use cool compress if needed</li>
                                    <li>Seek emergency care if difficulty breathing</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-heartbeat"></i> Chronic Disease Management</h5>
                                <ul>
                                    <li>Take medications regularly as prescribed</li>
                                    <li>Monitor blood pressure/blood sugar regularly</li>
                                    <li>Maintain regular hospital follow-ups</li>
                                    <li>Exercise and maintain healthy diet</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-baby"></i> Pregnancy Care</h5>
                                <ul>
                                    <li>Attend all antenatal checkups</li>
                                    <li>Take prescribed vitamins regularly</li>
                                    <li>Maintain healthy diet and rest</li>
                                    <li>Avoid heavy lifting and stress</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-syringe"></i> Vaccination</h5>
                                <ul>
                                    <li>Keep vaccination schedule updated</li>
                                    <li>Vaccinate children on time</li>
                                    <li>Get booster shots as recommended</li>
                                    <li>Maintain vaccination records</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php';?>