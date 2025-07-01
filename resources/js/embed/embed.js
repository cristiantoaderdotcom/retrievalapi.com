import { ChatbotApp } from './core/ChatbotApp.js';

/**
 * Initialize Chatbot when DOM is loaded
 */
document.addEventListener('DOMContentLoaded', () => {
  // Get configuration from window object or data attributes
  const chatbotConfig = window.chatbotConfig || {};
  
  // Initialize chatbot
  window.chatbot = new ChatbotApp({
    chatbotUuid: chatbotConfig.uuid,
    settings: chatbotConfig.settings,
    baseUrl: chatbotConfig.baseUrl || window.location.origin,
    storageId: chatbotConfig.storageId,
  });
});

/**
 * Expose module API for customization
 */
export { ChatbotApp } from './core/ChatbotApp.js';
export { ApiService } from './core/ApiService.js';
export { MessageRenderer } from './core/MessageRenderer.js';
export { LeadCollector } from './modules/LeadCollector.js';
export { SuggestedMessages } from './modules/SuggestedMessages.js';
export { Calendly } from './modules/Calendly.js'; 