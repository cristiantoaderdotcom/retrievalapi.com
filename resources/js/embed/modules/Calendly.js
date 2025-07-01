/**
 * Handles Calendly integration
 */
export class Calendly {
  /**
   * Create a new Calendly instance
   * @param {Object} settings - The chatbot settings
   * @param {Function} onCalendlyRendered - Callback when Calendly is rendered
   */
  constructor(settings, onCalendlyRendered) {
    this.settings = settings?.calendly || {};
    this.onCalendlyRendered = onCalendlyRendered;
    this.triggerKeywords = [];
    
    if (this.settings.enabled && this.settings.url) {
      // Parse trigger keywords
      const triggerKeywordsString = this.settings.trigger_keywords || 
        'schedule,meeting,appointment,book,calendar,time,availability';
      this.triggerKeywords = triggerKeywordsString
        .split(',')
        .map(keyword => keyword.trim().toLowerCase())
        .filter(keyword => keyword);
    }
  }

  /**
   * Check if a message should trigger Calendly
   * @param {string} message - The message to check
   * @returns {boolean} - Whether Calendly should be triggered
   */
  shouldTriggerCalendly(message) {
    if (!this.settings.enabled || !this.settings.url || !message) {
      return false;
    }

    const lowercaseMessage = message.toLowerCase();
    return this.triggerKeywords.some(keyword => 
      keyword && lowercaseMessage.includes(keyword)
    );
  }

  /**
   * Get Calendly HTML
   * @returns {string} - HTML string for Calendly
   */
  getCalendlyHTML() {
    return `
      <div class="flex max-w-[94%] calendly-container">
        <div class="chat-bubble-radius relative min-w-0 break-words shadow-sm chat-bubble-assistant">
          <div class="flex-auto px-3 py-2">
            <p class="mb-3">${this.settings.message || 'You can easily schedule a meeting with me using my calendar. Just click the button below:'}</p>
            <div class="flex justify-center">
              <a href="${this.settings.url}" target="_blank" rel="noopener noreferrer"
                class="chat-button px-4 py-2 text-sm font-medium hover:opacity-90 inline-block text-center">
                ${this.settings.button_text || 'Schedule a meeting'}
              </a>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  /**
   * Render Calendly in container
   * @param {HTMLElement} container - Container to append Calendly to
   */
  render(container) {
    if (!this.settings.enabled || !this.settings.url) {
      return;
    }

    container.insertAdjacentHTML('beforeend', this.getCalendlyHTML());
    
    if (typeof this.onCalendlyRendered === 'function') {
      this.onCalendlyRendered();
    }
  }

  /**
   * Get Calendly data for API response
   * @returns {Object|null} - Calendly data or null if not enabled
   */
  getCalendlyData() {
    if (!this.settings.enabled || !this.settings.url) {
      return null;
    }

    return {
      url: this.settings.url,
      message: this.settings.message || 'You can easily schedule a meeting with me using my calendar. Just click the button below:',
      button_text: this.settings.button_text || 'Schedule a meeting'
    };
  }
} 