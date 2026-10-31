{{-- لمسات تصميم للداش بورد بنفس هوية الموقع --}}
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=el-messiri:600,700&display=swap" rel="stylesheet">
<style>
    :root { --flora-rose: #922449; --flora-cream: #fbf7f3; --flora-sand: #f3ebe3; }

    .flora-brand { display: flex; align-items: center; gap: .55rem; }
    .flora-brand-name { font-family: 'El Messiri', 'Cairo', serif; font-size: 1.6rem; font-weight: 700; color: var(--flora-rose); line-height: 1; }
    .dark .flora-brand-name { color: #f6c9d5; }

    /* الخلفية العامة */
    html:not(.dark) .fi-body { background: var(--flora-cream); }

    /* السايدبار */
    html:not(.dark) .fi-sidebar { background: #fff; border-inline-end: 1px solid #ece2dc; }
    .fi-sidebar-group-label { letter-spacing: 0; font-weight: 700; opacity: .7; }
    .fi-sidebar-item-btn { border-radius: .9rem; transition: background .15s, transform .15s; }
    .fi-sidebar-item-btn:hover { transform: translateX(-2px); }
    [dir="ltr"] .fi-sidebar-item-btn:hover { transform: translateX(2px); }
    html:not(.dark) .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
        background: linear-gradient(135deg, #fbe4ea, #fdf2f5);
        box-shadow: inset 0 0 0 1px #f6c9d5;
    }

    /* التوب بار */
    html:not(.dark) .fi-topbar { background: rgba(251, 247, 243, .85); backdrop-filter: blur(10px); box-shadow: none; border-bottom: 1px solid #ece2dc; }

    /* العناوين */
    .fi-header-heading { font-family: 'El Messiri', 'Cairo', serif; letter-spacing: 0; }

    /* الكروت والسكاشن */
    .fi-section { border-radius: 1.25rem !important; }
    html:not(.dark) .fi-section { box-shadow: 0 1px 2px rgb(42 29 34 / .04), 0 8px 24px -12px rgb(42 29 34 / .12); border-color: #f0e6e0; }
    .fi-wi-stats-overview-stat { border-radius: 1.25rem !important; transition: transform .2s, box-shadow .2s; }
    .fi-wi-stats-overview-stat:hover { transform: translateY(-2px); box-shadow: 0 18px 40px -16px rgb(146 36 73 / .3); }
    html:not(.dark) .fi-ta-ctn { border-radius: 1.25rem; }

    /* صور كروت السلايدر والبانرات */
    .fi-ta-record .fi-ta-image, .fi-ta-record .fi-ta-image > div { width: 100%; }
    .flora-card-image { width: 100% !important; height: 170px !important; object-fit: cover; border-radius: .9rem; }

    /* صناديق السكاشن في منشئ الصفحات */
    .fi-fo-builder-item { border-radius: 1rem !important; }
    html:not(.dark) .fi-fo-builder-item-header { background: #fffbfa; }

    /* صفحة تسجيل الدخول */
    html:not(.dark) .fi-simple-layout {
        background:
            radial-gradient(circle at 15% 20%, rgba(246, 201, 213, .55), transparent 40%),
            radial-gradient(circle at 85% 80%, rgba(184, 147, 90, .25), transparent 45%),
            var(--flora-cream);
    }
    .fi-simple-main { border-radius: 1.75rem !important; box-shadow: 0 30px 60px -30px rgb(146 36 73 / .35) !important; }
</style>
