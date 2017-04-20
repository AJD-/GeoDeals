import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';

import { NewDealComponent } from './newdeal/new-deal.component';
import { AddDealComponent } from './adddeal/add-deal.component';
import { DealRepository } from '../api/deal-repository.service';

@NgModule({
  imports: [ 
      BrowserModule,
      RouterModule.forRoot([
          {
              path: 'deal/:dealid',
              component: AddDealComponent
          },
          {
              path: 'deal/new',
              component: NewDealComponent
          }
      ]),
  ],
  declarations: [
      NewDealComponent,
      AddDealComponent
  ],
  exports: [
      NewDealComponent,
      AddDealComponent
  ],
  providers: [
      DealRepository
  ]
})

export class DealModule { }
