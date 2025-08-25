<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';


if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}
?>
<div class="modal" id="modal-discount" role="dialog" aria-modal="true" aria-labelledby="modal-discount-title" style="display:none;">
    <div class="modal-content">
        <h2 id="modal-discount-title">Manage Discount</h2>
        <form id="form-discount" method="post" action="/backend/discounts/save.php">
            <input type="hidden" name="id" id="discount-id" value="">
            <div>
                <label for="discount-code">Code</label>
                <input type="text" id="discount-code" name="code" required>
            </div>
            <div>
                <label for="discount-description">Description</label>
                <textarea id="discount-description" name="description"></textarea>
            </div>
            <div>
                <label for="discount-amount">Amount</label>
                <input type="number" id="discount-amount" name="amount" step="0.01" min="0" required>
            </div>
            <div>
                <label for="discount-active">Active</label>
                <input type="checkbox" id="discount-active" name="active">
            </div>
            <div class="modal-actions">
                <button type="submit">Save</button>
                <button type="button" onclick="closeModal('modal-discount')">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
</script>
