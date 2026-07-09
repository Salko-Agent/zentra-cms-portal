<?php
// ============================================================
// Zentra – Analytics Display Helpers
// Shared functions for analytics admin pages
// ============================================================

/**
 * Render a severity badge for SEO audit issues.
 */
function severity_badge(string $severity): string {
    $map = [
        'critical' => ['label' => 'Kritisch',  'class' => 'badge-danger'],
        'warning'  => ['label' => 'Warnung',   'class' => 'badge-warning'],
        'info'     => ['label' => 'Info',       'class' => 'badge-info'],
        'good'     => ['label' => 'Gut',        'class' => 'badge-success'],
    ];
    $b = $map[$severity] ?? $map['info'];
    return '<span class="badge ' . $b['class'] . '">' . $b['label'] . '</span>';
}

/**
 * Format a CWV metric with color coding.
 */
function vitals_card(string $label, string $value, string $unit, string $rating): string {
    $color = match($rating) {
        'good'       => 'var(--green)',
        'needs-work' => 'var(--gold)',
        'poor'       => 'var(--red)',
        default      => 'var(--text-muted)',
    };
    return '<div class="vitals-card" style="--vital-color:' . $color . '">'
        . '<div class="vitals-label">' . htmlspecialchars($label) . '</div>'
        . '<div class="vitals-value" style="color:' . $color . '">' . $value . '<small>' . $unit . '</small></div>'
        . '</div>';
}

/**
 * Rate a Core Web Vital value.
 */
function rate_lcp(float $val): string {
    return $val <= 2.5 ? 'good' : ($val <= 4.0 ? 'needs-work' : 'poor');
}
function rate_tbt(float $val): string {
    return $val <= 200 ? 'good' : ($val <= 600 ? 'needs-work' : 'poor');
}
function rate_cls(float $val): string {
    return $val <= 0.1 ? 'good' : ($val <= 0.25 ? 'needs-work' : 'poor');
}
function rate_fcp(float $val): string {
    return $val <= 1.8 ? 'good' : ($val <= 3.0 ? 'needs-work' : 'poor');
}

/**
 * Activity log action label + icon.
 */
function activity_action_badge(string $action): string {
    $map = [
        'content_saved'     => ['label' => 'Inhalt gespeichert', 'icon' => '💾', 'class' => 'badge-info'],
        'settings_saved'    => ['label' => 'Einstellungen',      'icon' => '⚙️', 'class' => 'badge-info'],
        'seo_saved'         => ['label' => 'SEO gespeichert',    'icon' => '🔍', 'class' => 'badge-info'],
        'section_created'   => ['label' => 'Sektion erstellt',   'icon' => '➕', 'class' => 'badge-success'],
        'section_deleted'   => ['label' => 'Sektion gelöscht',   'icon' => '🗑️',  'class' => 'badge-danger'],
        'page_created'      => ['label' => 'Seite erstellt',     'icon' => '📄', 'class' => 'badge-success'],
        'page_deleted'      => ['label' => 'Seite gelöscht',     'icon' => '🗑️',  'class' => 'badge-danger'],
        'media_uploaded'    => ['label' => 'Upload',             'icon' => '📷', 'class' => 'badge-info'],
        'media_deleted'     => ['label' => 'Medien gelöscht',    'icon' => '🗑️',  'class' => 'badge-danger'],
        'user_login'        => ['label' => 'Login',              'icon' => '🔑', 'class' => 'badge-success'],
        'user_logout'       => ['label' => 'Logout',             'icon' => '🚪', 'class' => 'badge-muted'],
        'password_changed'  => ['label' => 'Passwort geändert',  'icon' => '🔒', 'class' => 'badge-warning'],
        'project_switched'  => ['label' => 'Projekt gewechselt', 'icon' => '🔄', 'class' => 'badge-info'],
    ];
    $a = $map[$action] ?? ['label' => $action, 'icon' => '📝', 'class' => 'badge-muted'];
    return '<span class="badge ' . $a['class'] . '">' . $a['icon'] . ' ' . htmlspecialchars($a['label']) . '</span>';
}

/**
 * Time ago (German).
 */
function time_ago(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60) return 'gerade eben';
    if ($diff < 3600) return floor($diff / 60) . ' Min.';
    if ($diff < 86400) return floor($diff / 3600) . ' Std.';
    if ($diff < 604800) return floor($diff / 86400) . ' Tage';
    return date('d.m.Y', strtotime($datetime));
}
