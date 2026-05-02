
<?php
$page_title = 'Campus Relief Hub — Zero Hunger System';
$use_leaflet = true;
$no_container = true;
require_once __DIR__ . '/includes/header.php';
?>
 
<!-- ========== HERO — Full-screen campus photo, Paraform-style ========== -->
<section class="hero-section">
 
    <!-- Background: ZPPSU campus photo — place your image at assets/img/campus-bg.jpg -->
    <div class="hero-bg" style="background-image: url('<?= APP_URL ?>/assets/img/zppsu.jpg');"></div>
 
    <!-- Dark cinematic overlay -->
    <div class="hero-overlay"></div>
 
    <!-- Grain texture for cinematic depth -->
    <div class="hero-grain" aria-hidden="true"></div>
 
    <!-- Content -->
    <div class="container hero-content position-relative">
        <div class="row align-items-center" style="min-height:92vh;">
            <div class="col-lg-7">
 
                <div class="hero-eyebrow">
                    <span class="eyebrow-dot"></span>
                    ZPPSU Campus Initiative &nbsp;·&nbsp; SDG&nbsp;2 — Zero Hunger
                </div>
 
                <h1 class="hero-headline">
                    Feed the<br>
                    <em class="hero-headline-em">future.</em>
                </h1>
 
                <p class="hero-sub">
                    A confidential hub where ZPPSU students request or donate
                    assistance. No one goes hungry on this campus.
                </p>
 
                <div class="hero-ctas">
                    <a href="#ways-to-give" class="btn btn-gold-hero">
                        <i class="fas fa-heart me-2"></i>I Want to Help
                    </a>
                    <?php if (is_logged_in() && current_user_role() === 'student'): ?>
                        <a href="<?= APP_URL ?>/student/request.php" class="btn btn-ghost-hero">
                            <i class="fas fa-clipboard-list me-2"></i>Request Assistance
                        </a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-ghost-hero">
                            <i class="fas fa-clipboard-list me-2"></i>Student Assistance
                        </a>
                    <?php endif; ?>
                </div>
 
            </div>
        </div>
    </div>
 
    <!-- Animated scroll indicator -->
    <div class="hero-scroll-cue" aria-hidden="true">
        <span class="scroll-line"></span>
        <span class="scroll-label">scroll</span>
    </div>
 
    <!-- Bottom fade -->
    <div class="hero-fade-bottom" aria-hidden="true"></div>
</section>
 
<!-- ========== MAP SECTION ========== -->
<section id="map-section" class="map-section">
    <div class="container">
        <div class="sec-header text-center mb-5">
            <span class="sec-tag"><i class="fas fa-map-marked-alt me-1"></i>Campus Map</span>
            <h2 class="sec-title">Find Food Resources Near You</h2>
            <p class="sec-sub">Locate markets &amp; food resources near ZPPSU campus</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="map-shell">
                    <div id="campusMap"></div>
                    <div class="text-center mt-4">
                        <button class="btn btn-gold-solid px-4" onclick="findNearbyFood()">
                            <i class="fas fa-utensils me-2"></i>Find Food Nearby
                        </button>
                    </div>
                    <div id="nearbyFoodResults" class="mt-3" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>
</section>
 
<!-- ========== WAYS TO GIVE ========== -->
<section id="ways-to-give" class="give-section">
    <div class="container">
        <div class="sec-header text-center mb-5">
            <span class="sec-tag"><i class="fas fa-hands-helping me-1"></i>Get Involved</span>
            <h2 class="sec-title">Support the ZPPSU Community</h2>
            <p class="sec-sub">Choose how you want to make an impact today.</p>
        </div>
        <div class="row g-4">
 
            <div class="col-md-4">
                <div class="give-card">
                    <div class="give-card__num">01</div>
                    <div class="give-card__icon"><i class="fas fa-wallet"></i></div>
                    <h3>Donate Funds</h3>
                    <p>Send monetary support securely via GCash or Bank Transfer to fund daily student meals.</p>
                    <button class="btn btn-give" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-qrcode me-2"></i>Scan QR to Donate
                        <span class="btn-give__arrow"><i class="fas fa-arrow-right"></i></span>
                    </button>
                </div>
            </div>
 
            <div class="col-md-4">
                <div class="give-card ">
                    <div class="give-card__num">02</div>
                    <div class="give-card__icon"><i class="fas fa-box-open"></i></div>
                    <h3>Donate Items</h3>
                    <p>Pledge canned goods, rice, or daily essentials. Drop them off at the campus hub.</p>
                    <?php if (is_logged_in()): ?>
                        <a href="<?= APP_URL ?>/student/donate.php" class="btn btn-give ">
                            <i class="fas fa-hand-holding-heart me-2"></i>Pledge Items
                            <span class="btn-give__arrow"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-give 
                            <i class="fas fa-hand-holding-heart me-2"></i>Login to Pledge
                            <span class="btn-give__arrow"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
 
            <div class="col-md-4">
                <div class="give-card">
                    <div class="give-card__num">03</div>
                    <div class="give-card__icon"><i class="fas fa-hands-helping"></i></div>
                    <h3>Offer Assistance</h3>
                    <p>Register as a student volunteer to help organize, pack, and distribute relief goods.</p>
                    <?php if (is_logged_in()): ?>
                        <a href="<?= APP_URL ?>/student/volunteer.php" class="btn btn-give">
                            <i class="fas fa-users me-2"></i>Become a Volunteer
                            <span class="btn-give__arrow"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    <?php else: ?>
                        <a href="<?= APP_URL ?>/auth/login.php" class="btn btn-give">
                            <i class="fas fa-users me-2"></i>Login to Volunteer
                            <span class="btn-give__arrow"><i class="fas fa-arrow-right"></i></span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
 
        </div>
    </div>
