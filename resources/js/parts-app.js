import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import PartsRoot from './parts/PartsRoot.vue'
import ModelBrowser from './parts/pages/ModelBrowser.vue'
import ModelPage from './parts/pages/ModelPage.vue'
import AssemblyPage from './parts/pages/AssemblyPage.vue'
import PartSearch from './parts/pages/PartSearch.vue'

const router = createRouter({
    history: createWebHistory('/parts-finder'),
    routes: [
        { path: '/',             component: ModelBrowser },
        { path: '/model/:id',    component: ModelPage,    props: true },
        { path: '/assembly/:id', component: AssemblyPage, props: true },
        { path: '/search',       component: PartSearch },
    ],
    scrollBehavior: () => ({ top: 0 }),
})

createApp(PartsRoot).use(router).mount('#parts-app')
