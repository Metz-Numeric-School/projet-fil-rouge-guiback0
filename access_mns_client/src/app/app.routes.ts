import { Routes } from '@angular/router';
import { LoginComponent } from './components/login/login.component';
import { UserComponent } from './components/user/user.component';

export const routes: Routes = [
  { path: '', redirectTo: '/login', pathMatch: 'full' },
  { path: 'login', component: LoginComponent },
  { path: 'dashboard', component: UserComponent }, // Placeholder for dashboard
  { path: 'user', component: UserComponent },
  { path: '**', redirectTo: '/login' }, // Wildcard route for 404 cases
];
