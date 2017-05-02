import { Component, Input } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { DealRepository } from '../../api/deal-repository.service';
import { Http } from '@angular/http';
import { LocationService } from '../../api/location.service';

@Component({
    selector: 'adddeal',
    templateUrl: './app/deal/adddeal/add-deal.component.html',
	styleUrls: [ './app/deal/adddeal/add-deal.component.css' ]
})
export class AddDealComponent {

    private deal: any = {};
    private loc: any = {};
    private stores: any[];

    constructor(private router: Router,
        private route: ActivatedRoute,
        private dealRepository: DealRepository,
        private http: Http,
        private locationService: LocationService) { }

    submit() {
        this.dealRepository.add(this.deal)
            .then(x => this.goToDealDetail('Deal Submitted'));
    }

    goToDealDetail(message: string) {
        this.router.navigateByUrl('feed') // change to send to deal detail when that is completed
            .then(() => console.log(message));
    }

    locationSubmit() {
        console.log("Location activated with " + this.loc.state + ", " + this.loc.city);
    }

    getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                window.sessionStorage.setItem("latitude", String(position.coords.latitude));
                window.sessionStorage.setItem("longitude", String(position.coords.longitude));
            });
            this.loc.latitude = Number(window.sessionStorage.getItem("latitude"));
            this.loc.longitude = Number(window.sessionStorage.getItem("longitude"));
        } else {
            console.log("Geolocation is not supported by this browser.");
        }
    }

    search(value: string) {
        console.log("ayy u hit search with search query: " + value);
        this.loc.store = value;
        console.log(this.loc);
        this.locationService.sendLoc(this.loc)
            .then(x => this.stores = x);
    }
}
