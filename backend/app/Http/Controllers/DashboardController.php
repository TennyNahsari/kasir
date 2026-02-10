<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard data based on user role
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isOwner() || $user->isSupervisor()) {
            return $this->supervisorDashboard();
        } elseif ($user->isTechnician()) {
            return $this->technicianDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    /**
     * Supervisor/Owner dashboard
     */
    private function supervisorDashboard()
    {
        // Ticket statistics
        $ticketStats = [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'OPEN')->count(),
            'in_progress' => Ticket::where('status', 'IN_PROGRESS')->count(),
            'resolved_today' => Ticket::where('status', 'RESOLVED')->whereDate('resolved_at', today())->count(),
            'high_priority' => Ticket::where('priority', 'HIGH')->whereNotIn('status', ['CLOSED', 'CANCELLED'])->count(),
            'overdue' => Ticket::where('sla_due_date', '<', now())->whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED'])->count(),
        ];

        // Tickets by status
        $ticketsByStatus = Ticket::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // Tickets by priority
        $ticketsByPriority = Ticket::select('priority', DB::raw('count(*) as count'))
            ->whereNotIn('status', ['CLOSED', 'CANCELLED'])
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority');

        // Tickets by type
        $ticketsByType = Ticket::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        // Recent tickets
        $recentTickets = Ticket::with(['asset.product', 'reporter', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // Asset statistics
        $assetStats = [
            'total' => Asset::count(),
            'available' => Asset::where('status', 'AVAILABLE')->count(),
            'assigned' => Asset::where('status', 'ASSIGNED')->count(),
            'in_use' => Asset::where('status', 'IN_USE')->count(),
            'maintenance' => Asset::where('status', 'MAINTENANCE')->count(),
            'damaged' => Asset::where('status', 'DAMAGED')->count(),
        ];

        // Top problematic assets
        $problematicAssets = Asset::whereHas('tickets', function ($query) {
            $query->where('type', 'INCIDENT');
        })
            ->withCount(['tickets' => function ($query) {
                $query->where('type', 'INCIDENT');
            }])
            ->with('product')
            ->orderBy('tickets_count', 'desc')
            ->take(5)
            ->get();

        // Technician performance
        $technicianPerformance = User::where('is_technician', true)
            ->withCount([
                'assignedTickets as total_tickets',
                'assignedTickets as open_tickets' => function ($query) {
                    $query->whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED']);
                },
                'assignedTickets as resolved_tickets' => function ($query) {
                    $query->where('status', 'RESOLVED');
                }
            ])
            ->get();

        return response()->json([
            'role' => 'supervisor',
            'ticket_stats' => $ticketStats,
            'tickets_by_status' => $ticketsByStatus,
            'tickets_by_priority' => $ticketsByPriority,
            'tickets_by_type' => $ticketsByType,
            'recent_tickets' => $recentTickets,
            'asset_stats' => $assetStats,
            'problematic_assets' => $problematicAssets,
            'technician_performance' => $technicianPerformance,
        ]);
    }

    /**
     * Technician dashboard
     */
    private function technicianDashboard()
    {
        $user = Auth::user();

        // My tickets statistics
        $myTickets = [
            'assigned' => Ticket::where('assigned_to', $user->id)->where('status', 'ASSIGNED')->count(),
            'in_progress' => Ticket::where('assigned_to', $user->id)->where('status', 'IN_PROGRESS')->count(),
            'on_hold' => Ticket::where('assigned_to', $user->id)->where('status', 'ON_HOLD')->count(),
            'resolved_this_week' => Ticket::where('assigned_to', $user->id)
                ->where('status', 'RESOLVED')
                ->whereBetween('resolved_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        // Tickets assigned to me
        $assignedTickets = Ticket::with(['asset.product', 'reporter', 'location'])
            ->where('assigned_to', $user->id)
            ->whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED'])
            ->orderByRaw("CASE WHEN priority = 'HIGH' THEN 1 WHEN priority = 'NORMAL' THEN 2 ELSE 3 END")
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        // Today's scheduled maintenance
        $todayMaintenance = Ticket::with(['asset.product', 'location'])
            ->where('type', 'MAINTENANCE')
            ->where('assigned_to', $user->id)
            ->whereDate('scheduled_date', today())
            ->get();

        // Overdue tickets
        $overdueTickets = Ticket::with(['asset.product', 'reporter'])
            ->where('assigned_to', $user->id)
            ->where('sla_due_date', '<', now())
            ->whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED'])
            ->orderBy('sla_due_date', 'asc')
            ->get();

        return response()->json([
            'role' => 'technician',
            'my_tickets' => $myTickets,
            'assigned_tickets' => $assignedTickets,
            'today_maintenance' => $todayMaintenance,
            'overdue_tickets' => $overdueTickets,
        ]);
    }

    /**
     * End user dashboard
     */
    private function userDashboard()
    {
        $user = Auth::user();

        // My tickets
        $myTickets = Ticket::with(['asset.product', 'assignedUser'])
            ->where('reported_by', $user->id)
            ->whereNotIn('status', ['CLOSED', 'CANCELLED'])
            ->orderBy('created_at', 'desc')
            ->get();

        // My assets (based on PIC field matching user email or name)
        $myAssets = Asset::with(['product', 'location'])
            ->where(function($query) use ($user) {
                $query->where('pic', 'like', '%' . $user->email . '%')
                      ->orWhere('pic', 'like', '%' . $user->name . '%');
            })
            ->get();

        // Ticket statistics
        $ticketStats = [
            'open' => Ticket::where('reported_by', $user->id)->where('status', 'OPEN')->count(),
            'in_progress' => Ticket::where('reported_by', $user->id)->whereIn('status', ['ASSIGNED', 'IN_PROGRESS'])->count(),
            'resolved' => Ticket::where('reported_by', $user->id)->where('status', 'RESOLVED')->count(),
            'closed' => Ticket::where('reported_by', $user->id)->where('status', 'CLOSED')->count(),
        ];

        return response()->json([
            'role' => 'user',
            'my_tickets' => $myTickets,
            'my_assets' => $myAssets,
            'ticket_stats' => $ticketStats,
        ]);
    }
}
