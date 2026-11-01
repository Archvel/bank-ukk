<?php

namespace App\Http\Controllers\Api\Teller;

use App\Enums\MemberAccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MemberLookupController extends Controller
{
    /**
     * Display a listing of inactive members ready for initial deposit & account activation.
     */
    public function pending(Request $request): AnonymousResourceCollection
    {
        $query = Member::with('user')
            ->where('account_status', MemberAccountStatus::Inactive)
            ->latest();

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('student_name', 'like', "%{$search}%")
                    ->orWhere('graduation_year', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('email', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        return MemberResource::collection($query->paginate(20));
    }
}
