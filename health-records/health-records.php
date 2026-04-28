<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../components/HealthRecordModal.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/HealthRecordController.php';
require_once __DIR__ . '/../components/Alert.php';
require_once __DIR__ . '/../components/DeleteConfirm.php';

requireLogin();

$user        = $auth->getCurrentUser();
$userRole    = $user['role'] ?? 'admin';
$userName    = $user['name'] ?? 'Admin User';
$currentPage = 'Health Records';

$studentController      = new StudentController($auth);
$healthRecordController = new HealthRecordController($auth);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['_action'] ?? 'add';

    if ($action === 'edit' && in_array($userRole, ['admin', 'nurse'])) {
        $recordId       = (int) $_POST['record_id'];
        $studentId      = (int) $_POST['student_id'];
        $allergies      = trim($_POST['allergies']);
        $medicalHistory = trim($_POST['medical_history']);

        if ($healthRecordController->updateHealthRecord($recordId, $allergies, $medicalHistory)) {
            $student     = $studentController->getStudentById($studentId);
            $studentName = $student['first_name'] . ' ' . $student['last_name'];
            Alert::setFlash('Health record for ' . htmlspecialchars($studentName) . ' updated successfully!', Alert::SUCCESS);
        } else {
            Alert::setFlash('Failed to update health record. Please try again.', Alert::ERROR);
        }

        header('Location: health-records.php?saved=1');
        exit();
    }

    $studentId      = (int) $_POST['student_id'];
    $allergies      = trim($_POST['allergies']);
    $medicalHistory = trim($_POST['medical_history']);

    if ($studentId > 0) {
        if ($healthRecordController->saveHealthRecord($studentId, $allergies, $medicalHistory)) {
            $student     = $studentController->getStudentById($studentId);
            $studentName = $student['first_name'] . ' ' . $student['last_name'];
            Alert::setFlash('Health record for ' . htmlspecialchars($studentName) . ' saved successfully!', Alert::SUCCESS);
        } else {
            Alert::setFlash('Failed to save health record. Please try again.', Alert::ERROR);
        }
    } else {
        Alert::setFlash('Please select a student.', Alert::ERROR);
    }

    header('Location: health-records.php?saved=1');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($userRole === 'admin') {
        if ($healthRecordController->deleteHealthRecord((int) $_GET['id'])) {
            Alert::setFlash('Health record deleted successfully!', Alert::SUCCESS);
        } else {
            Alert::setFlash('Failed to delete health record.', Alert::ERROR);
        }
    } else {
        Alert::setFlash("You don't have permission to delete health records.", Alert::ERROR);
    }

    header('Location: health-records.php');
    exit();
}

$healthRecords          = $healthRecordController->getAllHealthRecords();
$studentsWithoutRecords = $healthRecordController->getStudentsWithoutHealthRecords();
$allStudents            = $studentController->getAllStudents();

$sidebar   = new Sidebar($currentPage, $userRole, $userName);
$addModal  = HealthRecordModal::renderAddModal('addHealthRecordModal', $studentsWithoutRecords, 'lg');
$editModal = HealthRecordModal::renderEditModal('editHealthRecordModal', 'lg');

