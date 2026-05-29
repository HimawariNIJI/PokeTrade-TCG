<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MidtransController extends Controller
{
    public function notification(Request $request)
    {
        // Configure Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');

        // Read notification
        $notification = new \Midtrans\Notification();

        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
        $statusCode = $notification->status_code;
        $grossAmount = $notification->gross_amount;
        $signatureKey = $notification->signature_key;

        // Validate signature
        $generatedSignature = hash(
            'sha512',
            $orderId .
            $statusCode .
            $grossAmount .
            config('midtrans.server_key')
        );

        if ($generatedSignature !== $signatureKey) {
            return response()->json([
                'message' => 'Invalid signature',
            ], 403);
        }

        // Find bid
        $bid = Bid::where('order_id', $orderId)->first();

        if (! $bid) {
            return response()->json([
                'message' => 'Bid not found',
            ], 404);
        }

        DB::transaction(function () use ($bid, $transactionStatus) {

            // Prevent duplicate processing
            if ($bid->status === Bid::STATUS_PAID) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | SUCCESS / SETTLEMENT
            |--------------------------------------------------------------------------
            */
            if (
                $transactionStatus === 'settlement' ||
                $transactionStatus === 'capture'
            ) {

                $bid->update([
                    'status' => Bid::STATUS_PAID,
                    'paid_at' => now(),
                ]);

                $auction = Auction::lockForUpdate()->find($bid->auction_id);

                // Auction already ended
                if (! $auction || $auction->status !== 'live') {
                    return;
                }

                // Validate bid amount again
                if ($bid->amount <= $auction->current_bid) {
                    return;
                }

                // Update auction
                $auction->update([
                    'current_bid' => $bid->amount,
                    'current_leader_id' => $bid->user_id,
                ]);

                // Auto end if buy now reached
                $shouldEnd = $auction->buy_now_price !== null
                    && $bid->amount >= $auction->buy_now_price;

                if ($shouldEnd) {

                    $auction->snapshotWinner();

                    $auction->update([
                        'status' => 'ended',
                        'ends_at' => now(),
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | PENDING
            |--------------------------------------------------------------------------
            */
            elseif ($transactionStatus === 'pending') {

                $bid->update([
                    'status' => Bid::STATUS_PENDING,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | EXPIRE
            |--------------------------------------------------------------------------
            */
            elseif ($transactionStatus === 'expire') {

                $bid->update([
                    'status' => Bid::STATUS_EXPIRED,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | CANCEL
            |--------------------------------------------------------------------------
            */
            elseif ($transactionStatus === 'cancel') {

                $bid->update([
                    'status' => Bid::STATUS_CANCELLED,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | FAILURE
            |--------------------------------------------------------------------------
            */
            elseif (
                $transactionStatus === 'deny' ||
                $transactionStatus === 'failure'
            ) {

                $bid->update([
                    'status' => Bid::STATUS_FAILED,
                ]);
            }
        });

        return response()->json([
            'success' => true,
        ]);
    }
}