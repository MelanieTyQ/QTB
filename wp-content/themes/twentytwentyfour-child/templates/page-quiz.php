<?php
/*
Author: Queenie Ty Bolaños
Template Name: Quiz Page
*/

get_header(); ?>

<div class="wp-block-group quiz-container">
    <div class="quiz-header">
        <h1 class="quiz-title"><?php the_title(); ?></h1>
        <div class="quiz-description">
            <?php the_content(); ?>
        </div>
    </div>
    
    <div class="quiz-form">
        <?php
        // Check if Forminator is active and get quiz ID from custom field
        if (class_exists('Forminator')) {
            $quiz_id = get_post_meta(get_the_ID(), 'forminator_quiz_id', true);
            if ($quiz_id) {
                echo do_shortcode('[forminator_quiz id="' . esc_attr($quiz_id) . '"]');
            } else {
                // Fallback: try to get first available quiz
                $quizzes = Forminator_API::module_get_all_by_type('quiz');
                if (!empty($quizzes)) {
                    $first_quiz = reset($quizzes);
                    echo do_shortcode('[forminator_quiz id="' . esc_attr($first_quiz->id) . '"]');
                }
            }
        } else {
            echo '<div class="notice notice-warning"><p>Forminator plugin is required for the quiz functionality.</p></div>';
        }
        ?>
    </div>
    
    <!-- PWA Install Banner -->
    <div id="pwa-install-banner" class="pwa-install-banner" style="display: none;">
        <span>Install our Health Quiz App for a better experience!</span>
        <button id="pwa-install-button" class="pwa-install-button">Install App</button>
    </div>
</div>

<script>
// PWA Install functionality
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    document.getElementById('pwa-install-banner').style.display = 'block';
});

document.getElementById('pwa-install-button').addEventListener('click', async () => {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        console.log(`User response to the install prompt: ${outcome}`);
        deferredPrompt = null;
        document.getElementById('pwa-install-banner').style.display = 'none';
    }
});
</script>

<?php get_footer(); ?>