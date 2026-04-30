<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/QueueController.php';

$token = $_GET['token'] ?? '';
if (empty($token)) {
    die('Invalid QR code. Please contact the clinic administrator.');
}

$queueController = new QueueController($pdo);
$appointment = $queueController->getAppointmentByToken($token);

if (!$appointment) {
    die('This appointment is either invalid, expired, or fully booked.');
}

// Define the courses list (same as in add-student.php)
$courses = [
    'Education',
    'Information Technology',
    'Criminology',
    'Business Administration',
    'Human Services'
];

$error = '';
$success = false;
$priorityInfo = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $studentNumber  = $_POST['student_number'] ?? '';
    $firstName      = $_POST['first_name'] ?? '';
    $lastName       = $_POST['last_name'] ?? '';
    $course         = $_POST['course'] ?? '';
    $yearLevel      = $_POST['year_level'] ?? '';
    $contactNumber  = $_POST['contact_number'] ?? '';
    $symptoms       = $_POST['symptoms'] ?? '';

    $studentSql = "SELECT id FROM students WHERE student_number = :student_number";
    $studentStmt = $pdo->prepare($studentSql);
    $studentStmt->execute([':student_number' => $studentNumber]);
    $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

    if ($student) {
        $studentId = $student['id'];
    } else {
        $insertSql = "INSERT INTO students (student_number, first_name, last_name, course, year_level, contact_number)
                      VALUES (:student_number, :first_name, :last_name, :course, :year_level, :contact_number)";
        $insertStmt = $pdo->prepare($insertSql);
        $insertStmt->execute([
            ':student_number'  => $studentNumber,
            ':first_name'      => $firstName,
            ':last_name'       => $lastName,
            ':course'          => $course,
            ':year_level'      => $yearLevel,
            ':contact_number'  => $contactNumber
        ]);
        $studentId = $pdo->lastInsertId();
    }

    $formData = [
        'symptoms'         => $symptoms,
        'additional_notes' => $_POST['notes'] ?? '',
        'submitted_at'     => date('Y-m-d H:i:s')
    ];

    $result = $queueController->addToQueue($appointment['id'], $studentId, $formData);

    if (isset($result['error'])) {
        $error = $result['error'];
    } else {
        $success      = true;
        $priorityInfo = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinic Queue Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/queue.css">
</head>

<body>
    <div class="page-wrap">
        <div class="card">

            <?php if ($success): ?>

                <!-- ── SUCCESS ── -->
                <div class="success-header">
                    <div class="success-icon-ring">
                        <svg width="32" height="32" fill="none" stroke="#fff" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h1>You're in the queue!</h1>
                    <p>Registration completed successfully</p>
                </div>

                <div class="success-body">

                    <div class="priority-tile">
                        <div class="priority-num"><?php echo $priorityInfo['priority_number']; ?></div>
                        <div>
                            <p class="priority-text-label">Your Priority Number</p>
                            <p class="priority-text-val">Queue #<?php echo $priorityInfo['priority_number']; ?></p>
                            <p class="priority-text-sub">Please wait for your number to be called</p>
                        </div>
                    </div>

                    <div class="apt-strip">
                        <div class="apt-strip-label">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Appointment Details
                        </div>
                        <div class="apt-grid">
                            <div class="apt-item">
                                <span class="apt-item-key">Service</span>
                                <span class="apt-item-val"><?php echo htmlspecialchars($appointment['service_name']); ?></span>
                            </div>
                            <div class="apt-item">
                                <span class="apt-item-key">Provider</span>
                                <span class="apt-item-val"><?php echo htmlspecialchars($appointment['provider_name']); ?></span>
                            </div>
                            <div class="apt-item">
                                <span class="apt-item-key">Date</span>
                                <span class="apt-item-val"><?php echo date('F j, Y', strtotime($appointment['visit_date'])); ?></span>
                            </div>
                            <div class="apt-item">
                                <span class="apt-item-key">Time</span>
                                <span class="apt-item-val"><?php echo date('g:i A', strtotime($appointment['start_time'])); ?> – <?php echo date('g:i A', strtotime($appointment['end_time'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="info-notice">
                        <strong>Reminder:</strong> Save or print this page. You will be served in order — please be present at least 10 minutes before your turn.
                    </div>

                    <div class="divider"></div>

                    <button class="btn-print">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print / Save this page
                    </button>

                </div>

            <?php else: ?>

                <!-- ── FORM ── -->
                <div class="card-header">
                    <div class="header-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <h1>Queue Registration</h1>
                    <p>Fill in your details to receive a priority number</p>
                </div>

                <div class="card-body">

                    <!-- Appointment info -->
                    <div class="apt-strip">
                        <div class="apt-strip-label">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            Appointment Details
                        </div>
                        <div class="apt-grid">
                            <div class="apt-item">
                                <span class="apt-item-key">Service</span>
                                <span class="apt-item-val"><?php echo htmlspecialchars($appointment['service_name']); ?></span>
                            </div>
                            <div class="apt-item">
                                <span class="apt-item-key">Provider</span>
                                <span class="apt-item-val"><?php echo htmlspecialchars($appointment['provider_name']); ?></span>
                            </div>
                            <div class="apt-item">
                                <span class="apt-item-key">Date</span>
                                <span class="apt-item-val"><?php echo date('F j, Y', strtotime($appointment['visit_date'])); ?></span>
                            </div>
                            <div class="apt-item">
                                <span class="apt-item-key">Time</span>
                                <span class="apt-item-val"><?php echo date('g:i A', strtotime($appointment['start_time'])); ?> – <?php echo date('g:i A', strtotime($appointment['end_time'])); ?></span>
                            </div>
                            <div class="apt-item" style="grid-column:1/-1">
                                <span class="apt-item-key">Available Slots</span>
                                <span class="apt-item-val">
                                    <span class="slot-pill">
                                        <svg width="9" height="9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 11a4 4 0 100-8 4 4 0 000 8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                                        </svg>
                                        <?php echo $appointment['max_students'] - ($appointment['current_queue_count'] ?? 0); ?> of <?php echo $appointment['max_students']; ?> open
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <?php if ($error): ?>
                        <div class="error-box"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>

                    <div class="divider"></div>

                    <form method="POST">

                        <div class="section-label">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Student Information
                        </div>

                        <div class="field-grid" style="margin-bottom:0.875rem">
                            <div class="field">
                                <label>Student Number <span class="req">*</span></label>
                                <input type="text" name="student_number" required placeholder="e.g. 2021-00123">
                            </div>
                            <div class="field">
                                <label>Contact Number <span class="req">*</span></label>
                                <input type="text" name="contact_number" required placeholder="e.g. 09xx xxx xxxx">
                            </div>
                            <div class="field">
                                <label>First Name <span class="req">*</span></label>
                                <input type="text" name="first_name" required placeholder="Juan">
                            </div>
                            <div class="field">
                                <label>Last Name <span class="req">*</span></label>
                                <input type="text" name="last_name" required placeholder="Dela Cruz">
                            </div>
                            <div class="field">
                                <label>Course / Program <span class="req">*</span></label>
                                <div class="select-wrap">
                                    <select name="course" required>
                                        <option value="">Select Course</option>
                                        <?php foreach ($courses as $courseOption): ?>
                                            <option value="<?php echo htmlspecialchars($courseOption); ?>">
                                                <?php echo htmlspecialchars($courseOption); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="field">
                                <label>Year Level <span class="req">*</span></label>
                                <div class="select-wrap">
                                    <select name="year_level" required>
                                        <option value="">Select year</option>
                                        <option value="1">1st Year</option>
                                        <option value="2">2nd Year</option>
                                        <option value="3">3rd Year</option>
                                        <option value="4">4th Year</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="divider" style="margin-bottom:1rem"></div>

                        <div class="section-label">
                            <svg width="10" height="10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Visit Information
                        </div>

                        <div class="field-grid" style="margin-bottom:1.25rem">
                            <div class="field full">
                                <label>Symptoms / Reason for Visit</label>
                                <textarea name="symptoms" placeholder="Describe your symptoms or reason for the visit…"></textarea>
                            </div>
                            <div class="field full">
                                <label>Additional Notes</label>
                                <textarea name="notes" rows="2" placeholder="Anything else the clinic should know…"></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn-submit">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                            Submit & Get Priority Number
                        </button>

                    </form>

                </div>

            <?php endif; ?>

        </div>

        <p class="page-foot">School Clinic &nbsp;·&nbsp; Queue Registration System</p>
    </div>

    <script src="queue.js"></script>
</body>

</html>