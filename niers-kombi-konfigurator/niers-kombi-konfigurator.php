<?php
/*
Plugin Name: Kombi-Tour Konfigurator
Plugin URI: https://freizeitexperten.de
Description: Ein dynamischer Konfigurator fuer Kombi-Touren mit Backend-Datenbank, Sonderregeln, Saison-Limits und flexiblen API-/Warenkorb-Endpunkten fuer mehrere Websites.
Version: 1.7.67
Author: Webagentur Geldern / Chris Derix
*/

// Verhindert direkten Aufruf
if (!defined('ABSPATH')) {
    exit;
}

class NiersKombiKonfigurator {
    const VERSION = '1.7.67';

    public function __construct() {
        // Backend: Meta Box für einfache ID Eingabe
        add_action('add_meta_boxes', array($this, 'add_tour_meta_box'));
        add_action('save_post', array($this, 'save_tour_meta_box'));

        // Backend: Einstellungen & Datenbank
        add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
        add_action('admin_init', array($this, 'maybe_run_plugin_migrations'), 5);
        add_action('admin_init', array($this, 'register_plugin_settings'));
        add_action('admin_post_niers_kombi_scan_services', array($this, 'handle_scan_services_request'));
        add_action('admin_post_niers_kombi_resync_catalog', array($this, 'handle_resync_catalog_request'));
        add_action('admin_post_niers_kombi_reclassify_catalog', array($this, 'handle_reclassify_catalog_request'));
        add_action('admin_post_niers_kombi_save_catalog', array($this, 'handle_save_catalog_request'));
        add_action('admin_post_niers_kombi_export_services', array($this, 'handle_export_services_request'));
        add_action('admin_post_niers_kombi_export_contacts', array($this, 'handle_export_contacts_request'));
        add_action('admin_post_niers_kombi_cleanup_catalog', array($this, 'handle_cleanup_catalog_request'));
        add_action('admin_post_niers_kombi_export_config', array($this, 'handle_export_config_request'));
        add_action('admin_post_niers_kombi_import_config', array($this, 'handle_import_config_request'));
        add_action('admin_post_niers_kombi_save_voucher', array($this, 'handle_save_voucher_request'));
        add_action('admin_post_niers_kombi_delete_voucher', array($this, 'handle_delete_voucher_request'));
        add_action('admin_post_niers_kombi_export_vouchers', array($this, 'handle_export_vouchers_request'));
        add_action('admin_post_niers_kombi_seed_portal_demo', array($this, 'handle_seed_portal_demo_request'));
        add_action('admin_post_niers_kombi_save_email_template', array($this, 'handle_save_email_template_request'));
        add_action('admin_post_niers_kombi_send_transactional_email_test', array($this, 'handle_send_transactional_email_test_request'));
        add_action('admin_post_niers_kombi_send_transactional_email_live', array($this, 'handle_send_transactional_email_live_request'));
        add_action('admin_post_niers_kombi_save_contact_step', array($this, 'handle_save_contact_step_request'));
        add_action('admin_post_nopriv_niers_kombi_save_contact_step', array($this, 'handle_save_contact_step_request'));
        add_action('template_redirect', array($this, 'guard_cart_contact_step'));
        add_action('wp_ajax_nopriv_niers_kombi_track_order', array($this, 'handle_track_order_request'));
        add_action('wp_ajax_niers_kombi_track_order', array($this, 'handle_track_order_request'));
        add_action('wp_ajax_nopriv_niers_kombi_track_funnel_event', array($this, 'handle_track_funnel_event_request'));
        add_action('wp_ajax_niers_kombi_track_funnel_event', array($this, 'handle_track_funnel_event_request'));
        add_action('admin_post_niers_kombi_toggle_order_test', array($this, 'handle_toggle_order_test_request'));
        add_action('init', array($this, 'maybe_schedule_transactional_email_processing'));
        add_action('niers_kombi_process_transactional_emails', array($this, 'handle_transactional_email_cron_event'));
        add_filter('cron_schedules', array($this, 'extend_transactional_email_cron_schedules'));

        // Frontend: Shortcode
        add_shortcode('niers_kombi_tour', array($this, 'render_shortcode'));
        add_shortcode('kombi_tour_konfigurator', array($this, 'render_shortcode'));
        add_shortcode('niers_kombi_kundendaten', array($this, 'render_contact_step_shortcode'));
        add_shortcode('fxp_booking_contact_step', array($this, 'render_contact_step_shortcode'));
        add_shortcode('fxp_customer_portal', array($this, 'render_customer_portal_shortcode'));
        add_shortcode('niers_kombi_kundenportal', array($this, 'render_customer_portal_shortcode'));
        add_action('wp_footer', array($this, 'render_checkout_prefill_script'), 99);
    }

    /**
     * ==========================================
     * 1. BACKEND: SETTINGS & DATENBANK
     * ==========================================
     */
    public function register_plugin_settings() {
        register_setting('niers_kombi_options', 'niers_kombi_tour_ids');
        
        $default_rules = "{\n  \"156\": {\n    \"min_pax\": 10\n  },\n  \"610\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"612\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"613\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"619\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"620\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"621\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"622\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"623\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"640\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  },\n  \"645\": {\n    \"special_mode\": \"gecco_chillbike_combo\",\n    \"gecco_strategy\": \"prefer_gecco_units\"\n  }\n}";
        add_option('niers_kombi_custom_rules', $default_rules);
        register_setting('niers_kombi_options', 'niers_kombi_custom_rules');
        
        // NEU: Saison-Einstellungen
        add_option('niers_kombi_season_start', '15.04.');
        register_setting('niers_kombi_options', 'niers_kombi_season_start');
        
        add_option('niers_kombi_season_end', '31.10.');
        register_setting('niers_kombi_options', 'niers_kombi_season_end');

        add_option('niers_kombi_api_base_url', $this->get_default_remote_api_base_url());
        register_setting('niers_kombi_options', 'niers_kombi_api_base_url');

        add_option('niers_kombi_primary_highlight_color', '#2e7d28');
        register_setting('niers_kombi_options', 'niers_kombi_primary_highlight_color');

        add_option('niers_kombi_secondary_highlight_color', '#DD8100');
        register_setting('niers_kombi_options', 'niers_kombi_secondary_highlight_color');

        add_option('niers_kombi_cart_endpoint', home_url('/shopping_cart.php'));
        register_setting('niers_kombi_options', 'niers_kombi_cart_endpoint');

        add_option('niers_kombi_cart_redirect', home_url('/warenkorb/'));
        register_setting('niers_kombi_options', 'niers_kombi_cart_redirect');

        add_option('niers_kombi_debug_mode', '0');
        register_setting('niers_kombi_options', 'niers_kombi_debug_mode');

        add_option('niers_kombi_contacts_module_enabled', '0');
        register_setting('niers_kombi_options', 'niers_kombi_contacts_module_enabled');

        add_option('niers_kombi_contact_step_enabled', '0');
        register_setting('niers_kombi_options', 'niers_kombi_contact_step_enabled');

        add_option('niers_kombi_contact_step_url', home_url('/buchungsdaten/'));
        register_setting('niers_kombi_options', 'niers_kombi_contact_step_url');

        add_option('niers_kombi_vouchers_module_enabled', '0');
        register_setting('niers_kombi_options', 'niers_kombi_vouchers_module_enabled');

        add_option('niers_kombi_dashboard_module_enabled', '0');
        register_setting('niers_kombi_options', 'niers_kombi_dashboard_module_enabled');

        add_option('niers_kombi_customer_portal_module_enabled', '0');
        register_setting('niers_kombi_options', 'niers_kombi_customer_portal_module_enabled');

        add_option('niers_kombi_customer_portal_url', home_url('/kundenportal/'));
        register_setting('niers_kombi_options', 'niers_kombi_customer_portal_url');

        add_option('niers_kombi_transactional_email_module_enabled', '0');
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_module_enabled');

        add_option('niers_kombi_transactional_email_test_mode', '1');
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_test_mode');

        add_option('niers_kombi_transactional_email_test_recipient', get_option('admin_email', ''));
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_test_recipient');

        add_option('niers_kombi_transactional_email_runner_interval', 'hourly');
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_runner_interval');

        add_option('niers_kombi_transactional_email_pre_send_time', '09:00');
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_pre_send_time');

        add_option('niers_kombi_transactional_email_post_send_time', '09:00');
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_post_send_time');

        add_option('niers_kombi_transactional_email_review_link', '');
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_review_link');

        add_option('niers_kombi_transactional_email_contact_phone', '');
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_contact_phone');

        add_option('niers_kombi_transactional_email_contact_email', get_option('admin_email', ''));
        register_setting('niers_kombi_options', 'niers_kombi_transactional_email_contact_email');

        add_option('niers_kombi_email_templates', array());

        add_option('niers_kombi_test_order_emails', '');
        register_setting('niers_kombi_options', 'niers_kombi_test_order_emails');

        add_option('niers_kombi_service_catalog', array());
    }

    private function is_contacts_module_enabled() {
        return get_option('niers_kombi_contacts_module_enabled', '0') === '1';
    }

    private function is_contact_step_enabled() {
        return $this->is_contacts_module_enabled() && get_option('niers_kombi_contact_step_enabled', '0') === '1';
    }

    private function ensure_contact_session_started() {
        if (!session_id() && !headers_sent()) {
            session_start();
        }
    }

    private function get_contact_step_cookie_name() {
        return 'fxp_contact_step_completed';
    }

    private function get_contact_step_cookie_ttl() {
        return 12 * HOUR_IN_SECONDS;
    }

    private function build_contact_step_cookie_value($contact_id, $timestamp = null) {
        $timestamp = $timestamp ? intval($timestamp) : time();
        $contact_id = max(0, intval($contact_id));
        $signature = wp_hash($timestamp . '|' . $contact_id . '|fxp_contact_step_completed');
        return $timestamp . ':' . $contact_id . ':' . $signature;
    }

    private function is_valid_contact_step_cookie() {
        $cookie_name = $this->get_contact_step_cookie_name();
        if (empty($_COOKIE[$cookie_name])) {
            return false;
        }

        $raw = sanitize_text_field(wp_unslash($_COOKIE[$cookie_name]));
        $parts = explode(':', $raw, 3);
        if (count($parts) !== 3) {
            return false;
        }

        $timestamp = intval($parts[0]);
        $contact_id = intval($parts[1]);
        $signature = (string) $parts[2];
        if ($timestamp <= 0 || (time() - $timestamp) > $this->get_contact_step_cookie_ttl()) {
            return false;
        }

        $expected = wp_hash($timestamp . '|' . $contact_id . '|fxp_contact_step_completed');
        return hash_equals($expected, $signature);
    }

    private function set_contact_step_cookie($contact_id) {
        if (headers_sent()) {
            return;
        }

        $cookie_name = $this->get_contact_step_cookie_name();
        $ttl = $this->get_contact_step_cookie_ttl();
        $value = $this->build_contact_step_cookie_value($contact_id);
        $path = defined('COOKIEPATH') && COOKIEPATH ? COOKIEPATH : '/';
        $domain = defined('COOKIE_DOMAIN') ? COOKIE_DOMAIN : '';

        setcookie($cookie_name, $value, time() + $ttl, $path, $domain, is_ssl(), true);
        $_COOKIE[$cookie_name] = $value;
    }

    private function is_url_current_request($url) {
        if (empty($url)) return false;

        $target = wp_parse_url($url);
        if (empty($target['path'])) return false;

        $request_path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH) : '';
        if ($request_path === null) $request_path = '';

        return untrailingslashit($request_path) === untrailingslashit($target['path']);
    }

    public function guard_cart_contact_step() {
        if (!$this->is_contact_step_enabled() || is_admin() || wp_doing_ajax()) {
            return;
        }

        $cart_redirect = get_option('niers_kombi_cart_redirect', home_url('/warenkorb/'));
        if (!$this->is_url_current_request($cart_redirect)) {
            return;
        }

        $this->ensure_contact_session_started();
        $completed_at = isset($_SESSION['fxp_contact_step_completed_at']) ? intval($_SESSION['fxp_contact_step_completed_at']) : 0;
        $is_recent = $completed_at > 0 && (time() - $completed_at) < $this->get_contact_step_cookie_ttl();

        if ($is_recent || $this->is_valid_contact_step_cookie()) {
            return;
        }

        $contact_step_url = get_option('niers_kombi_contact_step_url', home_url('/buchungsdaten/'));
        $url = add_query_arg('fxp_return', $cart_redirect, $contact_step_url);
        wp_safe_redirect($url);
        exit;
    }

    private function is_vouchers_module_enabled() {
        return get_option('niers_kombi_vouchers_module_enabled', '0') === '1';
    }

    private function is_dashboard_module_enabled() {
        return get_option('niers_kombi_dashboard_module_enabled', '0') === '1';
    }

    private function is_customer_portal_module_enabled() {
        return get_option('niers_kombi_customer_portal_module_enabled', '0') === '1';
    }

    private function is_transactional_email_module_enabled() {
        return get_option('niers_kombi_transactional_email_module_enabled', '0') === '1';
    }

    private function is_transactional_email_test_mode_enabled() {
        return get_option('niers_kombi_transactional_email_test_mode', '1') === '1';
    }

    private function get_transactional_email_test_recipient() {
        return sanitize_email((string) get_option('niers_kombi_transactional_email_test_recipient', get_option('admin_email', '')));
    }

    private function normalize_transactional_email_time_value($value, $default = '09:00') {
        $value = trim((string) $value);
        if (preg_match('/^\d{2}:\d{2}$/', $value)) {
            list($hours, $minutes) = array_map('intval', explode(':', $value));
            if ($hours >= 0 && $hours <= 23 && $minutes >= 0 && $minutes <= 59) {
                return sprintf('%02d:%02d', $hours, $minutes);
            }
        }
        return $default;
    }

    private function get_transactional_email_runner_interval_options() {
        return array(
            'every_15_minutes' => 'Alle 15 Minuten',
            'every_30_minutes' => 'Alle 30 Minuten',
            'hourly' => 'Stündlich',
        );
    }

    private function get_transactional_email_runner_interval() {
        $value = sanitize_key((string) get_option('niers_kombi_transactional_email_runner_interval', 'hourly'));
        return isset($this->get_transactional_email_runner_interval_options()[$value]) ? $value : 'hourly';
    }

    private function get_transactional_email_pre_send_time() {
        return $this->normalize_transactional_email_time_value(get_option('niers_kombi_transactional_email_pre_send_time', '09:00'), '09:00');
    }

    private function get_transactional_email_post_send_time() {
        return $this->normalize_transactional_email_time_value(get_option('niers_kombi_transactional_email_post_send_time', '09:00'), '09:00');
    }

    private function get_transactional_email_review_link() {
        return esc_url_raw((string) get_option('niers_kombi_transactional_email_review_link', ''));
    }

    private function get_transactional_email_contact_phone() {
        return sanitize_text_field((string) get_option('niers_kombi_transactional_email_contact_phone', ''));
    }

    private function get_transactional_email_contact_email() {
        return sanitize_email((string) get_option('niers_kombi_transactional_email_contact_email', get_option('admin_email', '')));
    }

    private function get_wp_current_timestamp() {
        return current_datetime()->getTimestamp();
    }

    private function get_wp_today_start_timestamp() {
        return current_datetime()->setTime(0, 0, 0)->getTimestamp();
    }

    private function parse_wp_local_datetime_timestamp($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return false;
        }

        if (preg_match('/^\d+$/', $value)) {
            return intval($value);
        }

        $timezone = wp_timezone();
        $normalized = str_replace('T', ' ', $value);
        foreach (array('Y-m-d H:i:s', 'Y-m-d H:i', 'Y-m-d') as $format) {
            $dt = DateTimeImmutable::createFromFormat($format, $normalized, $timezone);
            if ($dt instanceof DateTimeImmutable) {
                return $dt->getTimestamp();
            }
        }

        $fallback = strtotime($normalized);
        return $fallback ?: false;
    }

    private function get_test_order_email_list() {
        $raw = (string) get_option('niers_kombi_test_order_emails', '');
        $parts = preg_split('/[\r\n,;]+/', $raw);
        $emails = array();
        foreach ($parts as $part) {
            $email = sanitize_email(trim((string) $part));
            if ($email !== '') {
                $emails[strtolower($email)] = strtolower($email);
            }
        }
        return array_values($emails);
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'Kombi-Touren Übersicht', 
            'Kombi-Touren', 
            'manage_options', 
            'niers-kombi-settings', 
            array($this, 'display_plugin_admin_page'), 
            'dashicons-tickets-alt', 
            30
        );
        add_submenu_page('niers-kombi-settings', 'Übersicht', 'Übersicht', 'manage_options', 'niers-kombi-settings', array($this, 'display_plugin_admin_page'));
        if ($this->is_dashboard_module_enabled()) {
            add_submenu_page('niers-kombi-settings', 'Dashboard', 'Dashboard', 'manage_options', 'niers-kombi-dashboard', array($this, 'display_plugin_admin_page'));
            add_submenu_page('niers-kombi-settings', 'Checkout-Funnel', 'Checkout-Funnel', 'manage_options', 'niers-kombi-funnel', array($this, 'display_plugin_admin_page'));
        }
        if ($this->is_customer_portal_module_enabled()) {
            add_submenu_page('niers-kombi-settings', 'Kundenportal', 'Kundenportal', 'manage_options', 'niers-kombi-portal', array($this, 'display_plugin_admin_page'));
        }
        add_submenu_page('niers-kombi-settings', 'Leistungskatalog', 'Leistungskatalog', 'manage_options', 'niers-kombi-catalog', array($this, 'display_plugin_admin_page'));
        add_submenu_page('niers-kombi-settings', 'Varianten', 'Varianten', 'manage_options', 'niers-kombi-variants', array($this, 'display_plugin_admin_page'));
        if ($this->is_contacts_module_enabled()) {
            add_submenu_page('niers-kombi-settings', 'Kundendaten', 'Kundendaten', 'manage_options', 'niers-kombi-contacts', array($this, 'display_plugin_admin_page'));
        }
        if ($this->is_transactional_email_module_enabled()) {
            add_submenu_page('niers-kombi-settings', 'Transaktionale E-Mails', 'Transaktionale E-Mails', 'manage_options', 'niers-kombi-emails', array($this, 'display_plugin_admin_page'));
        }
        if ($this->is_vouchers_module_enabled()) {
            add_submenu_page('niers-kombi-settings', 'Gutscheine', 'Gutscheine', 'manage_options', 'niers-kombi-vouchers', array($this, 'display_plugin_admin_page'));
        }
        add_submenu_page('niers-kombi-settings', 'Scanner & Resync', 'Scanner & Resync', 'manage_options', 'niers-kombi-scanner', array($this, 'display_plugin_admin_page'));
        add_submenu_page('niers-kombi-settings', 'Einstellungen', 'Einstellungen', 'manage_options', 'niers-kombi-options', array($this, 'display_plugin_admin_page'));
    }

    private function get_current_admin_subpage() {
        $page = isset($_REQUEST['page']) ? sanitize_key(wp_unslash($_REQUEST['page'])) : 'niers-kombi-settings';
        $allowed = array('niers-kombi-settings', 'niers-kombi-dashboard', 'niers-kombi-funnel', 'niers-kombi-portal', 'niers-kombi-catalog', 'niers-kombi-variants', 'niers-kombi-contacts', 'niers-kombi-emails', 'niers-kombi-vouchers', 'niers-kombi-scanner', 'niers-kombi-options');
        return in_array($page, $allowed, true) ? $page : 'niers-kombi-settings';
    }

    private function get_admin_page_url($page_slug, $args = array()) {
        return add_query_arg(array_merge(array('page' => $page_slug), $args), admin_url('admin.php'));
    }

    private function get_service_catalog() {
        $catalog = get_option('niers_kombi_service_catalog', array());
        return is_array($catalog) ? $catalog : array();
    }

    private function save_service_catalog($catalog) {
        ksort($catalog, SORT_NUMERIC);
        update_option('niers_kombi_service_catalog', $catalog, false);
    }

    private function get_default_remote_api_base_url() {
        return 'https://checkin.freizeitexperten.de/shop';
    }

    private function get_remote_api_base_url() {
        $base_url = trim((string) get_option('niers_kombi_api_base_url', $this->get_default_remote_api_base_url()));
        if ($base_url === '') {
            $base_url = $this->get_default_remote_api_base_url();
        }

        return untrailingslashit($base_url);
    }

    private function get_remote_service_data_url($service_id = null) {
        $url = $this->get_remote_api_base_url() . '/service_data.php';
        if ($service_id !== null) {
            $url = add_query_arg('service_id', intval($service_id), $url);
        }

        return $url;
    }

    private function get_remote_quota_data_url() {
        return $this->get_remote_api_base_url() . '/quota_data.php';
    }

    private function sanitize_hex_color_with_default($value, $default) {
        $default = strtoupper((string) $default);
        $value = trim((string) $value);
        $sanitized = function_exists('sanitize_hex_color') ? sanitize_hex_color($value) : null;

        if (!$sanitized && preg_match('/^#?[0-9a-fA-F]{6}$/', $value)) {
            $sanitized = '#' . ltrim($value, '#');
        }

        return $sanitized ? strtoupper($sanitized) : $default;
    }

    private function hex_to_rgb($hex_color) {
        $hex_color = ltrim($this->sanitize_hex_color_with_default($hex_color, '#000000'), '#');

        return array(
            hexdec(substr($hex_color, 0, 2)),
            hexdec(substr($hex_color, 2, 2)),
            hexdec(substr($hex_color, 4, 2)),
        );
    }

    private function hex_to_rgb_string($hex_color) {
        return implode(', ', $this->hex_to_rgb($hex_color));
    }

    private function blend_hex_colors($base_hex, $target_hex, $target_ratio) {
        $target_ratio = max(0, min(1, floatval($target_ratio)));
        $base_rgb = $this->hex_to_rgb($base_hex);
        $target_rgb = $this->hex_to_rgb($target_hex);
        $blended = array();

        for ($i = 0; $i < 3; $i++) {
            $blended[$i] = (int) round(($base_rgb[$i] * (1 - $target_ratio)) + ($target_rgb[$i] * $target_ratio));
        }

        return sprintf('#%02X%02X%02X', $blended[0], $blended[1], $blended[2]);
    }

    private function get_config_option_keys() {
        return array(
            'niers_kombi_tour_ids',
            'niers_kombi_custom_rules',
            'niers_kombi_season_start',
            'niers_kombi_season_end',
            'niers_kombi_api_base_url',
            'niers_kombi_primary_highlight_color',
            'niers_kombi_secondary_highlight_color',
            'niers_kombi_cart_endpoint',
            'niers_kombi_cart_redirect',
            'niers_kombi_debug_mode',
            'niers_kombi_contacts_module_enabled',
            'niers_kombi_contact_step_enabled',
            'niers_kombi_contact_step_url',
            'niers_kombi_vouchers_module_enabled',
            'niers_kombi_dashboard_module_enabled',
            'niers_kombi_customer_portal_module_enabled',
            'niers_kombi_customer_portal_url',
            'niers_kombi_transactional_email_module_enabled',
            'niers_kombi_transactional_email_test_mode',
            'niers_kombi_transactional_email_test_recipient',
            'niers_kombi_transactional_email_runner_interval',
            'niers_kombi_transactional_email_pre_send_time',
            'niers_kombi_transactional_email_post_send_time',
            'niers_kombi_transactional_email_review_link',
            'niers_kombi_transactional_email_contact_phone',
            'niers_kombi_transactional_email_contact_email',
            'niers_kombi_test_order_emails',
            'niers_kombi_service_catalog',
        );
    }

    private function build_config_export_payload() {
        $options = array();
        foreach ($this->get_config_option_keys() as $option_key) {
            $options[$option_key] = get_option($option_key);
        }

        return array(
            'plugin' => 'kombi-tour-konfigurator',
            'version' => self::VERSION,
            'exported_at' => current_time('mysql'),
            'source_site' => home_url('/'),
            'options' => $options,
        );
    }

    private function normalize_imported_config_payload($payload) {
        if (!is_array($payload) || empty($payload['options']) || !is_array($payload['options'])) {
            return null;
        }

        $options = $payload['options'];
        $normalized = array();
        $normalized['niers_kombi_tour_ids'] = isset($options['niers_kombi_tour_ids']) ? sanitize_text_field((string) $options['niers_kombi_tour_ids']) : '';
        $normalized['niers_kombi_custom_rules'] = isset($options['niers_kombi_custom_rules']) ? (string) $options['niers_kombi_custom_rules'] : '{}';
        $normalized['niers_kombi_season_start'] = isset($options['niers_kombi_season_start']) ? sanitize_text_field((string) $options['niers_kombi_season_start']) : '15.04.';
        $normalized['niers_kombi_season_end'] = isset($options['niers_kombi_season_end']) ? sanitize_text_field((string) $options['niers_kombi_season_end']) : '31.10.';
        $normalized['niers_kombi_api_base_url'] = isset($options['niers_kombi_api_base_url']) ? esc_url_raw((string) $options['niers_kombi_api_base_url']) : $this->get_default_remote_api_base_url();
        $normalized['niers_kombi_primary_highlight_color'] = $this->sanitize_hex_color_with_default(isset($options['niers_kombi_primary_highlight_color']) ? $options['niers_kombi_primary_highlight_color'] : '', '#2E7D28');
        $normalized['niers_kombi_secondary_highlight_color'] = $this->sanitize_hex_color_with_default(isset($options['niers_kombi_secondary_highlight_color']) ? $options['niers_kombi_secondary_highlight_color'] : '', '#DD8100');
        $normalized['niers_kombi_cart_endpoint'] = isset($options['niers_kombi_cart_endpoint']) ? esc_url_raw((string) $options['niers_kombi_cart_endpoint']) : home_url('/shopping_cart.php');
        $normalized['niers_kombi_cart_redirect'] = isset($options['niers_kombi_cart_redirect']) ? esc_url_raw((string) $options['niers_kombi_cart_redirect']) : home_url('/warenkorb/');
        $normalized['niers_kombi_debug_mode'] = (!empty($options['niers_kombi_debug_mode']) && (string) $options['niers_kombi_debug_mode'] === '1') ? '1' : '0';
        $normalized['niers_kombi_contacts_module_enabled'] = (!empty($options['niers_kombi_contacts_module_enabled']) && (string) $options['niers_kombi_contacts_module_enabled'] === '1') ? '1' : '0';
        $normalized['niers_kombi_contact_step_enabled'] = (!empty($options['niers_kombi_contact_step_enabled']) && (string) $options['niers_kombi_contact_step_enabled'] === '1') ? '1' : '0';
        $normalized['niers_kombi_contact_step_url'] = isset($options['niers_kombi_contact_step_url']) ? esc_url_raw((string) $options['niers_kombi_contact_step_url']) : home_url('/buchungsdaten/');
        $normalized['niers_kombi_vouchers_module_enabled'] = (!empty($options['niers_kombi_vouchers_module_enabled']) && (string) $options['niers_kombi_vouchers_module_enabled'] === '1') ? '1' : '0';
        $normalized['niers_kombi_dashboard_module_enabled'] = (!empty($options['niers_kombi_dashboard_module_enabled']) && (string) $options['niers_kombi_dashboard_module_enabled'] === '1') ? '1' : '0';
        $normalized['niers_kombi_customer_portal_module_enabled'] = (!empty($options['niers_kombi_customer_portal_module_enabled']) && (string) $options['niers_kombi_customer_portal_module_enabled'] === '1') ? '1' : '0';
        $normalized['niers_kombi_customer_portal_url'] = isset($options['niers_kombi_customer_portal_url']) ? esc_url_raw((string) $options['niers_kombi_customer_portal_url']) : home_url('/kundenportal/');
        $normalized['niers_kombi_transactional_email_module_enabled'] = (!empty($options['niers_kombi_transactional_email_module_enabled']) && (string) $options['niers_kombi_transactional_email_module_enabled'] === '1') ? '1' : '0';
        $normalized['niers_kombi_transactional_email_test_mode'] = (!empty($options['niers_kombi_transactional_email_test_mode']) && (string) $options['niers_kombi_transactional_email_test_mode'] === '1') ? '1' : '0';
        $normalized['niers_kombi_transactional_email_test_recipient'] = isset($options['niers_kombi_transactional_email_test_recipient']) ? sanitize_email((string) $options['niers_kombi_transactional_email_test_recipient']) : sanitize_email((string) get_option('admin_email', ''));
        $runner_interval = isset($options['niers_kombi_transactional_email_runner_interval']) ? sanitize_key((string) $options['niers_kombi_transactional_email_runner_interval']) : 'hourly';
        $normalized['niers_kombi_transactional_email_runner_interval'] = isset($this->get_transactional_email_runner_interval_options()[$runner_interval]) ? $runner_interval : 'hourly';
        $normalized['niers_kombi_transactional_email_pre_send_time'] = $this->normalize_transactional_email_time_value(isset($options['niers_kombi_transactional_email_pre_send_time']) ? $options['niers_kombi_transactional_email_pre_send_time'] : '09:00', '09:00');
        $normalized['niers_kombi_transactional_email_post_send_time'] = $this->normalize_transactional_email_time_value(isset($options['niers_kombi_transactional_email_post_send_time']) ? $options['niers_kombi_transactional_email_post_send_time'] : '09:00', '09:00');
        $normalized['niers_kombi_transactional_email_review_link'] = isset($options['niers_kombi_transactional_email_review_link']) ? esc_url_raw((string) $options['niers_kombi_transactional_email_review_link']) : '';
        $normalized['niers_kombi_transactional_email_contact_phone'] = isset($options['niers_kombi_transactional_email_contact_phone']) ? sanitize_text_field((string) $options['niers_kombi_transactional_email_contact_phone']) : '';
        $normalized['niers_kombi_transactional_email_contact_email'] = isset($options['niers_kombi_transactional_email_contact_email']) ? sanitize_email((string) $options['niers_kombi_transactional_email_contact_email']) : sanitize_email((string) get_option('admin_email', ''));
        $normalized['niers_kombi_test_order_emails'] = isset($options['niers_kombi_test_order_emails']) ? sanitize_textarea_field((string) $options['niers_kombi_test_order_emails']) : '';
        $normalized['niers_kombi_service_catalog'] = isset($options['niers_kombi_service_catalog']) && is_array($options['niers_kombi_service_catalog'])
            ? $options['niers_kombi_service_catalog']
            : array();

        return $normalized;
    }

    private function get_catalog_product_type_options() {
        return array(
            'unknown' => 'Unbekannt',
            'kombi' => 'Kombi-Tour',
            'paddel' => 'Paddel-Tour',
            'activity' => 'Aktivitäten',
            'stay' => 'Unterkunft',
            'event' => 'Event / Sonstiges',
        );
    }

    private function get_catalog_status_options() {
        return array(
            'neu' => 'Neu',
            'geprueft' => 'Geprüft',
            'aktiv' => 'Aktiv',
            'ignorieren' => 'Ignorieren',
        );
    }

    private function get_default_custom_rule_overrides() {
        return array(
            '202' => array(
                'linked_services' => array(
                    array('id' => 202, 'label' => 'Übernachtung im Tipidorf'),
                    array('id' => 203, 'label' => 'Übernachtung im Schloss'),
                ),
            ),
            '203' => array(
                'linked_services' => array(
                    array('id' => 202, 'label' => 'Übernachtung im Tipidorf'),
                    array('id' => 203, 'label' => 'Übernachtung im Schloss'),
                ),
            ),
            '513' => array('special_mode' => 'auto_double_rooms'),
            '514' => array('special_mode' => 'auto_lowest_room'),
            '156' => array('min_pax' => 10),
            '610' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '612' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '613' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '619' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '620' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '621' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '622' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '623' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '640' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
            '645' => array('special_mode' => 'gecco_chillbike_combo', 'gecco_strategy' => 'prefer_gecco_units'),
        );
    }

    private function normalize_manual_times_value($raw_value) {
        $times = array();
        $parts = array_map('trim', explode(',', (string)$raw_value));
        foreach ($parts as $part) {
            if ($part === '') continue;
            if (preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $part, $matches)) {
                $hours = max(0, min(23, intval($matches[1])));
                $minutes = max(0, min(59, intval($matches[2])));
                $times[] = sprintf('%02d:%02d', $hours, $minutes);
            }
        }
        $times = array_values(array_unique($times));
        return implode(',', $times);
    }

    private function normalize_linked_services_value($raw_value) {
        $linked = array();
        $parts = preg_split('/[\r\n,]+/', (string)$raw_value);
        foreach ($parts as $part) {
            $part = trim($part);
            if ($part === '') continue;

            $id = 0;
            $label = '';

            if (preg_match('/^(\d+)\s*[:=|-]\s*(.+)$/u', $part, $matches)) {
                $id = intval($matches[1]);
                $label = sanitize_text_field(trim($matches[2]));
            } elseif (preg_match('/^(\d+)\s+(.+)$/u', $part, $matches)) {
                $id = intval($matches[1]);
                $label = sanitize_text_field(trim($matches[2]));
            } else {
                $id = intval($part);
                $label = '';
            }

            if ($id < 1) continue;
            if ($label === '') $label = 'Variante ' . $id;
            $linked[] = array(
                'id' => $id,
                'label' => $label,
            );
        }

        $unique = array();
        foreach ($linked as $item) {
            $unique[$item['id']] = $item;
        }

        return array_values($unique);
    }

    private function format_linked_services_value($linked_services) {
        if (!is_array($linked_services) || empty($linked_services)) return '';
        $lines = array();
        foreach ($linked_services as $item) {
            $id = isset($item['id']) ? intval($item['id']) : 0;
            if ($id < 1) continue;
            $label = isset($item['label']) ? trim((string)$item['label']) : '';
            $lines[] = $label !== '' ? ($id . '=' . $label) : (string)$id;
        }
        return implode("\n", $lines);
    }

    private function get_linked_group_ids($service_id, $record) {
        $ids = array(intval($service_id));
        if (!empty($record['linked_services']) && is_array($record['linked_services'])) {
            foreach ($record['linked_services'] as $item) {
                if (empty($item['id'])) continue;
                $ids[] = intval($item['id']);
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));
        sort($ids, SORT_NUMERIC);
        return $ids;
    }

    private function build_canonical_linked_services($service_id, $linked_services, $catalog) {
        $labels = array();
        foreach ((array)$linked_services as $item) {
            $id = isset($item['id']) ? intval($item['id']) : 0;
            if ($id < 1) continue;
            $label = isset($item['label']) ? sanitize_text_field(trim((string)$item['label'])) : '';
            if ($label === '' && !empty($catalog[$id]['name'])) $label = sanitize_text_field($catalog[$id]['name']);
            if ($label === '') $label = 'Variante ' . $id;
            $labels[$id] = $label;
        }

        $service_id = intval($service_id);
        if ($service_id > 0 && !isset($labels[$service_id])) {
            $self_label = !empty($catalog[$service_id]['name']) ? sanitize_text_field($catalog[$service_id]['name']) : ('Variante ' . $service_id);
            $labels[$service_id] = $self_label;
        }

        ksort($labels, SORT_NUMERIC);

        $canonical = array();
        foreach ($labels as $id => $label) {
            $canonical[] = array(
                'id' => intval($id),
                'label' => $label,
            );
        }

        return $canonical;
    }

    private function synchronize_linked_service_group($catalog, $service_id, $new_linked_services, $previous_linked_services = array()) {
        $service_id = intval($service_id);
        if ($service_id < 1) return $catalog;

        $old_record = array('linked_services' => is_array($previous_linked_services) ? $previous_linked_services : array());
        $old_group_ids = $this->get_linked_group_ids($service_id, $old_record);
        $canonical = $this->build_canonical_linked_services($service_id, $new_linked_services, $catalog);
        $new_record = array('linked_services' => $canonical);
        $new_group_ids = !empty($new_linked_services) ? $this->get_linked_group_ids($service_id, $new_record) : array($service_id);

        $affected_ids = array_values(array_unique(array_merge($old_group_ids, $new_group_ids)));
        foreach ($affected_ids as $affected_id) {
            if (!isset($catalog[$affected_id]) || !is_array($catalog[$affected_id])) continue;

            if (in_array($affected_id, $new_group_ids, true) && count($new_group_ids) > 1) {
                $catalog[$affected_id]['linked_services'] = $canonical;
            } else {
                $catalog[$affected_id]['linked_services'] = array();
            }
        }

        return $catalog;
    }

    private function is_empty_catalog_record($record) {
        $name = isset($record['name']) ? trim((string)$record['name']) : '';
        $begin_time = isset($record['begin_time']) ? trim((string)$record['begin_time']) : '';
        $station_summary = isset($record['station_summary']) ? trim((string)$record['station_summary']) : '';
        $adult_price = isset($record['adult_price_brutto']) ? trim((string)$record['adult_price_brutto']) : '';
        $min_price = isset($record['min_price_brutto']) ? trim((string)$record['min_price_brutto']) : '';

        return empty($record['has_quotas']) &&
            $name === '' &&
            $begin_time === '' &&
            $station_summary === '' &&
            $adult_price === '' &&
            $min_price === '';
    }

    private function get_catalog_tab_definitions($catalog) {
        $tabs = array(
            'all' => array('label' => 'Alle', 'count' => count($catalog)),
            'kombi' => array('label' => 'Kombi', 'count' => 0),
            'paddel' => array('label' => 'Paddeln', 'count' => 0),
            'activity' => array('label' => 'Aktivitäten', 'count' => 0),
            'linked' => array('label' => 'Varianten', 'count' => 0),
            'stay' => array('label' => 'Unterkunft', 'count' => 0),
            'event' => array('label' => 'Event', 'count' => 0),
            'unknown' => array('label' => 'Unbekannt', 'count' => 0),
            'no_quotas' => array('label' => 'Ohne Quoten', 'count' => 0),
        );

        foreach ($catalog as $record) {
            $type = isset($record['product_type']) ? $record['product_type'] : 'unknown';
            if (isset($tabs[$type])) $tabs[$type]['count']++;
            if (!empty($record['linked_services'])) $tabs['linked']['count']++;
            if (empty($record['has_quotas'])) $tabs['no_quotas']['count']++;
        }

        return $tabs;
    }

    private function get_dummy_quotas_begin_time($service_data) {
        $y = (int)date('Y');
        if ((int)date('m') > 9) $y++;
        $dummy_date = $y . '-07-15';
        $dummy_time = !empty($service_data['begin_time']) ? $service_data['begin_time'] : '10:00:00';
        return $dummy_date . ' ' . $dummy_time;
    }

    private function get_station_blueprint($service_data, $quota_names = array()) {
        $stations = array();

        for ($i = 1; $i <= 15; $i++) {
            $key = "s{$i}_quotas";
            if (empty($service_data[$key])) continue;

            $ids = is_array($service_data[$key]) ? $service_data[$key] : explode(',', $service_data[$key]);
            $names_arr = array();
            foreach ($ids as $qid) {
                $qid = trim((string)$qid);
                if ($qid === '') continue;
                $names_arr[] = isset($quota_names[$qid]) ? $quota_names[$qid] : "ID {$qid}";
            }

            if (!empty($names_arr)) {
                $stations[] = array(
                    'step' => $i,
                    'items' => $names_arr,
                );
            }
        }

        return $stations;
    }

    private function get_station_summary_text($stations) {
        if (empty($stations)) return '';

        $parts = array();
        foreach ($stations as $station) {
            $parts[] = 'S' . $station['step'] . ': ' . implode(', ', $station['items']);
        }

        return implode(' | ', $parts);
    }

    private function infer_product_type($service_data, $stations, $quota_names = array()) {
        $name = isset($service_data['name']) ? strtolower($service_data['name']) : '';
        $station_count = count($stations);
        $quota_text = strtolower(implode(' ', array_values($quota_names)));

        if (
            strpos($name, 'hotel') !== false ||
            strpos($name, 'zimmer') !== false ||
            strpos($name, 'zelt') !== false ||
            strpos($name, 'tipi') !== false ||
            strpos($name, 'unterkunft') !== false
        ) {
            return 'stay';
        }

        if ($station_count >= 2) {
            return 'kombi';
        }

        if (
            strpos($name, 'paddel') !== false ||
            strpos($name, 'kajak') !== false ||
            strpos($name, 'kanu') !== false ||
            strpos($name, 'kanadier') !== false ||
            strpos($quota_text, 'kajak') !== false ||
            strpos($quota_text, 'kanu') !== false ||
            strpos($quota_text, 'kanadier') !== false ||
            strpos($quota_text, 'boot') !== false
        ) {
            return 'paddel';
        }

        if (
            strpos($name, 'bogenschieß') !== false ||
            strpos($name, 'bogenschiess') !== false ||
            strpos($name, 'bogen') !== false ||
            strpos($name, '3d parcour') !== false ||
            strpos($name, '3d-parcour') !== false ||
            strpos($name, 'geccomobil') !== false ||
            strpos($name, 'mindfall') !== false ||
            strpos($name, 'escape') !== false ||
            strpos($quota_text, 'bogen') !== false ||
            strpos($quota_text, 'mindfall') !== false
        ) {
            return 'activity';
        }

        if ($station_count === 1 || !empty($quota_names)) {
            return 'event';
        }

        return 'unknown';
    }

    private function build_service_record($service_id, $service_data, $existing_record = array()) {
        $quotas_begin_time = $this->get_dummy_quotas_begin_time($service_data);
        $overnight_stays = !empty($service_data['overnight_stays']) ? $service_data['overnight_stays'] : null;
        $quota_data = $this->fetch_quota_data($service_id, $quotas_begin_time, $overnight_stays);
        $quota_names = array();

        if ($quota_data) {
            foreach ($quota_data as $k => $v) {
                if (strpos($k, '_quotas') === false || !is_array($v)) continue;
                foreach ($v as $q) {
                    $qid = isset($q['id']) ? (string)$q['id'] : (isset($q['quota_id']) ? (string)$q['quota_id'] : '');
                    if ($qid !== '' && isset($q['name'])) $quota_names[$qid] = $q['name'];
                }
            }
        }

        $stations = $this->get_station_blueprint($service_data, $quota_names);
        $inferred_type = $this->infer_product_type($service_data, $stations, $quota_names);

        return array(
            'service_id' => (int)$service_id,
            'name' => isset($service_data['name']) ? $service_data['name'] : '',
            'product_type' => !empty($existing_record['product_type']) ? $existing_record['product_type'] : $inferred_type,
            'status' => !empty($existing_record['status']) ? $existing_record['status'] : 'neu',
            'manual_times' => !empty($existing_record['manual_times']) ? $this->normalize_manual_times_value($existing_record['manual_times']) : '',
            'linked_services' => !empty($existing_record['linked_services']) && is_array($existing_record['linked_services']) ? $existing_record['linked_services'] : array(),
            'has_quotas' => !empty($stations),
            'begin_time' => isset($service_data['begin_time']) ? $service_data['begin_time'] : '',
            'adult_price_brutto' => isset($service_data['adult_price_brutto']) ? $service_data['adult_price_brutto'] : null,
            'min_price_brutto' => isset($service_data['min_price_brutto']) ? $service_data['min_price_brutto'] : null,
            'station_summary' => $this->get_station_summary_text($stations),
            'stations' => $stations,
            'quota_names' => $quota_names,
            'raw_service_data' => $service_data,
            'raw_quota_data' => $quota_data,
            'last_synced_at' => current_time('mysql'),
        );
    }

    private function render_scan_progress_page($next_start, $end_id, $batch_size, $processed, $stored, $invalid) {
        $action_url = esc_url(admin_url('admin-post.php'));
        $redirect_url = esc_url($this->get_admin_page_url('niers-kombi-scanner', array('scan_done' => 1, 'processed' => intval($processed), 'stored' => intval($stored), 'invalid' => intval($invalid))));
        ?>
        <div class="wrap">
            <h1>Leistungs-Scan läuft...</h1>
            <p><?php echo esc_html("Bisher verarbeitet: {$processed} IDs | gespeichert: {$stored} | ungültig/inaktiv: {$invalid}"); ?></p>
            <p><?php echo esc_html("Nächster Block: {$next_start} bis " . min($end_id, $next_start + $batch_size - 1)); ?></p>
            <form id="niers-kombi-scan-continue" method="post" action="<?php echo $action_url; ?>">
                <?php wp_nonce_field('niers_kombi_scan_services', 'niers_kombi_scan_nonce'); ?>
                <input type="hidden" name="action" value="niers_kombi_scan_services">
                <input type="hidden" name="current_page" value="niers-kombi-scanner">
                <input type="hidden" name="start_id" value="<?php echo intval($next_start); ?>">
                <input type="hidden" name="end_id" value="<?php echo intval($end_id); ?>">
                <input type="hidden" name="batch_size" value="<?php echo intval($batch_size); ?>">
                <input type="hidden" name="processed" value="<?php echo intval($processed); ?>">
                <input type="hidden" name="stored" value="<?php echo intval($stored); ?>">
                <input type="hidden" name="invalid" value="<?php echo intval($invalid); ?>">
            </form>
            <p><a href="<?php echo $redirect_url; ?>" class="button">Scan abbrechen und zur Übersicht zurückkehren</a></p>
        </div>
        <script>
        setTimeout(function() {
            document.getElementById('niers-kombi-scan-continue').submit();
        }, 350);
        </script>
        <?php
    }

    private function render_resync_progress_page($catalog_ids, $offset, $batch_size, $processed, $updated, $invalid) {
        $action_url = esc_url(admin_url('admin-post.php'));
        $redirect_url = esc_url($this->get_admin_page_url('niers-kombi-scanner', array('resync_done' => 1, 'processed' => intval($processed), 'updated' => intval($updated), 'invalid' => intval($invalid))));
        $catalog_ids = array_values(array_map('intval', $catalog_ids));
        $current_slice = array_slice($catalog_ids, $offset, $batch_size);
        $next_label = !empty($current_slice)
            ? implode(', ', array_slice($current_slice, 0, 8)) . (count($current_slice) > 8 ? ' ...' : '')
            : '—';
        ?>
        <div class="wrap">
            <h1>Katalog-Resync läuft...</h1>
            <p><?php echo esc_html("Bisher verarbeitet: {$processed} IDs | aktualisiert: {$updated} | ungültig/inaktiv: {$invalid}"); ?></p>
            <p><?php echo esc_html("Nächster Block (" . count($current_slice) . " IDs): {$next_label}"); ?></p>
            <form id="niers-kombi-resync-continue" method="post" action="<?php echo $action_url; ?>">
                <?php wp_nonce_field('niers_kombi_resync_catalog', 'niers_kombi_resync_nonce'); ?>
                <input type="hidden" name="action" value="niers_kombi_resync_catalog">
                <input type="hidden" name="current_page" value="niers-kombi-scanner">
                <input type="hidden" name="catalog_ids" value="<?php echo esc_attr(implode(',', $catalog_ids)); ?>">
                <input type="hidden" name="offset" value="<?php echo intval($offset); ?>">
                <input type="hidden" name="batch_size" value="<?php echo intval($batch_size); ?>">
                <input type="hidden" name="processed" value="<?php echo intval($processed); ?>">
                <input type="hidden" name="updated" value="<?php echo intval($updated); ?>">
                <input type="hidden" name="invalid" value="<?php echo intval($invalid); ?>">
            </form>
            <p><a href="<?php echo $redirect_url; ?>" class="button">Resync abbrechen und zur Übersicht zurückkehren</a></p>
        </div>
        <script>
        setTimeout(function() {
            document.getElementById('niers-kombi-resync-continue').submit();
        }, 350);
        </script>
        <?php
    }

    public function handle_scan_services_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_scan_services', 'niers_kombi_scan_nonce');
        $current_page = isset($_POST['current_page']) ? sanitize_key(wp_unslash($_POST['current_page'])) : 'niers-kombi-scanner';
        if (!in_array($current_page, array('niers-kombi-scanner', 'niers-kombi-settings'), true)) $current_page = 'niers-kombi-scanner';

        $start_id = max(1, intval($_POST['start_id']));
        $end_id = max($start_id, intval($_POST['end_id']));
        $batch_size = max(1, min(100, intval($_POST['batch_size'])));
        $processed = max(0, intval(isset($_POST['processed']) ? $_POST['processed'] : 0));
        $stored = max(0, intval(isset($_POST['stored']) ? $_POST['stored'] : 0));
        $invalid = max(0, intval(isset($_POST['invalid']) ? $_POST['invalid'] : 0));
        $batch_end = min($end_id, $start_id + $batch_size - 1);

        $catalog = $this->get_service_catalog();

        for ($service_id = $start_id; $service_id <= $batch_end; $service_id++) {
            $service_data = $this->fetch_service_data($service_id);
            if (!$service_data) {
                $invalid++;
                $processed++;
                continue;
            }

            $existing = isset($catalog[$service_id]) && is_array($catalog[$service_id]) ? $catalog[$service_id] : array();
            $catalog[$service_id] = $this->build_service_record($service_id, $service_data, $existing);
            $stored++;
            $processed++;
        }

        $this->save_service_catalog($catalog);

        if ($batch_end < $end_id) {
            $this->render_scan_progress_page($batch_end + 1, $end_id, $batch_size, $processed, $stored, $invalid);
            exit;
        }

        wp_safe_redirect($this->get_admin_page_url($current_page, array('scan_done' => 1, 'processed' => intval($processed), 'stored' => intval($stored), 'invalid' => intval($invalid))));
        exit;
    }

    public function handle_resync_catalog_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_resync_catalog', 'niers_kombi_resync_nonce');
        $current_page = isset($_POST['current_page']) ? sanitize_key(wp_unslash($_POST['current_page'])) : 'niers-kombi-scanner';
        if (!in_array($current_page, array('niers-kombi-scanner', 'niers-kombi-settings'), true)) $current_page = 'niers-kombi-scanner';

        $catalog = $this->get_service_catalog();
        $catalog_ids_raw = isset($_POST['catalog_ids']) ? sanitize_text_field(wp_unslash($_POST['catalog_ids'])) : '';
        $catalog_ids = array_filter(array_map('intval', array_map('trim', explode(',', $catalog_ids_raw))));
        if (empty($catalog_ids)) $catalog_ids = array_map('intval', array_keys($catalog));

        $offset = max(0, intval(isset($_POST['offset']) ? $_POST['offset'] : 0));
        $batch_size = max(1, min(100, intval(isset($_POST['batch_size']) ? $_POST['batch_size'] : 50)));
        $processed = max(0, intval(isset($_POST['processed']) ? $_POST['processed'] : 0));
        $updated = max(0, intval(isset($_POST['updated']) ? $_POST['updated'] : 0));
        $invalid = max(0, intval(isset($_POST['invalid']) ? $_POST['invalid'] : 0));

        $current_ids = array_slice($catalog_ids, $offset, $batch_size);
        foreach ($current_ids as $service_id) {
            $existing = isset($catalog[$service_id]) && is_array($catalog[$service_id]) ? $catalog[$service_id] : array();
            $service_data = $this->fetch_service_data($service_id);

            if (!$service_data) {
                if (!empty($existing)) {
                    $existing['last_synced_at'] = current_time('mysql');
                    $existing['sync_error'] = 'Service nicht gefunden oder inaktiv';
                    $catalog[$service_id] = $existing;
                }
                $invalid++;
                $processed++;
                continue;
            }

            $record = $this->build_service_record($service_id, $service_data, $existing);
            if (isset($record['sync_error'])) unset($record['sync_error']);
            $catalog[$service_id] = $record;
            $updated++;
            $processed++;
        }

        $this->save_service_catalog($catalog);
        $next_offset = $offset + count($current_ids);

        if ($next_offset < count($catalog_ids)) {
            $this->render_resync_progress_page($catalog_ids, $next_offset, $batch_size, $processed, $updated, $invalid);
            exit;
        }

        wp_safe_redirect($this->get_admin_page_url($current_page, array('resync_done' => 1, 'processed' => intval($processed), 'updated' => intval($updated), 'invalid' => intval($invalid))));
        exit;
    }

    public function handle_reclassify_catalog_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_reclassify_catalog', 'niers_kombi_reclassify_nonce');
        $current_page = isset($_POST['current_page']) ? sanitize_key(wp_unslash($_POST['current_page'])) : 'niers-kombi-scanner';
        if (!in_array($current_page, array('niers-kombi-scanner', 'niers-kombi-settings'), true)) $current_page = 'niers-kombi-scanner';

        $catalog = $this->get_service_catalog();
        $updated = 0;

        foreach ($catalog as $service_id => $record) {
            if (!is_array($record)) continue;

            $service_data = isset($record['raw_service_data']) && is_array($record['raw_service_data']) ? $record['raw_service_data'] : array();
            $quota_names = isset($record['quota_names']) && is_array($record['quota_names']) ? $record['quota_names'] : array();
            $stations = $this->get_station_blueprint($service_data, $quota_names);
            $derived_type = $this->infer_product_type($service_data, $stations, $quota_names);

            if (!isset($record['product_type']) || $record['product_type'] !== $derived_type) {
                $record['product_type'] = $derived_type;
                $catalog[$service_id] = $record;
                $updated++;
            }
        }

        $this->save_service_catalog($catalog);
        wp_safe_redirect($this->get_admin_page_url($current_page, array('reclassify_done' => 1, 'updated' => intval($updated))));
        exit;
    }

    public function handle_save_catalog_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_save_catalog', 'niers_kombi_save_catalog_nonce');
        $current_page = isset($_POST['current_page']) ? sanitize_key(wp_unslash($_POST['current_page'])) : 'niers-kombi-catalog';
        if (!in_array($current_page, array('niers-kombi-catalog', 'niers-kombi-variants', 'niers-kombi-settings'), true)) $current_page = 'niers-kombi-catalog';

        $catalog = $this->get_service_catalog();
        $posted = isset($_POST['catalog']) && is_array($_POST['catalog']) ? $_POST['catalog'] : array();
        $selected_ids = isset($_POST['selected_ids']) && is_array($_POST['selected_ids'])
            ? array_map('intval', $_POST['selected_ids'])
            : array();
        $selected_lookup = array_fill_keys($selected_ids, true);
        $type_options = $this->get_catalog_product_type_options();
        $status_options = $this->get_catalog_status_options();
        $bulk_product_type = isset($_POST['bulk_product_type']) ? sanitize_key($_POST['bulk_product_type']) : '';
        $bulk_status = isset($_POST['bulk_status']) ? sanitize_key($_POST['bulk_status']) : '';
        $linked_service_updates = array();
        if (!isset($type_options[$bulk_product_type])) $bulk_product_type = '';
        if (!isset($status_options[$bulk_status])) $bulk_status = '';

        foreach ($posted as $service_id => $values) {
            $service_id = intval($service_id);
            if (!isset($catalog[$service_id]) || !is_array($catalog[$service_id])) continue;

            $previous_linked_services = !empty($catalog[$service_id]['linked_services']) && is_array($catalog[$service_id]['linked_services'])
                ? $catalog[$service_id]['linked_services']
                : array();
            $product_type = isset($values['product_type']) ? sanitize_key($values['product_type']) : 'unknown';
            $status = isset($values['status']) ? sanitize_key($values['status']) : 'neu';
            $manual_times = isset($values['manual_times']) ? $this->normalize_manual_times_value(wp_unslash($values['manual_times'])) : '';
            $linked_services = isset($values['linked_services']) ? $this->normalize_linked_services_value(wp_unslash($values['linked_services'])) : array();

            if (!isset($type_options[$product_type])) $product_type = 'unknown';
            if (!isset($status_options[$status])) $status = 'neu';

            if (!empty($selected_lookup) && isset($selected_lookup[$service_id])) {
                if ($bulk_product_type !== '') $product_type = $bulk_product_type;
                if ($bulk_status !== '') $status = $bulk_status;
            }

            $catalog[$service_id]['product_type'] = $product_type;
            $catalog[$service_id]['status'] = $status;
            $catalog[$service_id]['manual_times'] = $manual_times;
            $catalog[$service_id]['linked_services'] = $linked_services;
            $linked_service_updates[$service_id] = array(
                'new' => $linked_services,
                'old' => $previous_linked_services,
            );
        }

        foreach ($linked_service_updates as $service_id => $link_update) {
            $has_new = !empty($link_update['new']);
            $had_old = !empty($link_update['old']);
            if (!$has_new && !$had_old) continue;
            $catalog = $this->synchronize_linked_service_group($catalog, $service_id, $link_update['new'], $link_update['old']);
        }

        $this->save_service_catalog($catalog);
        $redirect_args = array(
            'page' => $current_page,
            'catalog_saved' => 1,
        );
        if (!empty($_POST['catalog_tab'])) $redirect_args['catalog_tab'] = sanitize_key($_POST['catalog_tab']);
        if (!empty($_POST['catalog_search'])) $redirect_args['catalog_search'] = sanitize_text_field(wp_unslash($_POST['catalog_search']));
        if (!empty($_POST['catalog_status_filter'])) $redirect_args['catalog_status'] = sanitize_key($_POST['catalog_status_filter']);
        if (!empty($_POST['catalog_quota_filter'])) $redirect_args['catalog_quota'] = sanitize_key($_POST['catalog_quota_filter']);

        wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
        exit;
    }

    public function handle_export_services_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_export_services', 'niers_kombi_export_nonce');

        $format = isset($_REQUEST['format']) ? sanitize_key($_REQUEST['format']) : 'json';
        $catalog = $this->get_service_catalog();

        if ($format === 'csv') {
            nocache_headers();
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=niers-service-catalog.csv');

            $out = fopen('php://output', 'w');
            fputcsv($out, array('service_id', 'name', 'product_type', 'status', 'begin_time', 'adult_price_brutto', 'min_price_brutto', 'has_quotas', 'station_summary', 'last_synced_at'), ';');
            foreach ($catalog as $record) {
                fputcsv($out, array(
                    $record['service_id'],
                    $record['name'],
                    $record['product_type'],
                    $record['status'],
                    $record['begin_time'],
                    $record['adult_price_brutto'],
                    $record['min_price_brutto'],
                    !empty($record['has_quotas']) ? '1' : '0',
                    $record['station_summary'],
                    $record['last_synced_at'],
                ), ';');
            }
            fclose($out);
            exit;
        }

        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=niers-service-catalog.json');
        echo wp_json_encode(array_values($catalog), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function handle_cleanup_catalog_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_cleanup_catalog', 'niers_kombi_cleanup_nonce');
        $current_page = isset($_POST['current_page']) ? sanitize_key(wp_unslash($_POST['current_page'])) : 'niers-kombi-catalog';
        if (!in_array($current_page, array('niers-kombi-catalog', 'niers-kombi-variants', 'niers-kombi-settings'), true)) $current_page = 'niers-kombi-catalog';

        $catalog = $this->get_service_catalog();
        $removed = 0;

        foreach ($catalog as $service_id => $record) {
            if ($this->is_empty_catalog_record($record)) {
                unset($catalog[$service_id]);
                $removed++;
            }
        }

        $this->save_service_catalog($catalog);
        wp_safe_redirect($this->get_admin_page_url($current_page, array('cleanup_done' => 1, 'removed' => intval($removed))));
        exit;
    }

    public function handle_export_config_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_export_config', 'niers_kombi_export_config_nonce');

        $payload = $this->build_config_export_payload();
        $filename = 'kombi-tour-config-' . gmdate('Ymd-His') . '.json';

        nocache_headers();
        header('Content-Type: application/json; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        echo wp_json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public function handle_import_config_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        check_admin_referer('niers_kombi_import_config', 'niers_kombi_import_config_nonce');

        $current_page = isset($_POST['current_page']) ? sanitize_key(wp_unslash($_POST['current_page'])) : 'niers-kombi-options';
        if (!in_array($current_page, array('niers-kombi-options', 'niers-kombi-settings'), true)) $current_page = 'niers-kombi-options';

        if (
            empty($_FILES['niers_kombi_config_file'])
            || !isset($_FILES['niers_kombi_config_file']['tmp_name'])
            || !is_uploaded_file($_FILES['niers_kombi_config_file']['tmp_name'])
        ) {
            wp_safe_redirect($this->get_admin_page_url($current_page, array('config_import' => 'missing_file')));
            exit;
        }

        $raw_json = file_get_contents($_FILES['niers_kombi_config_file']['tmp_name']);
        if ($raw_json === false || trim($raw_json) === '') {
            wp_safe_redirect($this->get_admin_page_url($current_page, array('config_import' => 'empty_file')));
            exit;
        }

        $payload = json_decode($raw_json, true);
        $normalized = $this->normalize_imported_config_payload($payload);
        if ($normalized === null) {
            wp_safe_redirect($this->get_admin_page_url($current_page, array('config_import' => 'invalid_json')));
            exit;
        }

        foreach ($normalized as $option_key => $option_value) {
            if ($option_key === 'niers_kombi_service_catalog') {
                $this->save_service_catalog($option_value);
                continue;
            }

            update_option($option_key, $option_value, false);
        }

        $imported_count = is_array($normalized['niers_kombi_service_catalog']) ? count($normalized['niers_kombi_service_catalog']) : 0;
        wp_safe_redirect($this->get_admin_page_url($current_page, array('config_import' => 'success', 'imported_catalog' => intval($imported_count))));
        exit;
    }

    public function maybe_run_plugin_migrations() {
        $stored_version = (string) get_option('niers_kombi_plugin_version', '');
        if ($stored_version === self::VERSION && $this->voucher_tables_exist()) {
            return;
        }

        self::create_plugin_tables();
        update_option('niers_kombi_plugin_version', self::VERSION, false);
    }

    public static function create_plugin_tables() {
        global $wpdb;

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $wpdb->get_charset_collate();
        $contacts_table = $wpdb->prefix . 'fxp_contacts';
        $contact_events_table = $wpdb->prefix . 'fxp_contact_events';
        $orders_table = $wpdb->prefix . 'fxp_orders';
        $email_log_table = $wpdb->prefix . 'fxp_email_log';
        $vouchers_table = $wpdb->prefix . 'fxp_vouchers';
        $redemptions_table = $wpdb->prefix . 'fxp_voucher_redemptions';

        $sql_contacts = "CREATE TABLE {$contacts_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            email varchar(255) NOT NULL,
            company varchar(255) DEFAULT NULL,
            gender varchar(50) DEFAULT NULL,
            title varchar(100) DEFAULT NULL,
            first_name varchar(255) DEFAULT NULL,
            last_name varchar(255) DEFAULT NULL,
            additional_name varchar(255) DEFAULT NULL,
            street varchar(255) DEFAULT NULL,
            zip varchar(50) DEFAULT NULL,
            city varchar(255) DEFAULT NULL,
            phone varchar(100) DEFAULT NULL,
            mobile varchar(100) DEFAULT NULL,
            newsletter_opt_in tinyint(1) NOT NULL DEFAULT 0,
            newsletter_opt_in_at datetime DEFAULT NULL,
            booking_reminder_opt_in tinyint(1) NOT NULL DEFAULT 0,
            booking_reminder_opt_in_at datetime DEFAULT NULL,
            source varchar(100) DEFAULT NULL,
            source_page text DEFAULT NULL,
            request_token varchar(64) DEFAULT NULL,
            paypal_order_id varchar(64) DEFAULT NULL,
            last_service_ids text DEFAULT NULL,
            last_booking_context longtext DEFAULT NULL,
            status varchar(30) NOT NULL DEFAULT 'lead',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email),
            KEY status (status),
            KEY request_token (request_token),
            KEY paypal_order_id (paypal_order_id)
        ) {$charset_collate};";

        $sql_contact_events = "CREATE TABLE {$contact_events_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            event_type varchar(60) NOT NULL,
            source varchar(100) DEFAULT NULL,
            source_page text DEFAULT NULL,
            request_token varchar(64) DEFAULT NULL,
            paypal_order_id varchar(64) DEFAULT NULL,
            event_payload longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY contact_id (contact_id),
            KEY email (email),
            KEY event_type (event_type),
            KEY request_token (request_token)
        ) {$charset_collate};";

        $sql_orders = "CREATE TABLE {$orders_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) unsigned DEFAULT NULL,
            request_token varchar(64) DEFAULT NULL,
            paypal_order_id varchar(64) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            first_name varchar(255) DEFAULT NULL,
            last_name varchar(255) DEFAULT NULL,
            source varchar(100) DEFAULT NULL,
            source_page text DEFAULT NULL,
            service_ids text DEFAULT NULL,
            service_names text DEFAULT NULL,
            item_count int(11) unsigned NOT NULL DEFAULT 0,
            people_count int(11) unsigned NOT NULL DEFAULT 0,
            order_total decimal(10,2) NOT NULL DEFAULT 0.00,
            currency varchar(10) NOT NULL DEFAULT 'EUR',
            payment_provider varchar(40) DEFAULT NULL,
            order_payload longtext DEFAULT NULL,
            is_test tinyint(1) NOT NULL DEFAULT 0,
            status varchar(30) NOT NULL DEFAULT 'submitted',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY request_token (request_token),
            UNIQUE KEY paypal_order_id (paypal_order_id),
            KEY email (email),
            KEY is_test (is_test),
            KEY created_at (created_at),
            KEY status (status)
        ) {$charset_collate};";

        $sql_email_log = "CREATE TABLE {$email_log_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            event_key varchar(190) NOT NULL,
            rule_key varchar(60) NOT NULL,
            order_id bigint(20) unsigned DEFAULT NULL,
            contact_id bigint(20) unsigned DEFAULT NULL,
            request_token varchar(64) DEFAULT NULL,
            recipient_email varchar(255) DEFAULT NULL,
            original_recipient_email varchar(255) DEFAULT NULL,
            subject varchar(255) DEFAULT NULL,
            status varchar(40) NOT NULL DEFAULT 'queued',
            send_mode varchar(40) NOT NULL DEFAULT 'live',
            payload longtext DEFAULT NULL,
            error_message text DEFAULT NULL,
            created_at datetime NOT NULL,
            sent_at datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY event_key (event_key),
            KEY rule_key (rule_key),
            KEY order_id (order_id),
            KEY recipient_email (recipient_email),
            KEY status (status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        $sql_vouchers = "CREATE TABLE {$vouchers_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            code varchar(64) NOT NULL,
            voucher_type varchar(20) NOT NULL DEFAULT 'fixed',
            amount decimal(10,2) NOT NULL DEFAULT 0.00,
            remaining_amount decimal(10,2) DEFAULT NULL,
            usage_limit int(11) unsigned DEFAULT NULL,
            usage_count int(11) unsigned NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'active',
            valid_from datetime DEFAULT NULL,
            valid_until datetime DEFAULT NULL,
            allowed_service_ids text DEFAULT NULL,
            notes text DEFAULT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY code (code),
            KEY status (status),
            KEY valid_until (valid_until)
        ) {$charset_collate};";

        $sql_redemptions = "CREATE TABLE {$redemptions_table} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            voucher_id bigint(20) unsigned NOT NULL,
            code_snapshot varchar(64) NOT NULL,
            request_token varchar(64) DEFAULT NULL,
            paypal_order_id varchar(64) DEFAULT NULL,
            email varchar(255) DEFAULT NULL,
            amount_applied decimal(10,2) DEFAULT NULL,
            service_ids text DEFAULT NULL,
            payload longtext DEFAULT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY voucher_id (voucher_id),
            KEY request_token (request_token),
            KEY paypal_order_id (paypal_order_id)
        ) {$charset_collate};";

        dbDelta($sql_contacts);
        dbDelta($sql_contact_events);
        dbDelta($sql_orders);
        dbDelta($sql_email_log);
        dbDelta($sql_vouchers);
        dbDelta($sql_redemptions);
    }

    private function voucher_tables_exist() {
        global $wpdb;

        $contacts_table = $this->get_contacts_table_name();
        $contact_events_table = $this->get_contact_events_table_name();
        $orders_table = $this->get_orders_table_name();
        $email_log_table = $this->get_email_log_table_name();
        $vouchers_table = $this->get_vouchers_table_name();
        $redemptions_table = $this->get_voucher_redemptions_table_name();

        $has_contacts = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $contacts_table));
        $has_contact_events = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $contact_events_table));
        $has_orders = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $orders_table));
        $has_email_log = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $email_log_table));
        $has_vouchers = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $vouchers_table));
        $has_redemptions = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $redemptions_table));

        return $has_contacts === $contacts_table
            && $has_contact_events === $contact_events_table
            && $has_orders === $orders_table
            && $has_email_log === $email_log_table
            && $has_vouchers === $vouchers_table
            && $has_redemptions === $redemptions_table;
    }

    private function get_contacts_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'fxp_contacts';
    }

    private function get_contact_events_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'fxp_contact_events';
    }

    private function get_orders_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'fxp_orders';
    }

    private function get_email_log_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'fxp_email_log';
    }

    private function get_vouchers_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'fxp_vouchers';
    }

    private function get_voucher_redemptions_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'fxp_voucher_redemptions';
    }

    private function get_voucher_status_options() {
        return array(
            'active' => 'Aktiv',
            'inactive' => 'Inaktiv',
            'archived' => 'Archiviert',
        );
    }

    private function get_voucher_type_options() {
        return array(
            'fixed' => 'Wertgutschein (€)',
            'percent' => 'Rabattcode (%)',
        );
    }

    private function normalize_voucher_datetime($value) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $timestamp = strtotime(str_replace('T', ' ', $value));
        if (!$timestamp) {
            return null;
        }

        return gmdate('Y-m-d H:i:s', $timestamp + (get_option('gmt_offset') * HOUR_IN_SECONDS));
    }

    private function format_voucher_datetime_for_input($value) {
        if (empty($value) || $value === '0000-00-00 00:00:00') {
            return '';
        }

        $timestamp = strtotime((string) $value);
        return $timestamp ? gmdate('Y-m-d\TH:i', $timestamp - (get_option('gmt_offset') * HOUR_IN_SECONDS)) : '';
    }

    private function normalize_voucher_service_ids($value) {
        $parts = preg_split('/[\s,;]+/', (string) $value);
        $ids = array();

        foreach ($parts as $part) {
            $id = intval($part);
            if ($id > 0) {
                $ids[$id] = (string) $id;
            }
        }

        ksort($ids, SORT_NUMERIC);
        return implode(',', $ids);
    }

    private function get_contact_status_options() {
        return array(
            'lead' => 'Lead',
            'checkout_started' => 'Checkout gestartet',
            'booked' => 'Gebucht',
        );
    }

    private function get_contact_consent_filter_options() {
        return array(
            'all' => 'Alle Kontakte',
            'newsletter_yes' => 'Nur Newsletter-Anmeldungen',
            'newsletter_no' => 'Ohne Newsletter-Anmeldung',
            'reminder_yes' => 'Nur Reminder-Einwilligungen',
            'reminder_no' => 'Ohne Reminder-Einwilligung',
            'any_optin' => 'Mindestens eine Einwilligung',
        );
    }

    private function get_contacts_filters() {
        $consent_filter = isset($_GET['consent_filter']) ? sanitize_key(wp_unslash($_GET['consent_filter'])) : 'all';
        if (!isset($this->get_contact_consent_filter_options()[$consent_filter])) {
            $consent_filter = 'all';
        }

        return array(
            'consent_filter' => $consent_filter,
        );
    }

    private function build_contacts_where_clause($filters) {
        $parts = array('1=1');
        $params = array();
        $consent_filter = isset($filters['consent_filter']) ? sanitize_key((string) $filters['consent_filter']) : 'all';

        if ($consent_filter === 'newsletter_yes') {
            $parts[] = 'newsletter_opt_in = 1';
        } elseif ($consent_filter === 'newsletter_no') {
            $parts[] = 'newsletter_opt_in = 0';
        } elseif ($consent_filter === 'reminder_yes') {
            $parts[] = 'booking_reminder_opt_in = 1';
        } elseif ($consent_filter === 'reminder_no') {
            $parts[] = 'booking_reminder_opt_in = 0';
        } elseif ($consent_filter === 'any_optin') {
            $parts[] = '(newsletter_opt_in = 1 OR booking_reminder_opt_in = 1)';
        }

        return array('WHERE ' . implode(' AND ', $parts), $params);
    }

    private function get_all_contacts($limit = 200, $filters = array()) {
        global $wpdb;

        $limit = max(1, min(1000, intval($limit)));
        $table = $this->get_contacts_table_name();
        list($where_sql, $params) = $this->build_contacts_where_clause($filters);
        $sql = "SELECT * FROM {$table} {$where_sql} ORDER BY updated_at DESC, id DESC LIMIT %d";
        $params[] = $limit;
        return $wpdb->get_results($this->prepare_sql_statement($sql, $params), ARRAY_A);
    }

    private function get_contact_summary() {
        global $wpdb;

        $contacts_table = $this->get_contacts_table_name();
        $row = $wpdb->get_row("
            SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN newsletter_opt_in = 1 THEN 1 ELSE 0 END) AS newsletter_count,
                SUM(CASE WHEN booking_reminder_opt_in = 1 THEN 1 ELSE 0 END) AS booking_reminder_count,
                SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) AS booked_count
            FROM {$contacts_table}
        ", ARRAY_A);

        return array(
            'total_count' => intval(isset($row['total_count']) ? $row['total_count'] : 0),
            'newsletter_count' => intval(isset($row['newsletter_count']) ? $row['newsletter_count'] : 0),
            'booking_reminder_count' => intval(isset($row['booking_reminder_count']) ? $row['booking_reminder_count'] : 0),
            'booked_count' => intval(isset($row['booked_count']) ? $row['booked_count'] : 0),
        );
    }

    private function get_contact_by_email($email) {
        global $wpdb;

        $email = sanitize_email((string) $email);
        if ($email === '') {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->get_contacts_table_name()} WHERE email = %s", $email),
            ARRAY_A
        );
    }

    private function get_orders_for_contact($contact, $limit = 100, $include_tests = true) {
        global $wpdb;

        $limit = max(1, min(500, intval($limit)));
        $where_parts = array("status = 'submitted'");
        $params = array();

        if (!$include_tests) {
            $where_parts[] = 'is_test = 0';
        }

        if (!empty($contact['id'])) {
            $where_parts[] = '(contact_id = %d OR email = %s)';
            $params[] = intval($contact['id']);
            $params[] = sanitize_email((string) $contact['email']);
        } else {
            $where_parts[] = 'email = %s';
            $params[] = sanitize_email((string) $contact['email']);
        }

        $params[] = $limit;
        $sql = "SELECT * FROM {$this->get_orders_table_name()} WHERE " . implode(' AND ', $where_parts) . ' ORDER BY created_at DESC, id DESC LIMIT %d';

        return $wpdb->get_results($this->prepare_sql_statement($sql, $params), ARRAY_A);
    }

    private function get_contact_booking_flags($contacts) {
        global $wpdb;

        $flags = array();
        $emails = array();
        $contact_ids = array();

        foreach ((array) $contacts as $contact) {
            $contact_id = !empty($contact['id']) ? intval($contact['id']) : 0;
            $email = !empty($contact['email']) ? strtolower(sanitize_email((string) $contact['email'])) : '';

            if ($contact_id > 0) {
                $contact_ids[$contact_id] = $contact_id;
                $flags['contact:' . $contact_id] = 0;
            }
            if ($email !== '') {
                $emails[$email] = $email;
                $flags['email:' . $email] = 0;
            }
        }

        if (empty($emails) && empty($contact_ids)) {
            return $flags;
        }

        $match_parts = array();
        $params = array();

        if (!empty($contact_ids)) {
            $match_parts[] = 'contact_id IN (' . implode(',', array_fill(0, count($contact_ids), '%d')) . ')';
            $params = array_merge($params, array_values($contact_ids));
        }

        if (!empty($emails)) {
            $match_parts[] = 'LOWER(email) IN (' . implode(',', array_fill(0, count($emails), '%s')) . ')';
            $params = array_merge($params, array_values($emails));
        }

        if (empty($match_parts)) {
            return $flags;
        }

        $sql = "SELECT contact_id, email, COUNT(*) AS booking_count
            FROM {$this->get_orders_table_name()}
            WHERE status = 'submitted' AND (" . implode(' OR ', $match_parts) . ")
            GROUP BY contact_id, email";

        $rows = $wpdb->get_results($this->prepare_sql_statement($sql, $params), ARRAY_A);
        foreach ((array) $rows as $row) {
            $count = max(0, intval(isset($row['booking_count']) ? $row['booking_count'] : 0));
            $row_contact_id = !empty($row['contact_id']) ? intval($row['contact_id']) : 0;
            $row_email = !empty($row['email']) ? strtolower(sanitize_email((string) $row['email'])) : '';

            if ($row_contact_id > 0) {
                $key = 'contact:' . $row_contact_id;
                $flags[$key] = max(isset($flags[$key]) ? intval($flags[$key]) : 0, $count);
            }
            if ($row_email !== '') {
                $key = 'email:' . $row_email;
                $flags[$key] = max(isset($flags[$key]) ? intval($flags[$key]) : 0, $count);
            }
        }

        return $flags;
    }

    private function decode_json_array($raw_value) {
        if (is_array($raw_value)) {
            return $raw_value;
        }

        if (!is_string($raw_value) || trim($raw_value) === '') {
            return array();
        }

        $decoded = json_decode($raw_value, true);
        return is_array($decoded) ? $decoded : array();
    }

    private function get_portal_order_rows($orders) {
        $rows = array();

        foreach ((array) $orders as $order) {
            $payload_items = $this->decode_json_array(isset($order['order_payload']) ? $order['order_payload'] : '');
            if (empty($payload_items)) {
                $payload_items = array(array(
                    'service_id' => 0,
                    'name' => isset($order['service_names']) ? $order['service_names'] : 'Buchung',
                    'quotas_begin_time' => '',
                    'ppl_adult' => isset($order['people_count']) ? intval($order['people_count']) : 0,
                    'ppl_child' => 0,
                    'ppl_baby' => 0,
                    'price_brutto' => isset($order['order_total']) ? (float) $order['order_total'] : 0,
                ));
            }

            foreach ($payload_items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $tour_start = '';
                if (!empty($item['quotas_begin_time'])) {
                    $tour_start = sanitize_text_field((string) $item['quotas_begin_time']);
                } elseif (!empty($item['date']) && !empty($item['time'])) {
                    $tour_start = sanitize_text_field((string) $item['date'] . ' ' . $item['time']);
                } elseif (!empty($item['date'])) {
                    $tour_start = sanitize_text_field((string) $item['date']);
                }

                $people_count = max(0, intval(isset($item['ppl_adult']) ? $item['ppl_adult'] : 0))
                    + max(0, intval(isset($item['ppl_child']) ? $item['ppl_child'] : 0))
                    + max(0, intval(isset($item['ppl_baby']) ? $item['ppl_baby'] : 0));

                $rows[] = array(
                    'order_id' => intval(isset($order['id']) ? $order['id'] : 0),
                    'request_token' => isset($order['request_token']) ? (string) $order['request_token'] : '',
                    'source' => isset($order['source']) ? (string) $order['source'] : '',
                    'service_id' => intval(isset($item['service_id']) ? $item['service_id'] : 0),
                    'tour_name' => sanitize_text_field(isset($item['name']) ? $item['name'] : 'Buchung'),
                    'tour_start' => $tour_start,
                    'tour_start_ts' => $tour_start !== '' ? $this->parse_wp_local_datetime_timestamp($tour_start) : false,
                    'people_count' => $people_count > 0 ? $people_count : max(0, intval(isset($order['people_count']) ? $order['people_count'] : 0)),
                    'order_total' => round((float) (isset($order['order_total']) ? $order['order_total'] : 0), 2),
                    'item_total' => round((float) (isset($item['price_brutto']) ? $item['price_brutto'] : 0), 2),
                    'status' => isset($order['status']) ? (string) $order['status'] : 'submitted',
                    'is_test' => !empty($order['is_test']),
                    'created_at' => isset($order['created_at']) ? (string) $order['created_at'] : '',
                );
            }
        }

        usort($rows, function($a, $b) {
            $a_ts = !empty($a['tour_start_ts']) ? intval($a['tour_start_ts']) : 0;
            $b_ts = !empty($b['tour_start_ts']) ? intval($b['tour_start_ts']) : 0;
            if ($a_ts === $b_ts) {
                return strcmp((string) $a['tour_name'], (string) $b['tour_name']);
            }
            return $a_ts <=> $b_ts;
        });

        return $rows;
    }

    private function split_portal_rows_by_time($rows) {
        $today_start = $this->get_wp_today_start_timestamp();
        $upcoming = array();
        $history = array();

        foreach ((array) $rows as $row) {
            $ts = !empty($row['tour_start_ts']) ? intval($row['tour_start_ts']) : 0;
            if ($ts > 0 && $ts >= $today_start) {
                $upcoming[] = $row;
            } else {
                $history[] = $row;
            }
        }

        usort($history, function($a, $b) {
            $a_ts = !empty($a['tour_start_ts']) ? intval($a['tour_start_ts']) : 0;
            $b_ts = !empty($b['tour_start_ts']) ? intval($b['tour_start_ts']) : 0;
            return $b_ts <=> $a_ts;
        });

        return array($upcoming, $history);
    }

    private function get_portal_contact_context() {
        if (!is_user_logged_in()) {
            return array(null, null, '');
        }

        $user = wp_get_current_user();
        if (!$user || empty($user->ID)) {
            return array(null, null, '');
        }

        $portal_email = sanitize_email((string) $user->user_email);
        if (current_user_can('manage_options') && !empty($_GET['fxp_portal_email'])) {
            $preview_email = sanitize_email(wp_unslash($_GET['fxp_portal_email']));
            if ($preview_email !== '') {
                $portal_email = $preview_email;
            }
        }

        if ($portal_email === '') {
            return array($user, null, '');
        }

        return array($user, $this->get_contact_by_email($portal_email), $portal_email);
    }

    private function format_portal_datetime_label($value) {
        if (empty($value)) {
            return 'Termin folgt';
        }

        $timestamp = $this->parse_wp_local_datetime_timestamp($value);
        return $timestamp ? wp_date('d.m.Y | H:i \U\h\r', $timestamp) : esc_html((string) $value);
    }

    private function upsert_contact_record($payload) {
        global $wpdb;

        $table = $this->get_contacts_table_name();
        $email = sanitize_email(isset($payload['email']) ? $payload['email'] : '');
        if ($email === '') {
            return 0;
        }

        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE email = %s", $email), ARRAY_A);
        $now = current_time('mysql');
        $newsletter_opt_in = !empty($payload['newsletter_opt_in']) ? 1 : 0;
        $booking_reminder_opt_in = !empty($payload['booking_reminder_opt_in']) ? 1 : 0;
        $status = isset($payload['status']) ? sanitize_key($payload['status']) : 'lead';
        if (!isset($this->get_contact_status_options()[$status])) {
            $status = 'lead';
        }
        if ($existing && !empty($existing['status']) && $existing['status'] === 'booked' && $status !== 'booked') {
            $status = 'booked';
        }

        $data = array(
            'email' => $email,
            'company' => sanitize_text_field(isset($payload['company']) ? $payload['company'] : ''),
            'gender' => sanitize_text_field(isset($payload['gender']) ? $payload['gender'] : ''),
            'title' => sanitize_text_field(isset($payload['title']) ? $payload['title'] : ''),
            'first_name' => sanitize_text_field(isset($payload['first_name']) ? $payload['first_name'] : ''),
            'last_name' => sanitize_text_field(isset($payload['last_name']) ? $payload['last_name'] : ''),
            'additional_name' => sanitize_text_field(isset($payload['additional_name']) ? $payload['additional_name'] : ''),
            'street' => sanitize_text_field(isset($payload['street']) ? $payload['street'] : ''),
            'zip' => sanitize_text_field(isset($payload['zip']) ? $payload['zip'] : ''),
            'city' => sanitize_text_field(isset($payload['city']) ? $payload['city'] : ''),
            'phone' => sanitize_text_field(isset($payload['phone']) ? $payload['phone'] : ''),
            'mobile' => sanitize_text_field(isset($payload['mobile']) ? $payload['mobile'] : ''),
            'newsletter_opt_in' => $newsletter_opt_in,
            'booking_reminder_opt_in' => $booking_reminder_opt_in,
            'source' => sanitize_text_field(isset($payload['source']) ? $payload['source'] : 'kombi-konfigurator'),
            'source_page' => esc_url_raw(isset($payload['source_page']) ? $payload['source_page'] : ''),
            'request_token' => sanitize_text_field(isset($payload['request_token']) ? $payload['request_token'] : ''),
            'paypal_order_id' => sanitize_text_field(isset($payload['paypal_order_id']) ? $payload['paypal_order_id'] : ''),
            'last_service_ids' => sanitize_text_field(isset($payload['last_service_ids']) ? $payload['last_service_ids'] : ''),
            'last_booking_context' => wp_json_encode(isset($payload['booking_context']) ? $payload['booking_context'] : array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'status' => $status,
            'updated_at' => $now,
        );
        $formats = array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s');

        if ($newsletter_opt_in) {
            $data['newsletter_opt_in_at'] = $now;
            $formats[] = '%s';
        }
        if ($booking_reminder_opt_in) {
            $data['booking_reminder_opt_in_at'] = $now;
            $formats[] = '%s';
        }

        if ($existing) {
            foreach (array('company', 'gender', 'title', 'first_name', 'last_name', 'additional_name', 'street', 'zip', 'city', 'phone', 'mobile') as $preserve_key) {
                if ($data[$preserve_key] === '' && !empty($existing[$preserve_key])) {
                    $data[$preserve_key] = $existing[$preserve_key];
                }
            }
            if (!$newsletter_opt_in && !empty($existing['newsletter_opt_in'])) {
                $data['newsletter_opt_in'] = 1;
            }
            if (!$newsletter_opt_in && !empty($existing['newsletter_opt_in_at'])) {
                $data['newsletter_opt_in_at'] = $existing['newsletter_opt_in_at'];
                $formats[] = '%s';
            }
            if (!$booking_reminder_opt_in && !empty($existing['booking_reminder_opt_in'])) {
                $data['booking_reminder_opt_in'] = 1;
            }
            if (!$booking_reminder_opt_in && !empty($existing['booking_reminder_opt_in_at'])) {
                $data['booking_reminder_opt_in_at'] = $existing['booking_reminder_opt_in_at'];
                $formats[] = '%s';
            }
            $wpdb->update($table, $data, array('id' => intval($existing['id'])), $formats, array('%d'));
            return intval($existing['id']);
        }

        $data['created_at'] = $now;
        $formats[] = '%s';
        $wpdb->insert($table, $data, $formats);
        return intval($wpdb->insert_id);
    }

    private function insert_contact_event($payload) {
        global $wpdb;

        $table = $this->get_contact_events_table_name();
        $wpdb->insert(
            $table,
            array(
                'contact_id' => !empty($payload['contact_id']) ? intval($payload['contact_id']) : null,
                'email' => sanitize_email(isset($payload['email']) ? $payload['email'] : ''),
                'event_type' => sanitize_key(isset($payload['event_type']) ? $payload['event_type'] : 'lead_created'),
                'source' => sanitize_text_field(isset($payload['source']) ? $payload['source'] : 'kombi-konfigurator'),
                'source_page' => esc_url_raw(isset($payload['source_page']) ? $payload['source_page'] : ''),
                'request_token' => sanitize_text_field(isset($payload['request_token']) ? $payload['request_token'] : ''),
                'paypal_order_id' => sanitize_text_field(isset($payload['paypal_order_id']) ? $payload['paypal_order_id'] : ''),
                'event_payload' => wp_json_encode(isset($payload['event_payload']) ? $payload['event_payload'] : array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at' => current_time('mysql'),
            ),
            array('%d','%s','%s','%s','%s','%s','%s','%s','%s')
        );
    }

    private function get_contact_step_default_values() {
        return array(
            'email' => '',
            'company' => '',
            'gender' => '',
            'title' => '',
            'first_name' => '',
            'last_name' => '',
            'additional_name' => '',
            'street' => '',
            'zip' => '',
            'city' => '',
            'phone' => '',
            'mobile' => '',
            'newsletter_opt_in' => '0',
            'booking_reminder_opt_in' => '0',
        );
    }

    private function get_dashboard_source_group_options() {
        return array(
            'all' => 'Alle Quellen',
            'niers' => 'Niers / Einzeltouren',
            'kombi' => 'Kombi-Touren',
        );
    }

    private function normalize_dashboard_date_value($value) {
        $value = trim((string) $value);
        if ($value !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }
        return '';
    }

    private function get_dashboard_filters() {
        $date_from = $this->normalize_dashboard_date_value(isset($_GET['date_from']) ? wp_unslash($_GET['date_from']) : '');
        $date_to = $this->normalize_dashboard_date_value(isset($_GET['date_to']) ? wp_unslash($_GET['date_to']) : '');
        if ($date_from !== '' && $date_to !== '' && $date_from > $date_to) {
            $tmp = $date_from;
            $date_from = $date_to;
            $date_to = $tmp;
        }

        $source_group = isset($_GET['source_group']) ? sanitize_key(wp_unslash($_GET['source_group'])) : 'all';
        if (!isset($this->get_dashboard_source_group_options()[$source_group])) {
            $source_group = 'all';
        }

        return array(
            'date_from' => $date_from,
            'date_to' => $date_to,
            'source_group' => $source_group,
            'include_tests' => isset($_GET['include_tests']) && (string) $_GET['include_tests'] === '1',
        );
    }

    private function get_dashboard_period_label($filters) {
        if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
            return mysql2date('d.m.Y', $filters['date_from']) . ' bis ' . mysql2date('d.m.Y', $filters['date_to']);
        }
        if (!empty($filters['date_from'])) {
            return 'ab ' . mysql2date('d.m.Y', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            return 'bis ' . mysql2date('d.m.Y', $filters['date_to']);
        }
        return 'komplette Historie';
    }

    private function prepare_sql_statement($sql, $params = array()) {
        global $wpdb;

        if (empty($params)) {
            return $sql;
        }

        return $wpdb->prepare($sql, ...$params);
    }

    private function build_orders_where_clause($filters, $column = 'created_at') {
        $parts = array("status = 'submitted'");
        $params = array();

        if (empty($filters['include_tests'])) {
            $parts[] = 'is_test = 0';
        }
        if (!empty($filters['date_from'])) {
            $parts[] = "{$column} >= %s";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $parts[] = "{$column} <= %s";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        return array('WHERE ' . implode(' AND ', $parts), $params);
    }

    private function build_contact_events_where_clause($filters, $event_type = 'contact_step_submitted', $column = 'created_at') {
        $parts = array('event_type = %s');
        $params = array($event_type);

        if (!empty($filters['date_from'])) {
            $parts[] = "{$column} >= %s";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $parts[] = "{$column} <= %s";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        return array('WHERE ' . implode(' AND ', $parts), $params);
    }

    private function get_dashboard_filtered_orders($filters, $limit = 0) {
        global $wpdb;

        $limit = max(0, intval($limit));
        $table = $this->get_orders_table_name();
        list($where_sql, $params) = $this->build_orders_where_clause($filters);
        $sql = "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC, id DESC";
        if ($limit > 0) {
            $sql .= " LIMIT %d";
            $params[] = $limit;
        }

        return $wpdb->get_results($this->prepare_sql_statement($sql, $params), ARRAY_A);
    }

    private function get_dashboard_filtered_lead_events($filters) {
        global $wpdb;

        $table = $this->get_contact_events_table_name();
        list($where_sql, $params) = $this->build_contact_events_where_clause($filters, 'contact_step_submitted');
        $sql = "SELECT * FROM {$table} {$where_sql} ORDER BY created_at DESC, id DESC";

        return $wpdb->get_results($this->prepare_sql_statement($sql, $params), ARRAY_A);
    }

    private function get_dashboard_service_ids_from_value($raw_value) {
        if (is_array($raw_value)) {
            $values = $raw_value;
        } else {
            $values = preg_split('/[\s,|;]+/', (string) $raw_value);
        }

        $service_ids = array();
        foreach ((array) $values as $value) {
            $service_id = intval($value);
            if ($service_id > 0) {
                $service_ids[$service_id] = $service_id;
            }
        }

        return array_values($service_ids);
    }

    private function get_dashboard_source_group_from_context($service_ids, $source_page = '', $source = '') {
        $catalog = $this->get_service_catalog();

        foreach ((array) $service_ids as $service_id) {
            $service_id = intval($service_id);
            if ($service_id < 1) continue;
            if (!empty($catalog[$service_id]['product_type']) && $catalog[$service_id]['product_type'] === 'kombi') {
                return 'kombi';
            }
        }

        $source_page = strtolower((string) $source_page);
        $source = strtolower((string) $source);

        if (strpos($source_page, '/kombi-touren/') !== false || strpos($source, 'kombi') !== false) {
            return 'kombi';
        }

        return 'niers';
    }

    private function get_dashboard_order_source_group($order) {
        $service_ids = $this->get_dashboard_service_ids_from_value(isset($order['service_ids']) ? $order['service_ids'] : '');
        return $this->get_dashboard_source_group_from_context(
            $service_ids,
            isset($order['source_page']) ? $order['source_page'] : '',
            isset($order['source']) ? $order['source'] : ''
        );
    }

    private function get_dashboard_lead_event_source_group($event) {
        $payload = array();
        if (!empty($event['event_payload'])) {
            $decoded = json_decode((string) $event['event_payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $service_ids = array();
        if (!empty($payload['service_ids'])) {
            $service_ids = $this->get_dashboard_service_ids_from_value($payload['service_ids']);
        }

        return $this->get_dashboard_source_group_from_context(
            $service_ids,
            isset($event['source_page']) ? $event['source_page'] : '',
            isset($event['source']) ? $event['source'] : ''
        );
    }

    private function filter_dashboard_orders_by_source_group($orders, $source_group) {
        if ($source_group === 'all') {
            return array_values($orders);
        }

        return array_values(array_filter((array) $orders, function($order) use ($source_group) {
            return $this->get_dashboard_order_source_group($order) === $source_group;
        }));
    }

    private function filter_dashboard_lead_events_by_source_group($events, $source_group) {
        if ($source_group === 'all') {
            return array_values($events);
        }

        return array_values(array_filter((array) $events, function($event) use ($source_group) {
            return $this->get_dashboard_lead_event_source_group($event) === $source_group;
        }));
    }

    private function decode_contact_event_payload($event) {
        if (empty($event['event_payload'])) {
            return array();
        }

        $decoded = json_decode((string) $event['event_payload'], true);
        return is_array($decoded) ? $decoded : array();
    }

    private function is_test_contact_event($event) {
        $email = strtolower(sanitize_email(isset($event['email']) ? $event['email'] : ''));
        if ($email === '') {
            $payload = $this->decode_contact_event_payload($event);
            $email = strtolower(sanitize_email(isset($payload['email']) ? $payload['email'] : ''));
        }

        return $email !== '' && in_array($email, $this->get_test_order_email_list(), true);
    }

    private function get_checkout_funnel_steps() {
        return array(
            'contact_step_submitted' => array(
                'label' => 'Kundendaten gesendet',
                'description' => 'Der Kunde hat den Zwischenschritt mit Pflichtdaten erfolgreich abgeschickt.',
            ),
            'cart_opened_after_contact' => array(
                'label' => 'Warenkorb nach Kontakt',
                'description' => 'Der Warenkorb wurde mit gültigem Kontakt-Step geöffnet.',
            ),
            'checkout_ready' => array(
                'label' => 'Zahlarten sichtbar',
                'description' => 'Der Checkout war sichtbar und zahlungsbereit.',
            ),
            'payment_clicked' => array(
                'label' => 'Zahlung gestartet',
                'description' => 'Der Kunde hat PayPal oder Debit-/Kreditkarte gestartet; erfolgreiche ERP-Übergaben werden als Fallback mitgezählt.',
            ),
            'cart_data_success' => array(
                'label' => 'ERP Übergabe OK',
                'description' => 'Die Buchungsdaten wurden erfolgreich an cart_data.php gemeldet.',
            ),
            'order_submitted' => array(
                'label' => 'Bestellung protokolliert',
                'description' => 'Das Plugin hat die Buchung als abgeschlossene Bestellung gespeichert.',
            ),
        );
    }

    private function get_checkout_funnel_filtered_events($filters, $limit = 0) {
        global $wpdb;

        $steps = $this->get_checkout_funnel_steps();
        $event_types = array_keys($steps);
        $event_types[] = 'paypal_clicked';
        $event_types[] = 'cart_data_detected';
        $event_types[] = 'cart_data_failed';
        $event_types = array_values(array_unique($event_types));
        $placeholders = implode(',', array_fill(0, count($event_types), '%s'));
        $params = $event_types;
        $parts = array("event_type IN ({$placeholders})");

        if (!empty($filters['date_from'])) {
            $parts[] = 'created_at >= %s';
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $parts[] = 'created_at <= %s';
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $table = $this->get_contact_events_table_name();
        $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $parts) . ' ORDER BY created_at DESC, id DESC';
        $limit = max(0, intval($limit));
        if ($limit > 0) {
            $sql .= ' LIMIT %d';
            $params[] = $limit;
        }

        $events = $wpdb->get_results($this->prepare_sql_statement($sql, $params), ARRAY_A);
        $events = $this->filter_dashboard_lead_events_by_source_group($events, isset($filters['source_group']) ? $filters['source_group'] : 'all');

        if (empty($filters['include_tests'])) {
            $events = array_values(array_filter($events, function($event) {
                return !$this->is_test_contact_event($event);
            }));
        }

        return $events;
    }

    private function get_checkout_funnel_event_identity($event) {
        if (!empty($event['contact_id'])) {
            return 'contact:' . intval($event['contact_id']);
        }

        $payload = $this->decode_contact_event_payload($event);
        $email = strtolower(sanitize_email(isset($event['email']) ? $event['email'] : ''));
        if ($email === '' && !empty($payload['email'])) {
            $email = strtolower(sanitize_email($payload['email']));
        }
        if ($email !== '') {
            return 'email:' . $email;
        }
        if (!empty($event['paypal_order_id'])) {
            return 'paypal:' . sanitize_text_field($event['paypal_order_id']);
        }
        if (!empty($event['request_token'])) {
            return 'request:' . sanitize_text_field($event['request_token']);
        }

        return 'event:' . intval(isset($event['id']) ? $event['id'] : 0);
    }

    private function get_checkout_funnel_event_label($event_type) {
        $labels = array(
            'contact_step_submitted' => 'Kundendaten gesendet',
            'cart_opened_after_contact' => 'Warenkorb geöffnet',
            'checkout_ready' => 'Checkout bereit',
            'payment_clicked' => 'Zahlung gestartet',
            'paypal_clicked' => 'PayPal geklickt',
            'cart_data_detected' => 'ERP-Daten erkannt',
            'cart_data_success' => 'ERP Übergabe OK',
            'cart_data_failed' => 'ERP Übergabe Fehler',
            'order_submitted' => 'Bestellung protokolliert',
        );

        return isset($labels[$event_type]) ? $labels[$event_type] : $event_type;
    }

    private function get_checkout_funnel_metrics($filters) {
        $events = $this->get_checkout_funnel_filtered_events($filters);
        $steps = $this->get_checkout_funnel_steps();
        $sets = array();
        $raw_counts = array();
        $latest_by_identity = array();

        foreach (array_keys($steps) as $event_type) {
            $sets[$event_type] = array();
            $raw_counts[$event_type] = 0;
        }
        $raw_counts['cart_data_failed'] = 0;

        foreach ($events as $event) {
            $event_type = isset($event['event_type']) ? sanitize_key($event['event_type']) : '';
            if (!isset($raw_counts[$event_type])) {
                $raw_counts[$event_type] = 0;
            }
            $raw_counts[$event_type]++;

            $set_event_type = $event_type === 'paypal_clicked' ? 'payment_clicked' : $event_type;
            if (in_array($event_type, array('cart_data_detected', 'cart_data_success', 'order_submitted'), true) && isset($sets['payment_clicked'])) {
                $set_event_type = 'payment_clicked';
            }
            if ($set_event_type !== $event_type && isset($raw_counts[$set_event_type])) {
                $raw_counts[$set_event_type]++;
            }

            if (!isset($sets[$set_event_type])) {
                continue;
            }

            $identity = $this->get_checkout_funnel_event_identity($event);
            $sets[$set_event_type][$identity] = true;
            if (!isset($latest_by_identity[$identity]) || strcmp((string) $event['created_at'], (string) $latest_by_identity[$identity]['created_at']) > 0) {
                $latest_by_identity[$identity] = $event;
            }
        }

        $step_rows = array();
        $previous_count = null;
        foreach ($steps as $event_type => $definition) {
            $count = count($sets[$event_type]);
            $rate_from_previous = $previous_count === null ? 100.0 : ($previous_count > 0 ? round(($count / $previous_count) * 100, 1) : 0.0);
            $step_rows[] = array(
                'event_type' => $event_type,
                'label' => $definition['label'],
                'description' => $definition['description'],
                'count' => $count,
                'raw_count' => isset($raw_counts[$event_type]) ? intval($raw_counts[$event_type]) : 0,
                'rate_from_previous' => $rate_from_previous,
            );
            $previous_count = $count;
        }

        $dropoffs = array();
        $step_keys = array_keys($steps);
        for ($i = 0; $i < count($step_keys) - 1; $i++) {
            $current_key = $step_keys[$i];
            $next_key = $step_keys[$i + 1];
            $lost = array_diff_key($sets[$current_key], $sets[$next_key]);
            $base_count = count($sets[$current_key]);
            $dropoffs[] = array(
                'label' => $steps[$current_key]['label'] . ' → ' . $steps[$next_key]['label'],
                'count' => count($lost),
                'rate' => $base_count > 0 ? round((count($lost) / $base_count) * 100, 1) : 0.0,
            );
        }

        usort($events, function($a, $b) {
            return strcmp((string) $b['created_at'], (string) $a['created_at']);
        });

        return array(
            'steps' => $step_rows,
            'dropoffs' => $dropoffs,
            'recent_events' => array_slice($events, 0, 80),
            'failed_count' => isset($raw_counts['cart_data_failed']) ? intval($raw_counts['cart_data_failed']) : 0,
        );
    }

    private function get_dashboard_order_identity($order) {
        if (!empty($order['contact_id'])) {
            return 'contact:' . intval($order['contact_id']);
        }

        $email = strtolower(sanitize_email(isset($order['email']) ? $order['email'] : ''));
        if ($email !== '') {
            return 'email:' . $email;
        }

        return '';
    }

    private function get_dashboard_lead_identity($event) {
        if (!empty($event['contact_id'])) {
            return 'contact:' . intval($event['contact_id']);
        }

        $email = strtolower(sanitize_email(isset($event['email']) ? $event['email'] : ''));
        if ($email !== '') {
            return 'email:' . $email;
        }

        return '';
    }

    private function get_dashboard_order_items($order) {
        $items = array();
        $payload = array();

        if (!empty($order['order_payload'])) {
            $decoded = json_decode((string) $order['order_payload'], true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        foreach ($payload as $item) {
            if (!is_array($item)) continue;
            $service_id = isset($item['service_id']) ? intval($item['service_id']) : 0;
            $name = sanitize_text_field(isset($item['name']) ? $item['name'] : '');
            $item_total = round((float) (isset($item['price_brutto']) ? $item['price_brutto'] : 0), 2);
            $people_count = max(0, intval(isset($item['ppl_adult']) ? $item['ppl_adult'] : 0))
                + max(0, intval(isset($item['ppl_child']) ? $item['ppl_child'] : 0))
                + max(0, intval(isset($item['ppl_baby']) ? $item['ppl_baby'] : 0));

            $items[] = array(
                'service_id' => $service_id,
                'name' => $name !== '' ? $name : ('Service ' . $service_id),
                'revenue' => $item_total,
                'people_count' => $people_count,
            );
        }

        if (!empty($items)) {
            return $items;
        }

        $service_ids = $this->get_dashboard_service_ids_from_value(isset($order['service_ids']) ? $order['service_ids'] : '');
        $names = array_map('trim', explode('|', (string) (isset($order['service_names']) ? $order['service_names'] : '')));
        foreach ($service_ids as $index => $service_id) {
            $items[] = array(
                'service_id' => $service_id,
                'name' => !empty($names[$index]) ? sanitize_text_field($names[$index]) : ('Service ' . $service_id),
                'revenue' => 0,
                'people_count' => max(0, intval(isset($order['people_count']) ? $order['people_count'] : 0)),
            );
        }

        return $items;
    }

    private function get_dashboard_order_tour_start_labels($order) {
        $labels = array();
        $payload = $this->decode_json_array(isset($order['order_payload']) ? $order['order_payload'] : '');

        foreach ($payload as $item) {
            if (!is_array($item)) {
                continue;
            }

            $tour_start = '';
            if (!empty($item['quotas_begin_time'])) {
                $tour_start = sanitize_text_field((string) $item['quotas_begin_time']);
            } elseif (!empty($item['date']) && !empty($item['time'])) {
                $tour_start = sanitize_text_field((string) $item['date'] . ' ' . $item['time']);
            } elseif (!empty($item['date'])) {
                $tour_start = sanitize_text_field((string) $item['date']);
            }

            if ($tour_start === '') {
                continue;
            }

            $timestamp = $this->parse_wp_local_datetime_timestamp($tour_start);
            $sort_key = $timestamp ? (string) $timestamp : $tour_start;
            $labels[$sort_key] = $this->format_portal_datetime_label($tour_start);
        }

        if (empty($labels)) {
            return array();
        }

        uksort($labels, function($a, $b) {
            if (is_numeric($a) && is_numeric($b)) {
                return intval($a) <=> intval($b);
            }
            return strcmp((string) $a, (string) $b);
        });

        return array_values(array_filter($labels));
    }

    private function get_upcoming_transactional_email_candidates($limit = 10) {
        $limit = max(1, min(50, intval($limit)));
        if (!$this->is_transactional_email_module_enabled()) {
            return array();
        }

        $definitions = $this->get_transactional_email_rule_definitions();
        $templates = $this->get_transactional_email_templates();
        $supported_rules = $this->get_supported_transactional_email_auto_rule_keys();
        $send_mode = $this->is_transactional_email_test_mode_enabled() ? 'test' : 'live';
        $test_recipient = $this->get_transactional_email_test_recipient();
        $now = $this->get_wp_current_timestamp();
        $window_end = strtotime('+7 days', $now);
        $candidates = array();

        foreach ($definitions as $rule) {
            if (!in_array($rule['key'], $supported_rules, true)) {
                continue;
            }

            $template = isset($templates[$rule['key']]) ? $templates[$rule['key']] : null;
            if (!$template || $template['status'] !== 'active') {
                continue;
            }

            foreach ($this->get_transactional_email_rule_row_candidates($rule) as $candidate) {
                $row = $candidate['row'];
                $order = $candidate['order'];
                if (empty($row['tour_start_ts'])) {
                    continue;
                }

                $send_ts = $this->get_transactional_email_send_timestamp($rule, intval($row['tour_start_ts']));
                if (!$send_ts || $send_ts < $now || $send_ts > $window_end) {
                    continue;
                }

                $order_id = intval(isset($order['id']) ? $order['id'] : 0);
                $customer_name = trim(((string) ($order['first_name'] ?? '')) . ' ' . ((string) ($order['last_name'] ?? '')));
                $original_email = sanitize_email(isset($order['email']) ? $order['email'] : '');
                $recipient_email = $send_mode === 'test' ? $test_recipient : $original_email;
                $event_key = sanitize_text_field($rule['key'] . '|order:' . $order_id . '|' . $send_mode . '|' . wp_date('Y-m-d', $send_ts));
                $already_logged = $this->transactional_email_event_key_exists($event_key);

                $candidates[] = array(
                    'rule_key' => $rule['key'],
                    'rule_title' => $rule['title'],
                    'send_ts' => intval($send_ts),
                    'send_label' => wp_date('d.m.Y H:i', $send_ts),
                    'tour_name' => isset($row['tour_name']) ? sanitize_text_field((string) $row['tour_name']) : 'Tour',
                    'tour_date_label' => $this->format_portal_datetime_label(isset($row['tour_start']) ? $row['tour_start'] : ''),
                    'customer_name' => $customer_name !== '' ? $customer_name : 'Ohne Namen',
                    'original_email' => $original_email,
                    'recipient_email' => $recipient_email,
                    'people_count' => intval(isset($row['people_count']) ? $row['people_count'] : 0),
                    'status_label' => $already_logged ? 'Bereits protokolliert' : 'Geplant',
                );
            }
        }

        usort($candidates, function($a, $b) {
            if ($a['send_ts'] === $b['send_ts']) {
                return strcmp((string) $a['tour_name'], (string) $b['tour_name']);
            }
            return $a['send_ts'] <=> $b['send_ts'];
        });

        return array_slice($candidates, 0, $limit);
    }

    private function get_due_transactional_email_candidates($rule) {
        $due = array();
        foreach ($this->get_transactional_email_rule_row_candidates($rule) as $candidate) {
            if ($this->should_send_transactional_email_today($rule, $candidate['row'])) {
                $due[] = $candidate;
            }
        }
        return $due;
    }

    private function get_orders_summary($orders) {
        $total_orders = count((array) $orders);
        $total_revenue = 0.0;

        foreach ((array) $orders as $order) {
            $total_revenue += round((float) (isset($order['order_total']) ? $order['order_total'] : 0), 2);
        }

        return array(
            'total_orders' => $total_orders,
            'total_revenue' => round($total_revenue, 2),
            'average_order_value' => $total_orders > 0 ? round($total_revenue / $total_orders, 2) : 0.0,
        );
    }

    private function get_dashboard_source_breakdown($orders) {
        $rows = array(
            'niers' => array('key' => 'niers', 'label' => 'Niers / Einzeltouren', 'orders' => 0, 'revenue' => 0.0, 'people_count' => 0),
            'kombi' => array('key' => 'kombi', 'label' => 'Kombi-Touren', 'orders' => 0, 'revenue' => 0.0, 'people_count' => 0),
        );

        foreach ((array) $orders as $order) {
            $group = $this->get_dashboard_order_source_group($order);
            if (!isset($rows[$group])) {
                $rows[$group] = array('key' => $group, 'label' => ucfirst($group), 'orders' => 0, 'revenue' => 0.0, 'people_count' => 0);
            }
            $rows[$group]['orders']++;
            $rows[$group]['revenue'] += round((float) (isset($order['order_total']) ? $order['order_total'] : 0), 2);
            $rows[$group]['people_count'] += max(0, intval(isset($order['people_count']) ? $order['people_count'] : 0));
        }

        foreach ($rows as &$row) {
            $row['average_order_value'] = $row['orders'] > 0 ? round($row['revenue'] / $row['orders'], 2) : 0.0;
        }
        unset($row);

        return array_values($rows);
    }

    private function get_dashboard_tour_breakdown($orders) {
        $catalog = $this->get_service_catalog();
        $product_types = $this->get_catalog_product_type_options();
        $rows = array();

        foreach ((array) $orders as $order) {
            foreach ($this->get_dashboard_order_items($order) as $item) {
                $service_id = intval(isset($item['service_id']) ? $item['service_id'] : 0);
                $key = $service_id > 0 ? 'service:' . $service_id : 'name:' . md5((string) $item['name']);
                if (!isset($rows[$key])) {
                    $product_type = ($service_id > 0 && !empty($catalog[$service_id]['product_type'])) ? $catalog[$service_id]['product_type'] : 'unknown';
                    $rows[$key] = array(
                        'service_id' => $service_id,
                        'name' => isset($item['name']) ? $item['name'] : ('Service ' . $service_id),
                        'product_type' => $product_type,
                        'product_type_label' => isset($product_types[$product_type]) ? $product_types[$product_type] : ucfirst($product_type),
                        'source_group' => $product_type === 'kombi' ? 'kombi' : 'niers',
                        'revenue' => 0.0,
                        'bookings' => 0,
                        'people_count' => 0,
                    );
                }

                $rows[$key]['revenue'] += round((float) (isset($item['revenue']) ? $item['revenue'] : 0), 2);
                $rows[$key]['bookings']++;
                $rows[$key]['people_count'] += max(0, intval(isset($item['people_count']) ? $item['people_count'] : 0));
            }
        }

        $rows = array_values($rows);
        usort($rows, function($a, $b) {
            if ((float) $a['revenue'] === (float) $b['revenue']) {
                return strcmp((string) $a['name'], (string) $b['name']);
            }
            return ((float) $a['revenue'] < (float) $b['revenue']) ? 1 : -1;
        });

        return $rows;
    }

    private function get_dashboard_conversion_metrics($filters, $orders = null) {
        $orders = is_array($orders) ? $orders : $this->filter_dashboard_orders_by_source_group(
            $this->get_dashboard_filtered_orders($filters),
            isset($filters['source_group']) ? $filters['source_group'] : 'all'
        );
        $lead_events = $this->filter_dashboard_lead_events_by_source_group(
            $this->get_dashboard_filtered_lead_events($filters),
            isset($filters['source_group']) ? $filters['source_group'] : 'all'
        );

        $lead_ids = array();
        foreach ($lead_events as $event) {
            $identity = $this->get_dashboard_lead_identity($event);
            if ($identity !== '') {
                $lead_ids[$identity] = true;
            }
        }

        $booker_ids = array();
        foreach ($orders as $order) {
            $identity = $this->get_dashboard_order_identity($order);
            if ($identity !== '') {
                $booker_ids[$identity] = true;
            }
        }

        $converted_ids = array_intersect_key($lead_ids, $booker_ids);
        $lead_count = count($lead_ids);
        $booker_count = count($booker_ids);
        $converted_count = count($converted_ids);

        return array(
            'lead_count' => $lead_count,
            'booker_count' => $booker_count,
            'converted_count' => $converted_count,
            'conversion_rate' => $lead_count > 0 ? round(($converted_count / $lead_count) * 100, 1) : 0.0,
        );
    }

    private function is_test_order_payload($payload) {
        $email = strtolower(sanitize_email(isset($payload['email']) ? $payload['email'] : ''));
        if ($email === '') {
            return false;
        }
        return in_array($email, $this->get_test_order_email_list(), true);
    }

    private function track_order_submission($payload) {
        global $wpdb;

        $table = $this->get_orders_table_name();
        $request_token = sanitize_text_field(isset($payload['request_token']) ? $payload['request_token'] : '');
        $paypal_order_id = sanitize_text_field(isset($payload['paypal_order_id']) ? $payload['paypal_order_id'] : '');
        $email = sanitize_email(isset($payload['email']) ? $payload['email'] : '');
        $is_test = $this->is_test_order_payload($payload) ? 1 : 0;

        $existing = null;
        if ($paypal_order_id !== '') {
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE paypal_order_id = %s", $paypal_order_id), ARRAY_A);
        }
        if (!$existing && $request_token !== '') {
            $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE request_token = %s", $request_token), ARRAY_A);
        }

        $contact_id = 0;
        if ($email !== '') {
            $contact_id = $this->upsert_contact_record(array(
                'email' => $email,
                'first_name' => isset($payload['first_name']) ? $payload['first_name'] : '',
                'last_name' => isset($payload['last_name']) ? $payload['last_name'] : '',
                'company' => isset($payload['company']) ? $payload['company'] : '',
                'gender' => isset($payload['gender']) ? $payload['gender'] : '',
                'title' => isset($payload['title']) ? $payload['title'] : '',
                'additional_name' => isset($payload['additional_name']) ? $payload['additional_name'] : '',
                'street' => isset($payload['street']) ? $payload['street'] : '',
                'zip' => isset($payload['zip']) ? $payload['zip'] : '',
                'city' => isset($payload['city']) ? $payload['city'] : '',
                'phone' => isset($payload['phone']) ? $payload['phone'] : '',
                'mobile' => isset($payload['mobile']) ? $payload['mobile'] : '',
                'newsletter_opt_in' => !empty($payload['newsletter_opt_in']) ? 1 : 0,
                'source' => isset($payload['source']) ? $payload['source'] : 'checkout-cart-data',
                'source_page' => isset($payload['source_page']) ? $payload['source_page'] : '',
                'request_token' => $request_token,
                'paypal_order_id' => $paypal_order_id,
                'last_service_ids' => isset($payload['service_ids']) ? $payload['service_ids'] : '',
                'booking_context' => isset($payload['order_payload']) ? $payload['order_payload'] : array(),
                'status' => 'booked',
            ));
        }

        $now = current_time('mysql');
        $data = array(
            'contact_id' => $contact_id ?: null,
            'request_token' => $request_token ?: null,
            'paypal_order_id' => $paypal_order_id ?: null,
            'email' => $email,
            'first_name' => sanitize_text_field(isset($payload['first_name']) ? $payload['first_name'] : ''),
            'last_name' => sanitize_text_field(isset($payload['last_name']) ? $payload['last_name'] : ''),
            'source' => sanitize_text_field(isset($payload['source']) ? $payload['source'] : 'checkout-cart-data'),
            'source_page' => esc_url_raw(isset($payload['source_page']) ? $payload['source_page'] : ''),
            'service_ids' => sanitize_text_field(isset($payload['service_ids']) ? $payload['service_ids'] : ''),
            'service_names' => sanitize_text_field(isset($payload['service_names']) ? $payload['service_names'] : ''),
            'item_count' => max(0, intval(isset($payload['item_count']) ? $payload['item_count'] : 0)),
            'people_count' => max(0, intval(isset($payload['people_count']) ? $payload['people_count'] : 0)),
            'order_total' => round((float) (isset($payload['order_total']) ? $payload['order_total'] : 0), 2),
            'currency' => sanitize_text_field(isset($payload['currency']) ? $payload['currency'] : 'EUR'),
            'payment_provider' => sanitize_text_field(isset($payload['payment_provider']) ? $payload['payment_provider'] : 'paypal'),
            'order_payload' => wp_json_encode(isset($payload['order_payload']) ? $payload['order_payload'] : array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'is_test' => $is_test,
            'status' => 'submitted',
            'updated_at' => $now,
        );
        $formats = array('%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%d','%d','%f','%s','%s','%s','%d','%s','%s');

        if ($existing) {
            $wpdb->update($table, $data, array('id' => intval($existing['id'])), $formats, array('%d'));
            $order_id = intval($existing['id']);
        } else {
            $data['created_at'] = $now;
            $formats[] = '%s';
            $wpdb->insert($table, $data, $formats);
            $order_id = intval($wpdb->insert_id);
        }

        if ($contact_id > 0) {
            $this->insert_contact_event(array(
                'contact_id' => $contact_id,
                'email' => $email,
                'event_type' => 'order_submitted',
                'source' => isset($payload['source']) ? $payload['source'] : 'checkout-cart-data',
                'source_page' => isset($payload['source_page']) ? $payload['source_page'] : '',
                'request_token' => $request_token,
                'paypal_order_id' => $paypal_order_id,
                'event_payload' => array(
                    'order_id' => $order_id,
                    'order_total' => $data['order_total'],
                    'service_ids' => $data['service_ids'],
                ),
            ));
        }

        return $order_id;
    }

    private function generate_voucher_code($prefix = 'FXP', $length = 8) {
        $prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string) $prefix));
        if ($prefix === '') {
            $prefix = 'FXP';
        }

        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $max_index = strlen($alphabet) - 1;
        $code = $prefix . '-';

        for ($i = 0; $i < $length; $i++) {
            $code .= $alphabet[random_int(0, $max_index)];
        }

        return $code;
    }

    private function voucher_code_exists($code, $exclude_id = 0) {
        global $wpdb;

        $table = $this->get_vouchers_table_name();
        if ($exclude_id > 0) {
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE code = %s AND id != %d", $code, $exclude_id));
        } else {
            $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE code = %s", $code));
        }

        return intval($count) > 0;
    }

    private function ensure_unique_voucher_code($requested_code, $exclude_id = 0) {
        $code = strtoupper(trim((string) $requested_code));
        $code = preg_replace('/[^A-Z0-9\-]/', '', $code);

        if ($code !== '' && !$this->voucher_code_exists($code, $exclude_id)) {
            return $code;
        }

        do {
            $code = $this->generate_voucher_code();
        } while ($this->voucher_code_exists($code, $exclude_id));

        return $code;
    }

    private function get_voucher($voucher_id) {
        global $wpdb;

        $table = $this->get_vouchers_table_name();
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", intval($voucher_id)), ARRAY_A);
    }

    private function get_all_vouchers() {
        global $wpdb;

        $table = $this->get_vouchers_table_name();
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY updated_at DESC, id DESC", ARRAY_A);
    }

    private function get_voucher_summary() {
        global $wpdb;

        $table = $this->get_vouchers_table_name();
        $row = $wpdb->get_row("
            SELECT
                COUNT(*) AS total_count,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active_count,
                SUM(CASE WHEN voucher_type = 'fixed' THEN amount ELSE 0 END) AS fixed_volume,
                SUM(CASE WHEN voucher_type = 'fixed' THEN COALESCE(remaining_amount, amount) ELSE 0 END) AS open_volume
            FROM {$table}
        ", ARRAY_A);

        return array(
            'total_count' => intval(isset($row['total_count']) ? $row['total_count'] : 0),
            'active_count' => intval(isset($row['active_count']) ? $row['active_count'] : 0),
            'fixed_volume' => floatval(isset($row['fixed_volume']) ? $row['fixed_volume'] : 0),
            'open_volume' => floatval(isset($row['open_volume']) ? $row['open_volume'] : 0),
        );
    }

    private function get_default_voucher_form_values() {
        return array(
            'id' => 0,
            'code' => '',
            'voucher_type' => 'fixed',
            'amount' => '50.00',
            'remaining_amount' => '',
            'usage_limit' => '',
            'status' => 'active',
            'valid_from' => '',
            'valid_until' => '',
            'allowed_service_ids' => '',
            'notes' => '',
        );
    }

    public function handle_save_voucher_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_vouchers_module_enabled()) wp_die('Gutscheinmodul ist deaktiviert.');
        check_admin_referer('niers_kombi_save_voucher', 'niers_kombi_save_voucher_nonce');

        global $wpdb;

        $table = $this->get_vouchers_table_name();
        $voucher_id = isset($_POST['voucher_id']) ? intval($_POST['voucher_id']) : 0;
        $type = isset($_POST['voucher_type']) ? sanitize_key(wp_unslash($_POST['voucher_type'])) : 'fixed';
        $status = isset($_POST['status']) ? sanitize_key(wp_unslash($_POST['status'])) : 'active';
        $amount = isset($_POST['amount']) ? round((float) wp_unslash($_POST['amount']), 2) : 0.0;
        $remaining_amount = isset($_POST['remaining_amount']) && $_POST['remaining_amount'] !== '' ? round((float) wp_unslash($_POST['remaining_amount']), 2) : null;
        $usage_limit = isset($_POST['usage_limit']) && $_POST['usage_limit'] !== '' ? max(1, intval($_POST['usage_limit'])) : null;
        $valid_from = $this->normalize_voucher_datetime(isset($_POST['valid_from']) ? wp_unslash($_POST['valid_from']) : '');
        $valid_until = $this->normalize_voucher_datetime(isset($_POST['valid_until']) ? wp_unslash($_POST['valid_until']) : '');
        $allowed_service_ids = $this->normalize_voucher_service_ids(isset($_POST['allowed_service_ids']) ? wp_unslash($_POST['allowed_service_ids']) : '');
        $notes = isset($_POST['notes']) ? sanitize_textarea_field(wp_unslash($_POST['notes'])) : '';

        if (!isset($this->get_voucher_type_options()[$type])) $type = 'fixed';
        if (!isset($this->get_voucher_status_options()[$status])) $status = 'active';
        if ($type === 'percent') {
            $amount = max(0, min(100, $amount));
            $remaining_amount = null;
        } else {
            $amount = max(0, $amount);
            if ($remaining_amount === null && $voucher_id === 0) {
                $remaining_amount = $amount;
            }
            if ($remaining_amount !== null) {
                $remaining_amount = max(0, $remaining_amount);
            }
        }

        $code = $this->ensure_unique_voucher_code(isset($_POST['code']) ? wp_unslash($_POST['code']) : '', $voucher_id);
        $now = current_time('mysql');
        $data = array(
            'code' => $code,
            'voucher_type' => $type,
            'amount' => $amount,
            'remaining_amount' => $remaining_amount,
            'usage_limit' => $usage_limit,
            'status' => $status,
            'valid_from' => $valid_from,
            'valid_until' => $valid_until,
            'allowed_service_ids' => $allowed_service_ids,
            'notes' => $notes,
            'updated_at' => $now,
        );
        $formats = array('%s', '%s', '%f', '%f', '%d', '%s', '%s', '%s', '%s', '%s', '%s');

        if ($voucher_id > 0) {
            $wpdb->update($table, $data, array('id' => $voucher_id), $formats, array('%d'));
        } else {
            $data['usage_count'] = 0;
            $data['created_at'] = $now;
            $wpdb->insert(
                $table,
                $data,
                array('%s', '%s', '%f', '%f', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
            );
            $voucher_id = intval($wpdb->insert_id);
        }

        wp_safe_redirect($this->get_admin_page_url('niers-kombi-vouchers', array('voucher_saved' => 1, 'voucher_id' => $voucher_id)));
        exit;
    }

    public function handle_delete_voucher_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_vouchers_module_enabled()) wp_die('Gutscheinmodul ist deaktiviert.');
        check_admin_referer('niers_kombi_delete_voucher', 'niers_kombi_delete_voucher_nonce');

        global $wpdb;

        $voucher_id = isset($_POST['voucher_id']) ? intval($_POST['voucher_id']) : 0;
        if ($voucher_id > 0) {
            $wpdb->update(
                $this->get_vouchers_table_name(),
                array(
                    'status' => 'archived',
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $voucher_id),
                array('%s', '%s'),
                array('%d')
            );
        }

        wp_safe_redirect($this->get_admin_page_url('niers-kombi-vouchers', array('voucher_deleted' => 1)));
        exit;
    }

    public function handle_export_vouchers_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_vouchers_module_enabled()) wp_die('Gutscheinmodul ist deaktiviert.');
        check_admin_referer('niers_kombi_export_vouchers', 'niers_kombi_export_vouchers_nonce');

        $vouchers = $this->get_all_vouchers();

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=freizeitexperten-gutscheine-' . gmdate('Ymd-His') . '.csv');

        $out = fopen('php://output', 'w');
        fputcsv($out, array('id', 'code', 'voucher_type', 'amount', 'remaining_amount', 'usage_limit', 'usage_count', 'status', 'valid_from', 'valid_until', 'allowed_service_ids', 'notes', 'created_at', 'updated_at'), ';');

        foreach ($vouchers as $voucher) {
            fputcsv($out, array(
                $voucher['id'],
                $voucher['code'],
                $voucher['voucher_type'],
                $voucher['amount'],
                $voucher['remaining_amount'],
                $voucher['usage_limit'],
                $voucher['usage_count'],
                $voucher['status'],
                $voucher['valid_from'],
                $voucher['valid_until'],
                $voucher['allowed_service_ids'],
                $voucher['notes'],
                $voucher['created_at'],
                $voucher['updated_at'],
            ), ';');
        }

        fclose($out);
        exit;
    }

    private function render_vouchers_admin_section() {
        $editing_voucher_id = isset($_GET['voucher_id']) ? intval($_GET['voucher_id']) : 0;
        $editing_voucher = $editing_voucher_id > 0 ? $this->get_voucher($editing_voucher_id) : null;
        $form_values = $this->get_default_voucher_form_values();
        if (is_array($editing_voucher)) {
            $form_values = array_merge($form_values, $editing_voucher);
            $form_values['valid_from'] = $this->format_voucher_datetime_for_input($editing_voucher['valid_from']);
            $form_values['valid_until'] = $this->format_voucher_datetime_for_input($editing_voucher['valid_until']);
        }

        $summary = $this->get_voucher_summary();
        $vouchers = $this->get_all_vouchers();
        $status_options = $this->get_voucher_status_options();
        $type_options = $this->get_voucher_type_options();
        ?>
        <div style="display:grid; grid-template-columns:minmax(340px, 420px) minmax(0, 1fr); gap:20px; align-items:start;">
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <h2 style="margin-top:0;"><?php echo $editing_voucher ? 'Gutschein bearbeiten' : 'Neuen Gutschein anlegen'; ?></h2>
                <p>Diese erste Ausbaustufe legt die Gutschein-Stammdaten sauber im Plugin an. Die Einlöse-Logik kann danach stabil an Warenkorb und Checkout angeschlossen werden.</p>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('niers_kombi_save_voucher', 'niers_kombi_save_voucher_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_save_voucher">
                    <input type="hidden" name="voucher_id" value="<?php echo intval($form_values['id']); ?>">

                    <table class="form-table" style="margin-top:10px;">
                        <tr>
                            <th scope="row"><label for="voucher-code">Code</label></th>
                            <td>
                                <input type="text" id="voucher-code" name="code" value="<?php echo esc_attr($form_values['code']); ?>" class="regular-text" placeholder="z.B. FXP-SOMMER2026">
                                <p class="description">Leer lassen = Code wird automatisch erzeugt.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-type">Typ</label></th>
                            <td>
                                <select id="voucher-type" name="voucher_type">
                                    <?php foreach ($type_options as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($form_values['voucher_type'], $value); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-amount">Wert</label></th>
                            <td>
                                <input type="number" id="voucher-amount" name="amount" value="<?php echo esc_attr($form_values['amount']); ?>" step="0.01" min="0" class="small-text">
                                <p class="description">Bei Wertgutscheinen in Euro, bei Rabattcodes in Prozent.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-remaining-amount">Restwert</label></th>
                            <td>
                                <input type="number" id="voucher-remaining-amount" name="remaining_amount" value="<?php echo esc_attr($form_values['remaining_amount']); ?>" step="0.01" min="0" class="small-text">
                                <p class="description">Nur bei Wertgutscheinen relevant. Leer = bei neuen Gutscheinen automatisch auf den Gutscheinwert setzen.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-usage-limit">Nutzungs-Limit</label></th>
                            <td>
                                <input type="number" id="voucher-usage-limit" name="usage_limit" value="<?php echo esc_attr($form_values['usage_limit']); ?>" min="1" class="small-text">
                                <p class="description">Leer = unbegrenzt. Für Einmalcodes einfach <code>1</code> eintragen.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-status">Status</label></th>
                            <td>
                                <select id="voucher-status" name="status">
                                    <?php foreach ($status_options as $value => $label): ?>
                                    <option value="<?php echo esc_attr($value); ?>" <?php selected($form_values['status'], $value); ?>><?php echo esc_html($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-valid-from">Gültig ab</label></th>
                            <td><input type="datetime-local" id="voucher-valid-from" name="valid_from" value="<?php echo esc_attr($form_values['valid_from']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-valid-until">Gültig bis</label></th>
                            <td><input type="datetime-local" id="voucher-valid-until" name="valid_until" value="<?php echo esc_attr($form_values['valid_until']); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-services">Erlaubte Service-IDs</label></th>
                            <td>
                                <input type="text" id="voucher-services" name="allowed_service_ids" value="<?php echo esc_attr($form_values['allowed_service_ids']); ?>" class="regular-text" placeholder="z.B. 202,203,514">
                                <p class="description">Optional. Leer = auf alle Touren anwendbar.</p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="voucher-notes">Notizen</label></th>
                            <td><textarea id="voucher-notes" name="notes" rows="4" class="large-text"><?php echo esc_textarea($form_values['notes']); ?></textarea></td>
                        </tr>
                    </table>

                    <?php submit_button($editing_voucher ? 'Gutschein aktualisieren' : 'Gutschein anlegen'); ?>
                    <?php if ($editing_voucher): ?>
                        <a href="<?php echo esc_url($this->get_admin_page_url('niers-kombi-vouchers')); ?>" class="button" style="margin-left:8px;">Neu anlegen</a>
                    <?php endif; ?>
                </form>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h2 style="margin-top:0;">Gutschein-Übersicht</h2>
                        <p style="margin-bottom:8px;">Aktuell gespeicherte Gutscheine, bereit für spätere Einlösung, Import/Export und Umsatz-/Restwert-Auswertungen.</p>
                    </div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('niers_kombi_export_vouchers', 'niers_kombi_export_vouchers_nonce'); ?>
                        <input type="hidden" name="action" value="niers_kombi_export_vouchers">
                        <?php submit_button('CSV exportieren', 'secondary', 'submit', false); ?>
                    </form>
                </div>

                <div style="display:grid; grid-template-columns:repeat(4, minmax(140px, 1fr)); gap:12px; margin:16px 0 18px;">
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Gutscheine gesamt</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($summary['total_count']); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Aktiv</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($summary['active_count']); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Volumen Wertgutscheine</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo esc_html(number_format($summary['fixed_volume'], 2, ',', '.') . ' €'); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Offener Restwert</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo esc_html(number_format($summary['open_volume'], 2, ',', '.') . ' €'); ?></div>
                    </div>
                </div>

                <?php if (empty($vouchers)): ?>
                    <p><em>Noch keine Gutscheine angelegt.</em></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:160px;">Code</th>
                                <th style="width:120px;">Typ</th>
                                <th style="width:110px;">Wert</th>
                                <th style="width:110px;">Restwert</th>
                                <th style="width:110px;">Nutzung</th>
                                <th style="width:110px;">Status</th>
                                <th>Services</th>
                                <th style="width:145px;">Gültig</th>
                                <th style="width:145px;">Update</th>
                                <th style="width:150px;">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vouchers as $voucher): ?>
                            <tr>
                                <td><strong><?php echo esc_html($voucher['code']); ?></strong></td>
                                <td><?php echo esc_html(isset($type_options[$voucher['voucher_type']]) ? $type_options[$voucher['voucher_type']] : $voucher['voucher_type']); ?></td>
                                <td><?php echo $voucher['voucher_type'] === 'percent'
                                    ? esc_html(number_format((float) $voucher['amount'], 2, ',', '.') . ' %')
                                    : esc_html(number_format((float) $voucher['amount'], 2, ',', '.') . ' €'); ?></td>
                                <td><?php echo $voucher['voucher_type'] === 'fixed' && $voucher['remaining_amount'] !== null
                                    ? esc_html(number_format((float) $voucher['remaining_amount'], 2, ',', '.') . ' €')
                                    : '—'; ?></td>
                                <td><?php echo intval($voucher['usage_count']); ?><?php echo $voucher['usage_limit'] ? ' / ' . intval($voucher['usage_limit']) : ''; ?></td>
                                <td><?php echo esc_html(isset($status_options[$voucher['status']]) ? $status_options[$voucher['status']] : $voucher['status']); ?></td>
                                <td><?php echo !empty($voucher['allowed_service_ids']) ? esc_html($voucher['allowed_service_ids']) : 'Alle'; ?></td>
                                <td>
                                    <?php
                                    $valid_parts = array();
                                    if (!empty($voucher['valid_from'])) $valid_parts[] = 'ab ' . esc_html(mysql2date('d.m.Y', $voucher['valid_from']));
                                    if (!empty($voucher['valid_until'])) $valid_parts[] = 'bis ' . esc_html(mysql2date('d.m.Y', $voucher['valid_until']));
                                    echo !empty($valid_parts) ? implode('<br>', $valid_parts) : 'Offen';
                                    ?>
                                </td>
                                <td><?php echo !empty($voucher['updated_at']) ? esc_html(mysql2date('d.m.Y H:i', $voucher['updated_at'])) : '—'; ?></td>
                                <td>
                                    <a class="button button-small" href="<?php echo esc_url($this->get_admin_page_url('niers-kombi-vouchers', array('voucher_id' => intval($voucher['id'])))); ?>">Bearbeiten</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;" onsubmit="return confirm('Diesen Gutschein wirklich archivieren?');">
                                        <?php wp_nonce_field('niers_kombi_delete_voucher', 'niers_kombi_delete_voucher_nonce'); ?>
                                        <input type="hidden" name="action" value="niers_kombi_delete_voucher">
                                        <input type="hidden" name="voucher_id" value="<?php echo intval($voucher['id']); ?>">
                                        <button type="submit" class="button button-small">Archivieren</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_contacts_admin_section() {
        $filters = $this->get_contacts_filters();
        $consent_filter_options = $this->get_contact_consent_filter_options();
        $contacts = $this->get_all_contacts(250, $filters);
        $booking_flags = $this->get_contact_booking_flags($contacts);
        $summary = $this->get_contact_summary();
        ?>
        <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
            <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                <div>
                    <h2 style="margin-top:0;">Kundendaten</h2>
                    <p>Hier landen die vor dem Checkout erfassten Kontaktdaten aus dem neuen Zwischenschritt. Diese Datenbasis ist die Grundlage für spätere Marketing-Automationen, transaktionale E-Mails und das Umsatz-Dashboard.</p>
                </div>
                <div style="display:grid; grid-template-columns:repeat(4, minmax(140px, 1fr)); gap:10px; min-width:min(100%, 640px);">
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Kontakte gesamt</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($summary['total_count']); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Newsletter Opt-ins</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($summary['newsletter_count']); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Reminder Opt-ins</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($summary['booking_reminder_count']); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Status Gebucht</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($summary['booked_count']); ?></div>
                    </div>
                </div>
            </div>

            <div style="margin:16px 0 20px; padding:14px 16px; background:#f8fafc; border-left:4px solid #2e7d28;">
                <strong>Nächster Schritt:</strong> Auf einer WordPress-Seite den Shortcode <code>[fxp_booking_contact_step]</code> einfügen und in den Plugin-Einstellungen den Zwischenschritt aktivieren. Danach werden Kundendaten vor dem eigentlichen Warenkorb in WordPress gespeichert und im Checkout vorausgefüllt.
            </div>

            <div style="display:flex; justify-content:space-between; gap:16px; align-items:end; flex-wrap:wrap; margin-bottom:20px;">
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                    <input type="hidden" name="page" value="niers-kombi-contacts">
                    <div>
                        <label for="niers-kombi-contact-consent-filter" style="display:block; font-weight:600; margin-bottom:6px;">Einwilligungsfilter</label>
                        <select id="niers-kombi-contact-consent-filter" name="consent_filter">
                            <?php foreach ($consent_filter_options as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($filters['consent_filter'], $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="button button-primary">Filter anwenden</button>
                    </div>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
                    <?php wp_nonce_field('niers_kombi_export_contacts', 'niers_kombi_export_contacts_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_export_contacts">
                    <input type="hidden" name="consent_filter" value="<?php echo esc_attr($filters['consent_filter']); ?>">
                    <button type="submit" class="button">Gefilterte Kundendaten exportieren</button>
                </form>
            </div>
            <p style="margin-top:0; color:#64748b;">Aktiver Filter: <strong><?php echo esc_html($consent_filter_options[$filters['consent_filter']]); ?></strong> · Angezeigte Kontakte: <strong><?php echo intval(count($contacts)); ?></strong></p>

            <?php if (empty($contacts)): ?>
                <p><em>Noch keine Kundendaten erfasst.</em></p>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:220px;">Kontakt</th>
                            <th style="width:150px;">Status</th>
                            <th style="width:110px;">Gebucht</th>
                            <th>Adresse / Telefon</th>
                            <th style="width:140px;">Einwilligungen</th>
                            <th style="width:130px;">Letzte Services</th>
                            <th style="width:160px;">Quelle</th>
                            <th style="width:150px;">Aktualisiert</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($contacts as $contact): ?>
                        <?php
                        $contact_key = !empty($contact['id']) ? 'contact:' . intval($contact['id']) : '';
                        $email_key = !empty($contact['email']) ? 'email:' . strtolower(sanitize_email((string) $contact['email'])) : '';
                        $booking_count = 0;
                        if ($contact_key !== '' && isset($booking_flags[$contact_key])) {
                            $booking_count = max($booking_count, intval($booking_flags[$contact_key]));
                        }
                        if ($email_key !== '' && isset($booking_flags[$email_key])) {
                            $booking_count = max($booking_count, intval($booking_flags[$email_key]));
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html(trim(($contact['first_name'] ?: '') . ' ' . ($contact['last_name'] ?: '')) ?: 'Ohne Namen'); ?></strong><br>
                                <a href="mailto:<?php echo esc_attr($contact['email']); ?>"><?php echo esc_html($contact['email']); ?></a>
                                <?php if (!empty($contact['company'])): ?><br><small><?php echo esc_html($contact['company']); ?></small><?php endif; ?>
                            </td>
                            <td><?php echo esc_html(isset($this->get_contact_status_options()[$contact['status']]) ? $this->get_contact_status_options()[$contact['status']] : $contact['status']); ?></td>
                            <td>
                                <?php if ($booking_count > 0): ?>
                                    Ja<br><small><?php echo intval($booking_count); ?> Buchung<?php echo $booking_count === 1 ? '' : 'en'; ?></small>
                                <?php else: ?>
                                    Nein
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $address_parts = array_filter(array($contact['street'], trim(($contact['zip'] ?: '') . ' ' . ($contact['city'] ?: ''))));
                                echo !empty($address_parts) ? esc_html(implode(', ', $address_parts)) : '—';
                                if (!empty($contact['phone']) || !empty($contact['mobile'])) {
                                    echo '<br><small>' . esc_html(trim(($contact['phone'] ?: '') . ' ' . ($contact['mobile'] ? ' / ' . $contact['mobile'] : ''))) . '</small>';
                                }
                                ?>
                            </td>
                            <td>
                                Newsletter: <?php echo !empty($contact['newsletter_opt_in']) ? 'Ja' : 'Nein'; ?>
                                <br><small>Reminder: <?php echo !empty($contact['booking_reminder_opt_in']) ? 'Ja' : 'Nein'; ?></small>
                            </td>
                            <td><?php echo !empty($contact['last_service_ids']) ? esc_html($contact['last_service_ids']) : '—'; ?></td>
                            <td>
                                <?php echo !empty($contact['source']) ? esc_html($contact['source']) : '—'; ?>
                                <?php if (!empty($contact['source_page'])): ?><br><small><a href="<?php echo esc_url($contact['source_page']); ?>" target="_blank" rel="noopener">Seite öffnen</a></small><?php endif; ?>
                            </td>
                            <td><?php echo !empty($contact['updated_at']) ? esc_html(mysql2date('d.m.Y H:i', $contact['updated_at'])) : '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    private function render_portal_admin_section() {
        $portal_url = get_option('niers_kombi_customer_portal_url', home_url('/kundenportal/'));
        $current_user = wp_get_current_user();
        $current_email = ($current_user && !empty($current_user->user_email)) ? sanitize_email((string) $current_user->user_email) : '';
        $current_contact = $current_email !== '' ? $this->get_contact_by_email($current_email) : null;
        $current_orders = is_array($current_contact) ? $this->get_orders_for_contact($current_contact, 25, true) : array();
        $current_rows = $this->get_portal_order_rows($current_orders);
        list($current_upcoming, $current_history) = $this->split_portal_rows_by_time($current_rows);
        ?>
        <div style="display:grid; gap:20px;">
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h2 style="margin-top:0;">Kundenportal</h2>
                        <p>Erste Portal-Version für eingeloggte WordPress-Benutzer. Das Portal liest die vorhandenen Kontakt- und Bestelldaten aus WordPress und zeigt kommende Touren, letzte Buchungen und das persönliche Profil an.</p>
                        <p style="margin-bottom:0;">Shortcode für die spätere Live-Seite: <code>[fxp_customer_portal]</code></p>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3, minmax(140px, 1fr)); gap:10px; min-width:min(100%, 520px);">
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="font-size:12px; color:#64748b;">Portal-Datensatz</div>
                            <div style="font-size:28px; font-weight:700;"><?php echo $current_contact ? 'Ja' : 'Nein'; ?></div>
                        </div>
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="font-size:12px; color:#64748b;">Kommende Touren</div>
                            <div style="font-size:28px; font-weight:700;"><?php echo count($current_upcoming); ?></div>
                        </div>
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="font-size:12px; color:#64748b;">Buchungshistorie</div>
                            <div style="font-size:28px; font-weight:700;"><?php echo count($current_history); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <h3 style="margin-top:0;">Admin-Testdaten</h3>
                <p>Hier kannst du für ein WordPress-Konto Demo-Kontakt- und Demo-Bestelldaten anlegen. So kann ein Admin-Konto wie <code>chderix</code> das Kundenportal vor dem Livegang realistisch durchklicken. Die Demo-Bestellungen werden automatisch als Test markiert.</p>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <?php wp_nonce_field('niers_kombi_seed_portal_demo', 'niers_kombi_seed_portal_demo_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_seed_portal_demo">
                    <div>
                        <label for="niers-kombi-portal-demo-username"><strong>WordPress-Benutzername</strong></label><br>
                        <input type="text" id="niers-kombi-portal-demo-username" name="username" value="chderix" class="regular-text" placeholder="z.B. chderix">
                    </div>
                    <?php submit_button('Demo-Daten erzeugen / aktualisieren', 'secondary', 'submit', false); ?>
                </form>

                <div style="margin-top:16px; padding:14px 16px; background:#f8fafc; border-left:4px solid #2e7d28;">
                    <strong>Empfohlener Testablauf:</strong><br>
                    1. Auf einer Seite den Shortcode <code>[fxp_customer_portal]</code> einfügen.<br>
                    2. Als Admin <code>chderix</code> einloggen.<br>
                    3. Demo-Daten erzeugen und danach die Portal-Seite öffnen.<br>
                    4. Im Portal kommende Touren, Historie und Kontaktdaten durchklicken.
                </div>

                <?php if ($current_email !== ''): ?>
                    <div style="margin-top:18px;">
                        <strong>Aktueller Admin-Match:</strong>
                        <?php echo esc_html($current_email); ?>
                        <?php if ($current_contact): ?>
                            <span style="display:inline-block; margin-left:8px; padding:4px 8px; border-radius:999px; background:#dcfce7; color:#166534; font-size:12px;">Portal-Datensatz gefunden</span>
                        <?php else: ?>
                            <span style="display:inline-block; margin-left:8px; padding:4px 8px; border-radius:999px; background:#fef3c7; color:#92400e; font-size:12px;">Noch keine Portaldaten zu dieser E-Mail</span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap;">
                    <div>
                        <h3 style="margin-top:0;">Portal-Vorschau für den aktuellen Admin</h3>
                        <p style="margin-bottom:0;">Diese Vorschau zeigt, was ein eingeloggter Benutzer mit derselben E-Mail im Portal sehen würde.</p>
                    </div>
                    <div>
                        <a class="button" href="<?php echo esc_url($portal_url); ?>" target="_blank" rel="noopener">Portal-Seite öffnen</a>
                    </div>
                </div>

                <?php if (!$current_contact): ?>
                    <p style="margin-top:16px;"><em>Für die aktuelle Admin-E-Mail liegen noch keine Portal-Daten vor.</em></p>
                <?php else: ?>
                    <div style="margin-top:16px; display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px;">
                        <div style="padding:16px; border:1px solid #e5e7eb; border-radius:12px; background:#f8fafc;">
                            <strong>Kontaktprofil</strong><br>
                            <?php echo esc_html(trim(($current_contact['first_name'] ?: '') . ' ' . ($current_contact['last_name'] ?: '')) ?: 'Ohne Namen'); ?><br>
                            <small><?php echo esc_html($current_contact['email']); ?></small>
                        </div>
                        <div style="padding:16px; border:1px solid #e5e7eb; border-radius:12px; background:#f8fafc;">
                            <strong>Nächste Tour</strong><br>
                            <?php if (!empty($current_upcoming[0])): ?>
                                <?php echo esc_html($current_upcoming[0]['tour_name']); ?><br>
                                <small><?php echo esc_html($this->format_portal_datetime_label($current_upcoming[0]['tour_start'])); ?></small>
                            <?php else: ?>
                                <small>Keine kommende Tour vorhanden.</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function is_portal_row_boat_related($row) {
        $catalog = $this->get_service_catalog();
        $service_id = intval(isset($row['service_id']) ? $row['service_id'] : 0);
        if ($service_id > 0 && !empty($catalog[$service_id]['product_type']) && in_array($catalog[$service_id]['product_type'], array('paddel', 'kombi'), true)) {
            return true;
        }

        $haystack = strtolower(
            trim(
                (isset($row['tour_name']) ? $row['tour_name'] : '') . ' ' .
                (isset($row['source']) ? $row['source'] : '')
            )
        );

        foreach (array('kanu', 'kajak', 'kanadier', 'niers', 'paddel', 'boot', 'schlauchboot') as $term) {
            if (strpos($haystack, $term) !== false) {
                return true;
            }
        }

        return false;
    }

    private function get_transactional_email_audience_group($row) {
        if ($this->is_portal_row_overnight_related($row)) {
            return 'overnight';
        }

        $catalog = $this->get_service_catalog();
        $service_id = intval(isset($row['service_id']) ? $row['service_id'] : 0);
        if ($service_id > 0 && !empty($catalog[$service_id]['product_type']) && $catalog[$service_id]['product_type'] === 'kombi') {
            return 'kombi';
        }

        return 'niers';
    }

    private function is_portal_row_overnight_related($row) {
        $catalog = $this->get_service_catalog();
        $service_id = intval(isset($row['service_id']) ? $row['service_id'] : 0);
        if ($service_id > 0 && !empty($catalog[$service_id]['product_type']) && $catalog[$service_id]['product_type'] === 'stay') {
            return true;
        }

        $haystack = strtolower((string) (isset($row['tour_name']) ? $row['tour_name'] : ''));
        foreach (array('übernachtung', 'uebernachtung', 'tipidorf', 'schloss', 'waldwasser', 'niederrhein erleben', 'landlust') as $term) {
            if (strpos($haystack, $term) !== false) {
                return true;
            }
        }

        return false;
    }

    private function get_transactional_email_rule_definitions() {
        return array(
            array(
                'key' => 'pre_niers',
                'title' => 'Vor der Tour: Niers-Tour',
                'status' => 'bereit zur Aktivierung',
                'trigger_label' => '1 Tag vor Tourbeginn, ideal morgens gegen 09:00 Uhr',
                'content_label' => 'Treffpunkt, Anfahrt, wasserfeste Kleidung, Wechselkleidung, Parken, Kontaktnummer',
                'weather_label' => 'Optionaler Wetterblock kann später ergänzt werden',
                'audience_group' => 'niers',
                'timing' => 'before',
            ),
            array(
                'key' => 'pre_kombi',
                'title' => 'Vor der Tour: Kombi-Tour',
                'status' => 'bereit zur Aktivierung',
                'trigger_label' => '1 Tag vor Tourbeginn',
                'content_label' => 'Tagesablauf, Wechsel zwischen Bausteinen, Treffpunkte, Packtipps, Ansprechpartner',
                'weather_label' => 'Wetterblock optional, falls Paddel-Anteil vorhanden',
                'audience_group' => 'kombi',
                'timing' => 'before',
            ),
            array(
                'key' => 'pre_overnight',
                'title' => 'Vor der Tour: Übernachtung',
                'status' => 'bereit zur Aktivierung',
                'trigger_label' => '1 Tag vor Tourbeginn',
                'content_label' => 'Check-in, Gepäck, Frühstück, Zimmer/Tipi, Tagesablauf, Ansprechpartner',
                'weather_label' => 'Kann bei Bedarf mit Wetter-Hinweis ergänzt werden',
                'audience_group' => 'overnight',
                'timing' => 'before',
            ),
            array(
                'key' => 'post_niers',
                'title' => 'Nach der Tour: Niers-Tour',
                'status' => 'Konzept',
                'trigger_label' => '1 Tag nach Tourbeginn bzw. nach dem Erlebnis',
                'content_label' => 'Danke, Google-Bewertung, Wiederbuchung, Gutschein- oder Empfehlungsimpuls',
                'weather_label' => 'Kein Wetterblock nötig',
                'audience_group' => 'niers',
                'timing' => 'after',
            ),
            array(
                'key' => 'post_kombi',
                'title' => 'Nach der Tour: Kombi-Tour',
                'status' => 'Konzept',
                'trigger_label' => '1 Tag nach Tourbeginn bzw. nach dem Erlebnis',
                'content_label' => 'Danke, Bewertungsbitte, Rückblick, passende Anschlussangebote',
                'weather_label' => 'Kein Wetterblock nötig',
                'audience_group' => 'kombi',
                'timing' => 'after',
            ),
            array(
                'key' => 'post_overnight',
                'title' => 'Nach der Tour: Übernachtung',
                'status' => 'Konzept',
                'trigger_label' => '1 Tag nach der Übernachtungstour',
                'content_label' => 'Danke, Google-Bewertung, Feedback zu Unterkunft und Ablauf, Wiederkehr-Angebote',
                'weather_label' => 'Kein Wetterblock nötig',
                'audience_group' => 'overnight',
                'timing' => 'after',
            ),
        );
    }

    private function get_transactional_email_template_defaults() {
        return array(
            'pre_niers' => array(
                'status' => 'active',
                'subject' => 'Deine Niers-Tour startet morgen: die wichtigsten Hinweise',
                'body' => "Hallo {first_name},\n\nmorgen ist es soweit: Deine Tour \"{tour_name}\" startet am {tour_date}.\n\nTreffpunkt / Station:\n{meeting_point}\n\nBitte denke an:\n- wetterfeste oder wasserfeste Kleidung\n- Wechselkleidung und Handtücher\n- pünktliches Erscheinen am Treffpunkt\n- bei Bedarf Getränke und Sonnenschutz\n\nBei Rückfragen erreichst du uns unter {contact_phone} oder {contact_email}.\n\nWir wünschen dir viel Freude auf dem Wasser!\nDein Freizeitexperten-Team",
            ),
            'pre_kombi' => array(
                'status' => 'active',
                'subject' => 'Deine Kombi-Tour startet morgen: Ablauf und Tipps',
                'body' => "Hallo {first_name},\n\nmorgen beginnt Deine Kombi-Tour \"{tour_name}\" am {tour_date}.\n\nBitte denke an:\n- wettergerechte Kleidung und ggf. Wechselkleidung\n- pünktliches Erscheinen zu den jeweiligen Bausteinen\n- ausreichend Getränke und Sonnenschutz\n- praktische Schuhe und persönliche Dinge für den Tag\n\nAlle weiteren Infos erhaltet ihr vor Ort von unserem Team.\n\nWir freuen uns auf euch!\nDein Freizeitexperten-Team",
            ),
            'pre_overnight' => array(
                'status' => 'active',
                'subject' => 'Deine Übernachtungstour startet morgen: wichtige Infos',
                'body' => "Hallo {first_name},\n\nmorgen startet Deine Übernachtungstour \"{tour_name}\" am {tour_date}.\n\nBitte denke an:\n- passende Kleidung für draußen und den Abend\n- persönliche Sachen und ggf. Hygieneartikel\n- pünktliches Erscheinen zum Check-in oder Startpunkt\n- wettergerechtes Gepäck und bequeme Schuhe\n\nVor Ort erhältst du alle weiteren Informationen zum Ablauf.\n\nBis morgen!\nDein Freizeitexperten-Team",
            ),
            'post_niers' => array(
                'status' => 'draft',
                'subject' => 'Danke für deine Niers-Tour - wir freuen uns über dein Feedback',
                'body' => "Hallo {first_name},\n\nschön, dass du mit uns unterwegs warst. Wir hoffen, deine Tour \"{tour_name}\" war ein tolles Erlebnis.\n\nWenn du einen Moment Zeit hast, freuen wir uns sehr über eine Google-Bewertung und dein Feedback:\n{review_link}\n\nVielen Dank und vielleicht bis bald auf der Niers!\nDein Freizeitexperten-Team",
            ),
            'post_kombi' => array(
                'status' => 'draft',
                'subject' => 'Danke für deine Kombi-Tour - wie hat es dir gefallen?',
                'body' => "Hallo {first_name},\n\nvielen Dank, dass du bei \"{tour_name}\" dabei warst.\n\nWir freuen uns sehr über dein Feedback und eine Google-Bewertung. So hilfst du auch anderen Gästen bei ihrer Entscheidung.\n\nDanke für dein Vertrauen und bis hoffentlich bald!\nDein Freizeitexperten-Team",
            ),
            'post_overnight' => array(
                'status' => 'draft',
                'subject' => 'Danke für deine Übernachtungstour - dein Feedback zählt',
                'body' => "Hallo {first_name},\n\nvielen Dank für deine Buchung und deinen Besuch bei \"{tour_name}\".\n\nWenn dir dein Aufenthalt gefallen hat, freuen wir uns sehr über eine Google-Bewertung und ein kurzes Feedback zu Tour und Unterkunft.\n\nHerzlichen Dank und bis zum nächsten Mal!\nDein Freizeitexperten-Team",
            ),
        );
    }

    private function get_transactional_email_template_status_options() {
        return array(
            'draft' => 'Entwurf',
            'active' => 'Aktiv',
            'paused' => 'Pausiert',
        );
    }

    private function get_supported_transactional_email_auto_rule_keys() {
        return array('pre_niers', 'post_niers');
    }

    private function get_transactional_email_templates() {
        $defaults = $this->get_transactional_email_template_defaults();
        $saved = get_option('niers_kombi_email_templates', array());
        if (!is_array($saved)) {
            $saved = array();
        }

        $templates = array();
        foreach ($this->get_transactional_email_rule_definitions() as $definition) {
            $key = $definition['key'];
            $default_template = isset($defaults[$key]) ? $defaults[$key] : array(
                'status' => 'draft',
                'subject' => $definition['title'],
                'body' => '',
            );
            $saved_template = isset($saved[$key]) && is_array($saved[$key]) ? $saved[$key] : array();

            $status = isset($saved_template['status']) ? sanitize_key($saved_template['status']) : $default_template['status'];
            if (!isset($this->get_transactional_email_template_status_options()[$status])) {
                $status = $default_template['status'];
            }

            $templates[$key] = array(
                'key' => $key,
                'status' => $status,
                'subject' => isset($saved_template['subject']) ? sanitize_text_field((string) $saved_template['subject']) : $default_template['subject'],
                'body' => isset($saved_template['body']) ? wp_kses_post((string) $saved_template['body']) : $default_template['body'],
            );
        }

        return $templates;
    }

    public function extend_transactional_email_cron_schedules($schedules) {
        if (!isset($schedules['every_15_minutes'])) {
            $schedules['every_15_minutes'] = array(
                'interval' => 15 * MINUTE_IN_SECONDS,
                'display' => 'Alle 15 Minuten',
            );
        }
        if (!isset($schedules['every_30_minutes'])) {
            $schedules['every_30_minutes'] = array(
                'interval' => 30 * MINUTE_IN_SECONDS,
                'display' => 'Alle 30 Minuten',
            );
        }
        return $schedules;
    }

    public function maybe_schedule_transactional_email_processing() {
        $desired_schedule = $this->get_transactional_email_runner_interval();
        $scheduled_event = function_exists('wp_get_scheduled_event') ? wp_get_scheduled_event('niers_kombi_process_transactional_emails') : null;

        if ($scheduled_event && isset($scheduled_event->schedule) && $scheduled_event->schedule !== $desired_schedule) {
            wp_clear_scheduled_hook('niers_kombi_process_transactional_emails');
            $scheduled_event = null;
        }

        if (!$scheduled_event && !wp_next_scheduled('niers_kombi_process_transactional_emails')) {
            wp_schedule_event(time() + 300, $desired_schedule, 'niers_kombi_process_transactional_emails');
        }
    }

    private function get_transactional_email_send_timestamp($rule, $base_ts) {
        $timezone = wp_timezone();
        $base_dt = new DateTimeImmutable('@' . intval($base_ts));
        $base_dt = $base_dt->setTimezone($timezone);
        $target_dt = $rule['timing'] === 'after' ? $base_dt->modify('+1 day') : $base_dt->modify('-1 day');
        $time_value = $rule['timing'] === 'after' ? $this->get_transactional_email_post_send_time() : $this->get_transactional_email_pre_send_time();
        list($hours, $minutes) = array_map('intval', explode(':', $time_value));
        $target_dt = $target_dt->setTime($hours, $minutes, 0);
        return $target_dt->getTimestamp();
    }

    private function get_transactional_email_log($limit = 100) {
        global $wpdb;

        $limit = max(1, min(500, intval($limit)));
        return $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM {$this->get_email_log_table_name()} ORDER BY created_at DESC, id DESC LIMIT %d", $limit),
            ARRAY_A
        );
    }

    private function transactional_email_event_key_exists($event_key) {
        global $wpdb;

        if ($event_key === '') {
            return false;
        }

        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->get_email_log_table_name()} WHERE event_key = %s",
            $event_key
        ));

        return intval($count) > 0;
    }

    private function insert_transactional_email_log($payload) {
        global $wpdb;

        $table = $this->get_email_log_table_name();
        $wpdb->insert(
            $table,
            array(
                'event_key' => sanitize_text_field(isset($payload['event_key']) ? $payload['event_key'] : ''),
                'rule_key' => sanitize_key(isset($payload['rule_key']) ? $payload['rule_key'] : ''),
                'order_id' => !empty($payload['order_id']) ? intval($payload['order_id']) : null,
                'contact_id' => !empty($payload['contact_id']) ? intval($payload['contact_id']) : null,
                'request_token' => sanitize_text_field(isset($payload['request_token']) ? $payload['request_token'] : ''),
                'recipient_email' => sanitize_email(isset($payload['recipient_email']) ? $payload['recipient_email'] : ''),
                'original_recipient_email' => sanitize_email(isset($payload['original_recipient_email']) ? $payload['original_recipient_email'] : ''),
                'subject' => sanitize_text_field(isset($payload['subject']) ? $payload['subject'] : ''),
                'status' => sanitize_key(isset($payload['status']) ? $payload['status'] : 'queued'),
                'send_mode' => sanitize_key(isset($payload['send_mode']) ? $payload['send_mode'] : 'live'),
                'payload' => wp_json_encode(isset($payload['payload']) ? $payload['payload'] : array(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'error_message' => isset($payload['error_message']) ? sanitize_textarea_field($payload['error_message']) : '',
                'created_at' => current_time('mysql'),
                'sent_at' => !empty($payload['sent_at']) ? $payload['sent_at'] : null,
            ),
            array('%s','%s','%d','%d','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')
        );

        return intval($wpdb->insert_id);
    }

    private function replace_transactional_email_placeholders($text, $context) {
        $replacements = array(
            '{first_name}' => isset($context['first_name']) ? (string) $context['first_name'] : '',
            '{tour_name}' => isset($context['tour_name']) ? (string) $context['tour_name'] : '',
            '{tour_date}' => isset($context['tour_date']) ? (string) $context['tour_date'] : '',
            '{people_count}' => isset($context['people_count']) ? (string) $context['people_count'] : '',
            '{review_link}' => isset($context['review_link']) ? (string) $context['review_link'] : '',
            '{contact_phone}' => isset($context['contact_phone']) ? (string) $context['contact_phone'] : '',
            '{contact_email}' => isset($context['contact_email']) ? (string) $context['contact_email'] : '',
            '{meeting_point}' => isset($context['meeting_point']) ? (string) $context['meeting_point'] : '',
            '{tour_url}' => isset($context['tour_url']) ? (string) $context['tour_url'] : '',
        );

        return strtr((string) $text, $replacements);
    }

    private function build_transactional_email_context($rule, $row, $order) {
        $catalog = $this->get_service_catalog();
        $first_name = isset($order['first_name']) ? sanitize_text_field((string) $order['first_name']) : '';
        if ($first_name === '' && !empty($order['email'])) {
            $contact = $this->get_contact_by_email($order['email']);
            if (is_array($contact) && !empty($contact['first_name'])) {
                $first_name = sanitize_text_field((string) $contact['first_name']);
            }
        }

        $service_id = intval(isset($row['service_id']) ? $row['service_id'] : 0);
        $catalog_record = ($service_id > 0 && !empty($catalog[$service_id]) && is_array($catalog[$service_id])) ? $catalog[$service_id] : array();
        $meeting_point = !empty($catalog_record['station_summary'])
            ? sanitize_text_field((string) $catalog_record['station_summary'])
            : 'Details zum Treffpunkt erhältst du direkt von unserem Team.';
        $tour_url = !empty($order['source_page']) ? esc_url_raw((string) $order['source_page']) : '';

        return array(
            'first_name' => $first_name !== '' ? $first_name : 'lieber Gast',
            'tour_name' => isset($row['tour_name']) ? (string) $row['tour_name'] : 'deine Tour',
            'tour_date' => $this->format_portal_datetime_label(isset($row['tour_start']) ? $row['tour_start'] : ''),
            'people_count' => isset($row['people_count']) ? intval($row['people_count']) : 0,
            'review_link' => $this->get_transactional_email_review_link(),
            'contact_phone' => $this->get_transactional_email_contact_phone(),
            'contact_email' => $this->get_transactional_email_contact_email(),
            'meeting_point' => $meeting_point,
            'tour_url' => $tour_url,
        );
    }

    private function get_transactional_email_rule_row_candidates($rule) {
        $filters = array(
            'date_from' => '',
            'date_to' => '',
            'source_group' => 'all',
            'include_tests' => false,
        );
        $orders = $this->get_dashboard_filtered_orders($filters);
        $order_map = array();
        foreach ((array) $orders as $order) {
            $order_map[intval($order['id'])] = $order;
        }

        $rows = $this->get_portal_order_rows($orders);
        $candidates = array();
        foreach ($rows as $row) {
            if ($this->get_transactional_email_audience_group($row) !== $rule['audience_group']) {
                continue;
            }
            $order_id = intval(isset($row['order_id']) ? $row['order_id'] : 0);
            if ($order_id < 1 || empty($order_map[$order_id])) {
                continue;
            }
            $candidates[] = array(
                'row' => $row,
                'order' => $order_map[$order_id],
            );
        }

        return $candidates;
    }

    private function should_send_transactional_email_today($rule, $row) {
        if (empty($row['tour_start_ts'])) {
            return false;
        }

        $send_ts = $this->get_transactional_email_send_timestamp($rule, intval($row['tour_start_ts']));
        if (!$send_ts) {
            return false;
        }

        $now_ts = $this->get_wp_current_timestamp();

        return wp_date('Y-m-d', $send_ts) === wp_date('Y-m-d', $now_ts)
            && $now_ts >= $send_ts;
    }

    private function send_transactional_email_for_candidate($rule, $template, $candidate, $send_mode = 'live', $force_send = false) {
        $row = $candidate['row'];
        $order = $candidate['order'];
        $context = $this->build_transactional_email_context($rule, $row, $order);
        $subject = $this->replace_transactional_email_placeholders($template['subject'], $context);
        $body = $this->replace_transactional_email_placeholders($template['body'], $context);

        $original_recipient = sanitize_email(isset($order['email']) ? $order['email'] : '');
        $recipient = $original_recipient;
        if ($send_mode === 'test' || $this->is_transactional_email_test_mode_enabled()) {
            $recipient = $this->get_transactional_email_test_recipient();
            $send_mode = 'test';
        }

        if ($recipient === '') {
            $this->insert_transactional_email_log(array(
                'event_key' => sanitize_text_field($rule['key'] . '|order:' . intval($order['id']) . '|missing-recipient|' . $send_mode),
                'rule_key' => $rule['key'],
                'order_id' => intval($order['id']),
                'contact_id' => intval(isset($order['contact_id']) ? $order['contact_id'] : 0),
                'request_token' => isset($order['request_token']) ? $order['request_token'] : '',
                'recipient_email' => '',
                'original_recipient_email' => $original_recipient,
                'subject' => $subject,
                'status' => 'skipped',
                'send_mode' => $send_mode,
                'payload' => array('reason' => 'missing_recipient', 'row' => $row),
                'error_message' => 'Keine Empfängeradresse vorhanden.',
            ));
            return false;
        }

        $event_key = sanitize_text_field($rule['key'] . '|order:' . intval($order['id']) . '|' . $send_mode . '|' . wp_date('Y-m-d', current_time('timestamp')));
        if ($force_send) {
            $event_key .= '|' . wp_generate_uuid4();
        }
        if (!$force_send && $this->transactional_email_event_key_exists($event_key)) {
            return false;
        }

        $headers = array('Content-Type: text/plain; charset=UTF-8');
        $result = wp_mail($recipient, $subject, $body, $headers);

        $this->insert_transactional_email_log(array(
            'event_key' => $event_key,
            'rule_key' => $rule['key'],
            'order_id' => intval($order['id']),
            'contact_id' => intval(isset($order['contact_id']) ? $order['contact_id'] : 0),
            'request_token' => isset($order['request_token']) ? $order['request_token'] : '',
            'recipient_email' => $recipient,
            'original_recipient_email' => $original_recipient,
            'subject' => $subject,
            'status' => $result ? 'sent' : 'failed',
            'send_mode' => $send_mode,
            'payload' => array(
                'context' => $context,
                'row' => $row,
                'rule_key' => $rule['key'],
            ),
            'error_message' => $result ? '' : 'wp_mail() hat false zurückgegeben.',
            'sent_at' => current_time('mysql'),
        ));

        return $result;
    }

    private function process_transactional_email_rule($rule_key) {
        $definitions = $this->get_transactional_email_rule_definitions();
        $templates = $this->get_transactional_email_templates();
        $rule = null;
        foreach ($definitions as $definition) {
            if ($definition['key'] === $rule_key) {
                $rule = $definition;
                break;
            }
        }
        if (!$rule || !in_array($rule_key, $this->get_supported_transactional_email_auto_rule_keys(), true)) {
            return 0;
        }

        $template = isset($templates[$rule_key]) ? $templates[$rule_key] : null;
        if (!$template || $template['status'] !== 'active') {
            return 0;
        }

        $sent_count = 0;
        foreach ($this->get_transactional_email_rule_row_candidates($rule) as $candidate) {
            if (!$this->should_send_transactional_email_today($rule, $candidate['row'])) {
                continue;
            }
            if ($this->send_transactional_email_for_candidate($rule, $template, $candidate)) {
                $sent_count++;
            }
        }

        return $sent_count;
    }

    public function handle_transactional_email_cron_event() {
        if (!$this->is_transactional_email_module_enabled()) {
            return;
        }

        foreach ($this->get_supported_transactional_email_auto_rule_keys() as $rule_key) {
            $this->process_transactional_email_rule($rule_key);
        }
    }

    private function save_transactional_email_templates($templates) {
        update_option('niers_kombi_email_templates', $templates, false);
    }

    private function get_transactional_email_rule_overview() {
        $filters = array(
            'date_from' => '',
            'date_to' => '',
            'source_group' => 'all',
            'include_tests' => false,
        );
        $orders = $this->get_dashboard_filtered_orders($filters);
        $rows = $this->get_portal_order_rows($orders);
        $now_ts = $this->get_wp_current_timestamp();
        $today = wp_date('Y-m-d', $now_ts);
        $tomorrow = wp_date('Y-m-d', strtotime('+1 day', $now_ts));
        $definitions = $this->get_transactional_email_rule_definitions();
        $templates = $this->get_transactional_email_templates();

        $overview = array();
        foreach ($definitions as $definition) {
            $matched_rows = array_values(array_filter($rows, function($row) use ($definition) {
                return $this->get_transactional_email_audience_group($row) === $definition['audience_group'];
            }));
            $today_count = 0;
            $tomorrow_count = 0;
            $next_send_ts = null;

            foreach ($matched_rows as $row) {
                if (empty($row['tour_start_ts'])) {
                    continue;
                }
                $send_ts = $this->get_transactional_email_send_timestamp($definition, intval($row['tour_start_ts']));
                if (!$send_ts) {
                    continue;
                }

                $send_date = wp_date('Y-m-d', $send_ts);
                if ($send_date === $today) {
                    $today_count++;
                }
                if ($send_date === $tomorrow) {
                    $tomorrow_count++;
                }
                if ($send_ts >= $now_ts && ($next_send_ts === null || $send_ts < $next_send_ts)) {
                    $next_send_ts = $send_ts;
                }
            }

            $definition['matched_count'] = count($matched_rows);
            $definition['today_count'] = $today_count;
            $definition['tomorrow_count'] = $tomorrow_count;
            $definition['next_send_label'] = $next_send_ts ? wp_date('d.m.Y H:i', $next_send_ts) : '—';
            $definition['audience_label'] = $definition['audience_group'] === 'niers'
                ? 'Niers'
                : ($definition['audience_group'] === 'kombi' ? 'Kombi' : 'Übernachtung');
            $definition['template'] = isset($templates[$definition['key']]) ? $templates[$definition['key']] : null;
            $overview[] = $definition;
        }

        return $overview;
    }

    private function render_transactional_email_admin_section() {
        $rules = $this->get_transactional_email_rule_overview();
        $templates = $this->get_transactional_email_templates();
        $template_status_options = $this->get_transactional_email_template_status_options();
        $test_mode_enabled = $this->is_transactional_email_test_mode_enabled();
        $test_recipient = $this->get_transactional_email_test_recipient();
        $runner_interval = $this->get_transactional_email_runner_interval();
        $runner_interval_options = $this->get_transactional_email_runner_interval_options();
        $pre_send_time = $this->get_transactional_email_pre_send_time();
        $post_send_time = $this->get_transactional_email_post_send_time();
        $email_log = $this->get_transactional_email_log(40);
        $editing_template_key = isset($_GET['edit_template']) ? sanitize_key(wp_unslash($_GET['edit_template'])) : (isset($rules[0]['key']) ? $rules[0]['key'] : '');
        $editing_rule = null;
        foreach ($rules as $rule) {
            if ($rule['key'] === $editing_template_key) {
                $editing_rule = $rule;
                break;
            }
        }
        if (!$editing_rule && !empty($rules)) {
            $editing_rule = $rules[0];
            $editing_template_key = $editing_rule['key'];
        }
        $editing_template = ($editing_rule && isset($templates[$editing_template_key])) ? $templates[$editing_template_key] : array(
            'status' => 'draft',
            'subject' => '',
            'body' => '',
        );
        $active_like_count = 0;
        $today_count = 0;
        $tomorrow_count = 0;

        foreach ($rules as $rule) {
            if ($rule['status'] !== 'Konzept') {
                $active_like_count++;
            }
            $today_count += intval($rule['today_count']);
            $tomorrow_count += intval($rule['tomorrow_count']);
        }
        ?>
        <div style="display:grid; gap:20px;">
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; gap:16px; flex-wrap:wrap; align-items:flex-start;">
                    <div>
                        <h2 style="margin-top:0;">Transaktionale E-Mails</h2>
                        <p>Diese Übersicht bereitet die spätere Automationslogik vor. Sie zeigt, welche Regeltypen geplant sind und wie viele echte Buchungen dafür heute oder morgen theoretisch Kandidaten wären.</p>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3, minmax(140px, 1fr)); gap:10px; min-width:min(100%, 520px);">
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="font-size:12px; color:#64748b;">Geplante Regeln</div>
                            <div style="font-size:28px; font-weight:700;"><?php echo count($rules); ?></div>
                        </div>
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="font-size:12px; color:#64748b;">Kandidaten heute</div>
                            <div style="font-size:28px; font-weight:700;"><?php echo intval($today_count); ?></div>
                        </div>
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                            <div style="font-size:12px; color:#64748b;">Kandidaten morgen</div>
                            <div style="font-size:28px; font-weight:700;"><?php echo intval($tomorrow_count); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="padding:14px 16px; background:<?php echo $test_mode_enabled ? '#fef3c7' : '#fee2e2'; ?>; border-left:4px solid <?php echo $test_mode_enabled ? '#d97706' : '#dc2626'; ?>; border-radius:8px;">
                    <?php if ($test_mode_enabled): ?>
                        <strong>Testmodus aktiv:</strong>
                        Solange dieser Modus aktiv ist, sollen transaktionale E-Mails nur an die hinterlegte Testadresse gehen und nicht an echte Kunden.
                        <?php if ($test_recipient !== ''): ?>
                            <br><small>Aktuelle Test-E-Mail: <?php echo esc_html($test_recipient); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <strong>Live-Modus vorbereitet:</strong>
                        Wenn der Versand später aktiviert wird, würden E-Mails an echte Empfänger gehen. Für inhaltliche Tests sollte der Testmodus daher vorab aktiv bleiben.
                    <?php endif; ?>
                    <br><small>Runner-Intervall: <?php echo esc_html(isset($runner_interval_options[$runner_interval]) ? $runner_interval_options[$runner_interval] : $runner_interval); ?> · Vor der Tour: <?php echo esc_html($pre_send_time); ?> Uhr · Nach der Tour: <?php echo esc_html($post_send_time); ?> Uhr</small>
                </div>
            </div>

            <?php if (isset($_GET['email_test_sent']) && $_GET['email_test_sent'] === 'success'): ?>
                <div class="notice notice-success is-dismissible" style="margin:0;">
                    <p>Test-E-Mail wurde an die definierte Testadresse versendet und im Versandlog protokolliert.</p>
                </div>
            <?php elseif (isset($_GET['email_test_sent']) && $_GET['email_test_sent'] === 'error'): ?>
                <div class="notice notice-error is-dismissible" style="margin:0;">
                    <p>Die Test-E-Mail konnte nicht versendet werden. Bitte prüfe Template, Testadresse und Mail-Konfiguration.</p>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['email_live_sent']) && $_GET['email_live_sent'] === 'success'): ?>
                <div class="notice notice-success is-dismissible" style="margin:0;">
                    <p><?php echo intval(isset($_GET['email_live_sent_count']) ? $_GET['email_live_sent_count'] : 0); ?> Live-E-Mail(s) wurden sofort angestoßen und im Versandlog protokolliert.</p>
                </div>
            <?php elseif (isset($_GET['email_live_sent']) && $_GET['email_live_sent'] === 'none_due'): ?>
                <div class="notice notice-warning is-dismissible" style="margin:0;">
                    <p>Für diese Regel sind aktuell keine heute fälligen Live-E-Mails vorhanden.</p>
                </div>
            <?php elseif (isset($_GET['email_live_sent']) && $_GET['email_live_sent'] === 'blocked_test_mode'): ?>
                <div class="notice notice-error is-dismissible" style="margin:0;">
                    <p>Live-Versand ist blockiert, solange der Testmodus aktiv ist.</p>
                </div>
            <?php elseif (isset($_GET['email_live_sent']) && $_GET['email_live_sent'] === 'error'): ?>
                <div class="notice notice-error is-dismissible" style="margin:0;">
                    <p>Der Live-Versand konnte nicht angestoßen werden. Bitte prüfe Regel, Template und Empfängerdaten.</p>
                </div>
            <?php endif; ?>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="margin-bottom:16px; padding:14px 16px; background:#f8fafc; border-left:4px solid #2e7d28;">
                    <strong>Aktueller Stand:</strong> In dieser Stufe sind die ersten echten Versandpfade für <code>Vor der Tour: Niers</code> und <code>Nach der Tour: Niers</code> vorbereitet. Die übrigen Regeln bleiben weiterhin in der fachlichen Vorbereitungsphase. Mit aktivem Testmodus geht jeder Versand ausschließlich an die definierte Testadresse.
                </div>

                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width:220px;">Regel</th>
                            <th style="width:110px;">Tourart</th>
                            <th style="width:160px;">Status</th>
                            <th style="width:220px;">Trigger</th>
                            <th>Inhalte</th>
                            <th style="width:110px;">Heute</th>
                            <th style="width:110px;">Morgen</th>
                            <th style="width:160px;">Nächster Versand</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rules as $rule): ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($rule['title']); ?></strong><br>
                                <small><?php echo esc_html($rule['weather_label']); ?></small>
                            </td>
                            <td><?php echo esc_html($rule['audience_label']); ?></td>
                            <td><?php echo esc_html($rule['status']); ?></td>
                            <td><?php echo esc_html($rule['trigger_label']); ?></td>
                            <td><?php echo esc_html($rule['content_label']); ?></td>
                            <td><?php echo intval($rule['today_count']); ?></td>
                            <td><?php echo intval($rule['tomorrow_count']); ?></td>
                            <td><?php echo esc_html($rule['next_send_label']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="display:grid; grid-template-columns:minmax(320px, 0.95fr) minmax(0, 1.4fr); gap:20px;">
                <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                    <h3 style="margin-top:0;">Templates</h3>
                    <p>Für jede Regel kann ein eigener Betreff und eigener Inhalt gepflegt werden. Diese Inhalte sollen später direkt für den Versand verwendet werden.</p>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Regel</th>
                                <th style="width:110px;">Status</th>
                                <th style="width:90px;">Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rules as $rule): ?>
                            <?php $rule_template = isset($templates[$rule['key']]) ? $templates[$rule['key']] : null; ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($rule['title']); ?></strong><br>
                                    <small><?php echo esc_html($rule['audience_label']); ?></small>
                                </td>
                                <td><?php echo esc_html(isset($template_status_options[$rule_template['status']]) ? $template_status_options[$rule_template['status']] : 'Entwurf'); ?></td>
                                <td><a class="button button-small" href="<?php echo esc_url($this->get_admin_page_url('niers-kombi-emails', array('edit_template' => $rule['key']))); ?>">Bearbeiten</a></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                    <h3 style="margin-top:0;">Template bearbeiten</h3>
                    <?php if ($editing_rule): ?>
                        <p><strong><?php echo esc_html($editing_rule['title']); ?></strong> · <?php echo esc_html($editing_rule['audience_label']); ?> · <?php echo esc_html($editing_rule['trigger_label']); ?></p>

                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                            <?php wp_nonce_field('niers_kombi_save_email_template', 'niers_kombi_save_email_template_nonce'); ?>
                            <input type="hidden" name="action" value="niers_kombi_save_email_template">
                            <input type="hidden" name="rule_key" value="<?php echo esc_attr($editing_rule['key']); ?>">

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><label for="niers-kombi-template-status">Status</label></th>
                                    <td>
                                        <select name="template_status" id="niers-kombi-template-status">
                                            <?php foreach ($template_status_options as $status_value => $status_label): ?>
                                                <option value="<?php echo esc_attr($status_value); ?>" <?php selected($editing_template['status'], $status_value); ?>><?php echo esc_html($status_label); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="niers-kombi-template-subject">Betreff</label></th>
                                    <td>
                                        <input type="text" class="regular-text" style="width:100%;" id="niers-kombi-template-subject" name="template_subject" value="<?php echo esc_attr($editing_template['subject']); ?>">
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><label for="niers-kombi-template-body">Inhalt</label></th>
                                    <td>
                                        <textarea id="niers-kombi-template-body" name="template_body" rows="14" style="width:100%; font-family:monospace;"><?php echo esc_textarea($editing_template['body']); ?></textarea>
                                        <p class="description">Verfügbare Platzhalter: <code>{first_name}</code>, <code>{tour_name}</code>, <code>{tour_date}</code>, <code>{people_count}</code>, <code>{review_link}</code>, <code>{contact_phone}</code>, <code>{contact_email}</code>, <code>{meeting_point}</code>, <code>{tour_url}</code>.</p>
                                    </td>
                                </tr>
                            </table>

                            <?php submit_button('Template speichern'); ?>
                        </form>
                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;">
                            <?php wp_nonce_field('niers_kombi_send_transactional_email_test', 'niers_kombi_send_transactional_email_test_nonce'); ?>
                            <input type="hidden" name="action" value="niers_kombi_send_transactional_email_test">
                            <input type="hidden" name="rule_key" value="<?php echo esc_attr($editing_rule['key']); ?>">
                            <?php submit_button('Test-E-Mail senden', 'secondary', 'submit', false); ?>
                            <?php if ($test_recipient !== ''): ?>
                                <p class="description" style="margin-top:8px;">Testversand geht aktuell an <code><?php echo esc_html($test_recipient); ?></code>.</p>
                            <?php endif; ?>
                        </form>
                        <?php if (in_array($editing_rule['key'], $this->get_supported_transactional_email_auto_rule_keys(), true)): ?>
                            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-top:12px;">
                                <?php wp_nonce_field('niers_kombi_send_transactional_email_live', 'niers_kombi_send_transactional_email_live_nonce'); ?>
                                <input type="hidden" name="action" value="niers_kombi_send_transactional_email_live">
                                <input type="hidden" name="rule_key" value="<?php echo esc_attr($editing_rule['key']); ?>">
                                <button type="submit" class="button button-primary" onclick="return confirm('Diesen Live-Versand jetzt wirklich an echte Empfänger anstoßen?');">Heute fällige Live-E-Mails jetzt senden</button>
                                <p class="description" style="margin-top:8px;">Dieser Button stößt alle heute für diese Regel fälligen Live-E-Mails sofort an. Im Testmodus ist der Live-Versand gesperrt.</p>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p><em>Keine Regel zum Bearbeiten gefunden.</em></p>
                    <?php endif; ?>
                </div>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <h3 style="margin-top:0;">Versandlog</h3>
                <?php if (empty($email_log)): ?>
                    <p><em>Noch keine E-Mails protokolliert.</em></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:170px;">Zeitpunkt</th>
                                <th style="width:120px;">Regel</th>
                                <th style="width:120px;">Modus</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:220px;">Empfänger</th>
                                <th>Betreff</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($email_log as $log_row): ?>
                            <tr>
                                <td><?php echo !empty($log_row['created_at']) ? esc_html(mysql2date('d.m.Y H:i', $log_row['created_at'])) : '—'; ?></td>
                                <td><?php echo esc_html($log_row['rule_key']); ?></td>
                                <td><?php echo esc_html($log_row['send_mode']); ?></td>
                                <td>
                                    <?php echo esc_html($log_row['status']); ?>
                                    <?php if (!empty($log_row['error_message'])): ?>
                                        <br><small><?php echo esc_html($log_row['error_message']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo !empty($log_row['recipient_email']) ? esc_html($log_row['recipient_email']) : '—'; ?>
                                    <?php if (!empty($log_row['original_recipient_email']) && $log_row['original_recipient_email'] !== $log_row['recipient_email']): ?>
                                        <br><small>eigentlich: <?php echo esc_html($log_row['original_recipient_email']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($log_row['subject']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_checkout_funnel_admin_section() {
        $filters = $this->get_dashboard_filters();
        $source_group_options = $this->get_dashboard_source_group_options();
        $period_label = $this->get_dashboard_period_label($filters);
        $metrics = $this->get_checkout_funnel_metrics($filters);
        $toggle_url = $this->get_admin_page_url('niers-kombi-funnel', array(
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'source_group' => $filters['source_group'],
            'include_tests' => $filters['include_tests'] ? 0 : 1,
        ));
        $reset_url = $this->get_admin_page_url('niers-kombi-funnel', array('include_tests' => $filters['include_tests'] ? 1 : 0));
        $first_count = !empty($metrics['steps'][0]['count']) ? max(1, intval($metrics['steps'][0]['count'])) : 1;
        ?>
        <div style="display:grid; gap:20px;">
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
                    <div>
                        <h2 style="margin-top:0;">Checkout-Funnel</h2>
                        <p>Diese Übersicht zeigt, wo Kunden nach dem Kundendaten-Schritt aussteigen: Warenkorb, sichtbarer Checkout, PayPal-Klick und erfolgreiche ERP-Übergabe.</p>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="<?php echo esc_url($toggle_url); ?>" class="button"><?php echo $filters['include_tests'] ? 'Testdaten ausblenden' : 'Testdaten einblenden'; ?></a>
                        <a href="<?php echo esc_url($reset_url); ?>" class="button">Filter zurücksetzen</a>
                    </div>
                </div>
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:grid; grid-template-columns:repeat(4, minmax(180px, 1fr)); gap:12px; margin-top:16px; align-items:end;">
                    <input type="hidden" name="page" value="niers-kombi-funnel">
                    <input type="hidden" name="include_tests" value="<?php echo $filters['include_tests'] ? '1' : '0'; ?>">
                    <div>
                        <label for="niers-kombi-funnel-date-from" style="display:block; font-weight:600; margin-bottom:6px;">Von</label>
                        <input type="date" id="niers-kombi-funnel-date-from" name="date_from" value="<?php echo esc_attr($filters['date_from']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="niers-kombi-funnel-date-to" style="display:block; font-weight:600; margin-bottom:6px;">Bis</label>
                        <input type="date" id="niers-kombi-funnel-date-to" name="date_to" value="<?php echo esc_attr($filters['date_to']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="niers-kombi-funnel-source-group" style="display:block; font-weight:600; margin-bottom:6px;">Quelle</label>
                        <select id="niers-kombi-funnel-source-group" name="source_group" style="width:100%;">
                            <?php foreach ($source_group_options as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($filters['source_group'], $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="button button-primary" style="width:100%;">Funnel aktualisieren</button>
                    </div>
                </form>
                <p style="margin:14px 0 0; color:#64748b;">
                    <strong>Aktiver Zeitraum:</strong> <?php echo esc_html($period_label); ?>
                    · <strong>Quelle:</strong> <?php echo esc_html($source_group_options[$filters['source_group']]); ?>
                    · <strong>Testdaten:</strong> <?php echo $filters['include_tests'] ? 'eingeschlossen' : 'ausgefiltert'; ?>
                </p>
            </div>

            <div style="display:grid; grid-template-columns:minmax(420px, 1.5fr) minmax(280px, .8fr); gap:20px;">
                <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                    <h3 style="margin-top:0;">Grafische Stufen</h3>
                    <?php if (empty($metrics['steps'])): ?>
                        <p><em>Noch keine Funnel-Daten vorhanden.</em></p>
                    <?php else: ?>
                        <div style="display:grid; gap:14px;">
                            <?php foreach ($metrics['steps'] as $index => $step): ?>
                                <?php
                                $width = min(100, max(3, round((intval($step['count']) / $first_count) * 100, 1)));
                                $bar_color = $index === 0 ? '#2563eb' : ($step['rate_from_previous'] >= 75 ? '#16a34a' : ($step['rate_from_previous'] >= 45 ? '#d97706' : '#dc2626'));
                                ?>
                                <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px;">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                                        <div>
                                            <div style="font-size:13px; color:#64748b;">Schritt <?php echo intval($index + 1); ?></div>
                                            <div style="font-size:18px; font-weight:700;"><?php echo esc_html($step['label']); ?></div>
                                            <div style="font-size:12px; color:#64748b; margin-top:3px;"><?php echo esc_html($step['description']); ?></div>
                                        </div>
                                        <div style="text-align:right;">
                                            <div style="font-size:28px; line-height:1; font-weight:800;"><?php echo intval($step['count']); ?></div>
                                            <div style="font-size:12px; color:#64748b;"><?php echo $index === 0 ? 'Start' : esc_html(number_format((float) $step['rate_from_previous'], 1, ',', '.') . ' % vom Vorschritt'); ?></div>
                                        </div>
                                    </div>
                                    <div style="height:12px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-top:12px;">
                                        <div style="height:12px; width:<?php echo esc_attr($width); ?>%; background:<?php echo esc_attr($bar_color); ?>; border-radius:999px;"></div>
                                    </div>
                                    <?php if (intval($step['raw_count']) !== intval($step['count'])): ?>
                                        <div style="font-size:11px; color:#64748b; margin-top:6px;"><?php echo intval($step['raw_count']); ?> Logeinträge, dedupliziert auf <?php echo intval($step['count']); ?> Vorgänge.</div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="display:grid; gap:20px;">
                    <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                        <h3 style="margin-top:0;">Abbruchpunkte</h3>
                        <?php if (empty($metrics['dropoffs'])): ?>
                            <p><em>Noch keine Abbruchdaten vorhanden.</em></p>
                        <?php else: ?>
                            <div style="display:grid; gap:10px;">
                                <?php foreach ($metrics['dropoffs'] as $dropoff): ?>
                                    <div style="padding:12px; background:#fff7ed; border:1px solid #fed7aa; border-radius:10px;">
                                        <div style="font-size:12px; color:#9a3412;"><?php echo esc_html($dropoff['label']); ?></div>
                                        <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-end; margin-top:4px;">
                                            <strong style="font-size:24px;"><?php echo intval($dropoff['count']); ?></strong>
                                            <span style="color:#9a3412;"><?php echo esc_html(number_format((float) $dropoff['rate'], 1, ',', '.') . ' %'); ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                        <h3 style="margin-top:0;">ERP-Fehler</h3>
                        <div style="font-size:34px; font-weight:800; color:<?php echo intval($metrics['failed_count']) > 0 ? '#dc2626' : '#16a34a'; ?>;"><?php echo intval($metrics['failed_count']); ?></div>
                        <p style="margin:6px 0 0; color:#64748b;">Fehlgeschlagene <code>cart_data.php</code>-Übergaben im gewählten Zeitraum.</p>
                    </div>
                </div>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <h3 style="margin-top:0;">Letzte Funnel-Ereignisse</h3>
                <?php if (empty($metrics['recent_events'])): ?>
                    <p><em>Noch keine Ereignisse im gewählten Zeitraum.</em></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:150px;">Zeitpunkt</th>
                                <th style="width:190px;">Ereignis</th>
                                <th style="width:220px;">Kontakt</th>
                                <th style="width:130px;">Services</th>
                                <th>Quelle</th>
                                <th style="width:180px;">Token / PayPal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($metrics['recent_events'] as $event): ?>
                                <?php
                                $payload = $this->decode_contact_event_payload($event);
                                $service_ids = '';
                                if (!empty($payload['service_ids'])) {
                                    $service_ids = implode(', ', $this->get_dashboard_service_ids_from_value($payload['service_ids']));
                                }
                                $email = sanitize_email(isset($event['email']) ? $event['email'] : '');
                                if ($email === '' && !empty($payload['email'])) {
                                    $email = sanitize_email($payload['email']);
                                }
                                ?>
                                <tr>
                                    <td><?php echo !empty($event['created_at']) ? esc_html(mysql2date('d.m.Y H:i', $event['created_at'])) : '—'; ?></td>
                                    <td><strong><?php echo esc_html($this->get_checkout_funnel_event_label(isset($event['event_type']) ? $event['event_type'] : '')); ?></strong></td>
                                    <td>
                                        <?php echo $email !== '' ? esc_html($email) : '—'; ?>
                                        <?php if (!empty($event['contact_id'])): ?><br><small>Kontakt #<?php echo intval($event['contact_id']); ?></small><?php endif; ?>
                                    </td>
                                    <td><?php echo $service_ids !== '' ? esc_html($service_ids) : '—'; ?></td>
                                    <td>
                                        <?php echo !empty($event['source']) ? esc_html($event['source']) : '—'; ?>
                                        <?php if (!empty($event['source_page'])): ?><br><small><?php echo esc_html($event['source_page']); ?></small><?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo !empty($event['request_token']) ? '<small>' . esc_html($event['request_token']) . '</small>' : '—'; ?>
                                        <?php if (!empty($event['paypal_order_id'])): ?><br><small><?php echo esc_html($event['paypal_order_id']); ?></small><?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    private function render_dashboard_admin_section() {
        $filters = $this->get_dashboard_filters();
        $source_group_options = $this->get_dashboard_source_group_options();
        $period_label = $this->get_dashboard_period_label($filters);
        $all_orders = $this->get_dashboard_filtered_orders($filters);
        $orders = $this->filter_dashboard_orders_by_source_group($all_orders, $filters['source_group']);
        $recent_orders_page = isset($_GET['orders_page']) ? max(1, intval(wp_unslash($_GET['orders_page']))) : 1;
        $recent_orders_per_page = 10;
        $recent_orders_total = count($orders);
        $recent_orders_total_pages = max(1, (int) ceil($recent_orders_total / $recent_orders_per_page));
        $recent_orders_page = min($recent_orders_page, $recent_orders_total_pages);
        $summary = $this->get_orders_summary($orders);
        $source_breakdown = $this->get_dashboard_source_breakdown($orders);
        $tour_breakdown = $this->get_dashboard_tour_breakdown($orders);
        $top_tours = array_slice($tour_breakdown, 0, 5);
        $recent_orders = array_slice($orders, ($recent_orders_page - 1) * $recent_orders_per_page, $recent_orders_per_page);
        $upcoming_email_candidates = $this->get_upcoming_transactional_email_candidates(10);
        $conversion = $this->get_dashboard_conversion_metrics($filters, $orders);
        $toggle_url = $this->get_admin_page_url('niers-kombi-dashboard', array(
            'date_from' => $filters['date_from'],
            'date_to' => $filters['date_to'],
            'source_group' => $filters['source_group'],
            'include_tests' => $filters['include_tests'] ? 0 : 1,
            'orders_page' => $recent_orders_page,
        ));
        $reset_url = $this->get_admin_page_url('niers-kombi-dashboard', array('include_tests' => $filters['include_tests'] ? 1 : 0));
        ?>
        <div style="display:grid; gap:20px;">
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
                    <div>
                        <h2 style="margin-top:0;">Umsatz-Dashboard</h2>
                        <p>Diese Live-Auswertung nutzt erfolgreiche Bestell-POSTs an <code>cart_data.php</code> sowie erfasste Kundendaten-Leads. Zeitraum, Quelle und Testbestellungen können direkt in der Ansicht gefiltert werden.</p>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="<?php echo esc_url($toggle_url); ?>" class="button"><?php echo $filters['include_tests'] ? 'Testbestellungen ausblenden' : 'Testbestellungen einblenden'; ?></a>
                        <a href="<?php echo esc_url($reset_url); ?>" class="button">Filter zurücksetzen</a>
                    </div>
                </div>
                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:grid; grid-template-columns:repeat(4, minmax(180px, 1fr)); gap:12px; margin-top:16px; align-items:end;">
                    <input type="hidden" name="page" value="niers-kombi-dashboard">
                    <input type="hidden" name="include_tests" value="<?php echo $filters['include_tests'] ? '1' : '0'; ?>">
                    <div>
                        <label for="niers-kombi-date-from" style="display:block; font-weight:600; margin-bottom:6px;">Von</label>
                        <input type="date" id="niers-kombi-date-from" name="date_from" value="<?php echo esc_attr($filters['date_from']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="niers-kombi-date-to" style="display:block; font-weight:600; margin-bottom:6px;">Bis</label>
                        <input type="date" id="niers-kombi-date-to" name="date_to" value="<?php echo esc_attr($filters['date_to']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="niers-kombi-source-group" style="display:block; font-weight:600; margin-bottom:6px;">Quelle</label>
                        <select id="niers-kombi-source-group" name="source_group" style="width:100%;">
                            <?php foreach ($source_group_options as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($filters['source_group'], $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="button button-primary" style="width:100%;">Auswertung aktualisieren</button>
                    </div>
                </form>
                <p style="margin:14px 0 0; color:#64748b;">
                    <strong>Aktiver Zeitraum:</strong> <?php echo esc_html($period_label); ?>
                    · <strong>Quelle:</strong> <?php echo esc_html($source_group_options[$filters['source_group']]); ?>
                    · <strong>Testbestellungen:</strong> <?php echo $filters['include_tests'] ? 'eingeschlossen' : 'ausgefiltert'; ?>
                </p>
                <div style="display:grid; grid-template-columns:repeat(5, minmax(160px, 1fr)); gap:12px; margin-top:16px;">
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Umsatz im Zeitraum</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo esc_html(number_format($summary['total_revenue'], 2, ',', '.') . ' €'); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Bestellungen</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($summary['total_orders']); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Ø Warenkorb</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo esc_html(number_format($summary['average_order_value'], 2, ',', '.') . ' €'); ?></div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Leads</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo intval($conversion['lead_count']); ?></div>
                        <div style="font-size:12px; color:#64748b; margin-top:4px;">Kundendaten-Step abgesendet</div>
                    </div>
                    <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div style="font-size:12px; color:#64748b;">Conversion Lead → Bestellung</div>
                        <div style="font-size:28px; font-weight:700;"><?php echo esc_html(number_format($conversion['conversion_rate'], 1, ',', '.') . ' %'); ?></div>
                        <div style="font-size:12px; color:#64748b; margin-top:4px;"><?php echo intval($conversion['converted_count']); ?> von <?php echo intval($conversion['lead_count']); ?> Leads wurden zu Bestellern</div>
                    </div>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:minmax(320px, 1fr) minmax(380px, 1.4fr); gap:20px;">
                <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                    <h3 style="margin-top:0;">Niers vs. Kombi</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Quelle</th>
                                <th style="width:110px;">Bestellungen</th>
                                <th style="width:120px;">Umsatz</th>
                                <th style="width:110px;">Ø Warenkorb</th>
                                <th style="width:90px;">Pers.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($source_breakdown as $row): ?>
                            <tr>
                                <td><strong><?php echo esc_html($row['label']); ?></strong></td>
                                <td><?php echo intval($row['orders']); ?></td>
                                <td><strong><?php echo esc_html(number_format((float) $row['revenue'], 2, ',', '.') . ' €'); ?></strong></td>
                                <td><?php echo esc_html(number_format((float) $row['average_order_value'], 2, ',', '.') . ' €'); ?></td>
                                <td><?php echo intval($row['people_count']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <h3 style="margin:24px 0 12px;">Top-Touren</h3>
                    <?php if (empty($top_tours)): ?>
                        <p><em>Im gewählten Zeitraum liegen noch keine Tourdaten vor.</em></p>
                    <?php else: ?>
                        <div style="display:grid; gap:10px;">
                            <?php foreach ($top_tours as $index => $tour): ?>
                                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px;">
                                    <div>
                                        <div style="font-size:12px; color:#64748b;">#<?php echo intval($index + 1); ?> · <?php echo $tour['source_group'] === 'kombi' ? 'Kombi' : 'Niers'; ?></div>
                                        <div style="font-weight:700;"><?php echo esc_html($tour['name']); ?></div>
                                        <div style="font-size:12px; color:#64748b;">
                                            <?php if (!empty($tour['service_id'])): ?>ID <?php echo intval($tour['service_id']); ?> · <?php endif; ?>
                                            <?php echo esc_html($tour['product_type_label']); ?> · <?php echo intval($tour['bookings']); ?> Buchungen
                                        </div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-size:22px; font-weight:700;"><?php echo esc_html(number_format((float) $tour['revenue'], 2, ',', '.') . ' €'); ?></div>
                                        <div style="font-size:12px; color:#64748b;"><?php echo intval($tour['people_count']); ?> Pers.</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                    <h3 style="margin-top:0;">Umsatz nach Tour</h3>
                    <?php if (empty($tour_breakdown)): ?>
                        <p><em>Noch keine Tourumsätze erfasst.</em></p>
                    <?php else: ?>
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>Tour</th>
                                    <th style="width:110px;">Quelle</th>
                                    <th style="width:120px;">Typ</th>
                                    <th style="width:110px;">Buchungen</th>
                                    <th style="width:100px;">Pers.</th>
                                    <th style="width:120px;">Umsatz</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($tour_breakdown, 0, 20) as $tour): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo esc_html($tour['name']); ?></strong>
                                            <?php if (!empty($tour['service_id'])): ?><br><small>ID <?php echo intval($tour['service_id']); ?></small><?php endif; ?>
                                        </td>
                                        <td><?php echo $tour['source_group'] === 'kombi' ? 'Kombi' : 'Niers'; ?></td>
                                        <td><?php echo esc_html($tour['product_type_label']); ?></td>
                                        <td><?php echo intval($tour['bookings']); ?></td>
                                        <td><?php echo intval($tour['people_count']); ?></td>
                                        <td><strong><?php echo esc_html(number_format((float) $tour['revenue'], 2, ',', '.') . ' €'); ?></strong></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
                    <div>
                        <h3 style="margin-top:0;">N&auml;chste transaktionale E-Mails</h3>
                        <p style="margin-top:0; color:#64748b;">Diese Vorschau zeigt die n&auml;chsten geplanten Auto-Versandkandidaten aus den aktuell aktiven Regeln innerhalb der n&auml;chsten 7 Tage.</p>
                    </div>
                    <?php if ($this->is_transactional_email_test_mode_enabled()): ?>
                        <div style="display:inline-flex; align-items:center; padding:6px 10px; border-radius:999px; background:#fff7ed; color:#c2410c; font-size:12px; font-weight:700;">Testmodus aktiv</div>
                    <?php endif; ?>
                </div>
                <?php if (empty($upcoming_email_candidates)): ?>
                    <p><em>Aktuell sind keine kommenden Versandkandidaten aus den aktiven Auto-Regeln vorhanden.</em></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:160px;">Versand</th>
                                <th style="width:170px;">Regel</th>
                                <th style="width:210px;">Kunde</th>
                                <th>Tour</th>
                                <th style="width:160px;">Tourtermin</th>
                                <th style="width:90px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcoming_email_candidates as $candidate): ?>
                                <tr>
                                    <td><?php echo esc_html($candidate['send_label']); ?></td>
                                    <td><?php echo esc_html($candidate['rule_title']); ?></td>
                                    <td>
                                        <strong><?php echo esc_html($candidate['customer_name']); ?></strong><br>
                                        <small><?php echo esc_html($candidate['recipient_email'] ?: '—'); ?></small>
                                        <?php if (!empty($candidate['original_email']) && $candidate['original_email'] !== $candidate['recipient_email']): ?>
                                            <br><small>eigentlich: <?php echo esc_html($candidate['original_email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($candidate['tour_name']); ?></strong><br>
                                        <small><?php echo intval($candidate['people_count']); ?> Pers.</small>
                                    </td>
                                    <td><?php echo esc_html($candidate['tour_date_label'] ?: '—'); ?></td>
                                    <td><?php echo esc_html($candidate['status_label']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px;">
                <h3 style="margin-top:0;">Letzte Bestellungen</h3>
                <?php if (!$filters['include_tests']): ?>
                    <p style="margin-top:0; color:#64748b;">Testbestellungen werden in dieser Ansicht standardmäßig herausgefiltert.</p>
                <?php endif; ?>
                <p style="margin-top:0; color:#64748b;">Es werden immer 10 Bestellungen pro Seite angezeigt.</p>
                <?php if (empty($recent_orders)): ?>
                    <p><em>Noch keine Bestellungen erfasst.</em></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:150px;">Zeitpunkt</th>
                                <th style="width:210px;">Kunde</th>
                                <th>Services</th>
                                <th style="width:170px;">Geplant f&uuml;r</th>
                                <th style="width:90px;">Pers.</th>
                                <th style="width:110px;">Positionen</th>
                                <th style="width:120px;">Umsatz</th>
                                <th style="width:100px;">Zahlung</th>
                                <th style="width:130px;">Analyse</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order): ?>
                            <?php $tour_start_labels = $this->get_dashboard_order_tour_start_labels($order); ?>
                            <tr>
                                <td><?php echo !empty($order['created_at']) ? esc_html(mysql2date('d.m.Y H:i', $order['created_at'])) : '—'; ?></td>
                                <td>
                                    <strong><?php echo esc_html(trim(($order['first_name'] ?: '') . ' ' . ($order['last_name'] ?: '')) ?: 'Ohne Namen'); ?></strong><br>
                                    <small><?php echo esc_html($order['email']); ?></small>
                                </td>
                                <td>
                                    <?php echo !empty($order['service_ids']) ? esc_html($order['service_ids']) : '—'; ?>
                                    <?php if (!empty($order['service_names'])): ?><br><small><?php echo esc_html($order['service_names']); ?></small><?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($tour_start_labels)): ?>
                                        —
                                    <?php else: ?>
                                        <?php foreach ($tour_start_labels as $index => $tour_start_label): ?>
                                            <?php if ($index > 0): ?><br><?php endif; ?>
                                            <small><?php echo esc_html($tour_start_label); ?></small>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo intval($order['people_count']); ?></td>
                                <td><?php echo intval($order['item_count']); ?></td>
                                <td><strong><?php echo esc_html(number_format((float) $order['order_total'], 2, ',', '.') . ' €'); ?></strong></td>
                                <td><?php echo !empty($order['payment_provider']) ? esc_html(strtoupper($order['payment_provider'])) : '—'; ?></td>
                                <td>
                                    <?php if (!empty($order['is_test'])): ?>
                                        <span style="display:inline-block; padding:3px 8px; border-radius:999px; background:#fff7ed; color:#c2410c; font-size:11px; font-weight:700; margin-bottom:6px;">TEST</span><br>
                                    <?php endif; ?>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline;">
                                        <?php wp_nonce_field('niers_kombi_toggle_order_test', 'niers_kombi_toggle_order_test_nonce'); ?>
                                        <input type="hidden" name="action" value="niers_kombi_toggle_order_test">
                                        <input type="hidden" name="order_id" value="<?php echo intval($order['id']); ?>">
                                        <input type="hidden" name="next_state" value="<?php echo !empty($order['is_test']) ? '0' : '1'; ?>">
                                        <input type="hidden" name="include_tests" value="<?php echo $filters['include_tests'] ? '1' : '0'; ?>">
                                        <button type="submit" class="button button-small"><?php echo !empty($order['is_test']) ? 'Echt markieren' : 'Als Test markieren'; ?></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if ($recent_orders_total_pages > 1): ?>
                        <div style="display:flex; justify-content:space-between; align-items:center; gap:12px; margin-top:14px; flex-wrap:wrap;">
                            <div style="color:#64748b;">
                                Seite <?php echo intval($recent_orders_page); ?> von <?php echo intval($recent_orders_total_pages); ?>
                            </div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                                <?php if ($recent_orders_page > 1): ?>
                                    <a href="<?php echo esc_url($this->get_admin_page_url('niers-kombi-dashboard', array(
                                        'date_from' => $filters['date_from'],
                                        'date_to' => $filters['date_to'],
                                        'source_group' => $filters['source_group'],
                                        'include_tests' => $filters['include_tests'] ? 1 : 0,
                                        'orders_page' => $recent_orders_page - 1,
                                    ))); ?>" class="button">Zur&uuml;ck</a>
                                <?php endif; ?>
                                <?php
                                $page_window_start = max(1, $recent_orders_page - 2);
                                $page_window_end = min($recent_orders_total_pages, $recent_orders_page + 2);
                                for ($page_number = $page_window_start; $page_number <= $page_window_end; $page_number++):
                                ?>
                                    <?php if ($page_number === $recent_orders_page): ?>
                                        <span class="button button-primary" style="pointer-events:none;"><?php echo intval($page_number); ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo esc_url($this->get_admin_page_url('niers-kombi-dashboard', array(
                                            'date_from' => $filters['date_from'],
                                            'date_to' => $filters['date_to'],
                                            'source_group' => $filters['source_group'],
                                            'include_tests' => $filters['include_tests'] ? 1 : 0,
                                            'orders_page' => $page_number,
                                        ))); ?>" class="button"><?php echo intval($page_number); ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                                <?php if ($recent_orders_page < $recent_orders_total_pages): ?>
                                    <a href="<?php echo esc_url($this->get_admin_page_url('niers-kombi-dashboard', array(
                                        'date_from' => $filters['date_from'],
                                        'date_to' => $filters['date_to'],
                                        'source_group' => $filters['source_group'],
                                        'include_tests' => $filters['include_tests'] ? 1 : 0,
                                        'orders_page' => $recent_orders_page + 1,
                                    ))); ?>" class="button">Weiter</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    public function render_contact_step_shortcode($atts = array()) {
        if (!$this->is_contacts_module_enabled()) {
            return '<div style="padding:20px; border:1px solid #e5e7eb; border-radius:12px; background:#fff8f1;">Das Kundendaten-Modul ist aktuell deaktiviert.</div>';
        }
        $defaults = $this->get_contact_step_default_values();
        $final_redirect = get_option('niers_kombi_cart_redirect', home_url('/warenkorb/'));
        $configured_return = isset($_GET['fxp_return']) ? esc_url_raw(wp_unslash($_GET['fxp_return'])) : '';
        if ($configured_return !== '') {
            $final_redirect = $configured_return;
        }

        ob_start();
        ?>
        <div class="fxp-contact-step" style="max-width:860px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:28px;">
            <h2 style="margin-top:0; color:#2e7d28;">Kontaktdaten</h2>

            <?php if (isset($_GET['fxp_contact_error']) && sanitize_key(wp_unslash($_GET['fxp_contact_error'])) === 'missing_required'): ?>
                <div style="padding:14px; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; color:#991b1b; margin-bottom:16px;">
                    Bitte fülle alle Pflichtfelder aus, bevor du zum Warenkorb weitergehst.
                </div>
            <?php endif; ?>

            <form id="fxp-contact-step-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:grid; gap:16px;">
                <?php wp_nonce_field('niers_kombi_save_contact_step', 'niers_kombi_save_contact_step_nonce'); ?>
                <input type="hidden" name="action" value="niers_kombi_save_contact_step">
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr($final_redirect); ?>">
                <input type="hidden" name="source" id="fxp-source" value="kombi-konfigurator">
                <input type="hidden" name="source_page" id="fxp-source-page" value="">
                <input type="hidden" name="request_token" id="fxp-request-token" value="">
                <input type="hidden" name="last_service_ids" id="fxp-last-service-ids" value="">
                <input type="hidden" name="booking_context_json" id="fxp-booking-context-json" value="">

                <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:16px;">
                    <div>
                        <label for="fxp-email"><strong>E-Mail*</strong></label><br>
                        <input type="email" id="fxp-email" name="email" required value="<?php echo esc_attr($defaults['email']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-company"><strong>Firma</strong></label><br>
                        <input type="text" id="fxp-company" name="company" value="<?php echo esc_attr($defaults['company']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-gender"><strong>Anrede</strong></label><br>
                        <input type="text" id="fxp-gender" name="gender" value="<?php echo esc_attr($defaults['gender']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-title"><strong>Titel</strong></label><br>
                        <input type="text" id="fxp-title" name="title" value="<?php echo esc_attr($defaults['title']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-first-name"><strong>Vorname*</strong></label><br>
                        <input type="text" id="fxp-first-name" name="first_name" required value="<?php echo esc_attr($defaults['first_name']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-last-name"><strong>Nachname*</strong></label><br>
                        <input type="text" id="fxp-last-name" name="last_name" required value="<?php echo esc_attr($defaults['last_name']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-additional-name"><strong>Zusatzname</strong></label><br>
                        <input type="text" id="fxp-additional-name" name="additional_name" value="<?php echo esc_attr($defaults['additional_name']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-phone"><strong>Telefon</strong></label><br>
                        <input type="text" id="fxp-phone" name="phone" value="<?php echo esc_attr($defaults['phone']); ?>" style="width:100%;">
                    </div>
                    <div style="grid-column:1 / -1;">
                        <label for="fxp-street"><strong>Straße*</strong></label><br>
                        <input type="text" id="fxp-street" name="street" required value="<?php echo esc_attr($defaults['street']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-zip"><strong>PLZ*</strong></label><br>
                        <input type="text" id="fxp-zip" name="zip" required value="<?php echo esc_attr($defaults['zip']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-city"><strong>Ort*</strong></label><br>
                        <input type="text" id="fxp-city" name="city" required value="<?php echo esc_attr($defaults['city']); ?>" style="width:100%;">
                    </div>
                    <div>
                        <label for="fxp-mobile"><strong>Mobil*</strong></label><br>
                        <input type="text" id="fxp-mobile" name="mobile" required value="<?php echo esc_attr($defaults['mobile']); ?>" style="width:100%;">
                    </div>
                </div>

                <div style="padding:14px; background:#f8fafc; border-radius:10px; font-size:14px; line-height:1.5;">
                    Wir verarbeiten deine Angaben zur Vorbereitung deiner Buchung. Details findest du in unserer
                    <a href="https://www.freizeitexperten.de/datenschutzerklaerung/" target="_blank" rel="noopener">Datenschutzerklärung</a>.
                </div>

                <label style="display:flex; gap:10px; align-items:flex-start; padding:14px; background:#f8fafc; border-radius:10px;">
                    <input type="checkbox" name="booking_reminder_opt_in" id="fxp-booking-reminder-opt-in" value="1">
                    <span>Ich möchte per E-Mail an meine noch nicht abgeschlossene Buchung erinnert werden.</span>
                </label>

                <label style="display:flex; gap:10px; align-items:flex-start; padding:14px; background:#f8fafc; border-radius:10px;">
                    <input type="checkbox" name="newsletter_opt_in" id="fxp-newsletter-opt-in" value="1">
                    <span>Ich möchte Informationen zu neuen Touren, Angeboten und <strong>Gutscheinen</strong> per E-Mail erhalten.</span>
                </label>

                <div id="fxp-contact-context-box" style="display:none; padding:14px; background:#f8fafc; border-left:4px solid #2e7d28; border-radius:8px;"></div>

                <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                    <button type="submit" style="background:#2e7d28; color:#fff; border:none; border-radius:10px; padding:14px 20px; font-size:16px; cursor:pointer;">Weiter zum Warenkorb</button>
                </div>
            </form>
        </div>

        <script>
        (function() {
          const STORAGE_KEY = 'fxpCheckoutPrefill';
          const CONTEXT_KEY = 'fxpBookingContext';
          const form = document.getElementById('fxp-contact-step-form');
          if (!form) return;

          const readJson = (key, sessionOnly = false) => {
            try {
              const raw = sessionOnly
                ? window.sessionStorage.getItem(key)
                : (window.sessionStorage.getItem(key) || window.localStorage.getItem(key));
              return raw ? JSON.parse(raw) : null;
            } catch (e) {
              return null;
            }
          };

          const hasMeaningfulText = (value) => {
            if (value == null) return false;
            const normalized = String(value).trim();
            if (!normalized) return false;
            const lower = normalized.toLowerCase();
            return lower !== 'undefined' && lower !== 'null' && !lower.includes('undefined');
          };

          const prefill = readJson(STORAGE_KEY);
          if (prefill) {
            Object.entries(prefill).forEach(([key, value]) => {
              const field = form.querySelector(`[name="${key}"]`);
              if (!field) return;
              if (field.type === 'checkbox') {
                field.checked = value === '1' || value === 1 || value === true;
              } else if (!field.value) {
                field.value = value || '';
              }
            });
          }

          const bookingContext = readJson(CONTEXT_KEY, true);
          if (bookingContext) {
            const sourceField = document.getElementById('fxp-source');
            const sourcePageField = document.getElementById('fxp-source-page');
            const requestTokenField = document.getElementById('fxp-request-token');
            const serviceIdsField = document.getElementById('fxp-last-service-ids');
            const contextJsonField = document.getElementById('fxp-booking-context-json');
            if (sourceField && bookingContext.source) sourceField.value = bookingContext.source;
            if (sourcePageField) sourcePageField.value = bookingContext.source_page || bookingContext.page_url || '';
            if (requestTokenField) requestTokenField.value = bookingContext.request_token || '';
            if (serviceIdsField) serviceIdsField.value = bookingContext.service_ids || '';
            if (contextJsonField) contextJsonField.value = JSON.stringify(bookingContext);

            const box = document.getElementById('fxp-contact-context-box');
            if (box) {
              const parts = [];
              if (hasMeaningfulText(bookingContext.tour_name)) parts.push(`<strong>Tour:</strong> ${bookingContext.tour_name}`);
              if (hasMeaningfulText(bookingContext.date_label)) parts.push(`<strong>Termin:</strong> ${bookingContext.date_label}`);
              if (hasMeaningfulText(bookingContext.pax_label)) parts.push(`<strong>Personen:</strong> ${bookingContext.pax_label}`);
              if (parts.length) {
                box.innerHTML = parts.join('<br>');
                box.style.display = 'block';
              }
            }
          }

          form.addEventListener('submit', function() {
            const data = {};
            Array.from(form.elements).forEach((field) => {
              if (!field.name) return;
              if (field.type === 'checkbox') {
                data[field.name] = field.checked ? '1' : '0';
              } else {
                data[field.name] = field.value || '';
              }
            });
            try {
              window.localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
              window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(data));
            } catch (e) {}
          });
        })();
        </script>
        <?php
        return ob_get_clean();
    }

    public function render_customer_portal_shortcode($atts = array()) {
        if (!$this->is_customer_portal_module_enabled()) {
            return '<div style="padding:20px; border:1px solid #e5e7eb; border-radius:12px; background:#fff8f1;">Das Kundenportal-Modul ist aktuell deaktiviert.</div>';
        }

        if (!is_user_logged_in()) {
            ob_start();
            ?>
            <div style="max-width:720px; margin:0 auto; background:#fff; border:1px solid #e5e7eb; border-radius:16px; padding:28px;">
                <h2 style="margin-top:0; color:#2e7d28;">Mein Tourenbereich</h2>
                <p>Bitte melde dich an, um deine kommenden Touren, deine Buchungshistorie und deine hinterlegten Kontaktdaten einzusehen.</p>
                <div style="margin-top:20px;">
                    <?php wp_login_form(array('redirect' => esc_url((string) $_SERVER['REQUEST_URI']))); ?>
                </div>
            </div>
            <?php
            return ob_get_clean();
        }

        list($user, $contact, $portal_email) = $this->get_portal_contact_context();
        $orders = is_array($contact) ? $this->get_orders_for_contact($contact, 100, current_user_can('manage_options')) : array();
        $portal_rows = $this->get_portal_order_rows($orders);
        list($upcoming_rows, $history_rows) = $this->split_portal_rows_by_time($portal_rows);

        $display_name = '';
        if (is_array($contact)) {
            $display_name = trim(($contact['first_name'] ?: '') . ' ' . ($contact['last_name'] ?: ''));
        }
        if ($display_name === '' && $user) {
            $display_name = $user->display_name;
        }
        if ($display_name === '') {
            $display_name = 'Tourgast';
        }

        ob_start();
        ?>
        <div class="fxp-customer-portal" style="max-width:1080px; margin:0 auto; display:grid; gap:24px;">
            <div style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:28px; display:grid; gap:18px;">
                <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap;">
                    <div>
                        <div style="display:inline-block; padding:6px 10px; background:#dcfce7; color:#166534; border-radius:999px; font-size:12px; font-weight:700;">Mein Tourenbereich</div>
                        <h2 style="margin:12px 0 6px; color:#163624;"><?php echo esc_html($display_name); ?></h2>
                        <div style="color:#475569;"><?php echo esc_html($portal_email ?: ($user ? $user->user_email : '')); ?></div>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3, minmax(110px, 1fr)); gap:10px; min-width:min(100%, 360px);">
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px;">
                            <div style="font-size:12px; color:#64748b;">Kommende Touren</div>
                            <div style="font-size:30px; font-weight:700;"><?php echo count($upcoming_rows); ?></div>
                        </div>
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px;">
                            <div style="font-size:12px; color:#64748b;">Buchungen</div>
                            <div style="font-size:30px; font-weight:700;"><?php echo count($portal_rows); ?></div>
                        </div>
                        <div style="padding:14px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px;">
                            <div style="font-size:12px; color:#64748b;">Newsletter</div>
                            <div style="font-size:16px; font-weight:700; margin-top:6px;"><?php echo (is_array($contact) && !empty($contact['newsletter_opt_in']) ? 'Aktiv' : 'Nicht aktiv'); ?></div>
                        </div>
                    </div>
                </div>

                <?php if (current_user_can('manage_options') && !empty($_GET['fxp_portal_email'])): ?>
                    <div style="padding:12px 14px; background:#fef3c7; color:#92400e; border-radius:10px;">
                        Admin-Vorschau aktiv für: <strong><?php echo esc_html($portal_email); ?></strong>
                    </div>
                <?php endif; ?>
            </div>

            <div style="display:grid; grid-template-columns:minmax(0, 1.6fr) minmax(280px, 0.9fr); gap:24px;">
                <div style="display:grid; gap:24px;">
                    <section style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:24px;">
                        <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start; flex-wrap:wrap;">
                            <div>
                                <h3 style="margin:0 0 6px; color:#163624;">Kommende Touren</h3>
                                <p style="margin:0; color:#64748b;">Hier siehst du deine nächsten gebuchten Erlebnisse auf einen Blick.</p>
                            </div>
                        </div>

                        <?php if (empty($upcoming_rows)): ?>
                            <div style="margin-top:18px; padding:18px; border:1px dashed #cbd5e1; border-radius:12px; color:#64748b;">Noch keine kommenden Touren hinterlegt.</div>
                        <?php else: ?>
                            <div style="display:grid; gap:14px; margin-top:18px;">
                                <?php foreach (array_slice($upcoming_rows, 0, 8) as $row): ?>
                                    <article style="padding:18px; border:1px solid #e5e7eb; border-radius:14px; background:linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);">
                                        <div style="display:flex; justify-content:space-between; gap:14px; flex-wrap:wrap; align-items:flex-start;">
                                            <div>
                                                <div style="font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.04em;"><?php echo esc_html($row['source'] ?: 'Buchung'); ?></div>
                                                <h4 style="margin:6px 0 8px; color:#163624;"><?php echo esc_html($row['tour_name']); ?></h4>
                                                <div style="color:#475569;"><?php echo esc_html($this->format_portal_datetime_label($row['tour_start'])); ?></div>
                                            </div>
                                            <div style="text-align:right;">
                                                <div style="font-size:28px; font-weight:700; color:#2e7d28;"><?php echo esc_html(number_format((float) $row['item_total'], 2, ',', '.')); ?> €</div>
                                                <div style="color:#64748b;"><?php echo intval($row['people_count']); ?> Pers.</div>
                                            </div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:24px;">
                        <h3 style="margin-top:0; color:#163624;">Letzte Buchungen</h3>
                        <?php if (empty($history_rows)): ?>
                            <div style="padding:18px; border:1px dashed #cbd5e1; border-radius:12px; color:#64748b;">Noch keine Buchungshistorie vorhanden.</div>
                        <?php else: ?>
                            <div style="overflow:auto;">
                                <table style="width:100%; border-collapse:collapse;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left; padding:10px 0; border-bottom:1px solid #e5e7eb;">Tour</th>
                                            <th style="text-align:left; padding:10px 0; border-bottom:1px solid #e5e7eb;">Termin</th>
                                            <th style="text-align:left; padding:10px 0; border-bottom:1px solid #e5e7eb;">Personen</th>
                                            <th style="text-align:right; padding:10px 0; border-bottom:1px solid #e5e7eb;">Betrag</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach (array_slice($history_rows, 0, 10) as $row): ?>
                                        <tr>
                                            <td style="padding:12px 0; border-bottom:1px solid #f1f5f9;"><?php echo esc_html($row['tour_name']); ?></td>
                                            <td style="padding:12px 0; border-bottom:1px solid #f1f5f9;"><?php echo esc_html($this->format_portal_datetime_label($row['tour_start'])); ?></td>
                                            <td style="padding:12px 0; border-bottom:1px solid #f1f5f9;"><?php echo intval($row['people_count']); ?></td>
                                            <td style="padding:12px 0; border-bottom:1px solid #f1f5f9; text-align:right;"><?php echo esc_html(number_format((float) $row['item_total'], 2, ',', '.')); ?> €</td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>

                <aside style="display:grid; gap:24px;">
                    <section style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:24px;">
                        <h3 style="margin-top:0; color:#163624;">Kontaktdaten</h3>
                        <?php if (!$contact): ?>
                            <p style="margin:0; color:#64748b;">Zu diesem Konto wurden noch keine Kontaktdaten aus einer Buchung gespeichert.</p>
                        <?php else: ?>
                            <div style="display:grid; gap:10px; color:#334155;">
                                <div><strong>E-Mail</strong><br><?php echo esc_html($contact['email']); ?></div>
                                <?php if (!empty($contact['mobile'])): ?><div><strong>Mobil</strong><br><?php echo esc_html($contact['mobile']); ?></div><?php endif; ?>
                                <?php if (!empty($contact['phone'])): ?><div><strong>Telefon</strong><br><?php echo esc_html($contact['phone']); ?></div><?php endif; ?>
                                <?php if (!empty($contact['street']) || !empty($contact['zip']) || !empty($contact['city'])): ?>
                                    <div><strong>Adresse</strong><br><?php echo esc_html(trim(($contact['street'] ?: '') . ', ' . ($contact['zip'] ?: '') . ' ' . ($contact['city'] ?: ''), ', ')); ?></div>
                                <?php endif; ?>
                                <div><strong>Newsletter</strong><br><?php echo !empty($contact['newsletter_opt_in']) ? 'Angemeldet' : 'Nicht angemeldet'; ?></div>
                            </div>
                        <?php endif; ?>
                    </section>

                    <section style="background:#fff; border:1px solid #e5e7eb; border-radius:18px; padding:24px;">
                        <h3 style="margin-top:0; color:#163624;">Service</h3>
                        <div style="display:grid; gap:10px; color:#475569;">
                            <div>Vor deiner Tour senden wir dir bei aktivierten Service-Strecken künftig wichtige Hinweise zu Treffpunkt, Kleidung und Ablauf.</div>
                            <div>Persönliche Daten oder Rückfragen kannst du wie gewohnt weiterhin über unser Team klären.</div>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function handle_save_contact_step_request() {
        if (!$this->is_contacts_module_enabled()) {
            wp_safe_redirect(home_url('/warenkorb/'));
            exit;
        }
        check_admin_referer('niers_kombi_save_contact_step', 'niers_kombi_save_contact_step_nonce');

        $redirect_to = isset($_POST['redirect_to']) ? esc_url_raw(wp_unslash($_POST['redirect_to'])) : home_url('/warenkorb/');
        if ($redirect_to === '') {
            $redirect_to = home_url('/warenkorb/');
        }

        $booking_context = array();
        if (!empty($_POST['booking_context_json'])) {
            $decoded = json_decode(wp_unslash($_POST['booking_context_json']), true);
            if (is_array($decoded)) {
                $booking_context = $decoded;
            }
        }

        $payload = array(
            'email' => isset($_POST['email']) ? wp_unslash($_POST['email']) : '',
            'company' => isset($_POST['company']) ? wp_unslash($_POST['company']) : '',
            'gender' => isset($_POST['gender']) ? wp_unslash($_POST['gender']) : '',
            'title' => isset($_POST['title']) ? wp_unslash($_POST['title']) : '',
            'first_name' => isset($_POST['first_name']) ? wp_unslash($_POST['first_name']) : '',
            'last_name' => isset($_POST['last_name']) ? wp_unslash($_POST['last_name']) : '',
            'additional_name' => isset($_POST['additional_name']) ? wp_unslash($_POST['additional_name']) : '',
            'street' => isset($_POST['street']) ? wp_unslash($_POST['street']) : '',
            'zip' => isset($_POST['zip']) ? wp_unslash($_POST['zip']) : '',
            'city' => isset($_POST['city']) ? wp_unslash($_POST['city']) : '',
            'phone' => isset($_POST['phone']) ? wp_unslash($_POST['phone']) : '',
            'mobile' => isset($_POST['mobile']) ? wp_unslash($_POST['mobile']) : '',
            'newsletter_opt_in' => !empty($_POST['newsletter_opt_in']) ? 1 : 0,
            'booking_reminder_opt_in' => !empty($_POST['booking_reminder_opt_in']) ? 1 : 0,
            'source' => isset($_POST['source']) ? wp_unslash($_POST['source']) : 'kombi-konfigurator',
            'source_page' => isset($_POST['source_page']) ? wp_unslash($_POST['source_page']) : '',
            'request_token' => isset($_POST['request_token']) ? wp_unslash($_POST['request_token']) : '',
            'last_service_ids' => isset($_POST['last_service_ids']) ? wp_unslash($_POST['last_service_ids']) : '',
            'booking_context' => $booking_context,
            'status' => 'lead',
        );

        $required_payload_fields = array('email', 'first_name', 'last_name', 'street', 'zip', 'city', 'mobile');
        foreach ($required_payload_fields as $field_key) {
            if (trim((string) $payload[$field_key]) === '') {
                $fallback_url = wp_get_referer();
                if (!$fallback_url) {
                    $fallback_url = get_option('niers_kombi_contact_step_url', home_url('/buchungsdaten/'));
                }
                wp_safe_redirect(add_query_arg('fxp_contact_error', 'missing_required', $fallback_url));
                exit;
            }
        }

        $contact_id = $this->upsert_contact_record($payload);
        if ($contact_id > 0) {
            $this->insert_contact_event(array(
                'contact_id' => $contact_id,
                'email' => $payload['email'],
                'event_type' => 'contact_step_submitted',
                'source' => $payload['source'],
                'source_page' => $payload['source_page'],
                'request_token' => $payload['request_token'],
                'event_payload' => array(
                    'service_ids' => $payload['last_service_ids'],
                    'newsletter_opt_in' => $payload['newsletter_opt_in'],
                    'booking_reminder_opt_in' => $payload['booking_reminder_opt_in'],
                    'booking_context' => $booking_context,
                ),
            ));
        }

        $this->ensure_contact_session_started();
        if (session_id()) {
            $_SESSION['fxp_contact_step_completed_at'] = time();
            $_SESSION['fxp_contact_step_contact_id'] = $contact_id;
        }
        $this->set_contact_step_cookie($contact_id);

        wp_safe_redirect($redirect_to);
        exit;
    }

    public function handle_track_order_request() {
        if (!$this->is_dashboard_module_enabled()) {
            wp_send_json_error(array('message' => 'Dashboard-Modul deaktiviert.'), 403);
        }

        check_ajax_referer('niers_kombi_track_order', 'nonce');

        $payload_raw = isset($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
        $payload = json_decode($payload_raw, true);
        if (!is_array($payload)) {
            wp_send_json_error(array('message' => 'Ungültige Payload.'), 400);
        }

        $order_id = $this->track_order_submission($payload);
        wp_send_json_success(array('order_id' => $order_id));
    }

    public function handle_track_funnel_event_request() {
        if (!$this->is_dashboard_module_enabled()) {
            wp_send_json_error(array('message' => 'Dashboard-Modul deaktiviert.'), 403);
        }

        check_ajax_referer('niers_kombi_track_funnel_event', 'nonce');

        $payload_raw = isset($_POST['payload']) ? wp_unslash($_POST['payload']) : '';
        $payload = json_decode($payload_raw, true);
        if (!is_array($payload)) {
            wp_send_json_error(array('message' => 'Ungültige Payload.'), 400);
        }

        $allowed_events = array(
            'cart_opened_after_contact',
            'checkout_ready',
            'payment_clicked',
            'paypal_clicked',
            'cart_data_detected',
            'cart_data_success',
            'cart_data_failed',
        );
        $event_type = isset($payload['event_type']) ? sanitize_key($payload['event_type']) : '';
        if (!in_array($event_type, $allowed_events, true)) {
            wp_send_json_error(array('message' => 'Ungültiger Eventtyp.'), 400);
        }

        $email = sanitize_email(isset($payload['email']) ? $payload['email'] : '');
        $contact = $email !== '' ? $this->get_contact_by_email($email) : null;
        $event_payload = array(
            'service_ids' => sanitize_text_field(isset($payload['service_ids']) ? $payload['service_ids'] : ''),
            'item_count' => max(0, intval(isset($payload['item_count']) ? $payload['item_count'] : 0)),
            'people_count' => max(0, intval(isset($payload['people_count']) ? $payload['people_count'] : 0)),
            'order_total' => round((float) (isset($payload['order_total']) ? $payload['order_total'] : 0), 2),
            'payment_method' => sanitize_text_field(isset($payload['payment_method']) ? $payload['payment_method'] : ''),
            'page_path' => sanitize_text_field(isset($payload['page_path']) ? $payload['page_path'] : ''),
            'user_agent_hint' => sanitize_text_field(isset($payload['user_agent_hint']) ? $payload['user_agent_hint'] : ''),
        );

        $this->insert_contact_event(array(
            'contact_id' => !empty($contact['id']) ? intval($contact['id']) : null,
            'email' => $email,
            'event_type' => $event_type,
            'source' => sanitize_text_field(isset($payload['source']) ? $payload['source'] : 'checkout-funnel'),
            'source_page' => isset($payload['source_page']) ? $payload['source_page'] : '',
            'request_token' => isset($payload['request_token']) ? $payload['request_token'] : '',
            'paypal_order_id' => isset($payload['paypal_order_id']) ? $payload['paypal_order_id'] : '',
            'event_payload' => $event_payload,
        ));

        wp_send_json_success(array('tracked' => true));
    }

    public function handle_toggle_order_test_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_dashboard_module_enabled()) wp_die('Dashboard-Modul ist deaktiviert.');
        check_admin_referer('niers_kombi_toggle_order_test', 'niers_kombi_toggle_order_test_nonce');

        global $wpdb;

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $next_state = isset($_POST['next_state']) && (string) $_POST['next_state'] === '1' ? 1 : 0;
        if ($order_id > 0) {
            $wpdb->update(
                $this->get_orders_table_name(),
                array(
                    'is_test' => $next_state,
                    'updated_at' => current_time('mysql'),
                ),
                array('id' => $order_id),
                array('%d', '%s'),
                array('%d')
            );
        }

        $redirect_args = array('page' => 'niers-kombi-dashboard');
        if (!empty($_POST['include_tests']) && (string) $_POST['include_tests'] === '1') {
            $redirect_args['include_tests'] = 1;
        }
        wp_safe_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
        exit;
    }

    public function handle_export_contacts_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_contacts_module_enabled()) wp_die('Kundendaten-Modul ist deaktiviert.');
        check_admin_referer('niers_kombi_export_contacts', 'niers_kombi_export_contacts_nonce');

        $filters = array(
            'consent_filter' => isset($_POST['consent_filter']) ? sanitize_key(wp_unslash($_POST['consent_filter'])) : 'all',
        );
        if (!isset($this->get_contact_consent_filter_options()[$filters['consent_filter']])) {
            $filters['consent_filter'] = 'all';
        }

        $contacts = $this->get_all_contacts(5000, $filters);
        $filename = 'freizeitexperten-kundendaten-' . $filters['consent_filter'] . '-' . gmdate('Ymd-His') . '.csv';

        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        if ($output === false) {
            wp_die('CSV-Export konnte nicht erstellt werden.');
        }

        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($output, array(
            'ID',
            'E-Mail',
            'Vorname',
            'Nachname',
            'Firma',
            'Telefon',
            'Mobil',
            'Strasse',
            'PLZ',
            'Ort',
            'Newsletter Opt-in',
            'Newsletter Opt-in am',
            'Reminder Opt-in',
            'Reminder Opt-in am',
            'Status',
            'Quelle',
            'Letzte Services',
            'Aktualisiert am',
            'Erstellt am',
        ), ';');

        foreach ($contacts as $contact) {
            fputcsv($output, array(
                intval(isset($contact['id']) ? $contact['id'] : 0),
                isset($contact['email']) ? (string) $contact['email'] : '',
                isset($contact['first_name']) ? (string) $contact['first_name'] : '',
                isset($contact['last_name']) ? (string) $contact['last_name'] : '',
                isset($contact['company']) ? (string) $contact['company'] : '',
                isset($contact['phone']) ? (string) $contact['phone'] : '',
                isset($contact['mobile']) ? (string) $contact['mobile'] : '',
                isset($contact['street']) ? (string) $contact['street'] : '',
                isset($contact['zip']) ? (string) $contact['zip'] : '',
                isset($contact['city']) ? (string) $contact['city'] : '',
                !empty($contact['newsletter_opt_in']) ? 'Ja' : 'Nein',
                !empty($contact['newsletter_opt_in_at']) ? (string) $contact['newsletter_opt_in_at'] : '',
                !empty($contact['booking_reminder_opt_in']) ? 'Ja' : 'Nein',
                !empty($contact['booking_reminder_opt_in_at']) ? (string) $contact['booking_reminder_opt_in_at'] : '',
                isset($contact['status']) ? (string) $contact['status'] : '',
                isset($contact['source']) ? (string) $contact['source'] : '',
                isset($contact['last_service_ids']) ? (string) $contact['last_service_ids'] : '',
                isset($contact['updated_at']) ? (string) $contact['updated_at'] : '',
                isset($contact['created_at']) ? (string) $contact['created_at'] : '',
            ), ';');
        }

        fclose($output);
        exit;
    }

    private function create_demo_portal_dataset_for_user($user) {
        global $wpdb;

        if (!$user || empty($user->ID) || empty($user->user_email)) {
            return false;
        }

        $email = sanitize_email((string) $user->user_email);
        if ($email === '') {
            return false;
        }

        $first_name = get_user_meta($user->ID, 'first_name', true);
        $last_name = get_user_meta($user->ID, 'last_name', true);
        if ($first_name === '' && $last_name === '' && !empty($user->display_name)) {
            $name_parts = preg_split('/\s+/', trim((string) $user->display_name));
            $first_name = isset($name_parts[0]) ? $name_parts[0] : '';
            $last_name = count($name_parts) > 1 ? implode(' ', array_slice($name_parts, 1)) : '';
        }

        $contact_id = $this->upsert_contact_record(array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'mobile' => get_user_meta($user->ID, 'billing_phone', true),
            'source' => 'demo-customer-portal',
            'source_page' => get_option('niers_kombi_customer_portal_url', home_url('/kundenportal/')),
            'request_token' => 'demo-portal-user-' . intval($user->ID),
            'last_service_ids' => '45,202,514',
            'booking_context' => array('seeded_for_user' => $user->user_login),
            'newsletter_opt_in' => 1,
            'status' => 'booked',
        ));

        if ($contact_id < 1) {
            return false;
        }

        $orders_table = $this->get_orders_table_name();
        $wpdb->query($wpdb->prepare("DELETE FROM {$orders_table} WHERE email = %s AND source = %s", $email, 'demo-customer-portal'));

        $base_ts = current_time('timestamp');
        $demo_orders = array(
            array(
                'service_id' => 45,
                'name' => 'Kanutour auf der Niers von Weeze - Kessel',
                'date_time' => wp_date('Y-m-d 12:00:00', strtotime('+5 days', $base_ts)),
                'people_count' => 2,
                'price' => 42.00,
                'source' => 'demo-customer-portal',
                'source_page' => home_url('/paddeln-auf-der-niers/'),
            ),
            array(
                'service_id' => 202,
                'name' => 'Amadahy (Waldwasser) im Tipidorf (2026)',
                'date_time' => wp_date('Y-m-d 11:00:00', strtotime('+18 days', $base_ts)),
                'people_count' => 2,
                'price' => 290.00,
                'source' => 'demo-customer-portal',
                'source_page' => home_url('/kombi-touren/uebernachtungstouren/'),
            ),
            array(
                'service_id' => 514,
                'name' => 'Niederrhein erleben',
                'date_time' => wp_date('Y-m-d 16:00:00', strtotime('-14 days', $base_ts)),
                'people_count' => 4,
                'price' => 398.00,
                'source' => 'demo-customer-portal',
                'source_page' => home_url('/kombi-touren/uebernachtungstouren/niederrhein-erleben/'),
            ),
        );

        foreach ($demo_orders as $index => $demo_order) {
            $request_token = 'demo-portal-' . intval($user->ID) . '-' . ($index + 1);
            $order_id = $this->track_order_submission(array(
                'request_token' => $request_token,
                'paypal_order_id' => 'DEMO-' . intval($user->ID) . '-' . ($index + 1),
                'email' => $email,
                'first_name' => $first_name,
                'last_name' => $last_name,
                'source' => $demo_order['source'],
                'source_page' => $demo_order['source_page'],
                'payment_provider' => 'demo',
                'currency' => 'EUR',
                'order_total' => $demo_order['price'],
                'item_count' => 1,
                'people_count' => $demo_order['people_count'],
                'service_ids' => (string) $demo_order['service_id'],
                'service_names' => $demo_order['name'],
                'order_payload' => array(
                    array(
                        'service_id' => $demo_order['service_id'],
                        'name' => $demo_order['name'],
                        'price_brutto' => $demo_order['price'],
                        'ppl_adult' => $demo_order['people_count'],
                        'ppl_child' => 0,
                        'ppl_baby' => 0,
                        'quotas_begin_time' => $demo_order['date_time'],
                    ),
                ),
            ));

            if ($order_id > 0) {
                $wpdb->update(
                    $orders_table,
                    array(
                        'is_test' => 1,
                        'payment_provider' => 'demo',
                        'updated_at' => current_time('mysql'),
                    ),
                    array('id' => $order_id),
                    array('%d', '%s', '%s'),
                    array('%d')
                );
            }
        }

        $this->insert_contact_event(array(
            'contact_id' => $contact_id,
            'email' => $email,
            'event_type' => 'portal_demo_seeded',
            'source' => 'demo-customer-portal',
            'source_page' => get_option('niers_kombi_customer_portal_url', home_url('/kundenportal/')),
            'request_token' => 'demo-portal-user-' . intval($user->ID),
            'event_payload' => array(
                'username' => $user->user_login,
                'demo_orders' => count($demo_orders),
            ),
        ));

        return true;
    }

    public function handle_seed_portal_demo_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_customer_portal_module_enabled()) wp_die('Kundenportal-Modul ist deaktiviert.');
        check_admin_referer('niers_kombi_seed_portal_demo', 'niers_kombi_seed_portal_demo_nonce');

        $username = isset($_POST['username']) ? sanitize_user(wp_unslash($_POST['username'])) : '';
        $user = $username !== '' ? get_user_by('login', $username) : null;
        $result = ($user && $this->create_demo_portal_dataset_for_user($user)) ? 'success' : 'error';

        wp_safe_redirect($this->get_admin_page_url('niers-kombi-portal', array(
            'portal_demo_seeded' => $result,
            'username' => $username,
        )));
        exit;
    }

    public function handle_save_email_template_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_transactional_email_module_enabled()) wp_die('Modul für transaktionale E-Mails ist deaktiviert.');
        check_admin_referer('niers_kombi_save_email_template', 'niers_kombi_save_email_template_nonce');

        $rule_key = isset($_POST['rule_key']) ? sanitize_key(wp_unslash($_POST['rule_key'])) : '';
        $known_keys = array();
        foreach ($this->get_transactional_email_rule_definitions() as $definition) {
            $known_keys[$definition['key']] = true;
        }
        if ($rule_key === '' || !isset($known_keys[$rule_key])) {
            wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array('template_saved' => 'error')));
            exit;
        }

        $status = isset($_POST['template_status']) ? sanitize_key(wp_unslash($_POST['template_status'])) : 'draft';
        if (!isset($this->get_transactional_email_template_status_options()[$status])) {
            $status = 'draft';
        }

        $templates = $this->get_transactional_email_templates();
        $templates[$rule_key] = array(
            'key' => $rule_key,
            'status' => $status,
            'subject' => sanitize_text_field(isset($_POST['template_subject']) ? wp_unslash($_POST['template_subject']) : ''),
            'body' => wp_kses_post(isset($_POST['template_body']) ? wp_unslash($_POST['template_body']) : ''),
        );

        $this->save_transactional_email_templates($templates);

        wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
            'template_saved' => 'success',
            'edit_template' => $rule_key,
        )));
        exit;
    }

    public function handle_send_transactional_email_test_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_transactional_email_module_enabled()) wp_die('Modul für transaktionale E-Mails ist deaktiviert.');
        check_admin_referer('niers_kombi_send_transactional_email_test', 'niers_kombi_send_transactional_email_test_nonce');

        $rule_key = isset($_POST['rule_key']) ? sanitize_key(wp_unslash($_POST['rule_key'])) : '';
        $definitions = $this->get_transactional_email_rule_definitions();
        $templates = $this->get_transactional_email_templates();
        $rule = null;
        foreach ($definitions as $definition) {
            if ($definition['key'] === $rule_key) {
                $rule = $definition;
                break;
            }
        }

        if (!$rule || empty($templates[$rule_key])) {
            wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
                'template_saved' => 'error',
                'edit_template' => $rule_key,
            )));
            exit;
        }

        $candidates = $this->get_transactional_email_rule_row_candidates($rule);
        if (!empty($candidates)) {
            $candidate = $candidates[0];
        } else {
            $candidate = array(
                'row' => array(
                    'tour_name' => $rule['title'],
                    'tour_start' => wp_date('Y-m-d H:i:s', strtotime('+1 day 12:00', current_time('timestamp'))),
                    'tour_start_ts' => strtotime('+1 day 12:00', current_time('timestamp')),
                    'people_count' => 2,
                    'order_id' => 0,
                ),
                'order' => array(
                    'id' => 0,
                    'contact_id' => 0,
                    'request_token' => 'manual-test-' . $rule_key,
                    'email' => $this->get_transactional_email_test_recipient(),
                    'first_name' => 'Test',
                    'last_name' => 'Empfänger',
                ),
            );
        }

        $result = $this->send_transactional_email_for_candidate($rule, $templates[$rule_key], $candidate, 'test', true);

        wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
            'edit_template' => $rule_key,
            'email_test_sent' => $result ? 'success' : 'error',
        )));
        exit;
    }

    public function handle_send_transactional_email_live_request() {
        if (!current_user_can('manage_options')) wp_die('Keine Berechtigung.');
        if (!$this->is_transactional_email_module_enabled()) wp_die('Modul für transaktionale E-Mails ist deaktiviert.');
        check_admin_referer('niers_kombi_send_transactional_email_live', 'niers_kombi_send_transactional_email_live_nonce');

        if ($this->is_transactional_email_test_mode_enabled()) {
            wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
                'edit_template' => isset($_POST['rule_key']) ? sanitize_key(wp_unslash($_POST['rule_key'])) : '',
                'email_live_sent' => 'blocked_test_mode',
            )));
            exit;
        }

        $rule_key = isset($_POST['rule_key']) ? sanitize_key(wp_unslash($_POST['rule_key'])) : '';
        $definitions = $this->get_transactional_email_rule_definitions();
        $templates = $this->get_transactional_email_templates();
        $rule = null;
        foreach ($definitions as $definition) {
            if ($definition['key'] === $rule_key) {
                $rule = $definition;
                break;
            }
        }

        if (!$rule || empty($templates[$rule_key]) || !in_array($rule_key, $this->get_supported_transactional_email_auto_rule_keys(), true)) {
            wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
                'edit_template' => $rule_key,
                'email_live_sent' => 'error',
            )));
            exit;
        }

        if ($templates[$rule_key]['status'] !== 'active') {
            wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
                'edit_template' => $rule_key,
                'email_live_sent' => 'error',
            )));
            exit;
        }

        $due_candidates = $this->get_due_transactional_email_candidates($rule);
        if (empty($due_candidates)) {
            wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
                'edit_template' => $rule_key,
                'email_live_sent' => 'none_due',
            )));
            exit;
        }

        $sent_count = 0;
        foreach ($due_candidates as $candidate) {
            if ($this->send_transactional_email_for_candidate($rule, $templates[$rule_key], $candidate, 'live', false)) {
                $sent_count++;
            }
        }

        wp_safe_redirect($this->get_admin_page_url('niers-kombi-emails', array(
            'edit_template' => $rule_key,
            'email_live_sent' => $sent_count > 0 ? 'success' : 'error',
            'email_live_sent_count' => $sent_count,
        )));
        exit;
    }

    public function render_checkout_prefill_script() {
        if (is_admin()) {
            return;
        }
        ?>
        <script>
        (function() {
          const PREFILL_KEY = 'fxpCheckoutPrefill';
          function getPrefill() {
            try {
              const raw = window.localStorage.getItem(PREFILL_KEY) || window.sessionStorage.getItem(PREFILL_KEY);
              return raw ? JSON.parse(raw) : null;
            } catch (e) {
              return null;
            }
          }

          function fillField(selectorList, value) {
            if (!value) return;
            for (const selector of selectorList) {
              const field = document.querySelector(selector);
              if (!field) continue;
              if (!field.value) {
                field.value = value;
                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
              }
              return;
            }
          }

          function fillCheckout() {
            const data = getPrefill();
            if (!data) return;

            fillField(['#email', '[name="email"]'], data.email);
            fillField(['#company', '[name="company"]'], data.company);
            fillField(['#gender', '[name="gender"]'], data.gender);
            fillField(['#title', '[name="title"]'], data.title);
            fillField(['#first_name', '[name="first_name"]'], data.first_name);
            fillField(['#last_name', '[name="last_name"]'], data.last_name);
            fillField(['#additional_name', '[name="additional_name"]'], data.additional_name);
            fillField(['#street', '[name="street"]'], data.street);
            fillField(['#zip', '[name="zip"]'], data.zip);
            fillField(['#city', '[name="city"]'], data.city);
            fillField(['#phone', '[name="phone"]'], data.phone);
            fillField(['#mobile', '[name="mobile"]'], data.mobile);
          }

          if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fillCheckout);
          } else {
            fillCheckout();
          }
          window.addEventListener('load', fillCheckout);
          setTimeout(fillCheckout, 600);
        })();
        </script>
        <?php if ($this->is_dashboard_module_enabled()): ?>
        <script>
        (function() {
          const endpointFragment = '/shop/cart_data.php';
          const ajaxUrl = <?php echo json_encode(admin_url('admin-ajax.php')); ?>;
          const ajaxNonce = <?php echo json_encode(wp_create_nonce('niers_kombi_track_order')); ?>;
          const funnelNonce = <?php echo json_encode(wp_create_nonce('niers_kombi_track_funnel_event')); ?>;
          const cartPath = <?php echo json_encode(wp_parse_url(get_option('niers_kombi_cart_redirect', home_url('/warenkorb/')), PHP_URL_PATH) ?: '/warenkorb/'); ?>;
          const seenKeys = new Set();
          const seenFunnelKeys = new Set();
          const prefillStorageKey = 'fxpCheckoutPrefill';
          const contactFieldKeys = [
            'email',
            'company',
            'gender',
            'title',
            'first_name',
            'last_name',
            'additional_name',
            'street',
            'zip',
            'city',
            'phone',
            'mobile'
          ];

          function getCheckoutPrefill() {
            try {
              const raw = window.localStorage.getItem(prefillStorageKey) || window.sessionStorage.getItem(prefillStorageKey);
              return raw ? JSON.parse(raw) : null;
            } catch (e) {
              return null;
            }
          }

          function getBookingContext() {
            try {
              const raw = window.sessionStorage.getItem('fxpBookingContext') || window.localStorage.getItem('fxpBookingContext');
              return raw ? JSON.parse(raw) : null;
            } catch (e) {
              return null;
            }
          }

          function isCartPage() {
            const expected = String(cartPath || '/warenkorb/').replace(/\/+$/, '');
            const current = window.location.pathname.replace(/\/+$/, '');
            return current === expected;
          }

          function buildFunnelBase(extra = {}) {
            const prefill = getCheckoutPrefill() || {};
            const context = getBookingContext() || {};
            return Object.assign({
              email: prefill.email || '',
              source: context.source || prefill.source || 'checkout-funnel',
              source_page: window.location.href,
              request_token: context.request_token || prefill.request_token || '',
              service_ids: context.service_ids || prefill.last_service_ids || '',
              page_path: window.location.pathname,
              user_agent_hint: (navigator.userAgent || '').slice(0, 160)
            }, extra);
          }

          function trackFunnelEvent(eventType, extra = {}) {
            const payload = buildFunnelBase(Object.assign({ event_type: eventType }, extra));
            const dedupeKey = [
              eventType,
              payload.email || 'no-email',
              payload.request_token || '',
              payload.service_ids || '',
              payload.paypal_order_id || ''
            ].join('|');
            if (seenFunnelKeys.has(dedupeKey)) return;
            seenFunnelKeys.add(dedupeKey);

            try {
              const storageKey = 'fxp_funnel_' + dedupeKey;
              if (window.sessionStorage.getItem(storageKey)) return;
              window.sessionStorage.setItem(storageKey, String(Date.now()));
            } catch (e) {}

            const body = new URLSearchParams();
            body.append('action', 'niers_kombi_track_funnel_event');
            body.append('nonce', funnelNonce);
            body.append('payload', JSON.stringify(payload));

            fetch(ajaxUrl, {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              credentials: 'same-origin',
              keepalive: true,
              body: body.toString()
            }).catch(() => {});
          }

          function getPaymentMethodFromElement(element) {
            if (!element || !element.getAttribute) return '';
            const fundingSource = String(element.getAttribute('data-funding-source') || '').toLowerCase();
            if (fundingSource) return fundingSource;
            const haystack = [
              element.id || '',
              element.className || '',
              element.getAttribute('aria-label') || '',
              element.getAttribute('title') || '',
              element.getAttribute('name') || '',
              element.getAttribute('src') || ''
            ].join(' ').toLowerCase();
            if (haystack.includes('card') || haystack.includes('debit') || haystack.includes('kredit')) return 'card';
            if (haystack.includes('paypal')) return 'paypal';
            return '';
          }

          function looksLikePaymentElement(element) {
            if (!element) return false;
            const text = (element.innerText || element.textContent || '').toLowerCase();
            const attrs = [
              element.id || '',
              element.className || '',
              element.getAttribute && element.getAttribute('aria-label') || '',
              element.getAttribute && element.getAttribute('title') || '',
              element.getAttribute && element.getAttribute('name') || '',
              element.getAttribute && element.getAttribute('src') || '',
              element.getAttribute && element.getAttribute('data-funding-source') || ''
            ].join(' ').toLowerCase();
            return text.includes('paypal') || attrs.includes('paypal')
              || text.includes('debit') || attrs.includes('debit')
              || text.includes('kredit') || attrs.includes('kredit')
              || text.includes('credit') || attrs.includes('credit')
              || text.includes('karte') || attrs.includes('card')
              || attrs.includes('data-funding-source');
          }

          function detectCheckoutReady() {
            if (!isCartPage()) return;
            const paypalCandidate = Array.from(document.querySelectorAll('button, a, iframe, [id], [class], [data-funding-source]'))
              .find(looksLikePaymentElement);
            if (paypalCandidate) {
              trackFunnelEvent('checkout_ready');
            }
          }

          function mergePrefillIntoParams(params) {
            if (!params) return null;
            const merged = new URLSearchParams(params.toString());
            const prefill = getCheckoutPrefill();
            if (!prefill) return merged;

            contactFieldKeys.forEach((key) => {
              if (!merged.get(key) && prefill[key]) {
                merged.set(key, prefill[key]);
              }
            });
            return merged;
          }

          function augmentBodyWithPrefill(body, mergedParams) {
            if (!mergedParams) return body;
            if (typeof body === 'string') {
              return mergedParams.toString();
            }
            if (body instanceof URLSearchParams) {
              return mergedParams;
            }
            if (body instanceof FormData) {
              const prefill = getCheckoutPrefill();
              if (!prefill) return body;
              contactFieldKeys.forEach((key) => {
                if (!body.get(key) && prefill[key]) {
                  body.set(key, prefill[key]);
                }
              });
            }
            return body;
          }

          function appendPrefillToForm(form) {
            const prefill = getCheckoutPrefill();
            if (!prefill) return;

            contactFieldKeys.forEach((key) => {
              if (!prefill[key]) return;
              const field = form.querySelector(`[name="${key}"]`);
              if (field) {
                if (!field.value) field.value = prefill[key];
                return;
              }

              const hidden = document.createElement('input');
              hidden.type = 'hidden';
              hidden.name = key;
              hidden.value = prefill[key];
              form.appendChild(hidden);
            });
          }

          function urlMatchesCartDataEndpoint(url) {
            if (!url) return false;
            try {
              const parsed = new URL(url, window.location.href);
              return parsed.pathname.endsWith('/shop/cart_data.php') || parsed.pathname.endsWith('/cart_data.php');
            } catch (e) {
              return String(url).indexOf(endpointFragment) !== -1 || String(url).indexOf('cart_data.php') !== -1;
            }
          }

          function parseRequestBody(body) {
            if (!body) return null;
            if (typeof body === 'string') return new URLSearchParams(body);
            if (body instanceof URLSearchParams) return body;
            if (body instanceof FormData) {
              const params = new URLSearchParams();
              body.forEach((value, key) => params.append(key, value));
              return params;
            }
            return null;
          }

          function buildOrderPayload(params) {
            params = mergePrefillIntoParams(params);
            if (!params) return null;

            const items = [];
            const serviceIds = [];
            const serviceNames = [];
            let orderTotal = 0;
            let peopleCount = 0;

            params.forEach((value, key) => {
              const match = key.match(/^cart_data\[(.+)\]$/);
              if (!match || match[1] === 'xxx') return;
              try {
                const parsed = JSON.parse(value);
                items.push(parsed);
                if (parsed.service_id != null) serviceIds.push(String(parsed.service_id));
                if (parsed.name) serviceNames.push(String(parsed.name));
                orderTotal += parseFloat(parsed.price_brutto || 0) || 0;
                peopleCount += parseInt(parsed.ppl_adult || 0, 10) || 0;
                peopleCount += parseInt(parsed.ppl_child || 0, 10) || 0;
                peopleCount += parseInt(parsed.ppl_baby || 0, 10) || 0;
              } catch (e) {}
            });

            if (!items.length) return null;

            return {
              request_token: params.get('request') || '',
              paypal_order_id: params.get('paypal_order_id') || '',
              email: params.get('email') || '',
              company: params.get('company') || '',
              gender: params.get('gender') || '',
              title: params.get('title') || '',
              first_name: params.get('first_name') || '',
              last_name: params.get('last_name') || '',
              additional_name: params.get('additional_name') || '',
              street: params.get('street') || '',
              zip: params.get('zip') || '',
              city: params.get('city') || '',
              phone: params.get('phone') || '',
              mobile: params.get('mobile') || '',
              source: 'checkout-cart-data',
              source_page: window.location.href,
              payment_provider: params.get('payment_provider') || params.get('payment_method') || (params.get('paypal_order_id') ? 'paypal' : 'unknown'),
              currency: 'EUR',
              order_total: Number(orderTotal.toFixed(2)),
              item_count: items.length,
              people_count: peopleCount,
              service_ids: serviceIds.join(','),
              service_names: serviceNames.join(' | '),
              order_payload: items
            };
          }

          function reportOrder(payload, preferBeacon = false) {
            if (!payload) return;
            const dedupeKey = payload.paypal_order_id || payload.request_token || `${payload.email}|${payload.order_total}|${payload.service_ids}`;
            if (!dedupeKey || seenKeys.has(dedupeKey)) return;
            seenKeys.add(dedupeKey);

            trackFunnelEvent('cart_data_detected', {
              email: payload.email || '',
              request_token: payload.request_token || '',
              paypal_order_id: payload.paypal_order_id || '',
              service_ids: payload.service_ids || '',
              payment_method: payload.payment_provider || '',
              item_count: payload.item_count || 0,
              people_count: payload.people_count || 0,
              order_total: payload.order_total || 0
            });

            const body = new URLSearchParams();
            body.append('action', 'niers_kombi_track_order');
            body.append('nonce', ajaxNonce);
            body.append('payload', JSON.stringify(payload));

            if (preferBeacon && navigator.sendBeacon) {
              try {
                const beaconBody = new Blob([body.toString()], {
                  type: 'application/x-www-form-urlencoded; charset=UTF-8'
                });
                if (navigator.sendBeacon(ajaxUrl, beaconBody)) {
                  return;
                }
              } catch (e) {}
            }

            fetch(ajaxUrl, {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              credentials: 'same-origin',
              keepalive: preferBeacon,
              body: body.toString()
            })
              .then((response) => {
                trackFunnelEvent(response && response.ok ? 'cart_data_success' : 'cart_data_failed', {
                  email: payload.email || '',
                  request_token: payload.request_token || '',
                  paypal_order_id: payload.paypal_order_id || '',
                  service_ids: payload.service_ids || '',
                  payment_method: payload.payment_provider || '',
                  item_count: payload.item_count || 0,
                  people_count: payload.people_count || 0,
                  order_total: payload.order_total || 0
                });
              })
              .catch(() => {
                trackFunnelEvent('cart_data_failed', {
                  email: payload.email || '',
                  request_token: payload.request_token || '',
                  paypal_order_id: payload.paypal_order_id || '',
                  service_ids: payload.service_ids || '',
                  payment_method: payload.payment_provider || '',
                  item_count: payload.item_count || 0,
                  people_count: payload.people_count || 0,
                  order_total: payload.order_total || 0
                });
              });
          }

          function responseLooksSuccessful(text) {
            if (!text) return true;
            try {
              const json = JSON.parse(text);
              return json && json.status !== false;
            } catch (e) {
              return true;
            }
          }

          const originalFetch = window.fetch;
          if (originalFetch) {
            window.fetch = function(input, init) {
              const url = typeof input === 'string' ? input : (input && input.url ? input.url : '');
              const bodySource = init && init.body ? init.body : (input && input.body ? input.body : null);
              const parsedParams = parseRequestBody(bodySource);
              const mergedParams = urlMatchesCartDataEndpoint(url) ? mergePrefillIntoParams(parsedParams) : null;
              const payload = mergedParams ? buildOrderPayload(mergedParams) : null;
              let fetchArgs = arguments;

              if (mergedParams && init && bodySource) {
                const nextInit = Object.assign({}, init, {
                  body: augmentBodyWithPrefill(bodySource, mergedParams)
                });
                fetchArgs = [input, nextInit];
              }

              return originalFetch.apply(this, fetchArgs).then((response) => {
                if (payload && response && response.ok) {
                  response.clone().text().then((text) => {
                    if (responseLooksSuccessful(text)) {
                      reportOrder(payload);
                    }
                  }).catch(() => reportOrder(payload));
                }
                return response;
              });
            };
          }

          const originalOpen = XMLHttpRequest.prototype.open;
          const originalSend = XMLHttpRequest.prototype.send;
          XMLHttpRequest.prototype.open = function(method, url) {
            this.__fxpTrackUrl = url;
            return originalOpen.apply(this, arguments);
          };
          XMLHttpRequest.prototype.send = function(body) {
            const parsedParams = urlMatchesCartDataEndpoint(this.__fxpTrackUrl) ? parseRequestBody(body) : null;
            const mergedParams = parsedParams ? mergePrefillIntoParams(parsedParams) : null;
            const payload = mergedParams ? buildOrderPayload(mergedParams) : null;
            const outboundBody = mergedParams ? augmentBodyWithPrefill(body, mergedParams) : body;
            if (payload) {
              this.addEventListener('load', function() {
                if (this.status >= 200 && this.status < 300) {
                  if (responseLooksSuccessful(this.responseText)) {
                    reportOrder(payload);
                  }
                }
              });
            }
            return originalSend.call(this, outboundBody);
          };

          document.addEventListener('submit', function(event) {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            const action = form.getAttribute('action') || form.action || '';
            if (!urlMatchesCartDataEndpoint(action)) return;
            appendPrefillToForm(form);
            const payload = buildOrderPayload(parseRequestBody(new FormData(form)));
            if (payload) {
              reportOrder(payload, true);
            }
          }, true);

          const originalFormSubmit = HTMLFormElement.prototype.submit;
          if (originalFormSubmit) {
            HTMLFormElement.prototype.submit = function() {
              const action = this.getAttribute('action') || this.action || '';
              if (urlMatchesCartDataEndpoint(action)) {
                appendPrefillToForm(this);
                const payload = buildOrderPayload(parseRequestBody(new FormData(this)));
                if (payload) {
                  reportOrder(payload, true);
                }
              }
              return originalFormSubmit.apply(this, arguments);
            };
          }

          if (isCartPage()) {
            trackFunnelEvent('cart_opened_after_contact');
            detectCheckoutReady();
            window.addEventListener('load', detectCheckoutReady);
            setTimeout(detectCheckoutReady, 800);
            setTimeout(detectCheckoutReady, 2000);

            try {
              const observer = new MutationObserver(detectCheckoutReady);
              observer.observe(document.documentElement, { childList: true, subtree: true });
              setTimeout(() => observer.disconnect(), 20000);
            } catch (e) {}

            document.addEventListener('click', function(event) {
              const target = event.target;
              const candidate = target && target.closest
                ? target.closest('button, a, iframe, [id], [class], [data-funding-source]')
                : null;
              if (looksLikePaymentElement(candidate)) {
                trackFunnelEvent('payment_clicked', {
                  payment_method: getPaymentMethodFromElement(candidate)
                });
              }
            }, true);

            window.addEventListener('blur', function() {
              setTimeout(function() {
                const active = document.activeElement;
                if (active && active.tagName === 'IFRAME' && looksLikePaymentElement(active)) {
                  trackFunnelEvent('payment_clicked', {
                    payment_method: getPaymentMethodFromElement(active) || 'iframe'
                  });
                }
              }, 0);
            });
          }
        })();
        </script>
        <?php endif; ?>
        <?php
    }

    public function display_plugin_admin_page() {
        $current_page = $this->get_current_admin_subpage();
        $saved_ids = get_option('niers_kombi_tour_ids', '');
        $id_array = array_filter(array_map('trim', explode(',', $saved_ids)));
        $catalog = $this->get_service_catalog();
        $catalog_product_types = $this->get_catalog_product_type_options();
        $catalog_statuses = $this->get_catalog_status_options();
        $catalog_tabs = $this->get_catalog_tab_definitions($catalog);
        $active_tab = $current_page === 'niers-kombi-variants' ? 'linked' : (isset($_GET['catalog_tab']) ? sanitize_key($_GET['catalog_tab']) : 'all');
        if (!isset($catalog_tabs[$active_tab])) $active_tab = 'all';
        $dashboard_module_enabled = $this->is_dashboard_module_enabled();
        $contacts_module_enabled = $this->is_contacts_module_enabled();
        $customer_portal_module_enabled = $this->is_customer_portal_module_enabled();
        $transactional_email_module_enabled = $this->is_transactional_email_module_enabled();
        $vouchers_module_enabled = $this->is_vouchers_module_enabled();
        $show_overview = $current_page === 'niers-kombi-settings';
        $show_dashboard = $dashboard_module_enabled && $current_page === 'niers-kombi-dashboard';
        $show_funnel = $dashboard_module_enabled && $current_page === 'niers-kombi-funnel';
        $show_portal = $customer_portal_module_enabled && $current_page === 'niers-kombi-portal';
        $show_catalog = in_array($current_page, array('niers-kombi-catalog', 'niers-kombi-variants'), true);
        $show_contacts = $contacts_module_enabled && $current_page === 'niers-kombi-contacts';
        $show_emails = $transactional_email_module_enabled && $current_page === 'niers-kombi-emails';
        $show_vouchers = $vouchers_module_enabled && $current_page === 'niers-kombi-vouchers';
        $show_scanner = $current_page === 'niers-kombi-scanner';
        $show_settings = $current_page === 'niers-kombi-options';
        $search_term = isset($_GET['catalog_search']) ? sanitize_text_field(wp_unslash($_GET['catalog_search'])) : '';
        $status_filter = isset($_GET['catalog_status']) ? sanitize_key($_GET['catalog_status']) : 'all';
        if ($status_filter !== 'all' && !isset($catalog_statuses[$status_filter])) $status_filter = 'all';
        $quota_filter = isset($_GET['catalog_quota']) ? sanitize_key($_GET['catalog_quota']) : 'all';
        if (!in_array($quota_filter, array('all', 'with', 'without'), true)) $quota_filter = 'all';
        $filtered_catalog = array();

        foreach ($catalog as $service_id => $record) {
            $matches_tab = $active_tab === 'all'
                || ($active_tab === 'no_quotas' && empty($record['has_quotas']))
                || ($active_tab === 'linked' && !empty($record['linked_services']))
                || ($active_tab !== 'no_quotas' && isset($record['product_type']) && $record['product_type'] === $active_tab);

            if (!$matches_tab) continue;
            if ($status_filter !== 'all' && (!isset($record['status']) || $record['status'] !== $status_filter)) continue;
            if ($quota_filter === 'with' && empty($record['has_quotas'])) continue;
            if ($quota_filter === 'without' && !empty($record['has_quotas'])) continue;

            if ($search_term !== '') {
                $haystack = strtolower(
                    implode(' ', array(
                        $service_id,
                        isset($record['name']) ? $record['name'] : '',
                        isset($record['station_summary']) ? $record['station_summary'] : '',
                    ))
                );
                if (strpos($haystack, strtolower($search_term)) === false) continue;
            }

            $filtered_catalog[$service_id] = $record;
        }
        
        $custom_rules_arr = json_decode(get_option('niers_kombi_custom_rules', '{}'), true);
        if(!is_array($custom_rules_arr)) $custom_rules_arr = array();
        foreach ($this->get_default_custom_rule_overrides() as $rule_id => $rule_values) {
            if(!isset($custom_rules_arr[$rule_id])) $custom_rules_arr[$rule_id] = $rule_values;
        }

        // Saison-Zeiten
        $season_start = get_option('niers_kombi_season_start', '15.04.');
        $season_end = get_option('niers_kombi_season_end', '31.10.');
        $api_base_url = $this->get_remote_api_base_url();
        $primary_highlight_color = $this->sanitize_hex_color_with_default(get_option('niers_kombi_primary_highlight_color', '#2e7d28'), '#2E7D28');
        $secondary_highlight_color = $this->sanitize_hex_color_with_default(get_option('niers_kombi_secondary_highlight_color', '#DD8100'), '#DD8100');
        $cart_endpoint = get_option('niers_kombi_cart_endpoint', home_url('/shopping_cart.php'));
        $cart_redirect = get_option('niers_kombi_cart_redirect', home_url('/warenkorb/'));
        $debug_mode = get_option('niers_kombi_debug_mode', '0');
        $contact_step_enabled = get_option('niers_kombi_contact_step_enabled', '0');
        $contact_step_url = get_option('niers_kombi_contact_step_url', home_url('/buchungsdaten/'));
        $customer_portal_url = get_option('niers_kombi_customer_portal_url', home_url('/kundenportal/'));
        $transactional_email_test_mode = get_option('niers_kombi_transactional_email_test_mode', '1');
        $transactional_email_test_recipient = $this->get_transactional_email_test_recipient();
        $transactional_email_runner_interval = $this->get_transactional_email_runner_interval();
        $transactional_email_runner_interval_options = $this->get_transactional_email_runner_interval_options();
        $transactional_email_pre_send_time = $this->get_transactional_email_pre_send_time();
        $transactional_email_post_send_time = $this->get_transactional_email_post_send_time();
        $transactional_email_review_link = $this->get_transactional_email_review_link();
        $transactional_email_contact_phone = $this->get_transactional_email_contact_phone();
        $transactional_email_contact_email = $this->get_transactional_email_contact_email();
        $test_order_emails = get_option('niers_kombi_test_order_emails', '');

        ?>
        <div class="wrap">
            <h1>🚣‍♂️ Kombi-Touren Konfigurator (Datenbank)</h1>
            <p>Verwalte hier alle bekannten Touren-IDs. Das Plugin ruft die Live-Daten ab, damit du den Aufbau der Touren auf einen Blick siehst.</p>

            <?php if (isset($_GET['scan_done'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html('Leistungs-Scan abgeschlossen. Verarbeitet: ' . intval($_GET['processed']) . ' | gespeichert/aktualisiert: ' . intval($_GET['stored']) . ' | ungültig/inaktiv: ' . intval($_GET['invalid'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['catalog_saved'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Katalog-Einstellungen wurden gespeichert.</p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['cleanup_done'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html('Leere Datensätze bereinigt: ' . intval($_GET['removed'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['resync_done'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html('Katalog-Resync abgeschlossen. Verarbeitet: ' . intval($_GET['processed']) . ' | aktualisiert: ' . intval($_GET['updated']) . ' | ungültig/inaktiv: ' . intval($_GET['invalid'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['reclassify_done'])): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html('Katalog-Typen neu abgeleitet. Geänderte Einträge: ' . intval($_GET['updated'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['config_import']) && $_GET['config_import'] === 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html('Konfiguration importiert. Uebernommene Katalog-Eintraege: ' . intval(isset($_GET['imported_catalog']) ? $_GET['imported_catalog'] : 0)); ?></p>
            </div>
            <?php elseif (isset($_GET['config_import']) && $_GET['config_import'] === 'missing_file'): ?>
            <div class="notice notice-error is-dismissible">
                <p>Bitte eine Export-Datei auswaehlen.</p>
            </div>
            <?php elseif (isset($_GET['config_import']) && $_GET['config_import'] === 'empty_file'): ?>
            <div class="notice notice-error is-dismissible">
                <p>Die gewaehlte Datei ist leer.</p>
            </div>
            <?php elseif (isset($_GET['config_import']) && $_GET['config_import'] === 'invalid_json'): ?>
            <div class="notice notice-error is-dismissible">
                <p>Die Import-Datei ist ungueltig oder passt nicht zu diesem Konfigurator-Export.</p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['voucher_saved'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Gutschein wurde gespeichert.</p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['voucher_deleted'])): ?>
            <div class="notice notice-success is-dismissible">
                <p>Gutschein wurde archiviert.</p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['portal_demo_seeded']) && $_GET['portal_demo_seeded'] === 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo esc_html('Demo-Daten für das Kundenportal wurden für ' . sanitize_text_field(isset($_GET['username']) ? wp_unslash($_GET['username']) : 'den Benutzer') . ' erzeugt bzw. aktualisiert.'); ?></p>
            </div>
            <?php elseif (isset($_GET['portal_demo_seeded']) && $_GET['portal_demo_seeded'] === 'error'): ?>
            <div class="notice notice-error is-dismissible">
                <p>Der angegebene WordPress-Benutzer konnte nicht gefunden werden oder hat keine gültige E-Mail-Adresse.</p>
            </div>
            <?php endif; ?>

            <?php if (isset($_GET['template_saved']) && $_GET['template_saved'] === 'success'): ?>
            <div class="notice notice-success is-dismissible">
                <p>E-Mail-Template wurde gespeichert.</p>
            </div>
            <?php elseif (isset($_GET['template_saved']) && $_GET['template_saved'] === 'error'): ?>
            <div class="notice notice-error is-dismissible">
                <p>Das E-Mail-Template konnte nicht gespeichert werden.</p>
            </div>
            <?php endif; ?>

            <?php if ($show_contacts): ?>
                <?php $this->render_contacts_admin_section(); ?>
            <?php endif; ?>

            <?php if ($show_dashboard): ?>
                <?php $this->render_dashboard_admin_section(); ?>
            <?php endif; ?>

            <?php if ($show_funnel): ?>
                <?php $this->render_checkout_funnel_admin_section(); ?>
            <?php endif; ?>

            <?php if ($show_portal): ?>
                <?php $this->render_portal_admin_section(); ?>
            <?php endif; ?>

            <?php if ($show_emails): ?>
                <?php $this->render_transactional_email_admin_section(); ?>
            <?php endif; ?>

            <?php if ($show_vouchers): ?>
                <?php $this->render_vouchers_admin_section(); ?>
            <?php endif; ?>
            
            <?php if ($show_settings): ?>
            <form method="post" action="options.php" style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; margin-bottom:20px;">
                <?php settings_fields('niers_kombi_options'); ?>
                
                <h3 style="margin-top:0;">Grundeinstellungen</h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="niers_kombi_tour_ids">Alle Kombi-Tour IDs</label></th>
                        <td>
                            <input type="text" name="niers_kombi_tour_ids" id="niers_kombi_tour_ids" value="<?php echo esc_attr($saved_ids); ?>" class="regular-text" style="width:100%;" placeholder="z.B. 603, 156, 12">
                            <p class="description">Gib hier die IDs kommagetrennt ein, um sie in der Tabelle unten aufzulisten.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_season_start">Saison-Start</label></th>
                        <td>
                            <input type="text" name="niers_kombi_season_start" id="niers_kombi_season_start" value="<?php echo esc_attr($season_start); ?>" class="regular-text" style="width:150px;" placeholder="15.04.">
                            <p class="description">Ab welchem Datum (Tag.Monat.) dürfen Kunden buchen?</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_season_end">Saison-Ende</label></th>
                        <td>
                            <input type="text" name="niers_kombi_season_end" id="niers_kombi_season_end" value="<?php echo esc_attr($season_end); ?>" class="regular-text" style="width:150px;" placeholder="31.10.">
                            <p class="description">Bis zu welchem Datum (Tag.Monat.) dürfen Kunden buchen?</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_api_base_url">Remote-API-Basis</label></th>
                        <td>
                            <input type="text" name="niers_kombi_api_base_url" id="niers_kombi_api_base_url" value="<?php echo esc_attr($api_base_url); ?>" class="regular-text" style="width:100%;" placeholder="<?php echo esc_attr($this->get_default_remote_api_base_url()); ?>">
                            <p class="description">Basis-URL fuer <code>service_data.php</code> und <code>quota_data.php</code>, z.B. <code><?php echo esc_html($this->get_default_remote_api_base_url()); ?></code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_primary_highlight_color">Highlight-Farbe Gruen</label></th>
                        <td>
                            <input type="text" name="niers_kombi_primary_highlight_color" id="niers_kombi_primary_highlight_color" value="<?php echo esc_attr($primary_highlight_color); ?>" class="regular-text" style="width:150px;" placeholder="#2E7D28">
                            <p class="description">Primaere Highlight-Farbe fuer Buttons, aktive Schritte, Preise und positive Markierungen.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_secondary_highlight_color">Highlight-Farbe Orange</label></th>
                        <td>
                            <input type="text" name="niers_kombi_secondary_highlight_color" id="niers_kombi_secondary_highlight_color" value="<?php echo esc_attr($secondary_highlight_color); ?>" class="regular-text" style="width:150px;" placeholder="#DD8100">
                            <p class="description">Sekundaere Highlight-Farbe fuer Checkout-CTA und orangene Status-Hinweise.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_custom_rules">Sonderregeln (JSON)</label></th>
                        <td>
                            <textarea name="niers_kombi_custom_rules" id="niers_kombi_custom_rules" rows="5" style="width:100%; font-family:monospace;"><?php echo esc_textarea(get_option('niers_kombi_custom_rules')); ?></textarea>
                            <p class="description">Beispiel: <code>{"156": {"min_pax": 10}}</code> erzwingt bei ID 156 mindestens 10 Personen (als Fallback zur API).</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_cart_endpoint">Warenkorb-Endpoint</label></th>
                        <td>
                            <input type="text" name="niers_kombi_cart_endpoint" id="niers_kombi_cart_endpoint" value="<?php echo esc_attr($cart_endpoint); ?>" class="regular-text" style="width:100%;" placeholder="<?php echo esc_attr(home_url('/shopping_cart.php')); ?>">
                            <p class="description">URL zur bestehenden Warenkorb-Schnittstelle, z.B. <code><?php echo esc_html(home_url('/shopping_cart.php')); ?></code>.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_cart_redirect">Warenkorb-Seite</label></th>
                        <td>
                            <input type="text" name="niers_kombi_cart_redirect" id="niers_kombi_cart_redirect" value="<?php echo esc_attr($cart_redirect); ?>" class="regular-text" style="width:100%;" placeholder="<?php echo esc_attr(home_url('/warenkorb/')); ?>">
                            <p class="description">Zielseite nach erfolgreichem Hinzufügen zum Warenkorb.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_contacts_module_enabled">Modul Kundendaten</label></th>
                        <td>
                            <label for="niers_kombi_contacts_module_enabled" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_contacts_module_enabled" id="niers_kombi_contacts_module_enabled" value="1" <?php checked($contacts_module_enabled ? '1' : '0', '1'); ?>>
                                Kundendaten-Modul aktivieren
                            </label>
                            <p class="description">Schaltet Backend-Bereich, Shortcode, WordPress-Speicherung und Checkout-Vorbefüllung frei.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_contact_step_enabled">Kundendaten-Zwischenschritt</label></th>
                        <td>
                            <label for="niers_kombi_contact_step_enabled" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_contact_step_enabled" id="niers_kombi_contact_step_enabled" value="1" <?php checked($contact_step_enabled, '1'); ?>>
                                Vor dem Warenkorb einen eigenen Kundendaten-Schritt aktivieren
                            </label>
                            <p class="description">Speichert Kontaktdaten zuerst in WordPress und füllt den späteren Checkout automatisch vor. Greift nur, wenn das Kundendaten-Modul aktiv ist.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_contact_step_url">URL Kundendaten-Schritt</label></th>
                        <td>
                            <input type="text" name="niers_kombi_contact_step_url" id="niers_kombi_contact_step_url" value="<?php echo esc_attr($contact_step_url); ?>" class="regular-text" style="width:100%;" placeholder="<?php echo esc_attr(home_url('/buchungsdaten/')); ?>">
                            <p class="description">Auf dieser Seite sollte der Shortcode <code>[fxp_booking_contact_step]</code> eingebunden sein.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_dashboard_module_enabled">Modul Umsatz-Dashboard</label></th>
                        <td>
                            <label for="niers_kombi_dashboard_module_enabled" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_dashboard_module_enabled" id="niers_kombi_dashboard_module_enabled" value="1" <?php checked($dashboard_module_enabled ? '1' : '0', '1'); ?>>
                                Umsatz-/Analyse-Dashboard aktivieren
                            </label>
                            <p class="description">Erfasst erfolgreiche <code>cart_data.php</code>-Bestellungen und zeigt Umsatz sowie letzte Orders im Backend an.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_customer_portal_module_enabled">Modul Kundenportal</label></th>
                        <td>
                            <label for="niers_kombi_customer_portal_module_enabled" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_customer_portal_module_enabled" id="niers_kombi_customer_portal_module_enabled" value="1" <?php checked($customer_portal_module_enabled ? '1' : '0', '1'); ?>>
                                Kundenportal-Modul aktivieren
                            </label>
                            <p class="description">Schaltet das Kundenportal-Backend und den Shortcode <code>[fxp_customer_portal]</code> frei. So können eingeloggte Benutzer ihre kommenden Touren und ihre Historie einsehen.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_customer_portal_url">URL Kundenportal</label></th>
                        <td>
                            <input type="text" name="niers_kombi_customer_portal_url" id="niers_kombi_customer_portal_url" value="<?php echo esc_attr($customer_portal_url); ?>" class="regular-text" style="width:100%;" placeholder="<?php echo esc_attr(home_url('/kundenportal/')); ?>">
                            <p class="description">Auf dieser Seite sollte der Shortcode <code>[fxp_customer_portal]</code> eingebunden sein. Die URL wird im Backend für Vorschau- und Demo-Links verwendet.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_module_enabled">Modul Transaktionale E-Mails</label></th>
                        <td>
                            <label for="niers_kombi_transactional_email_module_enabled" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_transactional_email_module_enabled" id="niers_kombi_transactional_email_module_enabled" value="1" <?php checked($transactional_email_module_enabled ? '1' : '0', '1'); ?>>
                                Übersicht für transaktionale E-Mails aktivieren
                            </label>
                            <p class="description">Zeigt geplante Trigger-Regeln wie Tour-Erinnerungen, Kandidaten und inhaltliche Bausteine im Backend an. Der automatische Versand bleibt in dieser Stufe noch deaktiviert.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_test_mode">Testmodus Transaktionale E-Mails</label></th>
                        <td>
                            <label for="niers_kombi_transactional_email_test_mode" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_transactional_email_test_mode" id="niers_kombi_transactional_email_test_mode" value="1" <?php checked($transactional_email_test_mode, '1'); ?>>
                                Versand im Testmodus halten
                            </label>
                            <p class="description">Sicherheitsmodus für den späteren Live-Versand: Solange aktiv, sollen transaktionale E-Mails nur an eine definierte Testadresse gehen und nicht an echte Kunden.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_test_recipient">Test-E-Mail für transaktionale E-Mails</label></th>
                        <td>
                            <input type="email" name="niers_kombi_transactional_email_test_recipient" id="niers_kombi_transactional_email_test_recipient" value="<?php echo esc_attr($transactional_email_test_recipient); ?>" class="regular-text" style="width:100%;" placeholder="<?php echo esc_attr(get_option('admin_email', '')); ?>">
                            <p class="description">An diese Adresse sollen Testversände gehen, solange der Testmodus aktiv ist.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_runner_interval">Cron-/Runner-Intervall</label></th>
                        <td>
                            <select name="niers_kombi_transactional_email_runner_interval" id="niers_kombi_transactional_email_runner_interval">
                                <?php foreach ($transactional_email_runner_interval_options as $interval_value => $interval_label): ?>
                                    <option value="<?php echo esc_attr($interval_value); ?>" <?php selected($transactional_email_runner_interval, $interval_value); ?>><?php echo esc_html($interval_label); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description">Wie häufig WordPress prüfen soll, ob transaktionale E-Mails jetzt fällig sind.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_pre_send_time">Versandzeit vor der Tour</label></th>
                        <td>
                            <input type="time" name="niers_kombi_transactional_email_pre_send_time" id="niers_kombi_transactional_email_pre_send_time" value="<?php echo esc_attr($transactional_email_pre_send_time); ?>">
                            <p class="description">Zu dieser Uhrzeit sollen Mails wie <code>Vor der Tour: Niers</code> am Vortag fällig werden.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_post_send_time">Versandzeit nach der Tour</label></th>
                        <td>
                            <input type="time" name="niers_kombi_transactional_email_post_send_time" id="niers_kombi_transactional_email_post_send_time" value="<?php echo esc_attr($transactional_email_post_send_time); ?>">
                            <p class="description">Zu dieser Uhrzeit sollen Mails wie <code>Nach der Tour: Niers</code> am Folgetag fällig werden.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_review_link">Google-Bewertungslink</label></th>
                        <td>
                            <input type="url" name="niers_kombi_transactional_email_review_link" id="niers_kombi_transactional_email_review_link" value="<?php echo esc_attr($transactional_email_review_link); ?>" class="regular-text" style="width:100%;" placeholder="https://...">
                            <p class="description">Wird für Platzhalter wie <code>{review_link}</code> in Nach-der-Tour-Mails verwendet.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_contact_phone">Kontakt-Telefon für E-Mails</label></th>
                        <td>
                            <input type="text" name="niers_kombi_transactional_email_contact_phone" id="niers_kombi_transactional_email_contact_phone" value="<?php echo esc_attr($transactional_email_contact_phone); ?>" class="regular-text" style="width:100%;" placeholder="z.B. 02831-9132930">
                            <p class="description">Wird für den Platzhalter <code>{contact_phone}</code> genutzt.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_transactional_email_contact_email">Kontakt-E-Mail für E-Mails</label></th>
                        <td>
                            <input type="email" name="niers_kombi_transactional_email_contact_email" id="niers_kombi_transactional_email_contact_email" value="<?php echo esc_attr($transactional_email_contact_email); ?>" class="regular-text" style="width:100%;" placeholder="<?php echo esc_attr(get_option('admin_email', '')); ?>">
                            <p class="description">Wird für den Platzhalter <code>{contact_email}</code> genutzt.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_test_order_emails">Testbestellungen per E-Mail filtern</label></th>
                        <td>
                            <textarea name="niers_kombi_test_order_emails" id="niers_kombi_test_order_emails" rows="4" style="width:100%;"><?php echo esc_textarea($test_order_emails); ?></textarea>
                            <p class="description">Eine E-Mail pro Zeile oder kommagetrennt. Bestellungen mit diesen E-Mail-Adressen werden im Dashboard automatisch als Test markiert und standardmäßig aus dem Umsatz herausgefiltert.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_vouchers_module_enabled">Modul Gutscheine</label></th>
                        <td>
                            <label for="niers_kombi_vouchers_module_enabled" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_vouchers_module_enabled" id="niers_kombi_vouchers_module_enabled" value="1" <?php checked($vouchers_module_enabled ? '1' : '0', '1'); ?>>
                                Gutschein-Modul aktivieren
                            </label>
                            <p class="description">Blendet die Gutscheinverwaltung im Backend ein. Die spätere Einlöse-Logik kann darauf aufbauen.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="niers_kombi_debug_mode">Debug-Ansicht im Frontend</label></th>
                        <td>
                            <label for="niers_kombi_debug_mode" style="display:inline-flex; align-items:center; gap:8px;">
                                <input type="checkbox" name="niers_kombi_debug_mode" id="niers_kombi_debug_mode" value="1" <?php checked($debug_mode, '1'); ?>>
                                Debug-Badge und API-/Payload-Daten im Frontend anzeigen
                            </label>
                            <p class="description">Für den Live-Betrieb deaktivieren. Bei Bedarf kann die Debug-Ansicht hier temporär wieder aktiviert werden.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Speichern & Live-Daten aktualisieren'); ?>
            </form>

            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; margin-bottom:20px;">
                <h3 style="margin-top:0;">Import / Export</h3>
                <p>Exportiert und importiert die gemeinsam nutzbaren Plugin-Zuweisungen und Einstellungen zwischen mehreren Daniel-van-Bonn-Seiten. Seitenbezogene WordPress-Metafelder pro Beitrag werden bewusst nicht mitgenommen.</p>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="margin-bottom:18px;">
                    <?php wp_nonce_field('niers_kombi_export_config', 'niers_kombi_export_config_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_export_config">
                    <?php submit_button('Konfiguration exportieren', 'secondary', 'submit', false); ?>
                </form>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" enctype="multipart/form-data" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap;">
                    <?php wp_nonce_field('niers_kombi_import_config', 'niers_kombi_import_config_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_import_config">
                    <input type="hidden" name="current_page" value="<?php echo esc_attr($current_page); ?>">
                    <div>
                        <label for="niers-kombi-config-file"><strong>Export-Datei</strong></label><br>
                        <input type="file" id="niers-kombi-config-file" name="niers_kombi_config_file" accept=".json,application/json" required>
                    </div>
                    <?php submit_button('Konfiguration importieren', 'secondary', 'submit', false); ?>
                </form>
                <p class="description">Der Import ersetzt die gespeicherte Plugin-Konfiguration auf dieser Website durch die Werte aus der Export-Datei.</p>
            </div>
            <?php endif; ?>

            <?php if ($show_scanner): ?>
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; margin-bottom:20px;">
                <h2 style="margin-top:0;">Leistungs-Scanner</h2>
                <p>Scanne den ID-Bereich der API und speichere alle gültigen Datensätze lokal im Plugin. Neue Datensätze landen zunächst neutral im Katalog und können danach klassifiziert werden.</p>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:10px;">
                    <?php wp_nonce_field('niers_kombi_scan_services', 'niers_kombi_scan_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_scan_services">
                    <input type="hidden" name="current_page" value="<?php echo esc_attr($current_page); ?>">
                    <div>
                        <label for="scan-start"><strong>Start-ID</strong></label><br>
                        <input type="number" id="scan-start" name="start_id" value="1" min="1" style="width:120px;">
                    </div>
                    <div>
                        <label for="scan-end"><strong>End-ID</strong></label><br>
                        <input type="number" id="scan-end" name="end_id" value="1000" min="1" style="width:120px;">
                    </div>
                    <div>
                        <label for="scan-batch"><strong>Batch-Größe</strong></label><br>
                        <input type="number" id="scan-batch" name="batch_size" value="50" min="1" max="100" style="width:120px;">
                    </div>
                    <?php submit_button('IDs scannen', 'secondary', 'submit', false); ?>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:10px;">
                    <?php wp_nonce_field('niers_kombi_resync_catalog', 'niers_kombi_resync_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_resync_catalog">
                    <input type="hidden" name="current_page" value="<?php echo esc_attr($current_page); ?>">
                    <div>
                        <label for="resync-batch"><strong>Batch-Größe Resync</strong></label><br>
                        <input type="number" id="resync-batch" name="batch_size" value="50" min="1" max="100" style="width:160px;">
                    </div>
                    <?php submit_button('Bestehenden Katalog synchronisieren', 'secondary', 'submit', false); ?>
                </form>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:10px;">
                    <?php wp_nonce_field('niers_kombi_reclassify_catalog', 'niers_kombi_reclassify_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_reclassify_catalog">
                    <input type="hidden" name="current_page" value="<?php echo esc_attr($current_page); ?>">
                    <?php submit_button('Katalog-Typen neu ableiten', 'secondary', 'submit', false); ?>
                </form>
                <p class="description">Empfehlung: Bereich <code>1-1000</code> mit Batch-Größe <code>50</code>. Der Scan läuft blockweise weiter, damit die Admin-Seite nicht in ein Timeout läuft.</p>
                <p class="description">Der Resync aktualisiert nur bereits bekannte IDs und behält manuell gepflegte Werte wie <code>Typ</code> und <code>Status</code> bei.</p>
                <p class="description">Die Neuableitung berechnet nur den <code>Typ</code> anhand der bereits gespeicherten Live-Daten neu, ohne neuen API-Scan.</p>
            </div>
            <?php endif; ?>

            <?php if ($show_catalog): ?>
            <div style="background:#fff; padding:20px; border:1px solid #ccd0d4; border-radius:4px; margin-bottom:20px;">
                <h2 style="margin-top:0;"><?php echo $current_page === 'niers-kombi-variants' ? 'Varianten' : 'Leistungskatalog'; ?></h2>
                <p><?php echo $current_page === 'niers-kombi-variants'
                    ? 'Hier pflegst du verknüpfte Leistungs-Varianten wie Tipidorf/Schloss oder spätere E-Bike-Auswahlen.'
                    : 'Hier kannst du pro ID pflegen, welche Art Leistung der Datensatz ist. Das ist die Grundlage für spätere Paddel-, Kombi-, Unterkunfts- und Onepager-Flows.'; ?></p>
                <p><strong>Gefundene Datensätze:</strong> <?php echo intval(count($catalog)); ?></p>

                <div style="display:flex; gap:10px; flex-wrap:wrap; margin-bottom:15px;">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('niers_kombi_export_services', 'niers_kombi_export_nonce'); ?>
                        <input type="hidden" name="action" value="niers_kombi_export_services">
                        <input type="hidden" name="format" value="json">
                        <?php submit_button('JSON exportieren', 'secondary', 'submit', false); ?>
                    </form>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('niers_kombi_export_services', 'niers_kombi_export_nonce'); ?>
                        <input type="hidden" name="action" value="niers_kombi_export_services">
                        <input type="hidden" name="format" value="csv">
                        <?php submit_button('CSV exportieren', 'secondary', 'submit', false); ?>
                    </form>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Wirklich alle leeren Platzhalter-Datensätze ohne Name, Preise und Quoten entfernen?');">
                        <?php wp_nonce_field('niers_kombi_cleanup_catalog', 'niers_kombi_cleanup_nonce'); ?>
                        <input type="hidden" name="action" value="niers_kombi_cleanup_catalog">
                        <input type="hidden" name="current_page" value="<?php echo esc_attr($current_page); ?>">
                        <?php submit_button('Leere Datensätze bereinigen', 'delete', 'submit', false); ?>
                    </form>
                </div>
                <p class="description" style="margin-top:-5px; margin-bottom:15px;">Die Bereinigung entfernt nur offensichtliche Platzhalter ohne Namen, Preise, Uhrzeiten und Quoten. Benannte Datensätze ohne Quoten bleiben erhalten.</p>

                <?php if ($current_page !== 'niers-kombi-variants'): ?>
                <nav class="nav-tab-wrapper" style="margin-bottom:15px;">
                    <?php foreach ($catalog_tabs as $tab_key => $tab): ?>
                        <?php
                        $tab_url = add_query_arg(array(
                            'page' => $current_page,
                            'catalog_tab' => $tab_key,
                            'catalog_search' => $search_term,
                            'catalog_status' => $status_filter,
                            'catalog_quota' => $quota_filter,
                        ), admin_url('admin.php'));
                        ?>
                        <a href="<?php echo esc_url($tab_url); ?>" class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                            <?php echo esc_html($tab['label'] . ' (' . $tab['count'] . ')'); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>
                <?php endif; ?>

                <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>" style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:15px;">
                    <input type="hidden" name="page" value="<?php echo esc_attr($current_page); ?>">
                    <input type="hidden" name="catalog_tab" value="<?php echo esc_attr($active_tab); ?>">
                    <div>
                        <label for="catalog-search"><strong>Suche</strong></label><br>
                        <input type="text" id="catalog-search" name="catalog_search" value="<?php echo esc_attr($search_term); ?>" placeholder="ID, Name oder Station..." style="width:260px;">
                    </div>
                    <div>
                        <label for="catalog-status"><strong>Status</strong></label><br>
                        <select id="catalog-status" name="catalog_status" style="width:160px;">
                            <option value="all" <?php selected($status_filter, 'all'); ?>>Alle</option>
                            <?php foreach ($catalog_statuses as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($status_filter, $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="catalog-quota"><strong>Quoten</strong></label><br>
                        <select id="catalog-quota" name="catalog_quota" style="width:160px;">
                            <option value="all" <?php selected($quota_filter, 'all'); ?>>Alle</option>
                            <option value="with" <?php selected($quota_filter, 'with'); ?>>Mit Quoten</option>
                            <option value="without" <?php selected($quota_filter, 'without'); ?>>Ohne Quoten</option>
                        </select>
                    </div>
                    <?php submit_button('Filtern', 'secondary', 'submit', false); ?>
                    <a href="<?php echo esc_url(add_query_arg(array('page' => $current_page, 'catalog_tab' => $active_tab), admin_url('admin.php'))); ?>" class="button">Filter zurücksetzen</a>
                </form>
                <p><strong>Aktuelle Treffer:</strong> <?php echo intval(count($filtered_catalog)); ?></p>

                <?php if (!empty($filtered_catalog)): ?>
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <?php wp_nonce_field('niers_kombi_save_catalog', 'niers_kombi_save_catalog_nonce'); ?>
                    <input type="hidden" name="action" value="niers_kombi_save_catalog">
                    <input type="hidden" name="current_page" value="<?php echo esc_attr($current_page); ?>">
                    <input type="hidden" name="catalog_tab" value="<?php echo esc_attr($active_tab); ?>">
                    <input type="hidden" name="catalog_search" value="<?php echo esc_attr($search_term); ?>">
                    <input type="hidden" name="catalog_status_filter" value="<?php echo esc_attr($status_filter); ?>">
                    <input type="hidden" name="catalog_quota_filter" value="<?php echo esc_attr($quota_filter); ?>">

                    <div style="display:flex; gap:12px; align-items:flex-end; flex-wrap:wrap; margin-bottom:15px; padding:12px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:8px;">
                        <div>
                            <label for="bulk-product-type"><strong>Massen-Typ</strong></label><br>
                            <select id="bulk-product-type" name="bulk_product_type" style="width:180px;">
                                <option value="">Nicht ändern</option>
                                <?php foreach ($catalog_product_types as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label for="bulk-status"><strong>Massen-Status</strong></label><br>
                            <select id="bulk-status" name="bulk_status" style="width:180px;">
                                <option value="">Nicht ändern</option>
                                <?php foreach ($catalog_statuses as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>"><?php echo esc_html($label); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <button type="button" class="button" id="catalog-select-visible">Alle Treffer auswählen</button>
                        </div>
                        <div>
                            <button type="button" class="button" id="catalog-clear-visible">Auswahl leeren</button>
                        </div>
                        <p style="margin:0; color:#6b7280;">Bulk-Werte werden nur auf markierte Einträge angewendet.</p>
                    </div>

                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width:48px;"><input type="checkbox" id="catalog-check-all"></th>
                                <th style="width:70px;">ID</th>
                                <th>Name</th>
                                <th style="width:140px;">Typ</th>
                                <th style="width:120px;">Status</th>
                                <th style="width:110px;">Start</th>
                                <th style="width:110px;">Erw.</th>
                                <th style="width:110px;">Min. Preis</th>
                                <th>Stationen / Hinweise</th>
                                <th style="width:190px;">Verknüpfte Leistungen</th>
                                <th style="width:170px;">Zusatz-Uhrzeiten</th>
                                <th style="width:145px;">Sync</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filtered_catalog as $service_id => $record): ?>
                            <?php
                            $group_ids = $this->get_linked_group_ids($service_id, $record);
                            $is_grouped = count($group_ids) > 1;
                            ?>
                            <tr>
                                <td><input type="checkbox" class="catalog-row-check" name="selected_ids[]" value="<?php echo intval($service_id); ?>"></td>
                                <td><strong><?php echo intval($service_id); ?></strong></td>
                                <td>
                                    <strong><?php echo esc_html($record['name']); ?></strong><br>
                                    <small style="color:#666;">Quoten: <?php echo !empty($record['has_quotas']) ? 'ja' : 'nein'; ?></small>
                                    <?php if ($is_grouped): ?>
                                    <div style="margin-top:6px;">
                                        <span style="display:inline-block; background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; border-radius:999px; padding:2px 8px; font-size:11px; font-weight:600;">
                                            Varianten: <?php echo esc_html(implode(', ', array_map('intval', $group_ids))); ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <select name="catalog[<?php echo intval($service_id); ?>][product_type]" style="width:100%;">
                                        <?php foreach ($catalog_product_types as $value => $label): ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($record['product_type'], $value); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <select name="catalog[<?php echo intval($service_id); ?>][status]" style="width:100%;">
                                        <?php foreach ($catalog_statuses as $value => $label): ?>
                                        <option value="<?php echo esc_attr($value); ?>" <?php selected($record['status'], $value); ?>><?php echo esc_html($label); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td><?php echo !empty($record['begin_time']) ? esc_html(substr($record['begin_time'], 0, 5) . ' Uhr') : '—'; ?></td>
                                <td><?php echo $record['adult_price_brutto'] !== null && $record['adult_price_brutto'] !== '' ? esc_html(number_format((float)$record['adult_price_brutto'], 2, ',', '.') . ' €') : '—'; ?></td>
                                <td><?php echo $record['min_price_brutto'] !== null && $record['min_price_brutto'] !== '' ? esc_html(number_format((float)$record['min_price_brutto'], 2, ',', '.') . ' €') : '—'; ?></td>
                                <td>
                                    <?php if (!empty($record['stations'])): ?>
                                        <?php foreach ($record['stations'] as $station): ?>
                                        <div style="margin-bottom:6px;">
                                            <strong><?php echo esc_html('S' . $station['step']); ?>:</strong>
                                            <?php echo esc_html(implode(', ', $station['items'])); ?>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <em style="color:#999;">Noch keine Stationsdaten erkannt</em>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <details>
                                        <summary style="cursor:pointer; font-weight:600;">
                                            <?php echo $is_grouped ? esc_html(count($group_ids) . ' Varianten') : 'Keine Varianten'; ?>
                                        </summary>
                                        <textarea
                                            name="catalog[<?php echo intval($service_id); ?>][linked_services]"
                                            rows="4"
                                            style="width:100%; font-family:monospace; margin-top:8px;"
                                            placeholder="202=Übernachtung im Tipidorf&#10;203=Übernachtung im Schloss"
                                        ><?php echo esc_textarea($this->format_linked_services_value(isset($record['linked_services']) ? $record['linked_services'] : array())); ?></textarea>
                                        <small style="display:block; color:#666; margin-top:4px;">Eine Zeile pro Variante: <code>ID=Label</code></small>
                                    </details>
                                </td>
                                <td>
                                    <input
                                        type="text"
                                        name="catalog[<?php echo intval($service_id); ?>][manual_times]"
                                        value="<?php echo esc_attr(isset($record['manual_times']) ? $record['manual_times'] : ''); ?>"
                                        placeholder="z.B. 09:00,10:00,11:00"
                                        style="width:100%;"
                                    >
                                </td>
                                <td><?php echo !empty($record['last_synced_at']) ? esc_html($record['last_synced_at']) : '—'; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php submit_button('Katalog speichern'); ?>
                </form>
                <script>
                (function() {
                    const master = document.getElementById('catalog-check-all');
                    const rowChecks = Array.from(document.querySelectorAll('.catalog-row-check'));
                    const selectBtn = document.getElementById('catalog-select-visible');
                    const clearBtn = document.getElementById('catalog-clear-visible');

                    if (master) {
                        master.addEventListener('change', function() {
                            rowChecks.forEach(function(cb) { cb.checked = master.checked; });
                        });
                    }
                    if (selectBtn) {
                        selectBtn.addEventListener('click', function() {
                            rowChecks.forEach(function(cb) { cb.checked = true; });
                            if (master) master.checked = true;
                        });
                    }
                    if (clearBtn) {
                        clearBtn.addEventListener('click', function() {
                            rowChecks.forEach(function(cb) { cb.checked = false; });
                            if (master) master.checked = false;
                        });
                    }
                    rowChecks.forEach(function(cb) {
                        cb.addEventListener('change', function() {
                            if (!master) return;
                            master.checked = rowChecks.length > 0 && rowChecks.every(function(item) { return item.checked; });
                        });
                    });
                })();
                </script>
                <?php else: ?>
                <div class="notice notice-info inline"><p><?php echo empty($catalog) ? 'Noch keine gescannten Datensätze vorhanden.' : 'Für diese Tab-/Filter-Kombination wurden keine Datensätze gefunden.'; ?></p></div>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <?php if ($show_overview && !empty($id_array)): ?>
            <h2>Live-Daten Übersicht & Baupläne</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Tour-Name</th>
                        <th>Preise & Zeiten</th>
                        <th>Stationen & Ausrüstung</th>
                        <th>Aktive Sonderregeln</th>
                        <th style="width:80px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    foreach ($id_array as $id) {
                        $response = wp_remote_get($this->get_remote_service_data_url($id));
                        if (is_wp_error($response)) {
                            echo "<tr><td>{$id}</td><td colspan='5' style='color:red;'>API Fehler</td></tr>";
                            continue;
                        }
                        
                        $body = wp_remote_retrieve_body($response);
                        $data = json_decode($body, true);
                        
                        if ($data && isset($data['status']) && $data['status'] == true) {
                            $tour = $data['data'];
                            
                            $price_a = $tour['adult_price_brutto'] !== null && $tour['adult_price_brutto'] !== '' ? '€ ' . number_format($tour['adult_price_brutto'], 2, ',', '.') : '-';
                            $price_c = $tour['child_price_brutto'] !== null && $tour['child_price_brutto'] !== '' ? '€ ' . number_format($tour['child_price_brutto'], 2, ',', '.') : '€ 0,00';
                            $time = $tour['begin_time'] ? substr($tour['begin_time'], 0, 5) . ' Uhr' : 'Flexibel';
                            $details = "<strong>Start:</strong> {$time}<br><strong>Erw:</strong> {$price_a} | <strong>Kind:</strong> {$price_c}";
                            
                            // ========================================================
                            // NEU: HINTERGRUND-ABFRAGE FÜR AUSRÜSTUNGSNAMEN
                            // Wir simulieren ein Datum mitten im Sommer, um die echten Namen zu laden.
                            // ========================================================
                            $quota_names = array();
                            $y = (int)date('Y');
                            if ((int)date('m') > 9) $y++; // Wenn Oktober-Dez, nimm nächstes Jahr
                            $dummy_date = $y . '-07-15';
                            $dummy_time = $tour['begin_time'] ? $tour['begin_time'] : '10:00:00';
                            
                            $q_resp = wp_remote_post($this->get_remote_quota_data_url(), array(
                                'body' => array(
                                    'service_id' => $id,
                                    'date' => $dummy_date,
                                    'time' => $dummy_time,
                                )
                            ));
                            
                            if (!is_wp_error($q_resp)) {
                                $q_data = json_decode(wp_remote_retrieve_body($q_resp), true);
                                if ($q_data && $q_data['status'] && isset($q_data['data'])) {
                                    foreach ($q_data['data'] as $k => $v) {
                                        if (strpos($k, '_quotas') !== false && is_array($v)) {
                                            foreach ($v as $q) {
                                                $qid = isset($q['id']) ? $q['id'] : (isset($q['quota_id']) ? $q['quota_id'] : '');
                                                if ($qid && isset($q['name'])) {
                                                    $quota_names[$qid] = $q['name'];
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $stations = array();
                            for ($i = 1; $i <= 15; $i++) {
                                $key = "s{$i}_quotas";
                                if (!empty($tour[$key])) {
                                    $ids = is_array($tour[$key]) ? $tour[$key] : explode(',', $tour[$key]);
                                    $names_arr = array();
                                    foreach($ids as $qid) {
                                        $qid = trim($qid);
                                        // Übersetze ID in Namen, falls gefunden
                                        $name = isset($quota_names[$qid]) ? $quota_names[$qid] : "ID {$qid}";
                                        $names_arr[] = $name;
                                    }
                                    $names_str = implode(', ', $names_arr);
                                    
                                    // Schickes Layout untereinander
                                    $stations[] = "<div style='margin-bottom:8px; line-height:1.4;'>
                                        <span style='display:inline-block; background:#1f2937; color:#fff; padding:2px 6px; border-radius:4px; font-size:10px; font-weight:bold; margin-right:6px; vertical-align:top; width: 50px; text-align:center;'>Schritt {$i}</span> 
                                        <span style='font-size:12px; display:inline-block; width:calc(100% - 75px);'>{$names_str}</span>
                                    </div>";
                                }
                            }
                            $stations_str = empty($stations) ? '<em style="color:#999;">Keine Stationen gefunden</em>' : implode('', $stations);

                            $rule_str = isset($custom_rules_arr[$id]) ? "<code style='background:#e6f4ff; color:#0050a1; font-size:11px; padding:4px; display:block;'>" . json_encode($custom_rules_arr[$id]) . "</code>" : "<em style='color:#ccc;'>Keine</em>";

                            echo "<tr>";
                            echo "<td><strong>{$tour['id']}</strong></td>";
                            echo "<td><strong>{$tour['name']}</strong></td>";
                            echo "<td>{$details}</td>";
                            echo "<td>{$stations_str}</td>";
                            echo "<td>{$rule_str}</td>";
                            echo "<td style='color:green; font-weight:bold;'>Aktiv</td>";
                            echo "</tr>";
                        } else {
                            echo "<tr><td><strong>{$id}</strong></td><td colspan='5' style='color:#dc3232;'>Tour nicht gefunden oder inaktiv</td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * ==========================================
     * 2. BACKEND: META BOX (Für Henrik)
     * ==========================================
     */
    public function add_tour_meta_box() {
        $screens = array('page', 'post');
        foreach ($screens as $screen) {
            add_meta_box('niers_kombi_id_box', '🚣‍♂️ Kombi-Tour Einstellungen', array($this, 'meta_box_html'), $screen, 'side', 'high');
        }
    }

    public function meta_box_html($post) {
        $value = get_post_meta($post->ID, '_niers_kombi_tour_id', true);
        wp_nonce_field('niers_kombi_save_data', 'niers_kombi_meta_nonce');
        ?>
        <p><strong>Service ID der Tour:</strong></p>
        <input type="number" name="niers_kombi_tour_id" id="niers_kombi_tour_id" value="<?php echo esc_attr($value); ?>" style="width:100%; padding:5px;" placeholder="z.B. 603">
        <p style="font-size:12px; color:#666;">Trage hier die ID ein und platziere den Shortcode <code>[niers_kombi_tour]</code> oder <code>[kombi_tour_konfigurator]</code> im Text.</p>
        <?php
    }

    public function save_tour_meta_box($post_id) {
        if (!isset($_POST['niers_kombi_meta_nonce']) || !wp_verify_nonce($_POST['niers_kombi_meta_nonce'], 'niers_kombi_save_data')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        if (isset($_POST['niers_kombi_tour_id'])) {
            update_post_meta($post_id, '_niers_kombi_tour_id', sanitize_text_field($_POST['niers_kombi_tour_id']));
            update_post_meta($post_id, 'tour_service_id', sanitize_text_field($_POST['niers_kombi_tour_id']));
        }
    }

    private function fetch_service_data($service_id) {
        $response = wp_remote_get($this->get_remote_service_data_url($service_id));
        if (is_wp_error($response)) return null;

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!$data || empty($data['status']) || empty($data['data'])) return null;

        return $data['data'];
    }

    private function fetch_quota_data($service_id, $quotas_begin_time, $overnight_stays = null) {
        $timestamp = strtotime($quotas_begin_time);
        $date = $timestamp ? date('Y-m-d', $timestamp) : substr((string)$quotas_begin_time, 0, 10);
        $time = $timestamp ? date('H:i:s', $timestamp) : (strlen((string)$quotas_begin_time) >= 19 ? substr((string)$quotas_begin_time, 11, 8) : '00:00:00');
        $body = array(
            'service_id' => intval($service_id),
            'date' => $date,
            'time' => $time,
        );
        if (!empty($overnight_stays)) {
            $body['overnight_stays'] = $overnight_stays;
        }

        $response = wp_remote_post($this->get_remote_quota_data_url(), array(
            'body' => $body,
        ));
        if (is_wp_error($response)) return null;

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!$data || empty($data['status']) || empty($data['data'])) return null;

        return $data['data'];
    }

    private function service_has_boats($service_id, $service_data = null) {
        if (!$service_data) $service_data = $this->fetch_service_data($service_id);
        if (!$service_data) return false;

        $y = (int)date('Y');
        if ((int)date('m') > 9) $y++;
        $dummy_date = $y . '-07-15';
        $dummy_time = !empty($service_data['begin_time']) ? $service_data['begin_time'] : '10:00:00';
        $overnight_stays = !empty($service_data['overnight_stays']) ? $service_data['overnight_stays'] : null;
        $quota_data = $this->fetch_quota_data($service_id, $dummy_date . ' ' . $dummy_time, $overnight_stays);
        if (!$quota_data) return false;

        foreach ($quota_data as $key => $quotas) {
            if (strpos($key, '_quotas') === false || !is_array($quotas)) continue;
            foreach ($quotas as $quota) {
                $name = isset($quota['name']) ? strtolower($quota['name']) : '';
                if (
                    strpos($name, 'kajak') !== false ||
                    strpos($name, 'kanadier') !== false ||
                    strpos($name, 'kanu') !== false ||
                    strpos($name, 'schlauchboot') !== false ||
                    strpos($name, 'boot') !== false
                ) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * ==========================================
     * 3. FRONTEND: SHORTCODE & UI
     * ==========================================
     */
    public function render_shortcode($atts) {
        $a = shortcode_atts(array('id' => ''), $atts);
        $service_id = esc_attr($a['id']);

        if (empty($service_id) && isset($_GET['tour_id'])) $service_id = sanitize_text_field($_GET['tour_id']);
        if (empty($service_id)) $service_id = get_post_meta(get_the_ID(), '_niers_kombi_tour_id', true);

        if (empty($service_id)) {
            return '<div style="padding:25px; border:2px dashed #dc2626; color:#dc2626; border-radius:12px; text-align:center; background:#fef2f2; font-family:sans-serif;">
                        <strong style="display:block; margin-bottom:10px; font-size:18px;">⚠️ Konfigurator-Fehler</strong>
                        Keine Touren-ID gefunden. Bitte ID in der rechten Seitenleiste eintragen.
                    </div>';
        }

        $catalog = $this->get_service_catalog();
        $catalog_record = isset($catalog[intval($service_id)]) && is_array($catalog[intval($service_id)]) ? $catalog[intval($service_id)] : array();

        // Sicherstellen, dass Sonderregeln geparst werden (und Hardcode Fallbacks existieren)
        $custom_rules_arr = json_decode(get_option('niers_kombi_custom_rules', '{}'), true);
        if(!is_array($custom_rules_arr)) $custom_rules_arr = array();
        foreach ($this->get_default_custom_rule_overrides() as $rule_id => $rule_values) {
            if(!isset($custom_rules_arr[$rule_id])) $custom_rules_arr[$rule_id] = $rule_values;
        }
        foreach ($catalog as $catalog_service_id => $catalog_rule_record) {
            if (empty($catalog_rule_record['linked_services']) || !is_array($catalog_rule_record['linked_services'])) continue;
            if (!isset($custom_rules_arr[$catalog_service_id]) || !is_array($custom_rules_arr[$catalog_service_id])) {
                $custom_rules_arr[$catalog_service_id] = array();
            }
            $custom_rules_arr[$catalog_service_id]['linked_services'] = $catalog_rule_record['linked_services'];
        }
        if (!empty($catalog_record['linked_services']) && is_array($catalog_record['linked_services'])) {
            if (!isset($custom_rules_arr[$service_id]) || !is_array($custom_rules_arr[$service_id])) {
                $custom_rules_arr[$service_id] = array();
            }
            $custom_rules_arr[$service_id]['linked_services'] = $catalog_record['linked_services'];
        }
        $custom_rules_json = json_encode($custom_rules_arr);
        
        // Saison-Zeiten laden
        $season_start = get_option('niers_kombi_season_start', '15.04.');
        $season_end = get_option('niers_kombi_season_end', '31.10.');
        $service_data_endpoint = $this->get_remote_service_data_url();
        $quota_data_endpoint = $this->get_remote_quota_data_url();
        $primary_highlight_color = $this->sanitize_hex_color_with_default(get_option('niers_kombi_primary_highlight_color', '#2e7d28'), '#2E7D28');
        $secondary_highlight_color = $this->sanitize_hex_color_with_default(get_option('niers_kombi_secondary_highlight_color', '#DD8100'), '#DD8100');
        $primary_highlight_rgb = $this->hex_to_rgb_string($primary_highlight_color);
        $secondary_highlight_rgb = $this->hex_to_rgb_string($secondary_highlight_color);
        $primary_highlight_light = $this->blend_hex_colors($primary_highlight_color, '#FFFFFF', 0.35);
        $primary_highlight_soft = $this->blend_hex_colors($primary_highlight_color, '#FFFFFF', 0.88);
        $primary_highlight_soft_border = $this->blend_hex_colors($primary_highlight_color, '#FFFFFF', 0.72);
        $primary_highlight_deep = $this->blend_hex_colors($primary_highlight_color, '#000000', 0.28);
        $cart_endpoint = get_option('niers_kombi_cart_endpoint', home_url('/shopping_cart.php'));
        $cart_redirect = get_option('niers_kombi_cart_redirect', home_url('/warenkorb/'));
        $contact_step_enabled = $this->is_contact_step_enabled();
        $contact_step_url = get_option('niers_kombi_contact_step_url', home_url('/buchungsdaten/'));
        $debug_mode_enabled = get_option('niers_kombi_debug_mode', '0') === '1';
        $manual_time_options = array();
        if (!empty($catalog_record['manual_times'])) {
            $manual_time_options = array_values(array_filter(array_map('trim', explode(',', $catalog_record['manual_times']))));
        }
        $linked_service_price_map = array();
        if (!empty($catalog_record['linked_services']) && is_array($catalog_record['linked_services'])) {
            foreach ($catalog_record['linked_services'] as $linked_service) {
                $linked_service_id = isset($linked_service['id']) ? intval($linked_service['id']) : 0;
                if ($linked_service_id < 1 || empty($catalog[$linked_service_id]) || !is_array($catalog[$linked_service_id])) continue;
                $linked_service_price_map[(string) $linked_service_id] = array(
                    'adult_price_brutto' => isset($catalog[$linked_service_id]['adult_price_brutto']) ? $catalog[$linked_service_id]['adult_price_brutto'] : null,
                    'name' => isset($catalog[$linked_service_id]['name']) ? $catalog[$linked_service_id]['name'] : '',
                );
            }
        }
        if (!isset($linked_service_price_map[(string) intval($service_id)])) {
            $linked_service_price_map[(string) intval($service_id)] = array(
                'adult_price_brutto' => isset($catalog_record['adult_price_brutto']) ? $catalog_record['adult_price_brutto'] : null,
                'name' => isset($catalog_record['name']) ? $catalog_record['name'] : '',
            );
        }
        $service_data = $this->fetch_service_data($service_id);
        $apply_season_limits = $this->service_has_boats($service_id, $service_data);

        ob_start();
        ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
        <script src="https://npmcdn.com/flatpickr/dist/l10n/de.js"></script>

        <style>
          #nkombi-wrapper { --nkombi-primary: <?php echo esc_html($primary_highlight_color); ?>; --nkombi-primary-rgb: <?php echo esc_html($primary_highlight_rgb); ?>; --nkombi-primary-light: <?php echo esc_html($primary_highlight_light); ?>; --nkombi-primary-soft: <?php echo esc_html($primary_highlight_soft); ?>; --nkombi-primary-soft-border: <?php echo esc_html($primary_highlight_soft_border); ?>; --nkombi-primary-deep: <?php echo esc_html($primary_highlight_deep); ?>; --nkombi-secondary: <?php echo esc_html($secondary_highlight_color); ?>; --nkombi-secondary-rgb: <?php echo esc_html($secondary_highlight_rgb); ?>; font-family: 'Open Sans', sans-serif !important; color: #4b5563 !important; width: 100% !important; margin: 0 auto !important; line-height: 1.5 !important; box-sizing: border-box !important; position: relative !important; }
          #nkombi-wrapper * { box-sizing: border-box !important; }
          @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=PT+Sans:wght@700&display=swap');

          /* --- EXTREM STRIKTES INPUT-CSS GEGEN THE7 (.form-control.input) --- */
          #nkombi-wrapper input.nc-input-date, 
          #nkombi-wrapper input.form-control,
          #nkombi-wrapper select.nc-select { 
              width: 100% !important; 
              height: 55px !important; 
              padding: 0 16px !important; 
              background-color: #f9fafb !important; 
              border: 1px solid #e5e7eb !important; 
              border-radius: 8px !important; 
              font-size: 16px !important; 
              font-weight: 600 !important; 
              color: #1f2937 !important; 
              cursor: pointer !important; 
              appearance: none !important; 
              -webkit-appearance: none !important; 
              box-shadow: none !important; 
              outline: none !important; 
              margin: 0 !important; 
              display: block !important;
              box-sizing: border-box !important;
          }

          #nkombi-wrapper input[type="number"]::-webkit-outer-spin-button,
          #nkombi-wrapper input[type="number"]::-webkit-inner-spin-button { -webkit-appearance: none !important; margin: 0 !important; }
          #nkombi-wrapper input[type="number"] { -moz-appearance: textfield !important; }

          /* --- FLATPICKR / KALENDER-FIXES GEGEN THE7 / WPBAKERY --- */
          #nkombi-wrapper .flatpickr-calendar {
              width: 320px !important;
              max-width: calc(100vw - 24px) !important;
              padding: 10px 12px 12px !important;
              border: 1px solid #e5e7eb !important;
              border-radius: 12px !important;
              box-shadow: 0 18px 40px rgba(15, 23, 42, 0.18) !important;
              font-family: 'Open Sans', sans-serif !important;
          }
          #nkombi-wrapper .flatpickr-calendar *,
          .flatpickr-calendar#nkombi-wrapper *,
          .flatpickr-calendar * {
              box-sizing: border-box !important;
          }
          #nkombi-wrapper .flatpickr-months,
          .flatpickr-calendar .flatpickr-months {
              position: relative !important;
              display: flex !important;
              align-items: center !important;
              min-height: 44px !important;
              margin-bottom: 8px !important;
          }
          #nkombi-wrapper .flatpickr-month,
          .flatpickr-calendar .flatpickr-month {
              height: auto !important;
          }
          #nkombi-wrapper .flatpickr-current-month,
          .flatpickr-calendar .flatpickr-current-month {
              position: static !important;
              left: auto !important;
              width: 100% !important;
              height: auto !important;
              padding: 0 34px !important;
              display: flex !important;
              align-items: center !important;
              justify-content: center !important;
              gap: 8px !important;
              font-size: 18px !important;
              line-height: 1.2 !important;
          }
          #nkombi-wrapper .flatpickr-current-month .flatpickr-monthDropdown-months,
          .flatpickr-calendar .flatpickr-current-month .flatpickr-monthDropdown-months {
              width: auto !important;
              min-width: 0 !important;
              height: auto !important;
              padding: 0 !important;
              margin: 0 !important;
              border: none !important;
              background: transparent !important;
              box-shadow: none !important;
              outline: none !important;
              font-family: 'PT Sans', sans-serif !important;
              font-size: 18px !important;
              font-weight: 700 !important;
              line-height: 1.2 !important;
              color: #1f2937 !important;
              appearance: none !important;
              -webkit-appearance: none !important;
          }
          #nkombi-wrapper .flatpickr-current-month input.cur-year,
          .flatpickr-calendar .flatpickr-current-month input.cur-year {
              width: 58px !important;
              min-width: 58px !important;
              height: auto !important;
              padding: 0 !important;
              margin: 0 !important;
              border: none !important;
              background: transparent !important;
              box-shadow: none !important;
              font-family: 'PT Sans', sans-serif !important;
              font-size: 18px !important;
              font-weight: 700 !important;
              line-height: 1.2 !important;
              color: #1f2937 !important;
          }
          #nkombi-wrapper .flatpickr-current-month .numInputWrapper,
          .flatpickr-calendar .flatpickr-current-month .numInputWrapper {
              width: 58px !important;
              min-width: 58px !important;
              padding: 0 !important;
          }
          #nkombi-wrapper .flatpickr-current-month .numInputWrapper span,
          .flatpickr-calendar .flatpickr-current-month .numInputWrapper span {
              border: none !important;
          }
          #nkombi-wrapper .flatpickr-prev-month,
          #nkombi-wrapper .flatpickr-next-month,
          .flatpickr-calendar .flatpickr-prev-month,
          .flatpickr-calendar .flatpickr-next-month {
              top: 50% !important;
              transform: translateY(-50%) !important;
              width: 32px !important;
              height: 32px !important;
              padding: 0 !important;
              display: flex !important;
              align-items: center !important;
              justify-content: center !important;
              border-radius: 999px !important;
              color: #1f2937 !important;
          }
          #nkombi-wrapper .flatpickr-prev-month:hover,
          #nkombi-wrapper .flatpickr-next-month:hover,
          .flatpickr-calendar .flatpickr-prev-month:hover,
          .flatpickr-calendar .flatpickr-next-month:hover {
              background: #f3f4f6 !important;
          }
          #nkombi-wrapper .flatpickr-prev-month svg,
          #nkombi-wrapper .flatpickr-next-month svg,
          .flatpickr-calendar .flatpickr-prev-month svg,
          .flatpickr-calendar .flatpickr-next-month svg {
              width: 14px !important;
              height: 14px !important;
          }
          #nkombi-wrapper .flatpickr-weekdays,
          .flatpickr-calendar .flatpickr-weekdays {
              height: auto !important;
              margin-bottom: 6px !important;
          }
          #nkombi-wrapper span.flatpickr-weekday,
          .flatpickr-calendar span.flatpickr-weekday {
              height: auto !important;
              margin: 0 !important;
              background: transparent !important;
              color: #6b7280 !important;
              font-size: 12px !important;
              font-weight: 700 !important;
              line-height: 1.2 !important;
              text-transform: none !important;
          }
          #nkombi-wrapper .flatpickr-days,
          .flatpickr-calendar .flatpickr-days {
              width: 100% !important;
          }
          #nkombi-wrapper .dayContainer,
          .flatpickr-calendar .dayContainer {
              width: 100% !important;
              min-width: 100% !important;
              max-width: 100% !important;
          }
          #nkombi-wrapper .flatpickr-day,
          .flatpickr-calendar .flatpickr-day {
              max-width: none !important;
              height: 40px !important;
              line-height: 40px !important;
              border-radius: 10px !important;
              font-size: 16px !important;
              color: #1f2937 !important;
          }
          #nkombi-wrapper .flatpickr-day:hover,
          .flatpickr-calendar .flatpickr-day:hover {
              background: #f3f4f6 !important;
              border-color: #f3f4f6 !important;
          }
          #nkombi-wrapper .flatpickr-day.selected,
          #nkombi-wrapper .flatpickr-day.startRange,
          #nkombi-wrapper .flatpickr-day.endRange,
          .flatpickr-calendar .flatpickr-day.selected,
          .flatpickr-calendar .flatpickr-day.startRange,
          .flatpickr-calendar .flatpickr-day.endRange {
              background: var(--nkombi-primary) !important;
              border-color: var(--nkombi-primary) !important;
              color: #ffffff !important;
          }
          #nkombi-wrapper .flatpickr-day.today:not(.selected):not(.startRange):not(.endRange),
          #nkombi-wrapper .flatpickr-day.today.flatpickr-disabled:not(.selected):not(.startRange):not(.endRange),
          .flatpickr-calendar .flatpickr-day.today:not(.selected):not(.startRange):not(.endRange),
          .flatpickr-calendar .flatpickr-day.today.flatpickr-disabled:not(.selected):not(.startRange):not(.endRange) {
              color: #1f2937 !important;
              -webkit-text-fill-color: #1f2937 !important;
              opacity: 1 !important;
              background: #f8fafc !important;
              border-color: var(--nkombi-primary) !important;
              box-shadow: inset 0 0 0 1px var(--nkombi-primary) !important;
              font-weight: 700 !important;
          }
          #nkombi-wrapper .flatpickr-day.flatpickr-disabled,
          #nkombi-wrapper .flatpickr-day.prevMonthDay,
          #nkombi-wrapper .flatpickr-day.nextMonthDay,
          .flatpickr-calendar .flatpickr-day.flatpickr-disabled,
          .flatpickr-calendar .flatpickr-day.prevMonthDay,
          .flatpickr-calendar .flatpickr-day.nextMonthDay {
              color: #d1d5db !important;
          }
          @media (max-width: 767px) {
              #nkombi-wrapper .flatpickr-calendar,
              .flatpickr-calendar {
                  width: min(320px, calc(100vw - 24px)) !important;
              }
          }

          /* --- LAYOUT --- */
          #nkombi-wrapper .nc-container { background: #ffffff !important; border-radius: 12px !important; box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08) !important; padding: 20px !important; border-top: 5px solid var(--nkombi-primary) !important; max-width: 1300px !important; margin: 0 auto !important; width: 100% !important; position: relative !important; }
          #nkombi-wrapper .nc-layout { display: flex !important; flex-direction: column !important; gap: 20px !important; flex-wrap: wrap !important; position: relative !important; }
          #nkombi-wrapper .nc-main { flex: 1 1 0% !important; min-width: 0 !important; position: relative !important; max-width: 100% !important; }
          
          #nkombi-wrapper .nc-sidebar { display: none !important; } 
          
          @media (max-width: 1023px) { 
              #nkombi-wrapper .nc-main { padding-bottom: 90px !important; } 
              #mb-btn-step1 { display: none !important; } 
          }
          
          @media (min-width: 1024px) { 
              #nkombi-wrapper .nc-layout { flex-direction: row !important; align-items: flex-start !important; flex-wrap: nowrap !important; } 
              #nkombi-wrapper .nc-main { padding: 30px !important; }
              #nkombi-wrapper .nc-sidebar { 
                  display: block !important; width: 340px !important; flex: 0 0 340px !important; position: relative !important; align-self: flex-start !important; 
              }
              #nkombi-wrapper .nc-sidebar-inner {
                  background: #ffffff !important; border-radius: 12px !important; box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08) !important; padding: 25px !important; border-top: 5px solid #0f172a !important;
                  position: sticky !important; top: 80px !important;
              }
          }

          /* --- MOBILE BAR --- */
          #nkombi-wrapper .nc-mobile-bar { display: none; justify-content: space-between; align-items: center; position: fixed; bottom: 0 !important; left: 0 !important; right: 0 !important; width: 100% !important; background: #fff; box-shadow: 0 -4px 30px rgba(0,0,0,0.25); padding: 15px 20px; z-index: 2147483647 !important; border-top: 1px solid #e5e7eb; }
          @media (max-width: 1023px) { #nkombi-wrapper .nc-mobile-bar.is-visible { display: flex !important; } }
          #nkombi-wrapper .nc-mobile-bar.is-footer-hidden { display: none !important; }
          @media (max-width: 1023px) { #nkombi-wrapper .nc-mobile-bar.is-visible.is-footer-hidden { display: none !important; } }

          /* --- UI ELEMENTS --- */
          #nkombi-wrapper .nc-label { display: block !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; color: #6b7280 !important; margin-bottom: 6px !important; letter-spacing: 0.5px !important; }
          
          #nkombi-wrapper .nc-counter-group { display: block !important; width: 100% !important; }
          #nkombi-wrapper .nc-counter { flex: 1 !important; background-color: #f9fafb !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; padding: 4px !important; height: 55px !important; margin: 0 !important; box-sizing: border-box !important; }
          #nkombi-wrapper .nc-input-number { flex: 1 !important; min-width: 0 !important; border: none !important; background: transparent !important; text-align: center !important; font-weight: 700 !important; font-size: 18px !important; color: #1f2937 !important; outline: none !important; pointer-events: none !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; height: auto !important; line-height: 1 !important; }
          #nkombi-wrapper .nc-btn-icon { width: 45px !important; height: 100% !important; min-height: 45px !important; border: none !important; background: #e5e7eb !important; cursor: pointer !important; color: var(--nkombi-primary) !important; border-radius: 6px !important; display: flex !important; align-items: center !important; justify-content: center !important; transition: background 0.2s !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; flex-shrink: 0 !important; }
          #nkombi-wrapper .nc-btn-icon:hover { background: var(--nkombi-primary-soft) !important; }
          #nkombi-wrapper .nc-btn-icon:disabled { opacity: 0.25 !important; cursor: not-allowed !important; }
          #nkombi-wrapper .nc-btn-icon svg { width: 18px !important; height: 18px !important; stroke-width: 2.5 !important; }

          /* --- MAIN BUTTON --- */
          #nkombi-wrapper .nc-btn-main { width: 100% !important; height: 60px !important; border: none !important; border-radius: 8px !important; font-family: 'PT Sans', sans-serif !important; font-weight: 700 !important; font-size: 18px !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #fff !important; transition: all 0.2s ease !important; text-decoration: none !important; margin: 0 !important; padding: 0 !important; }
          #nkombi-wrapper .nc-btn-main.active { background: var(--nkombi-primary) !important; box-shadow: 0 4px 15px rgba(var(--nkombi-primary-rgb), 0.3) !important; color: #ffffff !important; }
          #nkombi-wrapper .nc-btn-main.active.checkout-mode { background: var(--nkombi-secondary) !important; box-shadow: 0 4px 15px rgba(var(--nkombi-secondary-rgb), 0.3) !important; color: #ffffff !important; }
          #nkombi-wrapper .nc-btn-main.disabled,
          #nkombi-wrapper .nc-btn-main:disabled,
          #nkombi-wrapper .nc-btn-main.checkout-mode.disabled,
          #nkombi-wrapper .nc-btn-main.checkout-mode:disabled { background: #e5e7eb !important; color: #9ca3af !important; cursor: not-allowed !important; box-shadow: none !important; }
          #nkombi-wrapper .nc-btn-secondary { display: inline-flex !important; align-items: center !important; justify-content: center !important; min-height: 42px !important; padding: 0 18px !important; border-radius: 999px !important; border: 1px solid #cbd5e1 !important; background: #ffffff !important; color: var(--nkombi-primary) !important; font-family: 'PT Sans', sans-serif !important; font-size: 15px !important; font-weight: 700 !important; cursor: pointer !important; transition: all 0.2s ease !important; box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06) !important; }
          #nkombi-wrapper .nc-btn-secondary:hover { background: var(--nkombi-primary-soft) !important; border-color: var(--nkombi-primary-light) !important; color: var(--nkombi-primary-deep) !important; }

          /* --- GRIDS --- */
          #nkombi-wrapper .nc-grid { display: grid !important; grid-template-columns: 1fr !important; gap: 15px !important; margin-bottom: 20px !important; }
          #nkombi-wrapper .nc-pax-grid { display: grid !important; grid-template-columns: 1fr !important; gap: 15px !important; margin-bottom: 20px !important; }
          @media (min-width: 768px) { 
              #nkombi-wrapper .nc-grid { grid-template-columns: 1fr 1fr !important; gap: 20px !important; }
              #nkombi-wrapper .nc-pax-grid { grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)) !important; gap: 20px !important; }
          }

          /* --- CARDS & ITEMS --- */
          #nkombi-wrapper .nc-section-card { border: 2px solid #e5e7eb !important; border-radius: 12px !important; padding: 20px !important; background: #fff !important; transition: all 0.2s !important; margin-bottom: 20px !important; }
          #nkombi-wrapper .nc-section-card.is-full { border-color: var(--nkombi-primary-light) !important; background: var(--nkombi-primary-soft) !important; }
          #nkombi-wrapper .nc-section-header { display: flex !important; align-items: center !important; gap: 12px !important; margin-bottom: 15px !important; border-bottom: 1px dashed #e5e7eb !important; padding-bottom: 15px !important; }
          #nkombi-wrapper .nc-section-badge { background: #1f2937 !important; color: white !important; width: 30px !important; height: 30px !important; border-radius: 50% !important; display: flex !important; align-items: center !important; justify-content: center !important; font-weight: 700 !important; font-size: 14px !important; }
          
          #nkombi-wrapper .nc-quota-item { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 10px !important; background: #f9fafb !important; padding: 12px !important; border-radius: 10px !important; border: 1px solid #e5e7eb !important; transition: all 0.2s !important; }
          #nkombi-wrapper .nc-quota-item.is-active { background: var(--nkombi-primary-soft) !important; border-color: var(--nkombi-primary-light) !important; }
          
          #nkombi-wrapper .nc-price-val { font-family: 'PT Sans', sans-serif !important; font-weight: 700 !important; font-size: 32px !important; color: var(--nkombi-primary) !important; line-height: 1 !important; }
              #nkombi-wrapper .nc-price-pp { font-size: 13px !important; color: #6b7280 !important; font-weight: normal !important; margin-top: 4px !important; margin-bottom: 16px !important; }
          
          #nkombi-wrapper .nc-boat-counter { display: flex !important; align-items: center !important; gap: 8px !important; margin: 0 !important; }
          #nkombi-wrapper .nc-boat-counter span { min-width: 24px !important; text-align: center !important; font-weight: 700 !important; font-size: 16px !important; }

          /* --- STEPPER --- */
          #nkombi-wrapper .nc-stepper { display: flex !important; justify-content: center !important; align-items: center !important; margin-bottom: 25px !important; gap: 5px !important; flex-wrap: wrap !important; }
          #nkombi-wrapper .nc-step { display: flex !important; align-items: center !important; gap: 8px !important; font-weight: 700 !important; font-size: 13px !important; color: #9ca3af !important; }
          #nkombi-wrapper .nc-step.active { color: var(--nkombi-primary) !important; }
          #nkombi-wrapper .nc-step-num { width: 28px !important; height: 28px !important; border-radius: 50% !important; background: #f3f4f6 !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #9ca3af !important; }
          #nkombi-wrapper .nc-step.active .nc-step-num { background: var(--nkombi-primary) !important; color: #fff !important; }
          #nkombi-wrapper .nc-step-divider { flex-grow: 1 !important; max-width: 40px !important; height: 2px !important; background: #e5e7eb !important; }
          #nkombi-wrapper .nc-step-panel { display: none !important; animation: nc-fade-in 0.4s ease-out !important; }
          #nkombi-wrapper .nc-step-panel.active { display: block !important; }
          @keyframes nc-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
          
          #nc-error-msg { color: #dc2626 !important; background: #fee2e2 !important; border: 1px solid #fecaca !important; padding: 12px !important; border-radius: 8px !important; font-size: 14px !important; font-weight: 600 !important; margin-top: 15px !important; text-align: center !important; display: none; }
          #nc-success-overlay { position: absolute !important; top: 0 !important; left: 0 !important; width: 100% !important; height: 100% !important; background: rgba(255,255,255,0.95) !important; z-index: 10000 !important; display: none; flex-direction: column !important; align-items: center !important; justify-content: center !important; border-radius: 12px !important; text-align: center !important; }
          #nkombi-wrapper .sb-item { margin-bottom: 15px !important; }
          #nkombi-wrapper .nc-progress-track { width: 100% !important; height: 8px !important; background: #e5e7eb !important; border-radius: 999px !important; overflow: hidden !important; margin-top: 8px !important; }
          #nkombi-wrapper .nc-progress-bar { height: 100% !important; width: 0%; background: linear-gradient(90deg, var(--nkombi-primary) 0%, var(--nkombi-primary-light) 100%) !important; border-radius: 999px !important; transition: width 0.25s ease !important; }
          #nkombi-wrapper .nc-progress-text { font-size: 13px !important; color: #4b5563 !important; line-height: 1.45 !important; }
          #nkombi-wrapper .nc-summary-list { display: flex !important; flex-direction: column !important; gap: 8px !important; }
          #nkombi-wrapper .nc-summary-row { display: flex !important; justify-content: space-between !important; gap: 12px !important; align-items: flex-start !important; padding: 10px 12px !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; background: #f9fafb !important; }
          #nkombi-wrapper .nc-summary-row.is-complete { background: var(--nkombi-primary-soft) !important; border-color: var(--nkombi-primary-soft-border) !important; }
          #nkombi-wrapper .nc-summary-main { display: flex !important; flex-direction: column !important; gap: 3px !important; min-width: 0 !important; }
          #nkombi-wrapper .nc-summary-day { color: var(--nkombi-primary) !important; font-size: 11px !important; font-weight: 800 !important; text-transform: uppercase !important; letter-spacing: 0.04em !important; }
          #nkombi-wrapper .nc-summary-row strong { color: #1f2937 !important; font-size: 13px !important; white-space: normal !important; line-height: 1.35 !important; }
          #nkombi-wrapper .nc-summary-row span { color: #6b7280 !important; font-size: 12px !important; text-align: right !important; }
          #nkombi-wrapper .nc-summary-check { display: inline-flex !important; align-items: center !important; justify-content: flex-end !important; color: var(--nkombi-secondary) !important; }
          #nkombi-wrapper .nc-summary-check svg { width: 15px !important; height: 15px !important; }
          #nkombi-wrapper .nc-mobile-bar { gap: 12px !important; }
          #nkombi-wrapper .nc-mobile-summary { display: flex !important; flex-direction: column !important; gap: 6px !important; min-width: 0 !important; }
          #nkombi-wrapper .nc-mobile-link { border: none !important; background: none !important; padding: 0 !important; text-align: left !important; color: var(--nkombi-primary) !important; font-size: 12px !important; font-weight: 700 !important; cursor: pointer !important; text-decoration: underline !important; }
          #nkombi-wrapper .nc-mobile-sheet { position: fixed !important; left: 0 !important; right: 0 !important; bottom: 82px !important; z-index: 2147483646 !important; padding: 0 14px 12px !important; display: none !important; }
          #nkombi-wrapper .nc-mobile-sheet.is-open { display: block !important; }
          #nkombi-wrapper .nc-mobile-sheet-inner { background: rgba(255,255,255,0.98) !important; border: 1px solid #e5e7eb !important; border-radius: 16px !important; box-shadow: 0 -10px 35px rgba(0,0,0,0.18) !important; padding: 16px !important; max-height: 62vh !important; overflow: auto !important; backdrop-filter: blur(10px) !important; }
          #nkombi-wrapper .nc-mobile-sheet-head { display: flex !important; justify-content: space-between !important; align-items: center !important; margin-bottom: 14px !important; }
          #nkombi-wrapper .nc-mobile-sheet-close { border: none !important; background: none !important; color: #6b7280 !important; font-size: 13px !important; font-weight: 700 !important; cursor: pointer !important; padding: 0 !important; }
          @media (max-width: 767px) {
              #nkombi-wrapper .nc-section-card { padding: 16px !important; }
              #nkombi-wrapper .nc-quota-item { flex-direction: column !important; align-items: stretch !important; gap: 14px !important; }
              #nkombi-wrapper .nc-quota-item > div:first-child { width: 100% !important; }
              #nkombi-wrapper .nc-boat-counter { width: 100% !important; justify-content: space-between !important; }
              #nkombi-wrapper .nc-summary-row { flex-direction: column !important; gap: 6px !important; }
              #nkombi-wrapper .nc-summary-row span { text-align: left !important; }
              #nkombi-wrapper .nc-mobile-bar { padding: 14px 16px !important; }
              #nkombi-wrapper .nc-mobile-summary { flex: 1 1 auto !important; }
              #nkombi-wrapper .nc-mobile-sheet-inner { padding: 18px !important; max-height: 70vh !important; }
          }
          @media (min-width: 1024px) {
              #nkombi-wrapper .nc-mobile-sheet { display: none !important; }
          }
        </style>

        <div id="nkombi-wrapper">
          <?php if ($debug_mode_enabled): ?>
          <div style="text-align: right; margin-bottom: 5px; font-size: 11px; color: #9ca3af; font-family: monospace;">
             Plugin Version <?php echo esc_html(self::VERSION); ?> (Testphase) <br>
             <a href="#" id="nc-debug-btn" style="color: #9ca3af; text-decoration: underline; margin-top: 3px; display: inline-block;">🔍 API- & Payload-Daten anzeigen</a>
          </div>
          <pre id="nc-debug-out" style="display:none; background:#1f2937; color:var(--nkombi-primary-light); padding:15px; font-size:12px; border-radius:8px; overflow:auto; max-height:400px; margin-bottom:15px; text-align:left; border: 2px solid var(--nkombi-primary); box-shadow: inset 0 2px 10px rgba(0,0,0,0.5);"></pre>
          <?php endif; ?>

          <div class="nc-container">
            <div class="nc-layout">
                <div class="nc-main">
                    <div id="nc-success-overlay">
                        <div style="background:var(--nkombi-primary); width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <h2 style="font-family:'PT Sans', sans-serif; font-weight:700; color:#1f2937; margin-bottom:10px;">Moment bitte...</h2>
                        <p>Warenkorb wird vorbereitet.</p>
                    </div>

                    <div class="nc-stepper">
                        <div class="nc-step active" id="st-1"><div class="nc-step-num">1</div><span>Details</span></div>
                        <div class="nc-step-divider"></div>
                        <div class="nc-step" id="st-2"><div class="nc-step-num">2</div><span>Ausrüstung</span></div>
                    </div>

                    <div class="nc-step-panel active" id="panel-1">
                        <div style="background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 25px; text-align: center;">
                            <span class="nc-label">Gewählte Kombi-Tour</span>
                            <h2 id="nc-tour-name" style="font-family:'PT Sans', sans-serif; font-weight:700; color:#1f2937; margin:0; font-size:22px;">Lade Tour...</h2>
                            <div id="nc-linked-service-wrap" style="display:none; margin-top:16px; text-align:left;">
                                <span class="nc-label">Variante</span>
                                <select id="nc-linked-service" class="nc-select"></select>
                            </div>
                        </div>

                        <div class="nc-grid">
                          <div><span class="nc-label">Datum</span><input type="text" id="nc-date" class="nc-input-date" placeholder="Datum wählen..." /></div>
                          <div><span class="nc-label">Uhrzeit</span><select id="nc-time" class="nc-select" disabled><option value="">Zeit wählen</option></select></div>
                        </div>

                        <div class="nc-pax-grid">
                          <div>
                            <span class="nc-label">Erwachsene</span>
                            <div class="nc-counter-group">
                                <div class="nc-counter">
                                    <button id="btn-adults-minus" class="nc-btn-icon" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg></button>
                                    <input type="number" id="val-adults" class="nc-input-number" value="2" readonly />
                                    <button id="btn-adults-plus" class="nc-btn-icon" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></button>
                                </div>
                            </div>
                          </div>
                          <div>
                            <span class="nc-label">Kinder (5-11 J.)</span>
                            <div class="nc-counter-group">
                                <div class="nc-counter">
                                    <button id="btn-children-minus" class="nc-btn-icon" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg></button>
                                    <input type="number" id="val-children" class="nc-input-number" value="0" readonly />
                                    <button id="btn-children-plus" class="nc-btn-icon" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></button>
                                </div>
                            </div>
                          </div>
                          <div>
                            <span class="nc-label">Kleinkinder (0-4 J.)</span>
                            <div class="nc-counter-group">
                                <div class="nc-counter">
                                    <button id="btn-babies-minus" class="nc-btn-icon" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg></button>
                                    <input type="number" id="val-babies" class="nc-input-number" value="0" readonly />
                                    <button id="btn-babies-plus" class="nc-btn-icon" type="button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></button>
                                </div>
                            </div>
                          </div>
                        </div>

                        <div id="nc-error-msg"></div>
                        <div style="margin-top: 30px;"><button id="mb-btn-step1" class="nc-btn-main active">Ausrüstung wählen</button></div>
                    </div>

                    <div class="nc-step-panel" id="panel-2">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                            <h4 style="font-weight:700; margin:0; font-size:18px;">Ausrüstung zuordnen</h4>
                            <button id="btn-back-1" class="nc-btn-secondary" type="button">Daten ändern</button>
                        </div>
                        <div id="nc-loading-quotas" style="text-align:center; padding: 40px 0;">Live-Check Verfügbarkeit...</div>
                        <div id="dynamic-sections-container"></div>
                    </div>
                </div>

                <div class="nc-sidebar">
                    <div class="nc-sidebar-inner">
                    <h3 style="font-size:18px; font-weight:700; margin-bottom:15px; border-bottom:2px solid #e5e7eb; padding-bottom:10px; margin-top:0;">Deine Auswahl</h3>
                    <div class="sb-item">
                        <span class="nc-label">Tour</span>
                        <div id="sb-tour-name" style="font-weight:600; font-size:14px; color:#1f2937;">-</div>
                    </div>
                    <div class="sb-item">
                        <span class="nc-label">Termin & Personen</span>
                        <div id="sb-date" style="font-weight:400; color:#6b7280; font-size:13px;">Bitte wählen</div>
                    </div>
                    <div class="sb-item">
                        <span class="nc-label">Fortschritt</span>
                        <div id="sb-progress-text" class="nc-progress-text">Bitte wähle zuerst Termin und Personen.</div>
                        <div class="nc-progress-track"><div id="sb-progress-bar" class="nc-progress-bar"></div></div>
                    </div>
                    <div class="sb-item" id="sb-section-summary-wrap" style="display:none;">
                        <span class="nc-label">Auswahlübersicht</span>
                        <div id="sb-section-summary" class="nc-summary-list"></div>
                    </div>
                    <div style="border-top:2px solid #e5e7eb; padding-top:15px; margin-top:20px;">
                        <span class="nc-label">Gesamtpreis</span>
                        <div id="sb-price" class="nc-price-val">€ 0,00</div>
                        <div id="sb-price-pp" class="nc-price-pp"></div>
                    </div>
                    <button id="sb-btn-action" class="nc-btn-main active" style="margin-top:34px;">Weiter</button>
                    </div>
                </div>
            </div>
          </div>

          <div class="nc-mobile-bar is-visible" id="mobile-bottom-bar">
              <div class="nc-mobile-summary">
                  <div style="font-size:10px; font-weight:700; color:#9ca3af;">GESAMT</div>
                  <div id="mb-price" style="font-weight:700; font-size:24px; color:var(--nkombi-primary); line-height:1;">---</div>
                  <button id="mb-toggle-details" class="nc-mobile-link" type="button" aria-expanded="false">Details anzeigen</button>
              </div>
              <button id="mb-btn-action" class="nc-btn-main active" style="width:55% !important; height:50px !important;">Ausrüstung wählen</button>
          </div>
          <div class="nc-mobile-sheet" id="mobile-summary-sheet" aria-hidden="true">
              <div class="nc-mobile-sheet-inner">
                  <div class="nc-mobile-sheet-head">
                      <strong style="font-size:16px; color:#1f2937;">Deine Auswahl</strong>
                      <button id="mb-close-details" class="nc-mobile-sheet-close" type="button">Schließen</button>
                  </div>
                  <div class="sb-item">
                      <span class="nc-label">Tour</span>
                      <div id="mb-sheet-tour" style="font-weight:600; font-size:14px; color:#1f2937;">-</div>
                  </div>
                  <div class="sb-item">
                      <span class="nc-label">Termin & Personen</span>
                      <div id="mb-sheet-date" style="font-weight:400; color:#6b7280; font-size:13px;">Bitte wählen</div>
                  </div>
                  <div class="sb-item">
                      <span class="nc-label">Fortschritt</span>
                      <div id="mb-sheet-progress-text" class="nc-progress-text">Bitte wähle zuerst Termin und Personen.</div>
                      <div class="nc-progress-track"><div id="mb-sheet-progress-bar" class="nc-progress-bar"></div></div>
                  </div>
                  <div class="sb-item" id="mb-sheet-summary-wrap" style="display:none;">
                      <span class="nc-label">Auswahlübersicht</span>
                      <div id="mb-sheet-summary" class="nc-summary-list"></div>
                  </div>
                  <div style="border-top:2px solid #e5e7eb; padding-top:15px; margin-top:20px;">
                      <span class="nc-label">Gesamtpreis</span>
                      <div id="mb-sheet-price" class="nc-price-val">€ 0,00</div>
                      <div id="mb-sheet-price-pp" class="nc-price-pp"></div>
                  </div>
              </div>
          </div>
        </div>

        <script>
        (function() {
          const iconMinus = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg>`;
          const iconPlus = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>`;
          const iconCheck = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;

          const icons = {
              kanadier: `<div style="background:#e5e7eb; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; margin-right:12px; flex-shrink:0; color:#4b5563;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12c-4-4-10-4-14 0l-6 6h16l4-6z"/><path d="M8 12V8"/></svg></div>`,
              kajak: `<div style="background:#e5e7eb; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; margin-right:12px; flex-shrink:0; color:#4b5563;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12s-4-2-10-2-10 2-10 2 4 2 10 2 10-2 10-2z"/><path d="M7 9l3 6M17 9l-3 6"/></svg></div>`,
              fahrrad: `<div style="background:#e5e7eb; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; margin-right:12px; flex-shrink:0; color:#4b5563;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18.5" cy="17.5" r="3.5"/><circle cx="5.5" cy="17.5" r="3.5"/><circle cx="15" cy="5" r="1"/><path d="M12 17.5V14l-3-3 4-3 2 3h2"/></svg></div>`,
              transport: `<div style="background:#e5e7eb; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; margin-right:12px; flex-shrink:0; color:#4b5563;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3"/><path d="M20 17h2v-3.34a4 4 0 0 0-1.17-2.83L19 9h-5"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></div>`,
              generic: `<div style="background:#e5e7eb; border-radius:50%; width:36px; height:36px; display:flex; align-items:center; justify-content:center; margin-right:12px; flex-shrink:0; color:#4b5563;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/></svg></div>`
          };

          const getIcon = (name) => {
              const n = name.toLowerCase();
              if(n.includes('kajak')) return icons.kajak;
              if(n.includes('kanadier') || n.includes('kanu') || n.includes('boot')) return icons.kanadier;
              if(n.includes('rad') || n.includes('bike') || n.includes('pedal')) return icons.fahrrad;
              if(n.includes('transport') || n.includes('shuttle') || n.includes('anhänger')) return icons.transport;
              return icons.generic;
          };

          const customRules = <?php echo $custom_rules_json; ?>;

          const state = { 
              serviceId: <?php echo json_encode($service_id); ?>,
              seasonStartStr: <?php echo json_encode($season_start); ?>,
              seasonEndStr: <?php echo json_encode($season_end); ?>,
              manualTimeOptions: <?php echo json_encode($manual_time_options); ?>,
              linkedServicePriceMap: <?php echo wp_json_encode($linked_service_price_map); ?>,
              applySeasonLimits: <?php echo $apply_season_limits ? 'true' : 'false'; ?>,
              serviceDataEndpoint: <?php echo json_encode($service_data_endpoint); ?>,
              quotaDataEndpoint: <?php echo json_encode($quota_data_endpoint); ?>,
              cartEndpoint: <?php echo json_encode($cart_endpoint); ?>,
              cartRedirect: <?php echo json_encode($cart_redirect); ?>,
              contactStepEnabled: <?php echo $contact_step_enabled ? 'true' : 'false'; ?>,
              contactStepUrl: <?php echo json_encode($contact_step_url); ?>,
              step: 1, date: "", time: "", adults: 2, children: 0, babies: 0,
              apiServiceData: null, apiQuotaData: null, selectedQuotas: {}, autoFilledSections: {}, hiddenQuotaSelections: {}, hiddenQuotaIds: {},
              liveMinPax: null, prefetchedQuotaData: null, prefetchedQuotaKey: "", quotaPrefetchToken: 0, datePicker: null,
              linkedServiceChoicesMemory: [],
              linkedServicePriceFetched: {},
              mobileSummaryOpen: false
          };

          function stripLeadingWww(hostname) {
              return String(hostname || '').replace(/^www\./i, '');
          }

          function normalizeSameSiteUrl(rawUrl) {
              try {
                  const currentUrl = new URL(window.location.href);
                  const parsed = new URL(rawUrl, currentUrl.origin);
                  const currentHost = stripLeadingWww(currentUrl.hostname);
                  const targetHost = stripLeadingWww(parsed.hostname);

                  if ((parsed.protocol === 'http:' || parsed.protocol === 'https:') && currentHost === targetHost) {
                      return `${currentUrl.origin}${parsed.pathname}${parsed.search}${parsed.hash}`;
                  }

                  return parsed.toString();
              } catch (e) {
                  return rawUrl;
              }
          }

          function formatDateShort(dateValue) {
              return String(dateValue || '').split('-').reverse().join('.');
          }

          function saveCustomerPrefillContext() {
              try {
                  const totalPax = state.adults + state.children + state.babies;
                  const dateLabel = state.date && state.time ? `${formatDateShort(state.date)} | ${state.time.substring(0,5)} Uhr` : '';
                  const context = {
                      source: 'kombi-konfigurator',
                      source_page: window.location.href,
                      request_token: state.apiRequestToken || '',
                      service_ids: String(state.serviceId || ''),
                      tour_name: state.apiServiceData && state.apiServiceData.name ? state.apiServiceData.name : '',
                      date_label: dateLabel,
                      pax_label: `${totalPax} Pers.`,
                      adults: state.adults,
                      children: state.children,
                      babies: state.babies,
                      date: state.date || '',
                      time: state.time || ''
                  };
                  window.sessionStorage.setItem('fxpBookingContext', JSON.stringify(context));
                  window.localStorage.setItem('fxpBookingContext', JSON.stringify(context));
              } catch (e) {}
          }

          function redirectAfterCartSuccess() {
              if (state.contactStepEnabled && state.contactStepUrl) {
                  saveCustomerPrefillContext();
                  const url = new URL(state.contactStepUrl, window.location.origin);
                  url.searchParams.set('fxp_return', state.cartRedirect);
                  window.location.href = url.toString();
                  return;
              }
              window.location.href = state.cartRedirect;
          }

          state.cartEndpoint = normalizeSameSiteUrl(state.cartEndpoint);
          state.cartRedirect = normalizeSameSiteUrl(state.cartRedirect);
          state.contactStepUrl = normalizeSameSiteUrl(state.contactStepUrl);

          function buildServiceDataUrl(serviceId) {
              const url = new URL(state.serviceDataEndpoint, window.location.origin);
              url.searchParams.set('service_id', serviceId);
              return url.toString();
          }

          const els = { 
              p1: document.getElementById('panel-1'), p2: document.getElementById('panel-2'),
              st1: document.getElementById('st-1'), st2: document.getElementById('st-2'),
              date: document.getElementById('nc-date'), time: document.getElementById('nc-time'),
              linkedServiceWrap: document.getElementById('nc-linked-service-wrap'),
              linkedService: document.getElementById('nc-linked-service'),
              vA: document.getElementById('val-adults'), vC: document.getElementById('val-children'), vB: document.getElementById('val-babies'),
              sectionsContainer: document.getElementById('dynamic-sections-container'),
              sbBtnAction: document.getElementById('sb-btn-action'), mbBtnAction: document.getElementById('mb-btn-action'),
              sbTourName: document.getElementById('sb-tour-name'), sbDate: document.getElementById('sb-date'),
              sbProgressText: document.getElementById('sb-progress-text'), sbProgressBar: document.getElementById('sb-progress-bar'),
              sbSummaryWrap: document.getElementById('sb-section-summary-wrap'), sbSummary: document.getElementById('sb-section-summary'),
              mbPrice: document.getElementById('mb-price'),
              mbToggleDetails: document.getElementById('mb-toggle-details'),
              mbSheet: document.getElementById('mobile-summary-sheet'),
              mbCloseDetails: document.getElementById('mb-close-details'),
              mbSheetTour: document.getElementById('mb-sheet-tour'),
              mbSheetDate: document.getElementById('mb-sheet-date'),
              mbSheetProgressText: document.getElementById('mb-sheet-progress-text'),
              mbSheetProgressBar: document.getElementById('mb-sheet-progress-bar'),
              mbSheetSummaryWrap: document.getElementById('mb-sheet-summary-wrap'),
              mbSheetSummary: document.getElementById('mb-sheet-summary'),
              mbSheetPrice: document.getElementById('mb-sheet-price'),
              mbSheetPricePp: document.getElementById('mb-sheet-price-pp'),
              error: document.getElementById('nc-error-msg'),
              mbBar: document.getElementById('mobile-bottom-bar'),
              // Debug Elements
              debugBtn: document.getElementById('nc-debug-btn'),
              debugOut: document.getElementById('nc-debug-out')
          };

          window.getRequiredMinPax = () => {
              let req = 1;
              let hasAuthoritativePriceMin = false;
              if (state.apiServiceData) {
                  const adultPrice = parseFloat(state.apiServiceData.adult_price_brutto);
                  const minPrice = parseFloat(state.apiServiceData.min_price_brutto);
                  if (adultPrice > 0 && minPrice > 0) {
                      const derivedMin = minPrice / adultPrice;
                      if (Number.isInteger(derivedMin) && derivedMin >= 1) {
                          req = Math.max(req, derivedMin);
                          hasAuthoritativePriceMin = true;
                      }
                  }

                  if (!hasAuthoritativePriceMin) {
                      if (state.apiServiceData.min_pax !== undefined) req = parseInt(state.apiServiceData.min_pax);
                      else if (state.apiServiceData.min_persons !== undefined) req = parseInt(state.apiServiceData.min_persons);
                      else if (state.apiServiceData.min_ppl !== undefined) req = parseInt(state.apiServiceData.min_ppl);
                      else if (state.apiServiceData.min_participants !== undefined) req = parseInt(state.apiServiceData.min_participants);
                  }
              }
              if (!hasAuthoritativePriceMin && customRules[state.serviceId] && customRules[state.serviceId].min_pax) {
                  req = Math.max(req, parseInt(customRules[state.serviceId].min_pax));
              }
              if (!hasAuthoritativePriceMin && state.liveMinPax) {
                  req = Math.max(req, parseInt(state.liveMinPax, 10) || 1);
              }
              return req;
          };

          // Funktion zum Parsen von Datum-Strings ("15.04." -> 415 für einfachen Vergleich)
          function parseSeasonLimit(str) {
              if(!str) return null;
              let parts = str.split('.');
              if(parts.length >= 2) {
                  let d = parseInt(parts[0]);
                  let m = parseInt(parts[1]);
                  if(!isNaN(d) && !isNaN(m)) return m * 100 + d;
              }
              return null;
          }

          function getTomorrowDate() {
              const date = new Date();
              date.setHours(0, 0, 0, 0);
              date.setDate(date.getDate() + 1);
              return date;
          }

          function isDateWithinSeason(date) {
              const sStart = parseSeasonLimit(state.seasonStartStr);
              const sEnd = parseSeasonLimit(state.seasonEndStr);
              if (!state.applySeasonLimits || sStart === null || sEnd === null) return true;

              const current = (date.getMonth() + 1) * 100 + date.getDate();
              if (sStart <= sEnd) return current >= sStart && current <= sEnd;
              return current >= sStart || current <= sEnd;
          }

          function findNextBookableDate() {
              const date = getTomorrowDate();
              for (let i = 0; i < 400; i++) {
                  const candidate = new Date(date);
                  candidate.setDate(date.getDate() + i);
                  if (isDateWithinSeason(candidate)) return candidate;
              }
              return getTomorrowDate();
          }

          function relaxAncestorOverflow() {
              let current = document.getElementById('nkombi-wrapper');
              while (current && current.parentElement) {
                  current = current.parentElement;
                  if (current.matches && current.matches('.vc_row, .vc_row-fluid, .vc_column_container, .vc_column-inner, .wpb_wrapper')) {
                      current.style.overflow = 'visible';
                  }
              }
          }

          function setupStickySidebarFallback() {
              const layout = document.querySelector('#nkombi-wrapper .nc-layout');
              const sidebar = document.querySelector('#nkombi-wrapper .nc-sidebar');
              const sidebarInner = document.querySelector('#nkombi-wrapper .nc-sidebar-inner');
              if (!layout || !sidebar || !sidebarInner) return;

              const desktopMin = 1024;
              const topOffset = 80;

              function resetSidebar() {
                  sidebar.style.removeProperty('min-height');
                  sidebarInner.style.removeProperty('position');
                  sidebarInner.style.removeProperty('top');
                  sidebarInner.style.removeProperty('bottom');
                  sidebarInner.style.removeProperty('width');
                  sidebarInner.style.removeProperty('left');
                  sidebarInner.style.removeProperty('right');
              }

              function updateStickySidebar() {
                  if (window.innerWidth < desktopMin) {
                      resetSidebar();
                      return;
                  }

                  const layoutRect = layout.getBoundingClientRect();
                  const sidebarRect = sidebar.getBoundingClientRect();
                  const scrollTop = window.scrollY || window.pageYOffset;
                  const layoutTop = layoutRect.top + scrollTop;
                  const layoutBottom = layoutRect.bottom + scrollTop;
                  const sidebarTop = sidebarRect.top + scrollTop;
                  const sidebarHeight = sidebarInner.offsetHeight;
                  const sidebarWidth = sidebar.offsetWidth;
                  const sidebarLeft = sidebarRect.left;
                  const stopTop = layoutBottom - sidebarHeight;
                  sidebar.style.minHeight = `${sidebarHeight}px`;

                  if (scrollTop + topOffset <= layoutTop) {
                      resetSidebar();
                      return;
                  }

                  if (scrollTop + topOffset >= stopTop) {
                      sidebarInner.style.setProperty('position', 'absolute', 'important');
                      sidebarInner.style.setProperty('top', `${Math.max(0, stopTop - sidebarTop)}px`, 'important');
                      sidebarInner.style.setProperty('bottom', 'auto', 'important');
                      sidebarInner.style.setProperty('width', `${sidebarWidth}px`, 'important');
                      sidebarInner.style.setProperty('left', '0px', 'important');
                      sidebarInner.style.setProperty('right', 'auto', 'important');
                      return;
                  }

                  sidebarInner.style.setProperty('position', 'fixed', 'important');
                  sidebarInner.style.setProperty('top', `${topOffset}px`, 'important');
                  sidebarInner.style.setProperty('bottom', 'auto', 'important');
                  sidebarInner.style.setProperty('width', `${sidebarWidth}px`, 'important');
                  sidebarInner.style.setProperty('left', `${sidebarLeft}px`, 'important');
                  sidebarInner.style.setProperty('right', 'auto', 'important');
              }

              let rafId = null;
              function requestUpdate() {
                  if (rafId) cancelAnimationFrame(rafId);
                  rafId = requestAnimationFrame(updateStickySidebar);
              }

              window.addEventListener('scroll', requestUpdate, { passive: true });
              window.addEventListener('resize', requestUpdate);
              requestUpdate();
          }

          function getQuotaAvailability(q) {
              const available = parseInt(q.available, 10);
              if (!Number.isNaN(available)) return Math.max(0, available);

              const quota = parseInt(q.quota, 10);
              const reserved = parseInt(q.reserved, 10);
              if (!Number.isNaN(quota)) {
                  return Math.max(0, quota - (Number.isNaN(reserved) ? 0 : reserved));
              }

              return 0;
          }

          function getUnitsNeededForQuota(q, totalPax) {
              if (!q || totalPax < 1) return 0;

              const p = parseInt(q.ppl || q.capacity, 10) || 1;
              const m = getQuotaMinPeople(q);
              const availableUnits = getQuotaAvailability(q);
              const unitsNeeded = Math.ceil(totalPax / p);
              const minCovered = unitsNeeded * m;
              const maxCovered = unitsNeeded * p;

              if (unitsNeeded < 1 || availableUnits < unitsNeeded) return 0;
              if (minCovered > totalPax || maxCovered < totalPax) return 0;

              return unitsNeeded;
          }

          function getQuotaMinPeople(q) {
              const p = parseInt(q.ppl || q.capacity, 10) || 1;
              const rawMin = parseInt(q.min_ppl || q.quota_min_ppl, 10);
              const fallbackMin = (p >= 4 ? p - 1 : p);
              const normalizedMin = Number.isNaN(rawMin) ? fallbackMin : rawMin;
              return Math.min(normalizedMin, p);
          }

          function getSectionCoverage(selectionMap, quotas) {
              let minCovered = 0;
              let maxCovered = 0;

              Object.entries(selectionMap || {}).forEach(([id, countRaw]) => {
                  const count = parseInt(countRaw, 10) || 0;
                  if (count < 1) return;

                  const quota = quotas.find(q => String(q.id || q.quota_id) === String(id));
                  if (!quota) return;

                  const p = parseInt(quota.ppl || quota.capacity, 10) || 1;
                  const m = getQuotaMinPeople(quota);

                  minCovered += count * m;
                  maxCovered += count * p;
              });

              return { minCovered, maxCovered };
          }

          function sectionSelectionMatchesTotal(selectionMap, quotas, totalPax) {
              if (totalPax < 1) return false;
              const coverage = getSectionCoverage(selectionMap, quotas);
              return coverage.minCovered <= totalPax && coverage.maxCovered >= totalPax;
          }

          function normalizeQuotaName(name) {
              return (name || '').toLowerCase().replace(/\s+/g, ' ').trim();
          }

          function isLiveFireSourceQuota(q) {
              const name = normalizeQuotaName(q.name || '');
              return name.includes('live-fire-cooking') || name.includes('live fire cooking');
          }

          function isBreakfastQuota(q) {
              const raw = (q.name || '').toLowerCase();
              return raw.includes('frühstück') || raw.includes('fruhstuck');
          }

          function isBreakfastSourceQuota(q) {
              const raw = (q.name || '').toLowerCase();
              return raw.includes('frühstück im schloss walbeck') || raw.includes('fruhstuck im schloss walbeck');
          }

          function isYakariTargetQuota(q) {
              const id = String(q.id || q.quota_id || '');
              const name = normalizeQuotaName(q.name || '');
              return id === '107' || name.includes('yakaris quelle') || name.includes('yakari');
          }

          function isRittersaalTargetQuota(q) {
              const id = String(q.id || q.quota_id || '');
              const name = normalizeQuotaName(q.name || '');
              return id === '25' || name.includes('rittersaal');
          }

          function isBikeTransportQuota(q) {
              const name = normalizeQuotaName(q && q.name ? q.name : '');
              return name.includes('fahrradtransport');
          }

          function isBikeRentalQuota(q) {
              const name = normalizeQuotaName(q && q.name ? q.name : '');
              const compactName = name.replace(/[\s-]+/g, '');
              const looksLikeBikeRental = name.includes('fahrrad')
                  || compactName.includes('ebike')
                  || (name.includes('bike') && !name.includes('chillbike'));
              return looksLikeBikeRental && !name.includes('transport');
          }

          function isBoatQuota(q) {
              const name = normalizeQuotaName(q && q.name ? q.name : '');
              return name.includes('kajak') ||
                  name.includes('kanadier') ||
                  name.includes('kanu') ||
                  name.includes('schlauchboot') ||
                  name.includes('floß') ||
                  name.includes('floss') ||
                  name.includes('boot');
          }

          function isRoomLikeQuota(q) {
              const name = normalizeQuotaName(q && q.name ? q.name : '');
              return name.includes('zimmer') || name.includes('turmzimmer');
          }

          function getSectionQuotaType(sk) {
              const quotaDataType = state.apiQuotaData && state.apiQuotaData[`${sk}_quota_type`];
              if (quotaDataType !== undefined && quotaDataType !== null && quotaDataType !== '') {
                  return String(quotaDataType);
              }
              const serviceDataType = state.apiServiceData && state.apiServiceData[`${sk}_quota_type`];
              if (serviceDataType !== undefined && serviceDataType !== null && serviceDataType !== '') {
                  return String(serviceDataType);
              }
              return '';
          }

          function getCustomRule(serviceId = state.serviceId) {
              return customRules[serviceId] || {};
          }

          function getSectionSpecialRule(sk, quotas) {
              const rule = getCustomRule();
              const specialMode = rule && rule.special_mode ? rule.special_mode : null;

              if (specialMode === 'gecco_chillbike_combo') {
                  const gecco = (quotas || []).find(q => normalizeQuotaName(q.name || '').includes('geccomobil'));
                  if (!gecco) return null;

                  const chillbike = (quotas || []).find(q => normalizeQuotaName(q.name || '').includes('chillbike'));
                  return {
                      mode: specialMode,
                      geccoId: String(gecco.id || gecco.quota_id),
                      geccoCapacity: parseInt(gecco.ppl || gecco.capacity, 10) || 7,
                      geccoMin: getQuotaMinPeople(gecco),
                      chillbikeId: chillbike ? String(chillbike.id || chillbike.quota_id) : null,
                      geccoStrategy: ['610', '612', '613', '619', '620', '621', '622', '623', '640', '645'].includes(String(state.serviceId))
                          ? 'prefer_gecco_units'
                          : (rule.gecco_strategy || 'default')
                  };
              }

              if (specialMode === 'auto_double_rooms') {
                  const roomQuotas = (quotas || []).filter(q => {
                      const name = normalizeQuotaName(q.name || '');
                      const ppl = parseInt(q.ppl || q.capacity, 10) || 0;
                      return (name.includes('zimmer') || name.includes('turmzimmer')) && ppl === 2;
                  });
                  if (!roomQuotas.length) return null;
                  return {
                      mode: specialMode,
                      roomIds: roomQuotas.map(q => String(q.id || q.quota_id))
                  };
              }

              if (specialMode === 'auto_lowest_room') {
                  const roomQuotas = (quotas || []).filter(q => isRoomLikeQuota(q));
                  if (!roomQuotas.length) return null;
                  return {
                      mode: specialMode,
                      roomIds: roomQuotas.map(q => String(q.id || q.quota_id))
                  };
              }

              const genericRoomQuotas = (quotas || []).filter(q => isRoomLikeQuota(q));
              if (genericRoomQuotas.length && state.apiServiceData && state.apiServiceData.overnight) {
                  return {
                      mode: 'auto_lowest_room',
                      roomIds: genericRoomQuotas.map(q => String(q.id || q.quota_id))
                  };
              }

              return null;
          }

          function getRoomSortValue(q) {
              const rawName = String((q && q.name) || '');
              const match = rawName.match(/(\d+)/);
              if (match) return parseInt(match[1], 10);
              if (normalizeQuotaName(rawName).includes('turmzimmer')) return 9998;
              return 9999;
          }

          function getSpecialSectionMinPax(sk, quotas) {
              const specialRule = getSectionSpecialRule(sk, quotas);
              if (!specialRule || specialRule.mode !== 'gecco_chillbike_combo') return null;
              return specialRule.geccoMin || 5;
          }

          function getGeccoChillbikeRecommendation(specialRule, quotas, totalPax) {
              if (!specialRule || totalPax < 1) return null;

              const geccoQuota = (quotas || []).find(q => String(q.id || q.quota_id) === specialRule.geccoId);
              if (!geccoQuota) return null;

              const geccoCapacity = specialRule.geccoCapacity;
              const geccoMin = specialRule.geccoMin || getQuotaMinPeople(geccoQuota);
              const geccoAvailable = getQuotaAvailability(geccoQuota);
              const geccoStrategy = specialRule.geccoStrategy || 'default';
              const chillbikeQuota = specialRule.chillbikeId
                  ? (quotas || []).find(q => String(q.id || q.quota_id) === specialRule.chillbikeId)
                  : null;
              const chillbikeCapacity = chillbikeQuota ? (parseInt(chillbikeQuota.ppl || chillbikeQuota.capacity, 10) || 2) : 0;
              const chillbikeMin = chillbikeQuota ? getQuotaMinPeople(chillbikeQuota) : 0;
              const chillbikeAvailable = chillbikeQuota ? getQuotaAvailability(chillbikeQuota) : 0;

              const baseGeccoCount = Math.max(1, Math.floor(totalPax / geccoCapacity));
              let geccoCount = Math.min(baseGeccoCount, geccoAvailable);
              let chillbikeCount = 0;
              let remainder = Math.max(0, totalPax - (geccoCount * geccoCapacity));
              const canUseChillbikes = chillbikeQuota && remainder >= chillbikeMin && remainder % chillbikeCapacity === 0 && (remainder / chillbikeCapacity) <= chillbikeAvailable;
              const canUseNextGecco = geccoCount < geccoAvailable && totalPax >= ((geccoCount + 1) * geccoMin) && totalPax <= ((geccoCount + 1) * geccoCapacity);

              if (geccoStrategy === 'prefer_gecco_units' || (geccoStrategy === 'prefer_gecco_from_12' && totalPax >= 12)) {
                  const preferredGeccoCount = Math.ceil(totalPax / geccoCapacity);
                  if (
                      preferredGeccoCount >= 1 &&
                      preferredGeccoCount <= geccoAvailable &&
                      totalPax >= (preferredGeccoCount * geccoMin) &&
                      totalPax <= (preferredGeccoCount * geccoCapacity)
                  ) {
                      return {
                          geccoCount: preferredGeccoCount,
                          chillbikeCount: 0
                      };
                  }
              }

              if (remainder === 0) {
                  chillbikeCount = 0;
              } else if (remainder < geccoMin && canUseChillbikes) {
                  chillbikeCount = remainder / chillbikeCapacity;
              } else if (canUseNextGecco) {
                  geccoCount += 1;
                  chillbikeCount = 0;
              } else if (canUseChillbikes) {
                  chillbikeCount = remainder / chillbikeCapacity;
              } else {
                  for (let extraGecco = geccoCount + 1; extraGecco <= geccoAvailable; extraGecco++) {
                      if (totalPax >= (extraGecco * geccoMin) && totalPax <= (extraGecco * geccoCapacity)) {
                          geccoCount = extraGecco;
                          chillbikeCount = 0;
                          remainder = 0;
                          break;
                      }
                  }
              }

              return {
                  geccoCount,
                  chillbikeCount
              };
          }

          function applySectionSpecialRule(sk, quotas, totalPax) {
              const specialRule = getSectionSpecialRule(sk, quotas);
              if (!specialRule) {
                  return {
                      quotas,
                      lockedQuotaIds: {},
                      specialRule: null,
                      specialDisplayMins: {}
                  };
              }

              state.selectedQuotas[sk] = state.selectedQuotas[sk] || {};
              if (specialRule.mode === 'auto_double_rooms') {
                  const availableRooms = (quotas || []).filter(q =>
                      specialRule.roomIds.includes(String(q.id || q.quota_id)) && getQuotaAvailability(q) > 0
                  );
                  const neededRooms = Math.ceil(totalPax / 2);
                  const selected = {};
                  availableRooms.slice(0, neededRooms).forEach(q => {
                      selected[String(q.id || q.quota_id)] = 1;
                  });
                  state.selectedQuotas[sk] = selected;
                  const selectedIds = Object.keys(selected);
                  const lockedQuotaIds = {};
                  selectedIds.forEach(id => { lockedQuotaIds[id] = true; });
                  return {
                      quotas: availableRooms.filter(q => selectedIds.includes(String(q.id || q.quota_id))),
                      lockedQuotaIds,
                      specialRule,
                      specialDisplayMins: {}
                  };
              }

              if (specialRule.mode === 'auto_lowest_room') {
                  const availableRooms = (quotas || [])
                      .filter(q => specialRule.roomIds.includes(String(q.id || q.quota_id)) && getQuotaAvailability(q) > 0);
                  const selected = {};
                  let remaining = totalPax;
                  const usedRoomIds = {};
                  const sortedRooms = availableRooms.slice().sort((a, b) => {
                      const sortDiff = getRoomSortValue(a) - getRoomSortValue(b);
                      if (sortDiff !== 0) return sortDiff;
                      return String(a.name || '').localeCompare(String(b.name || ''), 'de');
                  });

                  while (remaining > 0) {
                      const nextRoom = sortedRooms.find(q => !usedRoomIds[String(q.id || q.quota_id)]);
                      if (!nextRoom) break;

                      const nextRoomId = String(nextRoom.id || nextRoom.quota_id);
                      const nextCapacity = parseInt(nextRoom.ppl || nextRoom.capacity, 10) || 0;
                      usedRoomIds[nextRoomId] = true;
                      selected[nextRoomId] = 1;
                      remaining = Math.max(0, remaining - nextCapacity);
                  }

                  state.selectedQuotas[sk] = selected;
                  const selectedIds = Object.keys(selected);
                  const lockedQuotaIds = {};
                  selectedIds.forEach(id => { lockedQuotaIds[id] = true; });
                  return {
                      quotas: selectedIds.length
                          ? availableRooms.filter(q => selectedIds.includes(String(q.id || q.quota_id)))
                          : availableRooms,
                      lockedQuotaIds,
                      specialRule,
                      specialDisplayMins: {}
                  };
              }

              const recommendation = getGeccoChillbikeRecommendation(specialRule, quotas, totalPax);
              state.selectedQuotas[sk][specialRule.geccoId] = recommendation ? recommendation.geccoCount : 1;

              if (specialRule.chillbikeId) {
                  state.selectedQuotas[sk][specialRule.chillbikeId] = recommendation ? recommendation.chillbikeCount : 0;
              }

              const visibleQuotaIds = { [specialRule.geccoId]: true };
              if (specialRule.chillbikeId && recommendation && recommendation.chillbikeCount > 0) {
                  visibleQuotaIds[specialRule.chillbikeId] = true;
              }

              return {
                  quotas: quotas.filter(q => visibleQuotaIds[String(q.id || q.quota_id)]),
                  lockedQuotaIds: { [specialRule.geccoId]: true },
                  specialRule,
                  specialDisplayMins: specialRule.chillbikeId ? { [specialRule.chillbikeId]: 2 } : {}
              };
          }

          function getSpecialSectionCoverage(sk, quotas, selectionMap, totalPax) {
              const specialRule = getSectionSpecialRule(sk, quotas);
              if (!specialRule) return null;

              if (specialRule.mode === 'auto_double_rooms' || specialRule.mode === 'auto_lowest_room') {
                  let covered = 0;
                  (quotas || []).forEach(q => {
                      const id = String(q.id || q.quota_id);
                      const count = parseInt((selectionMap || {})[id], 10) || 0;
                      const ppl = parseInt(q.ppl || q.capacity, 10) || 0;
                      covered += count * ppl;
                  });
                  return {
                      covered,
                      minCovered: covered,
                      maxCovered: covered,
                      remaining: Math.max(0, totalPax - covered),
                      isComplete: covered >= totalPax
                  };
              }

              if (specialRule.mode !== 'gecco_chillbike_combo') return null;

              const geccoCount = parseInt((selectionMap || {})[specialRule.geccoId], 10) || 0;
              const chillbikeCount = specialRule.chillbikeId ? (parseInt((selectionMap || {})[specialRule.chillbikeId], 10) || 0) : 0;
              const covered = (geccoCount * specialRule.geccoCapacity) + (chillbikeCount * 2);
              const remaining = Math.max(0, totalPax - covered);

              return {
                  covered,
                  minCovered: covered,
                  maxCovered: covered,
                  remaining,
                  isComplete: covered >= totalPax
              };
          }

          function escapeHtml(value) {
              return String(value || '').replace(/[&<>"']/g, (char) => ({
                  '&': '&amp;',
                  '<': '&lt;',
                  '>': '&gt;',
                  '"': '&quot;',
                  "'": '&#39;'
              }[char]));
          }

          function pluralizeLabel(count, singular, plural) {
              return Number(count) === 1 ? singular : plural;
          }

          function isAutoLockedPerPersonQuota(q, totalPax) {
              if (!q || totalPax < 1) return false;
              const p = parseInt(q.ppl || q.capacity, 10) || 1;
              const m = getQuotaMinPeople(q);
              return p === 1 && m <= 1 && getUnitsNeededForQuota(q, totalPax) === totalPax;
          }

          function isFixedIncludedQuota(q) {
              if (!q) return false;
              return isLiveFireSourceQuota(q) || isBreakfastQuota(q);
          }

          function isBreakfastSectionQuotas(quotas) {
              return (quotas || []).some(q => isBreakfastQuota(q));
          }

          function shouldUseFrontendDayGrouping() {
              return !!(state.apiServiceData && state.apiServiceData.overnight);
          }

          function decorateSectionsWithDayLabels(sections) {
              if (!shouldUseFrontendDayGrouping() || !sections || sections.length < 2) {
                  return (sections || []).map(section => Object.assign({}, section, {
                      dayNumber: null,
                      displayTitle: section.title
                  }));
              }

              const hasBreakfastBreak = sections.some((section, index) =>
                  index > 0 && isBreakfastSectionQuotas(section.quotas)
              );
              if (!hasBreakfastBreak) {
                  return sections.map(section => Object.assign({}, section, {
                      dayNumber: 1,
                      displayTitle: section.title
                  }));
              }

              let currentDay = 1;
              return sections.map((section, index) => {
                  let dayNumber = currentDay;
                  if (index > 0 && isBreakfastSectionQuotas(section.quotas)) {
                      dayNumber = currentDay + 1;
                      currentDay = dayNumber;
                  }

                  return Object.assign({}, section, {
                      dayNumber,
                      displayTitle: `Tag ${dayNumber} · ${section.title}`
                  });
              });
          }

          function getSectionUiState(sk, totalPax) {
              const raw = state.apiQuotaData ? state.apiQuotaData[`${sk}_quotas`] : null;
              if (!raw || (Array.isArray(raw) && raw.length === 0)) return null;

              let quotas = Array.isArray(raw) ? raw : Object.values(raw);
              const hiddenIds = getLinkedHiddenQuotaIdsForSection(sk);
              quotas = quotas.filter(q => !hiddenIds[String(q.id || q.quota_id)]);

              const specialConfig = applySectionSpecialRule(sk, quotas, totalPax);
              quotas = specialConfig.quotas;
              const specialDisplayMins = specialConfig.specialDisplayMins || {};
              const simpleLockedQuotaIds = !specialConfig.specialRule ? getSimpleSectionLockedQuotaIds(quotas, totalPax) : {};
              const lockedQuotaIds = Object.assign({}, specialConfig.lockedQuotaIds || {}, simpleLockedQuotaIds);
              const isInteractive = quotas.some(q => !lockedQuotaIds[String(q.id || q.quota_id)]);

              if (!quotas.length) return null;

              let cap = 0;
              let minCap = 0;
              const selectedParts = [];

              quotas.forEach(q => {
                  const id = q.id || q.quota_id;
                  const count = state.selectedQuotas[sk] && state.selectedQuotas[sk][id] ? state.selectedQuotas[sk][id] : 0;
                  const p = parseInt(q.ppl || q.capacity, 10) || 1;
                  const m = specialDisplayMins[String(id)] || getQuotaMinPeople(q);
                  cap += count * p;
                  minCap += count * m;
                  if (count > 0) {
                      const linkedTarget = getLinkedTargetInfo(sk, id);
                      selectedParts.push(linkedTarget
                          ? `${count}x ${q.name} (${linkedTarget.targetLabel})`
                          : `${count}x ${q.name}`);
                  }
              });

              const specialCoverage = getSpecialSectionCoverage(sk, quotas, state.selectedQuotas[sk] || {}, totalPax);
              const full = specialCoverage ? specialCoverage.isComplete : (minCap <= totalPax && cap >= totalPax);

              return {
                  key: sk,
                  title: getSectionTitle(sk, quotas, isInteractive),
                  quotas,
                  full,
                  covered: specialCoverage ? specialCoverage.covered : cap,
                  selectedParts
              };
          }

          function getSectionUiStates(totalPax) {
              const sections = [];
              for (let i = 1; i <= 15; i++) {
                  const section = getSectionUiState(`s${i}`, totalPax);
                  if (section) sections.push(section);
              }
              return decorateSectionsWithDayLabels(sections);
          }

          function setMobileSummaryOpen(open) {
              state.mobileSummaryOpen = !!open;
              els.mbSheet.classList.toggle('is-open', state.mobileSummaryOpen);
              els.mbSheet.setAttribute('aria-hidden', state.mobileSummaryOpen ? 'false' : 'true');
              els.mbToggleDetails.setAttribute('aria-expanded', state.mobileSummaryOpen ? 'true' : 'false');
              els.mbToggleDetails.textContent = state.mobileSummaryOpen ? 'Details ausblenden' : 'Details anzeigen';
          }

          function isMobileViewport() {
              return window.innerWidth <= 1023;
          }

          function isFooterInView() {
              if (!isMobileViewport()) return false;
              const footer = document.querySelector('#bottom-bar') || document.querySelector('#footer') || document.querySelector('footer.footer');
              if (!footer) return false;
              const rect = footer.getBoundingClientRect();
              const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
              return rect.top < viewportHeight;
          }

          function syncMobileFooterVisibility() {
              if (!els.mbBar) return;
              const footerVisible = isFooterInView();
              els.mbBar.classList.toggle('is-footer-hidden', footerVisible);
              if (footerVisible) {
                  setMobileSummaryOpen(false);
              }
          }

          function isConfiguratorEntryVisible() {
              const wrapper = document.getElementById('nkombi-wrapper');
              if (!wrapper) return true;
              const rect = wrapper.getBoundingClientRect();
              const viewportHeight = window.innerHeight || document.documentElement.clientHeight || 0;
              const triggerOffset = Math.min(140, Math.max(80, Math.round(viewportHeight * 0.18)));
              return rect.top <= triggerOffset;
          }

          function shouldUseMobileBookNowMode() {
              return isMobileViewport() && state.step === 1 && !isConfiguratorEntryVisible();
          }

          function scrollToConfigurator() {
              const wrapper = document.getElementById('nkombi-wrapper');
              if (!wrapper) return;
              const top = wrapper.getBoundingClientRect().top + window.scrollY - 90;
              window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
          }

          function syncMobileStepOneButton(paxOk, detailsReady) {
              const bookNowMode = shouldUseMobileBookNowMode();

              if (bookNowMode) {
                  els.mbBtnAction.textContent = "Jetzt buchen";
                  els.mbBtnAction.classList.remove('checkout-mode', 'disabled');
                  els.mbBtnAction.disabled = false;
                  return true;
              }

              els.mbBtnAction.textContent = "Ausrüstung wählen";
              els.mbBtnAction.classList.remove('checkout-mode');
              els.mbBtnAction.disabled = !paxOk || !detailsReady;
              els.mbBtnAction.classList.toggle('disabled', !paxOk || !detailsReady);
              return false;
          }

          function requestMobileStepOneRefresh() {
              if (state.step !== 1) return;
              const total = state.adults + state.children + state.babies;
              const requiredMin = window.getRequiredMinPax();
              const paxOk = total >= requiredMin;
              const detailsReady = paxOk && !!state.date && !!state.time;
              syncMobileStepOneButton(paxOk, detailsReady);
          }

          function normalizeTimeValue(value) {
              const val = String(value || '').trim();
              if (!val) return '';
              const match = val.match(/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/);
              if (!match) return '';
              return `${match[1].padStart(2, '0')}:${match[2]}:${(match[3] || '00').padStart(2, '0')}`;
          }

          function getSectionTitle(sk, quotas, isInteractive = true) {
              const names = (quotas || []).map(q => normalizeQuotaName(q.name || ''));
              if (!names.length) return 'Baustein';
              if (names.some(name => name.includes('frühstück') || name.includes('fruhstuck'))) return 'Frühstück';
              if (names.some(name => name.includes('lunchpaket'))) return 'Lunchpaket';
              if (names.some(name => name.includes('vitaminkorb'))) return 'Vitaminkorb';
              if (names.some(name => name.includes('fahrrad')) && names.every(name => name.includes('fahrrad'))) return isInteractive ? 'Fahrräder wählen' : 'Fahrräder';
              if (names.some(name => name.includes('geccomobil') || name.includes('chillbike'))) return isInteractive ? 'Fahrzeuge wählen' : 'Fahrzeuge';
              if (names.some(name => name.includes('kajak') || name.includes('kanadier') || name.includes('schlauchboot') || name.includes('floß') || name.includes('floss'))) return isInteractive ? 'Boote auswählen' : 'Boote';
              if (names.some(name => name.includes('zimmer') || name.includes('tipi') || name.includes('turmzimmer'))) return isInteractive ? 'Übernachtung wählen' : 'Übernachtung';
              if (names.some(name => name.includes('live-fire') || name.includes('live fire') || name.includes('yakaris') || name.includes('rittersaal'))) return isInteractive ? 'Verpflegung wählen' : 'Verpflegung';
              if (quotas.length === 1) return quotas[0].name || 'Baustein';
              return isInteractive ? 'Baustein wählen' : 'Baustein';
          }

          function getServiceQuotaLinkRules() {
              const rules = [];
              if (!state.apiQuotaData) return rules;

              const sections = [];
              for (let i = 1; i <= 15; i++) {
                  const sk = `s${i}`;
                  const raw = state.apiQuotaData[`${sk}_quotas`];
                  if (!raw || (Array.isArray(raw) && raw.length === 0)) continue;
                  const quotas = Array.isArray(raw) ? raw : Object.values(raw);
                  sections.push({ sk, quotas });
              }

              const allQuotas = sections.flatMap(section =>
                  section.quotas.map(q => ({ sk: section.sk, quota: q }))
              );

              const liveFireSource = allQuotas.find(entry => isLiveFireSourceQuota(entry.quota));
              const yakariTarget = allQuotas.find(entry => isYakariTargetQuota(entry.quota));
              if (liveFireSource && yakariTarget) {
                  rules.push({
                      sourceSection: liveFireSource.sk,
                      sourceId: String(liveFireSource.quota.id || liveFireSource.quota.quota_id),
                      sourceLabel: liveFireSource.quota.name || 'Live Fire Cooking',
                      targetSection: yakariTarget.sk,
                      targetId: String(yakariTarget.quota.id || yakariTarget.quota.quota_id),
                      targetLabel: yakariTarget.quota.name || 'Yakaris Quelle'
                  });
              }

              const breakfastSource = allQuotas.find(entry => isBreakfastSourceQuota(entry.quota));
              const rittersaalTarget = allQuotas.find(entry => isRittersaalTargetQuota(entry.quota));
              if (breakfastSource && rittersaalTarget) {
                  rules.push({
                      sourceSection: breakfastSource.sk,
                      sourceId: String(breakfastSource.quota.id || breakfastSource.quota.quota_id),
                      sourceLabel: breakfastSource.quota.name || 'Frühstück',
                      targetSection: rittersaalTarget.sk,
                      targetId: String(rittersaalTarget.quota.id || rittersaalTarget.quota.quota_id),
                      targetLabel: rittersaalTarget.quota.name || 'Rittersaal'
                  });
              }

              sections.forEach(section => {
                  const transportQuota = section.quotas.find(q => isBikeTransportQuota(q));
                  const boatQuotas = section.quotas.filter(q => isBoatQuota(q));
                  if (!transportQuota || !boatQuotas.length) return;

                  rules.push({
                      sourceSection: section.sk,
                      sourceIds: boatQuotas.map(q => String(q.id || q.quota_id)),
                      sourceLabel: 'Bootsauswahl',
                      targetSection: section.sk,
                      targetId: String(transportQuota.id || transportQuota.quota_id),
                      targetLabel: transportQuota.name || 'Fahrradtransport',
                      countMode: 'totalPax',
                      hideOnly: true
                  });
              });

              return rules;
          }

          function getLinkedHiddenQuotaIdsForSection(sk) {
              const hiddenIds = {};
              getServiceQuotaLinkRules().forEach(rule => {
                  if (rule.targetSection === sk) hiddenIds[rule.targetId] = true;
              });
              return hiddenIds;
          }

          function getLinkedHiddenSelectionsBySection() {
              const hiddenSelections = {};
              getServiceQuotaLinkRules().forEach(rule => {
                  let sourceCount = 0;
                  if (Array.isArray(rule.sourceIds) && rule.sourceIds.length) {
                      sourceCount = rule.sourceIds.reduce((sum, id) => {
                          return sum + (parseInt(((state.selectedQuotas[rule.sourceSection] || {})[id]), 10) || 0);
                      }, 0);
                  } else {
                      sourceCount = parseInt(((state.selectedQuotas[rule.sourceSection] || {})[rule.sourceId]), 10) || 0;
                  }

                  if (rule.hideOnly) return;

                  if (sourceCount > 0) {
                      if (!hiddenSelections[rule.targetSection]) hiddenSelections[rule.targetSection] = {};
                      hiddenSelections[rule.targetSection][rule.targetId] = rule.countMode === 'totalPax'
                          ? (state.adults + state.children + state.babies)
                          : sourceCount;
                  }
              });
              return hiddenSelections;
          }

          function getLinkedTargetInfo(sk, quotaId) {
              const rule = getServiceQuotaLinkRules().find(linkRule =>
                  !linkRule.hideOnly &&
                  linkRule.sourceSection === sk && (
                      linkRule.sourceId === String(quotaId) ||
                      (Array.isArray(linkRule.sourceIds) && linkRule.sourceIds.includes(String(quotaId)))
                  )
              );
              return rule ? { targetLabel: rule.targetLabel, targetId: rule.targetId } : null;
          }

          function deriveLiveMinPax(quotaData) {
              if (!quotaData) return null;

              let minRequired = 1;
              for (let i = 1; i <= 15; i++) {
                  const raw = quotaData[`s${i}_quotas`];
                  if (!raw || (Array.isArray(raw) && raw.length === 0)) continue;

                  const qs = Array.isArray(raw) ? raw : Object.values(raw);
                  if (!qs.length) continue;

                  const specialMin = getSpecialSectionMinPax(`s${i}`, qs);
                  if (specialMin !== null) {
                      minRequired = Math.max(minRequired, specialMin);
                      continue;
                  }

                  const visible = qs.filter(q =>
                      !isLiveFireSourceQuota(q) &&
                      !isYakariTargetQuota(q) &&
                      !isRittersaalTargetQuota(q) &&
                      !isBikeTransportQuota(q)
                  );
                  const relevant = visible.length ? visible : qs;
                  const sectionMin = relevant.reduce((min, q) => Math.min(min, getQuotaMinPeople(q)), Number.POSITIVE_INFINITY);

                  if (Number.isFinite(sectionMin)) minRequired = Math.max(minRequired, sectionMin);
              }

              return minRequired;
          }

          async function prefetchQuotaConstraints() {
              if (!state.date || !state.time) return;

              const requestKey = `${state.date} ${state.time}`;
              const token = ++state.quotaPrefetchToken;
              const fd = new FormData();
              fd.append('service_id', state.serviceId);
              fd.append('date', state.date);
              fd.append('time', state.time);
              if (state.apiServiceData && state.apiServiceData.overnight_stays) {
                  fd.append('overnight_stays', state.apiServiceData.overnight_stays);
              }
              if (state.apiRequestToken) {
                  fd.append('request', state.apiRequestToken);
              }

              try {
                  const res = await fetch(state.quotaDataEndpoint, { method: 'POST', body: fd });
                  const json = await res.json();
                  if (token !== state.quotaPrefetchToken || !json.status || !json.data) return;

                  state.prefetchedQuotaData = json.data;
                  state.prefetchedQuotaKey = requestKey;
                  state.liveMinPax = deriveLiveMinPax(json.data);

                  const totalPax = state.adults + state.children + state.babies;
                  const requiredMin = window.getRequiredMinPax();
                  if (totalPax < requiredMin) {
                      state.adults += (requiredMin - totalPax);
                      els.vA.value = state.adults;
                  }

                  refreshDebugPanel();

                  updateView();
              } catch (e) {
                  console.error(e);
              }
          }

          function getLinkedServiceChoices() {
              const rule = getCustomRule();
              const authoritativeChoices = Array.isArray(rule.linked_services) ? rule.linked_services : [];
              if (authoritativeChoices.length > 1) {
                  state.linkedServiceChoicesMemory = authoritativeChoices.slice();
                  return authoritativeChoices;
              }

              if (authoritativeChoices.length === 1 && state.linkedServiceChoicesMemory.length > 1) {
                  const hasCurrentService = state.linkedServiceChoicesMemory.some(choice => String(choice.id) === String(state.serviceId));
                  if (hasCurrentService) {
                      return state.linkedServiceChoicesMemory.slice();
                  }
              }

              if (!authoritativeChoices.length && state.linkedServiceChoicesMemory.length > 1) {
                  const hasCurrentService = state.linkedServiceChoicesMemory.some(choice => String(choice.id) === String(state.serviceId));
                  if (hasCurrentService) {
                      return state.linkedServiceChoicesMemory.slice();
                  }
              }

              return authoritativeChoices;
          }

          function hasUsableVariantPrice(rawPrice) {
              const numericPrice = parseFloat(rawPrice);
              return Number.isFinite(numericPrice) && numericPrice > 0;
          }

          function formatPerPersonPriceLabel(rawPrice) {
              const numericPrice = parseFloat(rawPrice);
              if (!hasUsableVariantPrice(numericPrice)) return '';
              return ` (${numericPrice.toLocaleString('de-DE', { style: 'currency', currency: 'EUR' })} p.P.)`;
          }

          async function hydrateLinkedServicePrices() {
              const choices = getLinkedServiceChoices();
              if (!choices.length) return;

              const missingIds = choices
                  .map(choice => String(parseInt(choice.id, 10) || ''))
                  .filter(id => {
                      if (!id) return false;
                      const meta = state.linkedServicePriceMap && state.linkedServicePriceMap[String(id)] ? state.linkedServicePriceMap[String(id)] : null;
                      return !hasUsableVariantPrice(meta ? meta.adult_price_brutto : null) && !state.linkedServicePriceFetched[String(id)];
                  });

              if (!missingIds.length) return;

              await Promise.all(missingIds.map(async (id) => {
                  state.linkedServicePriceFetched[String(id)] = true;
                  try {
                      const res = await fetch(buildServiceDataUrl(id));
                      const json = await res.json();
                      if (!json || !json.status || !json.data) return;

                      state.linkedServicePriceMap[String(id)] = Object.assign({}, state.linkedServicePriceMap[String(id)] || {}, {
                          adult_price_brutto: json.data.adult_price_brutto,
                          name: json.data.name || ((state.linkedServicePriceMap[String(id)] && state.linkedServicePriceMap[String(id)].name) ? state.linkedServicePriceMap[String(id)].name : '')
                      });
                  } catch (e) {
                      console.warn('Variant price hydration failed for service', id, e);
                  }
              }));

              renderLinkedServiceChoices();
          }

          function renderLinkedServiceChoices() {
              const choices = getLinkedServiceChoices();
              if (!choices.length) {
                  els.linkedServiceWrap.style.display = 'none';
                  els.linkedService.innerHTML = '';
                  return;
              }

              els.linkedService.innerHTML = '';
              choices.forEach(choice => {
                  const id = parseInt(choice.id, 10);
                  if (!id) return;
                  const label = choice.label || `Variante ${id}`;
                  const priceMeta = state.linkedServicePriceMap && state.linkedServicePriceMap[String(id)] ? state.linkedServicePriceMap[String(id)] : null;
                  const optionLabel = `${label}${formatPerPersonPriceLabel(priceMeta ? priceMeta.adult_price_brutto : null)}`;
                  els.linkedService.appendChild(new Option(optionLabel, String(id)));
              });
              els.linkedService.value = String(state.serviceId);
              els.linkedServiceWrap.style.display = 'block';
          }

          async function loadServiceData() {
              try {
                  let res = await fetch(buildServiceDataUrl(state.serviceId));
                  let json = await res.json();
                  if(json.status) {
                      state.apiServiceData = json.data;
                      state.apiRequestToken = json.request || '';
                      state.linkedServicePriceMap[String(state.serviceId)] = Object.assign({}, state.linkedServicePriceMap[String(state.serviceId)] || {}, {
                          adult_price_brutto: json.data.adult_price_brutto,
                          name: json.data.name || ((state.linkedServicePriceMap[String(state.serviceId)] && state.linkedServicePriceMap[String(state.serviceId)].name) ? state.linkedServicePriceMap[String(state.serviceId)].name : '')
                      });
                      document.getElementById('nc-tour-name').textContent = state.apiServiceData.name;
                      els.sbTourName.textContent = state.apiServiceData.name;
                      els.mbSheetTour.textContent = state.apiServiceData.name;
                      
                      let reqMin = window.getRequiredMinPax();
                      if (reqMin > state.adults) {
                          state.adults = reqMin;
                          els.vA.value = state.adults;
                      }
                  }
              } catch(e) { console.error(e); }
          }

          async function init() {
              relaxAncestorOverflow();
              setupStickySidebarFallback();

              if (els.debugBtn && els.debugOut) {
                   els.debugBtn.onclick = (e) => {
                       e.preventDefault();
                       if(els.debugOut.style.display === 'none') {
                           els.debugOut.style.display = 'block';
                           refreshDebugPanel();
                       } else {
                           els.debugOut.style.display = 'none';
                       }
                   };
              }

              await loadServiceData();
              renderLinkedServiceChoices();
              hydrateLinkedServicePrices();

              const sStart = parseSeasonLimit(state.seasonStartStr);
              const sEnd = parseSeasonLimit(state.seasonEndStr);

              state.datePicker = flatpickr(els.date, { 
                  locale: "de", dateFormat: "Y-m-d", altInput: true, altFormat: "d.m.Y",
                  minDate: getTomorrowDate(),
                  disable: [
                      function(date) {
                          // Falls Backend-Limits gesetzt sind, deaktiviere alle Daten außerhalb der Saison
                          if (state.applySeasonLimits && sStart !== null && sEnd !== null) return !isDateWithinSeason(date);
                          return false;
                      }
                  ],
                  onChange: (s, d) => { state.date = d; state.time = ""; updateTimeOptions(); updateView(); }
              });

              const initialDate = findNextBookableDate();
              state.datePicker.setDate(initialDate, true);

              document.getElementById('btn-adults-minus').onclick = () => { if(state.adults > 1) { state.adults--; els.vA.value = state.adults; paxUpdate(); } };
              document.getElementById('btn-adults-plus').onclick = () => { state.adults++; els.vA.value = state.adults; paxUpdate(); };
              document.getElementById('btn-children-minus').onclick = () => { if(state.children > 0) { state.children--; els.vC.value = state.children; paxUpdate(); } };
              document.getElementById('btn-children-plus').onclick = () => { state.children++; els.vC.value = state.children; paxUpdate(); };
              document.getElementById('btn-babies-minus').onclick = () => { if(state.babies > 0) { state.babies--; els.vB.value = state.babies; paxUpdate(); } };
              document.getElementById('btn-babies-plus').onclick = () => { state.babies++; els.vB.value = state.babies; paxUpdate(); };
              
              els.time.onchange = (e) => { state.time = e.target.value; prefetchQuotaConstraints(); updateView(); };
              document.getElementById('btn-back-1').onclick = () => goToStep(1);
              els.linkedService.onchange = async (e) => {
                  const nextId = parseInt(e.target.value, 10);
                  if (!nextId || nextId === parseInt(state.serviceId, 10)) return;
                  state.serviceId = nextId;
                  state.apiQuotaData = null;
                  state.prefetchedQuotaData = null;
                  state.prefetchedQuotaKey = "";
                  state.selectedQuotas = {};
                  state.hiddenQuotaSelections = {};
                  state.hiddenQuotaIds = {};
                  await loadServiceData();
                  renderLinkedServiceChoices();
                  hydrateLinkedServicePrices();
                  updateTimeOptions();
                  updateView();
                  if (state.step === 2) fetchLiveQuotas();
              };
              
              els.sbBtnAction.onclick = handleAction;
              els.mbBtnAction.onclick = handleAction;
              document.getElementById('mb-btn-step1').onclick = handleAction;
              els.mbToggleDetails.onclick = () => setMobileSummaryOpen(!state.mobileSummaryOpen);
              els.mbCloseDetails.onclick = () => setMobileSummaryOpen(false);
              let mobileRefreshRaf = null;
              const requestMobileRefresh = () => {
                  if (mobileRefreshRaf) cancelAnimationFrame(mobileRefreshRaf);
                  mobileRefreshRaf = requestAnimationFrame(requestMobileStepOneRefresh);
              };
              window.addEventListener('scroll', requestMobileRefresh, { passive: true });
              window.addEventListener('scroll', syncMobileFooterVisibility, { passive: true });
              window.addEventListener('resize', syncMobileFooterVisibility);
              syncMobileFooterVisibility();
              window.addEventListener('resize', requestMobileRefresh);
              
              updateView();
          }

          function paxUpdate() {
              updateView();
              if(state.step === 2) renderSections();
          }

          function updateTimeOptions() {
              els.time.innerHTML = '<option value="">Zeit wählen...</option>';
              if(!state.date || !state.apiServiceData) { els.time.disabled = true; return; }
              els.time.disabled = false;
              const timeOptions = (Array.isArray(state.manualTimeOptions) && state.manualTimeOptions.length
                  ? state.manualTimeOptions
                  : [state.apiServiceData.begin_time]
              ).map(normalizeTimeValue).filter(Boolean);

              if(timeOptions.length) {
                  timeOptions.forEach(timeValue => {
                      els.time.appendChild(new Option(timeValue.substring(0,5) + " Uhr", timeValue));
                  });
                  state.time = timeOptions[0];
                  els.time.value = timeOptions[0];
                  prefetchQuotaConstraints();
              }
          }

          function goToStep(s) {
              state.step = s;
              if (s !== 2) setMobileSummaryOpen(false);
              [els.p1, els.p2].forEach(p => p.classList.remove('active'));
              [els.st1, els.st2].forEach(st => st.classList.remove('active'));
              document.getElementById('panel-'+s).classList.add('active');
              document.getElementById('st-'+s).classList.add('active');
              updateView();
              window.scrollTo({top: document.getElementById('nkombi-wrapper').getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth'});
          }

          async function fetchLiveQuotas() {
              document.getElementById('nc-loading-quotas').style.display = 'block';
              els.sectionsContainer.innerHTML = '';
              try {
                  const requestKey = `${state.date} ${state.time}`;
                  if (state.prefetchedQuotaData && state.prefetchedQuotaKey === requestKey) {
                      state.apiQuotaData = state.prefetchedQuotaData;
                  } else {
                      let fd = new FormData();
                      fd.append('service_id', state.serviceId);
                      fd.append('date', state.date);
                      fd.append('time', state.time);
                      if (state.apiServiceData && state.apiServiceData.overnight_stays) {
                          fd.append('overnight_stays', state.apiServiceData.overnight_stays);
                      }
                      if (state.apiRequestToken) {
                          fd.append('request', state.apiRequestToken);
                      }
                      let res = await fetch(state.quotaDataEndpoint, {method:'POST', body:fd});
                      let json = await res.json();
                      state.apiQuotaData = json.data;
                  }
                  state.selectedQuotas = {};
                  state.autoFilledSections = {};
                  state.hiddenQuotaSelections = {};
                  state.hiddenQuotaIds = {};
                  renderSections();
                  updateView();
                  refreshDebugPanel();
                  
                  if (els.debugBtn && els.debugOut && els.debugOut.style.display === 'block') {
                      els.debugBtn.click();
                      els.debugBtn.click();
                  }
              } catch(e) { console.error(e); }
              document.getElementById('nc-loading-quotas').style.display = 'none';
          }

          function tryAutofillSimpleSection(sk, qs, totalPax) {
              if (!qs || qs.length !== 1 || totalPax < 1) return;

              const q = qs[0];
              const id = q.id || q.quota_id;
              const hasManualSelection = Object.values(state.selectedQuotas[sk] || {}).some(v => (parseInt(v, 10) || 0) > 0);
              const unitsNeeded = getSectionQuotaType(sk) === '2'
                  ? (isBikeRentalQuota(q) ? getUnitsNeededForQuota(q, totalPax) : (getQuotaAvailability(q) > 0 ? 1 : 0))
                  : getUnitsNeededForQuota(q, totalPax);

              if (unitsNeeded > 0 && (!hasManualSelection || state.autoFilledSections[sk])) {
                  state.selectedQuotas[sk] = { [id]: unitsNeeded };
                  state.autoFilledSections[sk] = true;
              }
          }

          function tryAutofillSingleViableChoice(sk, qs, totalPax, lockedQuotaIds, specialDisplayMins) {
              if (!qs || !qs.length || totalPax < 1) return false;

              const viableQuotas = qs.filter(q => {
                  const id = String(q.id || q.quota_id);
                  if (lockedQuotaIds && lockedQuotaIds[id]) return false;
                  if (getQuotaAvailability(q) < 1) return false;

                  const nextSelection = { [id]: 1 };
                  const p = parseInt(q.ppl || q.capacity, 10) || 1;
                  const m = (specialDisplayMins && specialDisplayMins[id]) || getQuotaMinPeople(q);
                  const unitsNeeded = getUnitsNeededForQuota(q, totalPax);
                  if (unitsNeeded < 1) return false;

                  const minCovered = unitsNeeded * m;
                  const maxCovered = unitsNeeded * p;
                  return minCovered <= totalPax && maxCovered >= totalPax;
              });

              if (viableQuotas.length !== 1) return false;

              const targetQuota = viableQuotas[0];
              const targetId = String(targetQuota.id || targetQuota.quota_id);
              const unitsNeeded = getUnitsNeededForQuota(targetQuota, totalPax);
              if (unitsNeeded < 1) return false;

              const hasManualSelection = Object.entries(state.selectedQuotas[sk] || {}).some(([id, value]) => {
                  if (String(id) === targetId) return false;
                  return (parseInt(value, 10) || 0) > 0;
              });

              if (hasManualSelection && !state.autoFilledSections[sk]) return false;

              state.selectedQuotas[sk] = Object.assign({}, state.selectedQuotas[sk], { [targetId]: unitsNeeded });
              state.autoFilledSections[sk] = true;
              return true;
          }

          function getFixedIncludedQuota(qs) {
              const fixedQuotas = (qs || []).filter(q => isFixedIncludedQuota(q) && getQuotaAvailability(q) > 0);
              return fixedQuotas.length === 1 ? fixedQuotas[0] : null;
          }

          function normalizeFixedIncludedSectionQuotas(qs) {
              if (!qs || !qs.length) return qs;

              const fixedIncludedQuota = getFixedIncludedQuota(qs);
              if (!fixedIncludedQuota) return qs;

              const fixedId = String(fixedIncludedQuota.id || fixedIncludedQuota.quota_id);
              return qs.filter(q => {
                  const id = String(q.id || q.quota_id);
                  if (id === fixedId) return true;
                  return getQuotaAvailability(q) > 0;
              });
          }

          function tryAutofillFixedIncludedSection(sk, qs, totalPax) {
              if (!qs || totalPax < 1) return false;

              const q = getFixedIncludedQuota(qs);
              if (!q) return false;

              const id = q.id || q.quota_id;
              const hasManualSelection = Object.values(state.selectedQuotas[sk] || {}).some(v => (parseInt(v, 10) || 0) > 0);
              const unitsNeeded = getSectionQuotaType(sk) === '2'
                  ? (getQuotaAvailability(q) > 0 ? 1 : 0)
                  : getUnitsNeededForQuota(q, totalPax);
              if (unitsNeeded < 1) return false;

              if (!hasManualSelection || state.autoFilledSections[sk]) {
                  state.selectedQuotas[sk] = { [id]: unitsNeeded };
                  state.autoFilledSections[sk] = true;
              }

              return true;
          }

          function getSimpleSectionLockedQuotaIds(qs, totalPax) {
              if (!qs || qs.length < 1) return {};

              const fixedIncludedQuota = getFixedIncludedQuota(qs);
              if (fixedIncludedQuota) {
                  return { [String(fixedIncludedQuota.id || fixedIncludedQuota.quota_id)]: true };
              }

              if (qs.length !== 1) return {};
              const q = qs[0];
              const id = String(q.id || q.quota_id);
              if (!isAutoLockedPerPersonQuota(q, totalPax)) return {};
              return { [id]: true };
          }

          function renderSections() {
              const totalPax = state.adults + state.children + state.babies;
              els.sectionsContainer.innerHTML = '';
              let sCount = 1;
              let displayDay = 1;
              let renderedSections = 0;
              const useDayGrouping = shouldUseFrontendDayGrouping();

              for(let i=1; i<=15; i++) {
                  let sk = `s${i}`;
                  let raw = state.apiQuotaData ? state.apiQuotaData[`${sk}_quotas`] : null;
                  if(!raw || (Array.isArray(raw) && raw.length === 0)) continue;
                  if(!state.selectedQuotas[sk]) state.selectedQuotas[sk] = {};
                  let qs = Array.isArray(raw) ? raw : Object.values(raw);
                  const hiddenIds = getLinkedHiddenQuotaIdsForSection(sk);
                  state.hiddenQuotaIds[sk] = hiddenIds;
                  qs = qs.filter(q => !hiddenIds[String(q.id || q.quota_id)]);

                  const specialConfig = applySectionSpecialRule(sk, qs, totalPax);
                  qs = specialConfig.quotas;
                  qs = normalizeFixedIncludedSectionQuotas(qs);
                  const simpleLockedQuotaIds = !specialConfig.specialRule ? getSimpleSectionLockedQuotaIds(qs, totalPax) : {};
                  const lockedQuotaIds = Object.assign({}, specialConfig.lockedQuotaIds || {}, simpleLockedQuotaIds);
                  const specialDisplayMins = specialConfig.specialDisplayMins || {};

                  if(qs.length === 0) continue;

                  if (!specialConfig.specialRule) {
                      tryAutofillFixedIncludedSection(sk, qs, totalPax);
                      tryAutofillSimpleSection(sk, qs, totalPax);
                  }

                  tryAutofillSingleViableChoice(sk, qs, totalPax, lockedQuotaIds, specialDisplayMins);

                  let cap = 0, minCap = 0;
                  qs.forEach(q => {
                      const id = q.id || q.quota_id;
                      const availableUnits = getQuotaAvailability(q);
                      const nextCount = Math.min(state.selectedQuotas[sk][id] || 0, availableUnits);
                      state.selectedQuotas[sk][id] = nextCount;
                      const count = nextCount;
                      const p = parseInt(q.ppl || q.capacity) || 1;
                      const m = specialDisplayMins[String(id)] || getQuotaMinPeople(q);
                      cap += count * p; minCap += count * m;
                  });

                  const specialCoverage = getSpecialSectionCoverage(sk, qs, state.selectedQuotas[sk], totalPax);
                  if (!specialCoverage && !sectionSelectionMatchesTotal(state.selectedQuotas[sk], qs, totalPax) && minCap > totalPax) {
                      state.selectedQuotas[sk] = {};
                      cap = 0;
                      minCap = 0;
                  }

                  const full = specialCoverage ? specialCoverage.isComplete : (minCap <= totalPax && cap >= totalPax);
                  const card = document.createElement('div');
                  card.className = `nc-section-card ${full ? 'is-full' : ''}`;
                  
                  const isInteractiveTitle = qs.some(q => !lockedQuotaIds[String(q.id || q.quota_id)]);
                  const baseSectionTitle = getSectionTitle(sk, qs, isInteractiveTitle);
                  const isBreakfastDisplaySection = isBreakfastSectionQuotas(qs);
                  let sectionDay = displayDay;
                  if (useDayGrouping && renderedSections > 0 && isBreakfastDisplaySection) {
                      sectionDay = displayDay + 1;
                      displayDay = sectionDay;
                  }
                  const sectionTitle = useDayGrouping ? `Tag ${sectionDay} · ${baseSectionTitle}` : baseSectionTitle;
                  let html = `<div class="nc-section-header">
                      <div class="nc-section-badge">${sCount}</div>
                      <strong style="color:#1f2937">${sectionTitle}</strong>
                  </div>`;

                  let items = 0;
                  qs.forEach(q => {
                      const id = q.id || q.quota_id;
                      const count = state.selectedQuotas[sk][id] || 0;
                      const p = parseInt(q.ppl || q.capacity) || 1;
                      const m = specialDisplayMins[String(id)] || getQuotaMinPeople(q);
                      const availableUnits = getQuotaAvailability(q);
                      
                      const nextSelection = Object.assign({}, state.selectedQuotas[sk], { [id]: count + 1 });
                      const specialNextCoverage = getSpecialSectionCoverage(sk, qs, nextSelection, totalPax);
                      const nextCoverage = specialNextCoverage || getSectionCoverage(nextSelection, qs);
                      const tooStrict = specialNextCoverage ? (nextCoverage.covered > totalPax) : (nextCoverage.minCovered > totalPax);
                      const unavailable = availableUnits < 1;
                      const isLocked = !!lockedQuotaIds[String(id)];
                      
                      if (count === 0 && full) return;
                      if(count === 0 && !full && (tooStrict || unavailable)) return;

                      items++;
                      const active = count > 0;
                      const linkedTarget = getLinkedTargetInfo(sk, id);
                      const linkedMeta = linkedTarget
                          ? `<div style="font-size:12px; color:var(--nkombi-primary); margin-top:4px; font-weight:600;">Ort: ${escapeHtml(linkedTarget.targetLabel)}</div>`
                          : '';
                      
                      html += `<div class="nc-quota-item ${active?'is-active':''}">
                          <div style="font-size:13px; display:flex; align-items:center;">
                            ${getIcon(q.name || '')}
                            <div>
                                <div style="font-weight:700; color:${active?'#065f46':'#1f2937'}; font-size:14px; margin-bottom:2px;">${q.name}</div>
                                <div style="font-size:12px; color:#6b7280;">(${p} ${pluralizeLabel(p, 'Platz', 'Plätze')} | Min. ${m} Pers. | ${availableUnits}x verfügbar)</div>
                                ${linkedMeta}
                            </div>
                          </div>
                          <div class="nc-boat-counter">
                            <button class="nc-btn-icon" onclick="window.updQ('${sk}','${id}',-1)" ${ isLocked ? 'disabled' : '' }>${iconMinus}</button>
                            <span>${count}</span>
                            <button class="nc-btn-icon" onclick="window.updQ('${sk}','${id}',1)" ${ (isLocked || full || tooStrict || count >= availableUnits) ? 'disabled' : '' }>${iconPlus}</button>
                          </div>
                      </div>`;
                  });

                  if(items === 0 && !full) html += `<div style="text-align:center; padding:10px; color:#9ca3af; font-size:13px;">Für diese Gruppengröße ist hier keine Ausrüstung verfügbar.</div>`;
                  
                  html += full ? `<div style="margin-top:10px; background:var(--nkombi-primary-soft); color:var(--nkombi-primary-deep); padding:10px; border-radius:8px; text-align:center; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:8px;">${iconCheck} Baustein gewählt</div>`
                               : `<div style="margin-top:10px; text-align:center; font-size:13px; font-weight:700;">${pluralizeLabel(totalPax, 'Platz', 'Plätze')}: <span style="color:#dc2626">${specialCoverage ? specialCoverage.covered : cap}</span> / ${totalPax}</div>`;

                  card.innerHTML = html;
                  els.sectionsContainer.appendChild(card);
                  sCount++;
                  renderedSections++;
              }

              state.hiddenQuotaSelections = getLinkedHiddenSelectionsBySection();
          }

          window.updQ = (sk, id, d) => {
              const raw = state.apiQuotaData ? state.apiQuotaData[`${sk}_quotas`] : null;
              const qs = raw ? (Array.isArray(raw) ? raw : Object.values(raw)) : [];
              const specialRule = getSectionSpecialRule(sk, qs);
              if (specialRule && String(id) === specialRule.geccoId) return;

              state.autoFilledSections[sk] = false;
              state.selectedQuotas[sk][id] = Math.max(0, (state.selectedQuotas[sk][id]||0) + d);
               renderSections();
               updateView();
               refreshDebugPanel();
           };

          function updateView() {
              const total = state.adults + state.children + state.babies;
              let price = 0;
              if (state.apiServiceData) {
                  const a = parseFloat(state.apiServiceData.adult_price_brutto) || 0;
                  const c = Number.isFinite(parseFloat(state.apiServiceData.child_price_brutto)) ? parseFloat(state.apiServiceData.child_price_brutto) : 0;
                  const b = Number.isFinite(parseFloat(state.apiServiceData.baby_price_brutto)) ? parseFloat(state.apiServiceData.baby_price_brutto) : 0;
                  price = (state.adults * a) + (state.children * c) + (state.babies * b);
              }
              
              const pStr = price.toLocaleString('de-DE', {style:'currency', currency:'EUR'});
              document.getElementById('sb-price').textContent = pStr;
              els.mbPrice.textContent = pStr;
              els.mbSheetPrice.textContent = pStr;
              
              const ppText = total > 0
                  ? `(${(price/total).toLocaleString('de-DE', {style:'currency', currency:'EUR'})} p.P.)`
                  : '';
              document.getElementById('sb-price-pp').textContent = ppText;
              els.mbSheetPricePp.textContent = ppText;
              syncMobileFooterVisibility();

              let dateText = 'Bitte wählen';
              if(state.date) {
                  let dStr = state.date.split('-').reverse().join('.');
                  dateText = `${dStr} | ${state.time ? state.time.substring(0,5) + ' Uhr' : 'Zeit wählen'} | ${total} Pers.`;
              }
              els.sbDate.textContent = dateText;
              els.mbSheetDate.textContent = dateText;

              let requiredMin = window.getRequiredMinPax();
              let paxOk = (total >= requiredMin);
              let detailsReady = paxOk && !!state.date && !!state.time;
              let btnStep1 = document.getElementById('mb-btn-step1');
              const sections = state.step === 2 && state.apiQuotaData ? getSectionUiStates(total) : [];
              const sectionsDone = sections.filter(section => section.full).length;
              const sectionsTotal = sections.length;
              const progressPercent = sectionsTotal > 0 ? Math.round((sectionsDone / sectionsTotal) * 100) : 0;
              const summaryHtml = sections.map((section, index) => {
                  const detail = section.selectedParts.length
                      ? escapeHtml(section.selectedParts.join(', '))
                      : 'Noch keine Auswahl';
                  const status = section.full
                      ? `<span class="nc-summary-check" aria-label="Bereit" title="Bereit">${iconCheck}</span>`
                      : `${section.covered} / ${total} ${pluralizeLabel(total, 'Platz', 'Plätze')}`;
                  const dayLabel = section.dayNumber ? `<div class="nc-summary-day">Tag ${section.dayNumber}</div>` : '';
                  const summaryTitle = section.title || `Baustein ${index + 1}`;
                  return `<div class="nc-summary-row ${section.full ? 'is-complete' : ''}">
                      <div class="nc-summary-main">${dayLabel}<strong>${escapeHtml(summaryTitle)}</strong></div>
                      <span>${detail}<br>${status}</span>
                  </div>`;
              }).join('');

              if(state.step === 1) {
                  els.sbBtnAction.textContent = "Weiter";
                  els.sbBtnAction.classList.remove('checkout-mode');
                  els.sbProgressText.textContent = detailsReady
                      ? 'Termin und Personen sind bereit. Im nächsten Schritt ordnest du die Ausrüstung zu.'
                      : 'Bitte wähle Termin und Personen, um die Ausrüstung zu laden.';
                  els.mbSheetProgressText.textContent = els.sbProgressText.textContent;
                  els.sbProgressBar.style.width = detailsReady ? '12%' : '0%';
                  els.mbSheetProgressBar.style.width = detailsReady ? '12%' : '0%';
                  els.sbSummaryWrap.style.display = 'none';
                  els.mbSheetSummaryWrap.style.display = 'none';
                  
                  if (!paxOk) {
                      els.error.textContent = `Mindestteilnehmerzahl: ${requiredMin} Personen.`;
                      els.error.style.display = 'block';
                      syncMobileStepOneButton(false, false);
                      btnStep1.disabled = true; btnStep1.classList.add('disabled');
                      els.sbBtnAction.disabled = true; els.sbBtnAction.classList.add('disabled');
                  } else {
                      els.error.style.display = 'none';
                      syncMobileStepOneButton(true, detailsReady);
                      btnStep1.disabled = !detailsReady; btnStep1.classList.toggle('disabled', !detailsReady);
                      els.sbBtnAction.disabled = !detailsReady; els.sbBtnAction.classList.toggle('disabled', !detailsReady);
                  }
               } else if(state.step === 2) {
                  els.sbProgressText.textContent = sectionsTotal > 0
                      ? `${sectionsDone} von ${sectionsTotal} Bausteinen bereit`
                      : 'Live-Verfügbarkeiten werden geladen.';
                  els.mbSheetProgressText.textContent = els.sbProgressText.textContent;
                  els.sbProgressBar.style.width = `${progressPercent}%`;
                  els.mbSheetProgressBar.style.width = `${progressPercent}%`;
                  els.sbSummaryWrap.style.display = sectionsTotal > 0 ? 'block' : 'none';
                  els.mbSheetSummaryWrap.style.display = sectionsTotal > 0 ? 'block' : 'none';
                  els.sbSummary.innerHTML = summaryHtml;
                  els.mbSheetSummary.innerHTML = summaryHtml;
                  
                  let ok = sectionsTotal > 0;
                  sections.forEach(section => {
                      if (!section.full) ok = false;
                      section.quotas.forEach(q => {
                          const id = q.id || q.quota_id;
                          const count = parseInt((state.selectedQuotas[section.key] || {})[id], 10) || 0;
                          if (count > getQuotaAvailability(q)) ok = false;
                      });
                  });
                  
                  els.sbBtnAction.textContent = ok ? "In den Warenkorb" : "Bausteine vervollständigen";
                  els.mbBtnAction.textContent = ok ? "In den Warenkorb" : "Bausteine vervollständigen";
                  els.sbBtnAction.classList.toggle('checkout-mode', ok);
                  els.mbBtnAction.classList.toggle('checkout-mode', ok);
                  els.sbBtnAction.disabled = !ok;
                  els.mbBtnAction.disabled = !ok;
                   els.sbBtnAction.classList.toggle('disabled', !ok);
                   els.mbBtnAction.classList.toggle('disabled', !ok);
                }

               refreshDebugPanel();
           }

          function buildCartPayloadEntries() {
              const entries = [];
              const push = (key, value) => entries.push([key, String(value)]);

              push('service_id', state.serviceId);
              if (state.date) push('date', state.date);
              if (state.time) push('time', state.time.length <= 5 ? state.time + ':00' : state.time);
              push('ppl_adult', state.adults);
              push('ppl_child', state.children);
              push('ppl_baby', state.babies);

              if (state.apiServiceData && state.apiServiceData.overnight && state.apiServiceData.overnight_stays) {
                  push('overnight_stays', state.apiServiceData.overnight_stays);
              }

              if (state.apiRequestToken) {
                  push('request', state.apiRequestToken);
              }

              let qIdx = 0;
              const emittedQuotaKeys = {};

              for (let i = 1; i <= 15; i++) {
                  const sk = `s${i}`;
                  const sNum = String(i);
                  const raw = state.apiQuotaData ? state.apiQuotaData[`${sk}_quotas`] : null;
                  const quotaList = Array.isArray(raw) ? raw : (raw ? Object.values(raw) : []);

                  const orderedIds = [];
                  quotaList.forEach(q => {
                      const id = String(q.id || q.quota_id);
                      if (!orderedIds.includes(id)) orderedIds.push(id);
                  });

                  Object.keys(state.selectedQuotas[sk] || {}).forEach(id => {
                      if (!orderedIds.includes(String(id))) orderedIds.push(String(id));
                  });
                  Object.keys(state.hiddenQuotaSelections[sk] || {}).forEach(id => {
                      if (!orderedIds.includes(String(id))) orderedIds.push(String(id));
                  });

                  orderedIds.forEach(id => {
                      const visibleCount = parseInt((state.selectedQuotas[sk] || {})[id], 10) || 0;
                      const hiddenCount = parseInt((state.hiddenQuotaSelections[sk] || {})[id], 10) || 0;
                      let totalCount = visibleCount + hiddenCount;
                      if (totalCount < 1) return;

                      const sectionQuotaType = getSectionQuotaType(sk);
                      const quota = quotaList.find(q => String(q.id || q.quota_id) === String(id));
                      if (sectionQuotaType === '2' && quota && !isRoomLikeQuota(quota) && !isBikeRentalQuota(quota)) {
                          totalCount = 1;
                      }

                      const entryKey = `${sNum}:${id}`;
                      if (emittedQuotaKeys[entryKey]) return;
                      emittedQuotaKeys[entryKey] = true;

                      push(`quotas[${qIdx}][section]`, sNum);
                      push(`quotas[${qIdx}][quota_id]`, id);
                      push(`quotas[${qIdx}][pcs]`, totalCount);
                      qIdx++;
                  });
              }

              push('pcs', 1);
              return entries;
          }

          function buildCartPayloadSearchParams() {
              const params = new URLSearchParams();
              buildCartPayloadEntries().forEach(([key, value]) => params.append(key, value));
              return params;
          }

          function submitCartViaFormFallback(params) {
              const iframeName = `nkombi-cart-target-${Date.now()}`;
              const iframe = document.createElement('iframe');
              iframe.name = iframeName;
              iframe.style.display = 'none';
              document.body.appendChild(iframe);

              const form = document.createElement('form');
              form.method = 'POST';
              form.action = state.cartEndpoint;
              form.target = iframeName;
              form.style.display = 'none';

              params.forEach((value, key) => {
                  const input = document.createElement('input');
                  input.type = 'hidden';
                  input.name = key;
                  input.value = value;
                  form.appendChild(input);
              });

              document.body.appendChild(form);
              form.submit();

              window.setTimeout(() => {
                  redirectAfterCartSuccess();
              }, 700);
          }

          function buildRoomDiagnostics() {
              const quotaData = state.apiQuotaData || state.prefetchedQuotaData;
              if (!quotaData) return null;

              const diagnostics = {
                  requested_pax: state.adults + state.children + state.babies,
                  overnight_stays: state.apiServiceData && state.apiServiceData.overnight_stays ? state.apiServiceData.overnight_stays : null,
                  sections: {}
              };

              for (let i = 1; i <= 15; i++) {
                  const sk = `s${i}`;
                  const raw = quotaData[`${sk}_quotas`];
                  const quotas = Array.isArray(raw) ? raw : (raw ? Object.values(raw) : []);
                  const roomQuotas = quotas.filter(q => isRoomLikeQuota(q));
                  if (!roomQuotas.length) continue;

                  const specialRule = getSectionSpecialRule(sk, roomQuotas);
                  const selectedMap = state.selectedQuotas[sk] || {};
                  diagnostics.sections[sk] = {
                      special_rule: specialRule ? specialRule.mode : null,
                      chosen_room_ids: Object.keys(selectedMap).filter(id => (parseInt(selectedMap[id], 10) || 0) > 0),
                      rooms: roomQuotas.map(q => {
                          const rawAvailable = parseInt(q.available, 10);
                          const effectiveAvailable = getQuotaAvailability(q);
                          return {
                              id: q.id || q.quota_id,
                              name: q.name || '',
                              ppl: parseInt(q.ppl || q.capacity, 10) || 0,
                              raw_available: Number.isNaN(rawAvailable) ? null : rawAvailable,
                              quota: parseInt(q.quota, 10) || 0,
                              reserved: parseInt(q.reserved, 10) || 0,
                              effective_available: effectiveAvailable,
                              interpreted_as_available: effectiveAvailable > 0,
                              auto_selected: parseInt(selectedMap[String(q.id || q.quota_id)], 10) || 0
                          };
                      })
                  };
              }

              return Object.keys(diagnostics.sections).length ? diagnostics : null;
          }

          function buildDebugData() {
              const payloadEntries = buildCartPayloadEntries();
              return {
                  "Stammdaten (aus Schritt 1)": state.apiServiceData || "Noch am Laden...",
                  "Live-Kontingente (aus Schritt 2)": state.apiQuotaData || state.prefetchedQuotaData || "Werden erst geladen, wenn Datum & Zeit gewählt sind.",
                  "Zimmer-Diagnose (Plugin-Interpretation)": buildRoomDiagnostics() || "Keine zimmerartigen Kontingente in den aktuellen Live-Daten.",
                  "Warenkorb-Payload (Schlüssel/Wert)": payloadEntries.map(([key, value]) => `${key} = ${value}`),
                  "Hinweis": "Leere quotas[xxx]-Platzhalter werden hier bewusst nicht gesendet. Das alte shopping_cart.php filtert solche Platzhalter ohnehin vor der ERP-Verarbeitung heraus. Das Top-Level-Feld pcs wird wie im alten Konfigurator mit 1 gesendet. Live-Kontingente werden jetzt wie auf der alten Seite mit date, time, overnight_stays und request an quota_data.php abgefragt. Negative available-Werte werden nicht mehr künstlich freigerechnet. Fahrradtransport in Boots-Sections wird nur noch versteckt, nicht automatisch mitgesendet. Bei quota_type=2 werden feste Folgebausteine wie im Altverhalten mit pcs=1 gesendet."
              };
          }

          function refreshDebugPanel() {
              if (!els.debugOut || els.debugOut.style.display !== 'block') return;
              els.debugOut.textContent = JSON.stringify(buildDebugData(), null, 2);
          }

          function handleAction() {
              if(state.step === 1) {
                  if (shouldUseMobileBookNowMode()) {
                      scrollToConfigurator();
                      return;
                  }
                  if(!state.date || !state.time) { els.error.textContent = "Bitte Termin wählen."; els.error.style.display = 'block'; return; }
                  
                  let totalPax = state.adults + state.children + state.babies;
                  let requiredMin = window.getRequiredMinPax();
                  if (totalPax < requiredMin) return; 

                  goToStep(2); fetchLiveQuotas();
              } else {
                  document.getElementById('nc-success-overlay').style.display = 'flex';
                  const p = buildCartPayloadSearchParams();

                  fetch(state.cartEndpoint, {
                      method: 'POST',
                      headers: {
                          'Content-Type': 'application/x-www-form-urlencoded',
                          'Accept': 'application/json'
                      },
                      credentials: 'include',
                      body: p.toString()
                  })
                  .then(async res => {
                      const rawText = await res.text();
                      let data = null;
                      try {
                          data = rawText ? JSON.parse(rawText) : null;
                      } catch (e) {
                          data = null;
                      }

                      if (data && data.status) {
                          redirectAfterCartSuccess();
                          return;
                      }

                      if (data && data.status === false) {
                          throw new Error(data.message || 'Warenkorb-Fehler.');
                      }

                      submitCartViaFormFallback(p);
                  })
                  .catch((err) => {
                      if (err && err.message && err.message !== 'Warenkorb-Fehler.') {
                          console.warn('Cart fetch fallback:', err.message);
                      }
                      submitCartViaFormFallback(p);
                  });
              }
          }

          init();
        })();
        </script>
        <?php
        return ob_get_clean();
    }
}

register_activation_hook(__FILE__, array('NiersKombiKonfigurator', 'create_plugin_tables'));

// Plugin initialisieren
new NiersKombiKonfigurator();
