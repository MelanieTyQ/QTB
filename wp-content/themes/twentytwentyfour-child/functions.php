<?php

/*
Theme Name: Twenty Twenty-Four Child
Theme URI: https://example.com/
Description: Child theme for the Twenty Twenty-Four theme
Author: Queenie Ty Bolaños
Author URI: https://example.com/
Template: twentytwentyfour
Version: 1.0.0
*/


// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Child theme setup - inherit from Twenty Twenty-Four
function health_quiz_child_theme_setup() {
    // Load parent theme styles first
    wp_enqueue_style('twentytwentyfour-style', get_template_directory_uri() . '/style.css');
    
    // Add child theme styles
    wp_enqueue_style('health-quiz-child-style', get_stylesheet_uri(), array('twentytwentyfour-style'), '1.0.0');
    
    // Add additional theme support for child theme
    add_theme_support('custom-logo');
    add_theme_support('customize-selective-refresh-widgets');
    
    // Override parent theme features if needed
    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor-style.css');
}
add_action('wp_enqueue_scripts', 'health_quiz_child_theme_setup');
add_action('after_setup_theme', 'health_quiz_child_theme_setup');

// Enqueue child theme scripts and additional styles
function health_quiz_child_scripts() {
    // Enqueue custom quiz styles (will override parent styles)
    wp_enqueue_style('quiz-custom-style', get_stylesheet_directory_uri() . '/assets/css/quiz-styles.css', array('twentytwentyfour-style'), '1.0.0');
    
    // Enqueue custom JavaScript
    wp_enqueue_script('quiz-script', get_stylesheet_directory_uri() . '/assets/js/quiz.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('quiz-script', 'quizAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('quiz_nonce'),
        'rest_url' => rest_url('health-quiz/v1/'),
        'rest_nonce' => wp_create_nonce('wp_rest')
    ));
}
add_action('wp_enqueue_scripts', 'health_quiz_child_scripts');

// Custom post types and fields for quiz management
function register_quiz_post_types() {
    // Register Quiz Questions Post Type
    register_post_type('quiz_question', array(
        'labels' => array(
            'name' => 'Quiz Questions',
            'singular_name' => 'Quiz Question',
            'add_new' => 'Add New Question',
            'add_new_item' => 'Add New Quiz Question',
            'edit_item' => 'Edit Quiz Question',
            'new_item' => 'New Quiz Question',
            'view_item' => 'View Quiz Question',
            'search_items' => 'Search Quiz Questions',
            'not_found' => 'No quiz questions found',
            'not_found_in_trash' => 'No quiz questions found in Trash'
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-feedback',
        'supports' => array('title', 'editor', 'custom-fields'),
        'show_in_rest' => true,
    ));
    
    // Register Quiz Results Post Type
    register_post_type('quiz_result', array(
        'labels' => array(
            'name' => 'Quiz Results',
            'singular_name' => 'Quiz Result',
            'add_new' => 'Add New Result',
            'add_new_item' => 'Add New Quiz Result',
            'edit_item' => 'Edit Quiz Result',
            'new_item' => 'New Quiz Result',
            'view_item' => 'View Quiz Result',
            'search_items' => 'Search Quiz Results',
            'not_found' => 'No quiz results found',
            'not_found_in_trash' => 'No quiz results found in Trash'
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_icon' => 'dashicons-chart-bar',
        'supports' => array('title', 'custom-fields'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'register_quiz_post_types');

// Add custom admin menu for quiz management
function quiz_admin_menu() {
    add_menu_page(
        'Quiz Management',
        'Quiz Manager',
        'manage_options',
        'quiz-manager',
        'quiz_manager_page',
        'dashicons-clipboard',
        30
    );
    
    add_submenu_page(
        'quiz-manager',
        'Quiz Analytics',
        'Analytics',
        'manage_options',
        'quiz-analytics',
        'quiz_analytics_page'
    );
}
add_action('admin_menu', 'quiz_admin_menu');

// Quiz manager admin page
function quiz_manager_page() {
    ?>
    <div class="wrap">
        <h1>Quiz Management Dashboard</h1>
        <div class="quiz-stats">
            <?php
            global $wpdb;
            $total_results = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}quiz_results");
            $today_results = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}quiz_results WHERE DATE(date_created) = %s",
                current_time('Y-m-d')
            ));
            ?>
            <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3>Total Quiz Results</h3>
                    <p style="font-size: 2em; color: #764ba2; font-weight: bold;"><?php echo $total_results; ?></p>
                </div>
                <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h3>Results Today</h3>
                    <p style="font-size: 2em; color: #667eea; font-weight: bold;"><?php echo $today_results; ?></p>
                </div>
            </div>
        </div>
        
        <h2>Recent Quiz Results</h2>
        <?php
        $recent_results = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}quiz_results ORDER BY date_created DESC LIMIT 10"
        );
        
        if ($recent_results) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th>Name</th><th>Email</th><th>Score</th><th>Date</th><th>Actions</th></tr></thead>';
            echo '<tbody>';
            foreach ($recent_results as $result) {
                echo '<tr>';
                echo '<td>' . esc_html($result->name) . '</td>';
                echo '<td>' . esc_html($result->email) . '</td>';
                echo '<td>' . esc_html($result->score) . '%</td>';
                echo '<td>' . esc_html($result->date_created) . '</td>';
                echo '<td><a href="#" class="button-secondary">View Details</a></td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<p>No quiz results yet.</p>';
        }
        ?>
    </div>
    <?php
}

