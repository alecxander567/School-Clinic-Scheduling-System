<?php
class Alert
{
    const SUCCESS = 'success';
    const ERROR   = 'error';
    const WARNING = 'warning';
    const INFO    = 'info';

    /**
     * Display an alert message.
     *
     * @param string $message     The alert message
     * @param string $type        success | error | warning | info
     * @param bool   $dismissible Whether the alert can be dismissed
     * @return string HTML for the alert
     */
    public static function show($message, $type = self::INFO, $dismissible = true)
    {
        if (empty($message)) {
            return '';
        }

        $icons = [
            self::SUCCESS => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            self::ERROR => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
            self::WARNING => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>',
            self::INFO => '
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
        ];

        $config = [
            self::SUCCESS => [
                'bg'          => '#f0faf5',
                'border'      => '#a8dcc0',
                'accent'      => '#2d8a6e',  
                'text_title'  => '#1a4a38',
                'text_body'   => '#2c5a48',
                'label'       => 'Success',
            ],
            self::ERROR => [
                'bg'          => '#fff5f5',
                'border'      => '#f5b8b8',
                'accent'      => '#c62828',
                'text_title'  => '#7a1515',
                'text_body'   => '#a83232',
                'label'       => 'Error',
            ],
            self::WARNING => [
                'bg'          => '#fffbf0',
                'border'      => '#f5d87a',
                'accent'      => '#c88a00',
                'text_title'  => '#6b4a00',
                'text_body'   => '#8a6200',
                'label'       => 'Warning',
            ],
            self::INFO => [
                'bg'          => '#f0f6ff',
                'border'      => '#b3cef5',
                'accent'      => '#2563eb',
                'text_title'  => '#1a3a7a',
                'text_body'   => '#2c4fa8',
                'label'       => 'Info',
            ],
        ];

        $c    = $config[$type];
        $icon = $icons[$type];
        $id   = 'alert-' . uniqid();

        $dismissBtn = '';
        if ($dismissible) {
            $dismissBtn = '
            <button
                onclick="(function(el){
                    el.style.opacity=\'0\';
                    el.style.transform=\'translateY(-4px)\';
                    setTimeout(function(){ el.remove(); }, 250);
                })(this.closest(\'.clinic-alert\'))"
                style="
                    flex-shrink: 0;
                    margin-left: 0.75rem;
                    padding: 0.25rem;
                    border-radius: 0.375rem;
                    color: ' . $c['accent'] . ';
                    background: transparent;
                    border: none;
                    cursor: pointer;
                    opacity: 0.6;
                    transition: opacity 0.15s ease, background 0.15s ease;
                "
                onmouseover="this.style.opacity=\'1\'; this.style.background=\'rgba(0,0,0,0.06)\';"
                onmouseout="this.style.opacity=\'0.6\'; this.style.background=\'transparent\';"
                aria-label="Dismiss">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>';
        }

        $html = '
        <div id="' . $id . '" class="clinic-alert" style="
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
            padding: 0.875rem 1rem;
            border-radius: 0.625rem;
            border: 1px solid ' . $c['border'] . ';
            border-left: 3.5px solid ' . $c['accent'] . ';
            background: ' . $c['bg'] . ';
            box-shadow: 0 2px 8px rgba(0,0,0,0.04), 0 1px 2px rgba(0,0,0,0.04);
            font-family: \'DM Sans\', system-ui, sans-serif;
            opacity: 0;
            transform: translateY(-6px);
            transition: opacity 0.25s ease, transform 0.25s ease;
        ">
            <!-- Icon + Text -->
            <div style="display:flex; align-items:flex-start; gap:0.75rem; flex:1; min-width:0;">

                <!-- Icon bubble -->
                <div style="
                    flex-shrink: 0;
                    width: 2rem;
                    height: 2rem;
                    border-radius: 0.5rem;
                    background: ' . $c['accent'] . '1a;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin-top: 0.05rem;
                ">
                    <svg class="w-4 h-4" fill="none" stroke="' . $c['accent'] . '" viewBox="0 0 24 24">
                        ' . $icon . '
                    </svg>
                </div>

                <!-- Text -->
                <div style="flex:1; min-width:0;">
                    <p style="
                        font-size: 0.72rem;
                        font-weight: 700;
                        letter-spacing: 0.05em;
                        text-transform: uppercase;
                        color: ' . $c['text_title'] . ';
                        margin: 0 0 0.2rem 0;
                        line-height: 1;
                    ">' . $c['label'] . '</p>
                    <p style="
                        font-size: 0.82rem;
                        color: ' . $c['text_body'] . ';
                        margin: 0;
                        line-height: 1.5;
                    ">' . htmlspecialchars($message) . '</p>
                </div>
            </div>

            ' . $dismissBtn . '
        </div>
        <script>
            (function() {
                var el = document.getElementById("' . $id . '");
                if (el) {
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            el.style.opacity = "1";
                            el.style.transform = "translateY(0)";
                        });
                    });
                }
            })();
        </script>';

        return $html;
    }

    /**
     * Show a success alert and optionally redirect after a delay.
     *
     * @param string      $message     Success message
     * @param string|null $redirectUrl URL to redirect to after $delay seconds
     * @param int         $delay       Seconds before redirect
     */
    public static function success($message, $redirectUrl = null, $delay = 2)
    {
        if ($redirectUrl) {
            echo '<script>
                setTimeout(function() {
                    window.location.href = "' . $redirectUrl . '";
                }, ' . ($delay * 1000) . ');
            </script>';
        }
        return self::show($message, self::SUCCESS);
    }

    /**
     * Show an error alert.
     *
     * @param string $message Error message
     */
    public static function error($message)
    {
        return self::show($message, self::ERROR);
    }

    /**
     * Store an alert in the session for display after a redirect.
     *
     * @param string $message Alert message
     * @param string $type    Alert type constant
     */
    public static function setFlash($message, $type = self::INFO)
    {
        $_SESSION['flash_alert'] = [
            'message' => $message,
            'type'    => $type,
        ];
    }

    /**
     * Display and clear the flash alert from the session.
     *
     * @return string HTML of the flash alert, or empty string
     */
    public static function displayFlash()
    {
        if (isset($_SESSION['flash_alert'])) {
            $alert = $_SESSION['flash_alert'];
            unset($_SESSION['flash_alert']);
            return self::show($alert['message'], $alert['type']);
        }
        return '';
    }
}
