<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../components/Alert.php';
require_once __DIR__ . '/../components/Modal.php';
require_once __DIR__ . '/../components/DeleteConfirm.php';

requireLogin();

$user     = $auth->getCurrentUser();
$userRole = $user['role'] ?? 'admin';
$userName = $user['name'] ?? 'Admin User';
$currentPage = 'students/list.php';

// Initialize controller
$studentController = new StudentController($auth);

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if (!in_array($userRole, ['admin'])) {
        Alert::setFlash("Only administrators can delete students.", Alert::ERROR);
        header('Location: list.php');
        exit();
    }

    $id      = (int)$_GET['id'];
    $student = $studentController->getStudentById($id);

    if ($student) {
        if ($studentController->deleteStudent($id)) {
            Alert::setFlash(
                "Student " . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . " has been deleted successfully.",
                Alert::SUCCESS
            );
        } else {
            Alert::setFlash("Failed to delete student. Please try again.", Alert::ERROR);
        }
    } else {
        Alert::setFlash("Student not found.", Alert::ERROR);
    }

    header('Location: list.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    if (!in_array($userRole, ['admin', 'nurse'])) {
        Alert::setFlash("You don't have permission to update students.", Alert::ERROR);
        header('Location: list.php');
        exit();
    }

    $id          = (int)$_POST['id'];
    $studentData = [
        'student_number'  => trim($_POST['student_number']),
        'first_name'      => trim($_POST['first_name']),
        'last_name'       => trim($_POST['last_name']),
        'course'          => trim($_POST['course']),
        'year_level'      => trim($_POST['year_level']),
        'contact_number'  => trim($_POST['contact_number']),
    ];

    $errors = [];
    if (empty($studentData['first_name']))      $errors[] = "First name is required.";
    if (empty($studentData['last_name']))       $errors[] = "Last name is required.";
    if (empty($studentData['student_number']))  $errors[] = "Student number is required.";
    if (empty($studentData['course']))          $errors[] = "Course is required.";
    if (empty($studentData['year_level']))      $errors[] = "Year level is required.";
    if (empty($studentData['contact_number']))  $errors[] = "Contact number is required.";

    if (empty($errors)) {
        if ($studentController->updateStudent($id, $studentData)) {
            Alert::setFlash("Student information updated successfully!", Alert::SUCCESS);
        } else {
            Alert::setFlash("Failed to update student. Please try again.", Alert::ERROR);
        }
    } else {
        Alert::setFlash(implode(" ", $errors), Alert::ERROR);
    }

    header('Location: list.php');
    exit();
}

$students = $studentController->getAllStudents();
$sidebar  = new Sidebar($currentPage, $userRole, $userName);

$editFields = [
    [
        'type'        => 'text',
        'name'        => 'student_number',
        'label'       => 'Student Number',
        'required'    => true,
        'placeholder' => 'e.g., 2024-0001',
        'help'        => 'Unique identifier for the student',
    ],
    [
        'type'        => 'tel',
        'name'        => 'contact_number',
        'label'       => 'Contact Number',
        'required'    => true,
        'placeholder' => 'e.g., 09123456789',
    ],
    [
        'type'        => 'text',
        'name'        => 'first_name',
        'label'       => 'First Name',
        'required'    => true,
        'placeholder' => 'Enter first name',
    ],
    [
        'type'        => 'text',
        'name'        => 'last_name',
        'label'       => 'Last Name',
        'required'    => true,
        'placeholder' => 'Enter last name',
    ],
    [
        'type'     => 'select',
        'name'     => 'course',
        'label'    => 'Course',
        'required' => true,
        'options'  => [
            'Education'              => 'Education',
            'Information Technology' => 'Information Technology',
            'Criminology'                => 'Criminology',
            'Business Administration' => 'Business Administration',
            'Human Services' => 'Human Services',
        ],
    ],
    [
        'type'     => 'select',
        'name'     => 'year_level',
        'label'    => 'Year Level',
        'required' => true,
        'options'  => [
            '1' => 'Year 1',
            '2' => 'Year 2',
            '3' => 'Year 3',
            '4' => 'Year 4',
        ],
    ],
];

