import Vue from "vue";
import VueRouter from "vue-router";
import LittersIndexPage from "./components/litters/LittersIndexPage";
import LitterCreateEditPage from "./components/litter/create/LitterCreateEditPage";
import LitterPage from "./components/litter/index/LitterPage";
import UsersIndexPage from "./components/users/UsersIndexPage";
import AnimalCreateEditPage from "./components/animal/create/AnimalCreateEditPage";
import AnimalsIndexPage from "./components/animals/AnimalsIndexPage";
import AnimalPage from "./components/animal/index/AnimalPage";
import UnauthorizedPage from "./components/auth/UnauthorizedPage";
import PageNotFound from "./components/common/PageNotFound";

Vue.use(VueRouter);

// Define routes and its components
const routes = [
    { path: '/', name: 'index', redirect: { name: 'animals' } },
    { path: '/animals', name: 'animals', component: AnimalsIndexPage },
    { path: '/animals/create', name: 'animals.create', component: AnimalCreateEditPage },
    { path: '/animals/:animal/', name: 'animal', component: AnimalPage },
    { path: '/animals/:animal/edit', name: 'animal.edit', component: AnimalCreateEditPage },
    { path: '/litters', name: 'litters', component: LittersIndexPage },
    { path: '/litters/create', name: 'litters.create', component: LitterCreateEditPage },
    { path: '/litters/:litter/', name: 'litter', component: LitterPage },
    { path: '/litters/:litter/edit', name: 'litter.edit', component: LitterCreateEditPage },
    { path: '/users', name: 'users', component: UsersIndexPage },
    { path: '/unauthorized', 'name': 'unauthorized', component: UnauthorizedPage },
    { path: '*', 'name': 'notfound', component: PageNotFound }
];

export default new VueRouter ({
    mode: 'history',
    routes
});