</section>
 
<!-- ========== NUTRITION LOOKUP ========== -->
<section class="nutrition-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="nutrition-card text-center">
                    <div class="nutrition-card__icon">
                        <i class="fas fa-apple-alt"></i>
                    </div>
                    <h3>Nutrition Lookup</h3>
                    <p class="text-muted mb-4">Search food for calories, protein, carbs &amp; fat (USDA)</p>
                    <div class="nutrition-input mb-3">
                        <input type="text" id="nutritionQuery" class="form-control" placeholder="e.g. rice, chicken breast...">
                        <button class="btn btn-gold-solid" onclick="searchNutrition()">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <div id="nutritionResult" style="display:none;" class="text-start nutrition-result p-3 rounded"></div>
                </div>
            </div>
        </div>
    </div>
</section>
 
<!-- ========== PAYMENT MODAL — all logic preserved ========== -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-zppsu">
            <div class="modal-header modal-zppsu__header">
                <h5 class="modal-title"><i class="fas fa-hand-holding-usd me-2"></i>Donate Funds</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <ul class="nav nav-pills nav-fill modal-tabs mb-4" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="pill" href="#gcashTab">GCash</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#bankTab">Bank</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="gcashTab">
                        <div class="text-center mb-4">
                            <div class="qr-wrap">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=ZPPSU-RELIEF-GCASH-09940325665" alt="GCash QR">
                            </div>
                            <p class="mt-3 mb-0 fw-bold modal-num">09940325665</p>
                            <small class="text-muted">ZPPSU Relief Hub — GCash</small>
                        </div>
                        <form method="POST" action="<?= APP_URL ?>/student/donate.php">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="donation_type" value="monetary">
                            <input type="hidden" name="payment_method" value="GCash">
                            <input type="text" name="donor_name" class="form-control modal-input mb-3" placeholder="Your Name (Optional)">
                            <input type="number" name="amount" class="form-control modal-input mb-3" placeholder="Amount (PHP)" required min="1">
                            <button type="submit" class="btn btn-gold-solid w-100 fw-bold py-2">
                                <i class="fas fa-check me-2"></i>Log GCash Donation
                            </button>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="bankTab">
                        <div class="bank-box mb-4">
                            <div class="bank-row"><span>Bank</span><strong>BDO Unibank</strong></div>
                            <div class="bank-row"><span>Account Name</span><strong>ZPPSU Relief Hub</strong></div>
                            <div class="bank-row"><span>Account No.</span><strong class="text-maroon">0123456789</strong></div>
                        </div>
                        <form method="POST" action="<?= APP_URL ?>/student/donate.php">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="donation_type" value="monetary">
                            <input type="hidden" name="payment_method" value="Bank Transfer">
                            <input type="text" name="donor_name" class="form-control modal-input mb-3" placeholder="Your Name (Optional)">
                            <input type="number" name="amount" class="form-control modal-input mb-3" placeholder="Amount (PHP)" required min="1">
                            <button type="submit" class="btn btn-maroon-solid w-100 fw-bold py-2">
                                <i class="fas fa-check me-2"></i>Log Bank Transfer
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
 
<?php
$extra_js = 'main.js';
require_once __DIR__ . '/includes/footer.php';
?>