$canAdd = !empty($studentsWithoutRecords) && in_array($userRole, ['admin', 'nurse']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Records — School Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/health-record.css">
</head>

<body>

    <!-- Mobile overlay -->
    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"></div>

    <!-- Mobile menu button -->
    <button id="mobileMenuButton" class="mobile-menu-btn lg:hidden fixed top-3.5 left-4 z-50 p-2 rounded-lg shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <!-- Sidebar -->
    <?php echo $sidebar->render(); ?>

    <!-- Main content -->
    <div id="mainContent" class="lg:ml-56 transition-all duration-300">

        <!-- Top bar -->
        <header class="topbar sticky top-0 z-20">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold">Health Records</h1>
                    <p class="topbar-subtitle text-xs">Manage student medical histories</p>
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

            <?php echo Alert::displayFlash(); ?>

            <!-- Page header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
                <div>
                    <h2 class="hr-page-title">Student Health Records</h2>
                    <p class="hr-page-subtitle">View and manage medical information</p>
                </div>
                <div class="mt-3 sm:mt-0 flex items-center gap-3 flex-wrap">

                    <!-- Search bar -->
                    <div class="search-wrapper">
                        <svg class="search-icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                        <input id="healthRecordSearch" type="text" class="search-input"
                            placeholder="Search records…" autocomplete="off" />
                        <button id="searchClear" class="search-clear" aria-label="Clear search">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <?php if ($canAdd): ?>
                        <button onclick="openModal('addHealthRecordModal')" class="hr-add-btn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Add Health Record
                        </button>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Stat cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

                <div class="hr-stat-card fade-in d1">
                    <div class="flex items-center justify-between mb-3">
                        <div class="hr-stat-icon hr-stat-icon--teal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="hr-stat-label">Total Students</p>
                    <p class="hr-stat-value hr-stat-value--primary"><?php echo count($allStudents); ?></p>
                </div>

                <div class="hr-stat-card fade-in d2">
                    <div class="flex items-center justify-between mb-3">
                        <div class="hr-stat-icon hr-stat-icon--green">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                    </div>
                    <p class="hr-stat-label">With Health Records</p>
                    <p class="hr-stat-value hr-stat-value--green"><?php echo count($healthRecords); ?></p>
                </div>

                <div class="hr-stat-card fade-in d3">
                    <div class="flex items-center justify-between mb-3">
                        <div class="hr-stat-icon hr-stat-icon--red">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="hr-stat-label">Missing Records</p>
                    <p class="hr-stat-value hr-stat-value--red"><?php echo count($studentsWithoutRecords); ?></p>
                </div>

            </div>

            <!-- Health Records Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5" id="recordsGrid">

                <?php if (empty($healthRecords)): ?>
                    <div class="hr-empty-state fade-in d4">
                        <svg class="hr-empty-icon w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="hr-empty-title">No health records yet</p>
                        <p class="hr-empty-subtitle">Click "Add Health Record" above to get started</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($healthRecords as $index => $record): ?>
                        <div class="health-record-card fade-in"
                            style="animation-delay: <?php echo 0.05 + ($index * 0.04); ?>s;"
                            onclick="openViewModal(<?php echo htmlspecialchars(json_encode($record), ENT_QUOTES); ?>)">

                            <!-- Card header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3 min-w-0 flex-1 pr-2">
                                    <div class="hr-card-avatar">
                                        <?php echo strtoupper(
                                            substr($record['first_name'], 0, 1) .
                                                substr($record['last_name'],  0, 1)
                                        ); ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="hr-card-name">
                                            <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                                        </h3>
                                        <p class="hr-card-meta">
                                            <?php echo htmlspecialchars($record['student_number']); ?> &bull;
                                            <?php echo htmlspecialchars($record['course']); ?> &bull;
                                            Year <?php echo htmlspecialchars($record['year_level']); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Card action buttons -->
                                <div class="flex items-center gap-1.5">
                                    <?php if (in_array($userRole, ['admin', 'nurse'])): ?>
                                        <button
                                            onclick="event.stopPropagation(); openEditModal(<?php echo htmlspecialchars(json_encode($record), ENT_QUOTES); ?>)"
                                            class="hr-edit-btn"
                                            title="Edit record">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($userRole === 'admin'): ?>
                                        <button
                                            onclick="event.stopPropagation(); deleteRecord(<?php echo $record['id']; ?>, '<?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name'], ENT_QUOTES); ?>')"
                                            class="hr-delete-btn"
                                            title="Delete record">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>

                            </div>

                            <!-- Fields -->
                            <div class="space-y-3">
                                <div>
                                    <p class="hr-field-label">Allergies</p>
                                    <p class="hr-field-value <?php echo empty($record['allergies']) ? 'hr-field-value--empty' : ''; ?>">
                                        <?php if (!empty($record['allergies'])): ?>
                                            <?php echo htmlspecialchars(substr($record['allergies'], 0, 100)) . (strlen($record['allergies']) > 100 ? '…' : ''); ?>
                                        <?php else: ?>
                                            None reported
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="hr-field-label">Medical History</p>
                                    <p class="hr-field-value <?php echo empty($record['medical_history']) ? 'hr-field-value--empty' : ''; ?>">
                                        <?php if (!empty($record['medical_history'])): ?>
                                            <?php echo htmlspecialchars(substr($record['medical_history'], 0, 100)) . (strlen($record['medical_history']) > 100 ? '…' : ''); ?>
                                        <?php else: ?>
                                            No medical history recorded
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Card footer -->
                            <div class="hr-card-footer mt-4">
                                <p class="hr-card-date">
                                    Created <?php echo date('M j, Y', strtotime($record['created_at'])); ?>
                                </p>
                                <span class="hr-card-view-hint">View full record →</span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>

        </main>
    </div>

    <!-- Add modal -->
    <?php echo $addModal; ?>

    <!-- Edit modal -->
    <?php echo $editModal; ?>

    <!-- View modal (populated dynamically via openViewModal()) -->
    <div id="viewHealthRecordModal"
        class="hr-modal-backdrop"
        style="display:none;"
        role="dialog"
        aria-modal="true"
        aria-labelledby="viewHealthRecordModal-title">

        <div class="hr-modal max-w-2xl animate-modal">

            <div class="hr-modal-header">
                <div class="hr-modal-header-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="hr-modal-title" id="viewHealthRecordModal-title">Health Record</h3>
                    <p class="hr-modal-subtitle" id="view-subtitle"></p>
                </div>
                <button onclick="closeModal('viewHealthRecordModal')" class="hr-modal-close" aria-label="Close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="hr-modal-divider"></div>

            <div class="hr-modal-body">

                <div class="hr-identity-strip">
                    <div class="hr-identity-avatar" id="view-avatar"></div>
                    <div class="min-w-0">
                        <p class="hr-identity-name" id="view-name"></p>
                        <div class="hr-identity-meta" id="view-meta"></div>
                    </div>
                </div>

                <div class="hr-view-section">
                    <div class="hr-view-section-header">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Allergies
                    </div>
                    <div class="hr-view-content" id="view-allergies"></div>
                </div>

                <div class="hr-view-section">
                    <div class="hr-view-section-header">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Medical History
                    </div>
                    <div class="hr-view-content" id="view-history"></div>
                </div>

                <p class="hr-view-timestamp" id="view-timestamp"></p>

            </div>

            <div class="hr-modal-divider"></div>

            <div class="hr-modal-footer">
                <button onclick="closeModal('viewHealthRecordModal')" class="hr-btn hr-btn-ghost">Close</button>
            </div>

        </div>
    </div>

    <!-- Modal styles + scripts -->
    <?php echo HealthRecordModal::getModalStyles(); ?>
    <?php echo HealthRecordModal::getModalScript(); ?>
    <!-- Delete confirm modal -->
    <?php echo DeleteConfirm::render('deleteConfirmModal', 'health record'); ?>

    <script src="../js/sidebar.js"></script>
    <script src="../js/health-record.js"></script>
    <?php echo DeleteConfirm::getScript(); ?>
    <script src="../js/modal.js"></script>
</body>

</html>