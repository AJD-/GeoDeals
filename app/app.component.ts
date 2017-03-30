import { Component } from '@angular/core';

@Component({
  selector: 'app',
  templateUrl: './app/app.component.html',
  styleUrls: [ './app/app.component.css' ],
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
