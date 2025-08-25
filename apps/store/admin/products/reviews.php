<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /backend/auth/login.php');
    exit;
}

// Handle review approval/rejection/deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $reviewId = intval($_POST['review_id'] ?? 0);

    if ($reviewId > 0) {
        if ($action === 'approve') {
            db_query('UPDATE product_reviews SET status = "approved" WHERE id = ?', [$reviewId]);
        } elseif ($action === 'reject') {
            db_query('UPDATE product_reviews SET status = "rejected" WHERE id = ?', [$reviewId]);
        } elseif ($action === 'delete') {
            db_query('DELETE FROM product_reviews WHERE id = ?', [$reviewId]);
        }
    }
}

// Fetch reviews with product info
$reviews = db_query('
    SELECT pr.id, pr.product_id, pr.reviewer_name, pr.rating, pr.comment, pr.status, pr.created_at, p.name AS product_name
    FROM product_reviews pr
    JOIN products p ON pr.product_id = p.id
    ORDER BY pr.created_at DESC
')->fetchAll(PDO::FETCH_ASSOC);

require __DIR__ . '/../components/header.php';
?>

<h1>Product Reviews</h1>

<table>
    <thead>
        <tr>
            <th>Product</th>
            <th>Reviewer</th>
            <th>Rating</th>
            <th>Comment</th>
            <th>Status</th>
            <th>Submitted</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($reviews as $review): ?>
        <tr>
            <td><?= htmlspecialchars($review['product_name']) ?></td>
            <td><?= htmlspecialchars($review['reviewer_name']) ?></td>
            <td><?= (int)$review['rating'] ?>/5</td>
            <td><?= nl2br(htmlspecialchars($review['comment'])) ?></td>
            <td><?= htmlspecialchars(ucfirst($review['status'])) ?></td>
            <td><?= htmlspecialchars($review['created_at']) ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
                    <button type="submit" name="action" value="approve">Approve</button>
                    <button type="submit" name="action" value="reject">Reject</button>
                    <button type="submit" name="action" value="delete" onclick="return confirm('Are you sure?')">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php
require __DIR__ . '/../components/footer.php';
