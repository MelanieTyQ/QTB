<?php
/*
Template Name: Results Page
*/
get_header(); ?>

<div class="container">
    <div class="quiz-container">
        <div class="quiz-header">
            <h1 class="quiz-title">Your Results</h1>
        </div>
        
        <div class="results-content">
            <?php the_content(); ?>
            
            <div id="quiz-results">
                <!-- Results will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
// Load quiz results if email is provided in URL
const urlParams = new URLSearchParams(window.location.search);
const email = urlParams.get('email');

if (email) {
    fetch(`/wp-json/health-quiz/v1/results/${email}`)
        .then(response => response.json())
        .then(data => {
            const resultsDiv = document.getElementById('quiz-results');
            if (data.length > 0) {
                resultsDiv.innerHTML = '<h3>Your Previous Results:</h3>' + 
                    data.map(result => `<div class="result-item">${JSON.stringify(result)}</div>`).join('');
            } else {
                resultsDiv.innerHTML = '<p>No results found for this email.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading results:', error);
        });
}
</script>

<?php get_footer(); ?>