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
    private addedStore: boolean = false;
    private store: any;
    private currentDate: any;

    constructor(private router: Router,
        private route: ActivatedRoute,
        private dealRepository: DealRepository,
        private http: Http,
        private locationService: LocationService) {

        //Get current date
        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1;
        var yyyy = today.getFullYear();
        let day = "";
        let month = "";
        if (dd < 10) {
            day = '0' + dd
        }
        if (mm < 10) {
            month = '0' + mm
        }
        let datestr = yyyy + '-' + month + '-' + day;
        this.currentDate = datestr;
    }

    submit() {
        console.log(this.deal);
        this.dealRepository.add(this.deal)
            .then(x => this.goToDealDetail('Deal Submitted'));
    }

    goToDealDetail(message: string) {
        this.router.navigateByUrl('feed') // change to send to deal detail when that is completed
            .then(() => console.log(message));
    }

    locationSubmit() {
        console.log("Location activated with: \nState: " + this.loc.state + "\nCity: " + this.loc.city + "\nLatitude: " + this.loc.latitude + "\nLongitude: " + this.loc.longitude);
    }

    getLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                //Hack to get around 'this' being null
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
        console.log("Sending search out with query: " + value);
        this.loc.store = value;
        console.log(this.loc);
        this.locationService.sendLoc(this.loc)
            .then(x => this.stores = x);
    }

    //expecting a json object with an id parameter
    selectedStore(store: any) {
        this.deal.store_id = store.id;
        this.store = store;
        console.log("Added store id: " + this.deal.store_id + " to deal.");
        this.addedStore = true;
    }
}
