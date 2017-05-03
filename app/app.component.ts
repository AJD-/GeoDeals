import { Component, ViewEncapsulation } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { DealRepository } from './api/deal-repository.service';
import { Deal } from './api/deal';

@Component({
  selector: 'app',
  templateUrl: './app/app.component.html',
  styleUrls: ['./app/app.component.css'],
  encapsulation: ViewEncapsulation.None
})

export class AppComponent { 
	title : string;
    loggedIn: boolean;
    clicked: boolean = false;
    searchQuery: any = {};
    loc: any = {};
    deals: Deal[];

	constructor(
		private router: Router,
        private route: ActivatedRoute,
        private dealRepository: DealRepository
	){
		router.events.subscribe((loggedIn) => this.loggedIn = (router.url === "/feed"));
		this.title = "GeoDeals";
	}
	ngOnInit(){
		if(this.router.url === '/'){	
			this.loggedIn = true;
		}else{
			this.loggedIn = false;
		}
	}
	handleTitleUpdate(titleFromChild:string):void{
		this.title = titleFromChild;
    }
    getLocation() {
        this.clicked = !this.clicked;
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
    search() {
        this.searchQuery.latitude = this.loc.latitude;
        this.searchQuery.longitude = this.loc.longitude;
        console.log(this.searchQuery);
        this.dealRepository.search(this.searchQuery)
            .then(x => {
                this.deals = x;
                window.sessionStorage.setItem("deals_search", JSON.stringify(this.deals));
            });
    }
}