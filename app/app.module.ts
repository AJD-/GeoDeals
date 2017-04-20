import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';

import { AppComponent }   from './app.component';
import { HomeComponent }   from './home/home.component';
import { SignupComponent } from './signup/signup.component';
import { LoginComponent } from './login/login.component';
import { FeedComponent } from './feed/feed.component';
import { DealRepository } from './api/deal-repository.service';
import { DealModule } from './deal/deal.module';


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
    ]),
      DealModule,
      FormsModule
],
  declarations: [ AppComponent, HomeComponent, SignupComponent, LoginComponent, FeedComponent ],
  providers: [ DealRepository ],
  bootstrap:    [ AppComponent ]
})

export class AppModule { }
