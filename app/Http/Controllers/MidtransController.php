<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Notifications\OrderPaidNotification;

class MidtransController extends Controller
{
    public function notification(Request $request)
    {
        // 1. Setup Konfigurasi Midtrans
        \Midtrans\Config::$serverKey = config('midtrans.server_key');
        \Midtrans\Config::$isProduction = config('midtrans.is_production');

        // 2. Terima Data Notifikasi
        $notification = new \Midtrans\Notification();
        $transactionStatus = $notification->transaction_status;
        $orderId = $notification->order_id;
        $statusCode = $notification->status_code;
        $grossAmount = $notification->gross_amount;
        $signatureKey = $notification->signature_key;

        // 3. Validasi Keamanan Signature
        $generatedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key'));
        if ($generatedSignature !== $signatureKey) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        /* =======================================================
           PENGECEKAN 1: APAKAH INI MERCHANDISE?
           (Cari di tabel orders)
           ======================================================= */
        $order = Order::where('code', $orderId)->first();

        if ($order) {
            DB::transaction(function () use ($order, $transactionStatus) {
                if ($order->payment_status === 'paid') return;

                if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                    $order->update([
                        'payment_status' => 'paid',
                        'status' => 'paid',
                        'paid_at' => now(),
                    ]);
                    
                    $pointsearned = floor($order->subtotal / 10000);
                    if ($order->user) {
                        $order->user->increment('points', $pointsearned);
                        $order->user->notify(new OrderPaidNotification($order));
                    }
                } 
                elseif ($transactionStatus === 'pending') {
                    $order->update(['payment_status' => 'unpaid', 'status' => 'pending']);
                } 
                elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
                    if ($order->status !== 'cancelled') {
                        foreach ($order->items as $orderItem) {
                            if ($product = $orderItem->itemable) {
                                $product->increment('stock', $orderItem->quantity);
                            }
                        }
                        $order->update(['payment_status' => 'failed', 'status' => 'cancelled']);
                    }
                }
            });

            return response()->json(['success' => true, 'type' => 'merchandise']);
        }


        /* =======================================================
           PENGECEKAN 2: APAKAH INI LELANG?
           (Cari di tabel bids)
           ======================================================= */
        $bid = Bid::where('order_id', $orderId)->first();

        if ($bid) {
            DB::transaction(function () use ($bid, $transactionStatus) {
                if ($bid->status === Bid::STATUS_PAID) return;

                if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                    $bid->update(['status' => Bid::STATUS_PAID, 'paid_at' => now()]);
                    
                    $auction = Auction::lockForUpdate()->find($bid->auction_id);
                    if (!$auction || $auction->status !== 'live') return;
                    
                    if ($bid->amount > $auction->current_bid) {
                        $auction->update([
                            'current_bid' => $bid->amount,
                            'current_leader_id' => $bid->user_id,
                        ]);
                    }

                    if ($auction->buy_now_price !== null && $bid->amount >= $auction->buy_now_price) {
                        $auction->snapshotWinner();
                        $auction->update(['status' => 'ended', 'ends_at' => now()]);
                    }
                } 
                elseif ($transactionStatus === 'pending') {
                    $bid->update(['status' => Bid::STATUS_PENDING]);
                } 
                elseif ($transactionStatus === 'expire') {
                    $bid->update(['status' => Bid::STATUS_EXPIRED]);
                } 
                elseif ($transactionStatus === 'cancel') {
                    $bid->update(['status' => Bid::STATUS_CANCELLED]);
                } 
                elseif (in_array($transactionStatus, ['deny', 'failure'])) {
                    $bid->update(['status' => Bid::STATUS_FAILED]);
                }
            });

            return response()->json(['success' => true, 'type' => 'auction']);
        }


        /* =======================================================
           PENGECEKAN 3: TIDAK KETEMU DI KEDUANYA
           ======================================================= */
        // Berikan status 200 agar Midtrans berhenti mengulang notifikasi (menghindari error log)
        return response()->json(['message' => 'Order ID not found in both tables, but acknowledged'], 200);
    }
}