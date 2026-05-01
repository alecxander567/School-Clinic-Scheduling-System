<?php
require_once __DIR__ . '/../config/env.php';

class NotificationController
{
    private $pdo;
    private $apiKey;
    private $fromEmail;
    private $fromName;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        Env::load(__DIR__ . '/../.env');
        $this->apiKey = Env::get('RESEND_API_KEY');
        $this->fromEmail = Env::get('RESEND_FROM_EMAIL', 'onboarding@resend.dev');
        $this->fromName = Env::get('RESEND_FROM_NAME', 'School Clinic System');
    }

    public function sendEmail($to, $subject, $htmlContent)
    {
        if (empty($this->apiKey)) {
            return ['success' => false, 'error' => 'Email service not configured'];
        }

        $ch = curl_init();
        $data = [
            'from' => $this->fromName . ' <' . $this->fromEmail . '>',
            'to'   => [$to],
            'subject' => $subject,
            'html' => $htmlContent,
        ];

        curl_setopt($ch, CURLOPT_URL, 'https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);

        if ($curlError) {
            return ['success' => false, 'error' => 'Connection error: ' . $curlError];
        }

        if ($httpCode === 200 || $httpCode === 201) {
            $result = json_decode($response, true);
            return ['success' => true, 'id' => $result['id'] ?? null];
        } else {
            $error    = json_decode($response, true);
            $errorMsg = $error['message'] ?? 'HTTP Error ' . $httpCode;
            return ['success' => false, 'error' => $errorMsg];
        }
    }

    public function sendAppointmentReminder($studentEmail, $studentName, $appointmentDetails, $priorityNumber)
    {
        $subject = "Appointment Reminder — School Clinic";
        return $this->sendEmail($studentEmail, $subject, $this->getReminderEmailHTML($studentName, $appointmentDetails, $priorityNumber));
    }

    public function sendQueueConfirmation($studentEmail, $studentName, $appointmentDetails, $priorityNumber)
    {
        $subject = "Queue Registration Confirmed — School Clinic";
        return $this->sendEmail($studentEmail, $subject, $this->getConfirmationEmailHTML($studentName, $appointmentDetails, $priorityNumber));
    }

    private function baseStyles(): string
    {
        return '
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: #f0f4f2; font-family: Arial, sans-serif; color: #1a2e27; }
        .shell { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 16px; overflow: hidden; }
        .header { background: #1a4a3a; padding: 36px 40px 32px; }
        .header h1 { font-size: 24px; font-weight: bold; color: #ffffff; line-height: 1.3; }
        .header p  { font-size: 14px; color: rgba(255,255,255,0.65); margin-top: 6px; }
        .body   { padding: 36px 40px; }
        .greeting { font-size: 16px; color: #1a2e27; margin-bottom: 28px; line-height: 1.6; }
        .priority-badge { background: #eaf5f0; border: 1.5px solid #2d8a6e; border-radius: 12px; padding: 20px 24px; text-align: center; margin-bottom: 28px; }
        .priority-badge .label { font-size: 12px; font-weight: bold; letter-spacing: 0.08em; text-transform: uppercase; color: #2d8a6e; margin-bottom: 6px; }
        .priority-badge .number { font-size: 52px; font-weight: bold; color: #1a4a3a; line-height: 1; }
        .details-card { background: #f7faf9; border-radius: 12px; padding: 20px 24px; margin-bottom: 28px; }
        .card-title { font-size: 11px; font-weight: bold; letter-spacing: 0.08em; text-transform: uppercase; color: #7aab97; margin-bottom: 16px; }
        .detail-row { padding: 10px 0; border-bottom: 1px solid #e8f0ec; }
        .detail-row:last-child { border-bottom: none; }
        .detail-icon-cell { width: 36px; vertical-align: middle; font-size: 16px; }
        .detail-key { font-size: 11px; color: #7aab97; font-weight: bold; margin-bottom: 2px; }
        .detail-val { font-size: 14px; color: #1a2e27; font-weight: bold; }
        .reminders { margin-bottom: 28px; }
        .reminders h3 { font-size: 13px; font-weight: bold; color: #1a4a3a; margin-bottom: 12px; }
        .reminder-row { margin-bottom: 10px; font-size: 14px; color: #3d5e52; line-height: 1.5; }
        .reminder-dot { color: #2d8a6e; font-weight: bold; margin-right: 6px; }
        .footer { background: #f7faf9; border-top: 1px solid #e8f0ec; padding: 20px 40px; text-align: center; }
        .footer p { font-size: 12px; color: #9ab8ae; line-height: 1.6; }
        .footer strong { color: #2d8a6e; }
    </style>';
    }

    private function iconCalendar(): string
    {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a4a3a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="16" y1="2" x2="16" y2="6"/></svg>';
    }

    private function iconClock(): string
    {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a4a3a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><polyline points="12 7 12 12 15 15"/></svg>';
    }

    private function iconHospital(): string
    {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a4a3a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>';
    }

    private function iconPerson(): string
    {
        return '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#1a4a3a" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>';
    }
    private function detailsBlock(array $d): string
    {
        $date     = date('F j, Y', strtotime($d['visit_date']));
        $timeFrom = date('g:i A', strtotime($d['start_time']));
        $timeTo   = date('g:i A', strtotime($d['end_time']));
        $service  = htmlspecialchars($d['service_name']);
        $provider = htmlspecialchars($d['provider_name']);

        $rows = [
            ['&#128197;', 'Date',     $date],
            ['&#128336;', 'Time',     $timeFrom . ' &ndash; ' . $timeTo],
            ['&#127973;', 'Service',  $service],
            ['&#128100;', 'Provider', $provider],
        ];

        $html = '
    <div class="details-card">
        <div class="card-title">Appointment Details</div>
        <table width="100%" cellpadding="0" cellspacing="0" border="0">';

        foreach ($rows as [$icon, $key, $val]) {
            $html .= '
            <tr class="detail-row">
                <td class="detail-icon-cell" style="width:36px;vertical-align:middle;font-size:18px;padding:10px 0;">' . $icon . '</td>
                <td style="vertical-align:middle;padding:10px 0;">
                    <div class="detail-key">' . $key . '</div>
                    <div class="detail-val">' . $val . '</div>
                </td>
            </tr>';
        }

        $html .= '
        </table>
    </div>';

        return $html;
    }

    private function getReminderEmailHTML($studentName, $appointmentDetails, $priorityNumber): string
    {
        $name = htmlspecialchars($studentName);
        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . $this->baseStyles()
            . '</head><body>
        <div class="shell">
            <div class="header">
                <div class="header-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                    </svg>
                </div>
                <h1>Appointment Reminder</h1>
                <p>School Clinic &mdash; upcoming visit today</p>
            </div>
            <div class="body">
                <p class="greeting">Hi <strong>' . $name . '</strong>,<br>Just a friendly reminder about your clinic visit scheduled for today. Please review the details below.</p>

                <div class="priority-badge">
                    <div class="label">Your Priority Number</div>
                    <div class="number">#' . $priorityNumber . '</div>
                </div>

                ' . $this->detailsBlock($appointmentDetails) . '

                <div class="reminders">
                    <h3>Before you come in</h3>
                    <div class="reminder-item"><div class="reminder-dot"></div>Arrive at least 10 minutes before your priority number is called.</div>
                    <div class="reminder-item"><div class="reminder-dot"></div>Bring your student ID and any relevant medical records.</div>
                    <div class="reminder-item"><div class="reminder-dot"></div>If you cannot attend, please inform the clinic in advance.</div>
                </div>

                <p style="font-size:14px;color:#3d5e52;line-height:1.6;">Thank you for using our clinic services. We look forward to seeing you!</p>
            </div>
            <div class="footer">
                <p><strong>School Clinic System</strong><br>This is an automated message &mdash; please do not reply to this email.</p>
            </div>
        </div>
        </body></html>';
    }

    private function getConfirmationEmailHTML($studentName, $appointmentDetails, $priorityNumber): string
    {
        $name = htmlspecialchars($studentName);
        return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . $this->baseStyles()
            . '</head><body>
        <div class="shell">
            <div class="header">
                <div class="header-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                <h1>You&rsquo;re registered!</h1>
                <p>School Clinic &mdash; queue confirmation</p>
            </div>
            <div class="body">
                <p class="greeting">Hi <strong>' . $name . '</strong>,<br>Your clinic appointment has been confirmed. Save this email for your reference.</p>

                <div class="priority-badge">
                    <div class="label">Your Priority Number</div>
                    <div class="number">#' . $priorityNumber . '</div>
                </div>

                ' . $this->detailsBlock($appointmentDetails) . '

                <div class="reminders">
                    <h3>What happens next</h3>
                    <div class="reminder-item"><div class="reminder-dot"></div>You will be served in priority order (1, 2, 3&hellip;).</div>
                    <div class="reminder-item"><div class="reminder-dot"></div>A reminder email will be sent on the day of your appointment.</div>
                    <div class="reminder-item"><div class="reminder-dot"></div>Please be at the clinic at least 10 minutes before your turn.</div>
                </div>

                <p style="font-size:14px;color:#3d5e52;line-height:1.6;">Thank you for using our clinic services. We look forward to seeing you!</p>
            </div>
            <div class="footer">
                <p><strong>School Clinic System</strong><br>This is an automated message &mdash; please do not reply to this email.</p>
            </div>
        </div>
        </body></html>';
    }

    public function getTodayAppointmentsWithStudents()
    {
        $sql = "SELECT 
                    aq.id, aq.priority_number, aq.status as queue_status, aq.created_at as registered_at,
                    a.*, s.id as student_id, s.student_number, s.first_name, s.last_name,
                    s.course, s.year_level, s.contact_number, s.email,
                    sv.service_name, p.name as provider_name
                FROM appointment_queues aq
                JOIN appointments a  ON aq.appointment_id = a.id
                JOIN students s      ON aq.student_id = s.id
                JOIN services sv     ON a.service_id = sv.id
                JOIN providers p     ON a.provider_id = p.id
                WHERE a.visit_date = CURDATE()
                  AND aq.status = 'pending'
                ORDER BY aq.priority_number ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendDailyReminders()
    {
        $appointments = $this->getTodayAppointmentsWithStudents();
        $sent = 0;
        $failed = 0;

        foreach ($appointments as $apt) {
            $studentEmail = $apt['email'] ?? null;
            if ($studentEmail) {
                $studentName = $apt['first_name'] . ' ' . $apt['last_name'];
                $details = [
                    'visit_date'   => $apt['visit_date'],
                    'start_time'   => $apt['start_time'],
                    'end_time'     => $apt['end_time'],
                    'service_name' => $apt['service_name'],
                    'provider_name' => $apt['provider_name'],
                ];
                $result = $this->sendAppointmentReminder($studentEmail, $studentName, $details, $apt['priority_number']);
                $result['success'] ? $sent++ : $failed++;
                if (!$result['success']) {
                    error_log("Failed to send reminder to: {$studentEmail} — " . ($result['error'] ?? 'Unknown error'));
                }
            } else {
                $failed++;
            }
        }

        return ['success' => true, 'sent' => $sent, 'failed' => $failed, 'total' => count($appointments)];
    }
}
