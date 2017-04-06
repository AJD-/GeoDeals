import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule }   from '@angular/router';

import { AppComponent }   from './app.component';
import { HomeComponent }   from './home/home.component';
import { SignupComponent } from './signup/signup.component';
import { LoginComponent } from './login/login.component';
import { FeedComponent } from './feed/feed.component';


@NgModule({
  imports:      [ 
	BrowserModule,
	RouterModule.forRoot([
		{
			path: '',
			component: HomeComponent
		},
		{
			path: 'signup',
			component: SignupComponent
		},
		{
			path: 'login',
			component: LoginComponent
		},
		{
			path: 'feed',
			component: FeedComponent
		}
	])
],
  declarations: [ AppComponent, HomeComponent, SignupComponent, LoginComponent, FeedComponent ],
  bootstrap:    [ AppComponent ]
})

export class AppModule { }
