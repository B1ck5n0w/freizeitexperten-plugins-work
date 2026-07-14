<?php
/*
Plugin Name: Niers Touren-Konfigurator
Plugin URI: https://freizeitexperten.de
Description: Ein 3-stufiger Konfigurator für Bootsbuchungen an der Niers mit Live-API-Schnittstelle. Shortcode: [niers_konfigurator_3_steps]
Version: 9.18.1
Author: Freizeitexperten
Author URI: https://freizeitexperten.de
*/

// Verhindert den direkten Aufruf der Datei von außen
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

function niers_konfigurator_get_tour_data_json() {
    return <<<'JSON'
[
  {"start":"Geldern","destinations":[
    {"name":"Wetten","km":7,"time":2,"price":19,"price1er":25.5,"priceChild":12,"ids":{"kanu":45,"kajak":38,"schlauch":25},"times":{"mf":["10:00","12:00"],"ss":["10:00","12:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Kevelaer","km":13,"time":3.5,"price":23,"price1er":28.5,"priceChild":12,"ids":{"kanu":47,"kajak":39,"schlauch":27},"times":{"mf":["10:00","12:00"],"ss":["10:00","12:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Wissen","km":18,"time":4,"price":25,"price1er":33,"priceChild":12,"ids":{"kanu":48,"kajak":41,"schlauch":29},"times":{"mf":["10:00","12:00"],"ss":["10:00","12:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Weeze","km":22,"time":6,"price":30,"price1er":37.5,"priceChild":12,"ids":{"kanu":50,"kajak":43,"schlauch":0},"times":{"mf":["10:00","12:00"],"ss":["10:00","12:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"}
  ]},
  {"start":"Wetten","destinations":[
    {"name":"Kevelaer","km":6,"time":2,"price":19,"price1er":25.5,"priceChild":12,"ids":{"kanu":65,"kajak":53,"schlauch":31},"times":{"mf":[],"ss":["14:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Wissen","km":11,"time":3,"price":21,"price1er":28.5,"priceChild":12,"ids":{"kanu":0,"kajak":54,"schlauch":32},"times":{"mf":[],"ss":["14:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Weeze","km":15,"time":4,"price":25,"price1er":33,"priceChild":12,"ids":{"kanu":68,"kajak":56,"schlauch":34},"times":{"mf":[],"ss":["14:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Jan an de Fähr","km":22,"time":6,"price":30,"price1er":37.5,"priceChild":12,"ids":{"kanu":69,"kajak":62,"schlauch":0},"times":{"mf":[],"ss":["14:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"}
  ]},
  {"start":"Kevelaer","destinations":[
    {"name":"Wissen","km":5,"time":1.5,"price":20,"price1er":30,"priceChild":12,"ids":{"kanu":71,"kajak":75,"schlauch":36},"times":{"mf":["13:00"],"ss":["11:00","13:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Weeze","km":9,"time":2,"price":21,"price1er":31.5,"priceChild":12,"ids":{"kanu":72,"kajak":76,"schlauch":40},"times":{"mf":["13:00"],"ss":["11:00","13:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Jan an de Fähr","km":16,"time":3.5,"price":23,"price1er":34.5,"priceChild":12,"ids":{"kanu":73,"kajak":77,"schlauch":42},"times":{"mf":["13:00"],"ss":["11:00","13:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Goch","km":20,"time":4.5,"price":26,"price1er":39,"priceChild":12,"ids":{"kanu":74,"kajak":78,"schlauch":44},"times":{"mf":["13:00"],"ss":["11:00","13:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"}
  ]},
  {"start":"Wissen","destinations":[
    {"name":"Weeze","km":4,"time":1.5,"price":17,"price1er":24,"priceChild":12,"ids":{"kanu":96,"kajak":95,"schlauch":46},"times":{"mf":["12:30"],"ss":["10:30","12:30"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Jan an de Fähr","km":11,"time":3,"price":21,"price1er":28.5,"priceChild":12,"ids":{"kanu":169,"kajak":98,"schlauch":49},"times":{"mf":["12:30"],"ss":["10:30","12:30"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Goch","km":15,"time":3.5,"price":25,"price1er":33,"priceChild":12,"ids":{"kanu":110,"kajak":109,"schlauch":51},"times":{"mf":["12:30"],"ss":["10:30","12:30"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Kessel","km":23,"time":5.5,"minPax":10,"price":30,"price1er":37.5,"priceChild":12,"ids":{"kanu":170,"kajak":168,"schlauch":0},"times":{"mf":["12:30"],"ss":["10:30","12:30"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"}
  ]},
  {"start":"Weeze","destinations":[
    {"name":"Jan an de Fähr","km":7,"time":2,"price":19,"price1er":25.5,"priceChild":12,"ids":{"kanu":171,"kajak":177,"schlauch":52},"times":{"mf":["12:00"],"ss":["10:00","12:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Goch","km":11,"time":3,"price":21,"price1er":28.5,"priceChild":12,"ids":{"kanu":172,"kajak":178,"schlauch":55},"times":{"mf":["12:00"],"ss":["10:00","12:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Kessel","km":19,"time":5,"minPax":10,"price":25,"price1er":33,"priceChild":12,"ids":{"kanu":173,"kajak":179,"schlauch":58},"times":{"mf":["12:00"],"ss":["10:00","12:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"}
  ]},
  {"start":"Jan an de Fähr","destinations":[
    {"name":"Goch","km":4,"time":1.5,"price":18,"price1er":24,"priceChild":12,"ids":{"kanu":174,"kajak":180,"schlauch":60},"times":{"mf":["11:00","13:00"],"ss":["11:00","13:00"]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"},
    {"name":"Kessel","km":12,"time":3.5,"minPax":10,"price":22,"price1er":28.5,"priceChild":12,"ids":{"kanu":175,"kajak":181,"schlauch":64},"times":{"mf":[],"ss":[]},"seasonStart":"2026-04-15","seasonEnd":"2026-10-31"}
  ]}
]
JSON;
}

function niers_konfigurator_get_tour_data() {
    $tour_data = json_decode(niers_konfigurator_get_tour_data_json(), true);
    return is_array($tour_data) ? $tour_data : array();
}

function niers_konfigurator_format_admin_times($times) {
    if (empty($times) || !is_array($times)) {
        return 'Nur auf Anfrage';
    }
    return implode(', ', array_map(static function($time) {
        return $time . ' Uhr';
    }, $times));
}

add_action('admin_menu', function() {
    add_options_page(
        'Niers Regeln',
        'Niers Regeln',
        'manage_options',
        'niers-konfigurator-regeln',
        'niers_konfigurator_render_rules_admin_page'
    );
});

function niers_konfigurator_render_rules_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Keine Berechtigung.');
    }

    if (isset($_POST['niers_konfigurator_settings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['niers_konfigurator_settings_nonce'])), 'niers_konfigurator_save_settings')) {
        update_option('niers_konfigurator_api_v1_base_url', untrailingslashit(esc_url_raw(wp_unslash($_POST['niers_konfigurator_api_v1_base_url'] ?? ''))));
        echo '<div class="notice notice-success is-dismissible"><p>Einstellungen gespeichert.</p></div>';
    }

    $api_v1_base_url = get_option('niers_konfigurator_api_v1_base_url', '');
    $tour_data = niers_konfigurator_get_tour_data();
    ?>
    <div class="wrap">
        <h1>Niers Konfigurator: Strecken & Regeln</h1>
        <form method="post" style="background:#fff;border:1px solid #ccd0d4;padding:16px;margin:16px 0;max-width:880px;">
            <?php wp_nonce_field('niers_konfigurator_save_settings', 'niers_konfigurator_settings_nonce'); ?>
            <h2 style="margin-top:0;">DEV API v1</h2>
            <p>Optionaler API-v1-Endpunkt fuer die serverseitige Warenkorb-Vorpruefung. Leer lassen, um die Vorpruefung zu deaktivieren.</p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="niers_konfigurator_api_v1_base_url">API v1 Basis-URL</label></th>
                    <td>
                        <input type="url" class="regular-text" id="niers_konfigurator_api_v1_base_url" name="niers_konfigurator_api_v1_base_url" value="<?php echo esc_attr($api_v1_base_url); ?>" placeholder="https://erp.ki-experte-derix.de/api/v1" />
                        <p class="description">Aktuell wird daraus <code>/cart_validate.php</code> aufgerufen. Fuer Live erst nach separater Freigabe aktivieren.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button('Einstellungen speichern'); ?>
        </form>
        <p>Diese Übersicht zeigt die aktuell im Niers-Konfigurator hinterlegten Strecken, Uhrzeiten, Mindestpersonen und Service-IDs. Leere Uhrzeit-Listen erscheinen im Frontend als <strong>Nur auf Anfrage</strong>.</p>
        <table class="widefat striped" style="margin-top:16px;">
            <thead>
                <tr>
                    <th>Strecke</th>
                    <th>KM / Dauer</th>
                    <th>Mo-Fr</th>
                    <th>Sa-So</th>
                    <th>Min.</th>
                    <th>Service-IDs</th>
                    <th>Saison</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tour_data as $start_group): ?>
                    <?php foreach ($start_group['destinations'] as $destination): ?>
                        <tr>
                            <td><strong><?php echo esc_html($start_group['start'] . ' → ' . $destination['name']); ?></strong></td>
                            <td><?php echo esc_html($destination['km'] . ' km / ca. ' . $destination['time'] . ' Std.'); ?></td>
                            <td><?php echo esc_html(niers_konfigurator_format_admin_times($destination['times']['mf'] ?? array())); ?></td>
                            <td><?php echo esc_html(niers_konfigurator_format_admin_times($destination['times']['ss'] ?? array())); ?></td>
                            <td><?php echo esc_html(!empty($destination['minPax']) ? $destination['minPax'] . ' Pers.' : 'Standard'); ?></td>
                            <td>
                                <?php echo esc_html('Kanadier: ' . ($destination['ids']['kanu'] ?: '-') . ' | 1er: ' . ($destination['ids']['kajak'] ?: '-') . ' | Schlauch: ' . ($destination['ids']['schlauch'] ?: '-')); ?>
                            </td>
                            <td><?php echo esc_html(($destination['seasonStart'] ?? '') . ' bis ' . ($destination['seasonEnd'] ?? '')); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Shortcode für den Niers Schnell-Konfigurator (V9.12 - Kevelaer Service-Mapping korrigiert)
 * Einbindung in WordPress: [niers_konfigurator_3_steps]
 */
add_shortcode('niers_konfigurator_3_steps', function() {
    $cart_endpoint = get_option('niers_kombi_cart_endpoint', home_url('/shopping_cart.php'));
    $cart_redirect = get_option('niers_kombi_cart_redirect', home_url('/warenkorb/'));
    $api_base_url = untrailingslashit(get_option('niers_kombi_api_base_url', 'https://checkin.freizeitexperten.de/shop'));
    $api_v1_base_url = untrailingslashit(get_option('niers_konfigurator_api_v1_base_url', ''));
    $cart_validate_endpoint = $api_v1_base_url ? $api_v1_base_url . '/cart_validate.php' : '';
    $contacts_module_enabled = get_option('niers_kombi_contacts_module_enabled', '0') === '1';
    $contact_step_enabled = $contacts_module_enabled && get_option('niers_kombi_contact_step_enabled', '0') === '1';
    $contact_step_url = get_option('niers_kombi_contact_step_url', home_url('/buchungsdaten/'));
    ob_start();
    ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/de.js"></script>

    <style>
      /* --- THE7 & WPBAKERY STICKY FIX --- */
      .vc_row, .vc_row-fluid, .vc_column_container, .vc_column-inner, .wpb_wrapper, 
      #page, #main, .wf-wrap, .wf-container, .main-container {
          overflow: visible !important;
      }

      /* --- CSS RESET & BASIS --- */
      #niers-configurator-wrapper { font-family: 'Open Sans', sans-serif; color: #4b5563; width: 100%; margin: 0 auto; line-height: 1.5; box-sizing: border-box; position: relative; }
      #niers-configurator-wrapper * { box-sizing: border-box; }
      @import url('https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=PT+Sans:wght@700&display=swap');

      .nc-container { background: #ffffff; border-radius: 12px; box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08); padding: 20px !important; border-top: 5px solid #2e7d28; max-width: 1300px; margin: 0 auto; width: 100%; position: relative; }
      .nc-layout { display: flex; flex-direction: column; gap: 20px; }
      
      .nc-main { flex: 1; min-width: 0; position: relative; }
      .nc-sidebar { display: none; } 

      @media (max-width: 1023px) { .nc-main { padding-bottom: 115px !important; } }

      @media (min-width: 1024px) { 
          .nc-layout { flex-direction: row; align-items: flex-start; } 
          .nc-main { padding: 30px; }
          .nc-sidebar { 
              display: block; width: 340px; flex-shrink: 0; position: sticky; top: 80px; align-self: flex-start; 
              background: #ffffff; border-radius: 12px; box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08); padding: 25px; border-top: 5px solid #0f172a; z-index: 250;
          }
      }

      .nc-payment-box { margin-top: 14px; padding-top: 16px; border-top: 1px dashed #d1d5db; }
      .nc-payment-title { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #6b7280; letter-spacing: 0.5px; margin-bottom: 10px; }
      .nc-payment-row { display: flex; flex-wrap: wrap; gap: 8px; }
      .nc-payment-badge {
          display: inline-flex; align-items: center; gap: 8px;
          min-height: 40px; padding: 8px 12px; border-radius: 10px;
          border: 1px solid #e5e7eb; background: #f8fafc; color: #1f2937;
          font-size: 13px; font-weight: 700; line-height: 1;
      }
      .nc-payment-icon { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; flex-shrink: 0; }

      /* Success Overlay */
      #nc-success-overlay {
          position: absolute; top: 0; left: 0; width: 100%; height: 100%;
          background: rgba(255,255,255,0.95); z-index: 10000;
          display: none; flex-direction: column; align-items: center; justify-content: center;
          border-radius: 12px; text-align: center; padding: 20px;
          animation: nc-fade-in 0.3s ease;
      }

      /* Mobile Sticky Footer */
      .nc-mobile-bar { display: flex; justify-content: space-between; align-items: center; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; box-shadow: 0 -4px 20px rgba(0,0,0,0.15); padding: 12px 20px; z-index: 9999; border-top: 1px solid #e5e7eb; transition: transform 0.3s ease; }
      @media (min-width: 1024px) { .nc-mobile-bar { display: none !important; } }

      /* FLATPICKR STYLING & MOBILE FIX */
      .flatpickr-calendar { font-family: 'Open Sans', sans-serif !important; z-index: 999999 !important; box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important; border: 1px solid #e5e7eb !important; border-radius: 12px !important; padding: 10px !important; }
      .flatpickr-month { color: #1f2937 !important; fill: #1f2937 !important; height: 50px !important; margin-bottom: 5px !important; }
      .flatpickr-day { border-radius: 8px !important; color: #4b5563 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; height: 35px !important; width: 35px !important; line-height: 1 !important; margin: 2px !important; }
      .flatpickr-day.selected { background: #2e7d28 !important; border-color: #2e7d28 !important; color: #fff !important; }
      .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover { color: #d1d5db !important; background: #f9fafb !important; cursor: not-allowed !important; border-color: transparent !important; }
      
      @media (max-width: 480px) {
          .flatpickr-calendar.open {
              left: 50% !important;
              transform: translateX(-50%) !important;
              right: auto !important;
          }
      }

      /* STEPPER */
      .nc-stepper { display: flex; justify-content: center; align-items: center; margin-bottom: 25px; gap: 5px; flex-wrap: wrap; }
      .nc-step { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; color: #9ca3af; transition: opacity 0.3s; }
      .nc-step.active { color: #2e7d28; }
      .nc-step-num { width: 28px; height: 28px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; color: #9ca3af; }
      .nc-step.active .nc-step-num { background: #2e7d28; color: #fff; }
      .nc-step-divider { flex-grow: 1; max-width: 40px; height: 2px; background: #e5e7eb; transition: opacity 0.3s; }

      /* PANELS & GRIDS */
      .nc-step-panel { display: none; animation: nc-fade-in 0.4s ease-out; }
      .nc-step-panel.active { display: block; }
      @keyframes nc-fade-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
      
      .nc-grid-row-1 { display: grid; grid-template-columns: minmax(0, 1fr); gap: 15px; margin-bottom: 15px; }
      .nc-grid-row-2 { display: grid; grid-template-columns: minmax(0, 1fr); gap: 15px; }
      
      @media (min-width: 768px) { 
          .nc-grid-row-1 { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; margin-bottom: 20px; }
          .nc-grid-row-2 { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
      }
      @media (min-width: 1024px) {
          .nc-grid-row-1 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
      }

      /* LABELS & TOOLTIPS */
      .nc-label { display: block !important; font-size: 11px !important; font-weight: 700 !important; text-transform: uppercase !important; color: #6b7280 !important; margin-bottom: 6px !important; letter-spacing: 0.5px !important; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%; }
      
      .nc-tooltip { position: relative; display: inline-flex; align-items: center; cursor: help; z-index: 100; }
      .nc-tooltip-text { visibility: hidden; width: 220px; background-color: #1f2937; color: #fff; text-align: left; border-radius: 6px; padding: 10px 12px; position: absolute; bottom: 130%; right: -10px; opacity: 0; transition: opacity 0.2s; font-size: 11px !important; font-weight: 400 !important; text-transform: none !important; letter-spacing: normal !important; line-height: 1.4; box-shadow: 0 5px 15px rgba(0,0,0,0.15); pointer-events: none; z-index: 400; }
      .nc-tooltip-text::after { content: ""; position: absolute; top: 100%; right: 14px; border-width: 6px; border-style: solid; border-color: #1f2937 transparent transparent transparent; }
      .nc-tooltip:hover .nc-tooltip-text { visibility: visible; opacity: 1; }
      .nc-info-icon { background: #2e7d28; color: #fff; border-radius: 50%; width: 16px; height: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-style: normal; font-weight: bold; margin-left: 6px; font-family: serif; flex-shrink: 0; }

      /* INPUTS & INTERACTION FIX - STRICKTE BREITEN */
      .nc-select, .nc-input-date { width: 100% !important; min-width: 0 !important; height: 55px !important; padding: 0 16px !important; background-color: #f9fafb !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; font-size: 16px !important; font-weight: 600 !important; color: #1f2937 !important; cursor: pointer !important; appearance: none; z-index: 10; position: relative; pointer-events: auto !important; }
      .nc-select:disabled, .nc-input-date:disabled { background-color: #f3f4f6 !important; color: #9ca3af !important; cursor: not-allowed !important; border-color: #e5e7eb !important; }

      .nc-counter-group { display: flex !important; width: 100% !important; min-width: 0 !important; gap: 10px !important; height: 55px !important; z-index: 100; position: relative; }
      .nc-counter { flex: 1 !important; width: 100% !important; min-width: 0 !important; background-color: #f9fafb !important; border: 1px solid #e5e7eb !important; border-radius: 8px !important; display: flex !important; align-items: center !important; justify-content: space-between !important; padding: 4px !important; box-sizing: border-box !important; }
      
      .nc-input-number { flex: 1 !important; width: 100% !important; min-width: 0 !important; height: 100% !important; border: none !important; background: transparent !important; text-align: center !important; font-weight: 700 !important; font-size: 18px !important; color: #1f2937 !important; outline: none !important; padding: 0 !important; margin: 0 !important; -moz-appearance: textfield !important; }
      .nc-input-number::-webkit-outer-spin-button, .nc-input-number::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
      
      /* BUTTONS & ICONS */
      .nc-btn-icon { width: 40px !important; height: 40px !important; flex-shrink: 0 !important; border: none !important; background: #e5e7eb !important; cursor: pointer !important; color: #2e7d28 !important; border-radius: 6px !important; display: flex !important; align-items: center !important; justify-content: center !important; z-index: 105; position: relative; transition: background 0.2s; padding: 0 !important; }
      .nc-btn-icon:hover { background: #dcfce7 !important; }
      .nc-btn-icon:disabled { opacity: 0.25 !important; cursor: not-allowed !important; background: #e5e7eb !important; }
      .nc-btn-icon svg { width: 18px !important; height: 18px !important; stroke-width: 2.5 !important; }

      .nc-boat-counter { display: flex !important; align-items: center !important; gap: 8px !important; }
      .nc-boat-counter button { width: 34px !important; height: 34px !important; }
      .nc-boat-counter span { min-width: 24px !important; text-align: center !important; font-weight: 700 !important; font-size: 16px !important; display: inline-block !important; }

      .nc-btn-main { width: 100% !important; height: 60px !important; border: none !important; border-radius: 8px !important; font-family: 'PT Sans', sans-serif !important; font-weight: 700 !important; font-size: 18px !important; cursor: pointer !important; display: flex !important; align-items: center !important; justify-content: center !important; color: #fff !important; transition: all 0.2s ease; }
      .nc-btn-main.active { background: #2e7d28 !important; box-shadow: 0 4px 15px rgba(46, 125, 40, 0.3); }
      .nc-btn-main.active.checkout-mode { background: #DD8100 !important; box-shadow: 0 4px 15px rgba(221, 129, 0, 0.3); }
      .nc-btn-main.disabled { background: #e5e7eb !important; color: #9ca3af !important; cursor: default; box-shadow: none; }
      .nc-btn-secondary { background: transparent; border: 1px solid #d1d5db; color: #6b7280; padding: 12px 24px; border-radius: 8px; font-weight: 600; cursor: pointer; width: 100%; }
      .nc-btn-quick { background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 4px; padding: 4px 8px; font-size: 10px; font-weight: 700; color: #4b5563; cursor: pointer; margin-top: 5px; transition: all 0.2s; }
      .nc-btn-quick:hover { background: #dcfce7; color: #2e7d28; border-color: #10b981; }

      /* BOATS GRID */
      .nc-boat-card { border: 2px solid #e5e7eb; border-radius: 12px; padding: 20px; background: #fff; transition: all 0.2s; cursor: pointer; position: relative; margin-bottom: 15px; }
      .nc-boat-card.selected { border-color: #2e7d28; background: #f0fdf4; }
      .nc-boat-card.locked-card { opacity: 0.6; cursor: not-allowed; background: #f9fafb; }
      .nc-boat-card-header { display: flex; align-items: center; gap: 12px; width: 100%; }
      .nc-boat-icon { width: 50px; height: 50px; background: #f3f4f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #2e7d28; flex-shrink: 0; }
      .nc-boat-icon img { width: 36px; height: 36px; object-fit: contain; }
      .nc-radio-circle { width: 24px; height: 24px; border-radius: 50%; border: 2px solid #d1d5db; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
      .nc-boat-card.selected .nc-radio-circle::after { content: ''; width: 12px; height: 12px; background: #2e7d28; border-radius: 50%; }

      /* SIDEBAR */
      .sb-item { margin-bottom: 15px; }
      .sb-lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; display: block; margin-bottom: 4px; }
      .sb-val { font-size: 14px; font-weight: 600; color: #1f2937; line-height: 1.4; }
      .sb-tip-row { display: inline-flex; align-items: center; gap: 8px; margin-top: 10px; color: #4b5563; }
      .sb-tip-icon { width: 26px; height: 26px; border-radius: 50%; background: #f3f4f6; display: inline-flex; align-items: center; justify-content: center; color: #2e7d28; flex-shrink: 0; }
      .sb-tip-icon svg { width: 15px; height: 15px; stroke-width: 2.2; }
      .sb-tip-label { font-size: 12px; font-weight: 700; color: #4b5563; }
      .nc-price-val { font-family: 'PT Sans', sans-serif; font-weight: 700; font-size: 32px; color: #2e7d28; line-height: 1; }
      .nc-price-ab { font-size: 14px; color: #6b7280; font-weight: 400; margin-right: 4px; }

      #nc-error-msg { color: #dc2626; background: #fee2e2; border: 1px solid #fecaca; padding: 12px; border-radius: 8px; font-size: 14px; font-weight: 600; margin-top: 15px; text-align: center; display: none; }
    </style>

    <div id="niers-configurator-wrapper">
      <div class="nc-container">
        <div class="nc-layout">
            <div class="nc-main">
                <div id="nc-success-overlay">
                    <div style="background:#2e7d28; width:60px; height:60px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:20px;">
                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h2 style="font-family:'PT Sans', sans-serif; font-weight:700; color:#1f2937; margin-bottom:10px;">Fast geschafft!</h2>
                    <p style="font-size:16px; color:#4b5563; max-width:300px; margin-bottom:25px;">Deine Auswahl wird nun an den Warenkorb übermittelt.</p>
                </div>

                <div class="nc-stepper">
                    <div class="nc-step active" id="st-1"><div class="nc-step-num">1</div><span>Tour</span></div>
                    <div class="nc-step-divider"></div>
                    <div class="nc-step" id="st-2"><div class="nc-step-num">2</div><span>Boote</span></div>
                    <div class="nc-step-divider" id="st-3-divider"></div>
                    <div class="nc-step" id="st-3"><div class="nc-step-num">3</div><span>Zubehör</span></div>
                </div>

                <!-- SCHRITT 1 -->
                <div class="nc-step-panel active" id="panel-1">
                    <div class="nc-grid-row-1">
                      <div><span class="nc-label" title="Start">Start</span><select id="nc-start" class="nc-select"><option value="">Bitte wählen...</option></select></div>
                      <div><span class="nc-label" title="Ziel">Ziel</span><select id="nc-dest" class="nc-select" disabled><option value="">Erst Start wählen</option></select></div>
                      <div><span class="nc-label" title="Datum">Datum</span><input type="text" id="nc-date" class="nc-input-date" disabled placeholder="Datum wählen..." /></div>
                      <div><span class="nc-label" title="Uhrzeit">Uhrzeit</span><select id="nc-time" class="nc-select" disabled><option value="">Erst Datum wählen</option></select></div>
                    </div>
                    <div class="nc-grid-row-2">
                      <div>
                        <span class="nc-label" title="Erwachsene">Erwachsene</span>
                        <div class="nc-counter-group">
                            <div class="nc-counter">
                                <button id="btn-adults-minus" class="nc-btn-icon" type="button" aria-label="Weniger">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                                <input type="number" id="val-adults" class="nc-input-number" min="1" value="2" />
                                <button id="btn-adults-plus" class="nc-btn-icon" type="button" aria-label="Mehr">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                            </div>
                        </div>
                      </div>
                      <div>
                        <span class="nc-label" title="Kinder (5-11 J.)">Kinder (5-11 J.)</span>
                        <div class="nc-counter-group">
                            <div class="nc-counter">
                                <button id="btn-children-minus" class="nc-btn-icon" type="button" aria-label="Weniger">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                                <input type="number" id="val-children" class="nc-input-number" min="0" value="0" />
                                <button id="btn-children-plus" class="nc-btn-icon" type="button" aria-label="Mehr">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                            </div>
                        </div>
                      </div>
                      <div>
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <span class="nc-label" style="margin-bottom: 0; width: auto; overflow: visible; text-overflow: clip;">Kleinkinder (0-4 J.)</span>
                            <div class="nc-tooltip">
                                <span class="nc-info-icon">i</span>
                                <span class="nc-tooltip-text">Kinder von 0-4 Jahren fahren bei uns auf allen Strecken kostenlos mit und nehmen keinen extra Platz ein.</span>
                            </div>
                        </div>
                        <div class="nc-counter-group">
                            <div class="nc-counter">
                                <button id="btn-babies-minus" class="nc-btn-icon" type="button" aria-label="Weniger">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                                <input type="number" id="val-babies" class="nc-input-number" min="0" value="0" />
                                <button id="btn-babies-plus" class="nc-btn-icon" type="button" aria-label="Mehr">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                                </button>
                            </div>
                        </div>
                      </div>
                    </div>
                    <div id="nc-error-msg"></div>
                    <div class="mobile-only-btn" style="margin-top: 25px;"><button id="mb-btn-step1" class="nc-btn-main active">Weiter zur Bootsauswahl</button></div>
                </div>

                <!-- SCHRITT 2 -->
                <div class="nc-step-panel" id="panel-2">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                        <h4 style="font-weight:700; margin:0; font-size: 18px;">Welches Boot möchtet ihr?</h4>
                        <button id="btn-back-1" class="nc-btn-secondary" style="width:auto; padding: 8px 16px;">Angaben ändern</button>
                    </div>
                    <div id="nc-loading-quotas" style="display:none; text-align:center; padding: 40px 0;">Lade Live-Verfügbarkeiten...</div>
                    <div id="nc-step2-content"><div class="nc-boats-grid" id="boats-container"></div></div>
                </div>

                <!-- SCHRITT 3 -->
                <div class="nc-step-panel" id="panel-3">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                        <h4 style="font-weight:700; margin:0; font-size: 18px;">Braucht ihr noch Zubehör?</h4>
                        <button id="btn-back-2" class="nc-btn-secondary" style="width:auto; padding: 8px 16px;">Boote ändern</button>
                    </div>
                    <div id="extras-container"></div>
                </div>

            </div>

            <div class="nc-sidebar">
                <h3 style="font-size:20px; font-weight:700; margin-bottom:15px; border-bottom:2px solid #e5e7eb; padding-bottom:10px;">Deine Buchung</h3>
                <div class="sb-item">
                    <span class="sb-lbl">Strecke & Zeit</span>
                    <div class="sb-val" id="sb-route">Bitte Tour wählen</div>
                    <div class="sb-val" id="sb-route-meta" style="font-weight:400; color:#6b7280; margin-top: 4px;"></div>
                    <div class="sb-val" id="sb-date" style="font-weight:400; color:#6b7280;"></div>
                    <div class="sb-val" id="sb-persons" style="font-weight:600; color:#4b5563; margin-top: 6px;"></div>
                    <div class="sb-tip-row" aria-label="Bahnhof-Hinweis">
                        <span class="sb-tip-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="7" y="3" width="10" height="12" rx="2"></rect>
                                <path d="M9 18h6"></path>
                                <path d="M8 21l2-3"></path>
                                <path d="M16 21l-2-3"></path>
                                <path d="M9 7h6"></path>
                                <path d="M9 11h6"></path>
                            </svg>
                        </span>
                        <span class="sb-tip-label">Bahn-Tipp</span>
                        <div class="nc-tooltip">
                            <span class="nc-info-icon">i</span>
                            <span class="nc-tooltip-text">Die Bahnhöfe in Geldern, Kevelaer, Weeze und Goch eignen sich gut für viele Niers-Touren. Je nach gewählter Strecke liegen Start oder Ziel besonders bahnfreundlich.</span>
                        </div>
                    </div>
                </div>
                <div class="sb-item" id="sb-boat-box" style="display:none;"><span class="sb-lbl">Gewählte Boote</span><div class="sb-val" id="sb-boats">-</div></div>
                <div style="border-top:2px solid #e5e7eb; padding-top:15px; margin-top:20px;">
                    <span class="sb-lbl">Gesamtpreis</span>
                    <div id="sb-price" class="nc-price-val"><span style="color:#9ca3af; font-size:16px;">Tour wählen...</span></div>
                    <div id="sb-price-pp" style="font-size: 13px; color: #6b7280; font-weight: normal; margin-top: 4px;"></div>
                </div>
                <button id="sb-btn-action" class="nc-btn-main active" style="margin-top:20px;">Weiter</button>
                <div class="nc-payment-box" aria-label="Verfügbare Zahlungsarten">
                    <span class="nc-payment-title">Sichere Zahlung mit</span>
                    <div class="nc-payment-row">
                        <div class="nc-payment-badge">
                            <span class="nc-payment-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="1" y="1" width="20" height="20" rx="6" fill="#F3F9FF" stroke="#D6E7FF"/>
                                    <path d="M8.1 6.5H11.5C13.4 6.5 14.45 7.35 14.45 8.9C14.45 10.75 13.1 11.85 10.95 11.85H9.55L8.95 15.5H6.85L8.1 6.5Z" fill="#003087"/>
                                    <path d="M10.85 6.5H14.15C15.95 6.5 17 7.25 17 8.65C17 10.55 15.65 11.65 13.5 11.65H12.75L12.15 15.5H10.05L10.85 6.5Z" fill="#009CDE" fill-opacity="0.95"/>
                                </svg>
                            </span>
                            <span>PayPal</span>
                        </div>
                        <div class="nc-payment-badge">
                            <span class="nc-payment-icon" aria-hidden="true">
                                <svg width="22" height="22" viewBox="0 0 22 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect x="2" y="4.5" width="18" height="13" rx="3" fill="#111827"/>
                                    <rect x="2" y="7.5" width="18" height="2" fill="#4B5563"/>
                                    <rect x="5" y="13" width="4" height="1.8" rx="0.9" fill="#F9FAFB"/>
                                    <rect x="10.5" y="13" width="5.5" height="1.8" rx="0.9" fill="#D1D5DB"/>
                                </svg>
                            </span>
                            <span>Debitkarte</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

      <div class="nc-mobile-bar" id="mobile-bottom-bar" style="transform: translateY(150%);">
          <div>
              <div style="font-size:10px; font-weight:700; color:#9ca3af;">GESAMT</div>
              <div id="mb-price" style="font-weight:700; font-size:24px; color:#2e7d28; line-height:1;">---</div>
              <div id="mb-price-pp" style="font-size: 11px; color: #6b7280; font-weight: normal; margin-top: 2px;"></div>
          </div>
          <button id="mb-btn-action" class="nc-btn-main active" style="width:55%; height:50px!important;">Weiter</button>
      </div>
    </div>

    <script>
    (function() {
      const tourData = <?php echo niers_konfigurator_get_tour_data_json(); ?>;

      const svgKanu = `<img src="/wp-content/uploads/2026/05/Kanadier-Icon-SW1kx1k.png" alt="Kanadier" />`;
      const svg1er = `<img src="/wp-content/uploads/2026/05/Kajak-Icon-SW1kx1k.png" alt="1er Kajak" />`;
      const svgSchlauch = `<img src="/wp-content/uploads/2026/05/SB-Icon-SW1kx1k.png" alt="Schlauchboot" />`;
      const iconMinus = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="5" y1="12" x2="19" y2="12"></line></svg>`;
      const iconPlus = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>`;
      const iconCheck = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;

      let state = { step: 1, start: "", end: "", date: "", time: "", adults: 2, children: 0, babies: 0, selectedCat: null, apiData: {}, apiPrices: {}, selectedQuotas: {}, priceLoading: false, priceError: false, livePriceKey: "", apiBaseUrl: <?php echo json_encode($api_base_url); ?>, cartEndpoint: <?php echo json_encode($cart_endpoint); ?>, cartRedirect: <?php echo json_encode($cart_redirect); ?>, cartValidateEndpoint: <?php echo json_encode($cart_validate_endpoint); ?>, contactStepEnabled: <?php echo $contact_step_enabled ? 'true' : 'false'; ?>, contactStepUrl: <?php echo json_encode($contact_step_url); ?> };
      const els = { p1: document.getElementById('panel-1'), p2: document.getElementById('panel-2'), p3: document.getElementById('panel-3'), st1: document.getElementById('st-1'), st2: document.getElementById('st-2'), st3: document.getElementById('st-3'), st3div: document.getElementById('st-3-divider'), start: document.getElementById('nc-start'), dest: document.getElementById('nc-dest'), date: document.getElementById('nc-date'), time: document.getElementById('nc-time'), vA: document.getElementById('val-adults'), vC: document.getElementById('val-children'), vB: document.getElementById('val-babies'), error: document.getElementById('nc-error-msg'), bGrid: document.getElementById('boats-container'), eGrid: document.getElementById('extras-container'), sbBtnAction: document.getElementById('sb-btn-action'), mbBtnAction: document.getElementById('mb-btn-action') };

      let fpInstance = null;
      function init() {
        tourData.forEach(t => els.start.appendChild(new Option(t.start, t.start)));
        fpInstance = flatpickr(els.date, { 
            locale: "de", 
            dateFormat: "Y-m-d", 
            altInput: true, 
            altFormat: "d.m.Y", 
            disableMobile: true, 
            minDate: new Date().fp_incr(1), 
            position: "auto center", 
            onChange: (s, d) => { 
                els.error.style.display = 'none'; 
                state.date = d; state.time = ""; 
                updateTimeOptions(); updateStateView(); 
            } 
        });
        document.getElementById('btn-back-1').onclick = () => goToStep(1);
        document.getElementById('btn-back-2').onclick = () => goToStep(2);
      }

      function goToStep(s) {
        state.step = s;
        [els.p1, els.p2, els.p3].forEach(p => p && p.classList.remove('active'));
        [els.st1, els.st2, els.st3].forEach(st => st && st.classList.remove('active'));
        document.getElementById('panel-'+s).classList.add('active');
        document.getElementById('st-'+s).classList.add('active');
        updateStateView();
        window.scrollTo({top: document.getElementById('niers-configurator-wrapper').getBoundingClientRect().top + window.scrollY - 100, behavior: 'smooth'});
      }

      function updateTimeOptions() {
        els.time.innerHTML = '<option value="">Wähle Uhrzeit...</option>';
        const r = getRoute(); if(!r || !state.date) { els.time.disabled = true; return; }
        els.time.disabled = false;
        const isWE = [0,6].includes(new Date(state.date).getDay());
        const times = (isWE ? r.times.ss : r.times.mf);
        if (times.length === 0) els.time.appendChild(new Option("Nur auf Anfrage", "Auf Anfrage"));
        else times.forEach(t => els.time.appendChild(new Option(t + " Uhr", t)));
      }

      function getRoute() { const d = tourData.find(t => t.start === state.start); return d ? d.destinations.find(x => x.name === state.end) : null; }

      function formatDuration(hours) {
          if (hours === null || hours === undefined || hours === "") return "";
          return Number(hours).toLocaleString('de-DE', { maximumFractionDigits: 1, minimumFractionDigits: Number(hours) % 1 === 0 ? 0 : 1 });
      }

      function getRouteMetaText(route) {
          if (!route) return "";
          return `${route.km} km | ca. ${formatDuration(route.time)} Std.`;
      }

      function getRouteMinPax(route = getRoute()) {
          return parseInt(route?.minPax || 0, 10) || 0;
      }

      function getPayingPax() {
          return state.adults + state.children;
      }

      function getRouteMinPaxMessage(route = getRoute()) {
          const minPax = getRouteMinPax(route);
          return minPax > 0 ? `Diese Strecke ist erst ab ${minPax} Personen buchbar.` : '';
      }

      function hasEnoughRoutePax(route = getRoute()) {
          const minPax = getRouteMinPax(route);
          return minPax <= 0 || getPayingPax() >= minPax;
      }

      function getRoutePriceKey(r = getRoute()) {
          if (!r) return "";
          return [r.ids?.kanu || 0, r.ids?.kajak || 0, r.ids?.schlauch || 0].join(':');
      }

      function resetLiveSelectionState() {
          state.selectedCat = null;
          state.apiData = {};
          state.apiPrices = {};
          state.selectedQuotas = { kanu: {}, kajak: {}, schlauch: {} };
          state.priceLoading = false;
          state.priceError = false;
          state.livePriceKey = "";
      }

      function normalizeApiBaseUrl(rawUrl) {
          return String(rawUrl || 'https://checkin.freizeitexperten.de/shop').replace(/\/+$/, '');
      }

      function buildApiUrl(fileName) {
          return `${normalizeApiBaseUrl(state.apiBaseUrl)}/${fileName}`;
      }

      async function fetchService(sid) {
          if(!sid || sid === 0) return null;
          try {
              let res = await fetch(`${buildApiUrl('service_data.php')}?service_id=${encodeURIComponent(sid)}`);
              if (!res.ok) return null;
              let json = await res.json();
              return json.data || json;
          } catch(e) { return null; }
      }

      async function fetchLivePrices() {
          const r = getRoute();
          const routeKey = getRoutePriceKey(r);

          if (!r) {
              state.apiPrices = {};
              state.priceLoading = false;
              state.priceError = false;
              state.livePriceKey = "";
              updateStateView();
              return;
          }

          state.apiPrices = {};
          state.priceLoading = true;
          state.priceError = false;
          state.livePriceKey = routeKey;
          updateStateView();

          const [kaS, kjS, scS] = await Promise.all([
              fetchService(r.ids.kanu), fetchService(r.ids.kajak), fetchService(r.ids.schlauch)
          ]);

          if (state.livePriceKey !== routeKey) return;

          state.apiPrices = { kanu: kaS, kajak: kjS, schlauch: scS };
          state.priceLoading = false;
          state.priceError = !hasAllRequiredLivePrices(r);
          updateStateView();
      }

      function hasLivePrice(cat) {
          const svc = state.apiPrices[cat];
          if (!svc) return false;
          const adultPrice = parseFloat(svc.adult_price_brutto);
          return Number.isFinite(adultPrice) && adultPrice > 0;
      }

      function getAvailablePriceCategories(r, seatingPax) {
          if (!r) return [];
          return ['kanu', 'kajak', 'schlauch']
              .filter(cat => r.ids?.[cat] && r.ids[cat] !== 0)
              .filter(cat => !(cat === 'schlauch' && seatingPax < 6));
      }

      function hasAllRequiredLivePrices(r = getRoute()) {
          const seatingPax = state.adults + state.children;
          const cats = getAvailablePriceCategories(r, seatingPax);
          return cats.length > 0 && cats.every(cat => hasLivePrice(cat));
      }

      function canUseSelectedLivePrice() {
          return !!state.selectedCat && hasLivePrice(state.selectedCat);
      }

      function getCategoryBasePrice(r, cat) {
          if (!r) return 0;

          const svc = state.apiPrices[cat];
          if (hasLivePrice(cat)) {
              const aPrice = parseFloat(svc.adult_price_brutto);
              const cPriceRaw = parseFloat(svc.child_price_brutto);
              const cPrice = Number.isFinite(cPriceRaw) ? cPriceRaw : aPrice;
              return (state.adults * aPrice) + (state.children * cPrice);
          }

          return 0;
      }

      function getStartingPrice(r, seatingPax) {
          if (!r) return 0;

          const prices = getAvailablePriceCategories(r, seatingPax)
              .map(cat => getCategoryBasePrice(r, cat))
              .filter(price => price > 0);

          return prices.length ? Math.min(...prices) : 0;
      }

      function checkHasExtras(cat) {
          const api = state.apiData[cat];
          if (!api) return false;
          let qs = api.s1_quotas || api;
          if (typeof qs === 'object' && !Array.isArray(qs)) qs = Object.values(qs).filter(v => v && typeof v === 'object');
          if (!Array.isArray(qs)) return false;
          
          return qs.some(q => {
              const n = (q.name || "").toLowerCase();
              const isAcc = n.includes('zubehör') || n.includes('tonne');
              if (n.includes('tonne') && cat === 'kajak') return false;
              return isAcc;
          });
      }

      function normalizeQuotaArray(rawQuotas) {
          let qs = rawQuotas || [];
          if (typeof qs === 'object' && !Array.isArray(qs)) {
              qs = Object.values(qs).filter(v => v && typeof v === 'object' && (v.id || v.quota_id || v.name));
          }
          return Array.isArray(qs) ? qs : [];
      }

      function getCategoryQuotaArray(cat) {
          const api = state.apiData[cat];
          return normalizeQuotaArray(api?.s1_quotas || api);
      }

      function isBoatQuota(q, cat) {
          const n = (q.name || "").toLowerCase();
          if (n.includes('weste') || n.includes('zubehör') || n.includes('tonne')) return false;
          if (n.includes('tonne') && cat === 'kajak') return false;
          return true;
      }

      function getBoatQuotaArray(cat) {
          return getCategoryQuotaArray(cat).filter(q => isBoatQuota(q, cat));
      }

      function getQuotaAvailable(cat, qid) {
          const quota = getCategoryQuotaArray(cat).find(q => String(q.id || q.quota_id) === String(qid));
          if (!quota) return 0;
          const available = parseInt(quota.available ?? quota.amount ?? quota.free);
          return isNaN(available) ? 0 : Math.max(0, available);
      }

      function normalizeBoatQuota(q) {
          const qId = q.id || q.quota_id;
          const qName = q.name || "";

          let qPpl = parseInt(q.ppl || q.capacity);
          if(isNaN(qPpl)) { let m = qName.match(/(\d+)er/); qPpl = m ? parseInt(m[1]) : 1; }

          let qMin = parseInt(q.min_ppl || q.quota_min_ppl);
          if(isNaN(qMin)) { qMin = (qPpl >= 4) ? qPpl - 1 : qPpl; }

          let qAvailable = parseInt(q.available ?? q.amount ?? q.free);
          qAvailable = isNaN(qAvailable) ? 0 : Math.max(0, qAvailable);

          return { raw: q, qId, qName, qPpl, qMin, qAvailable };
      }

      function getSelectedBoatValidation(cat = state.selectedCat) {
          const seatingPax = state.adults + state.children;
          if (!cat) {
              return { ok: false, capacity: 0, minPpl: 0, message: "Bitte zuerst ein Boot wählen." };
          }

          const selectedAmounts = state.selectedQuotas[cat] || {};
          const normalized = getBoatQuotaArray(cat).map(normalizeBoatQuota).filter(q => q.qId);
          const quotaById = {};
          normalized.forEach(q => { quotaById[String(q.qId)] = q; });

          let capacity = 0;
          let minPpl = 0;
          let selectedBoatCount = 0;

          for (let rawId in selectedAmounts) {
              const count = parseInt(selectedAmounts[rawId], 10) || 0;
              if (count <= 0) continue;
              const q = quotaById[String(rawId)];

              if (!q) {
                  return {
                      ok: false,
                      capacity,
                      minPpl,
                      message: "Die gewählte Bootsauswahl ist nicht mehr verfügbar. Bitte die Boote neu auswählen."
                  };
              }

              if (count > q.qAvailable) {
                  return {
                      ok: false,
                      capacity,
                      minPpl,
                      message: `${q.qName || 'Das gewählte Boot'} ist nur noch ${q.qAvailable}x verfügbar. Bitte die Auswahl anpassen.`
                  };
              }

              selectedBoatCount += count;
              capacity += count * q.qPpl;
              minPpl += count * q.qMin;
          }

          if (selectedBoatCount <= 0) {
              return { ok: false, capacity, minPpl, message: "Bitte zuerst ein Boot wählen." };
          }

          if (capacity < seatingPax) {
              return { ok: false, capacity, minPpl, message: "Bitte ausreichend Sitzplätze für alle Personen wählen." };
          }

          if (minPpl > seatingPax) {
              return {
                  ok: false,
                  capacity,
                  minPpl,
                  message: "Diese Bootsauswahl erfüllt die Mindestbelegung nicht. Bitte ein kleineres Boot oder eine andere Kombination wählen."
              };
          }

          return { ok: true, capacity, minPpl, message: "" };
      }

      function getVisibleBoatQuotas(cat, quotas, seatingPax) {
          const normalized = quotas.map(normalizeBoatQuota);

          if (cat === 'schlauch' && seatingPax >= 6 && seatingPax <= 11) {
              const validSingleBoats = normalized.filter(q => q.qPpl >= seatingPax && q.qMin <= seatingPax && q.qAvailable > 0);
              if (validSingleBoats.length > 0) return validSingleBoats;
          }

          return normalized;
      }
      
      // AUTO-SELECTOR FÜR BOOTE
      window.autoSelectBoats = function(cat) {
          const seatingPax = state.adults + state.children; // Kleinkinder belegen keinen physischen Sitzplatz!
          let qs = state.apiData[cat]?.s1_quotas || state.apiData[cat];
          if (!qs) return;
          if (typeof qs === 'object' && !Array.isArray(qs)) qs = Object.values(qs).filter(v => v && typeof v === 'object');
          if (!Array.isArray(qs)) return;

          let boatQuotas = qs.filter(q => {
              const n = (q.name || "").toLowerCase();
              return !n.includes('weste') && !n.includes('zubehör') && !n.includes('tonne');
          });

          boatQuotas = getVisibleBoatQuotas(cat, boatQuotas, seatingPax);

          if (!state.selectedQuotas[cat]) state.selectedQuotas[cat] = {};
          for(let qid in state.selectedQuotas[cat]) state.selectedQuotas[cat][qid] = 0;

          // 1. GIBT ES EIN EINZELNES BOOT, DAS FÜR DIE GANZE GRUPPE PASST?
          let validSingleBoats = boatQuotas.filter(q => q.qPpl >= seatingPax && q.qMin <= seatingPax && q.qAvailable > 0);
          if (validSingleBoats.length > 0) {
              validSingleBoats.sort((a, b) => a.qPpl - b.qPpl); // Kleinstes passendes zuerst
              state.selectedQuotas[cat][validSingleBoats[0].qId] = 1;
              return;
          }

          // 2. FALLBACK: Greedy-Auffüllen (Größte Boote zuerst)
          boatQuotas.sort((a, b) => b.qPpl - a.qPpl);
          let remainingPax = seatingPax;
          
          for (let q of boatQuotas) {
              while (remainingPax >= q.qPpl && q.qAvailable > (state.selectedQuotas[cat][q.qId] || 0)) {
                  state.selectedQuotas[cat][q.qId] = (state.selectedQuotas[cat][q.qId] || 0) + 1;
                  remainingPax -= q.qPpl;
              }
          }
          
          // 3. REST AUFFÜLLEN: Wenn noch Personen übrig sind, das kleinstmögliche passende Boot nehmen
          if (remainingPax > 0) {
              let availableBoats = boatQuotas.filter(q => q.qAvailable > (state.selectedQuotas[cat][q.qId] || 0));
              availableBoats.sort((a, b) => a.qPpl - b.qPpl); // Kleinste zuerst
              
              while (remainingPax > 0 && availableBoats.length > 0) {
                  let bestBoat = availableBoats.find(q => q.qPpl >= remainingPax) || availableBoats[availableBoats.length - 1];
                  state.selectedQuotas[cat][bestBoat.qId] = (state.selectedQuotas[cat][bestBoat.qId] || 0) + 1;
                  remainingPax -= bestBoat.qPpl;
                  
                  availableBoats = boatQuotas.filter(q => q.qAvailable > (state.selectedQuotas[cat][q.qId] || 0));
                  availableBoats.sort((a, b) => a.qPpl - b.qPpl);
              }
          }
      };

      function updateStateView() {
          const r = getRoute(); 
          const seatingPax = state.adults + state.children; 
          const totalPaxAll = state.adults + state.children + state.babies; 
          const isTourValid = state.start && state.end && state.date && state.time;
          const priceEl = document.getElementById('sb-price');
          const mbPriceEl = document.getElementById('mb-price');
          const ppEl = document.getElementById('sb-price-pp');
          const mbPpEl = document.getElementById('mb-price-pp');

          if(isTourValid) {
              document.getElementById('sb-route').textContent = `${state.start} → ${state.end}`;
              document.getElementById('sb-route-meta').textContent = getRouteMetaText(r);
              let deDate = state.date;
              if (deDate && deDate.includes('-')) { deDate = deDate.split('-').reverse().join('.'); }
              
              document.getElementById('sb-date').textContent = `${deDate} um ${state.time} Uhr`;
              
              // Personen aufgeschlüsselt anzeigen
              let personsText = `${state.adults} Erwachsene`;
              if (state.children > 0) personsText += `, ${state.children} Kind${state.children > 1 ? 'er' : ''}`;
              if (state.babies > 0) personsText += `, ${state.babies} Kleinkind${state.babies > 1 ? 'er' : ''}`;
              document.getElementById('sb-persons').textContent = personsText;
          } else {
              document.getElementById('sb-route-meta').textContent = r ? getRouteMetaText(r) : '';
              document.getElementById('sb-persons').textContent = '';
          }

          if (!state.start || !state.end) {
              priceEl.innerHTML = '<span style="color:#9ca3af; font-size:16px;">Tour wählen...</span>';
              mbPriceEl.textContent = '---';
              ppEl.textContent = ''; mbPpEl.textContent = '';
          } else {
              let basePrice = 0;
              let boatCapacity = 0;
              const routeMinMessage = getRouteMinPaxMessage(r);
              const routeMinOk = hasEnoughRoutePax(r);
              
              if (state.step >= 2 && state.selectedCat) {
                  basePrice = getCategoryBasePrice(r, state.selectedCat);
              } else {
                  basePrice = state.selectedCat ? getCategoryBasePrice(r, state.selectedCat) : getStartingPrice(r, seatingPax);
              }

              if (state.step === 1) {
                  if (state.priceLoading) {
                      priceEl.innerHTML = '<span style="color:#9ca3af; font-size:16px;">Preis wird geladen...</span>';
                      mbPriceEl.textContent = '...';
                  } else if (!routeMinOk) {
                      priceEl.innerHTML = `<span style="color:#dc2626; font-size:14px;">${routeMinMessage}</span>`;
                      mbPriceEl.textContent = '---';
                  } else if (state.priceError || basePrice <= 0) {
                      priceEl.innerHTML = '<span style="color:#dc2626; font-size:14px;">Preis aktuell nicht verfügbar</span>';
                      mbPriceEl.textContent = '---';
                  } else {
                      priceEl.innerHTML = `<span class="nc-price-ab">Ab</span>${basePrice.toLocaleString('de-DE', {style:'currency', currency:'EUR'})}`;
                      mbPriceEl.textContent = basePrice.toLocaleString('de-DE', {style:'currency', currency:'EUR'});
                  }
              } else {
                  if(state.selectedCat) {
                      let qs = state.apiData[state.selectedCat]?.s1_quotas || state.apiData[state.selectedCat];
                      if (typeof qs === 'object' && !Array.isArray(qs)) qs = Object.values(qs).filter(v => v && typeof v === 'object');
                      if (Array.isArray(qs)) {
                          qs.forEach(q => { 
                              const qId = q.id || q.quota_id;
                              const qName = (q.name || "").toLowerCase();
                              if (qName.includes('weste') || qName.includes('zubehör') || qName.includes('tonne')) return;
                              
                              let qPpl = parseInt(q.ppl || q.capacity);
                              if(isNaN(qPpl)) { let m = qName.match(/(\d+)er/); qPpl = m ? parseInt(m[1]) : 1; }
                              
                              if(qId) boatCapacity += (state.selectedQuotas[state.selectedCat][qId] || 0) * qPpl; 
                          });
                      }
                  }
                  
                  if (!routeMinOk) {
                      priceEl.innerHTML = `<span style="color:#dc2626; font-size:14px;">${routeMinMessage}</span>`;
                      mbPriceEl.textContent = '---';
                  } else if (!canUseSelectedLivePrice()) {
                      priceEl.innerHTML = '<span style="color:#dc2626; font-size:14px;">Preis aktuell nicht verfügbar</span>';
                      mbPriceEl.textContent = '---';
                  } else if (boatCapacity >= seatingPax) {
                      priceEl.textContent = basePrice.toLocaleString('de-DE', {style:'currency', currency:'EUR'});
                      mbPriceEl.textContent = basePrice.toLocaleString('de-DE', {style:'currency', currency:'EUR'});
                  } else {
                      priceEl.innerHTML = '<span style="color:#dc2626; font-size:14px;">Boote wählen</span>';
                      mbPriceEl.textContent = '---';
                  }
              }
              
              if (routeMinOk && !state.priceLoading && !state.priceError && basePrice > 0 && totalPaxAll > 0 && (state.step === 1 || (state.step >= 2 && boatCapacity >= seatingPax && canUseSelectedLivePrice()))) {
                  const payingPax = state.adults + state.children;
                  if (payingPax > 0) {
                      const ppStr = `(entspricht ${(basePrice / payingPax).toLocaleString('de-DE', {style:'currency', currency:'EUR'})} / Zahlender)`;
                      ppEl.textContent = ppStr; mbPpEl.textContent = ppStr;
                  }
              } else {
                  ppEl.textContent = ''; mbPpEl.textContent = '';
              }
          }

          // Stepper UI Dynamik
          const anyExtras = ['kanu', 'kajak', 'schlauch'].some(c => checkHasExtras(c));
          const showStep3 = state.selectedCat ? checkHasExtras(state.selectedCat) : (Object.keys(state.apiData).length > 0 ? anyExtras : true);
          
          if (els.st3 && els.st3div) {
              els.st3.style.display = showStep3 ? 'flex' : 'none';
              els.st3div.style.display = showStep3 ? '' : 'none';
          }

          // Buttons Dynamik
          if(state.step > 1) { 
              document.getElementById('mobile-bottom-bar').style.transform = 'translateY(0)'; 
              
              if (state.step === 2) {
                  const hasExtras = state.selectedCat ? checkHasExtras(state.selectedCat) : anyExtras;
                  els.sbBtnAction.textContent = hasExtras ? 'Weiter zu Zubehör' : 'In den Warenkorb'; 
                  els.mbBtnAction.textContent = hasExtras ? 'Weiter' : 'Warenkorb';
                  
                  if (!hasExtras && state.selectedCat) {
                      els.sbBtnAction.classList.add('checkout-mode');
                      els.mbBtnAction.classList.add('checkout-mode');
                  } else {
                      els.sbBtnAction.classList.remove('checkout-mode');
                      els.mbBtnAction.classList.remove('checkout-mode');
                  }
              } else if (state.step === 3) {
                  els.sbBtnAction.textContent = 'In den Warenkorb'; 
                  els.mbBtnAction.textContent = 'Warenkorb';
                  els.sbBtnAction.classList.add('checkout-mode');
                  els.mbBtnAction.classList.add('checkout-mode');
              }
          }
          else { 
              document.getElementById('mobile-bottom-bar').style.transform = 'translateY(150%)'; 
              els.sbBtnAction.textContent = 'Weiter';
              els.mbBtnAction.textContent = 'Weiter';
              els.sbBtnAction.classList.remove('checkout-mode');
              els.mbBtnAction.classList.remove('checkout-mode');
          }
      }

      async function fetchLiveQuotas() {
          const r = getRoute(); 
          document.getElementById('nc-loading-quotas').style.display = 'block'; 
          document.getElementById('nc-step2-content').style.display = 'none';
          const [kaQ, kjQ, scQ, kaS, kjS, scS] = await Promise.all([
              fetchQuotaData(r.ids.kanu), fetchQuotaData(r.ids.kajak), fetchQuotaData(r.ids.schlauch),
              fetchService(r.ids.kanu), fetchService(r.ids.kajak), fetchService(r.ids.schlauch)
          ]);
          state.apiData = { kanu: kaQ, kajak: kjQ, schlauch: scQ };
          state.apiPrices = { kanu: kaS, kajak: kjS, schlauch: scS };
          state.priceLoading = false;
          state.priceError = !hasAllRequiredLivePrices(r);
          state.livePriceKey = getRoutePriceKey(r);
          state.selectedQuotas = { kanu: {}, kajak: {}, schlauch: {} };
          
          if (state.selectedCat) {
              window.autoSelectBoats(state.selectedCat);
          }

          document.getElementById('nc-loading-quotas').style.display = 'none'; 
          document.getElementById('nc-step2-content').style.display = 'block';
          updateStateView();
          renderBoats();
      }

      async function fetchQuotaData(sid) {
          if(!sid || sid === 0) return null;
          const timeFormatted = state.time && state.time.length <= 5 ? state.time + ':00' : state.time;
          let fd = new FormData();
          fd.append('service_id', sid);
          fd.append('date', state.date);
          fd.append('time', timeFormatted);
          fd.append('quotas_begin_time', `${state.date} ${timeFormatted}`);
          try {
              let res = await fetch(buildApiUrl('quota_data.php'), {method:'POST', body:fd});
              if (!res.ok) return null;
              let json = await res.json();
              return json.data || json;
          } catch(e) { return null; }
      }

      async function refreshSelectedLiveQuotas() {
          const r = getRoute();
          if (!r || !state.selectedCat) return false;
          const serviceId = r.ids[state.selectedCat];
          const freshQuotaData = await fetchQuotaData(serviceId);
          if (!freshQuotaData) return false;
          state.apiData[state.selectedCat] = freshQuotaData;
          return true;
      }

      function renderBoats() {
          const totalPaxAll = state.adults + state.children + state.babies; 
          const seatingPax = state.adults + state.children; // Nur Sitzplatz-Benötiger
          els.bGrid.innerHTML = '';
          
          ['kanu', 'kajak', 'schlauch'].forEach(key => {
              const api = state.apiData[key]; 
              const r = getRoute();
              if(!api || !r.ids[key] || r.ids[key] === 0) return;
              let quotas = api.s1_quotas || api; 
              if (typeof quotas === 'object' && !Array.isArray(quotas)) { 
                  quotas = Object.values(quotas).filter(v => v && typeof v === 'object' && (v.id || v.quota_id || v.name)); 
              }
              if (!Array.isArray(quotas) || quotas.length === 0) return;

              // Zubehör aus Bootsauswahl filtern
              const boatQuotas = quotas.filter(q => {
                  const n = (q.name || "").toLowerCase();
                  return !n.includes('weste') && !n.includes('zubehör') && !n.includes('tonne');
              });

              if (boatQuotas.length === 0) return; 

              const visibleBoatQuotas = getVisibleBoatQuotas(key, boatQuotas, seatingPax);
              const boatQuotasForUi = visibleBoatQuotas.map(v => v.raw);
              
              let minC = 999, maxC = 0;
              let totalAvailableCapacity = 0;
              
              boatQuotas.forEach(q => {
                  let qPpl = parseInt(q.ppl || q.capacity);
                  if(isNaN(qPpl)) { let m = (q.name||"").match(/(\d+)er/); qPpl = m ? parseInt(m[1]) : 1; }
                  
                  let qMin = parseInt(q.min_ppl || q.quota_min_ppl);
                  if(isNaN(qMin)) { qMin = (qPpl >= 4) ? qPpl - 1 : qPpl; }
                  
                  let qAvail = parseInt(q.available ?? q.amount ?? q.free);
                  qAvail = isNaN(qAvail) ? 0 : Math.max(0, qAvail);

                  totalAvailableCapacity += (qPpl * qAvail);
                  
                  if(qPpl < minC) {
                      minC = qPpl;
                  }
                  if(qPpl > maxC) maxC = qPpl;
              });
              let capText = '';
              if (minC !== 999 && maxC !== 0) {
                  if (minC === maxC) capText = `Je Boot für ${minC} Person${minC > 1 ? 'en' : ''}`;
                  else capText = `Boote für ${minC} bis ${maxC} Personen`;
              }
              
              const isSchlauchLocked = key === 'schlauch' && seatingPax < 6;
              const isCapacityLocked = totalAvailableCapacity < seatingPax;
              
              const isLocked = isSchlauchLocked || isCapacityLocked;
              
              const isSelected = state.selectedCat === key && !isLocked; 
              const card = document.createElement('div'); 
              card.className = `nc-boat-card ${isSelected ? 'selected' : ''} ${isLocked ? 'locked-card' : ''}`;
              
              let capacityInfo = '';
              if(isSelected) {
                  let currentCap = 0; 
                  let currentMinPpl = 0;
                  
                  // PRÜFUNG: Gibt es ein einzelnes Boot, das die Vorgaben perfekt erfüllt?
                  let validSingleBoats = boatQuotasForUi.filter(q => {
                      let qPpl = parseInt(q.ppl || q.capacity);
                      if(isNaN(qPpl)) { let m = (q.name||"").match(/(\d+)er/); qPpl = m ? parseInt(m[1]) : 1; }
                      let qMin = parseInt(q.min_ppl || q.quota_min_ppl);
                      if(isNaN(qMin)) { qMin = (qPpl >= 4) ? qPpl - 1 : qPpl; }
                      
                      let qAvail = parseInt(q.available ?? q.amount ?? q.free);
                      qAvail = isNaN(qAvail) ? 0 : Math.max(0, qAvail);

                      return qPpl >= seatingPax && qMin <= seatingPax && qAvail > 0;
                  });

                  let bestSingleBoatId = null;
                  if (validSingleBoats.length > 0) {
                      validSingleBoats.sort((a, b) => {
                          let aPpl = parseInt(a.ppl || a.capacity); if(isNaN(aPpl)) { let m = (a.name||"").match(/(\d+)er/); aPpl = m ? parseInt(m[1]) : 1; }
                          let bPpl = parseInt(b.ppl || b.capacity); if(isNaN(bPpl)) { let m = (b.name||"").match(/(\d+)er/); bPpl = m ? parseInt(m[1]) : 1; }
                          return aPpl - bPpl; 
                      });
                      bestSingleBoatId = validSingleBoats[0].id || validSingleBoats[0].quota_id;
                  }

                  boatQuotasForUi.forEach(q => {
                      const qId = q.id || q.quota_id;
                      const qName = q.name || "";
                      let qPpl = parseInt(q.ppl || q.capacity);
                      if(isNaN(qPpl)) { let m = qName.match(/(\d+)er/); qPpl = m ? parseInt(m[1]) : 1; }
                      let qMin = parseInt(q.min_ppl || q.quota_min_ppl);
                      if(isNaN(qMin)) { qMin = (qPpl >= 4) ? qPpl - 1 : qPpl; }
                      
                      const count = state.selectedQuotas[key][qId] || 0;
                      if(qId) {
                          currentCap += count * qPpl; 
                          currentMinPpl += count * qMin;
                      }
                  });
                  
                  const isCartFull = currentCap >= seatingPax;

                  const qItems = boatQuotasForUi.map(q => {
                      const qId = q.id || q.quota_id;
                      const qName = q.name || "Boot";
                      
                      let qPpl = parseInt(q.ppl || q.capacity);
                      if(isNaN(qPpl)) { let m = qName.match(/(\d+)er/); qPpl = m ? parseInt(m[1]) : 1; }
                      
                      let qMin = parseInt(q.min_ppl || q.quota_min_ppl);
                      if(isNaN(qMin)) { qMin = (qPpl >= 4) ? qPpl - 1 : qPpl; }
                      
                      let qAvailable = parseInt(q.available ?? q.amount ?? q.free);
                      qAvailable = isNaN(qAvailable) ? 0 : Math.max(0, qAvailable);

                      if(!qId) return ''; 
                      
                      const count = state.selectedQuotas[key][qId] || 0;
                      
                      let plusDisabled = false;
                      let minusDisabled = false;
                      let ruleBadge = '';

                      const nextMinPpl = currentMinPpl + qMin;
                      const wouldExceedMinPpl = (nextMinPpl > seatingPax);
                      
                      plusDisabled = count >= qAvailable || isCartFull || wouldExceedMinPpl;
                      minusDisabled = count <= 0;
                      
                      if (bestSingleBoatId !== null && qId === bestSingleBoatId) {
                          ruleBadge = `<div style="font-size:11px; color:#10b981; margin-top:4px; font-weight:600;">✓ Optimales Boot (vorausgewählt)</div>`;
                      } else if (!isCartFull && wouldExceedMinPpl && count === 0) {
                          ruleBadge = `<div style="font-size:11px; color:#9ca3af; margin-top:4px;">(Nicht buchbar wg. Mindestbelegung)</div>`;
                      }
                      
                      const isItemActive = count > 0;
                      const rowBg = isItemActive ? '#ecfdf5' : '#f8fafc';
                      const rowBorder = isItemActive ? '#10b981' : '#e5e7eb';
                      const textColor = isItemActive ? '#065f46' : '#1f2937';

                      return `<div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; background:${rowBg}; border:1px solid ${rowBorder}; padding:8px 12px; border-radius:8px; transition: all 0.2s;">
                          <div style="font-size:13px; line-height: 1.2;">
                              <strong style="color: ${textColor}; display: flex; align-items: center; gap: 6px;">
                                  ${qName}
                                  ${isItemActive ? iconCheck : ''}
                              </strong>
                              <div style="font-size:11px; color:#6b7280; margin-top:2px;">(${qPpl} Plätze | Min. ${qMin} Pers.)</div>
                              ${ruleBadge}
                          </div>
                          <div class="nc-boat-counter">
                              <button class="nc-btn-icon" type="button" ${minusDisabled ? 'disabled' : ''} onclick="event.stopPropagation(); window.updateBoatCount('${key}', '${qId}', -1)">${iconMinus}</button>
                              <span style="color:${textColor};">${count}</span>
                              <button class="nc-btn-icon" type="button" ${plusDisabled ? 'disabled' : ''} onclick="event.stopPropagation(); window.updateBoatCount('${key}', '${qId}', 1)">${iconPlus}</button>
                          </div></div>`;
                  }).join('');
                  
                  if(qItems.trim() !== '') {
                      const statusBox = isCartFull 
                          ? `<div style="margin-top:12px; background:#dcfce7; color:#166534; padding:8px 12px; border-radius:6px; text-align:center; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; gap:6px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Perfekt! ${currentCap} / ${seatingPax} Plätze gewählt</div>`
                          : `<div style="margin-top:12px; text-align:center; font-size:13px; font-weight:700; color:#1f2937">Sitzplätze gewählt: <span style="color:#dc2626;">${currentCap}</span> / ${seatingPax}</div>`;

                      capacityInfo = `<div style="margin-top:15px; border-top:1px dashed #e5e7eb; padding-top:10px;">
                          <div style="font-size:11px; margin-bottom:10px; color:#6b7280; text-transform:uppercase; font-weight:700; letter-spacing:0.5px;">Wählt eure Boote:</div>
                          ${qItems}
                          ${statusBox}
                      </div>`;
                  }
              }
              
              let lockBadge = '';
              if (isCapacityLocked) lockBadge = `<span style="display:block; font-size:10px; color:#dc2626; font-weight:700; margin-top:4px;">Nicht genügend Boote (Max. ${totalAvailableCapacity} Pers.)</span>`;
              else if (isSchlauchLocked) lockBadge = `<span style="display:block; font-size:10px; color:#dc2626; font-weight:700; margin-top:4px;">Kategorie erst ab 6 Personen buchbar</span>`;
              
              card.innerHTML = `<div class="nc-boat-card-header">
                  <div class="nc-boat-icon">${key==='kanu'?svgKanu:key==='kajak'?svg1er:svgSchlauch}</div>
                  <div style="flex:1;">
                      <div style="font-weight:700; font-size:16px;">${key==='kanu'?'Kanutour':key==='kajak'?'1er Kajaks':'Schlauchboot'}</div>
                      <div style="font-size:12px; color:#6b7280; margin-top:2px; font-weight: 600;">${capText}</div>
                      ${lockBadge}
                  </div>
                  <div class="nc-radio-circle"></div>
              </div>${capacityInfo}`;
              
              card.onclick = () => { 
                  if(!isSelected && !isLocked) { 
                      state.selectedCat = key; 
                      window.autoSelectBoats(key); 
                      renderBoats(); 
                      updateStateView(); 
                  } 
              };
              els.bGrid.appendChild(card);
          });
      }

      function renderExtras() {
          els.eGrid.innerHTML = '';
          if(!state.selectedCat) return;

          let qs = state.apiData[state.selectedCat]?.s1_quotas || state.apiData[state.selectedCat];
          if (typeof qs === 'object' && !Array.isArray(qs)) qs = Object.values(qs).filter(v => v && typeof v === 'object');
          if (!Array.isArray(qs)) return;

          const extras = qs.filter(q => {
              const n = (q.name || "").toLowerCase();
              const isAcc = n.includes('zubehör') || n.includes('tonne');
              // Tonne NICHT für Kajak
              if (n.includes('tonne') && state.selectedCat === 'kajak') return false;
              return isAcc;
          });

          if (extras.length === 0) return;

          const totalPaxAll = state.adults + state.children + state.babies; 

          const html = extras.map(q => {
              const qId = q.id || q.quota_id;
              const qName = q.name || "Zubehör";
              const count = state.selectedQuotas[state.selectedCat][qId] || 0;
              
              let qAvailable = parseInt(q.available ?? q.amount ?? q.free);
              qAvailable = isNaN(qAvailable) ? 0 : Math.max(0, qAvailable);
              
              const maxAllowed = qAvailable;
              const plusDisabled = count >= maxAllowed;
              const minusDisabled = count <= 0;

              const isItemActive = count > 0;
              const rowBg = isItemActive ? '#ecfdf5' : '#f8fafc';
              const rowBorder = isItemActive ? '#10b981' : '#e5e7eb';
              const textColor = isItemActive ? '#065f46' : '#1f2937';

              return `<div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px; background:${rowBg}; border:1px solid ${rowBorder}; padding:12px; border-radius:8px; transition: all 0.2s;">
                  <div style="font-size:14px; line-height: 1.2;">
                      <strong style="color: ${textColor}; display: flex; align-items: center; gap: 6px;">
                          ${qName}
                          ${isItemActive ? iconCheck : ''}
                      </strong>
                  </div>
                  <div class="nc-boat-counter">
                      <button class="nc-btn-icon" type="button" ${minusDisabled ? 'disabled' : ''} onclick="window.updateBoatCount('${state.selectedCat}', '${qId}', -1)">${iconMinus}</button>
                      <span style="color:${textColor};">${count}</span>
                      <button class="nc-btn-icon" type="button" ${plusDisabled ? 'disabled' : ''} onclick="window.updateBoatCount('${state.selectedCat}', '${qId}', 1)">${iconPlus}</button>
                  </div></div>`;
          }).join('');

          els.eGrid.innerHTML = html;
      }

      window.updateBoatCount = (cat, qid, delta) => { 
          if(!state.selectedQuotas[cat][qid]) state.selectedQuotas[cat][qid] = 0; 
          const maxAvailable = getQuotaAvailable(cat, qid);
          state.selectedQuotas[cat][qid] = Math.min(maxAvailable, Math.max(0, state.selectedQuotas[cat][qid] + delta));
          if (state.step === 2) renderBoats(); 
          if (state.step === 3) renderExtras();
          updateStateView(); 
      };

      function normalizeSameSiteUrl(rawUrl) {
          if (!rawUrl) return rawUrl;
          try {
              const url = new URL(rawUrl, window.location.origin);
              const currentHost = window.location.hostname;
              const currentBareHost = currentHost.replace(/^www\./i, '');
              const urlBareHost = url.hostname.replace(/^www\./i, '');
              if (currentBareHost === urlBareHost) {
                  url.protocol = window.location.protocol;
                  url.host = window.location.host;
              }
              return url.toString();
          } catch (e) {
              return rawUrl;
          }
      }

      function saveCustomerPrefillContext(serviceId) {
          try {
              const seatingPax = state.adults + state.children;
              const context = {
                  source: 'niers-konfigurator',
                  source_page: window.location.href,
                  page_url: window.location.href,
                  request_token: '',
                  service_ids: String(serviceId || ''),
                  tour_name: (state.start && state.end) ? `${state.start} - ${state.end}` : '',
                  date_label: state.date && state.time ? `${state.date} | ${state.time.substring(0,5)} Uhr` : (state.date || ''),
                  pax_label: `${seatingPax} Pers.`,
                  date: state.date || '',
                  time: state.time || '',
                  adults: state.adults || 0,
                  children: state.children || 0,
                  babies: state.babies || 0
              };
              window.sessionStorage.setItem('fxpBookingContext', JSON.stringify(context));
              window.localStorage.setItem('fxpBookingContext', JSON.stringify(context));
              window.sessionStorage.setItem('fxpBookingContextTimestamp', String(Date.now()));
          } catch (e) {}
      }

      function redirectAfterCartSuccess(serviceId) {
          if (state.contactStepEnabled && state.contactStepUrl) {
              saveCustomerPrefillContext(serviceId);
              const url = new URL(state.contactStepUrl, window.location.origin);
              url.searchParams.set('fxp_return', state.cartRedirect);
              window.location.href = url.toString();
              return;
          }
          window.location.href = state.cartRedirect;
      }

      function submitCartViaFormFallback(params, serviceId) {
          const iframeName = `niers-cart-target-${Date.now()}`;
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
              redirectAfterCartSuccess(serviceId);
          }, 700);
      }

      function buildCartValidatePayload(serviceId, timeFormatted) {
          const selectedAmounts = state.selectedQuotas[state.selectedCat] || {};
          const quotas = [];

          for (let quotaId in selectedAmounts) {
              const pcs = parseInt(selectedAmounts[quotaId], 10) || 0;
              if (pcs > 0) {
                  quotas.push({
                      section: 1,
                      quota_id: parseInt(quotaId, 10),
                      pcs: pcs
                  });
              }
          }

          return {
              origin: window.location.hostname,
              source: 'niers-konfigurator',
              items: [
                  {
                      service_id: parseInt(serviceId, 10),
                      date: state.date,
                      time: timeFormatted,
                      overnight_stays: 0,
                      pax: {
                          adults: parseInt(state.adults, 10) || 0,
                          children: parseInt(state.children, 10) || 0,
                          babies: parseInt(state.babies, 10) || 0
                      },
                      quantity: quotas.reduce((sum, q) => sum + q.pcs, 0),
                      quotas: quotas,
                      metadata: {
                          start: state.start,
                          end: state.end,
                          selected_category: state.selectedCat,
                          page_url: window.location.href
                      }
                  }
              ],
              campaigns: [],
              request_id: `niers-${Date.now()}-${Math.random().toString(36).slice(2)}`
          };
      }

      function cartValidateMessage(data) {
          const defaultMessage = "Die Buchung konnte im ERP nicht bestätigt werden. Bitte Auswahl prüfen oder telefonisch buchen.";
          const item = data && data.items && data.items[0] ? data.items[0] : null;
          const messages = item && Array.isArray(item.messages) ? item.messages : [];
          const first = messages[0] || null;
          if (!first || !first.code) return defaultMessage;

          const map = {
              quota_stock_conflict: "Die gewählte Bootsauswahl ist nicht mehr ausreichend verfügbar. Bitte Auswahl anpassen.",
              quota_min_people: "Die Mindestbelegung für das gewählte Boot ist nicht erreicht. Bitte ein passendes Boot wählen.",
              quota_capacity_too_low: "Die gewählten Boote haben nicht genug Plätze für alle Personen.",
              quota_not_allowed: "Die gewählte Bootsauswahl passt nicht zu dieser Tour.",
              quota_required: "Bitte zuerst ein Boot auswählen.",
              invalid_date: "Das Datum konnte nicht bestätigt werden. Bitte erneut auswählen.",
              invalid_time: "Die Uhrzeit konnte nicht bestätigt werden. Bitte erneut auswählen.",
              service_not_found: "Die Tour konnte im ERP nicht gefunden werden."
          };
          return map[first.code] || first.message || defaultMessage;
      }

      async function validateCartBeforeSubmit(serviceId, timeFormatted) {
          if (!state.cartValidateEndpoint) {
              return { ok: true };
          }

          const payload = buildCartValidatePayload(serviceId, timeFormatted);
          let res, json;
          try {
              res = await fetch(state.cartValidateEndpoint, {
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json'
                  },
                  credentials: 'include',
                  body: JSON.stringify(payload)
              });
              json = await res.json();
          } catch (e) {
              return {
                  ok: false,
                  message: "Die ERP-Vorprüfung ist aktuell nicht erreichbar. Bitte erneut versuchen oder telefonisch buchen."
              };
          }

          if (!res.ok || !json || json.status !== true) {
              return {
                  ok: false,
                  message: "Die ERP-Vorprüfung konnte nicht abgeschlossen werden. Bitte erneut versuchen oder telefonisch buchen."
              };
          }

          if (!json.data || json.data.valid !== true) {
              return {
                  ok: false,
                  message: cartValidateMessage(json.data)
              };
          }

          return { ok: true, data: json.data };
      }

      async function submitToCart() {
          const r = getRoute();
          const serviceId = r.ids[state.selectedCat];
          if (!hasEnoughRoutePax(r)) {
              document.getElementById('nc-success-overlay').style.display = 'none';
              alert(getRouteMinPaxMessage(r));
              return;
          }
          if (!canUseSelectedLivePrice()) {
              document.getElementById('nc-success-overlay').style.display = 'none';
              alert("Der Preis konnte aktuell nicht aus dem Buchungssystem geladen werden. Bitte erneut versuchen oder telefonisch buchen.");
              return;
          }

          const refreshed = await refreshSelectedLiveQuotas();
          if (!refreshed) {
              document.getElementById('nc-success-overlay').style.display = 'none';
              alert("Die Live-Verfügbarkeit konnte aktuell nicht geprüft werden. Bitte erneut versuchen oder telefonisch buchen.");
              return;
          }

          const validation = getSelectedBoatValidation();
          if (!validation.ok) {
              document.getElementById('nc-success-overlay').style.display = 'none';
              renderBoats();
              updateStateView();
              alert(validation.message);
              return;
          }

          const params = new URLSearchParams();
          params.append('service_id', serviceId);
          params.append('date', state.date); 
          const timeFormatted = state.time.length <= 5 ? state.time + ':00' : state.time;

          const cartPrecheck = await validateCartBeforeSubmit(serviceId, timeFormatted);
          if (!cartPrecheck.ok) {
              document.getElementById('nc-success-overlay').style.display = 'none';
              alert(cartPrecheck.message);
              return;
          }

          params.append('time', timeFormatted); 
          params.append('ppl_adult', state.adults);
          params.append('ppl_child', state.children);
          params.append('ppl_baby', state.babies);
          
          let totalItems = 0;
          let qIndex = 0;
          const selectedAmounts = state.selectedQuotas[state.selectedCat];
          for (let quotaId in selectedAmounts) {
              if (selectedAmounts[quotaId] > 0) {
                  totalItems += selectedAmounts[quotaId];
                  params.append(`quotas[${qIndex}][section]`, '1');
                  params.append(`quotas[${qIndex}][quota_id]`, quotaId);
                  params.append(`quotas[${qIndex}][pcs]`, selectedAmounts[quotaId]);
                  qIndex++;
              }
          }
          params.append('pcs', totalItems);
          
          fetch(state.cartEndpoint, {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                  'Accept': 'application/json'
              },
              credentials: 'include',
              body: params.toString()
          })
          .then(async response => {
              const rawText = await response.text();
              let data = null;
              try {
                  data = rawText ? JSON.parse(rawText) : null;
              } catch (e) {
                  data = null;
              }

              if (data && data.status === true) {
                  redirectAfterCartSuccess(serviceId);
                  return;
              }

              if (data && data.status === false) {
                  throw new Error(data.message || 'Warenkorb-Fehler.');
              }

              submitCartViaFormFallback(params, serviceId);
          })
          .catch(error => {
              if (error && error.message && error.message !== 'Warenkorb-Fehler.') {
                  console.warn('Cart fetch fallback:', error.message);
              }
              submitCartViaFormFallback(params, serviceId);
          });
      }

      function handleActionClick() {
          if(state.step === 1) {
              let missing = [];
              if(!state.start) missing.push("Start"); if(!state.end) missing.push("Ziel"); if(!state.date) missing.push("Datum"); if(!state.time) missing.push("Uhrzeit");
              if(missing.length > 0) { els.error.textContent = "Bitte wählen: " + missing.join(", "); els.error.style.display = 'block'; return; }
              if(!hasEnoughRoutePax()) { els.error.textContent = getRouteMinPaxMessage(); els.error.style.display = 'block'; return; }
              if(state.priceLoading) { els.error.textContent = "Bitte kurz warten, der Preis wird noch geladen."; els.error.style.display = 'block'; return; }
              if(state.priceError || !hasAllRequiredLivePrices()) { els.error.textContent = "Der Preis konnte aktuell nicht aus dem Buchungssystem geladen werden. Bitte erneut versuchen oder telefonisch buchen."; els.error.style.display = 'block'; return; }
              els.error.style.display = 'none';
              goToStep(2); fetchLiveQuotas();
          } else if(state.step === 2) {
              if(!state.selectedCat) { alert("Bitte zuerst ein Boot wählen."); return; }
              if(!hasEnoughRoutePax()) { alert(getRouteMinPaxMessage()); return; }
              if(!canUseSelectedLivePrice()) { alert("Der Preis für diese Bootsauswahl konnte aktuell nicht aus dem Buchungssystem geladen werden. Bitte erneut versuchen oder telefonisch buchen."); return; }

              const validation = getSelectedBoatValidation();
              if(!validation.ok) { alert(validation.message); return; }
              
              // DYNAMISCH WEITER ODER DIREKT ZUM CHECKOUT
              const hasExtras = checkHasExtras(state.selectedCat);
              if (hasExtras) {
                  goToStep(3);
                  renderExtras();
              } else {
                  document.getElementById('nc-success-overlay').style.display = 'flex';
                  submitToCart();
              }
          } else if (state.step === 3) {
              if(!hasEnoughRoutePax()) { alert(getRouteMinPaxMessage()); return; }
              if(!canUseSelectedLivePrice()) { alert("Der Preis für diese Bootsauswahl konnte aktuell nicht aus dem Buchungssystem geladen werden. Bitte erneut versuchen oder telefonisch buchen."); return; }
              const validation = getSelectedBoatValidation();
              if(!validation.ok) { alert(validation.message); goToStep(2); renderBoats(); return; }
              document.getElementById('nc-success-overlay').style.display = 'flex';
              submitToCart();
          }
      }

      els.sbBtnAction.onclick = handleActionClick;
      els.mbBtnAction.onclick = handleActionClick;
      document.getElementById('mb-btn-step1').onclick = handleActionClick;
      
      state.cartEndpoint = normalizeSameSiteUrl(state.cartEndpoint);
      state.cartRedirect = normalizeSameSiteUrl(state.cartRedirect);
      state.contactStepUrl = normalizeSameSiteUrl(state.contactStepUrl);

      els.start.onchange = (e) => { 
          els.error.style.display = 'none'; state.start = e.target.value; state.end = ""; state.time = "";
          resetLiveSelectionState();
          els.dest.innerHTML = '<option value="">Wohin?</option>'; els.dest.disabled = !state.start;
          if(state.start) tourData.find(t => t.start === state.start).destinations.forEach(d => els.dest.appendChild(new Option(`${d.name} (${getRouteMetaText(d)})`, d.name)));
          els.date.disabled = true; if(fpInstance.altInput) fpInstance.altInput.disabled = true;
          els.time.disabled = true; updateTimeOptions(); updateStateView();
      };
      els.dest.onchange = (e) => { 
          els.error.style.display = 'none'; state.end = e.target.value;
          state.time = "";
          resetLiveSelectionState();
          if (state.end) { els.date.disabled = false; if(fpInstance.altInput) fpInstance.altInput.disabled = false; } 
          updateTimeOptions(); updateStateView();
          if (state.end) fetchLivePrices();
      };
      els.time.onchange = (e) => { els.error.style.display = 'none'; state.time = e.target.value; updateStateView(); };
      
      const upd = () => { 
          const seatingPax = state.adults + state.children;
          
          // Sperren automatisch abwählen
          if (state.selectedCat === 'schlauch' && seatingPax < 6) {
              state.selectedCat = null;
              for(let qid in state.selectedQuotas['schlauch']) state.selectedQuotas['schlauch'][qid] = 0;
          }

          if (state.selectedCat) {
              let cat = state.selectedCat;
              if(state.apiData[cat]) {
                  let currentCap = 0; 
                  let currentMinPpl = 0;
                  let totalAvailableCapacity = 0;
                  
                  let qs = state.apiData[cat].s1_quotas || state.apiData[cat];
                  if (typeof qs === 'object' && !Array.isArray(qs)) qs = Object.values(qs).filter(v => v && typeof v === 'object');
                  if (Array.isArray(qs)) {
                      qs.forEach(q => { 
                          const qId = q.id || q.quota_id;
                          const qName = (q.name || "").toLowerCase();
                          if (qName.includes('weste') || qName.includes('zubehör') || qName.includes('tonne')) return;
                          
                          let qPpl = parseInt(q.ppl || q.capacity);
                          if(isNaN(qPpl)) { let m = qName.match(/(\d+)er/); qPpl = m ? parseInt(m[1]) : 1; }
                          
                          let qMin = parseInt(q.min_ppl || q.quota_min_ppl);
                          if(isNaN(qMin)) { qMin = (qPpl >= 4) ? qPpl - 1 : qPpl; }
                          
                          let qAvail = parseInt(q.available ?? q.amount ?? q.free);
                          qAvail = isNaN(qAvail) ? 0 : Math.max(0, qAvail);
                          totalAvailableCapacity += (qPpl * qAvail);
                          
                          const count = state.selectedQuotas[cat][qId] || 0;
                          if(qId) {
                              currentCap += count * qPpl; 
                              currentMinPpl += count * qMin;
                          }
                      });
                      
                      // Auto-Deselect if capacity is too small for the new group size
                      if (totalAvailableCapacity < seatingPax) {
                          state.selectedCat = null;
                          for(let qid in state.selectedQuotas[cat]) state.selectedQuotas[cat][qid] = 0;
                      }
                      // Auto-Select auslösen, falls die aktuelle Wahl durch Personenänderung nicht mehr passt
                      else if (currentCap < seatingPax || currentMinPpl > seatingPax) { 
                          window.autoSelectBoats(cat); 
                      }
                  }
              }
          }
          updateStateView(); 
          if (state.step === 2) renderBoats();
          if (state.step === 3) renderExtras();
      };
      
      els.vA.onchange = (e) => { state.adults = Math.max(1, parseInt(e.target.value)||1); upd(); };
      els.vC.onchange = (e) => { state.children = Math.max(0, parseInt(e.target.value)||0); upd(); };
      els.vB.onchange = (e) => { state.babies = Math.max(0, parseInt(e.target.value)||0); upd(); };
      
      document.getElementById('btn-adults-minus').onclick = () => { if(state.adults > 1) { state.adults--; els.vA.value = state.adults; upd(); } };
      document.getElementById('btn-adults-plus').onclick = () => { state.adults++; els.vA.value = state.adults; upd(); };
      document.getElementById('btn-children-minus').onclick = () => { if(state.children > 0) { state.children--; els.vC.value = state.children; upd(); } };
      document.getElementById('btn-children-plus').onclick = () => { state.children++; els.vC.value = state.children; upd(); };
      document.getElementById('btn-babies-minus').onclick = () => { if(state.babies > 0) { state.babies--; els.vB.value = state.babies; upd(); } };
      document.getElementById('btn-babies-plus').onclick = () => { state.babies++; els.vB.value = state.babies; upd(); };
      
      init();
    })();
    </script>
    <?php
    return ob_get_clean();
});
