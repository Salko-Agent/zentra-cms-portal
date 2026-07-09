<?php
// includes/section-forms/_helpers.php
// Tiny helpers shared across all section form partials.

/**
 * Render a text input inside a form.
 */
function sf_text(string $name, string $label, string $value, string $hint = ''): void {
    $id = 'sf_' . preg_replace('/[^a-z0-9]/', '_', strtolower($name));
    echo '<div class="form-group">';
    echo '<label for="' . $id . '">' . htmlspecialchars($label) . '</label>';
    echo '<input id="' . $id . '" type="text" name="fields[' . htmlspecialchars($name) . ']"'
        . ' value="' . htmlspecialchars($value) . '">';
    if ($hint) echo '<div class="form-hint">' . htmlspecialchars($hint) . '</div>';
    echo '</div>';
}

function sf_textarea(string $name, string $label, string $value, string $hint = ''): void {
    $id = 'sf_' . preg_replace('/[^a-z0-9]/', '_', strtolower($name));
    echo '<div class="form-group">';
    echo '<label for="' . $id . '">' . htmlspecialchars($label) . '</label>';
    echo '<textarea id="' . $id . '" name="fields[' . htmlspecialchars($name) . ']">'
        . htmlspecialchars($value) . '</textarea>';
    if ($hint) echo '<div class="form-hint">' . htmlspecialchars($hint) . '</div>';
    echo '</div>';
}

function sf_image(string $name, string $label, string $value): void {
    $id = 'sf_' . preg_replace('/[^a-z0-9]/', '_', strtolower($name));
    $esc_name = htmlspecialchars($name);
    $esc_val  = htmlspecialchars($value);
    echo '<div class="form-group">';
    echo '<label for="' . $id . '">' . htmlspecialchars($label) . '</label>';
    echo '<div class="image-field-wrap">';
    echo '<input id="' . $id . '" type="url" name="fields[' . $esc_name . ']"'
        . ' value="' . $esc_val . '" placeholder="https://…" class="image-field-input">';
    echo '<button type="button" class="btn btn-secondary btn-sm media-picker-btn"'
        . ' data-target="' . $id . '">Bild wählen</button>';
    echo '</div>';
    echo '<div class="image-field-preview" id="' . $id . '_preview">';
    if ($value) {
        echo '<img src="' . $esc_val . '" alt="">';
        echo '<button type="button" class="image-field-clear" data-target="' . $id . '" title="Bild entfernen">&times;</button>';
    }
    echo '</div>';
    echo '</div>';
}

/**
 * Render an image field for repeater items (uses items[index][key] naming).
 */
function sf_item_image(string $name, string $label, string $value): void {
    $uid = 'sfi_' . bin2hex(random_bytes(4));
    $esc_val = htmlspecialchars($value);
    echo '<div class="form-group">';
    echo '<label>' . htmlspecialchars($label) . '</label>';
    echo '<div class="image-field-wrap">';
    echo '<input id="' . $uid . '" type="url" name="' . htmlspecialchars($name) . '"'
        . ' value="' . $esc_val . '" placeholder="https://…" class="image-field-input">';
    echo '<button type="button" class="btn btn-secondary btn-sm media-picker-btn"'
        . ' data-target="' . $uid . '">Bild wählen</button>';
    echo '</div>';
    echo '<div class="image-field-preview" id="' . $uid . '_preview">';
    if ($value) {
        echo '<img src="' . $esc_val . '" alt="">';
        echo '<button type="button" class="image-field-clear" data-target="' . $uid . '" title="Bild entfernen">&times;</button>';
    }
    echo '</div>';
    echo '</div>';
}
