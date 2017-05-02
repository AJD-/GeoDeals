import { Component, ViewEncapsulation } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app',
  templateUrl: './app/app.component.html',
  styleUrls: ['./app/app.component.css'],
  encapsulation: ViewEncapsulation.None
})

export class AppComponent { 
	title : string;
	loggedIn: boolean;

	constructor(
		private router: Router,
        private route: ActivatedRoute
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
}