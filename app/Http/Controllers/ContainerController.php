<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContainerController extends Controller
{
    private $containers = [
        [
            'container_id' => 'AB12345',
            'waste_type' => 'Chemical',
            'weight_kg' => 450,
            'status' => 'Active',
            'tracking_logs' => [
                ['location' => 'Gudang A - Jakarta', 'timestamp' => '2026-04-10 08:00:00', 'description' => 'Kontainer diterima di gudang utama.'],
                ['location' => 'Transit Hub - Bekasi', 'timestamp' => '2026-04-11 14:30:00', 'description' => 'Dalam perjalanan ke fasilitas pengolahan.'],
            ],
        ],
        [
            'container_id' => 'CD67890',
            'waste_type' => 'General',
            'weight_kg' => 1200,
            'status' => 'Active',
            'tracking_logs' => [
                ['location' => 'Laboratorium B - Surabaya', 'timestamp' => '2026-04-12 09:15:00', 'description' => 'Pengambilan sampel awal.'],
            ],
        ],
        [
            'container_id' => 'EF11111',
            'waste_type' => 'General',
            'weight_kg' => 780,
            'status' => 'Archived',
            'tracking_logs' => [],
        ],
        [
            'container_id' => 'GH22222',
            'waste_type' => 'General',
            'weight_kg' => 3200,
            'status' => 'Active',
            'tracking_logs' => [
                ['location' => 'Gudang C - Bandung', 'timestamp' => '2026-04-14 07:45:00', 'description' => 'Kontainer diisi dan disegel.'],
                ['location' => 'Pelabuhan - Semarang', 'timestamp' => '2026-04-15 16:00:00', 'description' => 'Menunggu pengiriman laut.'],
            ],
        ],
        [
            'container_id' => 'IJ33333',
            'waste_type' => 'Chemical',
            'weight_kg' => 95,
            'status' => 'Active',
            'tracking_logs' => [],
        ],
    ];

    public function index()
    {
        return response()->json($this->containers);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'container_id' => ['required', 'regex:/^[A-Z]{2}[0-9]{5}$/'],
            'waste_type' => 'required',
            'weight_kg' => 'required|numeric|min:10|max:5000'
        ]);

        if (strtolower($request->waste_type) === strtolower("chemical") && $request->weight_kg > 1000) {
            return response()->json([
                'errors' => ['weight_kg' => ['Chemical max 1000 kg']]
            ], 422);
        }

        return response()->json([
            "message" => "Created"
        ], 201);
    }

    public function update(Request $request, $id)
    {
        return response()->json([
            "message" => "Updated to Archived"
        ]);
    }

    public function destroy($id)
    {
        return response()->json([
            "message" => "Deleted"
        ]);
    }

    public function search(Request $request)
    {
        $type = $request->query('type');
        $min = $request->query('min_weight');

        $filtered = collect($this->containers)->filter(function ($c) use ($type, $min) {
            if ($type && $c['waste_type'] != $type)
                return false;
            if ($min && $c['weight_kg'] < $min)
                return false;
            return true;
        });

        if ($filtered->count() == 0) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        return response()->json($filtered->values());
    }

    public function logs($id)
    {
        foreach ($this->containers as $c) {
            if ($c['container_id'] == $id) {
                return response()->json($c['tracking_logs']);
            }
        }

        return response()->json([
            "message" => "Not Found"
        ], 404);
    }
}
