import { Component, Input, Output, EventEmitter } from '@angular/core';

import { DealRepository } from '../api/deal-repository.service';
import { Deal } from '../api/deal';


@Component({
  selector: 'feed',
  templateUrl: './app/feed/feed.component.html',
  styleUrls: [ './app/feed/feed.component.css' ]
})

export class FeedComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
	title : string;
	deals : Deal[];

	constructor(private dealRepository : DealRepository){
		this.title = "GeoDeals";
		this.titleUpdated.emit(this.title);
		this.deals = this.dealRepository.list();
	}
}
