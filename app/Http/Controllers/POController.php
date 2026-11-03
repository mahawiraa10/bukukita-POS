<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceivePORequest;
use App\Http\Requests\StorePORequest;
use App\Http\Resources\POResource;
use App\Models\PurchaseOrder;
use App\Services\POService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class POController extends Controller
{
    public function __construct(
        protected POService $poService
    ) {}

    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['vendor', 'items.product']);

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        return POResource::collection($query->paginate($request->input('per_page', 15)));
    }

    public function show(int $id)
    {
        $po = PurchaseOrder::with(['vendor', 'items.product'])->findOrFail($id);
        return new POResource($po);
    }

    public function store(StorePORequest $request): JsonResponse
    {
        $po = $this->poService->createPo($request->validated());

        return (new POResource($po))
            ->response()
            ->setStatusCode(201);
    }

    public function approve(int $id)
    {
        return new POResource($this->poService->approve($id));
    }

    public function receive(ReceivePORequest $request, int $id)
    {
        return new POResource($this->poService->receive($id, $request->input('items')));
    }

    public function cancel(int $id)
    {
        return new POResource($this->poService->cancel($id));
    }

}