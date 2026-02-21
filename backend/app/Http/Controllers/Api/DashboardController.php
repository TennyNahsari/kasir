<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Product;
use App\Models\CashFlow;
use App\Models\PurchaseRequest;
use App\Models\PurchaseOrder;
use App\Models\GoodsReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $outletId = $request->outlet_id ?? auth()->user()->outlet_id;
        $locationId = $request->location_id ?? auth()->user()->location_id;
        $dateFrom = $request->date_from ?? now()->startOfDay();
        $dateTo = $request->date_to ?? now()->endOfDay();

        // Today's stats
        $todayStats = $this->getTodayStats($outletId);

        // Sales chart (last 7 days)
        $salesChart = $this->getSalesChart($outletId, 7);

        // Top products
        $topProducts = $this->getTopProducts($outletId, $dateFrom, $dateTo);

        // Payment method breakdown
        $paymentBreakdown = $this->getPaymentBreakdown($outletId, $dateFrom, $dateTo);

        // Low stock products (filtered by location type if FNB)
        $lowStockProducts = $this->getLowStockProducts($locationId);

        return response()->json([
            'today' => $todayStats,
            'sales_chart' => $salesChart,
            'top_products' => $topProducts,
            'payment_breakdown' => $paymentBreakdown,
            'low_stock_products' => $lowStockProducts,
        ]);
    }

    private function getTodayStats($outletId)
    {
        $today = now()->startOfDay();
        
        $query = Transaction::where('status', 'completed')
            ->whereDate('created_at', $today);
        
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        
        $transactions = $query->get();

        $totalRevenue = $transactions->sum('total');
        $totalTransactions = $transactions->count();
        $averageTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;

        // Cash in hand (today's cash only)
        $cashFlowQuery = CashFlow::whereDate('created_at', $today);
        
        if ($outletId) {
            $cashFlowQuery->where('outlet_id', $outletId);
        }
        
        $cashInHand = $cashFlowQuery->sum(DB::raw("CASE WHEN type = 'in' THEN amount ELSE -amount END"));

        return [
            'total_revenue' => $totalRevenue,
            'total_transactions' => $totalTransactions,
            'average_transaction' => $averageTransaction,
            'cash_in_hand' => $cashInHand,
        ];
    }

    private function getSalesChart($outletId, $days)
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            
            $query = Transaction::where('status', 'completed')
                ->whereDate('created_at', $date);
            
            if ($outletId) {
                $query->where('outlet_id', $outletId);
            }
            
            $total = $query->sum('total');

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'total' => $total,
            ];
        }

        return $data;
    }

    private function getTopProducts($outletId, $dateFrom, $dateTo, $limit = 10)
    {
        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo]);
        
        if ($outletId) {
            $query->where('transactions.outlet_id', $outletId);
        }
        
        return $query->select(
                'products.id',
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getPaymentBreakdown($outletId, $dateFrom, $dateTo)
    {
        $query = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);
        
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        
        return $query->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();
    }

    private function getLowStockProducts($locationId = null, $limit = 10)
    {
        $query = Product::with('category')
            ->where('track_stock', true)
            ->whereColumn('stock', '<=', 'min_stock');
        
        // Check if location is FNB type
        if ($locationId) {
            $location = \App\Models\Location::find($locationId);
            
            if ($location && strtoupper($location->type) === 'FNB') {
                // Filter only FNB categories
                $query->whereHas('category', function($q) {
                    $q->where(function($subQ) {
                        $subQ->where('name', 'like', '%FNB%')
                             ->orWhere('slug', 'like', '%fnb%')
                             ->orWhere('slug', 'like', '%FNB%');
                    });
                });
                
                \Log::info('Dashboard: Filtering low stock for FNB location', [
                    'location_id' => $locationId,
                    'location_type' => $location->type
                ]);
            }
        }
        
        return $query->orderBy('stock', 'asc')
            ->limit($limit)
            ->get();
    }

    public function salesReport(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => 'nullable|exists:outlets,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
            'group_by' => 'nullable|in:day,week,month',
        ]);

        $user = auth()->user();
        $outletId = $validated['outlet_id'] ?? $user->outlet_id;
        $groupBy = $validated['group_by'] ?? 'day';

        $query = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$validated['date_from'], $validated['date_to']]);
        
        // Only filter by outlet if user has specific outlet or outlet_id provided
        if ($outletId) {
            $query->where('outlet_id', $outletId);
        }

        $dateFormat = match($groupBy) {
            'day' => 'YYYY-MM-DD',
            'week' => 'YYYY-WW',
            'month' => 'YYYY-MM',
        };

        $report = $query->select(
            DB::raw("TO_CHAR(created_at, '{$dateFormat}') as period"),
            DB::raw('COUNT(*) as transaction_count'),
            DB::raw('SUM(total) as total_revenue'),
            DB::raw('SUM(discount) as total_discount'),
            DB::raw('AVG(total) as average_transaction')
        )
        ->groupBy('period')
        ->orderBy('period')
        ->get();

        return response()->json($report);
    }
    
    public function procurementStats(Request $request)
    {
        $user = auth()->user()->load('location');
        
        // Build queries with role-based filters
        $prQuery = PurchaseRequest::query();
        $poQuery = PurchaseOrder::query();
        $grnQuery = GoodsReceipt::query();
        
        // Apply filters based on user role and location
        if (in_array($user->role, ['owner', 'inventory'])) {
            // Owner and inventory can see all
        } elseif ($this->isProcurementUser($user)) {
            // Procurement users can see all
        } else {
            // Other users only see items from their department
            if ($user->location_id) {
                $prQuery->where('location_id', $user->location_id);
                $poQuery->where('location_id', $user->location_id);
                $grnQuery->where('location_id', $user->location_id);
            }
        }
        
        // Count pending PRs (PENDING_APPROVAL status, not SUBMITTED)
        $pendingPR = $prQuery->where('status', 'PENDING_APPROVAL')->count();
        
        // Count active POs (APPROVED and SENT status)
        $activePO = $poQuery->whereIn('status', ['APPROVED', 'SENT'])->count();
        
        // Count pending GRNs (DRAFT and QUALITY_CHECK status)
        $pendingGRN = $grnQuery->whereIn('status', ['DRAFT', 'QUALITY_CHECK'])->count();
        
        return response()->json([
            'pendingPR' => $pendingPR,
            'activePO' => $activePO,
            'pendingGRN' => $pendingGRN,
        ]);
    }
    
    private function isProcurementUser($user)
    {
        if (!$user->location) return false;
        
        return $user->location->type === 'DEPARTMENT' && 
               stripos($user->location->name, 'procurement') !== false;
    }
    
    public function expectedDeliveries(Request $request)
    {
        $user = auth()->user()->load('location');
        
        $query = PurchaseOrder::with(['vendor', 'location', 'items.product', 'goodsReceipts'])
            ->where('status', 'SENT')
            ->where('expected_delivery_date', '<=', now()->toDateString())
            ->whereDoesntHave('goodsReceipts', function($q) {
                $q->where('is_posted', true);
            });
        
        // Apply role-based filtering
        if (in_array($user->role, ['owner', 'inventory'])) {
            // Owner and inventory can see all
        } elseif ($this->isProcurementUser($user)) {
            // Procurement department users can see all
        } else {
            // Staff/Supervisor from other departments see only their department POs
            if ($user->location_id) {
                $query->where('location_id', $user->location_id);
            }
        }
        
        $deliveries = $query->orderBy('expected_delivery_date', 'asc')->get()->map(function($po) {
            $expectedDate = \Carbon\Carbon::parse($po->expected_delivery_date);
            $daysOverdue = now()->startOfDay()->diffInDays($expectedDate->startOfDay(), false);
            
            return [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'vendor_name' => $po->vendor?->name,
                'location_name' => $po->location?->name,
                'expected_delivery_date' => $po->expected_delivery_date,
                'days_overdue' => $daysOverdue,
                'priority' => $daysOverdue < 0 ? 'overdue' : 'today',
                'total_amount' => $po->total_amount,
                'items_count' => $po->items->count(),
                'grn_status' => $po->goodsReceipts->first()?->status,
                'has_grn' => $po->goodsReceipts->isNotEmpty(),
            ];
        });
        
        return response()->json($deliveries);
    }
    
    public function recentPurchaseRequests(Request $request)
    {
        $user = auth()->user()->load('location');
        $perPage = $request->input('per_page', 10);
        
        $query = PurchaseRequest::with([
            'location',
            'items.product',
            'requestedBy',
            'approvedBy',
        ])
        ->whereDate('request_date', '<=', now()->toDateString())
        ->whereNotIn('status', ['APPROVED', 'FULLY_ORDERED', 'CANCELLED']);
        
        // Apply role-based filtering
        if (in_array($user->role, ['owner', 'inventory'])) {
            // Owner and inventory can see all PRs
        } elseif ($this->isProcurementUser($user)) {
            // Procurement department users can see all PRs
        } else {
            // Staff/Supervisor from other departments see only their department PRs
            if ($user->location_id) {
                $query->where('location_id', $user->location_id);
            }
        }
        
        $prs = $query->orderBy('request_date', 'desc')
                     ->orderBy('created_at', 'desc')
                     ->paginate($perPage);
        
        return response()->json($prs);
    }
}