// Quiz analytics page
function quiz_analytics_page() {
    ?>
    <div class="wrap">
        <h1>Quiz Analytics</h1>
        <div id="quiz-chart-container">
            <canvas id="quizChart" width="400" height="200"></canvas>
        </div>
        
        <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
        <script>
        // Get analytics data and create chart
        const ctx = document.getElementById('quizChart').getContext('2d');
        
        // Fetch data via AJAX
        fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=get_quiz_analytics')
            .then(response => response.json())
            .then(data => {
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Quiz Completions',
                            data: data.values,
                            borderColor: '#764ba2',
                            backgroundColor: 'rgba(118, 75, 162, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Quiz Completions Over Time'
                            }
                        }
                    }
                });
            });
        </script>
    </div>
    <?php
}

// AJAX handler for analytics data
function get_quiz_analytics() {
    global $wpdb;
    
    $results = $wpdb->get_results("
        SELECT DATE(date_created) as date, COUNT(*) as count 
        FROM {$wpdb->prefix}quiz_results 
        WHERE date_created >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(date_created)
        ORDER BY date
    ");
    
    $labels = array();
    $values = array();
    
    foreach ($results as $result) {
        $labels[] = date('M j', strtotime($result->date));
        $values[] = intval($result->count);
    }
    
    wp_send_json(array(
        'labels' => $labels,
        'values' => $values
    ));
}
add_action('wp_ajax_get_quiz_analytics', 'get_quiz_analytics');

// Email notification system for new quiz results
function send_quiz_notification($result_id, $email, $name, $score) {
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    
    $subject = sprintf('[%s] New Quiz Result: %s', $site_name, $name);
    $message = sprintf(
        "A new quiz has been completed:\n\n" .
        "Name: %s\n" .
        "Email: %s\n" .
        "Score: %s%%\n" .
        "Date: %s\n\n" .
        "View all results: %s",
        $name,
        $email,
        $score,
        current_time('Y-m-d H:i:s'),
        admin_url('admin.php?page=quiz-manager')
    );
    
    wp_mail($admin_email, $subject, $message);
    
    // Send confirmation email to user
    $user_subject = sprintf('Thank you for taking the %s quiz!', $site_name);
    $user_message = sprintf(
        "Hi %s,\n\n" .
        "Thank you for completing our health quiz! Your score was %s%%.\n\n" .
        "You can view your results anytime at: %s?email=%s\n\n" .
        "Best regards,\n%s",
        $name,
        $score,
        home_url('/results/'),
        urlencode($email),
        $site_name
    );
    
    wp_mail($email, $user_subject, $user_message);
}

// Hook into Forminator quiz submission (if using Forminator)
add_action('forminator_quiz_mail_sent', function($quiz_id, $response) {
    // Process Forminator quiz results
    if (isset($response['name']) && isset($response['email']) && isset($response['score'])) {
        $name = sanitize_text_field($response['name']);
        $email = sanitize_email($response['email']);
        $score = intval($response['score']);
        
        // Save to custom table
        global $wpdb;
        $result_id = $wpdb->insert(
            $wpdb->prefix . 'quiz_results',
            array(
                'email' => $email,
                'name' => $name,
                'score' => $score,
                'answers' => json_encode($response),
                'date_created' => current_time('mysql')
            ),
            array('%s', '%s', '%d', '%s', '%s')
        );
        
        if ($result_id) {
            send_quiz_notification($wpdb->insert_id, $email, $name, $score);
        }
    }
}, 10, 2);

// Get user quiz results
function get_user_quiz_results($request) {
    global $wpdb;
    
    $email = sanitize_email($request['email']);
    
    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Invalid email address', array('status' => 400));
    }
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}forminator_form_entry_meta 
         WHERE meta_key = 'email' AND meta_value = %s 
         ORDER BY entry_id DESC LIMIT 10",
        $email
    ));
    
    return new WP_REST_Response($results, 200);
}

// Submit quiz result
function submit_quiz_result($request) {
    $params = $request->get_json_params();
    
    // Validate and sanitize data
    $email = sanitize_email($params['email']);
    $name = sanitize_text_field($params['name']);
    $score = intval($params['score']);
    $answers = sanitize_text_field(json_encode($params['answers']));
    
    if (!is_email($email) || empty($name)) {
        return new WP_Error('invalid_data', 'Invalid data provided', array('status' => 400));
    }
    
    // Save to database (customize based on your needs)
    global $wpdb;
    
    $result = $wpdb->insert(
        $wpdb->prefix . 'quiz_results',
        array(
            'email' => $email,
            'name' => $name,
            'score' => $score,
            'answers' => $answers,
            'date_created' => current_time('mysql')
        ),
        array('%s', '%s', '%d', '%s', '%s')
    );
    
    if ($result === false) {
        return new WP_Error('db_error', 'Failed to save result', array('status' => 500));
    }
    
    return new WP_REST_Response(array('success' => true, 'id' => $wpdb->insert_id), 201);
}

// Create custom database table on theme activation
function create_quiz_results_table() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'quiz_results';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        email varchar(100) NOT NULL,
        name varchar(100) NOT NULL,
        score int(3) NOT NULL,
        answers longtext NOT NULL,
        date_created datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY email (email)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'create_quiz_results_table');
