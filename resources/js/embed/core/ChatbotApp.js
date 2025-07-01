import { ApiService } from './ApiService.js';
import { MessageRenderer } from './MessageRenderer.js';
import { LeadCollector } from '../modules/LeadCollector.js';
import { SuggestedMessages } from '../modules/SuggestedMessages.js';
import { Calendly } from '../modules/Calendly.js';

/**
 * Main Chatbot Application class
 */
export class ChatbotApp {
  /**
   * Create a new ChatbotApp instance
   * @param {Object} config - Configuration object
   */
  constructor(config) {
    this.config = config;
    this.chatbotUuid = config.chatbotUuid;
    this.settings = config.settings || {};
    this.baseUrl = config.baseUrl || '';
    
    // DOM Elements
    this.elements = {
      messagesContainer: document.getElementById('chatbot-messages'),
      suggestedContainer: document.getElementById('chatbot-suggested'),
      form: document.getElementById('chatbot-form'),
      input: document.getElementById('chatbot-input'),
      resetButton: document.getElementById('chatbot-reset'),
    };
    
    // State
    this.state = {
      messages: [],
      conversationUuid: localStorage.getItem(`chatbot_conversation_${this.getChatbotStorageId()}`),
      isDisabled: false,
      canReset: false,
    };
    
    // Initialize services and modules
    this.initializeServices();
    this.initializeModules();
    
    // Add event listeners
    this.addEventListeners();
    
    // Load session
    this.loadSession();
  }

  /**
   * Get chatbot storage ID
   * @returns {string} - Storage ID
   */
  getChatbotStorageId() {
    return this.config.storageId || md5(this.chatbotUuid);
  }

  /**
   * Initialize services
   */
  initializeServices() {
    this.apiService = new ApiService(this.baseUrl, this.chatbotUuid);
    this.messageRenderer = new MessageRenderer(this.settings);
  }

  /**
   * Initialize modules
   */
  initializeModules() {
    // Lead Collector
    this.leadCollector = new LeadCollector(
      this.settings,
      this.apiService,
      (confirmationMessage) => this.renderAssistantMessage(confirmationMessage)
    );
    
    // Suggested Messages
    this.suggestedMessages = new SuggestedMessages(
      this.settings,
      (message) => {
        this.elements.input.value = message;
        this.sendMessage();
      }
    );
    
    // Calendly
    this.calendly = new Calendly(
      this.settings,
      () => this.scrollToBottom()
    );
    
    // Set up auto-scrolling on DOM changes
    this.setupAutoScrolling();
  }
  
  /**
   * Set up automatic scrolling when messages are added
   */
  setupAutoScrolling() {
    // Simplified observer - we'll rely more on callback-based scrolling
    if (this.elements.messagesContainer) {
      const observer = new MutationObserver((mutations) => {
        // Only auto-scroll for typing indicators and other non-message content
        const hasNonMessageContent = mutations.some(mutation => {
          if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            return Array.from(mutation.addedNodes).some(node => {
              return node.nodeType === Node.ELEMENT_NODE && 
                     (node.id === 'typing-indicator' || 
                      node.classList?.contains('lead-collector-form') ||
                      node.classList?.contains('calendly-container'));
            });
          }
          return false;
        });
        
        if (hasNonMessageContent) {
          setTimeout(() => {
            this.scrollToBottom();
          }, 100);
        }
      });
      
      observer.observe(this.elements.messagesContainer, {
        childList: true,
        subtree: true
      });
    }
    
