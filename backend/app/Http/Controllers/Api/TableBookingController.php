<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TableBooking;
use App\Models\Location;
use Illuminate\Http\Request;

class TableBookingController extends Controller
{
    /**
     * Store public booking from Homepage
     */
    public function storePublic(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required',
            'customer_name' => 'required|string|max:255',
            'whatsapp_number' => 'required|string|max:50',
            'reservation_date' => 'required|date|after_or_equal:today',
            'reservation_time' => 'required|string|max:20',
            'guest_count' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $locationId = $validated['location_id'];
        $location = Location::find($locationId);

        // Fallback if demo outlet ID was sent from homepage
        if (!$location) {
            $location = Location::whereIn('type', ['FNB', 'OUTLET'])->first() ?? Location::first();
            $locationId = $location?->id;
        }

        if (!$locationId) {
            return response()->json([
                'message' => 'Tidak ada outlet F&B yang valid di sistem'
            ], 422);
        }

        $booking = TableBooking::create([
            'booking_code' => TableBooking::generateBookingCode(),
            'location_id' => $locationId,
            'outlet_id' => $location?->outlet_id,
            'customer_name' => $validated['customer_name'],
            'whatsapp_number' => $validated['whatsapp_number'],
            'reservation_date' => $validated['reservation_date'],
            'reservation_time' => $validated['reservation_time'],
            'guest_count' => $validated['guest_count'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Reservasi meja berhasil dikirim',
            'data' => $booking->load(['location', 'outlet'])
        ], 201);
    }

    /**
     * Index bookings for staff/owner with location scoping
     */
    public function index(Request $request)
    {
        $query = TableBooking::with(['location', 'outlet', 'confirmedBy']);

        $user = auth()->user();
        $isOwner = $user && ($user->role === 'owner' || ($user->role === 'inventory' && !$user->outlet_id));

        if (!$isOwner && $user?->location_id) {
            $query->where(function($q) use ($user) {
                $q->where('location_id', $user->location_id);
                if ($user->outlet_id) {
                    $q->orWhere('outlet_id', $user->outlet_id);
                }
            });
        } elseif (!$isOwner && $user?->outlet_id) {
            $query->where('outlet_id', $user->outlet_id);
        } else {
            // Owner or unassigned staff
            if ($request->filled('location_id')) {
                $locId = $request->location_id;
                $location = Location::find($locId);
                $query->where(function($q) use ($locId, $location) {
                    $q->where('location_id', $locId);
                    if ($location && $location->outlet_id) {
                        $q->orWhere('outlet_id', $location->outlet_id);
                    }
                });
            } elseif ($request->filled('outlet_id')) {
                $query->where('outlet_id', $request->outlet_id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('reservation_date', $request->date);
        }

        $bookings = $query->orderBy('reservation_date', 'asc')
            ->orderBy('reservation_time', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $bookings
        ]);
    }

    /**
     * Update booking status (confirm / cancel)
     */
    public function updateStatus(Request $request, TableBooking $tableBooking)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $tableBooking->update([
            'status' => $validated['status'],
            'confirmed_by' => in_array($validated['status'], ['confirmed', 'cancelled']) ? auth()->id() : null,
            'confirmed_at' => in_array($validated['status'], ['confirmed', 'cancelled']) ? now() : null,
        ]);

        return response()->json([
            'message' => 'Status reservasi berhasil diperbarui',
            'data' => $tableBooking->load(['location', 'outlet', 'confirmedBy'])
        ]);
    }

    /**
     * Delete table booking
     */
    public function destroy(TableBooking $tableBooking)
    {
        $tableBooking->delete();

        return response()->json([
            'message' => 'Reservasi meja berhasil dihapus'
        ]);
    }

    /**
     * Search booking for public users by code or phone
     */
    public function searchPublic(Request $request)
    {
        $queryStr = trim($request->input('booking_code') ?? $request->input('query') ?? '');

        if (!$queryStr) {
            return response()->json([
                'message' => 'Silakan masukkan kode booking atau nomor WhatsApp'
            ], 422);
        }

        $booking = TableBooking::with(['location', 'outlet'])
            ->where('booking_code', $queryStr)
            ->orWhere('whatsapp_number', $queryStr)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$booking) {
            return response()->json([
                'message' => 'Reservasi meja tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'data' => $booking
        ]);
    }
}
