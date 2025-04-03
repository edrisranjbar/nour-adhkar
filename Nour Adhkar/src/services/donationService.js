import axios from 'axios';
import { BASE_API_URL } from '@/config';

export const donationService = {
  /**
   * Get recent donations
   * @returns {Promise<Array>} Array of recent donations
   */
  async getRecentDonations() {
    try {
      const response = await axios.get(`${BASE_API_URL}/donations/recent`);
      
      if (response.data.success) {
        return response.data.data.donations;
      }
      
      return [];
    } catch (error) {
      console.error('Error fetching recent donations:', error);
      return [];
    }
  },
  
  /**
   * Create a donation request
   * 
   * @param {Object} donationData - Donation data
   * @param {number} donationData.amount - Donation amount in Rials
   * @param {string} donationData.name - Donor name (optional)
   * @param {string} donationData.email - Donor email (optional)
   * @returns {Promise<Object>} Result object with success status and redirect URL
   */
  async createDonation(donationData) {
    try {
      const response = await axios.post(`${BASE_API_URL}/donations/create`, donationData);
      
      return {
        success: response.data.success,
        redirectUrl: response.data.redirect_url,
        message: response.data.message || null
      };
    } catch (error) {
      console.error('Error creating donation:', error);
      const errorMessage = error.response?.data?.message || 'خطا در ارتباط با سرور. لطفا مجددا تلاش کنید.';
      
      return {
        success: false,
        message: errorMessage
      };
    }
  }
}; 