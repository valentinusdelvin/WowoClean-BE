<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\TrackingLog;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ContainerController extends Controller
{
    #[OA\Get(
        path: '/api/v1/gateway/containers',
        operationId: 'getContainers',
        tags: ['Containers'],
        summary: 'Ambil semua kontainer',
        description: 'Mengembalikan daftar semua kontainer beserta tracking log-nya.',
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Daftar kontainer berhasil diambil'
            )
        ]
    )]
    public function index()
    {
        $containers = Container::with('trackingLogs')->get();

        return response()->json($containers);
    }

    #[OA\Post(
        path: '/api/v1/gateway/containers',
        operationId: 'storeContainer',
        tags: ['Containers'],
        summary: 'Tambah kontainer baru',
        description: 'Membuat kontainer baru. Hanya dapat diakses oleh admin. Limbah kimia (Chemical) maksimal 1000 kg.',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Data kontainer baru',
            content: new OA\JsonContent(
                required: ['container_id', 'waste_type', 'weight_kg']
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Kontainer berhasil dibuat'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized - Token tidak valid atau tidak ada'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Hanya admin yang dapat menambah kontainer'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error'
            )
        ]
    )]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'container_id' => ['required', 'regex:/^[A-Z]{2}[0-9]{5}$/', 'unique:containers,container_id'],
            'waste_type' => 'required',
            'weight_kg' => 'required|numeric|min:10|max:5000'
        ]);

        // Conditional validation: Chemical waste maximum 1000 kg
        if (strtolower($request->waste_type) === strtolower("chemical") && $request->weight_kg > 1000) {
            return response()->json([
                'errors' => ['weight_kg' => ['Chemical max 1000 kg']]
            ], 422);
        }

        $container = Container::create([
            'container_id' => $request->container_id,
            'waste_type' => $request->waste_type,
            'weight_kg' => $request->weight_kg,
            'status' => 'Active',
        ]);

        return response()->json([
            "message" => "Created",
            "data" => $container
        ], 201);
    }

    #[OA\Patch(
        path: '/api/v1/gateway/containers/{id}',
        operationId: 'updateContainer',
        tags: ['Containers'],
        summary: 'Arsipkan kontainer',
        description: 'Mengubah status kontainer menjadi Archived. Hanya dapat diakses oleh admin.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Container ID (contoh: AB12345)',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'AB12345'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kontainer berhasil diarsipkan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Updated to Archived'
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'container_id', type: 'string', example: 'AB12345'),
                                new OA\Property(property: 'waste_type', type: 'string', example: 'Chemical'),
                                new OA\Property(property: 'weight_kg', type: 'number', format: 'float', example: 500),
                                new OA\Property(property: 'status', type: 'string', example: 'Archived'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Hanya admin'
            ),
            new OA\Response(
                response: 404,
                description: 'Kontainer tidak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Not Found'
                        )
                    ]
                )
            )
        ]
    )]
    public function update(Request $request, $id)
    {
        $container = Container::where('container_id', $id)->first();

        if (!$container) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        $container->update(['status' => 'Archived']);

        return response()->json([
            "message" => "Updated to Archived",
            "data" => $container
        ]);
    }

    #[OA\Delete(
        path: '/api/v1/gateway/containers/{id}',
        operationId: 'deleteContainer',
        tags: ['Containers'],
        summary: 'Hapus kontainer',
        description: 'Menghapus kontainer berdasarkan container_id. Hanya dapat diakses oleh admin.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Container ID (contoh: AB12345)',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'AB12345'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Kontainer berhasil dihapus',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Deleted'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Hanya admin'
            ),
            new OA\Response(
                response: 404,
                description: 'Kontainer tidak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Not Found'
                        )
                    ]
                )
            )
        ]
    )]
    public function destroy($id)
    {
        $container = Container::where('container_id', $id)->first();

        if (!$container) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        $container->delete();

        return response()->json([
            "message" => "Deleted"
        ]);
    }

    #[OA\Get(
        path: '/api/v1/gateway/containers/search',
        operationId: 'searchContainers',
        tags: ['Containers'],
        summary: 'Cari kontainer',
        description: 'Filter kontainer berdasarkan jenis limbah dan/atau berat minimum.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'type',
                in: 'query',
                required: false,
                description: 'Filter berdasarkan jenis limbah',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'Chemical'
                )
            ),
            new OA\Parameter(
                name: 'min_weight',
                in: 'query',
                required: false,
                description: 'Filter berat minimum (kg)',
                schema: new OA\Schema(
                    type: 'number',
                    format: 'float',
                    example: 100
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Hasil pencarian kontainer',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'integer', example: 1),
                            new OA\Property(property: 'container_id', type: 'string', example: 'AB12345'),
                            new OA\Property(property: 'waste_type', type: 'string', example: 'Chemical'),
                            new OA\Property(property: 'weight_kg', type: 'number', format: 'float', example: 500),
                            new OA\Property(property: 'status', type: 'string', example: 'Active'),
                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Tidak ada kontainer yang sesuai',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Not Found'
                        )
                    ]
                )
            )
        ]
    )]
    public function search(Request $request)
    {
        $type = $request->query('type');
        $min = $request->query('min_weight');

        $query = Container::query();

        if ($type) {
            $query->where('waste_type', $type);
        }

        if ($min) {
            $query->where('weight_kg', '>=', $min);
        }

        $filtered = $query->get();

        if ($filtered->count() == 0) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        return response()->json($filtered);
    }

    #[OA\Get(
        path: '/api/v1/gateway/containers/{id}/logs',
        operationId: 'getContainerLogs',
        tags: ['Containers'],
        summary: 'Ambil tracking log kontainer',
        description: 'Mengembalikan daftar tracking log untuk kontainer tertentu.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Container ID (contoh: AB12345)',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'AB12345'
                )
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tracking log berhasil diambil',
                content: new OA\JsonContent(
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(
                                property: 'id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'container_id',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'location',
                                type: 'string',
                                example: 'Gudang A'
                            ),
                            new OA\Property(
                                property: 'timestamp',
                                type: 'string',
                                format: 'date-time'
                            ),
                            new OA\Property(
                                property: 'description',
                                type: 'string',
                                example: 'Kontainer diterima di gudang'
                            ),
                        ]
                    )
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 404,
                description: 'Kontainer tidak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Not Found'
                        )
                    ]
                )
            )
        ]
    )]
    public function logs($id)
    {
        $container = Container::where('container_id', $id)->first();

        if (!$container) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        return response()->json($container->trackingLogs);
    }

    #[OA\Post(
        path: '/api/v1/gateway/containers/{id}/logs',
        operationId: 'storeContainerLog',
        tags: ['Containers'],
        summary: 'Tambah tracking log kontainer',
        description: 'Membuat tracking log baru untuk kontainer tertentu. Hanya dapat diakses oleh admin.',
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Container ID (contoh: AB12345)',
                schema: new OA\Schema(
                    type: 'string',
                    example: 'AB12345'
                )
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            description: 'Data tracking log',
            content: new OA\JsonContent(
                required: ['location', 'timestamp', 'description'],
                properties: [
                    new OA\Property(
                        property: 'location',
                        type: 'string',
                        example: 'Gudang A'
                    ),
                    new OA\Property(
                        property: 'timestamp',
                        type: 'string',
                        format: 'date-time',
                        example: '2026-05-30T10:00:00Z'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Kontainer diterima di gudang penyimpanan'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tracking log berhasil dibuat',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Tracking log created'
                        ),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer', example: 1),
                                new OA\Property(property: 'container_id', type: 'integer', example: 1),
                                new OA\Property(property: 'location', type: 'string', example: 'Gudang A'),
                                new OA\Property(property: 'timestamp', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'description', type: 'string', example: 'Kontainer diterima di gudang penyimpanan'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                            ]
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized'
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden - Hanya admin'
            ),
            new OA\Response(
                response: 404,
                description: 'Kontainer tidak ditemukan',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Not Found'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation Error',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string'
                        ),
                        new OA\Property(
                            property: 'errors',
                            type: 'object'
                        )
                    ]
                )
            )
        ]
    )]
    public function storeLog(Request $request, $id)
    {
        $container = Container::where('container_id', $id)->first();

        if (!$container) {
            return response()->json([
                "message" => "Not Found"
            ], 404);
        }

        $request->validate([
            'location' => 'required|string',
            'timestamp' => 'required|date',
            'description' => 'required|string',
        ]);

        $log = TrackingLog::create([
            'container_id' => $container->id,
            'location' => $request->location,
            'timestamp' => $request->timestamp,
            'description' => $request->description,
        ]);

        return response()->json([
            "message" => "Tracking log created",
            "data" => $log
        ], 201);
    }
}
