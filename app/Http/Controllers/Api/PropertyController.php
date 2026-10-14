<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PropertyController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()
            ->properties()
            ->withCount('units')
            ->latest()
            ->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'address_line' => 'required|string|max:255',
            'city'         => 'required|string|max:100',
            'state'        => 'required|string|max:100',
            'pincode'      => 'required|string|max:10',
        ]);

        $property = $request->user()->properties()->create($data);

        return response()->json($property, 201);
    }

    public function show(Property $property)
    {
        Gate::authorize('view', $property);

        return $property->load('units');
    }

    public function update(Request $request, Property $property)
    {
        Gate::authorize('update', $property);

        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'address_line' => 'sometimes|string|max:255',
            'city'         => 'sometimes|string|max:100',
            'state'        => 'sometimes|string|max:100',
            'pincode'      => 'sometimes|string|max:10',
        ]);

        $property->update($data);

        return $property;
    }

    public function destroy(Property $property)
    {
        Gate::authorize('delete', $property);

        $property->delete();

        return response()->noContent();
    }
}