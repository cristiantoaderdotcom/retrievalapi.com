/**
 * LeadCollector module for handling lead collection forms
 */
export class LeadCollector {
  /**
   * Create a new LeadCollector instance
   * @param {Object} settings - Lead collector settings
   * @param {ApiService} apiService - API service instance
   * @param {Function} onLeadSubmitted - Callback for when a lead is submitted
   */
  constructor(settings, apiService, onLeadSubmitted) {
    this.settings = settings || {};
    this.apiService = apiService;
    this.onLeadSubmitted = onLeadSubmitted;
    this.shown = false;
  }

  /**
   * Check if lead collector should be shown
   * @param {number} messageCount - Number of assistant messages
   * @returns {boolean} - Whether the lead collector should be shown
   */
  shouldShow(messageCount) {
    return (
      !this.shown &&
      this.settings['lead-enabled'] &&
      parseInt(messageCount) === parseInt(this.settings['lead-trigger_after_messages'] || 2)
    );
  }

  /**
   * Get HTML for lead collector form
   * @returns {string} - HTML string for lead collector form
   */
  getLeadCollectorHTML() {
    return `
      <div class="flex max-w-[94%] fade-in">
        <div class="chat-bubble-radius relative min-w-0 break-words shadow-sm chat-bubble-assistant lead-collector-form">
          <div class="flex-auto px-3 py-2">
            <p class="mb-3">${this.settings['lead-heading_message'] || 'To provide you with better assistance, could you please share your contact details?'}</p>
            <form class="space-y-3" id="lead-collector-form">
              <div>
                <input type="text" name="name" placeholder="Your Name" required
                  class="w-full rounded-lg border border-zinc-200 px-4 py-2 text-sm" />
              </div>
              <div>
                <input type="email" name="email" placeholder="Your Email" required
                  class="w-full rounded-lg border border-zinc-200 px-4 py-2 text-sm" />
              </div>
              <button type="submit" 
                class="chat-button w-full px-4 py-2 text-sm font-medium hover:opacity-90">
                ${this.settings['lead-button_label'] || 'Continue chatting...'}
              </button>
            </form>
          </div>
        </div>
      </div>
    `;
  }

  /**
   * Initialize the lead collector
   * @param {HTMLElement} container - Container to append lead collector to
   * @param {string} conversationUuid - The conversation UUID
   */
  initialize(container, conversationUuid) {
    container.insertAdjacentHTML('beforeend', this.getLeadCollectorHTML());
    this.shown = true;
    
    const form = document.getElementById('lead-collector-form');
    form.addEventListener('submit', (e) => this.handleSubmit(e, conversationUuid, container));
  }

  /**
   * Handle form submission
   * @param {Event} event - Submit event
   * @param {string} conversationUuid - The conversation UUID
   * @param {HTMLElement} container - The container element
   */
  async handleSubmit(event, conversationUuid, container) {
    event.preventDefault();
    const form = new FormData(event.target);
    const leadData = {
      name: form.get('name'),
      email: form.get('email')
    };

    try {
      const response = await this.apiService.submitLead(leadData, conversationUuid);
      
      if (response.status === 'success') {
        // Hide the lead collector form
        const leadForm = container.querySelector('.lead-collector-form');
        if (leadForm) {
          leadForm.parentElement.style.display = 'none';
        }

        // Create user profile summary
        const profileHTML = this.createProfileSummaryHTML(leadData);
        container.insertAdjacentHTML('beforeend', profileHTML);
        
        // Add confirmation message
        const confirmationMessage = this.settings['lead-confirmation_message'] || 
          'Thanks for sharing your contact details! How else can I help you?';
        
        // Call the callback if provided
        if (typeof this.onLeadSubmitted === 'function') {
          this.onLeadSubmitted(confirmationMessage);
        }
      }
    } catch (error) {
      console.error('Failed to submit lead:', error);
      alert('Sorry, there was an error submitting your information. Please try again.');
    }
  }

  /**
   * Create HTML for profile summary
   * @param {Object} leadData - The lead data
   * @returns {string} - HTML string for profile summary
   */
  createProfileSummaryHTML(leadData) {
    return `
      <div class="flex max-w-[94%] ml-auto justify-end">	
        <div class="relative min-w-0 break-words rounded-2xl shadow-lg bg-zinc-100 text-black">
          <div class="p-6">
            <div class="flex items-center space-x-4">
              <span class="relative flex shrink-0 overflow-hidden rounded-full h-12 w-12">
                <span class="flex h-full w-full items-center justify-center rounded-full bg-zinc-200 uppercase">${leadData.name.slice(0, 2)}</span>
              </span>
              <div class="space-y-1">
                <div class="flex items-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-user h-4 w-4 text-muted-foreground">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                  </svg>
                  <h3 class="font-medium">${leadData.name}</h3>
                </div>
                <div class="flex items-center space-x-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-mail h-4 w-4 text-muted-foreground">
                    <rect width="20" height="16" x="2" y="4" rx="2"></rect>
                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"></path>
                  </svg>
                  <p class="text-sm text-muted-foreground">${leadData.email}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    `;
  }
} 