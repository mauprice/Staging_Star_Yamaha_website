import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import PartsRoot from './parts/PartsRoot.vue'
import ModelBrowser from './parts/pages/ModelBrowser.vue'
import ModelPage from './parts/pages/ModelPage.vue'
import AssemblyPage from './parts/pages/AssemblyPage.vue'
import PartSearch from './parts/pages/PartSearch.vue'
import HondaModelBrowser from './parts/pages/honda/ModelBrowser.vue'
import HondaModelPage from './parts/pages/honda/ModelPage.vue'
import HondaAssemblyPage from './parts/pages/honda/AssemblyPage.vue'
import HondaPartSearch from './parts/pages/honda/PartSearch.vue'

const router = createRouter({
    history: createWebHistory('/parts-finder'),
    routes: [
        { path: '/',             component: ModelBrowser },
        { path: '/model/:id',    component: ModelPage,    props: true },
        { path: '/assembly/:id', component: AssemblyPage, props: true },
        { path: '/search',       component: PartSearch },
        { path: '/honda',               component: HondaModelBrowser },
        { path: '/honda/model/:id',     component: HondaModelPage,    props: true },
        { path: '/honda/assembly/:id',  component: HondaAssemblyPage, props: true },
        { path: '/honda/search',        component: HondaPartSearch },
    ],
    scrollBehavior: () => ({ top: 0 }),
})

createApp(PartsRoot).use(router).mount('#parts-app')
