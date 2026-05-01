<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/session.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';
$activeTab = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $result = $auth->login($email, $password);
        if ($result['success']) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = $result['message'];
        }
    } elseif (isset($_POST['signup'])) {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $result = $auth->signup($name, $email, $password, $confirmPassword);
        if ($result['success']) {
            $success = $result['message'];
            $activeTab = 'login';
        } else {
            $error = $result['message'];
            $activeTab = 'signup';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Clinic — Scheduling System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="./css/login.css">
</head>

<body class="min-h-screen" style="background:#f0f7f4;">

    <!-- Navbar -->
    <nav class="bg-white border-b fade-up d1 sticky top-0 z-50" style="border-color:#ddeee7;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 flex items-center justify-between h-14 sm:h-16">

            <!-- Logo -->
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl flex items-center justify-center flex-shrink-0" style="background:#2d8a6e;">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <span class="font-semibold text-sm sm:text-base" style="color:#1a2e25;">School Clinic</span>
            </div>

            <!-- Desktop nav -->
            <div class="hidden sm:flex items-center gap-6">
                <a href="#features" class="text-sm font-medium" style="color:#5a8a79;">Features</a>
                <a href="#about" class="text-sm font-medium" style="color:#5a8a79;">About</a>
                <a href="#login" class="text-sm font-medium text-white px-4 py-2 rounded-lg transition-opacity hover:opacity-90" style="background:#2d8a6e;">Admin login</a>
            </div>

            <!-- Mobile hamburger -->
            <button class="sm:hidden p-2 rounded-lg" style="color:#5a8a79;" onclick="toggleMenu()">
                <svg id="icon-open" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
                <svg id="icon-close" class="w-5 h-5 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Mobile menu -->
        <div id="mobile-menu" class="flex-col gap-0 bg-white border-t sm:hidden" style="border-color:#ddeee7;">
            <a href="#features" onclick="toggleMenu()" class="block px-6 py-3 text-sm font-medium border-b" style="color:#5a8a79;border-color:#ddeee7;">Features</a>
            <a href="#about" onclick="toggleMenu()" class="block px-6 py-3 text-sm font-medium border-b" style="color:#5a8a79;border-color:#ddeee7;">About</a>
            <a href="#login" onclick="toggleMenu()" class="block px-6 py-3 text-sm font-medium" style="color:#2d8a6e;">Admin login</a>
        </div>
    </nav>

    <!-- Hero -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-10 sm:py-16 lg:py-20 flex flex-col lg:grid lg:gap-16 lg:items-center gap-10" style="grid-template-columns:1fr 420px;">

        <!-- Left copy -->
        <div class="fade-up d2 text-center lg:text-left">
            <div class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-medium mb-5" style="background:#e1f5ee;color:#0f6e56;">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Trusted by school staff
            </div>

            <h1 class="font-bold leading-tight mb-4" style="font-size:clamp(28px,5vw,42px);color:#1a2e25;">
                Your school clinic,<br>
                <span style="color:#2d8a6e;">calmly organized.</span>
            </h1>

            <p class="text-sm sm:text-base leading-relaxed mb-8 mx-auto lg:mx-0 max-w-lg" style="color:#5a8a79;">
                A simple, peaceful scheduling system designed for school clinic administrators.
                Manage appointments, track student health, and keep everything in order — without the chaos.
            </p>

            <div class="flex items-center justify-center lg:justify-start gap-6 sm:gap-8">
                <div>
                    <div class="text-xl sm:text-2xl font-bold" style="color:#1a2e25;">500+</div>
                    <div class="text-xs mt-0.5" style="color:#7aaa96;">Students managed</div>
                </div>
                <div class="w-px h-8" style="background:#ddeee7;"></div>
                <div>
                    <div class="text-xl sm:text-2xl font-bold" style="color:#1a2e25;">98%</div>
                    <div class="text-xs mt-0.5" style="color:#7aaa96;">Uptime reliability</div>
                </div>
                <div class="w-px h-8" style="background:#ddeee7;"></div>
                <div>
                    <div class="text-xl sm:text-2xl font-bold" style="color:#1a2e25;">24/7</div>
                    <div class="text-xs mt-0.5" style="color:#7aaa96;">Record access</div>
                </div>
            </div>
        </div>

        <!-- Login card -->
        <div class="fade-up d3 w-full max-w-md mx-auto lg:max-w-none" id="login">
            <div class="bg-white rounded-2xl overflow-hidden" style="border:1px solid #ddeee7;box-shadow:0 8px 40px rgba(45,138,110,.08);">

                <!-- Tabs -->
                <div class="flex" style="border-bottom:1px solid #ddeee7;">
                    <button class="tab-btn <?php echo $activeTab === 'login'  ? 'active' : ''; ?>" id="tab-login" onclick="switchTab('login')">Sign in</button>
                    <button class="tab-btn <?php echo $activeTab === 'signup' ? 'active' : ''; ?>" id="tab-signup" onclick="switchTab('signup')">Create account</button>
                </div>

                <div class="p-5 sm:p-7">

                    <?php if ($error): ?>
                        <div class="mb-4 px-4 py-3 text-sm rounded-lg" style="background:#fff0f0;border-left:3px solid #e07070;color:#7a2020;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="mb-4 px-4 py-3 text-sm rounded-lg" style="background:#e1f5ee;border-left:3px solid #2d8a6e;color:#0f6e56;">
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- Login form -->
                    <div id="panel-login" <?php echo $activeTab !== 'login' ? 'style="display:none;"' : ''; ?>>
                        <p class="text-xs mb-5" style="color:#7aaa96;">Welcome back. Sign in to your admin account.</p>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="block text-xs font-medium mb-1.5 uppercase tracking-wide" style="color:#5a8a79;">Email address</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ab5aa;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <input type="email" name="email" required placeholder="admin@schoolclinic.com"
                                        class="w-full pl-9 pr-4 py-2.5 text-sm rounded-lg"
                                        style="border:1px solid #c5d9d0;background:#fff;color:#1a2e25;">
                                </div>
                            </div>
                            <div class="mb-6">
                                <label class="block text-xs font-medium mb-1.5 uppercase tracking-wide" style="color:#5a8a79;">Password</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ab5aa;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <input type="password" name="password" required placeholder="••••••••"
                                        class="w-full pl-9 pr-4 py-2.5 text-sm rounded-lg"
                                        style="border:1px solid #c5d9d0;background:#fff;color:#1a2e25;">
                                </div>
                            </div>
                            <button type="submit" name="login"
                                class="w-full py-3 rounded-xl text-white text-sm font-medium transition-all active:scale-95"
                                style="background:#2d8a6e;"
                                onmouseover="this.style.background='#236b55'"
                                onmouseout="this.style.background='#2d8a6e'">
                                Sign in to dashboard
                            </button>
                        </form>
                    </div>

                    <!-- Signup form -->
                    <div id="panel-signup" <?php echo $activeTab !== 'signup' ? 'style="display:none;"' : ''; ?>>
                        <p class="text-xs mb-5" style="color:#7aaa96;">Create a new administrator account.</p>
                        <form method="POST" action="">
                            <div class="mb-4">
                                <label class="block text-xs font-medium mb-1.5 uppercase tracking-wide" style="color:#5a8a79;">Full name</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ab5aa;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <input type="text" name="name" required placeholder="Juan Dela Cruz"
                                        class="w-full pl-9 pr-4 py-2.5 text-sm rounded-lg"
                                        style="border:1px solid #c5d9d0;background:#fff;color:#1a2e25;">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-medium mb-1.5 uppercase tracking-wide" style="color:#5a8a79;">Email address</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ab5aa;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                    <input type="email" name="email" required placeholder="admin@schoolclinic.com"
                                        class="w-full pl-9 pr-4 py-2.5 text-sm rounded-lg"
                                        style="border:1px solid #c5d9d0;background:#fff;color:#1a2e25;">
                                </div>
                            </div>
                            <div class="mb-4">
                                <label class="block text-xs font-medium mb-1.5 uppercase tracking-wide" style="color:#5a8a79;">Password</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ab5aa;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <input type="password" name="password" required placeholder="Minimum 6 characters"
                                        class="w-full pl-9 pr-4 py-2.5 text-sm rounded-lg"
                                        style="border:1px solid #c5d9d0;background:#fff;color:#1a2e25;">
                                </div>
                            </div>
                            <div class="mb-6">
                                <label class="block text-xs font-medium mb-1.5 uppercase tracking-wide" style="color:#5a8a79;">Confirm password</label>
                                <div class="relative">
                                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:#9ab5aa;" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                    </svg>
                                    <input type="password" name="confirm_password" required placeholder="Re-enter password"
                                        class="w-full pl-9 pr-4 py-2.5 text-sm rounded-lg"
                                        style="border:1px solid #c5d9d0;background:#fff;color:#1a2e25;">
                                </div>
                            </div>
                            <button type="submit" name="signup"
                                class="w-full py-3 rounded-xl text-white text-sm font-medium transition-all active:scale-95"
                                style="background:#1d9e75;"
                                onmouseover="this.style.background='#157a5a'"
                                onmouseout="this.style.background='#1d9e75'">
                                Create admin account
                            </button>
                        </form>
                    </div>

                    <p class="text-center mt-5" style="font-size:11px;color:#9ab5aa;">
                        Restricted to administrators only &bull; role_id = 1 required
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="bg-white" style="border-top:1px solid #ddeee7;border-bottom:1px solid #ddeee7;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
            <p class="text-xs font-semibold uppercase tracking-widest mb-2" style="color:#2d8a6e;">Features</p>
            <h2 class="text-xl sm:text-2xl font-bold mb-8 sm:mb-12" style="color:#1a2e25;">Everything you need, nothing you don't.</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">

                <div class="flex flex-col gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#e1f5ee;">
                        <svg class="w-5 h-5" fill="none" stroke="#2d8a6e" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold" style="color:#1a2e25;">Appointment scheduling</div>
                    <div class="text-xs leading-relaxed" style="color:#7aaa96;">Book and manage student clinic visits with ease. No double bookings, no confusion.</div>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#e1f5ee;">
                        <svg class="w-5 h-5" fill="none" stroke="#2d8a6e" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold" style="color:#1a2e25;">Health records</div>
                    <div class="text-xs leading-relaxed" style="color:#7aaa96;">Maintain organized student health records, visit history, and medical notes securely.</div>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#e1f5ee;">
                        <svg class="w-5 h-5" fill="none" stroke="#2d8a6e" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold" style="color:#1a2e25;">Student profiles</div>
                    <div class="text-xs leading-relaxed" style="color:#7aaa96;">Each student has a complete profile with contact info, grade, and visit history at a glance.</div>
                </div>

                <div class="flex flex-col gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#e1f5ee;">
                        <svg class="w-5 h-5" fill="none" stroke="#2d8a6e" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div class="text-sm font-semibold" style="color:#1a2e25;">Reports & analytics</div>
                    <div class="text-xs leading-relaxed" style="color:#7aaa96;">Generate clear reports on clinic usage, common ailments, and visit trends over time.</div>
                </div>

            </div>
        </div>
    </section>

    <!-- About -->
    <section id="about" class="py-12 sm:py-16 px-4 sm:px-6">
        <div class="max-w-xl mx-auto text-center">
            <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-2xl flex items-center justify-center mx-auto mb-5" style="background:#e1f5ee;">
                <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="#2d8a6e" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold mb-4" style="color:#1a2e25;">Built with care for school clinics</h2>
            <p class="text-sm leading-relaxed" style="color:#7aaa96;">
                This system was designed to reduce the stress of clinic management —
                giving nurses and health staff a calm, focused tool to do their best work for students every day.
            </p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white" style="border-top:1px solid #ddeee7;">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 sm:py-5 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center" style="background:#2d8a6e;">
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <span class="text-sm" style="color:#7aaa96;">School Clinic System</span>
            </div>
            <span style="font-size:11px;color:#9ab5aa;">Admin access only &bull; Secure &bull; Private</span>
        </div>
    </footer>

    <script src="./js/login.js"></script>

</body>

</html>