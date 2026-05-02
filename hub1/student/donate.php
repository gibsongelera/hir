<?php
$page_title = 'Donate - Campus Relief Hub';
require_once __DIR__ . '/../includes/header.php';
require_student();

$me = $pdo->prepare("SELECT * FROM users WHERE id=?");
$me->execute([current_user_id()]);
$user = $me->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $type = sanitize($_POST['donation_type'] ?? 'item');
    $donor_name = sanitize($_POST['donor_name'] ?? ($user['first_name'] . ' ' . $user['last_name']));
    if (empty($donor_name)) $donor_name = $user['first_name'] . ' ' . $user['last_name'];

    if ($type === 'item') {
        $items = sanitize($_POST['items'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        if (empty($items)) {
            set_flash('danger', 'Please specify the items.');
            redirect(APP_URL . '/student/donate.php');
        }
        $pdo->prepare("INSERT INTO donations (user_id, donor_name, donation_type, items, phone) VALUES (?, ?, 'item', ?, ?)")
            ->execute([current_user_id(), $donor_name, $items, $phone]);
    } else {
        $amount = (float)($_POST['amount'] ?? 0);
        $method = sanitize($_POST['payment_method'] ?? '');
        if ($amount <= 0) {
            set_flash('danger', 'Please enter a valid amount.');
            redirect(APP_URL . '/student/donate.php');
        }
        $pdo->prepare("INSERT INTO donations (user_id, donor_name, donation_type, amount, payment_method) VALUES (?, ?, 'monetary', ?, ?)")
            ->execute([current_user_id(), $donor_name, $amount, $method]);
    }

    set_flash('success', 'Thank you for your donation! It has been recorded.');
    redirect(APP_URL . '/student/dashboard.php');
}
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="text-center mb-4">
                <h2 class="fw-bold"><i class="fas fa-hand-holding-heart text-primary me-2"></i>Make a Donation</h2>
                <p class="text-muted">Your donations directly feed ZPPSU students in need.</p>
            </div>

            <div class="form-card">
                <ul class="nav nav-pills nav-fill mb-4" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="pill" href="#itemDonate"><i class="fas fa-box-open me-1"></i> Item Donation</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="pill" href="#moneyDonate"><i class="fas fa-wallet me-1"></i> Monetary</a></li>
                </ul>

                <div class="tab-content">
                    <!-- Item Donation -->
                    <div class="tab-pane fade show active" id="itemDonate">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="donation_type" value="item">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Your Name</label>
                                <input type="text" name="donor_name" class="form-control" value="<?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Items to Donate <span class="text-danger">*</span></label>
                                <input type="text" name="items" class="form-control" placeholder="e.g. 5kg Rice, 10 Canned Goods" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control" value="<?= sanitize($user['contact_number'] ?? '') ?>" placeholder="09XXXXXXXXX">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold"><i class="fas fa-gift me-1"></i> Pledge Items</button>
                        </form>
                        <div class="mt-3 p-3 bg-light rounded text-center">
                            <h6 class="fw-bold"><i class="fas fa-map-marker-alt text-primary"></i> Drop-off Location</h6>
                            <p class="mb-0 small text-muted">Student Center, ZPPSU Campus<br>Mon-Fri, 9AM - 5PM</p>
                        </div>
                    </div>

                    <!-- Monetary Donation -->
                    <div class="tab-pane fade" id="moneyDonate">
                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="donation_type" value="monetary">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Your Name</label>
                                <input type="text" name="donor_name" class="form-control" value="<?= sanitize($user['first_name'] . ' ' . $user['last_name']) ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Amount (PHP) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="form-control" placeholder="Enter amount" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <select name="payment_method" class="form-select">
                                    <option value="GCash">GCash</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Cash">Cash</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-warning w-100 py-2 fw-bold"><i class="fas fa-coins me-1"></i> Log Monetary Donation</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
