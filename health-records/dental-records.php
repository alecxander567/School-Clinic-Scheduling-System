<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../controllers/DentalRecordController.php';
require_once __DIR__ . '/../components/DentalRecordModal.php';
require_once __DIR__ . '/../components/Alert.php';
require_once __DIR__ . '/../components/DeleteConfirm.php';

requireLogin();

$user        = $auth->getCurrentUser();
$userRole    = $user['role'] ?? 'admin';
$userName    = $user['name'] ?? 'Admin User';
$currentPage = 'health-records/dental-records.php';

global $pdo;
$dentalRecordController = new DentalRecordController($pdo);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['_action']) ? $_POST['_action'] : 'add';

    if ($action === 'edit' && in_array($userRole, ['admin', 'nurse'])) {
        $visitId = isset($_POST['visit_id']) ? (int) $_POST['visit_id'] : 0;

        if ($visitId > 0) {
            $data = [
                'visit_date'  => isset($_POST['visit_date']) ? $_POST['visit_date'] : date('Y-m-d H:i:s'),
                'diagnosis'   => isset($_POST['diagnosis'])  ? $_POST['diagnosis']  : null,
                'treatment'   => isset($_POST['treatment'])  ? $_POST['treatment']  : null,
                'procedures'  => isset($_POST['procedures']) ? $_POST['procedures'] : [],
            ];

            if ($dentalRecordController->updateDentalRecord($visitId, $data)) {
                Alert::setFlash('Dental record updated successfully!', Alert::SUCCESS);
            } else {
                Alert::setFlash('Failed to update dental record.', Alert::ERROR);
            }
        } else {
            Alert::setFlash('Invalid record ID.', Alert::ERROR);
        }

        header('Location: dental-records.php?saved=1');
        exit();
    }

    if ($action === 'add' && in_array($userRole, ['admin', 'nurse'])) {
        $studentId = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;

        if ($studentId > 0) {
            $data = [
                'student_id'  => $studentId,
                'visit_date'  => isset($_POST['visit_date']) ? $_POST['visit_date'] : date('Y-m-d H:i:s'),
                'diagnosis'   => isset($_POST['diagnosis'])  ? $_POST['diagnosis']  : null,
                'treatment'   => isset($_POST['treatment'])  ? $_POST['treatment']  : null,
                'procedures'  => isset($_POST['procedures']) ? $_POST['procedures'] : [],
            ];

            if ($dentalRecordController->saveDentalRecord($data)) {
                Alert::setFlash('Dental record saved successfully!', Alert::SUCCESS);
            } else {
                Alert::setFlash('Failed to save dental record.', Alert::ERROR);
            }
        } else {
            Alert::setFlash('Please select a student.', Alert::ERROR);
        }

        header('Location: dental-records.php?saved=1');
        exit();
    }
}

// Handle delete
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    if ($userRole === 'admin') {
        if ($dentalRecordController->deleteDentalRecord((int) $_GET['id'])) {
            Alert::setFlash('Dental record deleted successfully!', Alert::SUCCESS);
        } else {
            Alert::setFlash('Failed to delete dental record.', Alert::ERROR);
        }
    } else {
        Alert::setFlash("You don't have permission to delete dental records.", Alert::ERROR);
    }

    header('Location: dental-records.php');
    exit();
}

// Get data for display
$dentalRecords          = $dentalRecordController->getAllDentalRecords();
$studentsWithoutRecords = $dentalRecordController->getStudentsWithoutDentalRecords();
$allStudents            = $dentalRecordController->getAllStudents();
$stats                  = $dentalRecordController->getDentalRecordStats();
$commonProcedures       = $dentalRecordController->getCommonProcedures();

$sidebar   = new Sidebar($currentPage, $userRole, $userName);
$addModal  = DentalRecordModal::renderAddModal('addDentalRecordModal', $studentsWithoutRecords, $commonProcedures, 'lg');
$editModal = DentalRecordModal::renderEditModal('editDentalRecordModal', $commonProcedures, 'lg');

