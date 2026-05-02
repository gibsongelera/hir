<?php
$page_title = 'Request Assistance - Campus Relief Hub';
require_once __DIR__ . '/../includes/header.php';
require_student();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf($_POST['csrf_token'] ?? '')) {
    $item_type = sanitize($_POST['item_type']);
    $quantity  = (int)($_POST['quantity'] ?? 1);
    $urgency   = sanitize($_POST['urgency'] ?? 'medium');
    $details   = sanitize($_POST['details'] ?? '');

    if (empty($item_type)) {
        set_flash('danger', 'Please select an item type.');
        redirect(APP_URL . '/student/request.php');
    }

    $stmt = $pdo->prepare("INSERT INTO requests (user_id, item_type, quantity, urgency, details) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([current_user_id(), $item_type, $quantity, $urgency, $details]);

    set_flash('success', 'Assistance request submitted! Admin will review within 24 hours.');
    redirect(APP_URL . '/student/my-requests.php');
}
?>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="text-center mb-4">
                <h2 class="fw-bold"><i class="fas fa-clipboard-list text-primary me-2"></i>Request Assistance</h2>
                <p class="text-muted">Submit a food assistance request for admin review. Campus pickup only.</p>
            </div>

            <div class="form-card">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Item Requested <span class="text-danger">*</span></label>
                        <select name="item_type" class="form-select" required>
                            <option value="" disabled selected>Select item type</option>
                            <option value="Daily Meal Pack">Daily Meal Pack</option>
                            <option value="Groceries Bundle">Groceries Bundle</option>
                            <option value="Protein-Rich Foods">Protein-Rich Foods</option>
                            <option value="Fruits & Vegetables">Fruits & Vegetables</option>
                            <option value="Vitamins & Supplements">Vitamins & Supplements</option>
                            <option value="Rice (per kg)">Rice (per kg)</option>
                            <option value="Canned Goods">Canned Goods</option>
                            <option value="Instant Noodles">Instant Noodles</option>
                            <option value="Other">Other (specify in details)</option>
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Quantity</label>
                            <input type="number" name="quantity" class="form-control" value="1" min="1" max="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Urgency Level</label>
                            <select name="urgency" class="form-select">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Additional Details <small class="text-muted">(optional)</small></label>
                        <textarea name="details" class="form-control" rows="3" placeholder="Describe your specific needs for faster approval..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">
                        <i class="fas fa-paper-plane me-1"></i> Submit Request
                    </button>
                </form>

                <div class="text-center mt-3">
                    <small class="text-muted"><i class="fas fa-info-circle"></i> Admin reviews within 24 hours &bull; <a href="<?= APP_URL ?>/student/my-requests.php">View My Requests</a></small>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
