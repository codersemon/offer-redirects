<?php
/**
 * Plugin Name: Offer Redirects
 * Plugin URI: https://github.com/codersemon/offer-redirects
 * Description: Create time-based redirect rules for pages with both global and individual user-specific validity periods.
 * Version: 1.0.0
 * Author: Emon Khan
 * Author URI: https://www.emonkhan.me
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: offer-redirects
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

// ------------------- ADMIN MENU -------------------
add_action('admin_menu', function () {
  add_menu_page(
    'Offer Redirects',
    'Offer Redirects',
    'manage_options',
    'offer-redirects',
    'render_offer_redirects_admin_page',
    'dashicons-randomize',
    58
  );
});

// ------------------- ADMIN PAGE -------------------
function render_offer_redirects_admin_page()
{
  $rules_global = get_option('offer_redirect_rules', []);
  $rules_user = get_option('offer_redirect_user_rules', []);
  $pages = get_pages(['post_status' => 'publish']);
  $server_tz = date_default_timezone_get();
  $current_server_time = date('Y-m-d H:i:s');
  ?>
  <div class="wrap">
    <h1>Offer Redirects</h1>

    <div style="background:#fff;border-left:4px solid #2271b1;padding:12px;margin:20px 0;">
      <p style="margin:0;"><strong>Server Timezone:</strong> <?php echo esc_html($server_tz); ?> (UTC)</p>
      <p style="margin:5px 0 0 0;"><strong>Current Server Time:</strong> <span id="server-time"><?php echo esc_html($current_server_time); ?></span></p>
      <p style="margin:5px 0 0 0;color:#646970;font-size:13px;">Times are saved in UTC but displayed in your local timezone for convenience.</p>
    </div>

    <form method="post" id="offer-redirect-form">
      <?php wp_nonce_field('save_offer_redirects', 'offer_redirect_nonce'); ?>
      <input type="hidden" name="timezone_offset" id="timezone-offset" value="">

      <!-- ===================== GLOBAL RULES ===================== -->
      <h2>Global Offer Redirect Rules</h2>
      <p>These rules apply globally. After the defined duration from the start time, all users will be redirected.</p>

      <table class="widefat responsive-table" id="redirect-rules-table">
        <thead>
          <tr>
            <th>Promoted Page</th>
            <th>Start Date & Time</th>
            <th>Duration (Minutes)</th>
            <th>Redirect To</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="redirect-rules-body">
          <?php if (!empty($rules_global)) :
            foreach ($rules_global as $index => $rule) :
              render_offer_redirect_row($pages, $index, $rule, 'global');
            endforeach;
          else :
            render_offer_redirect_row($pages, 0, [], 'global');
          endif; ?>
        </tbody>
      </table>
      <p><button type="button" class="button add-row" data-type="global">+ Add Global Rule</button></p>

      <hr style="margin: 30px 0;">

      <!-- ===================== USER RULES ===================== -->
      <h2>Individual User Redirect Rules</h2>
      <p>These rules apply individually per user. The validity starts when a user first visits the promoted page and lasts for the defined number of minutes.</p>

      <table class="widefat responsive-table" id="user-rules-table">
        <thead>
          <tr>
            <th>Promoted Page</th>
            <th>Validity (Minutes)</th>
            <th>Redirect To</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="user-rules-body">
          <?php if (!empty($rules_user)) :
            foreach ($rules_user as $index => $rule) :
              render_offer_redirect_row($pages, $index, $rule, 'user');
            endforeach;
          else :
            render_offer_redirect_row($pages, 0, [], 'user');
          endif; ?>
        </tbody>
      </table>
      <p><button type="button" class="button add-row" data-type="user">+ Add User Rule</button></p>

      <p style="margin-top: 20px;"><input type="submit" class="button button-primary" value="Save All Rules"></p>
    </form>
  </div>

  <style>
    .wp-core-ui select { max-width: 20rem !important; }
  </style>

  <script>
    document.getElementById('timezone-offset').value = new Date().getTimezoneOffset();

    // Convert UTC timestamps to local datetime for all datetime inputs
    function convertUtcToLocal() {
      document.querySelectorAll('.datetime-local-input').forEach(input => {
        const utcTimestamp = input.getAttribute('data-utc-timestamp');
        if (utcTimestamp && utcTimestamp !== '') {
          const utcDate = new Date(parseInt(utcTimestamp) * 1000);
          // Format: YYYY-MM-DDTHH:mm
          const year = utcDate.getFullYear();
          const month = String(utcDate.getMonth() + 1).padStart(2, '0');
          const day = String(utcDate.getDate()).padStart(2, '0');
          const hours = String(utcDate.getHours()).padStart(2, '0');
          const minutes = String(utcDate.getMinutes()).padStart(2, '0');
          input.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
      });
    }

    // Run on page load
    convertUtcToLocal();

    // Update server time every second
    setInterval(() => {
      const el = document.getElementById('server-time');
      if (!el) return;
      fetch(ajaxurl + '?action=get_server_time').then(r => r.text()).then(t => el.textContent = t);
    }, 1000);

    // Add new rows
    document.querySelectorAll('.add-row').forEach(btn => {
      btn.addEventListener('click', e => {
        const type = btn.dataset.type;
        const tbody = document.getElementById(type === 'global' ? 'redirect-rules-body' : 'user-rules-body');
        const index = tbody.querySelectorAll('tr').length;
        const ajaxData = new URLSearchParams({ action: 'render_offer_redirect_row', index, type });
        fetch(ajaxurl, { method: 'POST', body: ajaxData })
          .then(res => res.text())
          .then(html => {
            tbody.insertAdjacentHTML('beforeend', html);
            convertUtcToLocal(); // Convert newly added inputs
          });
      });
    });

    document.addEventListener('click', e => {
      if (e.target.classList.contains('remove-row')) e.target.closest('tr').remove();
    });
  </script>
<?php
}

// ------------------- ADMIN ROW RENDER -------------------
function render_offer_redirect_row($pages, $index, $rule = [], $type = 'global')
{
  $promoted = $rule['promoted'] ?? '';
  $redirect = $rule['redirect'] ?? '';

  if ($type === 'global') {
    $minutes = $rule['minutes'] ?? '';
    $start_value = '';
    $utc_timestamp = '';
    if (!empty($rule['start'])) {
      // Store UTC timestamp for JavaScript conversion
      $utc_timestamp = $rule['start'];
    }

    echo '<tr>';
    echo '<td><select name="rules_global[' . $index . '][promoted]" required>';
    echo '<option value="">-- Select Page --</option>';
    foreach ($pages as $p) {
      printf(
        '<option value="%s" %s>%s</option>',
        esc_attr($p->ID),
        selected($promoted, $p->ID, false),
        esc_html($p->post_title)
      );
    }
    echo '</select></td>';
    echo '<td><input type="datetime-local" name="rules_global[' . $index . '][start]" value="" data-utc-timestamp="' . esc_attr($utc_timestamp) . '" class="datetime-local-input" required /></td>';
    echo '<td><input type="number" name="rules_global[' . $index . '][minutes]" value="' . esc_attr($minutes) . '" min="1" style="width:100px;" required /></td>';
    echo '<td><select name="rules_global[' . $index . '][redirect]" required>';
    echo '<option value="">-- Select Page --</option>';
    foreach ($pages as $p) {
      printf(
        '<option value="%s" %s>%s</option>',
        esc_attr($p->ID),
        selected($redirect, $p->ID, false),
        esc_html($p->post_title)
      );
    }
    echo '</select></td>';
    echo '<td><button type="button" class="button remove-row">Remove</button></td>';
    echo '</tr>';
  } else {
    // User rules (no start time, just minutes validity)
    $minutes = $rule['minutes'] ?? '';

    echo '<tr>';
    echo '<td><select name="rules_user[' . $index . '][promoted]" required>';
    echo '<option value="">-- Select Page --</option>';
    foreach ($pages as $p) {
      printf(
        '<option value="%s" %s>%s</option>',
        esc_attr($p->ID),
        selected($promoted, $p->ID, false),
        esc_html($p->post_title)
      );
    }
    echo '</select></td>';
    echo '<td><input type="number" name="rules_user[' . $index . '][minutes]" value="' . esc_attr($minutes) . '" min="1" style="width:100px;" required /></td>';
    echo '<td><select name="rules_user[' . $index . '][redirect]" required>';
    echo '<option value="">-- Select Page --</option>';
    foreach ($pages as $p) {
      printf(
        '<option value="%s" %s>%s</option>',
        esc_attr($p->ID),
        selected($redirect, $p->ID, false),
        esc_html($p->post_title)
      );
    }
    echo '</select></td>';
    echo '<td><button type="button" class="button remove-row">Remove</button></td>';
    echo '</tr>';
  }
}

// ------------------- SAVE RULES -------------------
add_action('admin_init', function () {
  if (
    isset($_POST['offer_redirect_nonce']) &&
    wp_verify_nonce($_POST['offer_redirect_nonce'], 'save_offer_redirects')
  ) {
    $timezone_offset = intval($_POST['timezone_offset'] ?? 0);

    // Save Global Rules
    $rules_global = array_values($_POST['rules_global'] ?? []);
    foreach ($rules_global as $key => $rule) {
      if (!empty($rule['start'])) {
        $local_time = str_replace('T', ' ', $rule['start']);
        $local_timestamp = strtotime($local_time);
        $utc_timestamp = $local_timestamp + ($timezone_offset * 60);
        $rules_global[$key]['start'] = $utc_timestamp;
        error_log("Global Rule: Local=$local_time, UTC TS=$utc_timestamp (" . gmdate('Y-m-d H:i:s', $utc_timestamp) . ")");
      }
    }
    update_option('offer_redirect_rules', $rules_global);

    // Save User Rules
    $rules_user = array_values($_POST['rules_user'] ?? []);
    update_option('offer_redirect_user_rules', $rules_user);

    add_action('admin_notices', function () {
      echo '<div class="updated"><p>Redirect rules saved successfully!</p></div>';
    });
  }
});

// ------------------- AJAX FOR ADD ROW -------------------
add_action('wp_ajax_render_offer_redirect_row', function () {
  $index = intval($_POST['index']);
  $type = sanitize_text_field($_POST['type'] ?? 'global');
  $pages = get_pages(['post_status' => 'publish']);
  render_offer_redirect_row($pages, $index, [], $type);
  wp_die();
});

// ------------------- AJAX FOR SERVER TIME -------------------
add_action('wp_ajax_get_server_time', function () {
  echo gmdate('Y-m-d H:i:s');
  wp_die();
});

// ------------------- GET USER IDENTIFIER -------------------
function get_user_identifier() {
  // For logged-in users, use their ID
  if (is_user_logged_in()) {
    return 'user_' . get_current_user_id();
  }
  
  // For anonymous users, use a cookie-based identifier
  $cookie_name = 'offer_redirect_uid';
  if (isset($_COOKIE[$cookie_name])) {
    return $_COOKIE[$cookie_name];
  }
  
  // Create new identifier for anonymous user
  $identifier = 'anon_' . wp_generate_password(32, false);
  setcookie($cookie_name, $identifier, time() + (10 * 365 * 24 * 60 * 60), '/'); // 10 years
  return $identifier;
}

// ------------------- FRONTEND REDIRECT -------------------
add_action('template_redirect', function () {
  // Don't redirect in admin area or AJAX requests
  if (is_admin() || wp_doing_ajax()) {
    return;
  }

  // Don't redirect for logged-in admins/editors
  if (current_user_can('manage_options')) {
    error_log('Skipping redirect for admin user');
    return;
  }

  $current_page_id = get_queried_object_id();
  if (!$current_page_id) return;

  $now = time();

  // ===== CHECK GLOBAL RULES FIRST =====
  $rules_global = get_option('offer_redirect_rules', []);
  foreach ($rules_global as $rule) {
    $promoted = intval($rule['promoted'] ?? 0);
    $start_time = intval($rule['start'] ?? 0);
    $duration = intval($rule['minutes'] ?? 0);
    $expire_time = $start_time + ($duration * 60);

    error_log("Global Rule: Promoted=$promoted, Start=$start_time, Expire=$expire_time, Now=$now");

    if ($promoted === $current_page_id && $now >= $expire_time) {
      $redirect_id = intval($rule['redirect'] ?? 0);
      if (get_post_status($redirect_id)) {
        error_log("Global redirect triggered to: " . get_permalink($redirect_id));
        wp_redirect(get_permalink($redirect_id));
        exit;
      }
    }
  }

  // ===== CHECK USER-SPECIFIC RULES =====
  $rules_user = get_option('offer_redirect_user_rules', []);
  if (empty($rules_user)) return;

  $user_identifier = get_user_identifier();
  $user_visits = get_option('offer_redirect_user_visits', []);

  foreach ($rules_user as $rule) {
    $promoted = intval($rule['promoted'] ?? 0);
    $minutes = intval($rule['minutes'] ?? 0);
    $redirect_id = intval($rule['redirect'] ?? 0);

    if ($promoted !== $current_page_id) continue;

    $visit_key = $user_identifier . '_' . $promoted;

    // Check if user has visited this page before
    if (!isset($user_visits[$visit_key])) {
      // First visit - record the timestamp
      $user_visits[$visit_key] = $now;
      update_option('offer_redirect_user_visits', $user_visits);
      error_log("First visit recorded for $visit_key at $now");
      continue; // Don't redirect on first visit
    }

    // Check if validity period has expired
    $first_visit = intval($user_visits[$visit_key]);
    $validity_seconds = $minutes * 60;
    $expire_time = $first_visit + $validity_seconds;

    error_log("User Rule: Key=$visit_key, FirstVisit=$first_visit, Minutes=$minutes, Expire=$expire_time, Now=$now");

    if ($now >= $expire_time) {
      if (get_post_status($redirect_id)) {
        error_log("User redirect triggered to: " . get_permalink($redirect_id));
        wp_redirect(get_permalink($redirect_id));
        exit;
      }
    }
  }
});

// ------------------- PLUGIN ACTIVATION -------------------
register_activation_hook(__FILE__, function () {
  // Initialize options if they don't exist
  if (get_option('offer_redirect_rules') === false) {
    add_option('offer_redirect_rules', []);
  }
  if (get_option('offer_redirect_user_rules') === false) {
    add_option('offer_redirect_user_rules', []);
  }
  if (get_option('offer_redirect_user_visits') === false) {
    add_option('offer_redirect_user_visits', []);
  }
});

// ------------------- PLUGIN DEACTIVATION -------------------
register_deactivation_hook(__FILE__, function () {
  // Optional: Clear scheduled events if any
  // You can keep the data or clear it on deactivation
});

// ------------------- PLUGIN UNINSTALL -------------------
register_uninstall_hook(__FILE__, 'offer_redirects_uninstall');

function offer_redirects_uninstall() {
  // Remove all plugin data from database
  delete_option('offer_redirect_rules');
  delete_option('offer_redirect_user_rules');
  delete_option('offer_redirect_user_visits');
}