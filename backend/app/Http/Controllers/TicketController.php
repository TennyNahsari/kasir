<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Asset;
use App\Models\TicketWorklog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Get all tickets with filters
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::with(['asset.product', 'reporter', 'assignedUser', 'location'])
            ->orderBy('created_at', 'desc');

        // Role-based access control
        if ($user->isOwner() || $user->isTechnician()) {
            // Owner and Technician can see all tickets
        } elseif ($user->isSupervisor() || $user->isStaff()) {
            // Supervisor and Staff can only see tickets from users in the same location/department
            if ($user->location_id) {
                $query->whereHas('reporter', function ($q) use ($user) {
                    $q->where('location_id', $user->location_id);
                });
            } else {
                // If no location assigned, only see their own tickets
                $query->where('reported_by', $user->id);
            }
        } elseif ($user->isKasir()) {
            // Kasir can only see tickets created by kasir users
            $query->whereHas('reporter', function ($q) {
                $q->where('role', 'kasir');
            });
        } else {
            // Other roles (kitchen, warehouse, etc) can only see their own tickets
            $query->where('reported_by', $user->id);
        }

        // Filter by type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            if (is_array($request->status)) {
                $query->whereIn('status', $request->status);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Filter by priority
        if ($request->has('priority') && $request->priority !== 'all') {
            $query->where('priority', $request->priority);
        }

        // Filter by assigned user
        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Filter by reporter (for my tickets)
        if ($request->has('my_tickets') && $request->my_tickets) {
            $query->where('reported_by', Auth::id());
        }

        // Filter by assigned to me
        if ($request->has('assigned_to_me') && $request->assigned_to_me) {
            $query->where('assigned_to', Auth::id());
        }

        // Filter overdue only
        if ($request->has('overdue_only') && $request->overdue_only) {
            $query->where('sla_due_date', '<', now())
                ->whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED']);
        }

        // Search by ticket number or title
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                  ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $tickets = $query->paginate($perPage);

        return response()->json($tickets);
    }

    /**
     * Get ticket detail
     */
    public function show($id)
    {
        $user = Auth::user();
        $ticket = Ticket::with([
            'asset.product',
            'asset.location',
            'reporter',
            'assignedUser',
            'location',
            'resolver',
            'closer',
            'worklogs.user',
            'attachments.uploader'
        ])->findOrFail($id);

        // Check access permission based on role
        $canAccess = false;

        if ($user->isOwner() || $user->isTechnician()) {
            // Owner and Technician can see all tickets
            $canAccess = true;
        } elseif ($user->isSupervisor() || $user->isStaff()) {
            // Supervisor and Staff can see tickets from same location
            if ($user->location_id && $ticket->reporter->location_id === $user->location_id) {
                $canAccess = true;
            } elseif ($ticket->reported_by === $user->id) {
                // Or their own tickets
                $canAccess = true;
            }
        } elseif ($user->isKasir()) {
            // Kasir can see tickets created by kasir users
            if ($ticket->reporter->role === 'kasir') {
                $canAccess = true;
            }
        } elseif ($ticket->reported_by === $user->id || $ticket->assigned_to === $user->id) {
            // Other users can see their own reported or assigned tickets
            $canAccess = true;
        }

        if (!$canAccess) {
            return response()->json([
                'message' => 'Unauthorized. You do not have permission to view this ticket.'
            ], 403);
        }

        return response()->json($ticket);
    }

    /**
     * Create new ticket
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'title' => 'required|string|max:200',
            'description' => 'required|string',
            'priority' => 'required|in:NORMAL,HIGH',
            'category' => 'nullable|in:HARDWARE,SOFTWARE,NETWORK,FACILITY,OTHER',
            'type' => 'required|in:INCIDENT,MAINTENANCE',
            'scheduled_date' => 'nullable|date',
            'maintenance_type' => 'nullable|in:PREVENTIVE,CORRECTIVE,PREDICTIVE',
            'assigned_to' => 'nullable|exists:users,id',
            'create_schedule' => 'nullable|boolean',
            'frequency' => 'nullable|in:MONTHLY,QUARTERLY,SEMI_ANNUAL,ANNUAL',
        ]);

        $asset = Asset::findOrFail($validated['asset_id']);

        DB::beginTransaction();
        try {
            $ticket = Ticket::create([
                'ticket_number' => Ticket::generateTicketNumber(),
                'type' => $validated['type'],
                'asset_id' => $validated['asset_id'],
                'reported_by' => Auth::id(),
                'assigned_to' => $validated['assigned_to'] ?? null,
                'location_id' => $asset->location_id,
                'title' => $validated['title'],
                'description' => $validated['description'],
                'priority' => $validated['priority'],
                'status' => isset($validated['assigned_to']) ? 'ASSIGNED' : 'OPEN',
                'category' => $validated['category'] ?? null,
                'scheduled_date' => $validated['scheduled_date'] ?? null,
                'maintenance_type' => $validated['maintenance_type'] ?? null,
                'sla_due_date' => Ticket::calculateSLADueDate($validated['priority']),
            ]);

            // Create recurring maintenance schedule if requested
            if ($validated['type'] === 'MAINTENANCE' && 
                isset($validated['create_schedule']) && 
                $validated['create_schedule'] === true &&
                isset($validated['frequency'])) {
                
                $nextDate = $this->calculateNextMaintenanceDate($validated['frequency']);
                
                \App\Models\MaintenanceSchedule::create([
                    'asset_id' => $validated['asset_id'],
                    'maintenance_type' => $validated['maintenance_type'] ?? 'PREVENTIVE',
                    'frequency' => $validated['frequency'],
                    'last_maintenance_date' => now()->toDateString(),
                    'next_maintenance_date' => $nextDate,
                    'auto_create_ticket' => true,
                    'is_active' => true,
                    'notes' => "Auto-created from ticket: {$ticket->ticket_number}"
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Ticket created successfully' . (isset($validated['create_schedule']) && $validated['create_schedule'] ? ' with recurring schedule' : ''),
                'ticket' => $ticket->load(['asset.product', 'reporter', 'location', 'assignedUser'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate next maintenance date based on frequency
     */
    private function calculateNextMaintenanceDate($frequency)
    {
        switch ($frequency) {
            case 'MONTHLY':
                return now()->addMonth()->toDateString();
            case 'QUARTERLY':
                return now()->addMonths(3)->toDateString();
            case 'SEMI_ANNUAL':
                return now()->addMonths(6)->toDateString();
            case 'ANNUAL':
                return now()->addYear()->toDateString();
            default:
                return now()->addMonths(3)->toDateString();
        }
    }

    /**
     * Update ticket
     */
    public function update(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        // Check permissions - Owner and Technician have full access
        if (!$user->isOwner() && !$user->isTechnician()) {
            if (!$user->isSupervisor()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:200',
            'description' => 'sometimes|string',
            'priority' => 'sometimes|in:NORMAL,HIGH',
            'category' => 'nullable|in:HARDWARE,SOFTWARE,NETWORK,FACILITY,OTHER',
            'scheduled_date' => 'nullable|date',
            'status' => 'sometimes|in:OPEN,ASSIGNED,IN_PROGRESS,ON_HOLD,RESOLVED,CLOSED,CANCELLED',
            'assigned_to' => 'nullable|exists:users,id',
            'resolution_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $updateData = collect($validated)->except(['resolution_notes'])->toArray();

            // Handle status changes
            if (isset($validated['status']) && $validated['status'] !== $ticket->status) {
                if ($validated['status'] === 'RESOLVED') {
                    $updateData['resolved_at'] = now();
                    $updateData['resolved_by'] = $user->id;
                    if (isset($validated['resolution_notes'])) {
                        $updateData['resolution_notes'] = $validated['resolution_notes'];
                    }
                } elseif ($validated['status'] === 'CLOSED') {
                    $updateData['closed_at'] = now();
                    $updateData['closed_by'] = $user->id;
                } elseif ($validated['status'] === 'ASSIGNED' && !$ticket->assigned_to && !isset($validated['assigned_to'])) {
                    // Auto-assign to current user if assigning and no technician specified
                    $updateData['assigned_to'] = $user->id;
                }
            }

            // Update SLA if priority changed
            if (isset($validated['priority']) && $validated['priority'] !== $ticket->priority) {
                $updateData['sla_due_date'] = Ticket::calculateSLADueDate($validated['priority']);
            }

            $ticket->update($updateData);

            DB::commit();

            return response()->json([
                'message' => 'Ticket updated successfully',
                'ticket' => $ticket->fresh()->load(['asset.product', 'reporter', 'assignedUser', 'location'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update ticket',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get my assets (for ticket creation)
     */
    public function myAssets()
    {
        $user = Auth::user();
        
        // Owner and Technician see all assets, others see only assets in their location
        $query = Asset::with(['product', 'location']);
        
        if (!$user->isOwner() && !$user->isTechnician()) {
            if ($user->location_id) {
                $query->where('location_id', $user->location_id);
            } else {
                // Users without location_id see no assets
                $query->whereRaw('1 = 0');
            }
        }
        
        // Only show assets that are not retired
        $query->whereNotIn('status', ['RETIRED'])
            ->orderBy('asset_tag');
        
        $assets = $query->get();

        return response()->json($assets);
    }

    /**
     * Get ticket statistics
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();
        
        $query = Ticket::query();
        
        // Filter based on role
        if (!$user->isOwner() && !$user->isSupervisor()) {
            if ($user->isTechnician()) {
                $query->where('assigned_to', $user->id);
            } else {
                $query->where('reported_by', $user->id);
            }
        }

        $stats = [
            'total' => (clone $query)->count(),
            'open' => (clone $query)->where('status', 'OPEN')->count(),
            'assigned' => (clone $query)->where('status', 'ASSIGNED')->count(),
            'in_progress' => (clone $query)->where('status', 'IN_PROGRESS')->count(),
            'on_hold' => (clone $query)->where('status', 'ON_HOLD')->count(),
            'resolved' => (clone $query)->where('status', 'RESOLVED')->count(),
            'closed' => (clone $query)->where('status', 'CLOSED')->count(),
            'high_priority' => (clone $query)->where('priority', 'HIGH')->whereNotIn('status', ['CLOSED', 'CANCELLED'])->count(),
            'overdue' => (clone $query)->where('sla_due_date', '<', now())->whereNotIn('status', ['RESOLVED', 'CLOSED', 'CANCELLED'])->count(),
        ];

        // Include technicians for supervisors/owners
        if ($user->isOwner() || $user->isSupervisor()) {
            $stats['technicians'] = User::where('is_technician', true)
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();
        }

        return response()->json($stats);
    }

    /**
     * Add worklog to ticket
     */
    public function addWorklog(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        // Check permissions - Owner and Technician have full access, others can add worklog to tickets they can access
        if (!$user->isOwner() && !$user->isTechnician() && !$user->isSupervisor()) {
            // Staff can add worklog to tickets in their department
            if ($user->isStaff() && $user->location_id && $ticket->reporter->location_id === $user->location_id) {
                // Allow
            } 
            // Kasir can add worklog to kasir tickets
            elseif ($user->isKasir() && $ticket->reporter->role === 'kasir') {
                // Allow
            }
            // Others can only add worklog to their own tickets
            elseif ($ticket->reported_by !== $user->id && $ticket->assigned_to !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $validated = $request->validate([
            'notes' => 'required|string',
            'time_spent' => 'required|integer|min:1',
            'new_status' => 'nullable|in:OPEN,ASSIGNED,IN_PROGRESS,ON_HOLD,RESOLVED,CLOSED,CANCELLED',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $ticket->status;
            $newStatus = $validated['new_status'] ?? $oldStatus;
            $isStatusChange = isset($validated['new_status']) && $validated['new_status'] !== $oldStatus;

            // Prepare worklog description
            $description = $validated['notes'];
            if ($isStatusChange) {
                $description = "Status changed from {$oldStatus} to {$newStatus}\n\n" . $description;
            }

            // Create worklog
            $worklog = TicketWorklog::create([
                'ticket_id' => $ticket->id,
                'user_id' => $user->id,
                'worklog_type' => $isStatusChange ? 'STATUS_CHANGE' : 'WORK_DONE',
                'description' => $description,
                'time_spent_minutes' => $validated['time_spent'],
                'is_internal' => false,
            ]);

            // Update ticket status if provided
            if (isset($validated['new_status']) && $validated['new_status'] !== $oldStatus) {
                $updateData = ['status' => $validated['new_status']];

                // Handle status-specific updates
                if ($validated['new_status'] === 'RESOLVED') {
                    $updateData['resolved_at'] = now();
                    $updateData['resolved_by'] = $user->id;
                } elseif ($validated['new_status'] === 'CLOSED') {
                    $updateData['closed_at'] = now();
                    $updateData['closed_by'] = $user->id;
                } elseif ($validated['new_status'] === 'ASSIGNED' && $ticket->status === 'OPEN') {
                    // Auto-assign to current user if not assigned
                    if (!$ticket->assigned_to) {
                        $updateData['assigned_to'] = $user->id;
                    }
                }

                $ticket->update($updateData);
            }

            DB::commit();

            return response()->json([
                'message' => 'Worklog added successfully',
                'worklog' => $worklog->load('user'),
                'ticket' => $ticket->fresh()->load(['asset.product', 'reporter', 'assignedUser', 'location', 'worklogs.user'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to add worklog: ' . $e->getMessage(), [
                'ticket_id' => $id,
                'user_id' => $user->id,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Failed to add worklog',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete ticket (Owner & Supervisor only)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        // Only owner and supervisor can delete tickets
        if (!$user->isOwner() && !$user->isSupervisor()) {
            return response()->json([
                'message' => 'Unauthorized. Only owner and supervisor can delete tickets.'
            ], 403);
        }

        $ticket = Ticket::findOrFail($id);

        // Soft delete the ticket
        $ticket->delete();

        return response()->json([
            'message' => 'Ticket deleted successfully'
        ], 200);
    }

    /**
     * Upload attachment to ticket
     */
    public function uploadAttachment(Request $request, $id)
    {
        $ticket = Ticket::findOrFail($id);
        $user = Auth::user();

        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
            'worklog_id' => 'nullable|exists:ticket_worklogs,id'
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->store('ticket-attachments', 'public');

            $attachment = \App\Models\TicketAttachment::create([
                'ticket_id' => $ticket->id,
                'worklog_id' => $request->worklog_id,
                'uploaded_by' => $user->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize()
            ]);

            return response()->json([
                'message' => 'Attachment uploaded successfully',
                'attachment' => $attachment->load('uploader')
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Failed to upload attachment: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to upload attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete attachment
     */
    public function deleteAttachment($ticketId, $attachmentId)
    {
        $user = Auth::user();
        $attachment = \App\Models\TicketAttachment::where('ticket_id', $ticketId)
            ->where('id', $attachmentId)
            ->firstOrFail();

        // Only uploader, owner, technician, or supervisor can delete
        if ($attachment->uploaded_by !== $user->id && !$user->isOwner() && !$user->isTechnician() && !$user->isSupervisor()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        try {
            // Delete file from storage
            \Storage::disk('public')->delete($attachment->file_path);
            
            // Delete record
            $attachment->delete();

            return response()->json(['message' => 'Attachment deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete attachment',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
