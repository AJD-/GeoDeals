import { Component, Input, Output, EventEmitter } from '@angular/core';

import { DealRepository } from '../api/deal-repository.service';
import { VoteService } from '../api/vote.service';
import { Deal } from '../api/deal';
import { Vote } from '../api/vote';
import { User } from '../api/user';


@Component({
  selector: 'feed',
  templateUrl: './app/feed/feed.component.html',
  styleUrls: [ './app/feed/feed.component.css' ]
})

export class FeedComponent { 
	@Output() titleUpdated : EventEmitter<string> = new EventEmitter();
	title : string;
    deals: Deal[];
    vote: Vote = new Vote;
	uv : boolean[];
	dv : boolean[];

    constructor(private dealRepository: DealRepository, private voteService: VoteService) {
		this.title = "GeoDeals";
        this.titleUpdated.emit(this.title);
        this.dv = [];
        this.uv = [];
        dealRepository.list()
            .then(x => {
                console.log("Received " + x);
                if (x) {
                    this.deals = x;
                    for (let x = 0; x < this.deals.length; x++) {
                        this.dv.push(false);
                        this.uv.push(false);
                    }
                }
            });		
	}
	upvote(index : number, deal_id: number){
		if(!this.uv[index]){
			if(this.dv[index]){
                this.downvote(index, deal_id);
			}
            this.deals[index].vote_count++; //uv: send vote type  1, -1 for dv, 0 for unvote
            this.vote.deal_id = deal_id;
            this.vote.vote_type = 1;
            this.voteService.vote(this.vote)
                .then(x => console.log("Voted up"));
			this.uv[index] = true;
		}else{
            this.deals[index].vote_count--;
            this.vote.deal_id = deal_id;
            this.vote.vote_type = 0;
            this.voteService.vote(this.vote)
                .then(x => console.log("Unvoted"));
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
	downvote(index : number, deal_id: number){
		if(!this.dv[index]){
			if(this.uv[index]){
                this.upvote(index, deal_id);
			}
            this.deals[index].vote_count--;
            this.vote.deal_id = deal_id;
            this.vote.vote_type = -1;
            this.voteService.vote(this.vote)
                .then(x => console.log("Voted down"));
			this.dv[index] = true;
		}else{
            this.deals[index].vote_count++;
            this.vote.deal_id = deal_id;
            this.vote.vote_type = 0;
            this.voteService.vote(this.vote)
                .then(x => console.log("Unvoted"));
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
