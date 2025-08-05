<?php get_header(); ?>

<div class="container">
    <div class="quiz-container">
        <div class="quiz-header">
            <h1 class="quiz-title">Health Quiz App</h1>
            <p class="quiz-description">Discover insights about your health with our comprehensive quiz</p>
        </div>
        
        <div class="quiz-content">
            <?php
            if (have_posts()) :
                while (have_posts()) : the_post();
                    the_content();
                endwhile;
            else :
                echo '<p>Welcome to the Health Quiz App. Please navigate to a quiz page to get started.</p>';
            endif;
            ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>