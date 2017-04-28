import { Component, Input } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { DealRepository } from '../../api/deal-repository.service';

@Component({
    selector: 'adddeal',
    templateUrl: './app/deal/adddeal/add-deal.component.html',
	styleUrls: [ './app/deal/adddeal/add-deal.component.css' ]
})
export class AddDealComponent {

    private deal: any = {};

    constructor(private router: Router,
        private route: ActivatedRoute,
        private dealRepository: DealRepository) {  }
    submit() {
        this.dealRepository.add(this.deal)
            .then(x => this.goToDealDetail('Deal Submitted'));
    }
    goToDealDetail(message: string) {
        this.router.navigateByUrl('deal/' + this.deal.id) //this won't work as we don't know the deal id before we send it off
            .then(() => console.log(message));
    }
}