$canAdd = !empty($studentsWithoutRecords) && in_array($userRole, ['admin', 'nurse']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dental Records — School Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dental-records.css">
    <?php echo DentalRecordModal::getModalStyles(); ?>
</head>

<body>

    <div id="mobileOverlay" class="fixed inset-0 bg-black bg-opacity-40 z-30 hidden lg:hidden"></div>

    <button id="mobileMenuButton" class="mobile-menu-btn lg:hidden fixed top-3.5 left-4 z-50 p-2 rounded-lg shadow-md">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
    </button>

    <?php echo $sidebar->render(); ?>

    <div id="mainContent" class="lg:ml-56 transition-all duration-300">

        <header class="topbar sticky top-0 z-20">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold">Dental Records</h1>
                    <p class="topbar-subtitle text-xs">Manage student dental visits and procedures</p>
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
                    <h2 class="dental-page-title">Student Dental Records</h2>
                    <p class="dental-page-subtitle">View and manage dental examination history</p>
                </div>
                <div class="mt-3 sm:mt-0 flex items-center gap-3 flex-wrap">

                    <!-- Search bar -->
                    <div class="search-wrapper">
                        <svg class="search-icon w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                        <input id="dentalRecordSearch" type="text" class="search-input"
                            placeholder="Search records…" autocomplete="off" />
                        <button id="searchClear" class="search-clear" aria-label="Clear search">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <?php if ($canAdd): ?>
                        <button onclick="openModal('addDentalRecordModal')" class="dental-add-btn">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Dental Record
                        </button>
                    <?php endif; ?>

                </div>
            </div>

            <!-- Stats cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">

                <div class="dental-stat-card fade-in d1">
                    <div class="flex items-center justify-between mb-3">
                        <div class="dental-stat-icon dental-stat-icon--teal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.5a7.5 7.5 0 00-7.5 7.5c0 3.5 2.5 6.5 6 7.2V21h3v-1.8c3.5-0.7 6-3.7 6-7.2 0-4.15-3.35-7.5-7.5-7.5z" />
                            </svg>
                        </div>
                    </div>
                    <p class="dental-stat-label">Total Dental Visits</p>
                    <p class="dental-stat-value dental-stat-value--teal"><?php echo $stats['total_visits']; ?></p>
                </div>

                <div class="dental-stat-card fade-in d2">
                    <div class="flex items-center justify-between mb-3">
                        <div class="dental-stat-icon dental-stat-icon--blue">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <p class="dental-stat-label">Students with Records</p>
                    <p class="dental-stat-value dental-stat-value--blue"><?php echo $stats['unique_students']; ?></p>
                </div>

                <div class="dental-stat-card fade-in d3">
                    <div class="flex items-center justify-between mb-3">
                        <div class="dental-stat-icon dental-stat-icon--yellow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                    <p class="dental-stat-label">Total Procedures Done</p>
                    <p class="dental-stat-value dental-stat-value--yellow"><?php echo $stats['total_procedures']; ?></p>
                </div>

            </div>

            <!-- Dental Records Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5" id="recordsGrid">

                <?php if (empty($dentalRecords)): ?>
                    <div class="dental-empty-state fade-in d4">
                        <svg class="dental-empty-icon w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 4.5a7.5 7.5 0 00-7.5 7.5c0 3.5 2.5 6.5 6 7.2V21h3v-1.8c3.5-0.7 6-3.7 6-7.2 0-4.15-3.35-7.5-7.5-7.5z" />
                        </svg>
                        <p class="dental-empty-title">No dental records yet</p>
                        <p class="dental-empty-subtitle">Click "Add Dental Record" above to get started</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($dentalRecords as $index => $record): ?>
                        <div class="dental-record-card fade-in"
                            style="animation-delay: <?php echo 0.05 + ($index * 0.04); ?>s;"
                            onclick="openViewModal(<?php echo htmlspecialchars(json_encode($record), ENT_QUOTES); ?>)">

                            <!-- Card header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3 min-w-0 flex-1 pr-2">
                                    <div class="dental-card-avatar">
                                        <?php echo strtoupper(substr($record['first_name'], 0, 1) . substr($record['last_name'], 0, 1)); ?>
                                    </div>
                                    <div class="min-w-0">
                                        <h3 class="dental-card-name">
                                            <?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name']); ?>
                                        </h3>
                                        <p class="dental-card-meta">
                                            <?php echo htmlspecialchars($record['student_number']); ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Action buttons -->
                                <div class="flex items-center gap-1.5">
                                    <?php if (in_array($userRole, ['admin', 'nurse'])): ?>
                                        <button
                                            onclick="event.stopPropagation(); openEditModal(<?php echo htmlspecialchars(json_encode($record), ENT_QUOTES); ?>)"
                                            class="dental-edit-btn"
                                            title="Edit record">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($userRole === 'admin'): ?>
                                        <button
                                            onclick="event.stopPropagation(); deleteRecord(<?php echo $record['visit_id']; ?>, '<?php echo htmlspecialchars($record['first_name'] . ' ' . $record['last_name'], ENT_QUOTES); ?>')"
                                            class="dental-delete-btn"
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
                                    <p class="dental-field-label">Visit Date</p>
                                    <p class="dental-field-value"><?php echo date('M j, Y', strtotime($record['visit_date'])); ?></p>
                                </div>
                                <div>
                                    <p class="dental-field-label">Diagnosis</p>
                                    <p class="dental-field-value <?php echo empty($record['diagnosis']) ? 'dental-field-value--empty' : ''; ?>">
                                        <?php if (!empty($record['diagnosis'])): ?>
                                            <?php echo htmlspecialchars(substr($record['diagnosis'], 0, 100)) . (strlen($record['diagnosis']) > 100 ? '…' : ''); ?>
                                        <?php else: ?>
                                            None recorded
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="dental-field-label">Procedures</p>
                                    <p class="dental-field-value <?php echo empty($record['procedures']) ? 'dental-field-value--empty' : ''; ?>">
                                        <?php if (!empty($record['procedures'])): ?>
                                            <?php echo htmlspecialchars(substr($record['procedures'], 0, 100)) . (strlen($record['procedures']) > 100 ? '…' : ''); ?>
                                        <?php else: ?>
                                            None recorded
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Card footer -->
                            <div class="dental-card-footer mt-4">
                                <p class="dental-card-date">
                                    Visit on <?php echo date('M j, Y', strtotime($record['visit_date'])); ?>
                                </p>
                                <span class="dental-card-view-hint">View full record →</span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

            </div>
        </main>
    </div>

    <!-- Modals -->
    <?php
    echo $addModal;
    echo $editModal;
    ?>

    <!-- View Modal -->
    <div id="viewDentalRecordModal" class="hr-modal-backdrop" style="display:none;" role="dialog" aria-modal="true">
        <div class="hr-modal max-w-2xl animate-modal">
            <div class="hr-modal-header">
                <div class="hr-modal-header-icon">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.5a7.5 7.5 0 00-7.5 7.5c0 3.5 2.5 6.5 6 7.2V21h3v-1.8c3.5-0.7 6-3.7 6-7.2 0-4.15-3.35-7.5-7.5-7.5z" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <h3 class="hr-modal-title">Dental Record Details</h3>
                    <p class="hr-modal-subtitle">Complete dental examination information</p>
                </div>
                <button onclick="closeModal('viewDentalRecordModal')" class="hr-modal-close">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="hr-modal-divider"></div>

            <div class="hr-modal-body">
                <!-- Identity strip -->
                <div class="bg-gray-50 rounded-lg p-4 mb-4 flex items-center gap-3">
                    <div class="dental-card-avatar text-lg w-12 h-12" id="view-avatar"></div>
                    <div>
                        <p class="font-semibold text-gray-800 text-base" id="view-name"></p>
                        <p class="text-xs text-gray-500" id="view-meta"></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <p class="dental-field-label">Visit Date</p>
                        <p class="dental-field-value" id="view-visit-date"></p>
                    </div>
                    <div>
                        <p class="dental-field-label">Diagnosis</p>
                        <p class="dental-field-value bg-gray-50 p-3 rounded-lg" id="view-diagnosis"></p>
                    </div>
                    <div>
                        <p class="dental-field-label">Treatment</p>
                        <p class="dental-field-value bg-gray-50 p-3 rounded-lg" id="view-treatment"></p>
                    </div>
                    <div>
                        <p class="dental-field-label">Additional Procedures</p>
                        <p class="dental-field-value bg-gray-50 p-3 rounded-lg" id="view-procedures"></p>
                    </div>
                </div>
            </div>

            <div class="hr-modal-divider"></div>

            <div class="hr-modal-footer">
                <button onclick="closeModal('viewDentalRecordModal')" class="hr-btn hr-btn-ghost">Close</button>
            </div>
        </div>
    </div>

    <?php
    echo DentalRecordModal::getModalScript();
    echo DeleteConfirm::render('deleteConfirmModal', 'dental record');
    echo DeleteConfirm::getScript();
    ?>

    <script src="../js/dental-records.js"></script>

    <!-- Search functionality -->
    <script>
        (function() {
            const searchInput = document.getElementById('dentalRecordSearch');
            const searchClear = document.getElementById('searchClear');
            const grid = document.getElementById('recordsGrid');

            if (!searchInput || !grid) return;

            function filterCards(query) {
                const q = query.toLowerCase().trim();
                const cards = grid.querySelectorAll('.dental-record-card');
                let visible = 0;

                cards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    const show = !q || text.includes(q);
                    card.style.display = show ? '' : 'none';
                    if (show) visible++;
                });

                // Remove any existing no-results message
                const existing = grid.querySelector('.dental-no-results');
                if (existing) existing.remove();

                if (visible === 0 && q) {
                    const msg = document.createElement('div');
                    msg.className = 'dental-no-results';
                    msg.innerHTML = `<p style="font-size:.85rem;color:#8bae9d;">No records match "<strong>${query}</strong>"</p>`;
                    grid.appendChild(msg);
                }
            }

            searchInput.addEventListener('input', function() {
                const q = this.value;
                searchClear.classList.toggle('visible', q.length > 0);
                filterCards(q);
            });

            searchClear.addEventListener('click', function() {
                searchInput.value = '';
                this.classList.remove('visible');
                filterCards('');
                searchInput.focus();
            });
        })();
    </script>

</body>

</html>