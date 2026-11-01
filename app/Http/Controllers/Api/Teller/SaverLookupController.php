<?php

namespace App\Http\Controllers\Api\Teller;

use App\Http\Controllers\Controller;
use App\Http\Resources\SaverResource;
use App\Models\Saver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class SaverLookupController extends Controller
{
    /**
     * Display a listing of active savers.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Saver::with(['user', 'member'])->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('account_number', 'like', "%{$search}%")
                    ->orWhere('qr_code_token', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        return SaverResource::collection($query->paginate(15));
    }

    /**
     * Lookup saver profile instantly using QR code token or account number.
     */
    public function lookup(Request $request): JsonResponse
    {
        $code = $request->string('code')->trim()->toString();

        if (empty($code)) {
            return response()->json([
                'message' => 'Parameter nomor rekening atau QR code token wajib diisi.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var Saver|null $saver */
        $saver = Saver::with(['user', 'member'])
            ->where('account_number', $code)
            ->orWhere('qr_code_token', $code)
            ->first();

        if (! $saver) {
            return response()->json([
                'message' => 'Data penabung tidak ditemukan dengan nomor rekening atau QR code tersebut.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'message' => 'Profil penabung berhasil ditemukan.',
            'saver' => new SaverResource($saver),
        ]);
    }

    /**
     * Display details of a specific saver.
     */
    public function show(Saver $saver): JsonResponse
    {
        return response()->json([
            'saver' => new SaverResource($saver->load(['user', 'member', 'transactions'])),
        ]);
    }
}
