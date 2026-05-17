<?php
/**
 * Admin FinanceController - RZDK Store
 * 
 * Financial reporting: daily/monthly revenue, transaction log, CSV export.
 */

class FinanceController extends Controller
{
    /**
     * Finance dashboard with stats and transaction log
     */
    public function index(): void
    {
        $db = Model::getConnection();

        $month = $this->input('month', date('Y-m'));
        $year = substr($month, 0, 4);
        $monthNum = substr($month, 5, 2);
        $monthStart = "{$month}-01";
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        // Monthly revenue
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'income' AND recorded_at BETWEEN :start AND :end");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $monthRevenue = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Monthly refunds
        $stmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE type = 'refund' AND recorded_at BETWEEN :start AND :end");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $monthRefunds = (float) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Monthly order count (completed)
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM orders WHERE status = 'completed' AND completed_at BETWEEN :start AND :end");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $monthOrderCount = (int) $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Average order value
        $avgOrderValue = $monthOrderCount > 0 ? $monthRevenue / $monthOrderCount : 0;

        // Daily revenue chart data (for current month)
        $stmt = $db->prepare("
            SELECT DATE(recorded_at) as date, SUM(amount) as daily_revenue
            FROM transactions
            WHERE type = 'income' AND recorded_at BETWEEN :start AND :end
            GROUP BY DATE(recorded_at)
            ORDER BY date ASC
        ");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $dailyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Revenue by category
        $stmt = $db->prepare("
            SELECT c.name as category_name, SUM(t.amount) as total_revenue, COUNT(t.id) as order_count
            FROM transactions t
            JOIN orders o ON o.id = t.order_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            JOIN categories c ON c.id = p.category_id
            WHERE t.type = 'income' AND t.recorded_at BETWEEN :start AND :end
            GROUP BY c.id
            ORDER BY total_revenue DESC
        ");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $revenueByCategory = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top products this month
        $stmt = $db->prepare("
            SELECT p.name as product_name, pv.name as variant_name, 
                   SUM(t.amount) as total_revenue, COUNT(t.id) as order_count
            FROM transactions t
            JOIN orders o ON o.id = t.order_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE t.type = 'income' AND t.recorded_at BETWEEN :start AND :end
            GROUP BY pv.id
            ORDER BY total_revenue DESC
            LIMIT 10
        ");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Recent transactions
        $stmt = $db->prepare("
            SELECT t.*, o.order_number, u.username, p.name as product_name
            FROM transactions t
            JOIN orders o ON o.id = t.order_id
            JOIN users u ON u.id = o.user_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE t.recorded_at BETWEEN :start AND :end
            ORDER BY t.recorded_at DESC
            LIMIT 50
        ");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->view('admin/finance', [
            'pageTitle' => 'Keuangan',
            'month' => $month,
            'monthRevenue' => $monthRevenue,
            'monthRefunds' => $monthRefunds,
            'monthOrderCount' => $monthOrderCount,
            'avgOrderValue' => $avgOrderValue,
            'dailyData' => $dailyData,
            'revenueByCategory' => $revenueByCategory,
            'topProducts' => $topProducts,
            'transactions' => $transactions,
        ], 'admin');
    }

    /**
     * Export transactions as CSV
     */
    public function export(): void
    {
        $db = Model::getConnection();
        $month = $this->input('month', date('Y-m'));
        $monthStart = "{$month}-01";
        $monthEnd = date('Y-m-t', strtotime($monthStart));

        $stmt = $db->prepare("
            SELECT t.recorded_at, t.type, t.amount, t.description, 
                   o.order_number, u.username, p.name as product_name, pv.name as variant_name
            FROM transactions t
            JOIN orders o ON o.id = t.order_id
            JOIN users u ON u.id = o.user_id
            JOIN product_variants pv ON pv.id = o.variant_id
            JOIN products p ON p.id = pv.product_id
            WHERE t.recorded_at BETWEEN :start AND :end
            ORDER BY t.recorded_at DESC
        ");
        $stmt->execute([':start' => "{$monthStart} 00:00:00", ':end' => "{$monthEnd} 23:59:59"]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Output CSV
        $filename = "rzdk-finance-{$month}.csv";
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");

        $output = fopen('php://output', 'w');

        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Header row
        fputcsv($output, ['Tanggal', 'Tipe', 'Jumlah', 'No. Order', 'Customer', 'Produk', 'Varian', 'Keterangan']);

        // Data rows
        foreach ($transactions as $t) {
            fputcsv($output, [
                $t['recorded_at'],
                $t['type'] === 'income' ? 'Pemasukan' : 'Refund',
                $t['amount'],
                $t['order_number'],
                $t['username'],
                $t['product_name'],
                $t['variant_name'],
                $t['description'],
            ]);
        }

        // Summary row
        fputcsv($output, []);
        $totalIncome = array_sum(array_map(fn($t) => $t['type'] === 'income' ? $t['amount'] : 0, $transactions));
        $totalRefund = array_sum(array_map(fn($t) => $t['type'] === 'refund' ? $t['amount'] : 0, $transactions));
        fputcsv($output, ['TOTAL PEMASUKAN', '', $totalIncome]);
        fputcsv($output, ['TOTAL REFUND', '', $totalRefund]);
        fputcsv($output, ['NET REVENUE', '', $totalIncome - $totalRefund]);

        fclose($output);
        exit;
    }
}
