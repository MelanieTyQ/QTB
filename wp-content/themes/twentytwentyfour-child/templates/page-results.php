<?php
/*
Author: Queenie Ty Bolaños
Template Name: Results Page
*/


get_header(); ?>

<div class="wp-block-group quiz-container">
    <div class="quiz-header">
        <h1 class="quiz-title">Your Quiz Results</h1>
    </div>
    
    <div class="results-content">
        <?php the_content(); ?>
        
        <div id="quiz-results-container">
            <div id="loading" style="text-align: center; padding: 2rem;">
                <p>Loading your results...</p>
            </div>
            <div id="quiz-results" style="display: none;"></div>
            <div id="no-results" style="display: none;">
                <p>No results found. Please make sure you've completed a quiz first.</p>
                <a href="<?php echo home_url('/quiz/'); ?>" class="wp-block-button__link">Take Quiz Now</a>
            </div>
        </div>
        
        <!-- Email input for manual result lookup -->
        <div class="email-lookup" style="margin-top: 2rem; padding: 1.5rem; background: #f8f9fa; border-radius: 10px;">
            <h3>Look up your results by email:</h3>
            <form id="email-lookup-form" style="display: flex; gap: 1rem; margin-top: 1rem;">
                <input type="email" id="lookup-email" placeholder="Enter your email address" 
                       style="flex: 1; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;" required>
                <button type="submit" class="wp-block-button__link" style="white-space: nowrap;">
                    Get Results
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resultsContainer = document.getElementById('quiz-results');
    const loadingDiv = document.getElementById('loading');
    const noResultsDiv = document.getElementById('no-results');
    const emailForm = document.getElementById('email-lookup-form');
    
    // Check URL parameters for email
    const urlParams = new URLSearchParams(window.location.search);
    const email = urlParams.get('email');
    
    if (email) {
        loadUserResults(email);
    } else {
        loadingDiv.style.display = 'none';
        noResultsDiv.style.display = 'block';
    }
    
    // Handle email lookup form
    emailForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const lookupEmail = document.getElementById('lookup-email').value;
        if (lookupEmail) {
            loadUserResults(lookupEmail);
        }
    });
    
    function loadUserResults(userEmail) {
        loadingDiv.style.display = 'block';
        resultsContainer.style.display = 'none';
        noResultsDiv.style.display = 'none';
        
        fetch(`<?php echo rest_url('health-quiz/v1/'); ?>results/${encodeURIComponent(userEmail)}`, {
            headers: {
                'X-WP-Nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
            }
        })
        .then(response => response.json())
        .then(data => {
            loadingDiv.style.display = 'none';
            
            if (data && data.length > 0) {
                displayResults(data);
                resultsContainer.style.display = 'block';
            } else {
                noResultsDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading results:', error);
            loadingDiv.style.display = 'none';
            noResultsDiv.style.display = 'block';
        });
    }
    
    function displayResults(results) {
        let html = '<h3>Your Quiz History:</h3>';
        
        results.forEach((result, index) => {
            const date = new Date(result.date_created).toLocaleDateString();
            const answers = JSON.parse(result.answers || '{}');
            
            html += `
                <div class="result-item">
                    <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 1rem;">
                        <h4>Quiz #${index + 1}</h4>
                        <span style="color: #666; font-size: 0.9rem;">${date}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
                        <div class="score-display" style="text-align: center; padding: 1rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 8px;">
                            <div style="font-size: 2rem; font-weight: bold;">${result.score}%</div>
                            <div style="font-size: 0.9rem;">Your Score</div>
                        </div>
                        <div style="padding: 1rem;">
                            <strong>Name:</strong> ${result.name}<br>
                            <strong>Email:</strong> ${result.email}<br>
                            <strong>Date:</strong> ${date}
                        </div>
                    </div>
                    ${answers.feedback ? `<div style="margin-top: 1rem; padding: 1rem; background: #e8f4fd; border-radius: 5px;"><strong>Feedback:</strong> ${answers.feedback}</div>` : ''}
                </div>
            `;
        });
        
        resultsContainer.innerHTML = html;
    }
});
</script>

<?php get_footer(); ?>