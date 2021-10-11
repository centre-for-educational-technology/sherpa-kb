/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap')

Vue.use(require('bootstrap-vue'))

import store from './store'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import Vue from 'vue'

window.Vue = Vue

Vue.component('font-awesome-icon', FontAwesomeIcon)
Vue.config.productionTip = false

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))

Vue.component('language-expert-view', require('./components/LanguageExpertView.vue').default)
Vue.component('master-expert-view', require('./components/MasterExpertView.vue').default)
Vue.component('users-table', require('./components/User/Table.vue').default)
Vue.component('app-sync', require('./components/AppSync.vue').default)

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const app = new Vue({
    el: '#app',
    store,
    data() {
        return {
            userId: null,
            appSyncActive: false,
            connectionState: 'initialized'
        }
    },
    created() {
        this.userId = JSON.parse(document.querySelector('meta[name="current-user"]').content).id

        this.$on('init-app-sync', () => {
            if (!this.appSyncActive) {
                this.initAppSync()
            }
        })
    },
    mounted() {
        if (document.querySelector('meta[name="current-user"]')) {
            this.$store.dispatch('app/setUser', JSON.parse(document.querySelector('meta[name="current-user"]').content))
        }
        if (document.querySelector('meta[name="app-languages"]')) {
            this.$store.dispatch('app/setLanguages', JSON.parse(document.querySelector('meta[name="app-languages"]').content))
        }

        setInterval(() => {
            axios.post('/refresh_csrf_token')
                .then(response => {
                    document.querySelector('meta[name="csrf-token"]').content = response.data.csrfToken

                    const inputs = document.querySelectorAll('input[type="hidden"][name="_token"]')

                    if (inputs && inputs.length > 0) {
                        inputs.forEach(input => {
                            input.value = response.data.csrfToken
                        })
                    }
                })
                .catch(error => {
                    console.error(error)
                })
        }, 15 * 60 * 1000)
    },
    beforeDestroy() {
        if (window.Echo) {
            if (this.appSyncActive) {
                Echo.leave('App.Sync')
                Echo.leave(`App.User.${this.userId}`)
            }

            Echo.disconnect()
        }
    },
    methods: {
        initAppSync() {
            if (this.appSyncActive) {
                return
            } else if (!window.Echo) {
                return
            }

            Echo.private('App.Sync')
                .listen('TopicCreated', e => {
                    this.$store.dispatch('topics/insertTopic', e)
                })
                .listen('TopicUpdated', e => {
                    this.$store.dispatch('topics/updateTopic', e)
                })
                .listen('TopicDeleted', e => {
                    this.$store.dispatch('topics/localDeleteTopic', e)
                })
                .listen('AnswerCreated', e => {
                    this.$store.dispatch('answers/insertAnswer', e)
                })
                .listen('AnswerUpdated', e => {
                    this.$store.dispatch('answers/updateAnswer', e)
                })
                .listen('AnswerDeleted', e => {
                    this.$store.dispatch('answers/localDeleteAnswer', e)
                })
                .listen('QuestionCreated', e => {
                    this.$store.dispatch('questions/insertQuestion', e)
                })
                .listen('QuestionUpdated', e => {
                    this.$store.dispatch('questions/updateQuestion', e)
                })
                .listen('QuestionDeleted', e => {
                    this.$store.dispatch('questions/localDeleteQuestion', e)
                })
                .listen('PendingQuestionCreated', e => {
                    this.$store.dispatch('pendingQuestions/insertPendingQuestion', e)
                })
                .listen('PendingQuestionUpdated', e => {
                    this.$store.dispatch('pendingQuestions/updatePendingQuestion', e)
                })
                .listen('PendingQuestionDeleted', e => {
                    this.$store.dispatch('pendingQuestions/localDeletePendingQuestion', e)
                })

            Echo.private(`App.User.${this.userId}`)
                .listen('UserUpdated', e => {
                    this.$store.dispatch('app/setUser', e)
                })

            this.appSyncActive = true

            const showErrorToast = message => {
                this.$bvToast.toast(message, {
                    title: 'Live-updates service error',
                    variant: 'danger',
                    toaster: 'b-toaster-bottom-left',
                    noCloseButton: true,
                    autoHideDelay: 3000,
                })
            }

            Echo.connector.pusher.connection.bind('error', err => {
                console.error('Echo connection error', err)

                if (err.type === 'PusherError') {
                    if (err.data && err.data.code && err.data.message) {
                        showErrorToast(err.data.message)
                    }
                } else if (err.type === 'WebSocketError') {
                    if (err.error && err.error.type === 'PusherError' && err.error.data && err.error.data.code && err.error.data.message) {
                        showErrorToast(err.error.data.message)
                    }
                }
            })
            Echo.connector.pusher.connection.bind('state_change', states => {
                this.connectionState = states.current
            })
        }
    }
})
