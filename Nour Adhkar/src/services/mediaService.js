import axios from 'axios';
import { BASE_API_URL } from '@/config';
import store from '@/store';

/**
 * Media Service - Handles API communication for media management
 */
export const mediaService = {
  /**
   * Get auth headers for requests
   * @returns {Object} Headers with authentication token
   */
  getAuthHeaders() {
    const token = store.state.token;
    return token ? { Authorization: `Bearer ${token}` } : {};
  },

  /**
   * Get all media items with optional filtering
   * @param {Object} options - Query parameters
   * @param {string} options.type - Filter by media type (image, audio, other)
   * @param {string} options.search - Search term
   * @param {number} options.page - Page number for pagination
   * @param {number} options.limit - Number of items per page
   * @returns {Promise<Object>} Media items with pagination data
   */
  async getMedia(options = {}) {
    try {
      const response = await axios.get(`${BASE_API_URL}/admin/media`, { 
        params: options,
        headers: this.getAuthHeaders()
      });
      return response.data;
    } catch (error) {
      console.error('Error fetching media:', error);
      throw new Error(error.response?.data?.message || 'Failed to fetch media');
    }
  },

  /**
   * Get a single media item by ID
   * @param {number} id - Media item ID
   * @returns {Promise<Object>} Media item data
   */
  async getMediaById(id) {
    try {
      const response = await axios.get(`${BASE_API_URL}/admin/media/${id}`, {
        headers: this.getAuthHeaders()
      });
      return response.data;
    } catch (error) {
      console.error(`Error fetching media item #${id}:`, error);
      throw new Error(error.response?.data?.message || 'Failed to fetch media item');
    }
  },

  /**
   * Upload media files
   * @param {FormData} formData - Form data containing files to upload
   * @param {Function} onProgress - Progress callback function
   * @returns {Promise<Object>} Upload result
   */
  async uploadMedia(formData, onProgress) {
    try {
      // Get the authentication token
      const token = store.state.token;
      
      console.log('Sending upload request to:', `${BASE_API_URL}/admin/media/upload`);
      console.log('Auth header present:', !!token);

      // Using a more direct approach with fetch API for file uploads
      const xhr = new XMLHttpRequest();
      
      // Set up a promise to handle the XHR response
      const uploadPromise = new Promise((resolve, reject) => {
        xhr.open('POST', `${BASE_API_URL}/admin/media/upload`);
        
        // Set up headers
        if (token) {
          xhr.setRequestHeader('Authorization', `Bearer ${token}`);
        }
        
        // Handle progress
        xhr.upload.onprogress = (e) => {
          if (e.lengthComputable && onProgress) {
            const percentCompleted = Math.round((e.loaded * 100) / e.total);
            onProgress(percentCompleted);
          }
        };
        
        // Handle response
        xhr.onload = () => {
          if (xhr.status >= 200 && xhr.status < 300) {
            try {
              const response = JSON.parse(xhr.responseText);
              resolve(response);
            } catch (e) {
              reject(new Error('Invalid JSON response from server'));
            }
          } else {
            let errorMessage = 'Upload failed';
            try {
              const errorResponse = JSON.parse(xhr.responseText);
              errorMessage = errorResponse.message || errorMessage;
            } catch (e) {
              // If we can't parse the response, use the status text
              errorMessage = xhr.statusText || errorMessage;
            }
            reject(new Error(errorMessage));
          }
        };
        
        // Handle network errors
        xhr.onerror = () => {
          reject(new Error('Network error during upload'));
        };
        
        // Send the FormData
        xhr.send(formData);
      });
      
      return await uploadPromise;
    } catch (error) {
      console.error('Error uploading media:', error);
      // Include the response error message if available
      throw new Error(error.message || 'Failed to upload media');
    }
  },

  /**
   * Update media item
   * @param {number} id - Media item ID
   * @param {Object} data - Updated media data
   * @returns {Promise<Object>} Updated media item
   */
  async updateMedia(id, data) {
    try {
      const response = await axios.put(`${BASE_API_URL}/admin/media/${id}`, data, {
        headers: this.getAuthHeaders()
      });
      return response.data;
    } catch (error) {
      console.error(`Error updating media item #${id}:`, error);
      throw new Error(error.response?.data?.message || 'Failed to update media item');
    }
  },

  /**
   * Delete media item
   * @param {number} id - Media item ID
   * @returns {Promise<Object>} Deletion result
   */
  async deleteMedia(id) {
    try {
      const response = await axios.delete(`${BASE_API_URL}/admin/media/${id}`, {
        headers: this.getAuthHeaders()
      });
      return response.data;
    } catch (error) {
      console.error(`Error deleting media item #${id}:`, error);
      throw new Error(error.response?.data?.message || 'Failed to delete media item');
    }
  },

  /**
   * Delete multiple media items
   * @param {Array<number>} ids - Array of media item IDs to delete
   * @returns {Promise<Object>} Deletion result
   */
  async deleteMultipleMedia(ids) {
    try {
      const response = await axios.post(`${BASE_API_URL}/admin/media/delete-multiple`, { ids }, {
        headers: this.getAuthHeaders()
      });
      return response.data;
    } catch (error) {
      console.error(`Error deleting multiple media items:`, error);
      throw new Error(error.response?.data?.message || 'Failed to delete media items');
    }
  }
}; 