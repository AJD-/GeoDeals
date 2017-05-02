import { Component, Input, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { Deal } from '../../api/deal'
import { DealRepository } from '../../api/deal-repository.service';
import 'rxjs/add/operator/switchMap';

@Component({
    selector: 'dealdetail',
    templateUrl: './app/deal/dealdetail/deal-detail.component.html',
	styleUrls: [ './app/deal/dealdetail/deal-detail.component.css' ]
})
export class DealDetailComponent {

    title: string;
    deal: any;

    constructor(private router: Router,
                private route: ActivatedRoute,
                private dealRepository: DealRepository){}

	ngOnInit() {
        this.route.params
            .switchMap((params: Params) => this.dealRepository.get(+params['id']))
            .subscribe(deal => this.deal = deal);
    }
}
