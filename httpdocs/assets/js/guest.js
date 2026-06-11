// Guest Review Page JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const memberSelect = document.getElementById('memberSelect');
    const eventSelect = document.getElementById('eventSelect');
    const barSelect = document.getElementById('barSelect');
    const reviewerNameInput = document.getElementById('reviewerName');
    const reviewForm = document.getElementById('reviewForm');
    const successMessage = document.getElementById('successMessage');
    const errorMessage = document.getElementById('errorMessage');
    
    // Load members, events, and bars
    loadMembers();
    loadEvents();
    loadBars();
    
    // Load dark mode preference
    if (localStorage.getItem('darkMode') === 'true') {
        document.body.classList.add('dark-mode');
    }
    
    // Toggle dark mode (optional - could be added later as a setting)
    // document.addEventListener('click', function(e) {
    //     if (e.target.id === 'darkModeToggle') {
    //         document.body.classList.toggle('dark-mode');
    //         localStorage.setItem('darkMode', document.body.classList.contains('dark-mode'));
    //     }
    // });
    
    // Load members from API
    async function loadMembers() {
        try {
            const response = await fetch('/api/guest_members.php');
            if (!response.ok) throw new Error('Failed to load members');
            
            const data = await response.json();
            
            if (data.success && data.members) {
                data.members.forEach(member => {
                    const option = document.createElement('option');
                    option.value = member.id;
                    option.textContent = `${member.first_name} ${member.last_name}`;
                    memberSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading members:', error);
            // Display error message to user
            errorMessage.textContent = 'Konnte Jugendteam-Mitglieder nicht laden. Bitte versuchen Sie es später erneut.';
            errorMessage.classList.remove('hidden');
        }
    }
    
    // Load events from API
    async function loadEvents() {
        try {
            const response = await fetch('/api/guest_events.php');
            if (!response.ok) throw new Error('Failed to load events');
            
            const data = await response.json();
            
            if (data.success && data.events) {
                data.events.forEach(event => {
                    const option = document.createElement('option');
                    option.value = event.id;
                    option.textContent = `${event.title} (${new Date(event.event_date).toLocaleDateString('de-DE')})`;
                    eventSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading events:', error);
            // Display error message to user
            errorMessage.textContent = 'Konnte Veranstaltungen nicht laden. Bitte versuchen Sie es später erneut.';
            errorMessage.classList.remove('hidden');
        }
    }
    
    // Load bars from API
    async function loadBars() {
        try {
            const response = await fetch('/api/guest_bars.php');
            if (!response.ok) throw new Error('Failed to load bars');
            
            const data = await response.json();
            
            if (data.success && data.bars) {
                data.bars.forEach(bar => {
                    const option = document.createElement('option');
                    option.value = bar.id;
                    option.textContent = bar.name;
                    barSelect.appendChild(option);
                });
            }
        } catch (error) {
            console.error('Error loading bars:', error);
            // Display error message to user
            errorMessage.textContent = 'Konnte Rümli nicht laden. Bitte versuchen Sie es später erneut.';
            errorMessage.classList.remove('hidden');
        }
    }
    
    // Handle form submission
    reviewForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Reset messages
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        
        // Get form values
        const targetUserId = memberSelect.value;
        const eventId = eventSelect.value === '' ? null : eventSelect.value;
        const barId = barSelect.value === '' ? null : barSelect.value;
        const reviewerName = reviewerNameInput.value.trim();
        
        // Validate required fields
        if (!targetUserId) {
            errorMessage.textContent = 'Bitte wählen Sie ein Jugendteam-Mitglied aus.';
            errorMessage.classList.remove('hidden');
            return;
        }
        
        // Get ratings
        const ratingFriendly = parseInt(document.querySelector('input[name="rating_friendly"]:checked')?.value);
        const ratingProfessional = parseInt(document.querySelector('input[name="rating_professional"]:checked')?.value);
        const ratingOverall = parseInt(document.querySelector('input[name="rating_overall"]:checked')?.value);
        
        // Validate ratings
        if (!ratingFriendly || !ratingProfessional || !ratingOverall) {
            errorMessage.textContent = 'Bitte bewerten Sie alle drei Kriterien.';
            errorMessage.classList.remove('hidden');
            return;
        }
        
        // Get comment
        const comment = document.getElementById('comment').value.trim();
        
        // Prepare data for API
        const reviewData = {
            target_user_id: targetUserId,
            rating_friendly: ratingFriendly,
            rating_professional: ratingProfessional,
            rating_overall: ratingOverall,
            event_id: eventId,
            bar_id: barId,
            comment: comment,
            reviewer_type: 'guest'
        };
        
        // If reviewer name is provided, include it (for local storage only)
        if (reviewerName) {
            // We're not sending the name to the server, but we could store it locally
            // localStorage.setItem('lastReviewerName', reviewerName);
        }
        
        // Send to API
        try {
            const response = await fetch('/api/reviews_create.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(reviewData)
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show success message
                successMessage.classList.remove('hidden');
                
                // Reset form
                reviewForm.reset();
                
                // Scroll to top
                window.scrollTo({ top: 0, behavior: 'smooth' });
                
                // Clear any stored reviewer name
                // localStorage.removeItem('lastReviewerName');
            } else {
                throw new Error(data.message || 'Feedback konnte nicht gesendet werden');
            }
        } catch (error) {
            console.error('Error submitting review:', error);
            errorMessage.textContent = 'Es gab ein Problem beim Absenden Ihres Feedbacks. Bitte versuchen Sie es erneut.';
            errorMessage.classList.remove('hidden');
        }
    });
});