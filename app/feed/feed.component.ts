import { Component, Input, Output, EventEmitter } from '@angular/core';

import { MovieRepository } from 'api/deal-repository.service';
import { Deal } from 'api/deal';


@Component({
  selector: 'feed',
  templateUrl: './app/feed/feed.component.html',
  styleUrls: [ './app/feed/feed.component.css' ]
})

export class FeedComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
	title : string;

	constructor(){
		this.title = "GeoDeals";
		this.titleUpdated.emit(this.title);
	}
}
