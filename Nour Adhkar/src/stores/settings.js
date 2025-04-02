import { defineStore } from 'pinia'

export const useSettingsStore = defineStore('settings', {
  state: () => ({
    darkMode: false,
    fontSize: 3,
    notifications: true,
    vibration: true,
    sound: true
  }),
  
  actions: {
    init() {
      // Load settings from localStorage
      const savedSettings = localStorage.getItem('adhkar-settings')
      if (savedSettings) {
        const settings = JSON.parse(savedSettings)
        this.$patch(settings)
      }
      this.applySettings()
    },
    
    toggleDarkMode() {
      this.darkMode = !this.darkMode
      this.saveSettings()
    },
    
    setFontSize(size) {
      this.fontSize = size
      this.saveSettings()
    },
    
    increaseFontSize() {
      if (this.fontSize < 5) {
        this.fontSize++
        this.saveSettings()
      }
    },
    
    decreaseFontSize() {
      if (this.fontSize > 1) {
        this.fontSize--
        this.saveSettings()
      }
    },
    
    toggleNotifications() {
      this.notifications = !this.notifications
      this.saveSettings()
    },
    
    toggleVibration() {
      this.vibration = !this.vibration
      this.saveSettings()
    },
    
    toggleSound() {
      this.sound = !this.sound
      this.saveSettings()
    },
    
    saveSettings() {
      localStorage.setItem('adhkar-settings', JSON.stringify({
        darkMode: this.darkMode,
        fontSize: this.fontSize,
        notifications: this.notifications,
        vibration: this.vibration,
        sound: this.sound
      }))
      this.applySettings()
    },
    
    applySettings() {
      // Apply dark mode
      if (this.darkMode) {
        document.body.classList.add('dark-mode')
      } else {
        document.body.classList.remove('dark-mode')
      }
      
      // Apply font size
      document.documentElement.style.setProperty('--font-size-factor', this.fontSize / 3)
    }
  }
}) 