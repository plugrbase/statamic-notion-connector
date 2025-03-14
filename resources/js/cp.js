// Import Vue first
import { createApp } from 'vue'

// Make sure Statamic is loaded
if (typeof Statamic === 'undefined') {
    console.error('Statamic is not defined')
}

// Register with Statamic
Statamic.booting(() => {
    console.log('Registering components...')
})

// Add this for debugging
console.log('CP script loaded', Statamic.$components)

// Create and register components
createApp({
    components: {}
}).mount('#notion-connector-app') 