import { Component, Input } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { DealRepository } from '../../api/deal-repository.service';

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
        var onLoad = (data) => {
            this.deal = data;
            this.title = this.deal.title.toString();				
        };

		this.route.params.subscribe(params => {
			if(params['id'] !== undefined) {
                this.dealRepository.get(+params['id'])
                    .then(onLoad);
			} else {
				this.deal = {
				};
				this.title = 'New Deal';
			}
		});
	}

}
