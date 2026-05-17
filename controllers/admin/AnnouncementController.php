<?php
/**
 * Admin AnnouncementController - RZDK Store
 * 
 * CRUD for product-targeted announcements.
 * Only customers who have purchased the targeted product will see the announcement.
 */

class AnnouncementController extends Controller
{
    /**
     * List all announcements
     */
    public function index(): void
    {
        $db = Model::getConnection();

        $announcements = $db->query("
            SELECT a.*, p.name as product_name,
                   (SELECT COUNT(*) FROM announcement_reads ar WHERE ar.announcement_id = a.id) as read_count
            FROM announcements a
            JOIN products p ON p.id = a.product_id
            ORDER BY a.created_at DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        // Get products for dropdown
        $products = $db->query("SELECT id, name FROM products WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/announcements', [
            'pageTitle' => 'Pengumuman',
            'announcements' => $announcements,
            'products' => $products,
        ], 'admin');
    }

    /**
     * Store new announcement
     */
    public function store(): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'product_id' => 'required|exists:products,id',
            'title' => 'required|min:5|max:200',
            'content' => 'required|min:10|max:5000',
            'priority' => 'required|in:info,warning,urgent',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/announcements');
            return;
        }

        $db = Model::getConnection();
        $expiresAt = $this->input('expires_at') ? trim($this->input('expires_at')) : null;

        $stmt = $db->prepare("
            INSERT INTO announcements (product_id, title, content, priority, published_at, expires_at, is_active, created_at, updated_at)
            VALUES (:product_id, :title, :content, :priority, NOW(), :expires_at, 1, NOW(), NOW())
        ");
        $stmt->execute([
            ':product_id' => (int) $this->input('product_id'),
            ':title' => trim($this->input('title')),
            ':content' => trim($this->input('content')),
            ':priority' => $this->input('priority'),
            ':expires_at' => $expiresAt,
        ]);

        Helper::logActivity('announcement_created', "Announcement created: " . trim($this->input('title')));
        Session::flash('success', 'Pengumuman berhasil dipublikasikan.');
        $this->redirect('/admin/announcements');
    }

    /**
     * Update announcement
     */
    public function update(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $validator = new Validator($_POST);
        $validator->rules([
            'title' => 'required|min:5|max:200',
            'content' => 'required|min:10|max:5000',
            'priority' => 'required|in:info,warning,urgent',
        ]);

        if (!$validator->validate()) {
            Session::flash('error', $validator->firstError());
            $this->redirect('/admin/announcements');
            return;
        }

        $db = Model::getConnection();
        $expiresAt = $this->input('expires_at') ? trim($this->input('expires_at')) : null;
        $isActive = $this->input('is_active') ? 1 : 0;

        $stmt = $db->prepare("
            UPDATE announcements SET title = :title, content = :content, priority = :priority, 
            expires_at = :expires_at, is_active = :is_active, updated_at = NOW() WHERE id = :id
        ");
        $stmt->execute([
            ':title' => trim($this->input('title')),
            ':content' => trim($this->input('content')),
            ':priority' => $this->input('priority'),
            ':expires_at' => $expiresAt,
            ':is_active' => $isActive,
            ':id' => $id,
        ]);

        Helper::logActivity('announcement_updated', "Announcement #{$id} updated");
        Session::flash('success', 'Pengumuman berhasil diupdate.');
        $this->redirect('/admin/announcements');
    }

    /**
     * Delete announcement
     */
    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) return;

        $db = Model::getConnection();
        $stmt = $db->prepare("DELETE FROM announcements WHERE id = :id");
        $stmt->execute([':id' => $id]);

        Helper::logActivity('announcement_deleted', "Announcement #{$id} deleted");
        Session::flash('success', 'Pengumuman berhasil dihapus.');
        $this->redirect('/admin/announcements');
    }
}
