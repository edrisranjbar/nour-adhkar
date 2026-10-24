import { flushPromises, mount } from '@vue/test-utils'
import { beforeEach, describe, expect, it, vi } from 'vitest'
import axios from 'axios'
import { nextTick } from 'vue'
import UpdatesView from '../UpdatesView.vue'

vi.mock('axios')

const release = {
  versionCode: 25,
  versionName: '3.0.0',
  releaseDate: '2026-10-19',
  changelog: 'افزودن زبان کوردی\n\n<script>alert(1)</script>'
}
const mountView = () => mount(UpdatesView, {
  global: { stubs: { RouterLink: { template: '<a><slot /></a>' } } }
})

describe('UpdatesView', () => {
  beforeEach(() => vi.resetAllMocks())

  it('loads release history and renders admin text without executing HTML', async () => {
    let resolveRequest
    axios.get.mockReturnValue(new Promise(resolve => { resolveRequest = resolve }))
    const wrapper = mountView()
    await nextTick()
    expect(wrapper.get('[role="status"]').text()).toContain('در حال دریافت')
    resolveRequest({ data: { data: [release], current_page: 1, last_page: 1 } })
    await flushPromises()
    expect(axios.get).toHaveBeenCalledWith(expect.stringContaining('/app-versions'), { params: { page: 1 } })
    expect(wrapper.text()).toContain('3.0.0')
    expect(wrapper.findAll('li')).toHaveLength(2)
    expect(wrapper.text()).toContain('<script>alert(1)</script>')
    expect(wrapper.find('script').exists()).toBe(false)
    expect(wrapper.get('time').attributes('datetime')).toBe('2026-10-19')
  })

  it('retains loaded releases and retries a failed older page', async () => {
    axios.get.mockResolvedValueOnce({ data: { data: [release], current_page: 1, last_page: 2 } })
      .mockRejectedValueOnce(new Error('offline'))
      .mockResolvedValueOnce({ data: { data: [{ ...release, versionCode: 24, versionName: '2.5.0' }], current_page: 2, last_page: 2 } })
    const wrapper = mountView()
    await flushPromises()
    await wrapper.get('button').trigger('click')
    await flushPromises()
    expect(wrapper.findAll('article')).toHaveLength(1)
    expect(wrapper.find('[role="alert"]').exists()).toBe(true)
    await wrapper.get('button').trigger('click')
    await flushPromises()
    expect(axios.get.mock.calls[2][1].params.page).toBe(2)
    expect(wrapper.findAll('article')).toHaveLength(2)
    expect(wrapper.find('button').exists()).toBe(false)
  })

  it('shows an honest empty state', async () => {
    axios.get.mockResolvedValue({ data: { data: [], current_page: 1, last_page: 1 } })
    const wrapper = mountView()
    await flushPromises()
    expect(wrapper.text()).toContain('هنوز به‌روزرسانی‌ای منتشر نشده است.')
  })
})
