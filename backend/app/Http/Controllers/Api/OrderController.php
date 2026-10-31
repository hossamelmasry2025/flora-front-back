<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Support\Frontend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $settings = Setting::allCached();
        $methods = array_keys(array_filter([
            'cod' => $settings['cod_enabled'] ?? true,
            'transfer' => $settings['transfer_enabled'] ?? true,
        ]));

        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:30'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:50'],

            'sender_name' => ['required', 'string', 'max:100'],
            'sender_phone' => ['required', 'string', 'regex:/^[0-9+\s-]{8,20}$/'],
            'sender_email' => ['nullable', 'email', 'max:150'],
            'hide_sender' => ['nullable', 'boolean'],

            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'regex:/^[0-9+\s-]{8,20}$/'],
            'delivery_zone_id' => ['nullable', Rule::exists('delivery_zones', 'id')->where('is_active', true)],
            'address' => ['required', 'string', 'max:500'],
            'delivery_date' => ['nullable', 'date', 'after_or_equal:today'],
            'delivery_time' => ['nullable', 'string', 'max:50'],

            'card_message' => ['nullable', 'string', 'max:'.(int) ($settings['card_max_length'] ?? 300)],
            'notes' => ['nullable', 'string', 'max:1000'],

            'payment_method' => ['required', Rule::in($methods)],
            'payment_proof' => ['required_if:payment_method,transfer', 'nullable', 'image', 'max:5120'],
        ], [
            'payment_proof.required_if' => __('برجاء رفع صورة (سكرين شوت) التحويل.'),
            'sender_phone.regex' => __('رقم الموبايل غير صحيح.'),
            'recipient_phone.regex' => __('رقم الموبايل غير صحيح.'),
        ]);

        if (DeliveryZone::where('is_active', true)->exists() && empty($data['delivery_zone_id'])) {
            throw ValidationException::withMessages(['delivery_zone_id' => __('برجاء اختيار منطقة التوصيل.')]);
        }

        // الأسعار دايماً من قاعدة البيانات، مش من المتصفح
        $quantities = collect($data['items'])->groupBy('product_id')->map(fn ($rows) => $rows->sum('quantity'));
        $products = Product::active()->whereIn('id', $quantities->keys())->get()->keyBy('id');

        $unavailable = $quantities->keys()->reject(fn ($id) => $products->has($id) && $products[$id]->in_stock);
        if ($unavailable->isNotEmpty()) {
            throw ValidationException::withMessages(['items' => __('بعض المنتجات في السلة لم تعد متاحة، برجاء تحديث السلة.')]);
        }

        $subtotal = $quantities->map(fn ($qty, $id) => $products[$id]->finalPrice() * $qty)->sum();
        $minOrder = (float) ($settings['min_order'] ?? 0);
        if ($minOrder > 0 && $subtotal < $minOrder) {
            throw ValidationException::withMessages(['items' => __('الحد الأدنى للطلب :amount ج.م', ['amount' => $minOrder])]);
        }

        $zone = isset($data['delivery_zone_id']) ? DeliveryZone::find($data['delivery_zone_id']) : null;
        $fee = (float) ($zone?->fee ?? 0);

        // إيصالات الدفع في مساحة خاصة (مش متاحة للعامة) وبتتعرض للأدمن بس
        $proof = $request->file('payment_proof')?->store('payment-proofs', 'local');

        $order = DB::transaction(function () use ($data, $quantities, $products, $subtotal, $zone, $fee, $proof) {
            $order = Order::create([
                ...collect($data)->except(['items', 'payment_proof'])->all(),
                'status' => 'pending',
                'locale' => app()->getLocale(),
                'hide_sender' => (bool) ($data['hide_sender'] ?? false),
                'delivery_zone_name' => $zone?->name,
                'payment_proof' => $proof,
                'payment_status' => $proof ? 'under_review' : 'unpaid',
                'subtotal' => $subtotal,
                'delivery_fee' => $fee,
                'total' => $subtotal + $fee,
            ]);

            foreach ($quantities as $id => $qty) {
                $product = $products[$id];
                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_image' => $product->image,
                    'price' => $product->finalPrice(),
                    'quantity' => $qty,
                    'total' => $product->finalPrice() * $qty,
                ]);
            }

            return $order;
        });

        $this->notifyAdmins($order);

        return response()->json(['data' => $this->present($order->load('items.product', 'statusLogs', 'deliveryZone'))], 201);
    }

    /** تتبع الطلب برقم الطلب + موبايل المرسل */
    public function track(Request $request, string $number)
    {
        $request->validate(['phone' => ['required', 'string']]);

        $digits = fn ($v) => preg_replace('/\D/', '', $v);

        $order = Order::where('number', strtoupper(trim($number)))->with('items.product', 'statusLogs', 'deliveryZone')->first();

        abort_unless($order && $digits($order->sender_phone) === $digits($request->phone), 404);

        return ['data' => $this->present($order)];
    }

    /** إشعار في الداش بورد (الجرس) لكل الأدمنز */
    private function notifyAdmins(Order $order): void
    {
        rescue(fn () => Notification::make()
            ->title("طلب جديد #{$order->number}")
            ->body("{$order->sender_name} — ".number_format((float) $order->total).' ج.م — '.Order::PAYMENT_METHODS[$order->payment_method])
            ->icon('heroicon-o-shopping-bag')
            ->iconColor('success')
            ->actions([Action::make('view')->label('عرض الطلب')->url(route('filament.admin.resources.orders.view', $order))->markAsRead()])
            ->sendToDatabase(User::all()), report: false);
    }

    private function present(Order $order): array
    {
        return [
            'number' => $order->number,
            'status' => $order->status,
            'status_label' => Order::label($order->status),
            'payment_method' => $order->payment_method,
            'payment_method_label' => Order::label($order->payment_method),
            'payment_status' => $order->payment_status,
            'payment_status_label' => Order::label($order->payment_status),
            'recipient_name' => $order->recipient_name,
            'delivery_zone' => $order->deliveryZone?->tr('name') ?? $order->delivery_zone_name,
            'has_card' => filled($order->card_message),
            'delivery_date' => $order->delivery_date?->toDateString(),
            'delivery_time' => $order->delivery_time,
            'subtotal' => (float) $order->subtotal,
            'delivery_fee' => (float) $order->delivery_fee,
            'total' => (float) $order->total,
            'created_at' => $order->created_at->toIso8601String(),
            'history' => $order->statusLogs->map(fn ($log) => [
                'status' => $log->status,
                'label' => Order::label($log->status),
                'at' => $log->created_at?->toIso8601String(),
            ])->values(),
            'items' => $order->items->map(fn ($i) => [
                'name' => $i->product?->tr('name') ?? $i->product_name,
                'image' => Frontend::media($i->product_image),
                'price' => (float) $i->price,
                'quantity' => $i->quantity,
                'total' => (float) $i->total,
            ]),
        ];
    }
}
