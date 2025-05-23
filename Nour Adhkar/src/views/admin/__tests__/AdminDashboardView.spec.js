import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import AdminDashboardView from '../AdminDashboardView.vue'
import { createRouter, createWebHistory } from 'vue-router'
import { createStore } from 'vuex'
import axios from 'axios'

// Mock axios
vi.mock('axios')

// Mock router
const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/admin/blog', name: 'admin-blog' },
    { path: '/admin/blog/new', name: 'admin-blog-new' },
    { path: '/admin/users', name: 'admin-users' }
  ]
})

// Mock store
const store = createStore({
  state: {
    token: 'fake-token'
  }
})

// Mock toast
const toast = {
  error: vi.fn()
}

describe('AdminDashboardView', () => {
  let wrapper

  beforeEach(async () => {
    // Reset all mocks before each test
    vi.clearAllMocks()
    
    // Mock successful API responses
    axios.get.mockImplementation((url) => {
      if (url === 'admin/posts') {
        return Promise.resolve({
          data: {
            success: true,
            posts: {
              total: 10,
              data: [
                {
                  id: 1,
                  title: 'Test Post',
                  status: 'published',
                  published_at: '2024-04-13',
                  views: 100
                }
              ]
            }
          }
        })
      }
      if (url === 'posts/views/total') {
        return Promise.resolve({
          data: {
            total_views: 1000
          }
        })
      }
      if (url === 'admin/comments/pending/count') {
        return Promise.resolve({
          data: {
            count: 5
          }
        })
      }
      if (url === 'admin/users') {
        return Promise.resolve({
          data: {
            success: true,
            users: {
              total: 20
            }
          }
        })
      }
      return Promise.reject(new Error('Not found'))
    })

    // Mount component with mocks
    wrapper = mount(AdminDashboardView, {
      global: {
        plugins: [router, store],
        mocks: {
          $toast: toast,
          $router: {
            push: vi.fn()
          }
        }
      }
    })

    // Wait for router to be ready
    await router.isReady()
  })

  it('renders dashboard title', () => {
    expect(wrapper.find('.dashboard-title').text()).toBe('پیشخوان مدیریت')
  })

  it('displays correct stats after loading', async () => {
    // Wait for all promises to resolve
    await wrapper.vm.fetchDashboardData()

    expect(wrapper.find('.stat-value').text()).toBe('۱۰') // Blog posts count
    expect(wrapper.findAll('.stat-value')[1].text()).toBe('۲۰') // Users count
    expect(wrapper.findAll('.stat-value')[2].text()).toBe('۱٬۰۰۰') // Total views
    expect(wrapper.findAll('.stat-value')[3].text()).toBe('۵') // Comments count
  })

  it('handles API errors gracefully', async () => {
    // Mock API error
    axios.get.mockRejectedValueOnce(new Error('API Error'))

    await wrapper.vm.fetchStats()
    
    expect(toast.error).toHaveBeenCalledWith('خطا در دریافت آمار')
  })

  it('navigates to correct routes when clicking action cards', async () => {
    const actionCards = wrapper.findAll('.action-card')
    
    await actionCards[0].trigger('click')
    expect(wrapper.vm.$router.push).toHaveBeenCalledWith('/admin/blog/new')
    
    await actionCards[1].trigger('click')
    expect(wrapper.vm.$router.push).toHaveBeenCalledWith('/admin/blog')
    
    await actionCards[2].trigger('click')
    expect(wrapper.vm.$router.push).toHaveBeenCalledWith('/admin/users')
  })

  it('formats dates correctly', () => {
    const date = '2024-04-13'
    const formattedDate = wrapper.vm.formatDate(date)
    expect(formattedDate).toMatch(/فروردین ۱۴۰۳/) // Only check month and year
  })

  it('formats numbers correctly', () => {
    expect(wrapper.vm.formatNumber(1000)).toBe('۱٬۰۰۰')
  })
}) 