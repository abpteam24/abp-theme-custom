<?php
/**
 * Plugin Name: ABP Smart Support Form
 * Description: High-Performance Database-less Support Desk Pipeline. Sends rich HTML tickets directly to your support email address with absolute automated customer auto-reply acknowledgement system.
 * Version: 3.0.0
 * Author: ABP Team
 * Text Domain: abp-smart-support
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
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
    $plugins_list   = get_option('abp_smart_plugins_list', "ABP Booking System\nABP RentalForge");
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
    
    $plugins_raw = get_option('abp_smart_plugins_list', "ABP Booking System\nABP RentalForge");
    $plugins_arr = explode("\n", str_replace("\r", "", $plugins_raw));
    $hours = abp_smart_check_hours();
    $badge_color = ($hours['status'] == 'ONLINE') ? '#10b981' : '#ef4444';
    
    ob_start();
    ?>
    <style>
        .abp-smart-container { max-width:750px; margin:30px auto; font-family:'Segoe UI', system-ui, sans-serif; background:#fff; border-radius:16px; box-shadow:0 15px 40px rgba(0,0,0,0.06); border:1px solid #e2e8f0; overflow:hidden; }
        .abp-smart-header { background:linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding:30px; color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px; }
        .abp-smart-header h2 { margin:0; font-size:22px; font-weight:700; }
        .abp-smart-header p { margin:5px 0 0 0; opacity:0.8; font-size:13px; }
        .abp-smart-badge { background:rgba(255,255,255,0.15); padding:6px 14px; border-radius:30px; font-weight:700; font-size:12px; display:flex; align-items:center; }
        .abp-smart-dot { width:8px; height:8px; background:<?php echo $badge_color; ?>; border-radius:50%; margin-right:8px; display:inline-block; }
        .abp-smart-body { padding:35px; box-sizing:border-box; }
        .abp-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:25px; }
        @media(max-width:600px) { .abp-grid { grid-template-columns:1fr; } }
        .abp-wrapper { display:flex; flex-direction:column; }
        .abp-wrapper label { font-size:13px; font-weight:600; color:#475569; margin-bottom:6px; }
        .abp-input-field { padding:12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:#f8fafc; color:#1e293b; width:100%; box-sizing:border-box; }
        .abp-input-field:focus { border-color:#2563eb; background:#fff; outline:none; box-shadow:0 0 0 4px rgba(37,99,235,0.1); }
        .abp-submit-btn { width:100%; background:#2563eb; color:#fff; border:none; padding:14px; border-radius:8px; font-size:15px; font-weight:700; cursor:pointer; transition:all 0.2s ease; box-shadow:0 4px 12px rgba(37,99,235,0.1); }
        .abp-submit-btn:hover { background:#1d4ed8; }
        #abp-ajax-response { display:none; padding:14px; border-radius:8px; margin-bottom:20px; font-weight:600; font-size:14px; text-align:center; }
    </style>

    <div class="abp-smart-container">
        <div class="abp-smart-header">
            <div>
                <h2>🛡️ Help & Technical Support Desk</h2>
                <p>⏰ Business Hours: <b><?php echo esc_html($hours['display']); ?></b> (GMT+6)</p>
            </div>
            <div class="abp-smart-badge">
                <span class="abp-smart-dot"></span> Support is <?php echo esc_html($hours['status']); ?>
            </div>
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
                            <?php foreach($plugins_arr as $p): $p = trim($p); if(!empty($p)): ?>
                                <option value="<?php echo esc_attr($p); ?>"><?php echo esc_html($p); ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="abp-grid">
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
                </div>

                <button type="submit" class="abp-submit-btn">Send Support Request</button>
            </form>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($){
        $('#abp-smart-form-element').on('submit', function(e){
            e.preventDefault();
            
            var form = this;
            var fd = new FormData(form);
            fd.append('action', 'abp_smart_form_ajax_handler');

            var resNode = $('#abp-ajax-response');
            var btn = $('.abp-submit-btn');

            resNode.hide();
            btn.prop('disabled', true).text('Dispatching Payload... 🚀');

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: fd,
                contentType: false,
                processData: false,
                success: function(res) {
                    resNode.show().text(res.data.message);
                    if (res.success) {
                        resNode.css({'background':'#dcfce7','color':'#166534','border':'1px solid #bbf7d0'});
                        form.reset();
                    } else {
                        resNode.css({'background':'#fee2e2','color':'#991b1b','border':'1px solid #fca5a5'});
                    }
                    btn.prop('disabled', false).text('Send Support Request');
                },
                error: function() {
                    resNode.show().text('Network transport execution channel failure.').css({'background':'#fee2e2','color':'#991b1b'});
                    btn.prop('disabled', false).text('Send Support Request');
                }
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
}

// 4. DATABASE-LESS TWIN-WAY OUTBOUND ROUTING ENGINE
add_action('wp_ajax_abp_smart_form_ajax_handler', 'abp_smart_form_ajax_handler');
add_action('wp_ajax_nopriv_abp_smart_form_ajax_handler', 'abp_smart_form_ajax_handler');
function abp_smart_form_ajax_handler() {
    check_ajax_referer('abp_smart_form_submission_action', 'abp_smart_form_nonce');

    $name     = sanitize_text_field($_POST['cust_name']);
    $email    = sanitize_email($_POST['cust_email']);
    $subject  = sanitize_text_field($_POST['cust_subject']);
    $plugin   = sanitize_text_field($_POST['cust_plugin']);
    $priority = sanitize_text_field($_POST['cust_priority']);
    $desc     = wp_kses_post($_POST['cust_description']);

    $to_routing_email = get_option('abp_smart_target_email', 'support@abp-team.com');

    // Secure Multi-Attachment Parsing Stack
    $attachments_paths = [];
    if ( !empty($_FILES['cust_files']['name'][0]) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        $uploaded_files = $_FILES['cust_files'];
        
        foreach ($uploaded_files['name'] as $i => $filename) {
            if ($uploaded_files['name'][$i]) {
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
                    <tr><td style='font-weight:bold;'>Slect Plugin:</td><td>$plugin</td></tr>
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

    // PIPELINE 2: Customer Automated Acknowledgement Email Engine
    if ( $mail_success ) {
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
                <p style=\"font-size: 11px; color: #94a3b8; text-align: center;\">This is an automated operational confirmation dispatch message. Please do not reply directly to this notice unless tracking requests.</p>
            </div>
        </body>
        </html>";

        $customer_headers[] = 'MIME-Version: 1.0';
        $customer_headers[] = 'Content-Type: text/html; charset=UTF-8';
        $customer_headers[] = 'From: ABP Support <' . $to_routing_email . '>';

        // Dispatch Instant Auto-Reply back to the Customer
        wp_mail($email, $customer_subject, $customer_body, $customer_headers);

        // Wipe local temporary disk attachments
        foreach($attachments_paths as $file_path) {
            @unlink($file_path);
        }
        wp_send_json_success(['message' => 'Success: Request sent. Check your email for confirmation!']);
    } else {
        wp_send_json_error(['message' => 'Critical: Transport failure. Verify WP Mail SMTP configurations.']);
    }
}