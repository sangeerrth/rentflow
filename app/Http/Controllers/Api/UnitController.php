<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{
    public function index(Property $property)
    {
        Gate::authorize('view', $property);

        return $property->units()->orderBy('unit_number')->get();
    }

    public function store(Request $request, Property $property)
    {
        Gate::authorize('update', $property);

        $data = $request->validate([
            'unit_number' => [
                'required', 'string', 'max:20',
                Rule::unique('units')->where('property_id', $property->id),
            ],
            'bedrooms'    => 'nullable|integer|min:0|max:20',
            'rent_amount' => 'required|numeric|min:0',
            'status'      => 'nullable|in:vacant,occupied',
        ]);

        $unit = $property->units()->create($data);

        return response()->json($unit, 201);
    }

    public function show(Unit $unit)
    {
        Gate::authorize('view', $unit->property);

        return $unit->load('property');
    }

    public function update(Request $request, Unit $unit)
    {
        Gate::authorize('update', $unit->property);

        $data = $request->validate([
            'unit_number' => [
                'sometimes', 'string', 'max:20',
                Rule::unique('units')
                    ->where('property_id', $unit->property_id)
                    ->ignore($unit->id),
            ],
            'bedrooms'    => 'sometimes|integer|min:0|max:20',
            'rent_amount' => 'sometimes|numeric|min:0',
            'status'      => 'sometimes|in:vacant,occupied',
        ]);

        $unit->update($data);

        return $unit;
    }

    public function destroy(Unit $unit)
    {
        Gate::authorize('delete', $unit->property);

        $unit->delete();

        return response()->noContent();
    }
}