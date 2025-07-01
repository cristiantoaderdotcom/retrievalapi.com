/**
 * Handles suggested messages functionality
 */
export class SuggestedMessages {
  /**
   * Create a new SuggestedMessages instance
   * @param {Object} settings - The chatbot settings
   * @param {Function} onMessageSelected - Callback when a message is selected
   */
  constructor(settings, onMessageSelected) {
    this.settings = settings || {};
    this.onMessageSelected = onMessageSelected;
    this.container = null;
  }

  /**
   * Initialize suggested messages
   * @param {HTMLElement} container - Container to append suggested messages to
   */
  initialize(container) {
    this.container = container;
    this.messages = this.parseMessages();

    
    if (this.messages.length === 0) {
      return;
    }
    
    this.render();
  }

  /**
   * Parse messages from settings
   * @returns {Array} - Array of messages
   */
  parseMessages() {
    if (!this.settings?.suggested_messages) {
      return [];
    }

    return this.settings.suggested_messages
      .split('\n')
      .map(msg => msg.trim())
      .filter(msg => msg);
  }

  /**
   * Render suggested messages
   */
  render() {
    if (!this.container || this.messages.length === 0) {
      return;
    }

    const wrapper = document.createElement('div');
    wrapper.className = 'flex flex-wrap gap-3';
    
    // Optionally limit and randomize the displayed messages
    const displayMessages = this.messages.length > 3
      ? this.messages.sort(() => Math.random() - 0.5).slice(0, 3)
      : this.messages;
    
    displayMessages.forEach(message => {
      const button = document.createElement('button');
      button.className = 'suggested-button relative min-w-0 break-words cursor-pointer px-3 py-1';
      button.textContent = message;
      button.style.backgroundColor = 'var(--chat-bubble-assistant)';
      button.style.color = 'var(--chat-text-assistant)';
      button.style.borderColor = 'rgba(0,0,0,0.1)';
      
      button.addEventListener('click', () => {
        if (typeof this.onMessageSelected === 'function') {
          this.onMessageSelected(message);
          this.hide();
        }
      });
      
      wrapper.appendChild(button);
    });
    
    this.container.innerHTML = '';
    this.container.appendChild(wrapper);
  }

  /**
   * Hide suggested messages
   */
  hide() {
    if (this.container) {
      this.container.innerHTML = '';
    }
  }
} 