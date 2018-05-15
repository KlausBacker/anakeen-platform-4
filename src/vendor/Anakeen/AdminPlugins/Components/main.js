import Users from './UsersManagement/UsersManagement';
import UsersSection from './UsersSection/UsersSection';
import Settings from './TechnicalsSettings/TechnicalsSettings';
import Vue from 'vue';

Vue.component(Users.name, Users);
Vue.component(Settings.name, Settings);
Vue.component(UsersSection.name, UsersSection);