/**
 * Handles rendering messages in the chatbot
 */
export class MessageRenderer {
  constructor(settings) {
    this.settings = settings;
    this.avatarEnabled = settings?.avatar_style?.show_avatars ?? true;
    this.markedRenderer = this.setupMarkdownRenderer();
  }

  /**
   * Setup the markdown renderer
   * @returns {Object} - Marked renderer
   */
  setupMarkdownRenderer() {
    const renderer = new marked.Renderer();
    
    // Override link renderer to fix object/undefined issues
    renderer.link = (href, title, text) => {
      // Extract the actual URL if href is an object
      let safeHref = href;
      
      // For complex objects with href property (like what marked.js sometimes passes)
      if (typeof href === 'object' && href !== null) {
        // If the object has its own href property, use that
        if (href.href) {
          safeHref = href.href;
        } else if (href.text) {
          // Fall back to the text property if available
          safeHref = href.text;
        } else {
          // Last resort - try to convert the object to a string
          try {
            safeHref = String(href);
          } catch (e) {
            safeHref = '#';
          }
        }
      }
      
      // Handle undefined or null
      if (!safeHref) {
        safeHref = '#';
      }
      
      // Ensure safeHref is a string for the following operations
      safeHref = String(safeHref);
      
      // Handle email addresses
      if (safeHref.match(/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/)) {
        safeHref = `mailto:${safeHref}`;
      }
      // Handle phone numbers
      else if (safeHref.match(/^(\+?[0-9\s-()]{8,})$/)) {
        safeHref = `tel:${safeHref.replace(/[\s-()]/g, '')}`;
      }
      // Add protocol if missing for web URLs
      else if (!safeHref.match(/^[a-zA-Z]+:\/\//) 
              && !safeHref.startsWith('#') 
              && !safeHref.startsWith('/') 
              && !safeHref.startsWith('mailto:') 
              && !safeHref.startsWith('tel:')) {
        safeHref = `https://${safeHref}`;
      }
      
      // Use text from the link if available, otherwise use the URL
      const safeText = text || (typeof href === 'object' && href?.text) || safeHref || 'link';
      const safeTitle = title || '';
      
      return `<a class="text-blue-500" href="${safeHref}" target="_blank" rel="noopener noreferrer" title="${safeTitle}">${safeText}</a>`;
    };
    
    return renderer;
  }

  /**
   * Create a message HTML element
   * @param {string} message - The message text
   * @param {string} role - The role of the message sender (User or Assistant)
   * @param {Function} onRendered - Optional callback when message is fully rendered
   * @returns {string} - HTML string for the message
   */
  createMessageHTML(message, role = "Assistant", onRendered = null) {
    console.log('🎨 MessageRenderer: Creating message HTML', { message: message.substring(0, 100) + '...', role, hasCallback: !!onRendered });
    
    const isUser = role === "User";
    const bubbleClass = isUser ? "chat-bubble-user" : "chat-bubble-assistant";
    const alignment = isUser ? "ml-auto justify-end" : "";
    const avatarType = isUser 
      ? (this.settings?.avatar_style?.user_avatar || 'default') 
      : (this.settings?.avatar_style?.assistant_avatar || 'default');

    let avatarHtml = '';
    if (this.avatarEnabled) {
      const avatarContent = this.getAvatarContent(avatarType, isUser);
      avatarHtml = `
        <div class="flex-shrink-0 ${isUser ? 'order-2 ml-2' : 'mr-2'}" style="width: 32px; height: 32px;">
          ${avatarContent}
        </div>
      `;
    }
    
    // Ensure message is a string
    let safeMessage = message;
    if (typeof message !== 'string') {
      safeMessage = String(message || '');
    }
    
    try {
      const parsedMessage = marked.parse(safeMessage, { renderer: this.markedRenderer });
      
      const messageId = `message-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
      console.log('🎨 MessageRenderer: Generated message ID', messageId);
      
      const htmlContent = `
        <div class="flex max-w-[94%] ${alignment} items-start" id="${messageId}">
          ${!isUser && this.avatarEnabled ? avatarHtml : ''}
          <div class="chat-bubble-radius relative min-w-0 break-words shadow-sm ${bubbleClass}">
            <div class="flex-auto px-3 py-2">
              ${parsedMessage}
            </div>
          </div>
          ${isUser && this.avatarEnabled ? avatarHtml : ''}
        </div>
      `;
      
      // If callback provided, set up observer to call it when element is added to DOM
      if (onRendered && typeof onRendered === 'function') {
        console.log('🎨 MessageRenderer: Setting up callback for message', messageId);
        
        // Use a small delay to ensure the element is fully rendered
        setTimeout(() => {
          console.log('🎨 MessageRenderer: Looking for element', messageId);
          const element = document.getElementById(messageId);
          if (element) {
            console.log('🎨 MessageRenderer: Element found, waiting for content load', messageId);
            // Wait for any images or other content to load
            this.waitForContentLoad(element).then(() => {
              console.log('🎨 MessageRenderer: Content loaded, calling callback', messageId);
              onRendered(element);
            });
          } else {
            console.warn('🎨 MessageRenderer: Element not found, using fallback', messageId);
            // Fallback if element not found
            setTimeout(() => {
              console.log('🎨 MessageRenderer: Fallback callback triggered', messageId);
              onRendered(null);
            }, 100);
          }
        }, 50);
      } else {
        console.log('🎨 MessageRenderer: No callback provided for message', messageId);
      }
      
      return htmlContent;
    } catch (error) {
      console.error('🎨 MessageRenderer: Error creating message HTML', error);
      const errorMessageId = `error-message-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
      
      const errorContent = `
        <div class="flex max-w-[94%] ${alignment} items-start" id="${errorMessageId}">
          ${!isUser && this.avatarEnabled ? avatarHtml : ''}
          <div class="chat-bubble-radius relative min-w-0 break-words shadow-sm ${bubbleClass}">
            <div class="flex-auto px-3 py-2">
              <p>Error rendering message</p>
              <pre>${safeMessage}</pre>
            </div>
          </div>
          ${isUser && this.avatarEnabled ? avatarHtml : ''}
        </div>
      `;
      
      if (onRendered && typeof onRendered === 'function') {
        setTimeout(() => {
          const element = document.getElementById(errorMessageId);
          console.log('🎨 MessageRenderer: Error message callback triggered', errorMessageId);
          onRendered(element);
        }, 50);
      }
      
      return errorContent;
    }
  }

  /**
   * Wait for all content in an element to load (images, etc.)
   * @param {HTMLElement} element - The element to wait for
   * @returns {Promise} - Promise that resolves when content is loaded
   */
  waitForContentLoad(element) {
    console.log('🎨 MessageRenderer: Waiting for content load in element', element.id);
    
    return new Promise((resolve) => {
      const images = element.querySelectorAll('img');
      const iframes = element.querySelectorAll('iframe');
      
      console.log('🎨 MessageRenderer: Found content to wait for', { images: images.length, iframes: iframes.length });
      
      if (images.length === 0 && iframes.length === 0) {
        console.log('🎨 MessageRenderer: No media content, resolving immediately');
        // No media content, resolve immediately
        resolve();
        return;
      }
      
      let loadedCount = 0;
      const totalCount = images.length + iframes.length;
      
      const checkComplete = () => {
        loadedCount++;
        console.log('🎨 MessageRenderer: Content loaded', { loadedCount, totalCount });
        if (loadedCount >= totalCount) {
          console.log('🎨 MessageRenderer: All content loaded, resolving');
          resolve();
        }
      };
      
      // Set up load listeners for images
      images.forEach((img, index) => {
        if (img.complete) {
          console.log('🎨 MessageRenderer: Image already loaded', index);
          checkComplete();
        } else {
          console.log('🎨 MessageRenderer: Setting up load listener for image', index);
          img.addEventListener('load', () => {
            console.log('🎨 MessageRenderer: Image loaded', index);
            checkComplete();
          });
          img.addEventListener('error', () => {
            console.log('🎨 MessageRenderer: Image error', index);
            checkComplete();
          }); // Count errors as "loaded"
        }
      });
      
      // Set up load listeners for iframes
      iframes.forEach((iframe, index) => {
        console.log('🎨 MessageRenderer: Setting up load listener for iframe', index);
        iframe.addEventListener('load', () => {
          console.log('🎨 MessageRenderer: Iframe loaded', index);
          checkComplete();
        });
        iframe.addEventListener('error', () => {
          console.log('🎨 MessageRenderer: Iframe error', index);
          checkComplete();
        });
      });
      
      // Fallback timeout in case some content never loads
      setTimeout(() => {
        console.log('🎨 MessageRenderer: Timeout reached, resolving anyway');
        resolve();
      }, 2000);
    });
  }

  /**
   * Create typing indicator HTML
   * @returns {string} - HTML for typing indicator
   */
  createTypingIndicatorHTML() {
    return `
      <div class="flex max-w-[94%]" id="typing-indicator">
        <div class="chat-bubble-radius relative min-w-0 break-words shadow-sm chat-bubble-assistant">
          <div class="flex-auto px-3 py-2">
            <div class="flex items-center gap-1">
              <div class="animate-bounce [animation-delay:-0.3s] h-2 w-2 bg-zinc-500 rounded-full"></div>
              <div class="animate-bounce [animation-delay:-0.15s] h-2 w-2 bg-zinc-500 rounded-full"></div>
              <div class="animate-bounce h-2 w-2 bg-zinc-500 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  /**
   * Get avatar HTML content based on type
   * @param {string} type - Avatar type
   * @param {boolean} isUser - Whether the avatar is for a user
   * @returns {string} - HTML for the avatar
   */
  getAvatarContent(type, isUser) {
    if (isUser) {
      return `
        <div class="flex items-center justify-center h-full w-full rounded-full bg-gray-200 text-gray-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
      `;
    } else {
      return `
        <div class="flex items-center justify-center h-full w-full rounded-full bg-primary-100 text-primary-600" style="background-color: var(--primary-color); color: white;">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
        </div>
      `;
    }
  }
} 