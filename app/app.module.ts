import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { HttpModule } from '@angular/http';
import { Http, Headers, Response } from '@angular/http';
import { InMemoryWebApiModule } from 'angular-in-memory-web-api';
import { MockApiService } from './mock-api.service';
import { enableProdMode } from '@angular/core';

import { AppComponent }   from './app.component';
import { HomeComponent }   from './home/home.component';
import { SignupComponent } from './signup/signup.component';
import { LoginComponent } from './login/login.component';
import { FeedComponent } from './feed/feed.component';
import { DealRepository } from './api/deal-repository.service';
import { DealModule } from './deal/deal.module';
import { UserRepository } from './api/user-repository.service';
import { VoteService } from './api/vote.service';
import { LocationService } from './api/location.service';
enableProdMode();

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
      FormsModule,
      HttpModule,
      RouterModule
],
  declarations: [ AppComponent, HomeComponent, SignupComponent, LoginComponent, FeedComponent ],
  providers: [
      DealRepository,
      UserRepository,
      VoteService,
      LocationService
  ],
  bootstrap:    [ AppComponent ]
})

export class AppModule { }
