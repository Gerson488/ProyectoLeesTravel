import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/',
      redirect: '/login',
    },

    {
      path: '/login',
      name: 'login',
      component: () => import('../components/Login/Vue/LoginView.vue'),
    },

    {
      path: '/select-platform',
      name: 'select-platform',
      component: () => import('../components/Login/Vue/SelectionView.vue'),
      meta: {
        requiresAuth: true,
      },
    },

    {
      path: '/home',
      name: 'home-web',
      component: () => import('../components/Home/Vue/HomeWebView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'web',
      },
    },

    {
      path: '/trips',
      name: 'trips',
      component: () => import('../components/Trips/Vue/TripsView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'web',
      },
    },

    {
      path: '/promociones',
      name: 'promociones',
      component: () => import('../components/Promociones/Vue/PromocionesView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'web',
      },
    },

    {
      path: '/itinerarios',
      name: 'itinerarios',
      component: () => import('../components/Itinerarios/Vue/ItinerariosView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'web',
      },
    },

    {
      path: '/leads',
      name: 'leads',
      component: () => import('../components/Leads/Vue/AdminLeads.vue'),
      meta: {
        requiresAuth: true,
        platform: 'web',
      },
    },

    {
      path: '/app-dashboard',
      name: 'app-dashboard',
      component: () => import('../components/Home/Vue/HomeAppView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'app',
      },
    },

    {
      path: '/travelers',
      name: 'travelers',
      component: () => import('../components/Travelers/Vue/TravelersView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'app',
      },
    },

    {
      path: '/bookings',
      name: 'bookings',
      component: () => import('../components/Booking/Vue/BookingView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'app',
      },
    },

    {
      path: '/passengers',
      name: 'passengers',
      component: () => import('../components/Passengers/Vue/PassengerView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'app',
      },
    },

    {
      path: '/blog',
      name: 'blog',
      component: () => import('../components/Blog/Vue/BlogView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'app',
      },
    },

    {
      path: '/users',
      name: 'users',
      component: () => import('../components/User/Vue/UserView.vue'),
      meta: {
        requiresAuth: true,
        requiresAdmin: true,
        platform: 'app',
      },
    },
    {
      path: '/asistencia',
      name: 'asistencia',
      component: () => import('../components/Attendance/Vue/AttendanceView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'app',
      },
    },
    {
      path: '/history',
      name: 'history',
      component: () => import('../components/History/Vue/HistoryView.vue'),
      meta: {
        requiresAuth: true,
        platform: 'app',
      },
    },

    {
      path: '/:pathMatch(.*)*',
      redirect: '/login',
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('leestravel_token')
  const sessionStr = localStorage.getItem('leestravel_session')
  const selectedPlatform = localStorage.getItem('leestravel_platform')

  const session = (() => {
    try {
      return sessionStr ? JSON.parse(sessionStr) : null
    } catch {
      localStorage.clear()
      return null
    }
  })()

  if (to.meta.requiresAuth && (!token || !session)) {
    localStorage.removeItem('leestravel_token')
    localStorage.removeItem('leestravel_session')
    localStorage.removeItem('leestravel_platform')

    return '/login'
  }

  if (session?.Access_Role === 'Pasajero') {
    localStorage.clear()
    return '/login'
  }

  if (to.path === '/login' && token && session) {
    if (!selectedPlatform) {
      return '/select-platform'
    }

    return selectedPlatform === 'web' ? '/home' : '/app-dashboard'
  }

  if (to.meta.requiresAdmin) {
    if (session?.Access_Role !== 'Admin') {
      return '/select-platform'
    }
  }

  if (to.meta.platform) {
    if (!selectedPlatform) {
      return '/select-platform'
    }

    if (to.meta.platform !== selectedPlatform) {
      return selectedPlatform === 'web' ? '/home' : '/app-dashboard'
    }
  }

  return true
})

export default router
