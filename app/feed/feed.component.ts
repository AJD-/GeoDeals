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
	uv : boolean[];
	dv : boolean[];

	constructor(private dealRepository : DealRepository){
		this.title = "GeoDeals";
		this.titleUpdated.emit(this.title);
		this.deals = this.dealRepository.list();
		this.dv = [];
		this.uv = [];
		for(let x = 0; x < this.deals.length; x++){
			this.dv.push(false);
			this.uv.push(false);
		}
	}
	upvote(index : number){
		if(!this.uv[index]){
			if(this.dv[index]){
				this.downvote(index);
			}
			this.deals[index].rating++;
			this.uv[index] = true;
		}else{
			this.deals[index].rating--;
			this.uv[index] = false;
		}
	}
	isUpvoted(index : number){
		if(this.uv[index]) {
			return "green";
		} else {
			return "";
		}
	}
	downvote(index : number){
		if(!this.dv[index]){
			if(this.uv[index]){
				this.upvote(index);
			}
			this.deals[index].rating--;
			this.dv[index] = true;
		}else{
			this.deals[index].rating++;
			this.dv[index] = false;
		}
	}
	isDownvoted(index : number){
		if(this.dv[index]) {
			return "red";
		} else {
			return "";
		}
	}
}
