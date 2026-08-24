<?php
    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }
    if (!class_exists('ABP_Static')) {
        class ABP_Static {
            public static function array_info($key) {
                $current_date = current_time('Y-m-d H:i');
                $des = array(
                    'general_config' => __('Note: Configure the general settings for this transport here. If you do not want to use any specific feature, you can enable or disable it from Main Configuration → On/Off Sections. Disabling a feature will remove it from the entire site.', 'abp-transport-booking'),
                    'sale_continue' => __('Note: This switch indicate Transport Ticket sale close/continue . You can  sale close/continue  by this switch. By default sale will be  continue', 'abp-transport-booking'),
                    'abp_template' => __('Note: Here You can change your details page template.', 'abp-transport-booking'),
                    'post_sku' => __('Note: Here you can add an SKU for this post. You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'post_icon' => __('Note: Set a custom icon or emoji for this post. The selected icon/emoji will be displayed alongside the post title wherever the title appears across the website, helping it stand out and improve visual recognition.', 'abp-transport-booking'),
                    'sub_title' => __('Note: Add a Sub-title to enable the Post sub-tile. Leave this blank if you dont want to show any Sub-title information for this Post.', 'abp-transport-booking'),
                    'post_description' => __('Note: Add short description about this Transport . Leave this blank if you dont want to show any  description for this Transport.', 'abp-transport-booking'),
                    'display_capacity' => __('Note : Enable this option to display the capacity for this transport on the frontend. This setting only works when the global Transport Capacity Display option is enabled.', 'abp-transport-booking'),
                    'display_organizer' => __('Note : This switch indicate Transport Organizer . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'display_brand' => __('Note : This switch indicate Transport Brand name . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'display_category' => __('Note : This switch indicate Transport Category . You can also show or hide it on the frontend by turning the switch On or Off.', 'abp-transport-booking'),
                    'related_item' => __('Note: Select related items to display on the details page. Leave this option empty or disabled if you do not want to show related items.', 'abp-transport-booking'),
                    'post_feature' => __('Note: If you want to add feature for this Transport, you can add Here. These feature will be show with this Transport . You may leave this section empty if you do not want to show frontend. ', 'abp-transport-booking'),
                    'display_slider' => __('Note: If you want to add an image gallery for this transport, you can upload images below.  You may leave this section empty if you do not want to show images. ', 'abp-transport-booking'),
                    //=============================//
                    'route_config' => __('Note: Configure the transport route by selecting the required stops and their types. You can also add new stops while configuring the route. Boarding stops allow passengers to board only, Dropping stops allow passengers to get off only, and Both stops support both boarding and dropping. The first stop must always be Boarding, the last stop must always be Dropping, and the first stop time must be 0 minutes. All remaining stop times represent the travel time in minutes from the first stop and are applied according to your Transport Time Configuration. Enable Multiple Pickup/Drop-off Points to allow passengers to select from multiple pickup and drop-off locations. The available options are based on the configured route stops: Boarding stops become Pickup Points, Dropping stops become Drop-off Points, and Both stops are available for both. If the return journey uses the same transport, enable Same Transport Return to automatically use the same transport configuration for the return trip. ', 'abp-transport-booking'),
                    'seat_type' => __('Note: Please select your Transport seat type . Default is Seat Plan', 'abp-transport-booking'),
                    'ticket_type' => __('Note: You have disabled the Seat Plan System from the Global On/Off Settings, so your ticket types will function as regular tickets only.If you want to use a seat plan, enable Seat Plan System from the Global On/Off Settings. Once enabled, you can turn the Seat Plan feature on or off for each transport individually, allowing you to use either a seat plan or regular tickets as needed.', 'abp-transport-booking'),
                    'single_ticket_type' => __('Note: If the Global Multiple Ticket System is disabled, you cannot use multiple ticket types for any transport.To enable this feature, turn on Multiple Ticket System from the Global On/Off Settings. Once enabled, you can configure multiple ticket types and prices for each transport. You can also choose to use a single ticket for specific transports whenever needed.', 'abp-transport-booking'),
                    'display_ticket_type' => __('Note : This switch indicate Transport ticket type. if your all ticket/seat same type then switch will be off. if you want to multiple type please switch on', 'abp-transport-booking'),
                    'min_qty' => __('Note : Set the minimum quantity customers must select per order. This global setting applies across the entire booking system.', 'abp-transport-booking'),
                    'max_qty' => __('Note : Set the maximum quantity a customer can select per order. This global setting helps control the maximum number of tickets or seats a customer can book in a single order.', 'abp-transport-booking'),
                    //=============================//
                    //=============================//
                    'no_category' => __('No Category Found !', 'abp-transport-booking'),
                    'cat_name' => __('Note: Please enter a category name — the field cannot be empty. ', 'abp-transport-booking'),
                    'cat_slug' => __('Note: Category slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'cat_des' => __('Note: Category description is optional — you can add details to better explain this category. ', 'abp-transport-booking'),
                    //=============================//
                    'no_organizer' => __('No Organizer Found !', 'abp-transport-booking'),
                    'org_name' => __('Note: Please enter a Organizer name — the field cannot be empty. ', 'abp-transport-booking'),
                    'org_slug' => __('Note: Organizer slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'org_des' => __('Note: Organizer description is optional — you can add details to better explain this Organizer. ', 'abp-transport-booking'),
                    //=============================//
                    'no_location' => __('No Location Found ! ', 'abp-transport-booking'),
                    'loc_name' => __('Note: Please enter a Location name — the field cannot be empty. ', 'abp-transport-booking'),
                    'loc_slug' => __('Note: Location slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'loc_des' => __('Note: Location Address is optional — you can add details to better explain this Location Full  Address. ', 'abp-transport-booking'),
                    'display_pd' => __('You can add multiple pickup/drop-off  points for a single location. For each pickup/drop-off  point, set the travel time relative to the main location. Use a negative value (in minutes) if the pickup/drop-off  point is before the main location, or a positive value (in minutes) if it is after the main location. For example, use -15 for 15 minutes before the main location, or 20 for 20 minutes after it.', 'abp-transport-booking'),
                    //=============================//
                    'no_brand' => __('No Brand Found ! ', 'abp-transport-booking'),
                    'brand_name' => __('Note: Please enter a Brand name — the field cannot be empty. ', 'abp-transport-booking'),
                    'brand_slug' => __('Note: Brand slug is optional — leave it blank to auto-generate from the name. ', 'abp-transport-booking'),
                    'brand_des' => __('Note: Brand description  is optional — you can add details to better explain this Brand. ', 'abp-transport-booking'),
                    //=============================//
                    'no_feature' => __('No Feature Found ! ', 'abp-transport-booking'),
                    'feature_value' => __('Note: Please enter a Feature Value  — the field optional ', 'abp-transport-booking'),
                    'feature_icon' => __('Note: You can add an icon, or emoji for this Feature(optional).', 'abp-transport-booking'),
                    'feature_name' => __('Note: Please enter a Feature Name  — the field cannot be empty.', 'abp-transport-booking'),
                    //=============================//
                    'date_format' => __('Note:  If you want to change the Date  Format, simply choose a different format. The default date is: ', 'abp-transport-booking') . ' ' . date_i18n('D j M , Y', strtotime($current_date)),
                    'time_format' => __('Note : If you want to change the Time Format, simply choose a different format. The default Time Format is: ', 'abp-transport-booking') . ' ' . date_i18n(get_option('time_format'), strtotime($current_date)),
                    'sale_close_before' => __('Note:  Enter the time in minutes to close ticket sales before the transport starts. If not specified, it will default to 0 (e.g. 1 hour equals 60 minutes). ', 'abp-transport-booking'),
                    'advance_date_number' => __('Note: Kindly provide the number of days in advance for booking. By default, the advance booking period is set to 28 days.(optional) ', 'abp-transport-booking'),
                    'active_global_dates' => __('Note: Keep this switch ON to apply the global date settings.Switch it OFF if you want to set special date rules for this transport.Date configuration options will open when turned OFF. ', 'abp-transport-booking'),
                    'date_type' => __('Note: Please Select your Transport operational date type. Default operational date will be Periodic', 'abp-transport-booking'),
                    'specific_dates' => __('Note: Please add your Transport operational Specific Date lists  .', 'abp-transport-booking'),
                    'operation_time' => __('Note: Operation Time is required. If you do not specify any operation time, it will automatically be set to 12:00 AM (00:00). You can add multiple operation times for the same transport within a single day if needed. However, at least one operation time is required.', 'abp-transport-booking'),
                    'return_operation_time' => __('Note: Operation Time is required. If you do not specify any operation time, it will automatically be set to 12:00 AM (00:00). You can add multiple operation times for the same transport within a single day if needed. However, at least one operation time is required.', 'abp-transport-booking'),
                    'periodic_start_date' => __('Note: Please add your Transport Launching Date otherwise it will be start today ', 'abp-transport-booking'),
                    'periodic_end_date' => __('Note: Please add your Transport Terminate  Date otherwise it will be Continuously running periodically', 'abp-transport-booking'),
                    'periodic_after' => __('Note: Please add your periodically after days. if  your Transport operation day everyday this will be one(1).(optional)', 'abp-transport-booking'),
                    'date_rule' => __('Note: Enable this checkbox to configure special on/off date  settings. This option is optional. If you set a date/time in the special “On” date, that date will remain active even if it falls within an “Off” date range or on weekends.', 'abp-transport-booking'),
                    'special_on_dates' => __('Note: If you add any date  in Special On Dates, it will always remain active—even if that date falls within an off date range or on weekends.', 'abp-transport-booking'),
                    'weekend' => __('Note: Please select your weekend.Default all days open(optional)', 'abp-transport-booking'),
                    'day_wise_time' => __('Note:Add Day-wise Time if your transport operates on different schedules throughout the week. You can assign multiple departure times for each day, and only the configured times for the selected day will be available to passengers. ', 'abp-transport-booking'),
                    'specific_off_dates' => __('Note: please add your specific Operation off dates.(optional)', 'abp-transport-booking'),
                    'date_wise_time' => __('Note: Set the transport operation time for specific dates. A date will only be saved if it has at least one operation time. If a date is not saved, the regular day-wise schedule or the default operation time will be applied. You can add multiple operation times for the same date.(optional)', 'abp-transport-booking'),
                    'off_date_range' => __('Note: If you have off days between two dates which can add here.(optional)', 'abp-transport-booking'),
                    //=============================//
                    'qty_reserve_min_max' => __('Note: Set the total stock quantity available for sale. This field is required to save the transport. You can also set reserve, minimum, and maximum quantity limits for customer bookings. Reserve quantity keeps specific items unavailable, minimum quantity defaults to 1, and maximum quantity will follow the available stock if left empty.', 'abp-transport-booking'),
                    //=============================//
                    '_tax_class' => __('Note: If you want to add any new tax class , Please go to WooCommerce ->configuration->Tax Area', 'abp-transport-booking'),
                    'enable_tax_msg' => __('Note: Your Woo-commerce Tax setting already disable. If you want to enable tax please enable woo-commerce tax.', 'abp-transport-booking'),
                    //=============================//
                    'display_additional_services' => __('Note: If you want sale additional product/equipment with this  transport then active this button and add additional service. Additional item not depends on  operation time.', 'abp-transport-booking'),
                    'active_global_additional' => __('Note: Keep this switch ON to apply the global additional settings.Switch it OFF if you want to set special additional rules for this transport.additional configuration options will open when turned OFF. ', 'abp-transport-booking'),
                    //=============================//
                    'attendee_off' => __('Note: Globally, the Attendee Form feature is currently disabled. To add an attendee form for this transport, please enable the feature from the Global Settings, then reload this page.', 'abp-transport-booking'),
                    'client_form_option' => __('Use comma( , ) to separate option.', 'abp-transport-booking'),
                    'display_client_form' => __('Note: If you want to get Client information then active this button and add form/import global form or use global form as a client form', 'abp-transport-booking'),
                    'active_global_form' => __('Note: Keep this switch ON to apply the global Client Form settings.Switch it OFF if you want to set special  Client Form rules for this transport. Client Form configuration options will open when turned OFF. ', 'abp-transport-booking'),
                    'display_single_form' => __('If you want to get single traveller/attendee info for multiple ticket  then active this button .Default is on', 'abp-transport-booking'),
                    //=============================//
                    'display_tc' => __('Use this switch to control whether the Term & Condition is displayed on the frontend. Turn the switch ON to show the Term & Condition, and OFF to hide it. By default, this option is set to ON.', 'abp-transport-booking'),
                    'active_global_tc' => __('Enable this switch to apply the global Term & Condition to this post. If you want to add custom Term & Condition specifically for this post, turn the switch OFF and add your custom Term & Condition below.You can also use the Import button to bring in global Term & Condition, which you can then edit or delete based on your needs.', 'abp-transport-booking'),
                    //=============================//
                    'faq_item' => __('Both the Title and Description fields are required. If either field is left empty, this FAQ item will not be displayed on the frontend.', 'abp-transport-booking'),
                    'display_faq' => __('Use this switch to control whether the FAQ is displayed on the frontend. Turn the switch ON to show the FAQ, and OFF to hide it. By default, this option is set to ON.', 'abp-transport-booking'),
                    'active_global_faq' => __('Enable this switch to apply the global FAQ to this post. If you want to add custom FAQs specifically for this post, turn the switch OFF and add your custom FAQs below.You can also use the Import button to bring in global FAQs, which you can then edit or delete based on your needs.', 'abp-transport-booking'),
                    //=============================//
                    'search_get_wrong_data_info' => __('Somethings went Wrong ! Please Try again', 'abp-transport-booking'),
                    'sale_close_msg' => __('This transport sale close shortly. please try another transport.', 'abp-transport-booking'),
                    'not_date' => __('No Dates Found !', 'abp-transport-booking'),
                    'not_match' => __('No Results Found !', 'abp-transport-booking'),
                    'not_found' => __('Nothing Found !', 'abp-transport-booking'),
                    'no_sp' => __('No Seat Plan Found. Click Add New to create one.', 'abp-transport-booking'),
                    'transport_not_available' => __('The transport is not available for the selected date and time. Please choose a different schedule.', 'abp-transport-booking'),
                    //=============================//
                    'no_ticket_type' => __('No Ticket Type Found ! Please add Ticket Type to use Multiple Ticket Type', 'abp-transport-booking'),
                    'no_ticket_config' => __('No ticket configuration is available for this transport. Please contact the administrator.', 'abp-transport-booking'),
                    'no_sp_config' => __('No Seat Plan configuration is available for this transport. Please contact the administrator.', 'abp-transport-booking'),
                    'ticket_settings' => __('Configure the ticket or seat type with a name, color, prefix, and optional image, icon, or emoji. These settings will be applied automatically to all assigned seats in the Seat Plan and used throughout the booking process for consistent identification.', 'abp-transport-booking'), //=============================//
                    'no_decor_item' => __('No Decor Item Found ! Please add Decor item to use Multiple Decor item', 'abp-transport-booking'),
                    'decor_setting' => __('Note: Choose an image, icon, or emoji to represent this decoration item within the Seat Plan layout. Enter a name to identify the item while designing the layout. Decoration items are used only for creating and organizing the Seat Plan and are not considered bookable seats, so they do not affect pricing, availability, or the booking process. The item name will not be displayed to customers, but you can add custom text, change the font size, or modify individual items directly from the Seat Plan editor by double-clicking on them. You can also choose a background color to make different layout elements easier to identify while designing the seat arrangement. Once configured, the selected settings will automatically be applied wherever this decoration item is used.', 'abp-transport-booking'),
                    //=============================//
                    'must_wc' => __('Transport Booking is entirely dependent on the WooCommerce plugin. Please install and activate the WooCommerce plugin otherwise the plugin will not work. Installing this tool may take some time', 'abp-transport-booking'),
                    //=============================//
                    'abp_ticket' => __('Here you can create and manage dynamic ticket types that can be used across all transports. Examples include Economy, Business Class, VIP, Sleeper, Cabin, and more. Each ticket type can have its own price, capacity, color, icon, image, and other configurations. If a seat plan is enabled, you can create seats based on the selected ticket type, and every seat will automatically inherit the corresponding ticket settings. Every ticket type also has a unique ID . You can edit or delete ticket types at any time, but removing a ticket type that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    'abp_decor' => __('Note : Here you can create and manage decorative items for the seat plan layout. These items are used only for designing and organizing the seating arrangement and are not considered actual seats. Therefore, they cannot be booked, reserved, or sold. Examples include doors, exits, driver areas, engines, restrooms, food storage, baggage storage, waiting areas, lobbies, walkways, stairs, tables, partitions, and other decorative elements. If needed, you can also add custom text, icons, images, or emojis to create a more realistic seat layout. Every decoration item has its own unique ID and can be reused across multiple seat plans.', 'abp-transport-booking'),
                    'abp_sp' => __('Note : Here you can create and manage reusable seat plans that can be assigned to any transport multiple times. Instead of designing the same layout repeatedly, you can create a seat plan once and reuse it whenever needed. The system will automatically calculate the total number of available seats and detect all assigned ticket types from the selected seat plan. If the transport is configured to use a single ticket type, all seats in the selected seat plan will automatically be converted into that ticket type, even if the original layout contains multiple ticket types. You can also use decoration items such as doors, walkways, engines, exits, luggage storage areas, tables, and other custom elements to create a more realistic layout. Every seat plan has a unique ID', 'abp-transport-booking'),
                    'abp_sp_design' => __('Note : Here you can design and customize the entire seat layout according to your requirements. You can define the overall layout structure by setting the number of rows and columns, adjusting the background image or background color, and configuring the width, height, and spacing of individual cells. Each cell can occupy one or multiple positions, allowing you to create more complex layouts such as walkways, cabins, tables, lounges, storage areas, and other custom sections.You can select a ticket type and simply click on any cell to automatically assign the selected ticket type along with the corresponding seat number. Likewise, you can select a decoration item and place it anywhere in the layout. Both seat items and decoration items support drag-and-drop functionality, allowing you to move, duplicate, or clone them easily.Every cell can have its own custom text, icon, image, emoji, color, and font size. Double-clicking on a cell allows you to modify its content and appearance individually. You can also resize cells by defining custom width and height values.Advanced selection tools are also available to speed up the design process. Use Ctrl + Click to select individual items and Shift + Click to select multiple cells within a range. Once selected, you can apply changes to all selected items simultaneously.This powerful visual editor makes it easy to create simple or highly detailed layouts for buses, trains, ferries, aircraft, theaters, stadiums, conference halls, and many other seating arrangements. ', 'abp-transport-booking'),
                    'abp_dates' => __('Note: Set a global date configuration for your Transport  that can be reused across all posts, with options to import and customize anytime.', 'abp-transport-booking'),
                    'abp_additional' => __('Note: Add extra services for products/equipment with your transport—import or set per Post (also usable globally); stock applies per Post, empty quantity = unlimited, empty max qty = no limit, empty/Zero price = free.', 'abp-transport-booking'),
                    'abp_form' => __('Note: This is a flexibility global form system. Once you design the structure here, it serves as a global form. You can effortlessly import this form into any transport or use this setting at any transport,', 'abp-transport-booking'),
                    'abp_faq' => __('Note: You can set all transport-related FAQs here and use them globally across all transports. You can also import these FAQs into any individual transport and customize them as needed.', 'abp-transport-booking'),
                    'abp_tc' => __('Note: You can set all transport-related Term & Condition here and use them globally across all transport. You can also import these Term & Condition into any individual transport and customize them as needed.', 'abp-transport-booking'),
                    'abp_location' => __('Note: Here, you can add all of your transport stops, which can then be used across any transport post. You can edit or delete them at any time. However, please note that deleting a stop that is already assigned to a transport may affect the existing configuration. If a stop is not currently assigned anywhere, deleting it will not cause any issues.', 'abp-transport-booking'),
                    'abp_category' => __('Note : Here you can create and manage all transport types or categories, such as Bus, Train, Ferry, Shuttle, Taxi, AC, Non Ac, Sleeper and more. You can assign one  categories to each transport while creating or editing a post. Every category has a unique ID, which can also be used in shortcodes to display specific transport types anywhere on your website. You can edit or delete categories at any time, but removing a category that is already assigned to a transport may affect its existing configuration.', 'abp-transport-booking'),
                    'abp_organizer' => __('Note: Here you can create and manage transport organizers, operators, or companies. You can assign one organizers to each transport while creating or editing a post. Every organizer has a unique ID that can be used in shortcodes to display transports from a specific organizer anywhere on your website. You can edit or delete organizers at any time, but deleting an organizer that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    'abp_brand' => __('Note : Here you can create and manage transport brands, manufacturers, or service providers. You can assign one brand to each transport while creating or editing a post. Every brand has a unique ID that can be used in shortcodes to display transports from a specific brand anywhere on your website. You can edit or delete brands at any time, but deleting a brand that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    'abp_feature' => __('Note : Here you can create and manage transport features that help passengers understand the facilities and services available with each transport. Examples include Wi-Fi, air conditioning, charging ports, refreshments, entertainment systems, restrooms, and more. You can assign one or multiple features to each transport while creating or editing a post.  You can edit or delete features at any time, but removing a feature that is already assigned to a transport may affect the existing configuration.', 'abp-transport-booking'),
                    //=============================//
                    'sign_up_msg' => __('Please Login your account to Download/View ticket !', 'abp-transport-booking'),
                    'no_permit_msg' => __('You are not permitted to Download/View this ticket !', 'abp-transport-booking'),
                    'wrong_msg_id' => __('We see, this id are not valid !', 'abp-transport-booking'),
                    'no_order_found' => __('Sorry ! We can not find any Order in your criteria.', 'abp-transport-booking'),
                    //''          => __( '', 'abp-transport-booking' ),
                );
                $des = apply_filters('abp_info_array_filter', $des);
                return $des[$key] ?? '';
            }
            public static function svg($key): void {
                $des = [
                    'user_group_1' => '<svg  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"  d="M17 20c0-1.657-2.239-3-5-3s-5 1.343-5 3m14-3c0-1.23-1.234-2.287-3-2.75M3 17c0-1.23 1.234-2.287 3-2.75m12-4.014a3 3 0 1 0-4-4.472m-8 4.472a3 3 0 0 1 4-4.472M12 14a3 3 0 1 1 0-6a3 3 0 0 1 0 6Z"/></svg>',
                    'user_group_2' => '<svg  viewBox="0 0 24 24"> <path fill="currentColor" fill-rule="evenodd" d="M12 6a3.5 3.5 0 1 0 0 7a3.5 3.5 0 0 0 0-7m-1.5 8a4 4 0 0 0-4 4c0 1.1.9 2 2 2h7a2 2 0 0 0 2-2a4 4 0 0 0-4-4zm6.8-3.1a5.5 5.5 0 0 0-2.8-6.3c.6-.4 1.3-.6 2-.6a3.5 3.5 0 0 1 .8 6.9m2.2 7.1h.5a2 2 0 0 0 2-2a4 4 0 0 0-4-4h-1.1l-.5.8c1.9 1 3.1 3 3.1 5.2M4 7.5a3.5 3.5 0 0 1 5.5-2.9A5.5 5.5 0 0 0 6.7 11A3.5 3.5 0 0 1 4 7.5M7.1 12H6a4 4 0 0 0-4 4c0 1.1.9 2 2 2h.5a6 6 0 0 1 3-5.2z" clip-rule="evenodd"/></svg>',
                    'plus' => '<svg viewBox="0 0 16 16"><path fill="currentColor" d="M14 7H9V2H7v5H2v2h5v5h2V9h5V7z"/></svg>',
                    'minus_1' => '<svg viewBox="0 0 16 16"><path fill="currentColor" d="M2 7h12v2H2V7z"/></svg>',
                    'minus_2' => '<svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.707 10.295a2.41 2.41 0 0 0 0 3.41l7.588 7.588a2.41 2.41 0 0 0 3.41 0l7.588-7.588a2.41 2.41 0 0 0 0-3.41l-7.588-7.588a2.41 2.41 0 0 0-3.41 0zM8.5 12h7"/></svg>',
                    'minus_3' => '<svg viewBox="0 0 32 32"><path fill="currentColor" d="M16 3C8.832 3 3 8.832 3 16s5.832 13 13 13s13-5.832 13-13S23.168 3 16 3zm0 2c6.087 0 11 4.913 11 11s-4.913 11-11 11S5 22.087 5 16S9.913 5 16 5zm-6 10v2h12v-2H10z"/></svg>',
                    'minus_4' => '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M299 213H0v-42h299v42z"/></svg>',
                    'save' => '<svg  viewBox="0 0 100 100.016"><path fill="#23475F" d="M88.555 0H83v.016a2 2 0 0 1-2 2H19a2 2 0 0 1-2-2V0H4a4 4 0 0 0-4 4v92.016a4 4 0 0 0 4 4h92a4 4 0 0 0 4-4V11.525C100.049 11.436 88.564.071 88.555 0z"/><path fill="#1C3C50" d="M81.04 53.016H18.96a2 2 0 0 0-2 2v45h66.08v-45c0-1.106-.895-2-2-2zm-61.957-10h61.834a2 2 0 0 0 2-2V.555A1.993 1.993 0 0 1 81 2.015H19c-.916 0-1.681-.62-1.917-1.46v40.46a2 2 0 0 0 2 2.001z"/><path fill="#EBF0F1" d="M22 55.985h56a2 2 0 0 1 2 2v37.031a2 2 0 0 1-2 2H22c-1.104 0-2-.396-2-1.5V57.985a2 2 0 0 1 2-2z"/><path fill="#BCC4C8" d="M25 77.016h50v1H25v-1zm0 10h50v1H25v-1z"/><path fill="#1C3C50" d="M7 84.016h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2zm83 0h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-3a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2z"/><path fill="#BCC4C8" d="M37 1.989v36.026a2 2 0 0 0 2 2h39a2 2 0 0 0 2-2V1.989c0 .007-42.982.007-43 0zm37 29.027a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2V10.989a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v20.027z"/><path fill="#FF9D00" d="M78 55.985H22a2 2 0 0 0-2 2v10.031h60V57.985a2 2 0 0 0-2-2z"/></svg>',
                    'edit' => '<svg  viewBox="0 0 16 16"><path fill="currentColor" d="M15.49 7.3h-1.16v6.35H1.67V3.28H8V2H1.67A1.21 1.21 0 0 0 .5 3.28v10.37a1.21 1.21 0 0 0 1.17 1.25h12.66a1.21 1.21 0 0 0 1.17-1.25z"/><path fill="currentColor" d="M10.56 2.87L6.22 7.22l-.44.44l-.08.08l-1.52 3.16a1.08 1.08 0 0 0 1.45 1.45l3.14-1.53l.53-.53l.43-.43l4.34-4.36l.45-.44l.25-.25a2.18 2.18 0 0 0 0-3.08a2.17 2.17 0 0 0-1.53-.63a2.19 2.19 0 0 0-1.54.63l-.7.69l-.45.44zM5.51 11l1.18-2.43l1.25 1.26zm2-3.36l3.9-3.91l1.3 1.31L8.85 9zm5.68-5.31a.91.91 0 0 1 .65.27a.93.93 0 0 1 0 1.31l-.25.24l-1.3-1.3l.25-.25a.88.88 0 0 1 .69-.25z"/></svg>',
                    'drag' => '<svg  viewBox="0 0 16 16"><path fill="currentColor" d="m15.46 7l-3.2-2.19l-.71 1l2.29 1.57H8.62V2.16l1.57 2.29l1-.71L9 .54a1.25 1.25 0 0 0-2 0l-2.22 3.2l1 .71l1.59-2.29v5.22H2.16l2.29-1.57l-.71-1L.54 7a1.25 1.25 0 0 0 0 2l3.2 2.19l.71-1l-2.29-1.57h5.21v5.22l-1.56-2.29l-1 .71L7 15.46a1.25 1.25 0 0 0 2.06 0l2.19-3.2l-1-.71l-1.63 2.29V8.62h5.22l-2.29 1.57l.71 1L15.46 9a1.25 1.25 0 0 0 0-2z"/></svg>',
                    'order' => '<svg  viewBox="0 0 16 16"> <path fill="none" stroke="currentColor" stroke-linejoin="round" d="M5 11.5h4M5 9h6M5 6.5h6m-5.5-4h-2v12h9v-12h-2m-5-1h5l-.625 2h-3.75z"/></svg>',
                    'seat' => '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M4 21v-6h16v6h-2v-4H6v4H4Zm-1-7v-3h3v3H3Zm4 0V3h10v11H7Zm11 0v-3h3v3h-3Z"/></svg>',
                    'globe' => '<svg viewBox="0 0 100 100"><path id="gisGlobe0" fill="currentColor" fill-opacity="1" fill-rule="nonzero" stroke="none" stroke-dasharray="none" stroke-dashoffset="188.976" stroke-linecap="round" stroke-linejoin="round" stroke-miterlimit="4" stroke-opacity="1" stroke-width="5" d="M52.5 5.682v20.187h17.676c-.988-2.823-2.13-5.429-3.408-7.75c-3.966-7.2-9-11.541-14.268-12.437zm-5 .197c-4.93 1.223-9.61 5.462-13.342 12.24c-1.278 2.321-2.42 4.927-3.408 7.75H47.5V5.88zM35.98 7.232C25.985 10.5 17.545 17.163 12.01 25.87h13.455c1.187-3.695 2.633-7.112 4.312-10.162c1.793-3.255 3.88-6.123 6.203-8.475zm29.41.463c2.145 2.263 4.082 4.967 5.758 8.012c1.68 3.05 3.123 6.467 4.307 10.162H87.99c-5.28-8.306-13.202-14.761-22.6-18.174zM9.257 30.87A44.79 44.79 0 0 0 5.072 47.5h16.79c.194-5.872.957-11.469 2.202-16.63H9.256zm19.974 0c-1.32 5.077-2.15 10.696-2.363 16.631H47.5V30.87H29.23zm23.27 0V47.5H74.06c-.212-5.935-1.043-11.554-2.364-16.63H52.5zm24.355 0c1.243 5.163 2.004 10.76 2.198 16.631h15.875a44.79 44.79 0 0 0-4.184-16.63h-13.89zM5.072 52.5a44.79 44.79 0 0 0 4.184 16.63h14.572c-1.174-5.176-1.865-10.774-1.994-16.63H5.072zm21.762 0c.14 5.915.901 11.53 2.146 16.63H47.5V52.5H26.834zm25.666 0v16.63h19.445c1.245-5.1 2.006-10.715 2.147-16.63H52.5zm26.576 0c-.129 5.855-.815 11.453-1.986 16.63h13.654a44.79 44.79 0 0 0 4.184-16.63H79.076zM12.01 74.13c5.285 8.313 13.214 14.772 22.62 18.183c-1.785-2.05-3.415-4.407-4.853-7.018c-1.83-3.325-3.389-7.08-4.63-11.164H12.01zm18.394 0c1.062 3.216 2.326 6.159 3.754 8.753c3.5 6.355 7.834 10.475 12.424 11.974c.306.023.61.054.918.07V74.132H30.404zm22.096 0v20.798a45.48 45.48 0 0 0 2.127-.162c4.485-1.575 8.713-5.658 12.14-11.883c1.429-2.594 2.693-5.537 3.754-8.752H52.5zm23.275 0c-1.239 4.085-2.796 7.84-4.627 11.165c-1.311 2.382-2.782 4.556-4.386 6.476a45.06 45.06 0 0 0 21.228-17.64H75.775z" color="currentColor" color-interpolation="sRGB" color-rendering="auto" display="inline" opacity="1" vector-effect="none" visibility="visible"/></svg>',
                    'setting' => '<svg viewBox="0 0 24 24"><g fill="none" fill-rule="evenodd"><path d="M24 0v24H0V0h24ZM12.594 23.258l-.012.002l-.071.035l-.02.004l-.014-.004l-.071-.036c-.01-.003-.019 0-.024.006l-.004.01l-.017.428l.005.02l.01.013l.104.074l.015.004l.012-.004l.104-.074l.012-.016l.004-.017l-.017-.427c-.002-.01-.009-.017-.016-.018Zm.264-.113l-.014.002l-.184.093l-.01.01l-.003.011l.018.43l.005.012l.008.008l.201.092c.012.004.023 0 .029-.008l.004-.014l-.034-.614c-.003-.012-.01-.02-.02-.022Zm-.715.002a.023.023 0 0 0-.027.006l-.006.014l-.034.614c0 .012.007.02.017.024l.015-.002l.201-.093l.01-.008l.003-.011l.018-.43l-.003-.012l-.01-.01l-.184-.092Z"/><path fill="currentColor" d="M16 15c1.306 0 2.418.835 2.83 2H20a1 1 0 1 1 0 2h-1.17a3.001 3.001 0 0 1-5.66 0H4a1 1 0 1 1 0-2h9.17A3.001 3.001 0 0 1 16 15Zm0 2a1 1 0 1 0 0 2a1 1 0 0 0 0-2ZM8 9a3 3 0 0 1 2.762 1.828l.067.172H20a1 1 0 0 1 .117 1.993L20 13h-9.17a3.001 3.001 0 0 1-5.592.172L5.17 13H4a1 1 0 0 1-.117-1.993L4 11h1.17A3.001 3.001 0 0 1 8 9Zm0 2a1 1 0 1 0 0 2a1 1 0 0 0 0-2Zm8-8c1.306 0 2.418.835 2.83 2H20a1 1 0 1 1 0 2h-1.17a3.001 3.001 0 0 1-5.66 0H4a1 1 0 0 1 0-2h9.17A3.001 3.001 0 0 1 16 3Zm0 2a1 1 0 1 0 0 2a1 1 0 0 0 0-2Z"/></g></svg>',
                    'status' => '<svg  viewBox="0 0 24 24"><path fill="currentColor" fill-rule="evenodd" d="M16.001 5.2q-.262.385-.445.82a7 7 0 0 0-.157.405c-.165.461-.27.881-.373 1.303l-.005.019c-.132.533-.264 1.067-.525 1.687a5 5 0 0 1-.48.88l-.006.006l-.002.004c2.325.684 4.925-.224 5.868-2.452c.58-1.368.257-3.08-1.052-3.672c-1.054-.476-2.088-.086-2.822 1M3.184 12.023q.147-.447.38-.854q.111-.194.224-.37c.264-.408.532-.742.8-1.076l.026-.031c.335-.42.672-.84.998-1.414q.234-.407.386-.854l.006-.019l.018-.058c1.897 1.55 2.71 4.266 1.52 6.362c-.729 1.289-2.258 2.021-3.487 1.268c-.99-.605-1.29-1.702-.871-2.954M21 17.251c-2.51 0-4.544-2.108-4.544-4.708h-2.654C13.802 16.662 17.025 20 21 20zm-8.663-4.708c0 4.119-3.223 7.457-7.198 7.457v-2.75c2.51 0 4.544-2.107 4.544-4.707zm0 0l-.002-8.324H9.68l.002 8.324z" clip-rule="evenodd"/></svg>',
                    'category' => '<svg  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="12" rx="2"/>  <path d="M3 9h18"/>  <path d="M7 16v2"/>  <path d="M17 16v2"/><circle cx="7" cy="18.5" r="0.5" fill="currentColor" stroke="none"/>  <circle cx="17" cy="18.5" r="0.5" fill="currentColor" stroke="none"/></svg>',
                    'category_1' => '<svg  viewBox="0 0 24 24" fill="none">  <rect x="4" y="7" width="16" height="12" rx="2" stroke="currentColor" stroke-width="2"/>  <line x1="4" y1="11" x2="20" y2="11" stroke="currentColor" stroke-width="2"/>  <line x1="8" y1="4" x2="8" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>  <line x1="16" y1="4" x2="16" y2="9" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>  <circle cx="8.5" cy="15" r="1.3" fill="currentColor"/></svg>',
                    'brand_1' => '<svg  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l7 3v6c0 5-3 8.5-7 11-4-2.5-7-6-7-11V5z"/>  <path d="M9 12l2 2 4-4"/></svg>',
                    'brand_2' => '<svg  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="1"/><line x1="8" y1="7" x2="8" y2="7.01"/><line x1="12" y1="7" x2="12" y2="7.01"/><line x1="16" y1="7" x2="16" y2="7.01"/><line x1="8" y1="11" x2="8" y2="11.01"/><line x1="12" y1="11" x2="12" y2="11.01"/><line x1="16" y1="11" x2="16" y2="11.01"/><path d="M9 21v-4h6v4"/></svg>',
                    'organizer_1' => '<svg  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.5"/>  <path d="M5 20c0-3.5 3-6 7-6s7 2.5 7 6"/>  <path d="M17.5 4.5l1 1 2-2"/></svg>',
                    'organizer_2' => '<svg  viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="2"/><path d="M8 8h2"/><path d="M8 12h2"/><path d="M8 16h2"/><path d="M14 8l1.5 1.5L18 7"/><path d="M14 12l1.5 1.5L18 11"/><path d="M14 16l1.5 1.5L18 15"/></svg>',
                    'date_1' => '<svg viewBox="0 0 48 48">    <path fill="#CFD8DC" d="M5 38V14h38v24c0 2.2-1.8 4-4 4H9c-2.2 0-4-1.8-4-4z"/>    <path fill="#F44336" d="M43 10v6H5v-6c0-2.2 1.8-4 4-4h30c2.2 0 4 1.8 4 4z"/>    <g fill="#B71C1C">        <circle cx="33" cy="10" r="3"/>        <circle cx="15" cy="10" r="3"/>    </g>    <path fill="#B0BEC5" d="M33 3c-1.1 0-2 .9-2 2v5c0 1.1.9 2 2 2s2-.9 2-2V5c0-1.1-.9-2-2-2zM15 3c-1.1 0-2 .9-2 2v5c0 1.1.9 2 2 2s2-.9 2-2V5c0-1.1-.9-2-2-2z"/>    <path fill="#90A4AE" d="M13 20h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm-18 6h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm-18 6h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4z"/></svg>',
                    'date_2' => '<svg  viewBox="0 0 64 64">    <path fill="#ba9372" d="M58.1 19.6c-9.8-6-25-14.8-27.1-14.8c-2.1 0-16.7 8.9-26 14.8l-.5-.9C6.9 17.2 28 3.8 31 3.8s25.1 13.4 27.6 15l-.5.8"/>    <path fill="#93a2aa" d="M62 56.8c0 1.6-1.2 3-2.7 3H6.8c-1.5 0-2.7-1.3-2.7-3V21.1c0-1.6 1.2-3 2.7-3h52.5c1.5 0 2.7 1.3 2.7 3v35.7"/>    <path fill="#ed4c5c" d="M60 21.1c0-1.6-1.2-3-2.7-3H4.7c-1.5 0-2.7 1.3-2.7 3v9.5h58v-9.5z"/>    <path fill="#d9e3e8" d="M2 30.6v26.2c0 1.6 1.2 3 2.7 3h52.5c1.5 0 2.7-1.3 2.7-3V30.6H2z"/> <path fill="#93a2aa" d="M4.5 33h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zM28 37.4h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm-47 4.4h6V44h-6zm7.8 0h6V44h-6zm7.9 0h6V44h-6zm7.8 0h6V44h-6zm7.8 0h6V44h-6zm7.9 0h6V44h-6zm7.8 0h6V44h-6zm-47 4.5h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm-47 4.4h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm-47 4.4h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.8 0h6v2.2h-6zm7.9 0h6v2.2h-6z"/><ellipse cx="31.2" cy="6.2" fill="#333" rx="1.8" ry="1.9"/><ellipse cx="31" cy="6.2" fill="#93a2aa" rx="1.8" ry="1.9"/>    <path fill="#fff" d="M19.5 25.5v.2c0 .6.1 1 .2 1.3c.1.2.3.4.7.4c.4 0 .6-.1.7-.4c.1-.2.1-.4.1-.8v-5.5h1.6v5.4c0 .7-.1 1.2-.3 1.6c-.4.7-1 1-2 1s-1.6-.3-2-.8c-.3-.5-.5-1.3-.5-2.2v-.2h1.5m5-4.8h1.6v4.8c0 .5.1.9.2 1.2c.2.4.6.7 1.3.7c.6 0 1.1-.2 1.3-.7c.1-.3.1-.7.1-1.2v-4.8h1.6v4.8c0 .8-.1 1.5-.4 1.9c-.5.8-1.4 1.3-2.7 1.3c-1.3 0-2.2-.4-2.7-1.3c-.3-.5-.4-1.1-.4-1.9c.1 0 .1-4.8.1-4.8m7.7 0h1.6v6.4h3.8v1.4h-5.4v-7.8m10 0H44l-2.6 4.9v2.9h-1.6v-2.9l-2.7-4.9H39l1.6 3.4l1.6-3.4"/></svg>',
                    'clone_1' => '<svg viewBox="0 0 1792 1792"><path fill="currentColor" d="M1664 1632V544q0-13-9.5-22.5T1632 512H544q-13 0-22.5 9.5T512 544v1088q0 13 9.5 22.5t22.5 9.5h1088q13 0 22.5-9.5t9.5-22.5zm128-1088v1088q0 66-47 113t-113 47H544q-66 0-113-47t-47-113V544q0-66 47-113t113-47h1088q66 0 113 47t47 113zm-384-384v160h-128V160q0-13-9.5-22.5T1248 128H160q-13 0-22.5 9.5T128 160v1088q0 13 9.5 22.5t22.5 9.5h160v128H160q-66 0-113-47T0 1248V160Q0 94 47 47T160 0h1088q66 0 113 47t47 113z"/></svg>',
                    'clone_2' => '<svg viewBox="0 0 36 36"><path fill="currentColor" d="M24 10V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h4V12a2 2 0 0 1 2-2Z" class="clr-i-solid clr-i-solid-path-1"/>    <path fill="currentColor" d="M30 12H14a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V14a2 2 0 0 0-2-2Zm-2 11h-5v5h-2v-5h-5v-2h5v-5h2v5h5Z" class="clr-i-solid clr-i-solid-path-2"/>    <path fill="none" d="M0 0h36v36H0z"/></svg>',
                    'close_1' => '<svg viewBox="0 0 304 384"><path fill="currentColor" d="M299 73L179 192l120 119l-30 30l-120-119L30 341L0 311l119-119L0 73l30-30l119 119L269 43z"/></svg>',
                    'close_2' => '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2m-3.4 14L12 13.4L8.4 17L7 15.6l3.6-3.6L7 8.4L8.4 7l3.6 3.6L15.6 7L17 8.4L13.4 12l3.6 3.6l-1.4 1.4Z"/></svg>',
                    'view_1' => '<svg viewBox="0 0 24 24" stroke="currentColor"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M2.25.75h19.5s1.5 0 1.5 1.5v19.5s0 1.5-1.5 1.5H2.25s-1.5 0-1.5-1.5V2.25s0-1.5 1.5-1.5"/><path d="M4.267 10.722a1.825 1.825 0 0 0 0 2.544C5.818 14.821 8.591 17.25 12 17.25c3.41 0 6.183-2.428 7.735-3.983a1.825 1.825 0 0 0 0-2.544C18.182 9.168 15.406 6.739 12 6.739s-6.18 2.427-7.733 3.983"/><path d="M9.75 11.991a2.25 2.25 0 1 0 4.5 0a2.25 2.25 0 0 0-4.5 0"/></g></svg>',
                    'view_2' => '<svg viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-width="2" d="M12 21c-5 0-11-5-11-9s6-9 11-9s11 5 11 9s-6 9-11 9Zm0-14a5 5 0 1 0 0 10a5 5 0 0 0 0-10Z"/></svg>',
                    'view_3' => '<svg viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M5 12s2.545-5 7-5c4.454 0 7 5 7 5s-2.546 5-7 5c-4.455 0-7-5-7-5z"/><path d="M12 13a1 1 0 1 0 0-2a1 1 0 0 0 0 2zm9 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2M21 7V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2"/></g></svg>',
                    'pdf_1' => '<svg  viewBox="0 0 48 48"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M7.45 5.5a2 2 0 0 0-1.95 2v33.1a2 2 0 0 0 2 2h33.1a2 2 0 0 0 2-2V7.45a2 2 0 0 0-2-1.95Z"/><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" d="M20.09 30V18h2a6 6 0 0 1 6 6h0a6 6 0 0 1-6 6Zm12.39-11.96h5.98m-5.98 5.98h3.9m-3.9-5.98V30M9.54 30V18h4a4 4 0 0 1 0 8h-4"/></svg>',
                    'pdf_2' => '<svg  viewBox="0 0 24 24" fill="none">  <path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="#e53e3e"/>  <path d="M15 2v5h5" fill="#ffffff" opacity="0.4"/>  <rect x="4" y="14" width="16" height="6" rx="1" fill="#c53030"/>  <text x="12" y="18.5" text-anchor="middle" font-size="6" font-weight="700" fill="#ffffff" font-family="Arial, sans-serif">PDF</text></svg>',
                    'pdf_3' => '<svg viewBox="0 0 24 24" fill="none">  <circle cx="12" cy="12" r="11" fill="#e53e3e"/>  <path d="M8 7h6l3 3v7a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z" fill="#ffffff"/>  <text x="12" y="15.5" text-anchor="middle" font-size="5" font-weight="700" fill="#e53e3e" font-family="Arial, sans-serif">PDF</text></svg>',
                    'pdf_4' => '<svg  viewBox="0 0 32 32" fill="none">  <path d="M7 2h13l7 7v19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="currentColor"/>  <path d="M20 2v7h7" fill="#f87171"/>  <rect x="8" y="12" width="10" height="1.5" rx="0.75" fill="#ffffff" opacity="0.6"/>  <rect x="8" y="16" width="14" height="1.5" rx="0.75" fill="#ffffff" opacity="0.6"/>  <rect x="4" y="21" width="20" height="8" rx="1.5" fill="#991b1b"/>  <text x="14" y="27" text-anchor="middle" font-size="8" font-weight="700" fill="#ffffff" font-family="Arial, sans-serif">PDF</text></svg>',
                    'property' => '<svg viewBox="0 0 120 125" fill="none" stroke="currentColor" stroke-width="4" stroke-linejoin="round" stroke-linecap="round"><path d="M5 55 L 60 15 L 115 55"/><rect x="18" y="50" width="84" height="70" rx="2"/><rect x="50" y="80" width="24" height="40"/> <rect x="30" y="62" width="16" height="16"/> <rect x="74" y="62" width="16" height="16"/></svg>',
                    'check_1' => '<svg  viewBox="0 0 16 16"><path fill="currentColor" d="m5.063 2.616l-1.47.002l-.147.005a1.977 1.977 0 0 0-1.828 1.971l-.002 1.469l-1.038 1.04l-.115.126A1.977 1.977 0 0 0 .58 9.898l1.037 1.037l.002 1.471l.005.148a1.977 1.977 0 0 0 1.971 1.828l1.468-.001l1.04 1.04l.126.116a1.977 1.977 0 0 0 2.67-.116l1.036-1.04l1.471.001l.148-.005a1.977 1.977 0 0 0 1.83-1.971l-.002-1.472l1.04-1.036l.115-.126a1.977 1.977 0 0 0-.115-2.67l-1.04-1.04l.001-1.467l-.005-.148a1.977 1.977 0 0 0-1.971-1.83l-1.471-.001l-1.037-1.037a1.976 1.976 0 0 0-2.796 0zm3.127-.33l1.331 1.331h1.885c.539 0 .976.438.976.977v1.883l1.332 1.333c.381.38.381 1 0 1.38l-1.333 1.331l.001 1.885a.976.976 0 0 1-.976.976H9.52l-1.33 1.332a.976.976 0 0 1-1.381 0l-1.333-1.333l-1.883.001a.976.976 0 0 1-.976-.976l-.001-1.885l-1.331-1.33a.976.976 0 0 1 0-1.381l1.331-1.333V4.594c0-.539.438-.976.977-.976l1.883-.001L6.81 2.286a.976.976 0 0 1 1.38 0m2.664 3.86a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708 0l-2-2a.5.5 0 1 1 .708-.708L6.5 9.793l3.646-3.647a.5.5 0 0 1 .708 0"/></svg>',
                    'check_2' => '<svg   viewBox="0 0 24 24"><path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m4 12l4.95 4.95L19.557 6.343"/></svg>',
                    'check_3' => '<svg  viewBox="0 0 16 16"><path fill="currentColor" d="M14 1H2c-.55 0-1 .45-1 1v12c0 .55.45 1 1 1h12c.55 0 1-.45 1-1V2c0-.55-.45-1-1-1m-1.56 5.44l-5 5c-.12.12-.28.18-.44.18s-.32-.06-.44-.18l-2.5-2.5c-.24-.24-.24-.64 0-.88s.64-.24.88 0L7 10.12l4.56-4.56c.24-.24.64-.24.88 0c.25.24.25.64 0 .88"/></svg>',
                    'check_4' => '<svg  viewBox="0 0 12 12">    <g fill="none" stroke="currentColor">        <rect width="11" height="11" x=".5" y=".5" rx=".5" ry=".5"/>        <path stroke-linecap="round" stroke-linejoin="round" d="m3 6.5l2 2l4-4"/>    </g></svg>',
                    'box_1' => '<svg  viewBox="0 0 24 24">    <path fill="currentColor" d="M5 21q-.825 0-1.413-.588T3 19V5q0-.825.588-1.413T5 3h14q.825 0 1.413.588T21 5v14q0 .825-.588 1.413T19 21H5Zm0-2h14V5H5v14Z"/></svg>',
                    'box_2' => '<svg viewBox="0 0 24 24">    <path fill="currentColor" d="M4 20V4h16v16H4Zm1-1h14V5H5v14Z"/></svg>',
                    'csv_1' => '<svg  viewBox="0 0 32 32" fill="none">  <path d="M7 2h13l7 7v19a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="currentColor"/>  <path d="M20 2v7h7" fill="#4ade80"/>  <rect x="8" y="12" width="10" height="1.5" rx="0.75" fill="#ffffff" opacity="0.6"/>  <rect x="8" y="16" width="14" height="1.5" rx="0.75" fill="#ffffff" opacity="0.6"/>  <rect x="4" y="21" width="20" height="8" rx="1.5" fill="#166534"/>  <text x="14" y="27" text-anchor="middle" font-size="8" font-weight="700" fill="#ffffff" font-family="Arial, sans-serif">CSV</text></svg>',
                    'csv_2' => '<svg  viewBox="0 0 24 24" fill="none" >  <circle cx="12" cy="12" r="11" fill="currentColor"/>  <path d="M8 7h6l3 3v7a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z" fill="currentColor"/>  <text x="12" y="15.5" text-anchor="middle" font-size="5" font-weight="700" fill="#38a169" font-family="Arial, sans-serif">CSV</text></svg>',
                    'csv_3' => '<svg  viewBox="0 0 24 24" fill="none">  <path d="M6 2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2z" fill="currentColor"/>  <path d="M15 2v5h5" fill="#ffffff" opacity="0.4"/>  <rect x="4" y="14" width="16" height="6" rx="1" fill="#2f855a"/>  <text x="12" y="18.5" text-anchor="middle" font-size="6" font-weight="700" fill="#ffffff" font-family="Arial, sans-serif">CSV</text></svg>',
                    '' => '',
                ];
                $allowed_svg_tags = [
                    'svg' => ['class' => true, 'aria-hidden' => true, 'aria-labelledby' => true, 'role' => true, 'xmlns' => true, 'width' => true, 'height' => true, 'viewbox' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,], 'g' => ['fill' => true,], 'title' => ['title' => true,], 'path' => ['d' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linecap' => true, 'stroke-linejoin' => true,], 'rect' => ['x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true,],
                    'circle' => ['cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true,],
                    'line' => ['x1' => true, 'y1' => true, 'x2' => true, 'y2' => true, 'stroke' => true, 'stroke-width' => true,],
                    'polyline' => ['points' => true, 'fill' => true, 'stroke' => true,],
                ];
                $icons = $des[$key] ?? '';
                echo !empty($icons) ? wp_kses($icons, $allowed_svg_tags) : '';
            }
            public static function static_additional(): array {
                return [
                    'as_1' => ['icon' => 'fas fa-suitcase-rolling', 'name' => 'Free Luggage', 'qty' => 50, 'max_qty' => 1, 'price' => 0, 'description' => '5 kg · 20×10×10 cm (Maximum one)', 'returnable' => 'no',],
                    'as_2' => ['icon' => 'fas fa-suitcase-rolling', 'name' => 'Additional luggage', 'qty' => 50, 'max_qty' => 2, 'price' => 4.99, 'description' => '20 kg · 80×50×30 cm', 'returnable' => 'no',],
                    'as_3' => ['icon' => 'fas fa-suitcase', 'name' => 'Bulky baggage', 'qty' => 30, 'max_qty' => 3, 'price' => 7.99, 'description' => '50 kg · 80×50×30 cm', 'returnable' => 'yes',],
                    'as_4' => ['icon' => 'fas fa-coffee', 'name' => 'Coffee', 'qty' => 100, 'price' => 2.00, 'description' => 'Price for a cup of coffee', 'returnable' => 'no',]
                ];
            }
            public static function static_form($key = ''): array {
                $form['pass_name'] = ['type' => 'text', 'required' => 'on', 'label' => __('First Name', 'abp-transport-booking')];
                $form['pass_name_2'] = ['type' => 'text', 'required' => 'on', 'label' => __('Last Name', 'abp-transport-booking')];
                $form['pass_email'] = ['type' => 'email', 'required' => 'on', 'label' => __('E-Mail', 'abp-transport-booking')];
                $form['pass_phone'] = ['type' => 'text', 'required' => 'on', 'label' => __('Phone', 'abp-transport-booking')];
                $form['pass_gender'] = ['type' => 'select', 'required' => 'off', 'label' => __('Gender', 'abp-transport-booking'), 'option' => 'male,female'];
                $form['pass_date'] = ['type' => 'date', 'required' => 'off', 'label' => __('Date of Birth', 'abp-transport-booking')];
                if (!is_string($key) && !is_int($key)) {
                    return $form;
                }
                if ($key === '') {
                    return $form;
                }
                return is_array($form[$key] ?? null) ? $form[$key] : [];
            }
            public static function static_location(): array {
                return [
                    0 => ['name' => 'New York City'],
                    1 => ['name' => 'Los Angeles'],
                    2 => ['name' => 'Chicago'],
                    3 => ['name' => 'Houston'],
                    4 => ['name' => 'Phoenix'],
                    5 => ['name' => 'Philadelphia'],
                    6 => ['name' => 'San Antonio'],
                    7 => ['name' => 'San Diego'],
                    8 => ['name' => 'Dallas'],
                    9 => ['name' => 'San Jose'],
                    10 => ['name' => 'Austin'],
                    11 => ['name' => 'Jacksonville'],
                    12 => ['name' => 'Fort Worth'],
                    13 => ['name' => 'Columbus'],
                    14 => ['name' => 'Charlotte'],
                    15 => ['name' => 'Indianapolis'],
                    16 => ['name' => 'Seattle'],
                    17 => ['name' => 'Denver'],
                    18 => ['name' => 'Washington, D.C.'],
                    19 => ['name' => 'Boston'],
                    20 => ['name' => 'Nashville'],
                    21 => ['name' => 'Detroit'],
                    22 => ['name' => 'Portland'],
                    23 => ['name' => 'Las Vegas'],
                    24 => ['name' => 'Memphis'],
                    25 => ['name' => 'Baltimore'],
                    26 => ['name' => 'Milwaukee'],
                    27 => ['name' => 'Albuquerque'],
                    28 => ['name' => 'Tucson'],
                    29 => ['name' => 'Fresno'],
                    30 => ['name' => 'Sacramento'],
                    31 => ['name' => 'Kansas City'],
                    32 => ['name' => 'Atlanta'],
                    33 => ['name' => 'Miami'],
                    34 => ['name' => 'Orlando'],
                    35 => ['name' => 'Tampa'],
                    36 => ['name' => 'New Orleans'],
                    37 => ['name' => 'Cleveland'],
                    38 => ['name' => 'Pittsburgh'],
                    39 => ['name' => 'Cincinnati'],
                    40 => ['name' => 'Minneapolis'],
                    41 => ['name' => 'St. Louis'],
                    42 => ['name' => 'Salt Lake City'],
                    43 => ['name' => 'Raleigh'],
                    44 => ['name' => 'Richmond'],
                    45 => ['name' => 'Buffalo'],
                    46 => ['name' => 'Providence'],
                    47 => ['name' => 'Honolulu'],
                    48 => ['name' => 'Anchorage'],
                    49 => ['name' => 'Charleston'],
                    50 => ['name' => 'Tacoma'],
                    51 => ['name' => 'Olympia'],
                    52 => ['name' => 'Bakersfield'],
                    53 => ['name' => 'South Bend'],
                    54 => ['name' => 'Toledo'],
                    55 => ['name' => 'New Haven'],
                    56 => ['name' => 'Corsicana'],
                    57 => ['name' => 'Huntsville'],
                    58 => ['name' => 'San Francisco'],
                ];
            }
            public static function static_category(): array {
                return [
                    0 => ['name' => 'AC'],
                    1 => ['name' => 'Non AC'],
                    2 => ['name' => 'Economy'],
                    3 => ['name' => 'Business'],
                    4 => ['name' => 'VIP'],
                    5 => ['name' => 'Sleeper'],
                    6 => ['name' => 'Express'],
                    7 => ['name' => 'Local'],
                    8 => ['name' => 'Luxury'],
                    9 => ['name' => 'Shuttle'],
                ];
            }
            public static function static_organizer(): array {
                return [
                    0 => ['name' => 'Global Transit Group'],
                    1 => ['name' => 'Express Travel Network'],
                    2 => ['name' => 'Premium Coach Services'],
                    3 => ['name' => 'InterCity Transport'],
                    4 => ['name' => 'Continental Bus Lines'],
                    5 => ['name' => 'Smart Mobility Solutions'],
                ];
            }
            public static function static_brand(): array {
                return [
                    0 => ['name' => 'Mercedes-Benz'],
                    1 => ['name' => 'Volvo'],
                    2 => ['name' => 'Scania'],
                    3 => ['name' => 'MAN'],
                    4 => ['name' => 'IVECO'],
                    5 => ['name' => 'Setra'],
                    6 => ['name' => 'Yutong'],
                    7 => ['name' => 'King Long'],
                    8 => ['name' => 'Alexander Dennis'],
                    9 => ['name' => 'VDL Bus & Coach'],
                ];
            }
            public static function static_ticket(): array {
                return [
                    1 => ['label' => 'Ticket', 'color' => '', 'prefix' => '', 'icon' => '🎟️', 'type' => 'seat',],
                    2 => ['label' => 'VIP', 'color' => '#A78BFA', 'prefix' => 'VIP-', 'icon' => '👑', 'type' => 'seat',],
                    3 => ['label' => 'Business Class', 'color' => '#0EA5E9', 'prefix' => 'B-', 'icon' => '🛋️', 'type' => 'seat',],
                    4 => ['label' => 'Special', 'color' => '#6366F1', 'prefix' => 'S-', 'icon' => 'fas fa-couch', 'type' => 'seat',],
                    5 => ['label' => 'Couple', 'color' => '#C026D3', 'prefix' => 'C-', 'icon' => '💑', 'type' => 'seat',],
                    6 => ['label' => 'Female', 'color' => '#F472B6', 'prefix' => 'F-', 'icon' => '👩', 'type' => 'seat',],
                    7 => ['label' => 'Adult', 'color' => '#78350F', 'prefix' => 'AD-', 'icon' => 'fas fa-chair', 'type' => 'seat',],
                    8 => ['label' => 'Child', 'color' => '#F59E0B', 'prefix' => 'CH-', 'icon' => '🪑', 'type' => 'seat',],
                    9 => ['label' => 'Economy', 'color' => '#84CC16', 'prefix' => 'E-', 'icon' => '💺', 'type' => 'seat',],
                ];
            }
            public static function static_decoration(): array {
                return [
                    1 => ['label' => 'Blank Space', 'color' => '', 'icon' => '', 'type' => 'other'],
                    2 => ['label' => 'Driver Seat', 'color' => '#1E293B', 'icon' => '👨‍✈️', 'type' => 'other'],
                    3 => ['label' => 'Door Entry', 'color' => '#EAB308', 'icon' => '🚪', 'type' => 'other'],
                    4 => ['label' => 'Stairs', 'color' => '#64748B', 'icon' => '🪜', 'type' => 'other'],
                    5 => ['label' => 'Aisle/Walkway', 'color' => '#94A3B8', 'icon' => '↔', 'type' => 'other'],
                    6 => ['label' => 'Window', 'color' => '#38BDF8', 'icon' => '🪟', 'type' => 'other'],
                    7 => ['label' => 'Engine Box', 'color' => '#475569', 'icon' => '⚙️', 'type' => 'other'],
                    8 => ['label' => 'Toilet', 'color' => '#06B6D4', 'icon' => '🚽', 'type' => 'other'],
                    9 => ['label' => 'Luggage Rack', 'color' => '#F97316', 'icon' => '🧳', 'type' => 'other'],
                    10 => ['label' => 'Food/Snacks', 'color' => '#10B981', 'icon' => '🍔', 'type' => 'other'],
                    11 => ['label' => 'Emergency Exit', 'color' => '#EF4444', 'icon' => '🚨', 'type' => 'other'],
                ];
            }
            public static function static_feature(): array {
                return [
                    1 => ['icon' => '🚍', 'label' => 'Coach Bus'],
                    2 => ['icon' => '🚐', 'label' => 'Minibus'],
                    3 => ['icon' => '🏫', 'label' => 'School Bus'],
                    4 => ['icon' => '🏙️', 'label' => 'City Bus'],
                    5 => ['icon' => '🏨', 'label' => 'Shuttle Bus'],
                    6 => ['icon' => '🚇', 'label' => 'Metro'],
                    7 => ['icon' => '🚉', 'label' => 'Subway'],
                    8 => ['icon' => '🚝', 'label' => 'Monorail'],
                    9 => ['icon' => '🚈', 'label' => 'Light Rail'],
                    10 => ['icon' => '🚄', 'label' => 'High Speed Train'],
                    11 => ['icon' => '🚅', 'label' => 'Bullet Train'],
                    12 => ['icon' => '🚞', 'label' => 'Mountain Railway'],
                    13 => ['icon' => '🚠', 'label' => 'Cable Car'],
                    15 => ['icon' => '🏠', 'label' => 'Domestic Flight'],
                    16 => ['icon' => '🌍', 'label' => 'International Flight'],
                    17 => ['icon' => '🛩️', 'label' => 'Air Charter'],
                    18 => ['icon' => '🚢', 'label' => 'Passenger Ship'],
                    19 => ['icon' => '🛳️', 'label' => 'Cruise Ship'],
                    21 => ['icon' => '🚤', 'label' => 'Speed Boat'],
                    22 => ['icon' => '🛶', 'label' => 'River Boat'],
                    23 => ['icon' => '🚕', 'label' => 'Cab Service'],
                    24 => ['icon' => '🚘', 'label' => 'Ride Sharing'],
                    25 => ['icon' => '🚗', 'label' => 'Car Rental'],
                    26 => ['icon' => '🏍️', 'label' => 'Motorbike Taxi'],
                    27 => ['icon' => '🚲', 'label' => 'Bicycle Rental'],
                    28 => ['icon' => '🛺', 'label' => 'Auto Rickshaw'],
                    29 => ['icon' => '🚜', 'label' => 'Van Service'],
                    31 => ['icon' => '🎟️', 'label' => 'E-Ticket'],
                    32 => ['icon' => '📱', 'label' => 'Mobile Ticket'],
                    33 => ['icon' => '🔳', 'label' => 'QR Code Ticket'],
                    34 => ['icon' => '💺', 'label' => 'Seat Reservation'],
                    36 => ['icon' => '⭐', 'label' => 'VIP Seat'],
                    37 => ['icon' => '🛏️', 'label' => 'Sleeper Seat'],
                    38 => ['icon' => '👨‍👩‍👧‍👦', 'label' => 'Family Seat'],
                    40 => ['icon' => '🔄', 'label' => 'Round Trip Booking'],
                    41 => ['icon' => '🌐', 'label' => 'Multi City Booking'],
                    42 => ['icon' => '⚡', 'label' => 'Instant Booking'],
                    43 => ['icon' => '❌', 'label' => 'Ticket Cancellation'],
                    44 => ['icon' => '💰', 'label' => 'Ticket Refund'],
                    45 => ['icon' => '📍', 'label' => 'Route Tracking'],
                    46 => ['icon' => '🛰️', 'label' => 'GPS Tracking'],
                    47 => ['icon' => '📡', 'label' => 'Live Location'],
                    50 => ['icon' => '🌙', 'label' => 'Night Service'],
                    51 => ['icon' => '🚀', 'label' => 'Express Service'],
                    52 => ['icon' => '🌎', 'label' => 'International Routes'],
                    53 => ['icon' => '💳', 'label' => 'Online Payment'],
                    54 => ['icon' => '💵', 'label' => 'Cash Payment'],
                    55 => ['icon' => '💳', 'label' => 'Card Payment'],
                    56 => ['icon' => '📲', 'label' => 'Mobile Banking'],
                    57 => ['icon' => '🧾', 'label' => 'Invoice Support'],
                    58 => ['icon' => '❄️', 'label' => 'Air Conditioning'],
                    59 => ['icon' => '📶', 'label' => 'WiFi'],
                    60 => ['icon' => '🔌', 'label' => 'USB Charging'],
                    61 => ['icon' => '🔋', 'label' => 'Power Outlet'],
                    62 => ['icon' => '🪑', 'label' => 'Reclining Seats'],
                    63 => ['icon' => '🎬', 'label' => 'Entertainment System'],
                    64 => ['icon' => '💡', 'label' => 'Reading Light'],
                    66 => ['icon' => '🚻', 'label' => 'Toilet Facility'],
                    67 => ['icon' => '🧳', 'label' => 'Luggage Storage'],
                    68 => ['icon' => '♿', 'label' => 'Wheelchair Access'],
                    69 => ['icon' => '👴', 'label' => 'Priority Seating'],
                    70 => ['icon' => '👶', 'label' => 'Child Friendly'],
                    71 => ['icon' => '🐕', 'label' => 'Pet Friendly'],
                    72 => ['icon' => '🎥', 'label' => 'CCTV Security'],
                    73 => ['icon' => '🚪', 'label' => 'Emergency Exit'],
                    74 => ['icon' => '⛑️', 'label' => 'First Aid Kit'],
                ];
            }
            public static function static_faq(): array {
                return [
                    1 => [
                        'title' => 'How do I book a ticket?',
                        'des' => '<p>You can book a ticket by selecting your route, travel date, departure time, and preferred seat. Complete the payment process to confirm your booking.</p>',
                    ],
                    2 => [
                        'title' => 'Can I cancel my booking?',
                        'des' => '<p>Yes, you can cancel your booking according to the cancellation policy set by the transport operator. Refund eligibility may vary depending on the booking conditions.</p>',
                    ],
                    3 => [
                        'title' => 'Can I change my travel date?',
                        'des' => '<p>Travel dates can be modified only if the transport operator allows schedule changes and seats are available for the selected date.</p>',
                    ],
                    4 => [
                        'title' => 'When should I arrive at the boarding point?',
                        'des' => '<p>Passengers are advised to arrive at least 15 to 30 minutes before the scheduled departure time to avoid missing the journey.</p>',
                    ],
                    5 => [
                        'title' => 'Will I receive a booking confirmation?',
                        'des' => '<p>Yes, a booking confirmation will be sent after a successful payment. You can also review your booking details from your account dashboard.</p>',
                    ],
                    6 => [
                        'title' => 'What payment methods are supported?',
                        'des' => '<p>Available payment methods depend on the payment gateways configured by the website administrator.</p>',
                    ],
                    7 => [
                        'title' => 'Can I select my preferred seat?',
                        'des' => '<p>Yes, if seat selection is enabled by the operator, you will be able to choose your preferred seat during the booking process.</p>',
                    ],
                    8 => [
                        'title' => 'What happens if the trip is delayed?',
                        'des' => '<p>Departure times may change due to weather conditions, traffic congestion, maintenance requirements, or other unforeseen circumstances.</p>',
                    ],
                    9 => [
                        'title' => 'Are children allowed to travel?',
                        'des' => '<p>Yes, children are allowed to travel. Additional requirements and ticket policies may vary depending on the transport operator.</p>',
                    ],
                    10 => [
                        'title' => 'Who should I contact for assistance?',
                        'des' => '<p>If you need assistance, please contact the transport operator using the contact information provided on the website.</p>',
                    ],
                ];
            }
            public static function static_tc(): false|string {
                ob_start(); ?>
                <h6>1. Acceptance of Terms</h6>
                By using our transport booking service, you agree to comply with these terms and conditions. If you do not agree with any part of these terms, please do not use this service.
                <h6>2. Booking Confirmation</h6>
                A booking is considered confirmed only after successful payment and receipt of a booking confirmation. The customer is responsible for providing accurate information during the booking process.
                <h6>3. Passenger Information</h6>
                Passengers must provide valid information, including their name, contact details, and any other required information. Incorrect information may result in booking cancellation without notice.
                <h6>4. Seat Availability</h6>
                All bookings are subject to seat availability. The operator reserves the right to change or reassign seats when necessary due to operational requirements.
                <h6>5. Boarding and Arrival</h6>
                Passengers are advised to arrive at the designated boarding point at least 15 to 30 minutes before departure. Failure to arrive on time may result in the loss of the booking without any refund.
                <h6>6. Cancellation and Refund Policy</h6>
                Cancellation and refund eligibility may vary depending on the operator's policy. Any applicable processing fees, taxes, or service charges may be deducted from the refund amount.
                <h6>7. Schedule Changes</h6>
                Departure times, routes, and stops may be modified because of weather conditions, traffic, technical issues, government regulations, or other unforeseen circumstances.
                <h6>8. Passenger Conduct</h6>
                Passengers must behave appropriately during the journey. Any unlawful, abusive, or disruptive behavior may result in immediate removal from the service without compensation.
                <h6>9. Luggage Policy</h6>
                Passengers are responsible for their belongings. The transport operator will not be liable for any loss, damage, or theft of personal items.
                <h6>10. Limitation of Liability</h6>
                The transport operator shall not be held responsible for delays, cancellations, accidents, natural disasters, or circumstances beyond reasonable control.
                <h6>11. Privacy Policy</h6>
                Personal information collected during the booking process will be used only to provide and improve the service in accordance with the applicable privacy policy.
                <h6>12. Changes to Terms</h6>
                These terms and conditions may be updated at any time without prior notice. Continued use of the service indicates acceptance of the revised terms and conditions.
                <h6>13. Contact Information</h6>
                If you have any questions regarding these terms and conditions, please contact the transport operator directly.
                <?php return ob_get_clean();
            }
        }
    }