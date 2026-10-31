<?php

use App\Models\Order;
use App\Support\Frontend;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

// الموقع نفسه على Next.js، فأي حد يفتح الباك إند بيتحول للداش بورد
Route::redirect('/', '/admin');

Route::middleware('auth')->prefix('admin-tools')->group(function () {
    Route::get('orders/{order}/payment-proof', function (Order $order) {
        abort_unless($order->payment_proof && Storage::disk('local')->exists($order->payment_proof), 404);

        return Storage::disk('local')->response($order->payment_proof);
    })->name('admin.payment-proof');

    Route::get('orders/{order}/print', fn (Order $order) => view('orders.print', [
        'order' => $order->load('items'),
        'siteName' => \App\Models\Setting::get('site_name', 'فلورا'),
        'siteUrl' => Frontend::url(),
    ]))->name('admin.orders.print');
});
