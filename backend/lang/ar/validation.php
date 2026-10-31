<?php

// رسائل الأخطاء بالعربي للـ API (صفحة إتمام الطلب)
return [
    'required' => 'حقل :attribute مطلوب.',
    'required_if' => 'حقل :attribute مطلوب.',
    'string' => 'حقل :attribute لازم يكون نص.',
    'email' => 'الإيميل غير صحيح.',
    'max' => [
        'string' => 'حقل :attribute أطول من :max حرف.',
        'file' => 'حجم الملف أكبر من المسموح.',
        'numeric' => 'حقل :attribute أكبر من :max.',
        'array' => 'عدد العناصر كبير.',
    ],
    'min' => [
        'string' => 'حقل :attribute أقصر من :min حروف.',
        'numeric' => 'حقل :attribute أقل من :min.',
        'array' => 'السلة فاضية.',
    ],
    'image' => 'لازم ترفع صورة.',
    'date' => 'التاريخ غير صحيح.',
    'after_or_equal' => 'التاريخ لازم يكون النهارده أو بعد كده.',
    'exists' => 'القيمة المختارة غير متاحة.',
    'in' => 'القيمة المختارة غير متاحة.',
    'integer' => 'حقل :attribute لازم يكون رقم.',
    'boolean' => 'قيمة غير صحيحة.',
    'array' => 'قيمة غير صحيحة.',
    'regex' => 'صيغة :attribute غير صحيحة.',

    'attributes' => [
        'sender_name' => 'اسمك',
        'sender_phone' => 'رقم موبايلك',
        'sender_email' => 'الإيميل',
        'recipient_name' => 'اسم المستلم',
        'recipient_phone' => 'موبايل المستلم',
        'delivery_zone_id' => 'منطقة التوصيل',
        'address' => 'العنوان',
        'delivery_date' => 'يوم التوصيل',
        'delivery_time' => 'الوقت',
        'card_message' => 'رسالة الكارت',
        'notes' => 'الملاحظات',
        'payment_method' => 'طريقة الدفع',
        'payment_proof' => 'سكرين شوت التحويل',
        'items' => 'المنتجات',
    ],
];
