<?php
session_start();
include 'db.php';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php"); exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        $_SESSION['role'] = $row['role']; // Role Save kar liya
        
        header("Location: index.php");
        exit;
    } else {
        $error = "Ghalat Username ya Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CellNex - Login</title>
    <script src="js/tailwind.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @font-face { font-family: 'Poppins'; src: url('../Tijarat-PRO/fonts/Poppins-Regular.ttf'); }
        @font-face { font-family: 'Poppins'; src: url('../Tijarat-PRO/fonts/Poppins-Bold.ttf'); font-weight: 700; }
        body { font-family: 'Poppins', sans-serif; }
        .fade-in { animation: fadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .slide-up { animation: slideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.97) translateY(10px); } to { opacity: 1; transform: scale(1) translateY(0); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-12px); } }
        .float-anim { animation: float 6s ease-in-out infinite; }
        .feature-item { opacity: 0; animation: slideUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .feature-item:nth-child(1) { animation-delay: 0.3s; }
        .feature-item:nth-child(2) { animation-delay: 0.45s; }
        .feature-item:nth-child(3) { animation-delay: 0.6s; }
        .feature-item:nth-child(4) { animation-delay: 0.75s; }
    </style>
</head>
<body class="bg-slate-950 h-screen flex overflow-hidden relative">

    <!-- ═══════════════ LEFT PANEL — Branding & Visuals ═══════════════ -->
    <div class="hidden lg:flex w-[55%] relative overflow-hidden flex-col justify-between p-12">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-950 via-slate-950 to-indigo-950 z-0"></div>
        <div class="absolute -top-[20%] -left-[15%] w-[60%] h-[60%] bg-blue-500/15 rounded-full blur-[150px] float-anim"></div>
        <div class="absolute -bottom-[15%] -right-[10%] w-[50%] h-[50%] bg-indigo-500/12 rounded-full blur-[130px] float-anim" style="animation-delay: 3s;"></div>
        <div class="absolute inset-0 opacity-[0.03] bg-[radial-gradient(#fff_1px,transparent_1px)] [background-size:32px_32px] z-[1]"></div>

        <div class="relative z-10 slide-up">
            <div class="flex items-center gap-3">
                <div class="h-11 w-11 bg-gradient-to-tr from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center text-white text-lg shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>
                <span class="text-white font-black text-xl tracking-tight">CellNex</span>
            </div>
        </div>

        <div class="relative z-10 -mt-8">
            <h2 class="text-4xl font-black text-white leading-tight tracking-tight mb-4 slide-up">
                Smart Mobile Shop<br>
                <span class="bg-gradient-to-r from-blue-400 to-indigo-400 bg-clip-text text-transparent">POS & Repair Hub</span>
            </h2>
            <p class="text-slate-400 text-sm leading-relaxed mb-10 max-w-md slide-up" style="animation-delay: 0.15s;">
                Advanced digital registry designed for mobile phone retail stores, accessories shops, repairs job status tracking, and offline data sync.
            </p>

            <div class="space-y-4 max-w-sm">
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-blue-500/15 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-barcode text-blue-400 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-white text-[13px] font-bold">IMEI & Serial Tracking</div>
                        <div class="text-slate-500 text-[11px] font-medium">Auto logging device purchase and sales history</div>
                    </div>
                </div>
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-indigo-500/15 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-screwdriver-wrench text-indigo-400 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-white text-[13px] font-bold">Repair Job Cards tracking</div>
                        <div class="text-slate-500 text-[11px] font-medium">Assign tasks and notify ready repairs via WhatsApp</div>
                    </div>
                </div>
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-sky-500/15 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-users text-sky-400 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-white text-[13px] font-bold">Customer Khata & Udhaar</div>
                        <div class="text-slate-500 text-[11px] font-medium">Track local client credit balance ledgers easily</div>
                    </div>
                </div>
                <div class="feature-item flex items-center gap-4 bg-white/[0.04] border border-white/[0.06] rounded-2xl px-5 py-4 backdrop-blur-sm">
                    <div class="w-10 h-10 bg-violet-500/15 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-shield-halved text-violet-400 text-sm"></i>
                    </div>
                    <div>
                        <div class="text-white text-[13px] font-bold">Multi-role permissions</div>
                        <div class="text-slate-500 text-[11px] font-medium">Owner, Salesman, and Technician role limits</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="relative z-10">
            <p class="text-[10px] text-slate-600 font-bold uppercase tracking-widest">© 2026 TechBrain • All Rights Reserved</p>
        </div>
    </div>

    <!-- ═══════════════ RIGHT PANEL — Login Form ═══════════════ -->
    <div class="w-full lg:w-[45%] flex items-center justify-center p-6 lg:p-12 relative">
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-[20%] right-[-20%] w-[60%] h-[60%] bg-blue-500/5 rounded-full blur-[100px]"></div>
        </div>

        <div class="w-full max-w-[420px] z-10 fade-in relative">
          <div class="bg-white/[0.04] backdrop-blur-2xl border border-white/[0.08] rounded-[28px] p-10 shadow-2xl shadow-black/30">
            <!-- Mobile brand header -->
            <div class="flex flex-col items-center mb-8 lg:hidden">
                <div class="h-16 w-16 bg-gradient-to-tr from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center text-white text-3xl shadow-xl shadow-blue-500/20 mb-4">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </div>
                <h1 class="text-3xl font-black text-white tracking-tight">CellNex</h1>
                <p class="text-slate-400 text-[10px] font-black uppercase tracking-widest mt-2">Premium Mobile POS Portal</p>
            </div>

            <!-- Desktop welcome header -->
            <div class="hidden lg:block mb-8">
                <h2 class="text-2xl font-black text-white tracking-tight mb-1">Welcome Back</h2>
                <p class="text-slate-500 text-sm font-medium">Sign in to CellNex Portal</p>
            </div>

            <?php if ($error): ?>
            <div class="mb-5 bg-rose-500/10 border border-rose-500/20 p-3 rounded-xl text-rose-400 text-xs font-bold text-center">
                <i class="fa-solid fa-circle-exclamation mr-1.5"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Credentials Login Form -->
            <form method="POST" id="loginForm" class="space-y-5">
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Username</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="username" class="w-full py-3.5 pl-12 pr-4 bg-slate-900/60 border border-slate-800 focus:border-blue-500 text-white focus:bg-slate-900/80 rounded-2xl outline-none font-bold text-sm transition-all focus:ring-4 focus:ring-blue-500/10 placeholder-slate-600" placeholder="admin" required autofocus>
                    </div>
                </div>
                <div>
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2 ml-1">Password</label>
                    <div class="relative">
                        <i class="fa-solid fa-key absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password" class="w-full py-3.5 pl-12 pr-4 bg-slate-900/60 border border-slate-800 focus:border-blue-500 text-white focus:bg-slate-900/80 rounded-2xl outline-none font-bold text-sm transition-all focus:ring-4 focus:ring-blue-500/10 placeholder-slate-600" placeholder="••••••••" required>
                    </div>
                </div>
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white py-4 rounded-2xl font-black shadow-lg shadow-blue-500/15 transition-all transform active:scale-95 flex items-center justify-center gap-2 text-sm tracking-wider uppercase">
                    <span>Login</span><i class="fa-solid fa-arrow-right-to-bracket"></i>
                </button>
            </form>

            <div class="mt-8 text-center border-t border-slate-800/40 pt-6">
                <p class="text-[9px] text-slate-600 font-black uppercase tracking-widest">Powered by TechBrain</p>
            </div>
          </div><!-- glass card end -->
        </div>
    </div>
</body>
</html>