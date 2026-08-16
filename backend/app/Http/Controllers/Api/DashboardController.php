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
        $user = auth()->user();
        $isOwner = $user && ($user->role === 'owner' || ($user->role === 'inventory' && !$user->outlet_id));

        if (!$isOwner && $user?->location_id) {
            $locationId = $user->location_id;
            $outletId = $user->outlet_id;
        } else {
            $outletId = $request->outlet_id ?? $user?->outlet_id;
            $locationId = $request->location_id ?? $user?->location_id;
        }
        
        $dateFrom = $request->date_from ? \Carbon\Carbon::parse($request->date_from)->startOfDay() : null;
        $dateTo = $request->date_to ? \Carbon\Carbon::parse($request->date_to)->endOfDay() : now()->endOfDay();

        // Today's stats
        $todayStats = $this->getTodayStats($outletId, $locationId);

        // Sales chart (last 7 days)
        $salesChart = $this->getSalesChart($outletId, $locationId, 7);

        // Top products (default to last 30 days / fallback to all-time if no sales in 30 days)
        $topProducts = $this->getTopProducts($outletId, $locationId, $dateFrom, $dateTo);

        // Payment method breakdown
        $paymentBreakdown = $this->getPaymentBreakdown($outletId, $locationId, $dateFrom, $dateTo);

        // Low stock products (filtered by outlet or location)
        $lowStockProducts = $this->getLowStockProducts($outletId, $locationId);

        return response()->json([
            'today' => $todayStats,
            'sales_chart' => $salesChart,
            'top_products' => $topProducts,
            'payment_breakdown' => $paymentBreakdown,
            'low_stock_products' => $lowStockProducts,
        ]);
    }

    private function getTodayStats($outletId = null, $locationId = null)
    {
        $today = now()->startOfDay();
        
        $query = Transaction::where('status', 'completed')
            ->whereDate('created_at', $today);
        
        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif ($outletId) {
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
            'total_revenue' => (float) $totalRevenue,
            'total_transactions' => (int) $totalTransactions,
            'average_transaction' => (float) $averageTransaction,
            'cash_in_hand' => (float) $cashInHand,
        ];
    }

    private function getSalesChart($outletId = null, $locationId = null, $days = 7)
    {
        $data = [];
        
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            
            $query = Transaction::where('status', 'completed')
                ->whereDate('created_at', $date);
            
            if ($locationId) {
                $query->where('location_id', $locationId);
            } elseif ($outletId) {
                $query->where('outlet_id', $outletId);
            }
            
            $total = $query->sum('total');

            $data[] = [
                'date' => $date->format('Y-m-d'),
                'total' => (float) $total,
            ];
        }

        return $data;
    }

    private function getTopProducts($outletId = null, $locationId = null, $dateFrom = null, $dateTo = null, $limit = 10)
    {
        $from = $dateFrom ?? now()->subDays(30)->startOfDay();
        $to = $dateTo ?? now()->endOfDay();

        $query = DB::table('transaction_items')
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->join('products', 'transaction_items.product_id', '=', 'products.id')
            ->where('transactions.status', 'completed')
            ->whereBetween('transactions.created_at', [$from, $to]);
        
        if ($locationId) {
            $query->where('transactions.location_id', $locationId);
        } elseif ($outletId) {
            $query->where('transactions.outlet_id', $outletId);
        }
        
        $results = $query->select(
                'products.id',
                'products.name',
                DB::raw('SUM(transaction_items.quantity) as total_quantity'),
                DB::raw('SUM(transaction_items.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_quantity', 'desc')
            ->limit($limit)
            ->get();

        // Fallback to all-time if no transactions in last 30 days and dateFrom was not explicitly requested
        if ($results->isEmpty() && !$dateFrom) {
            $fallbackQuery = DB::table('transaction_items')
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->join('products', 'transaction_items.product_id', '=', 'products.id')
                ->where('transactions.status', 'completed');
            
            if ($locationId) {
                $fallbackQuery->where('transactions.location_id', $locationId);
            } elseif ($outletId) {
                $fallbackQuery->where('transactions.outlet_id', $outletId);
            }

            $results = $fallbackQuery->select(
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

        $mapped = $results->map(function ($item) {
            $qty = (float) $item->total_quantity;
            return [
                'id' => $item->id,
                'name' => $item->name,
                'total_quantity' => $qty == (int) $qty ? (int) $qty : $qty,
                'total_revenue' => (float) $item->total_revenue,
            ];
        });

        return array_values($mapped->all());
    }

    private function getPaymentBreakdown($outletId = null, $locationId = null, $dateFrom = null, $dateTo = null)
    {
        $from = $dateFrom ?? now()->subDays(30)->startOfDay();
        $to = $dateTo ?? now()->endOfDay();

        $query = Transaction::where('status', 'completed')
            ->whereBetween('created_at', [$from, $to]);
        
        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif ($outletId) {
            $query->where('outlet_id', $outletId);
        }
        
        return $query->select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(total) as total'))
            ->groupBy('payment_method')
            ->get();
    }

    private function getLowStockProducts($outletId = null, $locationId = null, $limit = 10)
    {
        $query = \App\Models\InventoryStock::whereHas('product')
            ->with(['product.category', 'location'])
            ->whereRaw('quantity <= reorder_level')
            ->where('reorder_level', '>', 0); // Only check products with reorder level set
        
        // Priority: If specific location is specified, use it. Otherwise use outlet filter
        if ($locationId) {
            $query->where('location_id', $locationId);
        } elseif ($outletId) {
            // Filter by outlet (all locations in the outlet)
            $query->whereHas('location', function($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            });
        }
        
        $results = $query->orderBy('quantity', 'asc')
            ->limit($limit)
            ->get();
        
        $mapped = [];
        foreach ($results as $stock) {
            if (!$stock->product) continue;

            $mapped[] = [
                'id' => $stock->product->id,
                'name' => $stock->product->name,
                'sku' => $stock->product->sku ?? '',
                'stock' => (float) $stock->quantity == (int) $stock->quantity ? (int) $stock->quantity : (float) $stock->quantity,
                'min_stock' => (float) $stock->reorder_level == (int) $stock->reorder_level ? (int) $stock->reorder_level : (float) $stock->reorder_level,
                'category' => $stock->product->category,
            ];
        }

        return $mapped;
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
