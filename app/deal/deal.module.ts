import { NgModule }      from '@angular/core';
import { BrowserModule } from '@angular/platform-browser';
import { RouterModule } from '@angular/router';
import { FormsModule } from '@angular/forms';

import { DealDetailComponent } from './dealdetail/deal-detail.component';
import { AddDealComponent } from './adddeal/add-deal.component';
import { DealRepository } from '../api/deal-repository.service';

@NgModule({
  imports: [ 
      BrowserModule,
      RouterModule.forRoot([
          {
              path: 'deal/:dealid',
              component: DealDetailComponent
          },
          {
              path: 'new',
              component: AddDealComponent
          }
      ]),
  ],
  declarations: [
      DealDetailComponent,
      AddDealComponent
  ],
  exports: [
      DealDetailComponent,
      AddDealComponent
  ],
  providers: [
      DealRepository
  ]
})

export class DealModule { }
