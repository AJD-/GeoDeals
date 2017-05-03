import { Component, Input, OnInit, Output, EventEmitter } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { Deal } from '../../api/deal'
import { DealRepository } from '../../api/deal-repository.service';
import 'rxjs/add/operator/switchMap';
import { Http } from '@angular/http';
import { LocationService } from '../../api/location.service';
import { Vote } from '../../api/vote';
import { User } from '../../api/user';
import { VoteService } from '../../api/vote.service';

@Component({
    selector: 'dealdetail',
    templateUrl: './app/deal/dealdetail/deal-detail.component.html',
	styleUrls: [ './app/deal/dealdetail/deal-detail.component.css' ]
})
export class DealDetailComponent {
    @Output() titleUpdated : EventEmitter<string> = new EventEmitter();
    title: string;
    deal: Deal;
    deals: Deal[];
    vote: Vote = new Vote;
	uv : boolean[];
	dv : boolean[];

    constructor(private router: Router,
                private route: ActivatedRoute,
                private dealRepository: DealRepository,
                private http: Http,
                private locationService: LocationService,
                private voteService: VoteService){
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
                    route.params.subscribe(params => {
                        console.log(this.deals.values);
                        this.deal = this.deals[params['dealid']-1];
                    });
                }
            });
    }
    upvote(index : number, deal_id: number){
		if(!this.uv[index]){
			if(this.dv[index]){
                this.downvote(index, deal_id);
			}
            this.deals[index].vote_count++; //uv: send vote type  1, 0 for dv, 2 for unvote
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
