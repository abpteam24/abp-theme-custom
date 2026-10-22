<?php
/**
 * Plugin Name: ABP Smart Support Form
 * Description: High-Performance Database-less Support Desk Pipeline. Sends rich HTML tickets directly to your support email address with absolute automated customer auto-reply acknowledgement system.
 * Version: 3.1.0
 * Author: ABP Team
 * Text Domain: abp-smart-support
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Default selectable plugin list. Kept in one place so the admin default and
 * the front-end default can never drift apart.
 */
function abp_smart_default_plugins() {
    return "ABP RentalForge\nABP Transport Booking\nABP Event Ticket\nABP Tour & Travel";
}

// 1. ADMIN MENU & SETTINGS WORKSPACE
add_action( 'admin_menu', 'abp_smart_support_menu' );
function abp_smart_support_menu() {
    add_menu_page( 'ABP Support', 'ABP Form', 'manage_options', 'abp-smart-form', 'abp_smart_support_render_settings', 'dashicons-email-alt', 26 );
}

function abp_smart_support_render_settings() {
    if ( isset($_POST['abp_save_smart_settings']) ) {
        check_admin_referer('abp_smart_nonce_action', 'abp_smart_nonce');
        update_option('abp_smart_target_email', sanitize_email($_POST['abp_smart_target_email']));
        update_option('abp_smart_plugins_list', sanitize_textarea_field($_POST['abp_smart_plugins_list']));
        update_option('abp_smart_start_time', sanitize_text_field($_POST['abp_smart_start_time']));
        update_option('abp_smart_end_time', sanitize_text_field($_POST['abp_smart_end_time']));
        update_option('abp_smart_autoreply_text', sanitize_textarea_field($_POST['abp_smart_autoreply_text']));
        echo '<div class="notice notice-success is-dismissible"><p><b>Success:</b> Settings updated securely. Auto-reply text template saved.</p></div>';
    }

    $target_email   = get_option('abp_smart_target_email', 'support@abp-team.com');
    $plugins_list   = get_option('abp_smart_plugins_list', abp_smart_default_plugins());
    $start_time     = get_option('abp_smart_start_time', '09:00');
    $end_time       = get_option('abp_smart_end_time', '18:00');
    $default_reply  = "Dear Customer,\n\nWe have successfully received your technical support ticket under our ABP Team desk framework. Our engineering team is reviewing your incident log details and will get back to your email node as soon as possible.\n\nThank you for your patience.\n\nBest Regards,\nABP Team Support Desk";
    $autoreply_text = get_option('abp_smart_autoreply_text', $default_reply);
    ?>
    <style>
        .abp-box { background:#fff; padding:30px; border-radius:12px; box-shadow:0 10px 25px rgba(0,0,0,0.05); max-width:750px; margin-top:20px; font-family:sans-serif; }
        .abp-group { margin-bottom: 20px; }
        .abp-group label { display:block; font-weight:700; color:#1e293b; margin-bottom:8px; font-size:14px; }
        .abp-input { width:100%; padding:10px; border:1px solid #cbd5e1; border-radius:8px; background:#f8fafc; font-size:14px; box-sizing:border-box; }
        .abp-input:focus { border-color:#2563eb; outline:none; background:#fff; }
    </style>
    <div class="wrap">
        <h1 style="color:#1e3a8a; font-weight:800;">🛡️ ABP Smart Support Pipeline Configuration</h1>
        <div class="abp-box">
            <form method="POST">
                <?php wp_nonce_field('abp_smart_nonce_action', 'abp_smart_nonce'); ?>

                <div class="abp-group">
                    <label>Target Support Email Address (Sent to Mail)</label>
                    <input type="email" name="abp_smart_target_email" value="<?php echo esc_attr($target_email); ?>" class="abp-input" required>
                </div>

                <div class="abp-group">
                    <label>Selectable Plugins List (One per line)</label>
                    <textarea name="abp_smart_plugins_list" rows="4" class="abp-input" required><?php echo esc_textarea($plugins_list); ?></textarea>
                    <p style="color:#64748b; font-size:12px; margin-top:5px;">Only list the free/standard plugins here (for example: ABP RentalForge, ABP Transport Booking, ABP Event Ticket, ABP Tour &amp; Travel).</p>
                </div>

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom: 20px;">
                    <div class="abp-group" style="margin-bottom:0;">
                        <label>Support Hour Start</label>
                        <input type="time" name="abp_smart_start_time" value="<?php echo esc_attr($start_time); ?>" class="abp-input" required>
                    </div>
                    <div class="abp-group" style="margin-bottom:0;">
                        <label>Support Hour End</label>
                        <input type="time" name="abp_smart_end_time" value="<?php echo esc_attr($end_time); ?>" class="abp-input" required>
                    </div>
                </div>

                <div class="abp-group">
                    <label>🛡️ Customer Auto-Reply Email Message Template</label>
                    <textarea name="abp_smart_autoreply_text" rows="7" class="abp-input" required><?php echo esc_textarea($autoreply_text); ?></textarea>
                    <p style="color:#64748b; font-size:12px; margin-top:5px;">This text will be wrapped inside a professional layout and instantly sent back to the customer's input email inbox.</p>
                </div>

                <button type="submit" name="abp_save_smart_settings" class="button button-primary button-large" style="font-weight:700; margin-top:10px;">Save All Custom Configurations</button>
            </form>
        </div>
    </div>
    <?php
}

// 2. LIVE SUPPORT HOURS VALIDATOR ENGINE
function abp_smart_check_hours() {
    $start = get_option('abp_smart_start_time', '09:00');
    $end   = get_option('abp_smart_end_time', '18:00');

    $gmt_time = time() - (int)date('Z');
    $bd_local = $gmt_time + (6 * 3600);
    $current  = date('H:i', $bd_local);

    $is_online = ($start <= $end) ? ($current >= $start && $current <= $end) : ($current >= $start || $current <= $end);
    return [
        'status' => $is_online ? 'ONLINE' : 'OFFLINE',
        'display' => date('g:i A', strtotime($start)) . ' - ' . date('g:i A', strtotime($end))
    ];
}

// 3. FRONTEND INTERFACE ENGINE SHORTCODE: [abp_smart_support]
add_shortcode( 'abp_smart_support', 'abp_smart_support_render_frontend' );
function abp_smart_support_render_frontend() {
    wp_enqueue_script( 'jquery' );

    $plugins_raw = get_option('abp_smart_plugins_list', abp_smart_default_plugins());
    $plugins_arr = array_filter( array_map( 'trim', explode( "\n", str_replace( "\r", "", $plugins_raw ) ) ) );
    $hours       = abp_smart_check_hours();
    $badge_color = ($hours['status'] == 'ONLINE') ? '#10b981' : '#ef4444';

    ob_start();
    ?>
    <style>
        .abp-smart-wrap { max-width:820px; margin:30px auto; padding:0 4px; font-family:'Segoe UI', system-ui, -apple-system, sans-serif; box-sizing:border-box; }
        .abp-smart-wrap *, .abp-smart-wrap *::before, .abp-smart-wrap *::after { box-sizing:border-box; }
        .abp-smart-container { background:#fff; border-radius:18px; box-shadow:0 18px 46px rgba(15,23,42,.10); border:1px solid #e2e8f0; overflow:hidden; }
        .abp-smart-header { background:linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding:30px 32px; color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px; }
        .abp-smart-eyebrow { display:inline-block; font-size:10px; font-weight:800; letter-spacing:2px; text-transform:uppercase; color:#93c5fd; margin:0 0 2px; }
        .abp-smart-header h2 { margin:16px 0 0; padding:0; color:#ffffff !important; font-family:inherit !important; font-size:23px; font-weight:800; line-height:1.25; letter-spacing:-0.3px; text-transform:none; }
        .abp-smart-header p { margin:8px 0 0; opacity:.85; font-size:13px; color:rgba(255,255,255,.85); }
        .abp-smart-header p b { color:#ffffff; }
        .abp-smart-badge { background:rgba(255,255,255,.14); padding:8px 16px; border-radius:30px; font-weight:700; font-size:12px; display:inline-flex; align-items:center; white-space:nowrap; }
        .abp-smart-dot { width:8px; height:8px; background:<?php echo esc_attr($badge_color); ?>; border-radius:50%; margin-right:8px; display:inline-block; box-shadow:0 0 0 4px rgba(255,255,255,.12); }
        .abp-smart-notice { display:flex; align-items:flex-start; gap:12px; padding:15px 20px; background:#eff6ff; border-bottom:1px solid #dbeafe; color:#1e3a8a; font-size:13px; line-height:1.6; }
        .abp-smart-notice svg { flex:0 0 20px; width:20px; height:20px; margin-top:1px; color:#2563eb; }
        .abp-smart-notice b { color:#1e3a8a; }
        .abp-smart-notice a { color:#2563eb; font-weight:700; text-decoration:underline; word-break:break-word; }
        .abp-smart-body { padding:34px 32px 30px; }
        .abp-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:20px; }
        .abp-wrapper { display:flex; flex-direction:column; }
        .abp-wrapper label { font-size:13px; font-weight:600; color:#475569; margin-bottom:6px; }
        .abp-input-field { padding:12px 14px; border:1px solid #cbd5e1; border-radius:9px; font-size:14px; background:#f8fafc; color:#1e293b; width:100%; transition:.2s; }
        .abp-input-field:focus { border-color:#2563eb; background:#fff; outline:none; box-shadow:0 0 0 4px rgba(37,99,235,.10); }
        textarea.abp-input-field { resize:vertical; min-height:120px; }
        .abp-submit-btn { width:100%; background:#2563eb; color:#fff; border:none; padding:15px; border-radius:9px; font-size:15px; font-weight:700; cursor:pointer; transition:.2s; box-shadow:0 10px 22px rgba(37,99,235,.18); }
        .abp-submit-btn:hover { background:#1d4ed8; }
        .abp-submit-btn:disabled { opacity:.65; cursor:default; }
        #abp-ajax-response { display:none; padding:14px; border-radius:9px; margin-bottom:20px; font-weight:600; font-size:14px; text-align:center; }
        @media (max-width:680px) {
            .abp-smart-wrap { margin:18px auto; }
            .abp-smart-header { padding:24px 20px; }
            .abp-smart-header h2 { font-size:20px; }
            .abp-smart-badge { width:100%; justify-content:center; }
            .abp-smart-body { padding:24px 20px; }
            .abp-grid { grid-template-columns:1fr; gap:16px; }
            .abp-smart-notice { font-size:12.5px; padding:14px 16px; }
        }
        @media (max-width:420px) {
            .abp-smart-header h2 { font-size:18px; }
            .abp-smart-body { padding:20px 15px; }
        }
    </style>

    <div class="abp-smart-wrap">
        <div class="abp-smart-container">
            <div class="abp-smart-header">
                <div>
                    <span class="abp-smart-eyebrow">ABP-TEAM · Support Desk</span>
                    <h2>🛡️ Help &amp; Technical Support</h2>
                    <p>⏰ Business Hours: <b><?php echo esc_html($hours['display']); ?></b> (GMT+6)</p>
                </div>
                <div class="abp-smart-badge">
                    <span class="abp-smart-dot"></span> Support is <?php echo esc_html($hours['status']); ?>
                </div>
            </div>

            <div class="abp-smart-notice">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M12 3L2 20H22L12 3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                    <path d="M12 10V14.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <circle cx="12" cy="17.2" r="1" fill="currentColor"/>
                </svg>
                <span><b>Facing any problem sending this form?</b> If mail sending fails or you get any error, you can email us directly at <a href="mailto:support@abp-team.com">support@abp-team.com</a> or <a href="mailto:abpteam24@gmail.com">abpteam24@gmail.com</a>.</span>
            </div>

            <div class="abp-smart-body">
                <div id="abp-ajax-response"></div>

                <form id="abp-smart-form-element" enctype="multipart/form-data">
                    <?php wp_nonce_field('abp_smart_form_submission_action', 'abp_smart_form_nonce'); ?>

                    <div class="abp-grid">
                        <div class="abp-wrapper">
                            <label>Your Full Name *</label>
                            <input type="text" name="cust_name" class="abp-input-field" required>
                        </div>
                        <div class="abp-wrapper">
                            <label>Your Email Address *</label>
                            <input type="email" name="cust_email" class="abp-input-field" required>
                        </div>
                    </div>

                    <div class="abp-grid">
                        <div class="abp-wrapper">
                            <label>Subject Header *</label>
                            <input type="text" name="cust_subject" class="abp-input-field" required>
                        </div>
                        <div class="abp-wrapper">
                            <label>Select Target Plugin *</label>
                            <select name="cust_plugin" class="abp-input-field" required>
                                <?php foreach($plugins_arr as $p): ?>
                                    <option value="<?php echo esc_attr($p); ?>"><?php echo esc_html($p); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="abp-grid" style="grid-template-columns:1fr 1fr;">
                        <div class="abp-wrapper">
                            <label>Select Priority Level</label>
                            <select name="cust_priority" class="abp-input-field">
                                <option value="Low">Low</option>
                                <option value="Standard" selected>Standard</option>
                                <option value="Urgent">Urgent Escalation</option>
                            </select>
                        </div>
                    </div>

                    <div class="abp-wrapper" style="margin-bottom:20px;">
                        <label>Explain the incident details *</label>
                        <textarea name="cust_description" class="abp-input-field" rows="5" required></textarea>
                    </div>

                    <div class="abp-wrapper" style="margin-bottom:25px;">
                        <label>Attachments (Multi-file Support)</label>
                        <input type="file" name="cust_files[]" class="abp-input-field" multiple style="padding:7px;">
                        <small style="color:#94a3b8; font-size:11.5px; margin-top:6px;">Optional. Total size should stay under 8&nbsp;MB for a reliable upload.</small>
                    </div>

                    <button type="submit" class="abp-submit-btn">Send Support Request</button>
                </form>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($){
        $('#abp-smart-form-element').on('submit', function(e){
            e.preventDefault();

            var form = this;
            var resNode = $('#abp-ajax-response');
            var btn = $('.abp-submit-btn');
            var maxBytes = 8 * 1024 * 1024;

            resNode.hide();

            // Guard against post_max_size failures that show up as a "network error".
            var totalSize = 0;
            $('input[name="cust_files[]"]', form)[0].files && $('input[name="cust_files[]"]', form)[0].files.length && $.each($('input[name="cust_files[]"]', form)[0].files, function(i, f){ totalSize += f.size; });
            if (totalSize > maxBytes) {
                resNode.show().css({'background':'#fee2e2','color':'#991b1b','border':'1px solid #fca5a5'})
                    .text('Your attachments are too large (max 8 MB total). Please reduce the files or email us at support@abp-team.com / abpteam24@gmail.com.');
                return;
            }

            var fd = new FormData(form);
            fd.append('action', 'abp_smart_form_ajax_handler');

            btn.prop('disabled', true).text('Dispatching Payload... 🚀');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    var message = (res && res.data && res.data.message) ? res.data.message : 'Unexpected response from server. Please email support@abp-team.com / abpteam24@gmail.com.';
                    resNode.show().text(message);
                    if (res && res.success) {
                        resNode.css({'background':'#dcfce7','color':'#166534','border':'1px solid #bbf7d0'});
                        form.reset();
                    } else {
                        resNode.css({'background':'#fee2e2','color':'#991b1b','border':'1px solid #fca5a5'});
                    }
                    btn.prop('disabled', false).text('Send Support Request');
                },
                error: function(xhr, status) {
                    var msg = 'We could not reach the server. Please check your connection, or email us directly at support@abp-team.com / abpteam24@gmail.com.';
                    if (status === 'timeout') {
                        msg = 'The request timed out. Please try again, or email support@abp-team.com / abpteam24@gmail.com.';
                    } else if (status === 'parsererror') {
                        msg = 'The server response could not be processed. Please email us at support@abp-team.com / abpteam24@gmail.com.';
                    }
                    resNode.show().text(msg).css({'background':'#fee2e2','color':'#991b1b','border':'1px solid #fca5a5'});
                    btn.prop('disabled', false).text('Send Support Request');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

/**
 * Always answer with valid JSON, even if another plugin printed a PHP notice
 * or warning (that stray output is the usual cause of jQuery "network errors").
 */
function abp_smart_send_json( $success, $data ) {
    while ( ob_get_level() > 0 ) {
        ob_end_clean();
    }
    if ( $success ) {
        wp_send_json_success( $data );
    }
    wp_send_json_error( $data );
}

// 4. DATABASE-LESS TWIN-WAY OUTBOUND ROUTING ENGINE
add_action('wp_ajax_abp_smart_form_ajax_handler', 'abp_smart_form_ajax_handler');
add_action('wp_ajax_nopriv_abp_smart_form_ajax_handler', 'abp_smart_form_ajax_handler');
function abp_smart_form_ajax_handler() {
    if ( ! check_ajax_referer('abp_smart_form_submission_action', 'abp_smart_form_nonce', false) ) {
        abp_smart_send_json( false, array( 'message' => 'Security token expired. Please refresh the page and try again.' ) );
    }

    $name     = isset($_POST['cust_name']) ? sanitize_text_field(wp_unslash($_POST['cust_name'])) : '';
    $email    = isset($_POST['cust_email']) ? sanitize_email(wp_unslash($_POST['cust_email'])) : '';
    $subject  = isset($_POST['cust_subject']) ? sanitize_text_field(wp_unslash($_POST['cust_subject'])) : '';
    $plugin   = isset($_POST['cust_plugin']) ? sanitize_text_field(wp_unslash($_POST['cust_plugin'])) : '';
    $priority = isset($_POST['cust_priority']) ? sanitize_text_field(wp_unslash($_POST['cust_priority'])) : 'Standard';
    $desc     = isset($_POST['cust_description']) ? wp_kses_post(wp_unslash($_POST['cust_description'])) : '';

    if ( '' === $name || ! is_email($email) || '' === $subject || '' === $desc ) {
        abp_smart_send_json( false, array( 'message' => 'Please fill in all required fields with valid information.' ) );
    }

    if ( class_exists('ABP_Visitor_Tracker') ) {
        ABP_Visitor_Tracker::capture_email( $email );
    }

    $to_routing_email = ABP_Function::get_valid_email( get_option('abp_smart_target_email', 'support@abp-team.com') );

    // Secure Multi-Attachment Parsing Stack
    $attachments_paths = [];
    if ( ! empty( $_FILES['cust_files']['name'][0] ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $uploaded_files = $_FILES['cust_files'];

        foreach ( $uploaded_files['name'] as $i => $filename ) {
            if ( empty( $filename ) ) {
                continue;
            }
            if ( isset( $uploaded_files['error'][$i] ) && (int) $uploaded_files['error'][$i] !== UPLOAD_ERR_OK ) {
                continue;
            }
            $file = [
                'name'     => $uploaded_files['name'][$i],
                'type'     => $uploaded_files['type'][$i],
                'tmp_name' => $uploaded_files['tmp_name'][$i],
                'error'    => $uploaded_files['error'][$i],
                'size'     => $uploaded_files['size'][$i]
            ];
            $upload = wp_handle_upload($file, ['test_form' => false]);
            if ( isset($upload['file']) ) {
                $attachments_paths[] = $upload['file'];
            }
        }
    }

    // PIPELINE 1: Rich Structural HTML Admin Notification Engine
    $email_subject = "[$priority] $plugin: $subject";

    $email_body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 20px auto; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; }
            .header { background: #1e3a8a; color: #fff; padding: 20px; font-size: 18px; font-weight: bold; }
            .body { padding: 25px; background: #fff; }
            .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .meta-table td { padding: 8px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
            .desc-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 6px; white-space: pre-wrap; font-size: 14px; margin-top: 15px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>🛡️ New Support Ticket</div>
            <div class='body'>
                <table class='meta-table'>
                    <tr><td style='width:120px; font-weight:bold;'>Name:</td><td>$name</td></tr>
                    <tr><td style='font-weight:bold;'>Email:</td><td><a href='mailto:$email'>$email</a></td></tr>
                    <tr><td style='font-weight:bold;'>Select Plugin:</td><td>$plugin</td></tr>
                    <tr><td style='font-weight:bold;'>Priority:</td><td>$priority</td></tr>
                    <tr><td style='font-weight:bold;'>Subject:</td><td>$subject</td></tr>
                </table>
                <div style='font-weight:bold; margin-top:15px; font-size:14px;'>Description:</div>
                <div class='desc-box'>$desc</div>
            </div>
        </div>
    </body>
    </html>";

    $headers[] = 'MIME-Version: 1.0';
    $headers[] = 'Content-Type: text/html; charset=UTF-8';
    $headers[] = 'From: ' . $name . ' <' . $to_routing_email . '>';
    $headers[] = 'Reply-To: ' . $name . ' <' . $email . '>';

    // Execute Main Notification Routing Delivery Loop
    $mail_success = wp_mail($to_routing_email, $email_subject, $email_body, $headers, $attachments_paths);

    // Clean up temporary uploads no matter what happens next.
    $cleanup = function () use ( $attachments_paths ) {
        foreach ( $attachments_paths as $file_path ) {
            @unlink( $file_path );
        }
    };

    if ( ! $mail_success ) {
        $cleanup();
        abp_smart_send_json( false, array(
            'message' => 'We could not send your request right now. Please email us directly at support@abp-team.com or abpteam24@gmail.com.'
        ) );
    }

    // PIPELINE 2: Customer Automated Acknowledgement Email Engine
    $default_reply  = "Dear Customer,\n\nWe have successfully received your technical support ticket under our ABP Team desk framework. Our engineering team is reviewing your incident log details and will get back to your email node as soon as possible.\n\nThank you for your patience.\n\nBest Regards,\nABP Team Support Desk";
    $saved_reply    = get_option('abp_smart_autoreply_text', $default_reply);

    $customer_subject = "Acknowledgment: We received your request - $subject";
    $customer_body = "
    <html>
    <body style=\"font-family: Arial, sans-serif; color: #333; line-height: 1.6;\">
        <div style=\"max-width: 600px; margin: 20px auto; padding: 25px; border: 1px solid #e2e8f0; border-radius: 8px; background:#fff;\">
            <div style=\"text-align:center; margin-bottom: 25px;\">
                <span style=\"font-size: 24px;\">🛡️</span>
                <h2 style=\"color: #1e3a8a; margin: 10px 0 0 0;\">ABP Team Support Desk</h2>
            </div>
            <div style=\"font-size: 14px; color: #334155; white-space: pre-wrap;\">" . esc_html($saved_reply) . "</div>
            <hr style=\"border: none; border-top: 1px solid #f1f5f9; margin: 25px 0;\">
            <p style=\"font-size: 11px; color: #94a3b8; text-align: center;\">This is an automated operational confirmation dispatch message. If you face any problem, email us at support@abp-team.com or abpteam24@gmail.com.</p>
        </div>
    </body>
    </html>";

    $customer_headers[] = 'MIME-Version: 1.0';
    $customer_headers[] = 'Content-Type: text/html; charset=UTF-8';
    $customer_headers[] = 'From: ABP Support <' . $to_routing_email . '>';

    // Dispatch Instant Auto-Reply back to the Customer
    wp_mail($email, $customer_subject, $customer_body, $customer_headers);

    $cleanup();
    abp_smart_send_json( true, array( 'message' => 'Success: Request sent. Check your email for confirmation!' ) );
}
