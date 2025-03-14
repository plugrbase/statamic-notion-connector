// Import Vue first
import { createApp } from 'vue'

// Import components
import NotionConnector from './components/NotionConnector.vue'
import DatabaseMapping from './components/DatabaseMapping.vue'

// Make sure Statamic is loaded
if (typeof Statamic === 'undefined') {
    console.error('Statamic is not defined')
}

// Register with Statamic
Statamic.booting(() => {
    console.log('Registering components...')
    Statamic.component('notion-connector', NotionConnector)
    Statamic.component('database-mapping', DatabaseMapping)
})

// Add this for debugging
console.log('CP script loaded', Statamic.$components)

// Create and register components
createApp({
    components: {
        NotionConnector,
        DatabaseMapping
    }
}).mount('#notion-connector-app') 