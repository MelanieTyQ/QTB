// Enhanced quiz functionality for child theme
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize quiz enhancements
    initQuizEnhancements();
    
    // PWA functionality
    initPWAFeatures();
    
    // Analytics tracking
    initAnalytics();
    
    function initQuizEnhancements() {
        // Add smooth scrolling to quiz questions
        const questions = document.querySelectorAll('.forminator-question');
        questions.forEach((question, index) => {
            question.style.opacity = '0';
            question.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                question.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                question.style.opacity = '1';
                question.style.transform = 'translateY(0)';
            }, index * 100);
        });
        
        // Enhanced answer selection feedback
        const answers = document.querySelectorAll('.forminator-answer input[type="radio"]');
        answers.forEach(answer => {
            answer.addEventListener('change', function() {
                // Remove previous selections in this question
                const questionContainer = this.closest('.forminator-question');
                const allAnswers = questionContainer.querySelectorAll('.forminator-answer--design');
                allAnswers.forEach(ans => ans.classList.remove('selected'));
                
                // Add selection class to current answer
                this.closest('.forminator-answer--design').classList.add('selected');
                
                // Add some visual feedback
                this.closest('.forminator-answer--design').style.transform = 'scale(1.02)';
                setTimeout(() => {
                    this.closest('.forminator-answer--design').style.transform = 'scale(1)';
                }, 200);
            });
        });
        
        // Progress tracking
        trackQuizProgress();
    }
    
    function trackQuizProgress() {
        const form = document.querySelector('.forminator-quiz--form');
        if (!form) return;
        
        const totalQuestions = form.querySelectorAll('.forminator-question').length;
        let answeredQuestions = 0;
        
        form.addEventListener('change', function() {
            answeredQuestions = form.querySelectorAll('input[type="radio"]:checked').length;
            const progress = (answeredQuestions / totalQuestions) * 100;
            
            // Update progress bar if exists
            const progressBar = document.querySelector('.forminator-quiz--progress-bar');
            if (progressBar) {
                progressBar.style.width = progress + '%';
            }
            
            // Show completion message when all questions are answered
            if (answeredQuestions === totalQuestions) {
                showCompletionMessage();
            }
        });
    }
    
    function showCompletionMessage() {
        const submitButton = document.querySelector('.forminator-button-submit');
        if (submitButton && !document.querySelector('.completion-message')) {
            const message = document.createElement('div');
            message.className = 'completion-message';
            message.style.cssText = `
                background: #d4edda;
                color: #155724;
                padding: 1rem;
                border-radius: 6px;
                margin: 1rem 0;
                text-align: center;
                border: 1px solid #c3e6cb;
            `;
            message.textContent = 'Great! You\'ve answered all questions. Click submit to see your results.';
            
            submitButton.parentNode.insertBefore(message, submitButton);
            
            // Add pulsing animation to submit button
            submitButton.style.animation = 'pulse 2s infinite';
        }
    }
    
    function initPWAFeatures() {
        // Service worker registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => console.log('SW registered'))
                .catch(error => console.log('SW registration failed'));
        }
        
        // Install prompt handling
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            showInstallPrompt();
        });
        
        function showInstallPrompt() {
            const banner = document.getElementById('pwa-install-banner');
            const button = document.getElementById('pwa-install-button');
            
            if (banner) {
                banner.style.display = 'block';
                
                button.addEventListener('click', async () => {
                    if (deferredPrompt) {
                        deferredPrompt.prompt();
                        const { outcome } = await deferredPrompt.userChoice;
                        
                        // Track installation
                        if (typeof gtag !== 'undefined') {
                            gtag('event', 'pwa_install', {
                                'outcome': outcome
                            });
                        }
                        
                        deferredPrompt = null;
                        banner.style.display = 'none';
                    }
                });
            }
        }
        
        // Handle app installation
        window.addEventListener('appinstalled', (e) => {
            console.log('PWA was installed');
            if (typeof gtag !== 'undefined') {
                gtag('event', 'pwa_installed');
            }
        });
    }
    
    function initAnalytics() {
        // Track quiz start
        const quizForm = document.querySelector('.forminator-quiz--form');
        if (quizForm && typeof gtag !== 'undefined') {
            gtag('event', 'quiz_started', {
                'quiz_id': quizForm.id || 'unknown'
            });
        }
        
        // Track quiz completion
        document.addEventListener('forminator_quiz_submit', function(e) {
            if (typeof gtag !== 'undefined') {
                gtag('event', 'quiz_completed', {
                    'quiz_id': e.detail.quiz_id || 'unknown',
                    'score': e.detail.score || 0
                });
            }
        });
        
        // Track page views for SPA-like behavior
        let currentPath = window.location.pathname;
        
        function trackPageView() {
            if (window.location.pathname !== currentPath) {
                currentPath = window.location.pathname;
                
                if (typeof gtag !== 'undefined') {
                    gtag('config', 'GA_MEASUREMENT_ID', {
                        page_path: currentPath
                    });
                }
            }
        }
        
        // Listen for history changes
        window.addEventListener('popstate', trackPageView);
        
        // Override pushState and replaceState
        const originalPushState = history.pushState;
        const originalReplaceState = history.replaceState;
        
        history.pushState = function(...args) {
            originalPushState.apply(this, args);
            trackPageView();
        };
        
        history.replaceState = function(...args) {
            originalReplaceState.apply(this, args);
            trackPageView();
        };
    }
    
    // Utility functions
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .forminator-answer--design.selected {
            background: linear-gradient(135deg, rgba(118, 75, 162, 0.1), rgba(102, 126, 234, 0.1)) !important;
            border-color: #764ba2 !important;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
});
```# Custom WordPress Quiz App Setup Guide

Build a complete quiz application using WordPress without any theme dependencies. This setup gives you full control over design, performance, and functionality.

## 🎯 Tech Stack Overview

| Component | Solution | Purpose |
|-----------|----------|---------|
| **Theme** | Custom built from scratch | Complete design control |
| **Quiz System** | Forminator Plugin | Quiz creation & management |
| **SEO** | Rank Math SEO | Search optimization & schema |
| **PWA** | PWA Plugin | App-like mobile experience |
| **API** | WordPress REST API | Mobile app integration |

## 📁 Step 1: Create Child Theme Structure for Twenty Twenty-Four

Create your child theme folder in `/wp-content/themes/twentytwentyfour-child/` with these files: