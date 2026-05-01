<?php
class Sidebar
{
    private $activeMenu;
    private $userRole;
    private $userName;

    private static $appRoot = '';

    public function __construct($activeMenu = '', $userRole = 'admin', $userName = 'User')
    {
        $this->activeMenu = $activeMenu;
        $this->userRole   = $userRole;
        $this->userName   = $userName;
    }

    private function url($link)
    {
        return self::$appRoot . '/' . ltrim($link, '/');
    }

    public function render()
    {
        $initials = strtoupper(substr($this->userName, 0, 2));
        ob_start();
?>
        <aside id="sidebar"
            class="fixed left-0 top-0 z-40 h-screen w-56 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-300"
            style="background:#1a2e25;">

            <!-- Header -->
            <div class="p-4" style="border-bottom:1px solid rgba(255,255,255,0.07);">

                <!-- Brand -->
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#2d8a6e;">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <span class="text-sm font-semibold" style="color:#e1f5ee;">Clinic Scheduler</span>
                </div>

                <!-- User chip -->
                <div class="flex items-center gap-2 px-2.5 py-2 rounded-lg" style="background:rgba(45,138,110,0.15); border:1px solid rgba(45,138,110,0.25);">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-semibold flex-shrink-0"
                        style="background:#2d8a6e; color:#e1f5ee;">
                        <?php echo htmlspecialchars($initials); ?>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium truncate" style="color:#c8e8de;"><?php echo htmlspecialchars($this->userName); ?></p>
                        <p class="text-xs" style="color:#7aaa96;"><?php echo ucfirst($this->userRole); ?></p>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-3 px-2.5" style="scrollbar-width:thin; scrollbar-color:#4a7a65 transparent;">
                <?php echo $this->getMenuItems(); ?>
            </nav>

            <!-- Footer -->
            <div class="p-3" style="border-top:1px solid rgba(255,255,255,0.07);">
                <a href="<?php echo htmlspecialchars($this->url('logout.php')); ?>"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-all duration-200"
                    style="color:#e07070;"
                    onmouseover="this.style.background='rgba(224,112,112,0.1)'"
                    onmouseout="this.style.background='transparent'">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="text-xs">Logout</span>
                </a>
            </div>
        </aside>
<?php
        return ob_get_clean();
    }

    private function getMenuItems()
    {
        $menuItems   = $this->defineMenuItems();
        $html        = '';
        $lastSection = null;

        foreach ($menuItems as $key => $item) {
            if (!$this->hasAccess($item)) continue;

            $section = $item['section'] ?? null;
            if ($section && $section !== $lastSection) {
                $html .= '<p class="text-xs font-medium uppercase tracking-widest px-2 mt-4 mb-1" style="color:#4a7a65; font-size:9px; letter-spacing:0.08em;">'
                    . htmlspecialchars($section) . '</p>';
                $lastSection = $section;
            }

            $html .= $this->renderMenuItem($item, $key);
        }

        return $html;
    }

