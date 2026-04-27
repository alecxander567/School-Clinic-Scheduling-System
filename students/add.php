<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../components/Alert.php';

requireLogin();

// Initialize controller
$studentController = new StudentController($auth);

// Check permission
if (!$studentController->hasAddPermission()) {
    Alert::setFlash("You don't have permission to add students.", Alert::ERROR);
    header('Location: ../dashboard.php');
    exit();
}

// Process form submission
$result = $studentController->addStudent($_POST);

// Get user data for the view
$userData = $studentController->getUserData();
$error    = $result['error'];
$success  = $result['success'];
$formData = $result['formData'];
$user     = $userData['user'];
$userRole = $userData['userRole'];
$userName = $userData['userName'];

$currentPage = 'students/add.php';
$sidebar     = new Sidebar($currentPage, $userRole, $userName);

$courses = [
    'Education',
    'Information Technology',
    'Criminology',
    'Business Administration',
    'Human Services'
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Student — School Clinic</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Shared dashboard styles -->
    <link rel="stylesheet" href="../css/dashboard.css">

    <!-- Page-specific styles (extracted) -->
    <link rel="stylesheet" href="../css/add-student.css">
</head>

<body>

    <!-- Mobile overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"></div>

    <!-- Mobile menu button -->
    <button id="mobileMenuButton"
        class="mobile-menu-btn lg:hidden fixed top-3.5 left-4 z-50 p-2 rounded-lg shadow-md"
        style="background:#1a2e25; color:white;">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Sidebar -->
    <?php echo $sidebar->render(); ?>

    <!-- Main content -->
    <div id="mainContent" class="lg:ml-56 transition-all duration-300">

        <header class="topbar sticky top-0 z-20" style="background:white; border-bottom:1px solid #edf2f0;">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold" style="color:#1a2e25;">Add New Student</h1>
                    <p class="topbar-subtitle text-xs" style="color:#627a6e;">Register a student to the clinic system</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="topbar-date hidden sm:block text-xs px-3 py-1.5 rounded-lg"
                        style="background:#f4f8f5; color:#2c5a48;">
                        <?php echo date('D, M j Y'); ?>
                    </span>
                    <div class="hidden sm:flex items-center gap-2">
                        <div class="topbar-avatar w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold"
                            style="background:#2d8a6e; color:white;">
                            <?php echo strtoupper(substr($userName, 0, 2)); ?>
                        </div>
                        <div class="hidden md:block text-right">
                            <p class="topbar-user-name text-xs font-medium" style="color:#1a2e25;"><?php echo htmlspecialchars($userName); ?></p>
                            <p class="topbar-user-email text-xs" style="color:#7a9b8a;"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-5 py-6 sm:px-6">
            <div class="form-container">

                <!-- Dynamic alert container -->
                <div id="alert-container"></div>

                <!-- PHP alerts -->
                <?php if ($success): ?>
                    <?php echo Alert::success($success, 'list.php', 2); ?>
                <?php endif; ?>
                <?php if ($error): ?>
                    <?php echo Alert::error($error); ?>
                <?php endif; ?>

                <div class="form-card">

                    <!-- Card header -->
                    <div class="form-card-header">
                        <div class="form-card-header-icon">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <span class="form-card-header-title">Student Information</span>
                    </div>

                    <!-- Form body -->
                    <div class="p-6">
                        <form method="POST" action="" id="studentForm">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">

                                <!-- Section: Identity -->
                                <p class="form-section-label">Identification</p>

                                <!-- Student Number -->
                                <div class="field-group">
                                    <label class="form-label">
                                        Student Number<span class="required-star">*</span>
                                    </label>
                                    <input type="text"
                                        name="student_number"
                                        class="form-input"
                                        value="<?php echo htmlspecialchars($formData['student_number']); ?>"
                                        placeholder="e.g., 2024-0001"
                                        required>
                                    <span class="form-help">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Unique identifier for the student
                                    </span>
                                </div>

                                <!-- Contact Number -->
                                <div class="field-group">
                                    <label class="form-label">
                                        Contact Number<span class="required-star">*</span>
                                    </label>
                                    <input type="tel"
                                        name="contact_number"
                                        class="form-input"
                                        value="<?php echo htmlspecialchars($formData['contact_number']); ?>"
                                        placeholder="e.g., 09123456789"
                                        required>
                                    <span class="form-help">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        Active mobile number
                                    </span>
                                </div>

                                <!-- Section: Personal Details -->
                                <p class="form-section-label">Personal Details</p>

                                <!-- First Name -->
                                <div class="field-group">
                                    <label class="form-label">
                                        First Name<span class="required-star">*</span>
                                    </label>
                                    <input type="text"
                                        name="first_name"
                                        class="form-input"
                                        value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                                        placeholder="Enter first name"
                                        required>
                                </div>

                                <!-- Last Name -->
                                <div class="field-group">
                                    <label class="form-label">
                                        Last Name<span class="required-star">*</span>
                                    </label>
                                    <input type="text"
                                        name="last_name"
                                        class="form-input"
                                        value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                                        placeholder="Enter last name"
                                        required>
                                </div>

                                <!-- Section: Academic -->
                                <p class="form-section-label">Academic</p>

                                <!-- Course -->
                                <div class="field-group">
                                    <label class="form-label">
                                        Course<span class="required-star">*</span>
                                    </label>
                                    <select name="course" class="form-input" required>
                                        <option value="">Select Course</option>
                                        <?php foreach ($courses as $courseOption): ?>
                                            <option value="<?php echo htmlspecialchars($courseOption); ?>"
                                                <?php echo ($formData['course'] === $courseOption) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($courseOption); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Year Level -->
                                <div class="field-group">
                                    <label class="form-label">
                                        Year Level<span class="required-star">*</span>
                                    </label>
                                    <select name="year_level" class="form-input" required>
                                        <option value="">Select Year Level</option>
                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                            <option value="<?php echo $i; ?>"
                                                <?php echo ((int)$formData['year_level'] === $i) ? 'selected' : ''; ?>>
                                                Year <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                            </div>

                            <div class="form-actions">
                                <p class="form-footer-hint">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Fields marked <span class="required-star" style="margin:0 0.15rem;">*</span> are required
                                </p>
                                <div class="form-actions-right">
                                    <a href="list.php" class="btn-secondary">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                        </svg>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn-primary" id="submitBtn">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Add Student
                                    </button>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="../js/sidebar.js"></script>
    <script src="../js/alerts.js"></script>
    <script src="../js/add-student.js"></script>

</body>

</html>