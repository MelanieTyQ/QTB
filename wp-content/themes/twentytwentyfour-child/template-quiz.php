<?php
/*
Template Name: Quiz Page
*/
get_header(); ?>

<div class="container">
    <div class="quiz-container">
        <div class="quiz-header">
            <h1 class="quiz-title"><?php the_title(); ?></h1>
            <div class="quiz-description">
                <?php the_content(); ?>
            </div>
        </div>
        
        <div class="quiz-form">
            <?php
            // Display Forminator quiz shortcode
            // Replace 'XXX' with your actual quiz ID
            echo do_shortcode('[forminator_quiz id="XXX"]');
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>