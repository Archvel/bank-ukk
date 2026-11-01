<?php

namespace App\Http\Controllers\Api\Teller;

use App\Actions\DepositAction;
use App\Actions\WithdrawAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\WithdrawRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    /**
     * Process cash deposit or initial deposit activation.
     */
    public function deposit(DepositRequest $request, DepositAction $action): JsonResponse
    {
        $teller = $request->user();
        $amount = $request->integer('amount');
        $pin = $request->filled('pin') ? $request->string('pin')->toString() : null;
        $notes = $request->filled('notes') ? $request->string('notes')->toString() : null;

        $member = null;
        $saver = null;

        if ($request->filled('member_id')) {
            $member = Member::findOrFail($request->integer('member_id'));
        } elseif ($request->filled('saver_id')) {
            $saver = Saver::findOrFail($request->integer('saver_id'));
        } elseif ($request->filled('account_number')) {
            $saver = Saver::where('account_number', $request->string('account_number'))->firstOrFail();
        }

        $transaction = $action->handle(
            teller: $teller,
            amount: $amount,
            member: $member,
            saver: $saver,
            pin: $pin,
            notes: $notes
        );

        return response()->json([
            'message' => 'Transaksi setoran tunai berhasil diproses.',
            'transaction' => new TransactionResource($transaction->load(['saver.user', 'teller', 'journalEntries'])),
        ], Response::HTTP_CREATED);
    }

    /**
     * Process cash withdrawal with 6-digit PIN verification.
     */
    public function withdraw(WithdrawRequest $request, WithdrawAction $action): JsonResponse
    {
        $teller = $request->user();
        $accountNumber = $request->string('account_number')->toString();
        $pin = $request->string('pin')->toString();
        $amount = $request->integer('amount');
        $notes = $request->filled('notes') ? $request->string('notes')->toString() : null;

        $saver = Saver::where('account_number', $accountNumber)->firstOrFail();

        $transaction = $action->handle(
            teller: $teller,
            saver: $saver,
            pin: $pin,
            amount: $amount,
            notes: $notes
        );

        return response()->json([
            'message' => 'Transaksi penarikan tunai berhasil diproses.',
            'transaction' => new TransactionResource($transaction->load(['saver.user', 'teller', 'journalEntries'])),
        ], Response::HTTP_CREATED);
    }

    /**
     * Get transactions processed today by the authenticated teller.
     */
    public function today(Request $request): AnonymousResourceCollection
    {
        $teller = $request->user();

        $transactions = Transaction::with(['saver.user', 'teller'])
            ->where('teller_id', $teller->id)
            ->whereDate('created_at', today())
            ->latest('id')
            ->paginate(20);

        return TransactionResource::collection($transactions);
    }

    /**
     * Display the specified transaction.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        return response()->json([
            'transaction' => new TransactionResource($transaction->load(['saver.user', 'teller', 'journalEntries'])),
        ]);
    }
}