    // Also scroll on window resize
    window.addEventListener('resize', () => {
      setTimeout(() => {
        this.scrollToBottom();
      }, 100);
    });
  }

  /**
   * Add event listeners
   */
  addEventListeners() {
    // Form submission
    this.elements.form.addEventListener('submit', (e) => {
      e.preventDefault();
      this.sendMessage();
    });
    
    // Reset button
    if (this.elements.resetButton) {
      this.elements.resetButton.addEventListener('click', () => this.handleReset());
    }
    
    // Focus input field on load
    setTimeout(() => {
      if (this.elements.input && !this.state.isDisabled) {
        this.elements.input.focus();
      }
    }, 500);
    
    // Handle enter key for sending messages
    if (this.settings?.chat_interface?.send_on_enter ?? true) {
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey && this.elements.input.value.trim() !== '' && !this.state.isDisabled) {
          e.preventDefault();
          this.sendMessage();
        }
      });
    }
  }

  /**
   * Load session data
   */
  async loadSession() {
    try {
      const response = await this.apiService.loadSession(this.state.conversationUuid);
      
      if (response.status === 'success') {
        this.state.conversationUuid = response.conversation_uuid;
        localStorage.setItem(`chatbot_conversation_${this.getChatbotStorageId()}`, this.state.conversationUuid);
        
        this.state.messages = response.messages || [];
        this.renderMessages();
        this.state.canReset = this.state.messages.length > 0;
        
        if (this.elements.resetButton) {
          this.elements.resetButton.style.display = this.state.canReset ? 'flex' : 'none';
        }
        
        if (this.state.messages.length === 0) {
          this.suggestedMessages.initialize(this.elements.suggestedContainer);
        }
        
        // Ensure we scroll to bottom after loading session
        setTimeout(() => {
          this.scrollToBottom();
        }, 100);
      } else {
        console.error("API returned error:", response.message || "Unknown error");
        this.state.conversationUuid = null;
        localStorage.removeItem(`chatbot_conversation_${this.getChatbotStorageId()}`);
        this.state.messages = [];
        this.renderMessages();
      }
    } catch (error) {
      console.error("Failed to load session:", error);
      this.elements.messagesContainer.innerHTML = this.messageRenderer.createMessageHTML(
        this.settings?.chat_interface?.fallback_message || "Sorry, I had trouble connecting. Please try again later."
      );
      this.scrollToBottom();
    }
  }

  /**
   * Render all messages
   */
  renderMessages() {
    console.log('📝 ChatbotApp: Starting renderMessages');
    
    const container = this.elements.messagesContainer;
    container.innerHTML = "";
    
    let messagesToRender = [];
    let renderedCount = 0;
    
    // Welcome message
    if (this.settings?.chat_interface?.welcome_message) {
      console.log('📝 ChatbotApp: Adding welcome message to render queue');
      messagesToRender.push({
        message: this.settings.chat_interface.welcome_message,
        role: "Assistant",
        isWelcome: true
      });
    }
    
    // Existing messages
    this.state.messages.forEach(message => {
      console.log('📝 ChatbotApp: Adding existing message to render queue', { role: message.role_label });
      messagesToRender.push({
        message: message.message,
        role: message.role_label,
        isWelcome: false
      });
    });
    
    console.log('📝 ChatbotApp: Total messages to render', messagesToRender.length);
    
    // Function to handle when a message is rendered
    const onMessageRendered = () => {
      renderedCount++;
      console.log('📝 ChatbotApp: Message rendered', { renderedCount, total: messagesToRender.length });
      if (renderedCount === messagesToRender.length) {
        console.log('📝 ChatbotApp: All messages rendered, scrolling to bottom');
        // All messages rendered, scroll to bottom
        this.scrollToBottom();
      }
    };
    
    // Render all messages
    messagesToRender.forEach((msg, index) => {
      const isLast = index === messagesToRender.length - 1;
      console.log('📝 ChatbotApp: Rendering message', { index, isLast, role: msg.role });
      
      const messageHTML = this.messageRenderer.createMessageHTML(
        msg.message,
        msg.role,
        isLast ? onMessageRendered : () => {} // Only scroll after the last message
      );
      container.innerHTML += messageHTML;
    });
    
    // Fallback scroll if no messages to render
    if (messagesToRender.length === 0) {
      console.log('📝 ChatbotApp: No messages to render, scrolling anyway');
      this.scrollToBottom();
    }
  }

  /**
   * Render an assistant message
   * @param {string} message - The message to render
   */
  renderAssistantMessage(message) {
    console.log('📝 ChatbotApp: Rendering assistant message with callback');
    const messageHTML = this.messageRenderer.createMessageHTML(message, "Assistant", () => {
      console.log('📝 ChatbotApp: Assistant message rendered callback triggered');
      // Scroll after message is fully rendered
      this.scrollToBottom();
    });
    this.elements.messagesContainer.innerHTML += messageHTML;
  }

  /**
   * Send a message
   */
  async sendMessage() {
    console.log('📤 ChatbotApp: Starting sendMessage');
    
    const messageInput = this.elements.input;
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    messageInput.value = "";
    this.state.isDisabled = true;
    this.updateInputState();
    this.suggestedMessages.hide();
    
    // Display user message with callback for scrolling
    console.log('📤 ChatbotApp: Creating user message with callback');
    const userMessageHTML = this.messageRenderer.createMessageHTML(message, "User", () => {
      console.log('📤 ChatbotApp: User message rendered callback triggered');
      // Scroll after user message is fully rendered
      this.scrollToBottom();
    });
    this.elements.messagesContainer.innerHTML += userMessageHTML;
    
    // Show typing indicator
    this.showTypingIndicator();
    
    try {
      if (!this.state.conversationUuid) {
        console.warn("No conversation UUID found, attempting to create a new session");
        await this.loadSession();
      }
      
      // Check if message should trigger Calendly before API
      if (this.calendly.shouldTriggerCalendly(message)) {
        console.log('📤 ChatbotApp: Triggering Calendly');
        this.hideTypingIndicator();
        this.calendly.render(this.elements.messagesContainer);
        
        // Scroll after Calendly render
        setTimeout(() => {
          this.scrollToBottom();
        }, 100);
        
        this.state.isDisabled = false;
        this.updateInputState();
        return;
      }
      
      console.log('📤 ChatbotApp: Sending message to API');
      const response = await this.apiService.sendMessage(message, this.state.conversationUuid);
      
      if (response.status === "success" && response.message) {
        console.log('📤 ChatbotApp: API response received, hiding typing indicator');
        this.hideTypingIndicator();
        
        // Add the user message to state
        this.state.messages.push({
          role_label: "User",
          message: message
        });
        
        // Add assistant message with callback for scrolling
        console.log('📤 ChatbotApp: Creating assistant message with callback');
        const assistantMessageHTML = this.messageRenderer.createMessageHTML(
          response.message.message,
          response.message.role_label,
          () => {
            console.log('📤 ChatbotApp: Assistant message rendered callback triggered');
            // Scroll after assistant message is fully rendered
            this.scrollToBottom();
            
            // Check for additional content after scrolling
            this.handlePostMessageContent(response);
          }
        );
        this.elements.messagesContainer.innerHTML += assistantMessageHTML;
        
        // Add to state
        this.state.messages.push({
          role_label: "Assistant",
          message: response.message.message
        });
        
        this.state.canReset = true;
        if (this.elements.resetButton) {
          this.elements.resetButton.style.display = 'flex';
        }
        
      } else {
        throw new Error(response.message || "Unknown error");
      }
    } catch (error) {
      console.error("📤 ChatbotApp: Failed to send message:", error);
      this.hideTypingIndicator();
      
      // Add error message with callback for scrolling
      console.log('📤 ChatbotApp: Creating error message with callback');
      const errorMessageHTML = this.messageRenderer.createMessageHTML(
        error.message || "Sorry, I encountered an error. Please try again.",
        "Assistant",
        () => {
          console.log('📤 ChatbotApp: Error message rendered callback triggered');
          // Scroll after error message is fully rendered
          this.scrollToBottom();
        }
      );
      this.elements.messagesContainer.innerHTML += errorMessageHTML;
    } finally {
      this.state.isDisabled = false;
      this.updateInputState();
      
      // Focus the input after sending a message
      if (this.elements.input) {
        this.elements.input.focus();
      }
    }
  }

  /**
   * Handle additional content after a message is rendered
   * @param {Object} response - The API response
   */
  handlePostMessageContent(response) {
    console.log('📤 ChatbotApp: Handling post-message content');
    
    // Check for Calendly
    if (response.calendly) {
      console.log('📤 ChatbotApp: Rendering Calendly from response');
      this.calendly.render(this.elements.messagesContainer);
      setTimeout(() => {
        this.scrollToBottom();
      }, 100);
    } else if (this.calendly.shouldTriggerCalendly(response.message.message)) {
      console.log('📤 ChatbotApp: Rendering Calendly from message trigger');
      this.calendly.render(this.elements.messagesContainer);
      setTimeout(() => {
        this.scrollToBottom();
      }, 100);
    }
    
    // Check for lead collector
    const assistantMessageCount = this.state.messages.filter(m => m.role_label === "Assistant").length;
    if (this.leadCollector.shouldShow(assistantMessageCount)) {
      console.log('📤 ChatbotApp: Showing lead collector');
      setTimeout(() => {
        this.leadCollector.initialize(this.elements.messagesContainer, this.state.conversationUuid);
        
        // Scroll after lead collector
        setTimeout(() => {
          this.scrollToBottom();
        }, 100);
        
        if (this.settings['lead-mandatory_form_submission'] ?? false) {
          this.state.isDisabled = true;
          this.updateInputState();
        }
      }, 5000);
    }
  }

  /**
   * Show typing indicator
   */
  showTypingIndicator() {
    console.log('⌨️ ChatbotApp: Showing typing indicator');
    this.elements.messagesContainer.innerHTML += this.messageRenderer.createTypingIndicatorHTML();
    
    // Scroll after typing indicator is added
    setTimeout(() => {
      console.log('⌨️ ChatbotApp: Scrolling after typing indicator');
      this.scrollToBottom();
    }, 50);
  }

  /**
   * Hide typing indicator
   */
  hideTypingIndicator() {
    console.log('⌨️ ChatbotApp: Hiding typing indicator');
    const indicator = document.getElementById("typing-indicator");
    if (indicator) {
      indicator.remove();
      
      // Small delay then scroll to ensure proper positioning
      setTimeout(() => {
        console.log('⌨️ ChatbotApp: Scrolling after hiding typing indicator');
        this.scrollToBottom();
      }, 50);
    }
  }

  /**
   * Update input state based on disabled status
   */
  updateInputState() {
    this.elements.input.disabled = this.state.isDisabled;
    if (this.state.isDisabled) {
      this.elements.input.classList.add('opacity-50', 'cursor-not-allowed');
    } else {
      this.elements.input.classList.remove('opacity-50', 'cursor-not-allowed');
    }
  }

  /**
   * Handle reset button click
   */
  async handleReset() {
    if (!confirm("Are you sure you want to reset the chat?")) {
      return;
    }
    
    localStorage.removeItem(`chatbot_conversation_${this.getChatbotStorageId()}`);
    this.state.conversationUuid = null;
    this.state.messages = [];
    this.state.canReset = false;
    
    if (this.elements.resetButton) {
      this.elements.resetButton.style.display = 'none';
    }
    
    await this.loadSession();
    
    // Focus the input after reset
    if (this.elements.input && !this.state.isDisabled) {
      this.elements.input.focus();
    }
  }

  /**
   * Scroll to bottom of messages container and page
   */
  scrollToBottom() {
    console.log('🔄 ChatbotApp: scrollToBottom called');
    
    // Log current scroll positions
    console.log('🔄 ChatbotApp: Current scroll positions', {
      window: {
        scrollY: window.scrollY,
        innerHeight: window.innerHeight,
        pageYOffset: window.pageYOffset
      },
      document: {
        bodyScrollHeight: document.body.scrollHeight,
        documentElementScrollHeight: document.documentElement.scrollHeight,
        bodyOffsetHeight: document.body.offsetHeight,
        documentElementOffsetHeight: document.documentElement.offsetHeight,
        bodyScrollTop: document.body.scrollTop,
        documentElementScrollTop: document.documentElement.scrollTop
      }
    });
    
    // Use requestAnimationFrame for smooth scrolling
    requestAnimationFrame(() => {
      console.log('🔄 ChatbotApp: Inside requestAnimationFrame');
      
      try {
        // Calculate the maximum scroll position
        const maxScroll = Math.max(
          document.body.scrollHeight,
          document.body.offsetHeight,
          document.documentElement.scrollHeight,
          document.documentElement.offsetHeight
        );
        
        console.log('🔄 ChatbotApp: Scroll calculation', {
          bodyScrollHeight: document.body.scrollHeight,
          bodyOffsetHeight: document.body.offsetHeight,
          docScrollHeight: document.documentElement.scrollHeight,
          docOffsetHeight: document.documentElement.offsetHeight,
          maxScroll,
          currentWindowScrollY: window.scrollY
        });
        
        // Method 1: Use window.scrollTo with smooth behavior
        const oldScrollY = window.scrollY;
        window.scrollTo({
          top: maxScroll,
          left: 0,
          behavior: 'smooth'
        });
        
        console.log('🔄 ChatbotApp: Smooth scroll attempted', {
          from: oldScrollY,
          to: maxScroll,
          currentScrollY: window.scrollY
        });
        
        // Method 2: Immediate scroll fallback (in case smooth doesn't work)
        setTimeout(() => {
          const currentScroll = window.scrollY;
          window.scrollTo(0, maxScroll);
          console.log('🔄 ChatbotApp: Immediate scroll fallback', {
            beforeFallback: currentScroll,
            targetScroll: maxScroll,
            afterFallback: window.scrollY
          });
        }, 100);
        
        // Method 3: Force scroll to very large number (ensures we hit bottom)
        setTimeout(() => {
          const currentScroll = window.scrollY;
          window.scrollTo(0, 999999);
          console.log('🔄 ChatbotApp: Force scroll executed', {
            beforeForce: currentScroll,
            afterForce: window.scrollY
          });
        }, 200);
        
        // Method 4: Try scrolling document elements directly
        setTimeout(() => {
          if (document.documentElement.scrollTop !== undefined) {
            const oldDocScroll = document.documentElement.scrollTop;
            document.documentElement.scrollTop = document.documentElement.scrollHeight;
            console.log('🔄 ChatbotApp: DocumentElement direct scroll', {
              from: oldDocScroll,
              to: document.documentElement.scrollHeight,
              result: document.documentElement.scrollTop
            });
          }
          
          if (document.body.scrollTop !== undefined) {
            const oldBodyScroll = document.body.scrollTop;
            document.body.scrollTop = document.body.scrollHeight;
            console.log('🔄 ChatbotApp: Body direct scroll', {
              from: oldBodyScroll,
              to: document.body.scrollHeight,
              result: document.body.scrollTop
            });
          }
        }, 300);
        
        // Method 5: Final verification after all attempts
        setTimeout(() => {
          console.log('🔄 ChatbotApp: Final scroll verification', {
            windowScrollY: window.scrollY,
            bodyScrollTop: document.body.scrollTop,
            docElementScrollTop: document.documentElement.scrollTop,
            maxPossibleScroll: Math.max(
              document.body.scrollHeight - window.innerHeight,
              document.documentElement.scrollHeight - window.innerHeight,
              0
            )
          });
        }, 500);
        
      } catch (e) {
        console.error('🔄 ChatbotApp: Scroll failed:', e);
        // Ultimate fallback
        try {
          window.scrollTo(0, 999999);
          document.documentElement.scrollTop = 999999;
          document.body.scrollTop = 999999;
          console.log('🔄 ChatbotApp: Ultimate fallback executed');
        } catch (fallbackError) {
          console.error('🔄 ChatbotApp: All scroll methods failed:', fallbackError);
        }
      }
    });
  }
} 