// Generate modals via reusable components
$editModal   = Modal::renderFormModal('editStudentModal', 'Edit Student', $editFields, '', 'lg');
$deleteModal = DeleteConfirm::render('deleteConfirmModal', 'student', '');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student List — School Clinic</title>

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Shared dashboard styles -->
    <link rel="stylesheet" href="../css/dashboard.css">

    <!-- Page-specific styles (extracted) -->
    <link rel="stylesheet" href="../css/students.css">
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

        <header class="topbar sticky top-0 z-20">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold">Student List</h1>
                    <p class="topbar-subtitle text-xs">Manage registered students</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="topbar-date hidden sm:block text-xs px-3 py-1.5 rounded-lg">
                        <?php echo date('D, M j Y'); ?>
                    </span>
                    <div class="hidden sm:flex items-center gap-2">
                        <div class="topbar-avatar w-8 h-8 rounded-full flex items-center justify-center text-xs font-semibold">
                            <?php echo strtoupper(substr($userName, 0, 2)); ?>
                        </div>
                        <div class="hidden md:block text-right">
                            <p class="topbar-user-name text-xs font-medium"><?php echo htmlspecialchars($userName); ?></p>
                            <p class="topbar-user-email text-xs"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="px-5 py-6 sm:px-6">

            <!-- Flash alert -->
            <?php echo Alert::displayFlash(); ?>

            <!-- Page header + Add button + Search -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-5">
                <div>
                    <h2 class="page-section-title">Student Directory</h2>
                    <p class="page-section-subtitle">View and manage student profiles</p>
                </div>
                <div class="mt-3 sm:mt-0 flex items-center gap-3 flex-wrap">
                    <!-- Search Bar -->
                    <div class="search-wrapper">
                        <svg class="search-icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                        <input
                            id="studentSearch"
                            type="text"
                            class="search-input"
                            placeholder="Search students…"
                            autocomplete="off" />
                        <button id="searchClear" class="search-clear" aria-label="Clear search">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <a href="add.php" class="add-student-btn">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Student
                    </a>
                </div>
            </div>

            <div class="simple-card overflow-hidden border border-gray-100">

                <!-- Card header -->
                <div class="simple-header px-5 py-3 bg-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4" style="color:#2d8a6e;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="section-label">Student Records</span>
                    </div>
                    <!-- Live count badge -->
                    <span class="student-count-badge">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20H7M17 20v-2a4 4 0 00-4-4H7a4 4 0 00-4 4v2" />
                        </svg>
                        <?php echo count($students); ?> enrolled
                    </span>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="student-table w-full text-left simple-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student No.</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Course</th>
                                <th>Year</th>
                                <th>Contact</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($students)): ?>
                                <tr>
                                    <td colspan="8" class="px-5 py-14 text-center">
                                        <div class="empty-message max-w-sm mx-auto flex flex-col items-center">
                                            <div class="empty-state-icon w-14 h-14 rounded-full flex items-center justify-center mb-3"
                                                style="background: linear-gradient(135deg,#eef7f3,#d9f0e7);">
                                                <svg class="w-7 h-7" style="color:#6db89a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-semibold" style="color:#2c4b3e;">No students registered yet</p>
                                            <p class="text-xs mt-1" style="color:#8bae9d;">Student list will appear here once records are added.</p>
                                            <a href="add.php" class="add-student-btn mt-4"
                                                style="background:#2d8a6e; color:white; border-color:#2d8a6e;">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Add your first student
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($students as $i => $student): ?>
                                    <tr>
                                        <td class="text-xs" style="color:#8bae9d; font-weight:500;">
                                            <?php echo $i + 1; ?>
                                        </td>
                                        <td>
                                            <span class="font-mono-student">
                                                <?php echo htmlspecialchars($student['student_number']); ?>
                                            </span>
                                        </td>
                                        <td class="font-medium"><?php echo htmlspecialchars($student['first_name']); ?></td>
                                        <td class="font-medium"><?php echo htmlspecialchars($student['last_name']); ?></td>
                                        <td style="color:#456b5b;"><?php echo htmlspecialchars($student['course']); ?></td>
                                        <td>
                                            <span style="background:#f0f7f3; color:#2d6b52; border-radius:9999px;
                                                         padding:0.15rem 0.5rem; font-size:0.7rem; font-weight:600;">
                                                Yr <?php echo htmlspecialchars($student['year_level']); ?>
                                            </span>
                                        </td>
                                        <td style="color:#627a6e;"><?php echo htmlspecialchars($student['contact_number']); ?></td>
                                        <td class="text-center">
                                            <!-- Edit -->
                                            <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($student)); ?>)"
                                                class="action-btn edit-btn">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Edit
                                            </button>
                                            <!-- Delete (admin only) -->
                                            <?php if ($userRole === 'admin'): ?>
                                                <button onclick="confirmDeleteStudent(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>')"
                                                    class="action-btn delete-btn">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Delete
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Card footer -->
                <div class="simple-footer px-5 py-2.5 flex justify-between items-center">
                    <p class="text-xs" style="color:#8fae9f;">
                        Showing <strong><?php echo count($students); ?></strong> student<?php echo count($students) !== 1 ? 's' : ''; ?>
                    </p>
                    <?php if (!empty($students)): ?>
                        <p class="text-xs" style="color:#b0c8bc;">
                            Last updated: <?php echo date('M j, Y · g:i A'); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

        </main>
    </div>

    <!-- Modals -->
    <?php echo $editModal; ?>
    <?php echo $deleteModal; ?>

    <!-- Scripts -->
    <script src="../js/sidebar.js"></script>
    <script src="../js/modal.js"></script>
    <?php echo DeleteConfirm::getScript(); ?>
    <script src="../js/students.js"></script>

</body>

</html>