    private function defineMenuItems()
    {
        return [
            'dashboard' => [
                'section' => 'Main',
                'icon'  => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'label' => 'Dashboard',
                'link'  => 'dashboard.php',
                'roles' => ['admin', 'nurse', 'student', 'teacher'],
            ],
            'appointments' => [
                'section' => 'Main',
                'icon'  => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
                'label' => 'Appointments',
                'link'  => 'appointments.php',
                'roles' => ['admin', 'nurse', 'student', 'teacher'],
                'submenu' => [
                    ['icon' => 'M12 4v16m8-8H4',                                                                                        'label' => 'New Appointment',   'link' => 'appointments/new.php',      'roles' => ['student', 'teacher']],
                    ['icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16',                                                                       'label' => 'View Appointments', 'link' => 'appointments/list.php',     'roles' => ['admin', 'nurse', 'student', 'teacher']],
                    ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',              'label' => 'Calendar',          'link' => 'appointments/calendar.php', 'roles' => ['admin', 'nurse']],
                ],
            ],
            'schedule' => [
                'section' => 'Main',
                'icon'  => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'label' => 'Schedule',
                'link'  => 'schedule.php',
                'roles' => ['admin', 'nurse'],
            ],
            'students' => [
                'section' => 'Records',
                'icon'  => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',
                'label' => 'Students',
                'link'  => 'students.php',
                'roles' => ['admin', 'nurse'],
                'submenu' => [
                    ['icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',                                                                                                                                  'label' => 'Add Student',    'link' => 'students/add.php',                         'roles' => ['admin']],
                    ['icon' => 'M4 6h16M4 10h16M4 14h16M4 18h16',                                                                                                                                                                                        'label' => 'Student List',   'link' => 'students/list.php',                        'roles' => ['admin', 'nurse']],
                    ['icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',                                                                                                  'label' => 'Health Records', 'link' => 'health-records/health-records.php',        'roles' => ['admin', 'nurse']],
                    ['icon' => 'M12 4.5a7.5 7.5 0 00-7.5 7.5c0 3.5 2.5 6.5 6 7.2V21h3v-1.8c3.5-0.7 6-3.7 6-7.2 0-4.15-3.35-7.5-7.5-7.5z M12 9a3 3 0 100 6 3 3 0 000-6z M9 20.5v-2.5h6v2.5H9z', 'label' => 'Dental Records', 'link' => 'health-records/dental-records.php',        'roles' => ['admin', 'nurse']],
                ],
            ],
            'medical-records' => [
                'section' => 'Records',
                'icon'  => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'label' => 'Medical Records',
                'link'  => 'medical-records.php',
                'roles' => ['admin', 'nurse'],
                'submenu' => [
                    ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Visit History',  'link' => 'medical/visits.php',      'roles' => ['admin', 'nurse']],
                    ['icon' => 'M20 12H4M12 4v16',                                                                                                                                 'label' => 'Medications',     'link' => 'medical/medications.php', 'roles' => ['admin', 'nurse']],
                    ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',                 'label' => 'Medical Reports', 'link' => 'medical/reports.php',     'roles' => ['admin', 'nurse']],
                ],
            ],
            'dental-records' => [
                'section' => 'Records',
                'icon'  => 'M12 4.5a7.5 7.5 0 00-7.5 7.5c0 3.5 2.5 6.5 6 7.2V21h3v-1.8c3.5-0.7 6-3.7 6-7.2 0-4.15-3.35-7.5-7.5-7.5z M12 9a3 3 0 100 6 3 3 0 000-6z M9 20.5v-2.5h6v2.5H9z',
                'label' => 'Dental Records',
                'link'  => 'dental-records.php',
                'roles' => ['admin', 'nurse'],
                'submenu' => [
                    ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                      'label' => 'Dental Visits',   'link' => 'dental/visits.php',     'roles' => ['admin', 'nurse']],
                    ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'label' => 'Treatments',        'link' => 'dental/treatments.php', 'roles' => ['admin', 'nurse']],
                    ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',   'label' => 'Dental Reports',   'link' => 'dental/reports.php',    'roles' => ['admin', 'nurse']],
                ],
            ],
            'consultations' => [
                'section' => 'Records',
                'icon'  => 'M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z',
                'label' => 'Consultations',
                'link'  => 'consultations.php',
                'roles' => ['admin', 'nurse', 'student', 'teacher'],
                'submenu' => [
                    ['icon' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z', 'label' => 'Online Consultation',  'link' => 'consultations/online.php',   'roles' => ['student', 'teacher']],
                    ['icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                             'label' => 'Consultation History', 'link' => 'consultations/history.php',  'roles' => ['admin', 'nurse', 'student', 'teacher']],
                    ['icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z',                                                                                                             'label' => 'Messages',             'link' => 'consultations/messages.php', 'roles' => ['admin', 'nurse']],
                ],
            ],
            'reports' => [
                'section' => 'Manage',
                'icon'  => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
                'label' => 'Reports',
                'link'  => 'reports.php',
                'roles' => ['admin', 'nurse'],
                'submenu' => [
                    ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',                                        'label' => 'Daily Reports',   'link' => 'reports/daily.php',   'roles' => ['admin', 'nurse']],
                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'Monthly Reports', 'link' => 'reports/monthly.php', 'roles' => ['admin', 'nurse']],
                    ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',                      'label' => 'Health Trends',   'link' => 'reports/trends.php',  'roles' => ['admin']],
                ],
            ],
            'announcements' => [
                'section' => 'Manage',
                'icon'  => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
                'label' => 'Announcements',
                'link'  => 'announcements.php',
                'roles' => ['admin', 'nurse'],
            ],
            'settings' => [
                'section' => 'Manage',
                'icon'  => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
                'label' => 'Settings',
                'link'  => 'settings.php',
                'roles' => ['admin'],
                'submenu' => [
                    ['icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',   'label' => 'Clinic Settings', 'link' => 'settings/clinic.php',        'roles' => ['admin']],
                    ['icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z',                                   'label' => 'User Management', 'link' => 'settings/users.php',         'roles' => ['admin']],
                    ['icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9', 'label' => 'Notifications',   'link' => 'settings/notifications.php', 'roles' => ['admin']],
                ],
            ],
        ];
    }

    private function hasAccess($item)
    {
        return in_array($this->userRole, $item['roles']);
    }

    private function renderMenuItem($item, $key = '')
    {
        $active     = ($this->activeMenu === $item['label'] || $this->activeMenu === $item['link']);
        $hasSubmenu = !empty($item['submenu']);
        $itemId     = 'menu-' . $key;

        $baseStyle     = 'display:flex;align-items:center;gap:8px;padding:6px 10px;border-radius:8px;width:100%;transition:background 0.15s;text-decoration:none;';
        $activeStyle   = 'background:#2d8a6e;';
        $inactiveStyle = 'background:transparent;';
        $hoverOn  = "this.style.background='" . ($active ? '#2d8a6e' : 'rgba(255,255,255,0.07)') . "'";
        $hoverOff = "this.style.background='" . ($active ? '#2d8a6e' : 'transparent') . "'";

        $html = '<div class="mb-0.5">';

        if (!$hasSubmenu) {
            $html .= '<a href="' . htmlspecialchars($this->url($item['link'])) . '" '
                . 'style="' . $baseStyle . ($active ? $activeStyle : $inactiveStyle) . '" '
                . 'onmouseover="' . $hoverOn . '" onmouseout="' . $hoverOff . '">';
        } else {
            $html .= '<button onclick="toggleSubmenu(\'' . $itemId . '\')" '
                . 'style="' . $baseStyle . ($active ? $activeStyle : $inactiveStyle) . '" '
                . 'onmouseover="' . $hoverOn . '" onmouseout="' . $hoverOff . '">';
        }

        $iconColor = $active ? '#e1f5ee' : '#7aaa96';
        $html .= '<svg style="width:14px;height:14px;flex-shrink:0;color:' . $iconColor . ';" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">';
        $html .= '<path stroke-linecap="round" stroke-linejoin="round" d="' . $item['icon'] . '"/>';
        $html .= '</svg>';

        $labelColor = $active ? '#e1f5ee' : '#9ab5aa';
        $html .= '<span style="font-size:12px;flex:1;text-align:left;color:' . $labelColor . ';">' . htmlspecialchars($item['label']) . '</span>';

        if ($hasSubmenu) {
            $html .= '<svg id="arrow-' . $itemId . '" style="width:12px;height:12px;color:#4a7a65;transition:transform 0.2s;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>';
            $html .= '</svg>';
        }

        $html .= $hasSubmenu ? '</button>' : '</a>';

        if ($hasSubmenu) {
            $html .= '<ul id="' . $itemId . '" class="hidden" style="margin-left:20px;margin-top:2px;">';
            foreach ($item['submenu'] as $subitem) {
                if (!$this->hasAccess($subitem)) continue;
                $subActive   = ($this->activeMenu === $subitem['label'] || $this->activeMenu === $subitem['link']);
                $subBg       = $subActive ? '#2d8a6e' : 'transparent';
                $subHoverOn  = "this.style.background='" . ($subActive ? '#2d8a6e' : 'rgba(255,255,255,0.06)') . "'";
                $subHoverOff = "this.style.background='" . $subBg . "'";
                $html .= '<li class="mb-0.5">';
                $html .= '<a href="' . htmlspecialchars($this->url($subitem['link'])) . '" '
                    . 'style="display:flex;align-items:center;gap:7px;padding:5px 8px;border-radius:6px;text-decoration:none;background:' . $subBg . ';" '
                    . 'onmouseover="' . $subHoverOn . '" onmouseout="' . $subHoverOff . '">';
                $subIconColor  = $subActive ? '#e1f5ee' : '#4a7a65';
                $subLabelColor = $subActive ? '#e1f5ee' : '#7aaa96';
                $html .= '<svg style="width:12px;height:12px;flex-shrink:0;color:' . $subIconColor . ';" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">';
                $html .= '<path stroke-linecap="round" stroke-linejoin="round" d="' . $subitem['icon'] . '"/>';
                $html .= '</svg>';
                $html .= '<span style="font-size:11px;color:' . $subLabelColor . ';">' . htmlspecialchars($subitem['label']) . '</span>';
                $html .= '</a>';
                $html .= '</li>';
            }
            $html .= '</ul>';
        }

        $html .= '</div>';
        return $html;
    }
}
?>