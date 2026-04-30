<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../components/Sidebar.php';
require_once __DIR__ . '/../components/AppointmentModal.php';
require_once __DIR__ . '/../controllers/AppointmentController.php';
require_once __DIR__ . '/../components/DeleteConfirm.php';

requireLogin();

$user          = $auth->getCurrentUser();
$userRole      = $user['role'] ?? 'admin';
$userName      = $user['name'] ?? 'Admin User';
$currentPage   = 'View Appointments';
$currentUserId = $_SESSION['user_id'] ?? 1;

global $pdo;
$appointmentController = new AppointmentController($pdo);

$filterDate   = $_GET['date']   ?? date('Y-m-d');
$filterStatus = $_GET['status'] ?? '';

$appointments = $filterStatus
    ? $appointmentController->getAppointmentsByStatus($filterStatus)
    : $appointmentController->getAppointmentsByDate($filterDate);

$statuses = $appointmentController->getStatuses();
$sidebar  = new Sidebar($currentPage, $userRole, $userName);

function aptBadgeClass(string $statusName): string
{
    $s = strtolower($statusName);
    if (str_contains($s, 'progress'))                             return 'apt-badge--progress';
    if (str_contains($s, 'cancel'))                               return 'apt-badge--cancelled';
    if (str_contains($s, 'complet') || str_contains($s, 'done')) return 'apt-badge--completed';
    if (str_contains($s, 'confirm'))                              return 'apt-badge--confirmed';
    return 'apt-badge--scheduled';
}

