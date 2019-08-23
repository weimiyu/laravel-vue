import Vue from 'vue'
import Router from 'vue-router'
import routes from '@/router/routes'
import { getToken, removeToken } from '@/libs/token'
import store from '@/store'
import _get from 'lodash/get'
import { cancelAllRequest } from '@/plugins/axios'

import NProgress from 'nprogress'
import 'nprogress/nprogress.css'
import { SYSTEM_BASIC } from '@/libs/constants'

NProgress.configure({ showSpinner: false })

Vue.use(Router)

const router = new Router({
  mode: 'history',
  base: process.env.NODE_ENV === 'development' ? 'admin-dev' : 'admin',
  routes,
})

const loginRoute = to => ({
  name: 'login',
  query: {
    redirect: to.path,
  },
})

/**
 * 在组件被解析前，修改组件的名字为：组件名 + 路由数据库 id，
 * 使唯一，可用于 keep-alive 的 include 中
 *
 * @param to
 * @returns {Promise<void>}
 */
const renameComponent = async (to) => {
  // 如果去的页面不需要缓存，则跳过
  if (!_get(to, 'meta.cache')) {
    return
  }

  let c = router.getMatchedComponents(to)
  c = c[c.length - 1]

  if (c && (c instanceof Function)) {
    c = (await c()).default
    c.name += to.meta.id

    store.commit('ADD_INCLUDE', c.name)
  }
}

router.beforeEach(async (to, from, next) => {
  cancelAllRequest('页面切换，取消请求')

  await renameComponent(to)

  // 刷新页面, 往 query 中加入 _refresh 当前时间戳
  // 然后立马用原页面 replace 掉
  if (to.query._refresh !== undefined) {
    const query = {
      ...to.query,
    }
    delete query._refresh
    next()
    router.replace({
      path: to.path,
      query,
    })
    return
  }

  NProgress.start()

  try {
    if (!store.getters.getConfig(SYSTEM_BASIC.SLUG)) {
      await store.dispatch('getSystemBasicConfigs')
    }
  } catch (e) {
    next(false)
    NProgress.done()
    throw e
  }

  if (getToken()) { // 有 token 暂定为已登录
    if (to.name === 'login') { // 有 token，访问登录页，跳转到首页
      next('/')
    } else { // 否则应该获取用户信息和路由配置
      const requests = []
      try {
        const loggedIn = store.getters.loggedIn
        const vueRoutersLoaded = store.state.vueRouters.loaded

        !loggedIn && requests.push(store.dispatch('getUser'))
        !vueRoutersLoaded && requests.push(store.dispatch('getVueRouters'))
        await Promise.all(requests)

        // 如果之前没有路由配置，则获取完路由配置后，要重新定位到要去的路由
        // 因为路由配置已经变了
        if (!vueRoutersLoaded) {
          router.replace(to)
        } else {
          next()
        }
      } catch ({ response: res }) {
        if (res && res.status === 401) {
          removeToken()
          location.href = router.resolve(loginRoute(to)).href
        } else {
          NProgress.done()
          next(false)
        }
      }
    }
  } else if (to.name !== 'login') { // 没 token 访问后台，跳到登录页
    next(loginRoute(to))
  } else { // 没 token 访问登录页，通过
    next()
  }
})

router.afterEach(() => {
  NProgress.done()
  Vue.nextTick(() => {
    const { matchedMenu, appName } = store.getters
    const title = matchedMenu ? matchedMenu.title : _get(router, 'currentRoute.meta.title')
    document.title = `${title ? title + ' - ' : ''} ${appName}`

    // 滚动到顶部
    const main = document.querySelector('#main')
    main && main.scrollTo({ left: 0, top: 0 })
  })
})

router.onError((e) => {
  NProgress.done()
  throw e
})

export default router
