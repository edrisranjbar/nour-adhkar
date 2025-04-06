import axios from 'axios';
import { BASE_API_URL } from '@/config';

export default {
    methods: {
        // Update a single user in the table without refetching all users
        updateUserInTable(updatedUser) {
            const userIndex = this.users.findIndex(u => u.id === updatedUser.id);
            if (userIndex !== -1) {
                // Using Vue.$set to ensure reactivity
                this.$set(this.users, userIndex, updatedUser);
            }
        },

        // Toggle user active/inactive status
        async toggleUserStatus(user) {
            try {
                const response = await axios.patch(
                    `${BASE_API_URL}/admin/users/${user.id}/toggle-status`,
                    {},
                    {
                        headers: {
                            Authorization: `Bearer ${this.token}`
                        }
                    }
                );

                if (response.data.success) {
                    // Update user in the table immediately
                    if (response.data.user) {
                        this.updateUserInTable(response.data.user);
                    } else {
                        // If no user is returned, just toggle the active property
                        user.active = !user.active;
                        this.updateUserInTable(user);
                    }

                    this.$toast.success(`کاربر با موفقیت ${user.active ? 'غیرفعال' : 'فعال'} شد`);
                } else {
                    this.$toast.error(response.data.message || 'خطا در تغییر وضعیت کاربر');
                }
            } catch (error) {
                console.error('Error toggling user status:', error);
                this.$toast.error(error.response?.data?.message || 'خطا در تغییر وضعیت کاربر');
            }
        },

        // Save user (create or update)
        async saveUser() {
            // Add validation here if needed
            this.saving = true;

            try {
                let response;

                if (this.editedUser.id) {
                    // Update existing user
                    response = await axios.put(
                        `${BASE_API_URL}/admin/users/${this.editedUser.id}`,
                        this.editedUser,
                        {
                            headers: {
                                Authorization: `Bearer ${this.token}`
                            }
                        }
                    );
                } else {
                    // Create new user
                    response = await axios.post(
                        `${BASE_API_URL}/admin/users`,
                        this.editedUser,
                        {
                            headers: {
                                Authorization: `Bearer ${this.token}`
                            }
                        }
                    );
                }

                if (response.data.success) {
                    this.$toast.success(this.editedUser.id ? 'کاربر با موفقیت ویرایش شد' : 'کاربر جدید با موفقیت ایجاد شد');

                    // If updating an existing user, update it in the table without a full refresh
                    if (this.editedUser.id && response.data.user) {
                        this.updateUserInTable(response.data.user);
                        this.closeModal();
                    } else {
                        // For new users or if no user is returned, fetch all users
                        this.closeModal();
                        this.fetchUsers();
                    }
                } else {
                    this.$toast.error(response.data.message || 'خطا در ذخیره اطلاعات کاربر');
                }
            } catch (error) {
                console.error('Error saving user:', error);
                this.$toast.error(error.response?.data?.message || 'خطا در ذخیره اطلاعات کاربر');
            } finally {
                this.saving = false;
            }
        }
    }
};