function aptDotClass(string $statusName): string
{
    $s = strtolower($statusName);
    if (str_contains($s, 'cancel'))                               return 'apt-dot--cancel';
    if (str_contains($s, 'complet') || str_contains($s, 'done')) return 'apt-dot--done';
    return 'apt-dot--active';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments — School Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/appointments.css">
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>

    <style>
        @keyframes aptSlideDown {
            from {
                opacity: 0;
                transform: translateY(-40px) scale(0.98);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .apt-modal-animate {
            animation: aptSlideDown 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>
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

        <!-- Top bar -->
        <header class="topbar sticky top-0 z-20">
            <div class="pl-14 pr-5 lg:px-6 py-3 flex items-center justify-between">
                <div>
                    <h1 class="topbar-title text-lg font-semibold">Appointments</h1>
                    <p class="topbar-subtitle text-xs">Manage and view all scheduled provider visits</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="topbar-date hidden sm:block text-xs px-3 py-1.5 rounded-lg">
                        <?php echo date('D, M j Y'); ?>
                    </span>
                    <button class="topbar-notif-btn relative w-8 h-8 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="topbar-notif-dot absolute top-1 right-1 w-1.5 h-1.5 rounded-full"></span>
                    </button>
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

            <!-- Filter bar -->
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="filterDate">Date</label>
                    <input type="date" id="filterDate" value="<?php echo htmlspecialchars($filterDate); ?>">
                </div>
                <div class="filter-group">
                    <label for="filterStatus">Status</label>
                    <select id="filterStatus">
                        <option value="">All statuses</option>
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?php echo $s['id']; ?>"
                                <?php echo $filterStatus == $s['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($s['status_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display:flex;gap:8px;align-items:flex-end">
                    <button onclick="applyFilters()" class="qa-tile qa-tile--teal px-4 py-2 text-xs rounded-lg font-medium">
                        Apply
                    </button>
                    <button onclick="resetFilters()" class="btn-apt-view px-4 py-2 text-xs">
                        Reset
                    </button>
                </div>
            </div>

            <!-- List panel -->
            <div class="list-panel">
                <div class="list-panel-header">
                    <span class="list-panel-title">
                        <?php echo $filterStatus
                            ? 'Filtered by status'
                            : 'Appointments for ' . date('F j, Y', strtotime($filterDate)); ?>
                    </span>
                    <span class="list-panel-count"><?php echo count($appointments); ?> appointments</span>
                </div>

                <?php if (empty($appointments)): ?>
                    <div class="apt-empty">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p>No appointments found</p>
                        <a href="#" onclick="openAppointmentModal(); return false;">+ Schedule a new visit</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($appointments as $apt):
                        $filled  = (int)($apt['registered_students'] ?? 0);
                        $max     = (int)($apt['max_students'] ?? 1);
                        $pct     = min(100, $max > 0 ? round($filled / $max * 100) : 0);
                        $isFull  = $filled >= $max;
                        $sname   = $apt['status_name'] ?? 'Scheduled';
                        $isDone  = str_contains(strtolower($sname), 'complet') || str_contains(strtolower($sname), 'done');
                        $aptId   = (int)$apt['id'];
                    ?>
                        <div class="apt-card" id="apt-card-<?php echo $aptId; ?>">

                            <!-- Body -->
                            <div class="apt-card-body">
                                <div class="apt-dot <?php echo aptDotClass($sname); ?>"></div>

                                <div class="apt-time">
                                    <span class="apt-time-start"><?php echo date('g:i A', strtotime($apt['start_time'])); ?></span>
                                    <div class="apt-time-sep"></div>
                                    <span class="apt-time-end"><?php echo date('g:i', strtotime($apt['end_time'])); ?></span>
                                </div>

                                <div class="apt-info">
                                    <div class="apt-top">
                                        <span class="apt-name"><?php echo htmlspecialchars($apt['service_name']); ?></span>
                                        <span class="apt-badge <?php echo aptBadgeClass($sname); ?>">
                                            <?php echo htmlspecialchars($sname); ?>
                                        </span>
                                    </div>

                                    <div class="apt-meta">
                                        <span class="apt-meta-item">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2" />
                                                <circle cx="12" cy="7" r="4" />
                                            </svg>
                                            <?php echo htmlspecialchars($apt['provider_name'] ?? 'N/A'); ?>
                                        </span>
                                        <span class="apt-meta-item">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <?php echo date('F j, Y', strtotime($apt['visit_date'] ?? $apt['created_at'])); ?>
                                        </span>
                                    </div>

                                    <!-- Capacity bar + live count -->
                                    <div class="cap-wrap">
                                        <div class="cap-bar">
                                            <div class="cap-fill <?php echo $isDone ? 'cap-fill--done' : ($isFull ? 'cap-fill--full' : ''); ?>"
                                                id="cap-fill-<?php echo $aptId; ?>"
                                                style="width:<?php echo $pct; ?>%"></div>
                                        </div>
                                        <span class="cap-label" id="cap-label-<?php echo $aptId; ?>">
                                            <strong><?php echo $filled; ?> / <?php echo $max; ?></strong> students
                                        </span>
                                    </div>

                                    <!-- Next priority pill -->
                                    <?php if ($isFull || $isDone): ?>
                                        <div class="next-priority next-priority--closed">
                                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <?php echo $isDone ? 'Completed' : 'Fully booked'; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="next-priority" id="next-priority-<?php echo $aptId; ?>">
                                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                            </svg>
                                            Next priority: <span>#<?php echo $filled + 1; ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($apt['notes'])): ?>
                                        <p class="apt-note"><?php echo htmlspecialchars(substr($apt['notes'], 0, 120)); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="apt-card-footer">
                                <div class="apt-footer-left">
                                    <button class="btn-apt-view"
                                        onclick="viewAppointmentDetails(<?php echo $aptId; ?>)">
                                        View details
                                    </button>
                                    <button class="btn-apt-qr"
                                        id="qr-btn-<?php echo $aptId; ?>"
                                        onclick="generateQRCode(<?php echo $aptId; ?>, this)"
                                        <?php echo ($isFull || $isDone) ? 'disabled' : ''; ?>>
                                        Generate QR
                                    </button>
                                </div>
                                <?php if ($userRole === 'admin'): ?>
                                    <div class="apt-footer-right">
                                        <button class="btn-apt-edit"
                                            onclick="openEditModal(<?php echo $aptId; ?>)">
                                            Edit
                                        </button>
                                        <button class="btn-apt-delete"
                                            onclick="deleteAppointment(<?php echo $aptId; ?>, '<?php echo htmlspecialchars($apt['service_name'], ENT_QUOTES); ?>', this)">
                                            Delete
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <?php AppointmentModal::render($appointmentController, $currentUserId); ?>
    <?php require_once __DIR__ . '/../components/ViewAppointmentModal.php'; ?>
    <?php ViewAppointmentModal::render(); ?>
    <?php echo DeleteConfirm::render('deleteConfirmModal', 'appointment'); ?>

    <script src="../js/sidebar.js"></script>
    <script src="../js/appointments.js"></script>
    <?php echo DeleteConfirm::getScript(); ?>

</body>

</html>