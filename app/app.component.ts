import { Component, ViewEncapsulation } from '@angular/core';

@Component({
  selector: 'app',
  templateUrl: './app/app.component.html',
  styleUrls: ['./app/app.component.css'],
  encapsulation: ViewEncapsulation.None
})

export class AppComponent { 
	title : string;

	constructor(){
		this.title = "GeoDeals";
	}
	handleTitleUpdate(titleFromChild:string):void{
		this.title = titleFromChild;
	}
}
