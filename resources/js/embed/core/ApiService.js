/**
 * Handles all API calls for the chatbot
 */
export class ApiService {
  constructor(baseUrl, chatbotUuid) {
    this.baseUrl = baseUrl;
    this.chatbotUuid = chatbotUuid;
  }

  /**
   * Load session data
   * @param {string|null} conversationUuid - The conversation UUID
   * @returns {Promise<Object>} - Session data
   */
  async loadSession(conversationUuid) {
    try {
      const response = await fetch(`${this.baseUrl}/api/website/session`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({
          workspace_uuid: this.chatbotUuid,
          conversation_uuid: conversationUuid,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("Failed to load session:", error);
      throw error;
    }
  }

  /**
   * Send a message to the chatbot
   * @param {string} message - The message to send
   * @param {string} conversationUuid - The conversation UUID
   * @returns {Promise<Object>} - Response data
   */
  async sendMessage(message, conversationUuid) {
    try {
      const response = await fetch(`${this.baseUrl}/api/website/message`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({
          workspace_uuid: this.chatbotUuid,
          message,
          conversation_uuid: conversationUuid,
        }),
      });

      if (!response.ok) {
        const data = await response.json();
        throw new Error(data.message || `HTTP error! Status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("Failed to send message:", error);
      throw error;
    }
  }

  /**
   * Submit lead information
   * @param {Object} leadData - The lead data
   * @param {string} conversationUuid - The conversation UUID
   * @returns {Promise<Object>} - Response data
   */
  async submitLead(leadData, conversationUuid) {
    try {
      const response = await fetch(`${this.baseUrl}/api/website/lead`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({
          workspace_uuid: this.chatbotUuid,
          conversation_uuid: conversationUuid,
          name: leadData.name,
          email: leadData.email,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("Failed to submit lead:", error);
      throw error;
    }
  }

  /**
   * Mark a message as disliked
   * @param {number} messageId - The message ID to dislike
   * @param {string} conversationUuid - The conversation UUID
   * @returns {Promise<Object>} - Response data
   */
  async dislikeMessage(messageId, conversationUuid) {
    try {
      const response = await fetch(`${this.baseUrl}/api/website/dislike`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
        },
        body: JSON.stringify({
          workspace_uuid: this.chatbotUuid,
          conversation_uuid: conversationUuid,
          message_id: messageId,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      return await response.json();
    } catch (error) {
      console.error("Failed to dislike message:", error);
      throw error;
    }
  }
} 