<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index(Request $request)
    {
        $query = Table::query();

        if ($request->has('outlet_id')) {
            $query->where('outlet_id', $request->outlet_id);
        }

        if ($request->has('location_id')) {
            $location = \App\Models\Location::find($request->location_id);
            if ($location && $location->outlet_id) {
                $query->where('outlet_id', $location->outlet_id);
            }
        }

        $tables = $query->orderBy('table_number', 'asc')->get();

        return response()->json($tables);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'outlet_id' => 'required|exists:outlets,id',
            'table_number' => 'required|string',
            'capacity' => 'nullable|integer',
        ]);

        $table = Table::create([
            'outlet_id' => $validated['outlet_id'],
            'table_number' => $validated['table_number'],
            'capacity' => $validated['capacity'] ?? 4,
            'status' => 'available',
            'is_active' => true,
        ]);

        return response()->json($table, 201);
    }
}
