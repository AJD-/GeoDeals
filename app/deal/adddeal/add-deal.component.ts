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
    private lat: number;
    private long: number;

    constructor(private router: Router,
        private route: ActivatedRoute,
        private dealRepository: DealRepository) {  }
    submit() {
        this.dealRepository.add(this.deal)
            .then(x => this.goToDealDetail('Deal Submitted'));
    }
    goToDealDetail(message: string) {
        this.router.navigateByUrl('feed') // change to send to deal detail when that is completed
            .then(() => console.log(message));
    }
    getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(this.getCoords);
        } else {
            let errstr = "Geolocation is not supported by this browser.";
        }
    }
    getCoords(position) {
        this.lat = position.coords.latitude;
        this.long = position.coords.longitude;
    }
}
